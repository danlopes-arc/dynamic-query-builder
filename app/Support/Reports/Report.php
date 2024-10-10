<?php

namespace App\Support\Reports;

use App\Support\Meta\Fields\Field;
use App\Support\Meta\Model;
use App\Support\Meta\Types\TypeValidationException;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class Report
{
    /** @var Collection<string, Header> $headers */
    public Collection $headers;


    /** @var Collection<string, 'asc'|'desc'> $orderBy */
    private Collection $orderBy;

    private Model $model;

    /**
     * @param Header[] $headers
     * @param class-string<Model> $model
     * @param array<string, 'asc'|'dsc'> $orderBy
     */
    public function __construct(public string $name, string $model, array $headers, array $orderBy = [])
    {
        $this->headers = collect($headers)->keyBy('key');
        $this->model = new $model();

        foreach ($orderBy as $key => $direction) {
            if (!$this->headers->has($key)) {
                throw new Exception("OrderBy key [$key] is not a header.");
            }
        }

        $this->orderBy = collect($orderBy);
    }

    public function orderBy(array $keys): static
    {
        return new static(
            name: $this->name,
            model: $this->model::class,
            headers: $this->headers->values()->all(),
            orderBy: $keys
        );
    }

    /** @return Collection<int, object> */
    public function getRecords(): Collection
    {
        // TODO: extract
        $query = $this->toQuery();

        foreach ($this->orderBy as $key => $direction) {
            $query->orderBy($key, $direction);
        }

        return $query
            ->get()
            ->map(function (object $record) {
                return $this->headers->map(function (Header $header) use ($record) {
                    try {
                        return $header->getType($this->model)->fromDatabase($record->{$header->key});
                    } catch (TypeValidationException $e) {
                        throw new Exception("Type casting error for [$header->key]: {$e->getMessage()}");
                    }
                })->all();
            });
    }

    public function toQuery(): Builder
    {
        /** @var Collection<string, Field> $fields */
        $fields = collect();

        foreach ($this->headers->all() as $header) {
            $fields = $fields->merge($header->getDependentFields($this->model, $fields, $this->model->name)->all());
        }

        $relationTree = $fields->keys()
            ->mapWithKeys(fn(string $path) => [implode('.', array_slice(explode(".", $path), 0, -1)) => []])
            ->undot()
            ->get($this->model->name);

        $selects = $this->headers
            ->map(function (Header $header) use ($fields) {
                $relations = array_slice(explode('.', $header->path), 0, -1);
                $sql = $header->getField($this->model)->toSql($fields, implode('.', [$this->model->name, ...$relations]));

                return DB::raw("$sql as $header->key");
            })
            ->all();

        $query = DB::table("{$this->model->table} as {$this->model->name}")->select($selects);

        return self::applyJoins($query, $relationTree, $this->model, $fields, $this->model->name);
    }

    /** @param Collection<string, Field> $fields */
    private static function applyJoins(Builder $query, array $relationTree, Model $model, Collection $fields, string $prefix): Builder
    {
        foreach ($relationTree as $relationName => $childRelations) {
            $relation = $model->relations->get($relationName);
            $alias = str_replace('.', '__', $prefix).'__'.$relationName;

            $query->leftJoin("{$relation->getModel()->table} as $alias", function (JoinClause $join) use ($fields, $prefix, $relation, $alias, $childRelations) {
                $relation->joinClause->apply($join, $fields, $prefix);
            });

            if ($childRelations) {
                self::applyJoins($query, $childRelations, $relation->getModel(), $fields, $prefix.'.'.$relationName);
            }
        }

        return $query;
    }
}
