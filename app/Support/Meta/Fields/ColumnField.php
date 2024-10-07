<?php

namespace App\Support\Meta\Fields;

use App\Support\Meta\Types\Type;
use Illuminate\Support\Collection;

readonly class ColumnField extends Field
{
    public function __construct(string $name, Type $type)
    {
        parent::__construct($name, $type, []);
    }

    /** @param Collection<string, Field> $fields */
    function toSql(Collection $fields, string $prefix): string
    {
        return "$prefix.$this->name";
    }
}
