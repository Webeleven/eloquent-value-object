<?php

namespace Webeleven\EloquentValueObject;

use Illuminate\Contracts\Support\Arrayable;

interface ValueObject extends Arrayable
{
    /**
     * @param $value
     */
    public function __construct($value);

    /**
     * @return mixed
     */
    public function toScalar();

    /**
     * @return string
     */
    public function __toString();
}