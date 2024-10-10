<?php

namespace App\Support\Meta\Fields;

use App\Support\Meta\Model;
use App\Support\Meta\Types\Type;
use Illuminate\Support\Collection;

abstract readonly class Field
{
    /** @var Collection<int, string> $dependencies */
    public Collection $dependencies;

    /** @param string[] $dependencies */
    public function __construct(public string $name, public Type $type, array $dependencies)
    {
        $this->dependencies = collect($dependencies);
    }

    /** @param Collection<string, Field> $fields */
    abstract function toSql(Collection $fields, string $prefix): string;

    /* ************************************************************************
     * Static Methods
     * ************************************************************************/

    public static function column(string $name, Type $type): ColumnField
    {
        return new ColumnField($name, $type);
    }

    /** @param callable(string ...$args): string $get */
    public static function computed(string $name, Type $type, array $dependencies, callable $get): ComputedField
    {
        return new ComputedField($name, $type, $dependencies, $get);
    }

    /** @return Collection<string, Field> */
    public static function getDependentFields(string $path, Model $model, Collection $dependencyFields, string $prefix): Collection
    {
        if ($dependencyFields->has("$prefix.$path")) {
            return $dependencyFields;
        }

        $currentModel = $model;

        $relations = explode('.', $path);
        $fieldName = array_pop($relations);

        $updatedFields = collect($dependencyFields);

        $currentPrefix = $prefix;

        foreach ($relations as $relationName) {
            $relation = $currentModel->relations->get($relationName);

            foreach ($relation->dependencies as $relationPath) {
                $relationPathRelations = explode('.', $relationPath);
                $relationPathFieldName = array_pop($relationPathRelations);

                // TODO: refactor
                if ($relationPathRelations && $relationPathRelations[0] === $relationName) {
                    $newPath = implode('.', [...array_slice($relationPathRelations, 1), $relationPathFieldName]);
                    $newPrefix = "$currentPrefix.$relationName";

                    $updatedFields = $updatedFields->merge(
                        static::getDependentFields($newPath, $relation->getModel(), $updatedFields, $newPrefix)
                    );
                } else {
                    $updatedFields = $updatedFields->merge(
                        static::getDependentFields($relationPath, $currentModel, $updatedFields, $currentPrefix)
                    );
                }

            }

            $currentPrefix = "$prefix.$relationName";
            $currentModel = $relation->getModel();
        }

        $field = $currentModel->fields->get($fieldName);

        foreach ($field->dependencies as $relativePath) {
            $dependentFields = static::getDependentFields($relativePath, $currentModel, $updatedFields, implode('.', [$prefix, ...$relations]));
            $updatedFields = $updatedFields->merge($dependentFields);
        }

        return $updatedFields->merge(["$prefix.$path" => $field]);
    }
}
