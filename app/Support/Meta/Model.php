<?php

namespace App\Support\Meta;

use App\Support\Meta\Fields\Field;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

abstract readonly class Model
{
    /** @var Collection<string, Field> */
    public Collection $fields;

    /** @var Collection<string, Relation> */
    public Collection $relations;

    public string $name;

    /**
     * @param Field[] $fields
     * @param Relation[] $relations
     */
    public function __construct(public string $table, public string $primaryKey, array $fields, array $relations = [])
    {
        // TODO: validate column and relation names
        $this->fields = collect($fields)->keyBy('name');
        $this->relations = collect($relations)->keyBy('name');

        $this->name = Str::snake((new ReflectionClass($this))->getShortName());
    }

    public function getFieldByPath(string $path): Field
    {
        $currentModel = $this;

        $relations = explode('.', $path);
        $fieldName = array_pop($relations);

        foreach ($relations as $relationName) {
            $currentModel = $currentModel->relations->get($relationName)->getModel();
        }

        // TODO: throw if it does not exist
        return $currentModel->fields->get($fieldName);
    }
}
