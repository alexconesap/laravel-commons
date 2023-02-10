<?php

namespace Alexconesap\Commons;

use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;

/**
 * Simple ArrayList implementation to emulate the Java class with the same name. Stores elements as key/values.
 *
 * It is helpful for being used as an extended 'array bag' with many wrappers defined for easily accessing its elements.
 *
 * It is also inspired in the SYMFONY "ParametersBag" class, and it picks part of its method names to make it compatible
 * or easily portable for using it when coding in the LARAVEL framework.
 *
 * Example:
 * <code>
 * $example_array = ['one' => '123', 'two' => 'Alex', 'three' => false, 'four' => '2_$ax1'];
 *
 * $alist = ArrayList::valueOf($example_array);
 *
 * $alist->has('one')            true
 * $alist->has('test')           false
 * $alist->has(null)             false
 *
 * $alist->filled('one')         true
 * $alist->set('one', null)
 * $alist->has('one')            true
 * $alist->filled('one')         false
 * $alist->count()               4
 *
 * $alist->remove('one')
 * $alist->has('one')            false
 * $alist->count()               3
 *
 * $alist->replaceAll($example_array)
 * $alist->count()               4
 * $alist->add('five', 'Hello')
 * $alist->add('six',  true)
 * $alist->add('seven', 33)
 * $alist->count()               7
 *
 * $alist->get('four')           '2_$ax1'
 * $alist->input('four')         '2_$ax1'
 * $alist->post('four')          '2_$ax1'
 * $alist->getAlpha('four')      'ax'
 * $alist->getAlnum('four')      '2ax1'
 * $alist->getDigits('four')     '21'
 * $alist->getInt('four')        2
 *
 * $alist->getInt('three')       0
 * $alist->getBoolean('three')   false
 *
 * $alist->clear()
 * $alist->count()               0
 * $alist->isEmpty()             true
 * * </code>
 */
class ArrayList implements IteratorAggregate, Countable
{

    /**
     * Internal values storage
     */
    protected $elements;

    /**
     * Static factory typed constructor
     *
     * @param array $elements An array of elements
     * @return ArrayList
     */
    public static function valueOf(array $elements = []): ArrayList
    {
        return new static($elements);
    }

    /**
     * Default constructor
     *
     * @param array $elements An array of elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * Returns all the elements
     *
     * @return array|null
     */
    public function all(): ?array
    {
        return $this->elements;
    }

    /**
     * Returns all the element keys
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * Returns the elements keys that matches with $key
     *
     * @param string $key The key
     * @return array
     */
    public function keysOf(string $key): array
    {
        return array_keys($this->elements, $key);
    }

    /**
     * Replaces all current elements by a new set of elements.
     *
     * @param array $elements An array of elements
     * @return $this
     */
    public function replaceAll(array $elements = []): ArrayList
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * Replaces all current elements by a new set of elements.
     * It is a wrapper for replaceAll().
     *
     * @param array $elements An array of elements
     * @return $this
     */
    public function assign(array $elements = []): ArrayList
    {
        return $this->replaceAll($elements);
    }

    /**
     * Returns an element using its key name.
     *
     * @param string $key The key
     * @param mixed $default The default value if the element key does not exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->elements) ? $this->elements[$key] : $default;
    }

    /**
     * Returns a element using its key name.
     * It is a wrapper for method get() to make ArrayList compatible with Symfony Request class.
     *
     * @param string $key The key
     * @param mixed $default The default value if the element key does not exist
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * Returns the value of one parameter named $key
     * If the key do not exist as parameter it returns the default value.
     * If the key exists, but it is not set, it returns null.
     * Otherwise, it returns the parameter value.
     *
     * @param string $key The key
     * @param mixed $default The default value if the element key does not exist
     * @return mixed
     */
    public function inputDefault(string $key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        if ($this->filled($key)) {
            return $this->input($key);
        } else {
            return null;
        }
    }

    /**
     * Mimics the InteractsWithIO Laravel trait method (used by artisan commands to process command line options set)
     * @param string $option_name_key
     * @return bool
     */
    public function option(string $option_name_key): bool
    {
        if (!$this->has($option_name_key)) return false;
        return $this->getBoolean($option_name_key, false);
    }

    /**
     * Returns an element using its key name. Used for processing Request objects.
     * It is a wrapper for method get() to make ArrayList compatible with Symfony Request class.
     *
     * @param string $key The key
     * @param mixed $default The default value if the element key does not exist
     *
     * @return mixed
     */
    public function post(string $key, $default = null): mixed
    {
        return $this->get($key, $default);
    }

    /**
     * Returns an iterator for the array elements.
     *
     * @return ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Returns true if the key exists regardless of its assignment (value set or not)
     *
     * @param string|null $key The key
     * @return bool
     */
    public function has(?string $key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    /**
     * Wrapper for has(). Returns true if the element exists, false otherwise
     *
     * @param string|null $key The key
     * @return bool
     */
    public function exists(?string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Sets (update or add) an element by key.
     *
     * @param string $key The key
     * @param mixed $value The value
     * @return $this
     */
    public function set(string $key, $value): ArrayList
    {
        $this->elements[$key] = $value;
        return $this;
    }

    /**
     * Add an element. It is a wrapper for @method set()
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addElement(string $key, $value): ArrayList
    {
        return $this->set($key, $value);
    }

    /**
     * Add elements from an array. If some element exists the existing one is replaced.
     *
     * To add a specific key use either method addElement() or method set() instead.
     *
     * @param array|ArrayList|null $elements A list of elements to be added/replaced
     * @return $this
     */
    public function add(array|ArrayList|null $elements = []): ArrayList
    {
        if (is_null($elements)) return $this;

        if ($elements instanceof static) $this->elements = array_replace($this->elements, $elements->all());
        else $this->elements = array_replace($this->elements, $elements);

        return $this;
    }

    /**
     * Returns true if the element is defined and have a value assigned, false otherwise.
     *
     * @param string $key The key
     * @return bool
     */
    public function filled(string $key): bool
    {
        if (!$this->has($key) || is_null($this->get($key))) {
            return false;
        }

        // Special cases
        $type = \gettype($this->get($key));
        $v = $this->get($key);
        return match ($type) {
            "string" => $v !== '',
            "integer", "double" => trim($v) != '',
            default => true,
        };
    }

    /**
     * Removes a element.
     *
     * @param string $key The key
     */
    public function remove(string $key)
    {
        unset($this->elements[$key]);
    }

    /**
     * Removes all elements.
     */
    public function clear()
    {
        $this->elements = [];
    }

    /**
     * Returns true in case its internal bag of elements is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Returns the alphabetic characters of the element value.
     *
     * @param string $key The element key
     * @param mixed $default The default value if the element key does not exist
     *
     * @return mixed
     */
    public function getAlpha(string $key, $default = '')
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the element value.
     *
     * @param string $key The element key
     * @param mixed $default The default value if the element key does not exist
     *
     * @return mixed
     */
    public function getAlnum(string $key, $default = '')
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the digits of the element value.
     *
     * @param string $key The element key
     * @param mixed $default The default value if the element key does not exist
     * @return mixed
     */
    public function getDigits(string $key, $default = '')
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(
            array('-', '+'),
            '',
            $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT)
        );
    }

    /**
     * Returns the element value converted to integer.
     *
     * @param string $key The element key
     * @param int $default The default value if the element key does not exist
     * @return int The filtered value
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int)$this->get($key, $default);
    }

    /**
     * Returns the element value as boolean using PHP filter FILTER_VALIDATE_BOOLEAN
     *
     * @param string $key The element key
     * @param bool $default The default value if the given key does not exist
     * @return bool
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Filter key.
     *
     * @param string $key Key
     * @param mixed $default Optional (null)
     * @param int $filter Optional (FILTER_DEFAULT) FILTER_* constant
     * @param mixed $options Optional ([]) Filter options ['flags']
     *
     * @return mixed
     * @see http://php.net/manual/en/function.filter-var.php
     *
     */
    public function filter(string $key, $default = null, int $filter = FILTER_DEFAULT, array $options = [])
    {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!\is_array($options) && $options) {
            $options = array('flags' => $options);
        }

        // Add a convenience check for arrays.
        if (\is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Returns the number of elements.
     *
     * @return int The number of elements
     */
    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * Returns the number of elements.
     *
     * @return int The number of elements
     */
    public function size(): int
    {
        return \count($this->elements);
    }

    /**
     * Return a string representation of current object
     * @return string
     */
    public function __toString()
    {
        try {
            $r = "";
            foreach ($this->elements as $k => $v) {
                $r .= ($r == "" ? "$k=$v" : ", $k=$v");
            }
            return $r;
        } catch (Exception $ex) {
            return print_r($this->elements, true);
        }
    }

}
