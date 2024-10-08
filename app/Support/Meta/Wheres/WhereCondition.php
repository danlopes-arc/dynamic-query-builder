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
        $firstRelations = array_slice(explode('.', $this->first), 0, -1);
        $first = $fields->get("$prefix.$this->first")->toSql($fields, implode('.', [$prefix, ...$firstRelations]));

        $secondRelations = array_slice(explode('.', $this->second), 0, -1);
        $second = $fields->get("$prefix.$this->second")->toSql($fields, implode('.', [$prefix, ...$secondRelations]));

        $query->where(DB::raw($first), $this->operator, DB::raw($second), $this->boolean);
    }
}
