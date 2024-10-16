<?php

namespace App\Support\Reports;

use App\Support\Meta\Fields\Field;
use App\Support\Meta\Model;
use App\Support\Meta\Types\Type;
use Illuminate\Support\Collection;

readonly class Header
{
    public string $path;

    public function __construct(public string $key, public string $label, ?string $path = null)
    {
        $this->path = $path ?? $this->key;
    }

    /** @return Collection<int, string> */
    public function getRelations(): Collection
    {
        return collect(array_slice(explode(".", $this->path), 0, -1));
    }

    public function getFieldName(): string
    {
        $segments = explode(".", $this->path);
        return array_pop($segments);
    }

    public function getField(Model $model): Field
    {
        $currentModel = $model;

        foreach ($this->getRelations() as $relation) {
            $currentModel = $currentModel->relations->get($relation)->getModel();
        }

        return $currentModel->fields->get($this->getFieldName());
    }

    /**
     * @param Collection<string, Field> $fields
     * @return Collection<int, string>
     */
    public function getDependentFields(Model $model, Collection $fields, string $prefix): Collection
    {
        return Field::getDependentFields($this->path, $model, $fields, $prefix);
    }

    public function getType(Model $model): Type
    {
        return $this->getField($model)->type;
    }
}
