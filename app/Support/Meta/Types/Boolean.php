<?php

namespace App\Support\Meta\Types;

/** @extends Type<bool> */
class Boolean extends Type
{
    function fromString(string $value): ?bool
    {
        if ($value === '' && !$this->isNullable) {
            throw new TypeValidationException('Value is empty.');
        }

        if (!in_array($value, ['Y', 'N'])) {
            throw new TypeValidationException('Value is not Y nor N.');
        }

        return $value === '' ? null : $value === 'Y';
    }

    function fromDatabase(mixed $value): ?bool
    {
        if (!in_array($value, [false, 0, '0', true, 1, '1'])) {
            throw new TypeValidationException('Not a boolean.');
        }

        return $value === null ? null : in_array($value, [true, 1, '1']);
    }

    function toString(mixed $value): string
    {
        return $value === null ? '' : (in_array($value, [true, 1]) ? 'Y' : 'N');
    }
}
