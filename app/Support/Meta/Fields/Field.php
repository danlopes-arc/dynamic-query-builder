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

        foreach ($relations as $relationName) {
            $currentModel = $currentModel->relations->get($relationName)->getModel();
        }

        $field = $currentModel->fields->get($fieldName);

        $updatedFields = collect($dependencyFields);

        foreach ($field->dependencies as $relativePath) {
            $dependentFields = static::getDependentFields($relativePath, $currentModel, $updatedFields, implode('.', [$prefix, ...$relations]));
            $updatedFields = $updatedFields->merge($dependentFields);
        }

        return $updatedFields->merge(["$prefix.$path" => $field]);
    }
}
