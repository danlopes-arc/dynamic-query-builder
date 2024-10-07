<?php

namespace App\Support\Meta\Types;

/** @extends Type<float> */
class Subunit extends Type
{

    private int $factor;

    public function __construct(int $precision)
    {
        $this->factor = pow(10, $precision);
    }

    function fromString(string $value): ?int
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

        return (int)($value * $this->factor);
    }

    function toString(mixed $value): string
    {
        $converted = $value / $this->factor;

        return $value === null ? '' : "$converted";
    }
}
