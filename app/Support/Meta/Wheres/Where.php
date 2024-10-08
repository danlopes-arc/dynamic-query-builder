<?php

namespace App\Support\Meta\Wheres;

use App\Support\Meta\Fields\Field;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

abstract readonly class Where
{
    /** @var Collection<int, string> $dependencies */
    public Collection $dependencies;

    /**
     * @param string[] $dependencies
     * @param 'and'|'or' $boolean
     */
    public function __construct(array $dependencies, protected string $boolean)
    {
        $this->dependencies = collect($dependencies);
    }

    /** @param Collection<string, Field> $fields */
    abstract function apply(Builder $query, Collection $fields, string $prefix): void;

    /* ************************************************************************
     * Static Methods
     * ************************************************************************/

    /** @param 'and'|'or' $boolean */
    public static function make(string $first, string $operator, string $second, string $boolean = 'and'): WhereCondition
    {
        return new WhereCondition($first, $operator, $second, $boolean);
    }

    public static function or(string $first, string $operator, string $second): WhereCondition
    {
        return new WhereCondition($first, $operator, $second, 'or');
    }

    public static function equals(string $first, string $second): WhereCondition
    {
        return new WhereCondition($first, '=', $second, 'and');
    }

    /**
     * @param Where[] $clauses
     * @param 'and'|'or' $boolean
     */
    public static function group(array $clauses, string $boolean): WhereGroup
    {
        return new WhereGroup($clauses, $boolean);
    }

    /** @param Where[] $clauses */
    public static function orGroup(array $clauses): WhereGroup
    {
        return new WhereGroup($clauses, 'or');
    }
}
