<?php

/**
 * Generic classes
 *
 * PHP version 7.0
 *
 * @author   Yakuma <alexconesap@gmail.com>
 */

namespace Alexconesap\Commons\Models;

use Alexconesap\Commons\Contracts\BeanAccessible;
use Alexconesap\Commons\Contracts\DataMapeable;
use ArrayAccess;
use JsonSerializable;
use RuntimeException;

/**
 * Base class that allows its descendants to "auto-magically" use properties
 * instead of requiring to call to their supposed "setters" and "getters".
 *
 * Example:
 * <code>
 * class Test extends BasicBean {
 *    protected $available_attributes = [
 *        'name',
 *        'city',
 *    ];
 * }
 * </code>
 *
 * Allow us to use the class Test as follows:
 * <code>
 * $t = new Test();
 * $t->name = 'Alex';
 * $t->city = 'Rome';
 *
 * $t2 = new Test(['name' => 'Alex', 'city' => 'Barcelona']);
 * </code>
 *
 * <code>$t->name = 'Alex';</code> will internally call setName('Alex')
 *
 * PHP version 7.0
 */
class BasicBean implements ArrayAccess, JsonSerializable, DataMapeable, BeanAccessible
{

    /**
     * May be overwritten by descendants to handle different data structures
     *
     * Will be serialized as 'version' by default or a getVersionFieldname()
     * method may be overwritten to replace that name.
     *
     * May be overridden in the constructor
     *
     * @var string
     */
    protected $version = '1';

    /**
     * All of the available attributes defined to be "assignable"
     *
     * @var array
     */
    protected $available_attributes = [];

    /**
     * Set to false to allow to set any properties, regardless of the ones
     * defined at $available_attributes
     *
     * Set to true (default) to raise an exception when attempting to set a
     * property that it is not listed at $available_attributes
     *
     * @var bool
     */
    protected $strict_assignment = false;

    /**
     * Attributes set on the fluent instance.
     * @var array
     */
    protected $attributes = [];

    /**
     * Define default values for the attributes when not during object creation
     * @var array
     */
    protected array $attributes_defaults = [];

    /**
     * Create a new fluent instance.
     *
     * @param array|object $attributes Example ['attribute' => value, ...]
     * @param bool $include_version Optional (true) Overrides
     * @return void
     */
    public function __construct($attributes = [], ?bool $include_version = true)
    {
        if (count($this->attributes_defaults) > 0) {
            if (count($this->available_attributes) > 0) {
                foreach ($this->available_attributes as $key) {
                    $this->attributes[$key] = $this->attributes_defaults[$key] ?? null;
                }
            } else {
                foreach ($this->attributes_defaults as $key => $value) {
                    $this->attributes[$key] = $value ?? null;
                }
            }
        } else {
            foreach ($this->available_attributes as $key) {
                $this->attributes[$key] = null;
            }
        }

        $check_properties = $this->strict_assignment && count($this->available_attributes) > 0;

        foreach ($attributes as $key => $value) {
            if ($check_properties && !in_array($key, $this->available_attributes)) {
                $clazz = get_class($this);
                throw new RuntimeException("Invalid key '$key'. Not defined at \$available_attributes when \$strict_assignment is enabled. ($clazz)");
            }
            $this->attributes[$key] = $value ?? $this->attributes_defaults[$key] ?? null;
        }

        // If not already set above, include the version attribute
        if ($include_version) {
            if ($this->getVersionFieldname() && !isset($this->attributes[$this->getVersionFieldname()])) {
                $this->attributes[$this->getVersionFieldname()] = $this->version;
            }
        }
    }

    /**
     * Wrapper for creating a new instance
     *
     * @param array|object $attributes
     * @param bool|null $include_version
     * @return static
     */
    public static function make(array|object $attributes = [], ?bool $include_version = true): static
    {
        return new static($attributes, $include_version);
    }

    /**
     * "Overridable" method to set the name of the version field to be serialized
     * as part of the $attributes
     *
     * Set to null to do not serialize the version attribute
     *
     * Overwrite this method to return false to avoid the version to be exported as part of the JSON
     *
     * By default, it returns 'version'
     *
     * Note that the constructor allows to override the usage of this attribute.
     *
     * @return string|bool
     */
    public function getVersionFieldname()
    {
        return 'version';
    }

    /**
     * Get an attribute from the fluent instance.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return value($default);
    }

    /**
     * Get the attributes from the fluent instance.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON "serializable".
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the fluent instance to JSON.
     *
     * @param int $options JSON_PRETTY_PRINT
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     *
     * @param string $method
     * @param array $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Converts all the attributes to a JSON string
     * @return string
     */
    public function __toString()
    {
        return ($result = $this->toJson()) == false ? (__CLASS__ . ' Error parsing to json string') : $result;
    }

    /**
     * Returns true when all internal attributes are null
     *
     * It do not process the 'version' attribute
     *
     * @return bool
     */
    public function hasEmptyAttributes()
    {
        $v = $this->getVersionFieldname();
        foreach ($this->attributes as $key => $value) {
            if ($key != $v && !is_null($value)) {
                return false;
            }
        }
        return true;
    }

}
