<?php

namespace App\Support\Meta\Types;

/** @extends Type<int> */
class Integer extends Type
{
    function fromString(string $value): ?int
    {
        if ($value === '' && !$this->isNullable) {
            throw new TypeValidationException('Value is empty.');
        }

        if ($value === '') {
            return null;
        }

        if (!is_numeric($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new TypeValidationException('Not an integer.');
        }

        return (int) $value;
    }

    function fromDatabase(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new TypeValidationException('Not an integer.');
        }

        return (int) $value;
    }

    function toString(mixed $value): string
    {
        return $value === null ? '' : "$value";
    }
}
