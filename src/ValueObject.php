<?php

namespace Webeleven\EloquentValueObject;

abstract class ValueObject implements ValueObjectInterface
{

    public static function make($value = null)
    {
        return new static($value);
    }

}
