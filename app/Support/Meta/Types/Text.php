<?php

namespace App\Support\Meta\Types;

/** @extends Type<string> */
class Text extends Type
{
    function fromString(string $value): ?string
    {
        if ($value === '' && !$this->isNullable) {
            throw new TypeValidationException('Value is empty.');
        }

        return $value === '' ? null : $value;
    }

    function toString(mixed $value): string
    {
        return $value === null ? '' : "$value";
    }
}
