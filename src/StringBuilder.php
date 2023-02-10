<?php

namespace Alexconesap\Commons;

/**
 * StringBuilder inspired (in functionality) to the Java class.
 *
 * It helps to construct a String concatenating sub-strings dynamically.
 *
 * Helpful for constructing URLs with parameters, converting API results into strings as key=value pairs,
 * constructing complex strings based on conditions, and much more.
 *
 * Example:
 * <code>
 * public function getFormattedDeliveryInfoParameters()
 * {
 *      return (string)(new StringBuilder($this->business_name))
 *          ->addPair($this->address1, $this->address2)
 *          ->add($this->city)
 *          ->add($this->state)
 *          ->add($this->zip_code)
 *          ->addPair($this->latitude, $this->longitude, '/')
 *          ->add($this->phone)
 *          ->add($this->notes);
 * }
 * </code>
 *
 * @author Yakuma, 2020 <alexconesap@gmail.com>
 * @version 1.1
 */
class StringBuilder
{

    const DEFAULT_GLUE = ', ';

    private ?string $string = null;
    private ?string $glue;
    private bool $url_encode;

    /**
     * Constructs a StringBuilder object.
     *
     * Example:
     * <code>
     * $params = new StringBuilder('', '&', true);
     * $params->addKeyValue('auth-id', '1234');
     * $params->addKeyValue('auth-token', 'it is the auth');
     * $params->addKeyValue('match', '1');
     * $url = 'https://api.test/function?' . $params->toString();
     *
     * $url contains:
     * https://api.test/function?auth-id=1234&auth-token=it+is+the+auth&match=1
     * </code>
     *
     * When $initial_value is:
     * string > It just adds it as the initial string
     * array  > It concatenates its values to the string automatically
     * null   > The initial value is an empty string
     *
     * @param mixed|null $initial_value Optional. The starting data. Either an array or a string.
     * @param string|null $glue Optional. The default glue is ', '
     * @param bool $url_encode Optional (false) All values added later on will be encoded for being part of a URL call
     */
    public function __construct(array|string|null $initial_value = null, ?string $glue = null, bool $url_encode = false)
    {
        $this->glue = $glue ?? self::DEFAULT_GLUE;
        $this->url_encode = $url_encode;

        if (is_array($initial_value)) {
            $this->addArray($initial_value, '=', $glue, $url_encode);
            $this->string = $this->string ?? "";
        } else {
            $this->string = (string)$initial_value;
        }
    }

    /**
     * Adds all array key/value pairs to the current string.
     *
     * The $glue_pair is used to glue each array key with its corresponding value. Most typical is to set as '='.
     *
     * NOTE: It processes elements recursively in case $data contains an element of type array.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder('', ' | '))
     * ->addArray(['a' => 1, 'b' => 2, ['c' => 55]])  >>> a=1 | b=2 | c=55
     * </code>
     *
     * @param array $data The elements to process
     * @param string $glue_pair Optional String to use to glue the $data elements. An = is used by default
     * @param string|null $glue Optional Char used to glue the given $data to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function addArray(array $data, string $glue_pair = '=', ?string $glue = null, ?bool $url_encode = null): static
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->addArray($v, $glue_pair, $glue, $url_encode);
            } else {
                $this->addPair($k, $v, $glue_pair, $glue, $url_encode);
            }
        }
        return $this;
    }


    /**
     * Concatenates a pair of strings to the current string.
     *
     * If both elements of the given pair are not empty, they are glued themselves using the $glue_pair.
     * When one of the given strings is empty, the $glue_pair is ignored.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder())      >>> ''
     * ->addPair('Hello', 'Alex', '*') >>> 'Hello*Alex'
     * ->addPair('Bye', '', '*')       >>> 'Hello*Alex, Bye'
     * </code>
     *
     * <code>
     * $s = (new StringBuilder())           >>> ''
     * ->addPair('Alex', 'Conesa', ' ')     >>> 'Alex Conesa'
     * ->addPair('Street name', '', '*')    >>> 'Alex Conesa, Street name'
     * ->addPair('T3H4T1', 'Calgary', '-')  >>> 'Alex Conesa, Street name, T3H4T1-Calgary'
     * </code>
     *
     * <code>
     * $s = (new StringBuilder())           >>> ''
     * ->addPair('Alex', 'Conesa', ' ')     >>> 'Alex Conesa'
     * ->addPair('', 'Calgary', '-')        >>> 'Alex Conesa, Calgary'
     * </code>
     *
     * @param string|null $left String to add at the left of $glue_pair
     * @param string|null $right String to add at the left of $glue_pair
     * @param string $glue_pair String to use to glue both $new_l and $new_r when both are set
     * @param string|null $glue Optional Char used to glue the resulting pair to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function addPair(?string $left, ?string $right, string $glue_pair = ' ', ?string $glue = null, ?bool $url_encode = null): static
    {
        if (trim($left) == '' && trim($right) == '') {
            return $this;
        }

        $nl = ($url_encode ?? $this->url_encode) ? urlencode($left) : $left;
        $nr = ($url_encode ?? $this->url_encode) ? urlencode($right) : $right;

        if (trim($nl) != '' && trim($nr) != '') {
            return $this->add($nl . $glue_pair . $nr, $glue, $url_encode);
        } else {
            return $this->add($nl . $nr, $glue, $url_encode);
        }
    }

    /**
     * Concatenates the given $value to the current string.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder('', '&'))
     * ->add('')                >>> ''
     * ->add('Hey')             >>> 'Hey'
     * ->add('It is me')        >>> 'Hey, It is me'
     * ->add(null)              >>> 'Hey, It is me'
     * ->add('and you', '...')  >>> 'Hey, It is me...and you'
     * </code>
     *
     * @param string|null $value String to add
     * @param string|null $glue Optional Char used to glue the resulting pair to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function add(?string $value, ?string $glue = null, ?bool $url_encode = null): static
    {
        if (trim($value) == '') {
            return $this;
        }

        $g = (trim($this->string) == '') ? '' : ($glue ?? $this->glue);

        $ne = ($url_encode ?? $this->url_encode) ? urlencode($value) : $value;

        $this->string = ($this->string . $g . $ne);

        return $this;
    }

    /**
     * Adds all array keys (not values) to the current string
     *
     * NOTE: It processes elements recursively in case $data contains an element of type array.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder('', ' | '))
     * ->addArray(['a' => 1, 'b' => 2, ['c' => 55]])  >>> a | b | c
     * </code>
     *
     * @param array $data Key/Value elements to process
     * @param string|null $glue Optional Char used to glue the resulting pair to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function addArrayKeys(array $data, ?string $glue = null, ?bool $url_encode = null): static
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->addArrayKeys($v, $glue, $url_encode);
            } else {
                $this->add($k, $glue, $url_encode);
            }
        }
        return $this;
    }

    /**
     * Adds all array values (not keys) to the current string
     *
     * NOTE: It processes elements recursively in case $data contains an element of type array.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder('', ' | '))
     * ->addArrayValues(['a' => 1, 'b' => 2, ['c' => 55]])  >>> 1 | 2 | 55
     * </code>
     *
     * @param array $data The elements to process
     * @param string|null $glue Optional Char used to glue the resulting pair to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function addArrayValues(array $data, ?string $glue = null, ?bool $url_encode = null): static
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->addArrayValues($v, $glue, $url_encode);
            } else {
                $this->add($v, $glue, $url_encode);
            }
        }
        return $this;
    }

    /**
     * Concatenates a given $key and a $value as $key=$value to the current string.
     *
     * Example:
     * <code>
     * $s = (new StringBuilder('', '&'))
     * ->addKeyValue('', '', '...')          >>> ''
     * ->addKeyValue('a', '')                >>> 'a='
     * ->addKeyValue('m', 'It is me')        >>> 'a=&m=It is me'
     * ->addKeyValue(null)                   >>> 'a=&m=It is me'
     * ->addKeyValue('m2', 'It is me', true) >>> 'a=&m=It is me&m2=It+is+me'
     * </code>
     *
     * @param string $key Key name to add
     * @param string|null $value String to add
     * @param string|null $glue Optional Char used to glue the given key/value to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     */
    public function addKeyValue(string $key, ?string $value, ?string $glue = null, ?bool $url_encode = null): static
    {
        if (trim($value) == '' && trim($key) == '') {
            return $this;
        }
        if (trim($key) == '') {
            return $this;
        }

        $g = (trim($this->string) == '') ? '' : ($glue ?? $this->glue);

        $ne = ($url_encode ?? $this->url_encode) ? urlencode($value) : $value;

        $this->string = ($this->string . $g . "$key=$ne");

        return $this;
    }

    /**
     * Wrapper for addKeyValue() to conditionally add the given $key=$value
     *
     * @param bool $condition When true the key/value is added. When false nothing is done.
     * @param string $key Key name to add
     * @param string|null $value String to add
     * @param string|null $glue Optional Char used to glue the resulting pair to the existing string. When not set, the default glue is used
     * @param bool|null $url_encode Optional (null) Set either to true/false or keep it as null to use the default set when constructing the object
     * @return $this
     * @see addKeyValue()
     */
    public function addKeyValueWhen(bool $condition, string $key, ?string $value, ?string $glue = null, ?bool $url_encode = null): static
    {
        if (!$condition) {
            return $this;
        }
        return $this->addKeyValue($key, $value, $glue, $url_encode);
    }

    /**
     * If you want to avoid PHPs magic method __toString() use this method instead.
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->string;
    }

    /**
     * Returns current string
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}
