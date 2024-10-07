<?php

namespace App\Support\Reports;

use App\Support\Meta\Fields\Field;
use App\Support\Meta\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;

readonly class Report
{
    /** @var Collection<string, Header> $headers */
    public Collection $headers;

    private Model $model;

    /**
     * @param Header[] $headers
     * @param class-string<Model> $model
     */
    public function __construct(public string $name, string $model, array $headers)
    {
        $this->headers = collect($headers)->keyBy('key');
        $this->model = new $model();
    }

    /** @return Collection<int, object> */
    public function getRecords(): Collection
    {
        return $this->toQuery()->get();
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

        $selects = $fields
            ->map(function (Field $field, string $path) use ($fields) {
                $relations = array_slice(explode('.', $path), 0, -1);
                $sql = $fields->get($path)->toSql($fields, implode('.', $relations));

                return DB::raw("$sql as $field->name");
            })
            ->all();

        $query = DB::table("{$this->model->table} as {$this->model->name}")->select($selects);

        return self::applyJoins($query, $relationTree, $this->model, $this->model->name);
    }

    private static function applyJoins(Builder $query, array $relationTree, Model $model, string $prefix): Builder
    {
        foreach ($relationTree as $relationName => $childRelations) {
            $relation = $model->relations->get($relationName);
            $alias = $prefix.'__'.$relationName;

            $query->leftJoin("{$relation->getModel()->table} as $alias", function (JoinClause $join) use ($prefix, $relation, $alias, $childRelations) {
                foreach ($relation->onColumns as $left => $right) {
                    $join->on($prefix.'.'.$left, '=', $alias.'.'.$right);
                }
            });

            if ($childRelations) {
                self::applyJoins($query, $childRelations, $relation->getModel(), $alias);
            }
        }

        return $query;
    }
}
