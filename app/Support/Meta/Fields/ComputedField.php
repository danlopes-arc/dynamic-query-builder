<?php

namespace App\Support\Meta\Fields;

use App\Support\Meta\Types\Type;
use Closure;
use Illuminate\Support\Collection;

readonly class ComputedField extends Field
{
    /** @var Closure(string ...$args): string $get */
    private Closure $get;

    /** @param callable(string ...$args): string $get */
    public function __construct(string $name, Type $type, array $dependencies, callable $get)
    {
        parent::__construct($name, $type, $dependencies);
        $this->get = $get(...);
    }

    /** @param Collection<string, Field> $fields */
    function toSql(Collection $fields, string $prefix): string
    {
        $args = $this->dependencies->map(function (string $path) use ($fields, $prefix) {
            $relations = array_slice(explode('.', $path), 0, -1);
            return $fields->get("$prefix.$path")->toSql($fields, implode('.', [$prefix, ...$relations]));
        });

        $sql = ($this->get)(...$args);

        // TODO: if verbose: return "(/* $prefix.$this->name */ $sql)";
        return "($sql)";
    }
}
