<?php

namespace App\Support\Meta\Types;

use \App\Support\Meta\Types\Integer as IntegerType;
use \App\Support\Meta\Types\Boolean as BooleanType;

/** @template T */
abstract class Type
{
    protected bool $isNullable = false;

    /**
     * @return null|T
     * @throws TypeValidationException
     */
    abstract function fromString(string $value): mixed;

    /** @param null|T $value */
    abstract function toString(mixed $value): string;

    function validateString(string $value): bool
    {
        try {
            $this->fromString($value);
            return true;
        }
        catch (TypeValidationException) {
            return false;
        }
    }

    public function nullable(bool $isNullable = true): self {
        $this->isNullable = $isNullable;
        return $this;
    }

    public static function integer(): IntegerType
    {
        return new IntegerType();
    }

    public static function subunit(int $precision): Subunit
    {
        return new Subunit($precision);
    }

    public static function boolean(): BooleanType
    {
        return new BooleanType();
    }

    public static function number(): Number
    {
        return new Number();
    }

    public static function text(): Text
    {
        return new Text();
    }
}
