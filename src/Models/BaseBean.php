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

/**
 * Base class that facilitates its descendants to effortlessly utilize 'properties' rather than having to
 * explicitly call their respective 'setters' and 'getters.'
 *
 * Example:
 * <code>
 * class Test extends BaseClass {
 *    private $name;
 *
 *    public function setName($value) {
 *        $this->name = $value;
 *    }
 *    public function getName() {
 *        return $this->name;
 *    }
 * }
 * </code>
 *
 * Allows to use the class Test as follows:
 * <code>
 * $t = new Test();
 * $t->name = 'Alex';
 * var_dump($t->name)  will output  Alex
 * </code>
 *
 * Explanation:
 * <code>$t->name = 'Alex';</code>
 * will magically call the method
 * <code>setName('Alex')</code>
 */
class BaseBean implements DataMapeable, BeanAccessible
{

    /**
     * "Auto-magically" calls a getter named 'get$name' or 'is$name' or 'has$name'
     *
     * @param string $name Attribute name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (method_exists($this, ($method = 'get' . $name)) || method_exists($this, ($method = 'is' . $name)) || method_exists($this, ($method = 'has' . $name))) {
            return $this->$method();
        }
        return null;
    }

    /**
     * "Auto-magically" calls a getter named 'set$name'
     *
     * @param string $name Attribute name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        if (method_exists($this, ($method = 'set' . $name))) {
            $this->$method($value);
        }
    }

    /**
     * "Auto-magically" calls a getter named 'isset$name'
     *
     * @param string $name Attribute name
     * @return mixed
     */
    public function __isset(string $name)
    {
        if (method_exists($this, ($method = 'isset' . $name))) {
            return $this->$method();
        }
        return false;
    }

    /**
     * "Auto-magically" calls a getter named 'unset$name'
     *
     * @param string $name Attribute name
     * @return void
     */
    public function __unset(string $name)
    {
        if (method_exists($this, ($method = 'unset' . $name))) {
            $this->$method();
        }
    }

}
