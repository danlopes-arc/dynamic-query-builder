<?php

namespace App\Support\Meta;

use Illuminate\Support\Collection;

readonly class Relation
{
    /** @var Collection<string, string> Format local_column => external_column */
    public Collection $onColumns;

    /**
     * @param class-string<Model> $model
     * @param array<string, string> $onColumns Format local_column => external_column
     */
    public function __construct(public string $name, private string $model, array $onColumns)
    {
        $this->onColumns = collect($onColumns);
    }

    public function getModel(): Model
    {
        return new $this->model();
    }

    /**
     * @param class-string<Model> $model
     * @param array<string, string> $onColumns Format local_column => external_column
     */
    public static function make(string $name, string $model, array $onColumns): static
    {
        return new static($name, $model, $onColumns);
    }
}
