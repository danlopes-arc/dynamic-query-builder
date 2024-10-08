<?php

namespace App\Support\Meta\Wheres;

use App\Support\Meta\Fields\Field;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

readonly class WhereGroup extends Where
{

    /** @var Collection<int, Where> $clauses */
    public Collection $clauses;

    /**
     * @param Where[] $clauses
     * @param 'and'|'or' $boolean
     */
    public function __construct(array $clauses, string $boolean)
    {
        $this->clauses = collect($clauses);

        parent::__construct($this->clauses->flatMap(fn(Where $joinClause) => $joinClause->dependencies), $boolean);
    }

    /** @param Collection<string, Field> $fields */
    function apply(Builder $query, Collection $fields, string $prefix): void
    {
        $query->where(function (Builder $query) use ($prefix, $fields) {
            foreach ($this->clauses->all() as $clause) {
                $clause->apply($query, $fields, $prefix);
            }
        }, boolean: $this->boolean);
    }
}
