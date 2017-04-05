<?php

namespace Webeleven\EloquentValueObject;

trait CastsValueObjects
{

    /**
     * @var array
     */
    protected $cachedObjects = [];

    /**
     * @var array
     */
    protected $oldValueObjectsAttributes = [];

    public static function bootCastsValueObjects()
    {
        static::saving(function($model) {

            $objects = $model->getValueObjects();

            if (count($objects)) {
                foreach ($objects as $key => $value) {
                    if (isset($model->{$key})) {
                        $model->attributes[$key] = $model->{$key}->__tostring();
                    }
                }
            }

        });
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $this->isValueObject($key)) {
            return parent::getAttribute($key);
        }

        if (! $this->isValueObjectCached($key)) {

            // Allow other mutators and such to do their work first.
            $value = parent::getAttribute($key);

            // Don't cast empty $value.
            if ($value === null || $value === '') {
                return null;
            }

            // Cache the instantiated value for future access.
            // This allows tests such as ($model->casted === $model->casted) to be true.
            $this->cacheValueObject($key, $this->createValueObject($key, $value));
        }

        return $this->getCachedValueObject($key);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (! $this->isValueObject($key) || empty($value)) {
            return parent::setAttribute($key, $value);
        }

        $old = $this->getAttribute($key);

        $this->invalidateValueObjectCache($key);

        if (! $value instanceof ValueObjectInterface) {
            $value = $this->createValueObject($key, $value);
        }

        // We'll need to cast value object to a scalar
        // and then let it be set into Eloquent's attributes array.
        parent::setAttribute($key, $scalar = $value->toScalar());

        $this->storeOldValueObjectAttribute($key, $old);

        event('easymutators.new-attribute-set', [$key, $value, $old, $this]);

        // If the value wasn't modified during the set process
        // store the original ValueObject in our cache.
        if ($this->attributes[$key] === $scalar) {
            return $this->cacheValueObject($key, $value);
        }

        // Otherwise, we'll invalidate the cache for this key and defer
        // to the get action for re-instantiating the ValueObject.
        return $this->invalidateValueObjectCache($key);
    }

    /**
     * @return array
     */
    public function getValueObjects()
    {
        return isset($this->objects) && is_array($this->objects) ? $this->objects : [];
    }

    /**
     * @param $key
     * @return bool
     */
    protected function isValueObject($key)
    {
        $objects = $this->getValueObjects();

        return isset($objects[$key]);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return mixed
     */
    protected function createValueObject($key, $value)
    {
        $class = $this->getValueObjects()[$key];

        return new $class($value);
    }

    /**
     * @param string $key
     * @param ValueObjectInterface $object
     * @return $this
     */
    private function cacheValueObject($key, $object)
    {
        $this->cachedObjects[$key] = $object;
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    private function invalidateValueObjectCache($key)
    {
        unset($this->cachedObjects[$key]);
        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isValueObjectCached($key)
    {
        return isset($this->cachedObjects[$key]);
    }

    /**
     * @param string $key
     *
     * @return ValueObjectInterface
     */
    private function getCachedValueObject($key)
    {
        return $this->cachedObjects[$key];
    }

    public function getOldValueObjectsAttributes()
    {
        return $this->oldValueObjectsAttributes;
    }

    public function storeOldValueObjectAttribute($key, $value)
    {
        if (! isset($this->oldValueObjectsAttributes[$key])) {
            $this->oldValueObjectsAttributes[$key] = [];
        }

        $this->oldValueObjectsAttributes[$key][] = $value;
    }

}