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
        return $this->toQuery()->get()->map(function (object $record) {
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

        // TODO: fields depend on relationTree and vice versa. After this line we might have new fields without relations.
        $fields = static::getRelationDependentFields($this->model, $relationTree, $fields, $this->model->name);

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


    /**
     * @param Collection<string, Field> $fields
     * @return  Collection<string, Field>
     */
    private static function getRelationDependentFields(Model $model, array $relationTree, Collection $fields, string $prefix): Collection
    {
        $updatedFields = collect($fields);

        foreach ($relationTree as $relationName => $childRelations) {
            $relation = $model->relations->get($relationName);

            foreach ($relation->dependencies as $relativePath) {
                // TODO: do i need the merge or $updatedFields = Field::getDependentF...?
                $dependentFields = Field::getDependentFields($relativePath, $model, $fields, $prefix);
                $updatedFields = $updatedFields->merge($dependentFields);
            }

            if ($childRelations) {
                $updatedFields = self::getRelationDependentFields($relation->getModel(), $childRelations, $updatedFields, "$prefix.$relationName");
            }
        }

        return $updatedFields;
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
