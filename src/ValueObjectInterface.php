<?php

namespace Webeleven\EloquentValueObject;

use Illuminate\Contracts\Support\Arrayable;

interface ValueObjectInterface extends Arrayable
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

    /**
     * @param array $args
     * @return ValueObjectInterface
     */
    public static function make();
}