<?php

namespace App\Support\Meta\Types;

/** @extends Type<float> */
class Number extends Type
{
    function fromString(string $value): ?float
    {
        if ($value === '' && !$this->isNullable) {
            throw new TypeValidationException('Value is empty.');
        }

        if ($value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TypeValidationException('Not a number.');
        }

        return (float) $value;
    }

    function fromDatabase(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TypeValidationException('Not a number.');
        }

        return (float) $value;
    }

    function toString(mixed $value): string
    {
        return $value === null ? '' : "$value";
    }
}
