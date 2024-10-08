<?php

namespace App\Support\Meta;

use App\Support\Meta\Wheres\Where;
use Illuminate\Support\Collection;

readonly class Relation
{
    /** @var Collection<int, string> $dependencies */
     public Collection $dependencies;

    /** @param class-string<Model> $model */
    public function __construct(public string $name, private string $model, public Where $joinClause)
    {
        $this->dependencies = $this->joinClause->dependencies;
    }

    public function getModel(): Model
    {
        return new $this->model();
    }

    /** @param class-string<Model> $model */
    public static function make(string $name, string $model, Where $joinClause): static
    {
        return new static($name, $model, $joinClause);
    }
}
