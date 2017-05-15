<?php
namespace TimMcLeod\LaravelCoreLib\Models\Traits;

/**
 * The purpose of this trait is to allow us to keep track of changes
 * that are made to attribute values within our eloquent models,
 * so we can access those changes AFTER the model is saved.
 */
trait ChangeTrackable
{
    /** @var array */
    protected $trackedChanges = [];

    /**
     * Boot trait.
     */
    public static function bootChangeTrackable()
    {
        static::saving(function ($model)
        {
            $model->trackChanges();
        });
    }

    /**
     * Track current state of the model.
     */
    public function trackChanges()
    {
        if ($this->isDirty())
        {
            $newValues = $this->getDirty();

            foreach ($newValues as $key => $value)
            {
                $new = $this->getAttributeValue($key);
                $old = $this->getOriginalAttributeValue($key);

                if ($new !== $old) $this->trackChange($key, $old, $new);
            }
        }
    }

    /**
     * @param string $key
     * @param mixed  $old
     * @param mixed  $new
     */
    protected function trackChange($key, $old, $new)
    {
        $hasAttribute = array_has($this->trackedChanges, "$key.old");

        // Only set old attribute if it doesn't already exist.
        if (!$hasAttribute) array_set($this->trackedChanges, "$key.old", $old);

        array_set($this->trackedChanges, "$key.new", $new);

        ksort($this->trackedChanges);
    }

    /**
     * Returns an array of the attributes that have changed. If a property called
     * $trackable exists on the model, then the $trackable array will be used
     * to limit or filter the attributes that are returned in the result.
     *
     * If the $trackable property doesn't exist on the model or evaluates as empty,
     * then all of the tracked changes will be returned without being filtered.
     *
     * @return array
     */
    public function getTrackedChangesArray()
    {
        $attributes = property_exists(static::class, 'trackable') ? $this->trackable : [];

        return $this->getTrackedChangesArrayFor($attributes);
    }

    /**
     * Returns an array of the attributes that have changed. If the $attributes
     * parameter evaluates as empty, then all tracked changes are returned.
     *
     * @param array $attributes
     * @return array
     */
    public function getTrackedChangesArrayFor($attributes = [])
    {
        return empty($attributes) ? $this->trackedChanges : array_only($this->trackedChanges, $attributes);
    }

    /**
     * Returns an array of the attributes that have changed. If the $trackable
     * property exists on the model, it's ignored, and all tracked changes
     * are always returned, regardless of whether $trackable exists.
     *
     * @return array
     */
    public function getTrackedChangesArrayForAll()
    {
        return $this->trackedChanges;
    }

    /**
     * Returns true if any of the $trackable attributes have changed. If the
     * $trackable property evaluates as empty, this will return true if
     * any of the attributes' values have changed on the model.
     *
     * @return bool
     */
    public function hasTrackedChanges()
    {
        return !empty($this->getTrackedChangesArray());
    }

    /**
     * Returns true if any of the model's attributes have changed, regardless
     * of whether or not the $trackable property is defined on the model.
     *
     * @return bool
     */
    public function hasAnyTrackedChanges()
    {
        return !empty($this->trackedChanges);
    }

    /**
     * Returns true if any of the given attributes have changed on the model,
     * regardless of whether or not the $trackable property is defined on
     * the model.
     *
     * @param array $attributes
     * @return bool
     */
    public function hasAnyTrackedChangesFor($attributes = [])
    {
        return !empty(array_only($this->trackedChanges, $attributes));
    }

    /**
     * Returns a string representation of the attributes that have changed. If a
     * property called $trackable exists on the model, then that $trackable
     * array is used to filter the attributes that should be serialized.
     *
     * If the $trackable property doesn't exist on the model or evaluates as empty,
     * then all of the tracked changes will be serialized without being filtered.
     *
     * @param string $format
     * @param string $delimiter
     * @param string $emptyOld
     * @param string $emptyNew
     * @return string
     */
    public function getTrackedChanges(
        $format = '{attribute}: {old} > {new}', $delimiter = ' | ', $emptyOld = '', $emptyNew = ''
    ) {
        $attributes = property_exists(static::class, 'trackable') ? $this->trackable : [];

        return $this->getTrackedChangesFor($attributes, $format, $delimiter, $emptyOld, $emptyNew);
    }

    /**
     * Returns a string representation of the attributes that have changed. If the
     * $attributes parameter evaluates as empty, then all tracked changes will
     * be serialized into a string without being filtered.
     *
     * @param array  $attributes
     * @param string $format
     * @param string $delimiter
     * @param string $emptyOld
     * @param string $emptyNew
     * @return string
     */
    public function getTrackedChangesFor(
        $attributes = [], $format = '{attribute}: {old} > {new}', $delimiter = ' | ', $emptyOld = '', $emptyNew = ''
    ) {
        $changes = empty($attributes) ? $this->trackedChanges : array_only($this->trackedChanges, $attributes);

        return $this->getChangesString($changes, $format, $delimiter, $emptyOld, $emptyNew);
    }

    /**
     * Returns a string representation of ALL of the attributes that have changed
     * on the model, even if the $trackable property does exist on the model.
     *
     * @param string $format
     * @param string $delimiter
     * @param string $emptyOld
     * @param string $emptyNew
     * @return string
     */
    public function getTrackedChangesForAll(
        $format = '{attribute}: {old} > {new}', $delimiter = ' | ', $emptyOld = '', $emptyNew = ''
    ) {
        return $this->getChangesString($this->trackedChanges, $format, $delimiter, $emptyOld, $emptyNew);
    }

    /**
     * Serializes the changes into a string using the given $format.
     *
     * @param array  $changes
     * @param string $format
     * @param string $delimiter
     * @param string $emptyOld
     * @param string $emptyNew
     * @return string
     */
    protected function getChangesString(
        $changes, $format = '{attribute}: {old} > {new}', $delimiter = ' | ', $emptyOld = '', $emptyNew = ''
    ) {
        $str = '';
        $i = 0;
        $count = count($changes);

        foreach ($changes as $key => $value)
        {
            $i++;

            $old = ($value['old'] === '' || $value['old'] === null) ? $emptyOld : $value['old'];
            $new = ($value['new'] === '' || $value['new'] === null) ? $emptyNew : $value['new'];

            $str .= str_replace(['{attribute}', '{label}', '{old}', '{new}'],
                [$key, title_case(str_replace('_', ' ', $key)), $old, $new], $format);

            if ($i < $count) $str .= $delimiter;
        }

        return $str;
    }

    /**
     * Get the value of the original attribute (not a relationship). This method
     * mimics the model's getAttributeValue() method except this one is used
     * to return the original value in its mutated or casted state.
     *
     * @param  string $key
     * @return mixed
     */
    public function getOriginalAttributeValue($key)
    {
        $value = $this->getOriginal($key);

        if ($this->hasGetMutator($key)) return $this->mutateAttribute($key, $value);

        if ($this->hasCast($key)) return $this->castAttribute($key, $value);

        if (in_array($key, $this->getDates()) && !is_null($value)) return $this->asDateTime($value);

        return $value;
    }
}