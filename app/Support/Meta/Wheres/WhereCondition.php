<?php

namespace App\Support\Meta\Wheres;

use App\Support\Meta\Fields\Field;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class WhereCondition extends Where
{
    /** @param 'and'|'or' $boolean */
    public function __construct(
        private string $first,
        private string $operator,
        private string $second,
        string $boolean,
    )
    {
        parent::__construct([$this->first, $this->second], $boolean);
    }

    /** @param Collection<string, Field> $fields */
    function apply(Builder $query, Collection $fields, string $prefix): void
    {
        $first = static::resolveDependency($this->first, $fields, $prefix);
        $second = static::resolveDependency($this->second, $fields, $prefix);

        $query->where(DB::raw($first), $this->operator, DB::raw($second), $this->boolean);
    }

    /** @param Collection<string, Field> $fields */
    private static function resolveDependency(string $dependency, Collection $fields, string $prefix)
    {
        $relations = array_slice(explode('.', $dependency), 0, -1);
        return $fields->get("$prefix.$dependency")->toSql($fields, implode('.', [$prefix, ...$relations]));
    }
}
