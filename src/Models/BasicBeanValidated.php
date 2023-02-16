<?php

namespace Alexconesap\Commons\Models;

use Alexconesap\Commons\Exceptions\InvalidObjectAttributesException;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Maps JSON API keys into local classes with well known/described attributes assigning default values when
 * required. Rather than using a JSON object in our applications we can use a PHP formatted Bean.
 * <br><br>
 * This class allows to strictly map the given JSON to the object definition whereas a given JSON that
 * contains less/more mandatory fields are rejected ($strict_assignment == true) or rather the object allows
 * partial JSON or JSON that contains more attributes than expected.
 * <br>
 * Based on the $attributes_details array this class will automatically validate/map
 * the object details either during its construction
 * (when $auto_validate_on_construction = true) or manually
 * calling to the validateObjectOrFail() method.
 *
 * <br>
 * Example of class definition:
 * <code>
 * // A Group contains a Collection of Elements
 * class Group extends BasicBeanValidated {
 *
 *     // When not defined or returning true, the current bean will automatically include a 'version' attribute
 *     public function getVersionFieldname() {
 *        return false;
 *     }
 *
 *     // The attribute names defined must match the JSON keys for automatic mapping.
 *     protected array $attributes_details = [
 *          'title' => ['mandatory' => true, 'type' => 'string'],
 *          'item_description' => ['mandatory' => true, 'type' => 'string'],
 *          'price' => ['mandatory' => false, 'type' => 'string_currency'],
 *          'is_alcohol' => ['mandatory' => true, 'type' => 'bool'],
 *          'elements' => ['mandatory' => true, 'type' => 'collection', 'class' => Element::class]
 *     ];
 *
 *     protected array $attributes_defaults = [
 *        'price' => '0.00',
 *        'is_alcohol' => false,
 *     ];
 *
 *     public function __construct(array $of_elements, array $attributes) {
 *        parent::__construct([
 *           'elements' => $of_elements,
 *           // ... other $attributes mapping
 *        ]);
 *     }
 * }
 * </code>
 * <br>
 * WARNING:
 * Do not manually assign the $attributes and/or $available_attributes arrays
 * that are defined at the parent class.
 * <br>
 * Instead, this class requires to assign the $attributes_details array.
 * This is not following the Liskov principle. Sorry.
 *
 * <br>
 * `$attributes_details` array structure:
 * <code>
 * 'mandatory' => boolean   true / false
 * 'min_count' => integer   Minimal number of elements for Collections
 * 'type'      => string    Data type conversion
 *        'array'               Converts the array elements into pipe | separated elements
 *        'string'
 *        'string_lowercase'    Converts the string to lowercase automatically
 *        'string_uppercase'
 *        'string_percent'      String without format assumed last 2 positions are 2 decimals
 *        'string_currency'     String without format assumed last 2 positions are 2 decimals
 *        'string_time24'       For time formatted as 24h in hours:minutes:seconds
 *        'string_time24_hhmm'  For time formatted as 24h in hours:minutes (no seconds)
 *        'date_iso8601'        2022-11-29T18:55:58.969+0000
 *        'date'
 *        'date_iso8601_carbon' date_default_timezone_get
 *        'bool'
 *        'boolean'
 *        'yes_no_as_boolean'   Parses YES, Y, SI, S as true; false otherwise
 *        'int'
 *        'int_timestamp'
 *        'float'
 *        'money'
 *        'class'               References to a 'class' for mapping
 *        'array_object'        Converts an array into an array of 'class' objects (or BasicBean if 'class' not set)
 *        'object'              Converts an object into a 'class' object (or BasicBean if 'class' not set)
 *        'collection'          When is a Collection is set 'as is'. When it is an array, the 'class' key is used for mapping.
 *
 * 'class'     => string    Class extending BasicBeanValidated used to map elements when 'type' is a Collection
 * </code>
 */
class BasicBeanValidated extends BasicBean
{

    /**
     * When set to true the unique attributes that can be assigned on the Model are defined at $available_attributes
     * @var bool $strict_assignment
     */
    protected $strict_assignment = true;

    /**
     * When set to true the mandatory fields will be verified during object construction.
     * Call to $this->validateObjectOrFail() manually on your own constructor when set to false.
     * @var bool $auto_validate_on_construction
     */
    protected $auto_validate_on_construction = true;

    /**
     * Allows defining when an attribute is 'mandatory' true/false, its type, etc.
     *
     * @var array
     */
    protected array $attributes_details = [];

    /**
     * Constructs the objects and validates its properties.
     *
     * The attributes will be automatically validated when $auto_validate_on_construction flag is set.
     * Otherwise, call to $this->validateObjectOrFail() in your customised constructor
     * to perform the validation on demand.
     *
     * @param array $attributes
     * @param bool|null $include_version Default true. Set as false to avoid including the version as part of the attributes
     * @throws InvalidObjectAttributesException
     */
    public function __construct($attributes = [], ?bool $include_version = true)
    {
        // Construct the "available_attributes" array based on "attributes_details" to make the object structure
        // compatible with the parent constructor
        if (count($this->attributes_details) > count($this->available_attributes)) {
            $this->available_attributes = [];
            foreach ($this->attributes_details as $key => $details) {
                $this->available_attributes[] = $key;
            }
        }

        parent::__construct($attributes, $include_version);

        if ($this->auto_validate_on_construction) {
            $this->validateObjectOrFail();
        }

        $this->formatAttributes();
    }

    /**
     * @throws InvalidObjectAttributesException
     */
    public function validateObjectOrFail()
    {
        foreach ($this->attributes_details as $attribute => $details) {
            if (!$details) {
                throw new InvalidObjectAttributesException("Invalid attribute '$attribute': [details] not set at 'attributes_details' array");
            }
            $this->verifyAttributeOrFail($attribute, $details);
        }
    }

    /**
     * @param string $attribute
     * @param array $details
     * @throws InvalidObjectAttributesException
     */
    private function verifyAttributeOrFail(string $attribute, array $details)
    {
        if (!$this->isMandatory($details)) {
            return;
        }

        if (!isset($this->attributes[$attribute])) {
            $clazz = get_class($this);
            throw new InvalidObjectAttributesException(
                "Invalid data. Mandatory '$attribute' attribute is not defined for $clazz"
            );
        }

        if (is_null($this->attributes[$attribute])) {
            $clazz = get_class($this);
            throw new InvalidObjectAttributesException(
                "Invalid data. $clazz.'$attribute' must have a value (null provided and no default set)"
            );
        }

        if ($this->isCountable($details)) {
            $min_elements_count = $details['min_count'] ?? 0;
            if (count($this->attributes[$attribute]) < $min_elements_count) {
                $clazz = get_class($this);
                throw new InvalidObjectAttributesException(
                    "Invalid data. $clazz.'$attribute' attribute of type Collection must have at least $min_elements_count elements"
                );
            }
        }
    }

    /**
     * @param array $details
     * @return bool
     */
    private function isMandatory(array $details): bool
    {
        return (bool)$details['mandatory'] ?? false;
    }

    /**
     * @param array $details
     * @return bool
     */
    private function isCountable(array $details): bool
    {
        return ($details['type'] ?? false) && ($details['type'] == 'collection') || ($details['type'] == 'array');
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws InvalidObjectAttributesException
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $this->getValueFormatted($key, $value));
    }

    /**
     * Formats the provided $value using the format details defined for $key at attributes_details
     *
     * For array type values it is used a conversion to string separated with '|'
     *
     * When $value is null
     *
     * @param string $key
     * @param mixed|null $value
     * @return mixed|null
     * @throws InvalidObjectAttributesException
     */
    private function getValueFormatted(string $key, mixed $value = null): mixed
    {
        $details = $this->attributes_details[$key] ?? null;

        $type = strtolower($details == null ? 'default' : ($details['type'] ?? 'default'));
        $mandatory = $details == null ? false : ($details['mandatory'] ?? false);
        $ref_class = $details == null ? null : ($details['class'] ?? null);

        if (is_null($value)) {
            if ($mandatory) {
                throw new InvalidObjectAttributesException("Invalid mandatory attribute '$key': null value set");
            } else {
                return null;
            }
        }

        try {
            switch ($type) {
                case 'string':
                    $result = (string)$value;
                    break;

                case 'string_lowercase':
                    $result = strtolower($value);
                    break;

                case 'string_uppercase':
                    $result = strtoupper($value);
                    break;

                case 'int':
                case 'int_timestamp':
                    $result = (int)$value;
                    break;

                case 'float':
                case 'money':
                    $result = (float)$value;
                    break;

                case 'object':
                    if ($value instanceof BasicBean || $value instanceof stdClass) {
                        $result = $value;
                    } else {
                        $va = is_array($value) ? $value : [$value];
                        $result = $ref_class == null ? new BasicBean($va, false) : new $ref_class($va);
                    }
                    break;

                case 'array_object':
                    if (!is_array($value)) {
                        $result = $value;
                        break;
                    }

                    $result = [];
                    foreach ($value as $v) {
                        $va = is_array($v) ? $v : [$v];
                        $result[] = $ref_class == null ? new BasicBean($va, false) : new $ref_class($va);
                    }
                    break;

                case 'array':
                    if (is_array($value)) {
                        $result = implode('|', $value);
                    } else {
                        $result = $value;
                    }
                    break;

                case 'array_array':
                    if (is_array($value)) {
                        $result = $value;
                        break;
                    }
                    $result = [];
                    break;

                case 'collection':
                    if ($value instanceof Collection) {
                        $result = $value;
                    } else {
                        // No specific class for Collection elements?
                        //   Return the Collection directly
                        if ($ref_class == null) {
                            $result = new Collection(is_array($value) ? $value : [$value]);
                        } else {
                            if (is_array($value)) {
                                $result = new Collection();
                                foreach ($value as $item) {
                                    $result->add(new $ref_class($item));
                                }
                            } else {
                                $result = new Collection([$value]);
                            }
                        }
                    }
                    break;

                case 'class':
                    if ($ref_class == null) {
                        throw new InvalidObjectAttributesException("Null class reference provided for key '$key'");
                    }
                    $result = new $ref_class($value);
                    break;

                case 'bool':
                case 'boolean':
                    $result = (bool)$value;
                    break;

                case 'yes_no_as_boolean':
                    $result = in_array(strtoupper($value), ['YES', 'Y', 'SI', 'S']);
                    break;

                case 'string_percent':
                case 'string_currency':
                    $result = (string)number_format($value, 2);
                    break;

                case 'date_iso8601':
                case 'date':
                    $result = (string)$value; // Carbon::parse($value);
                    break;

                case 'date_iso8601_carbon_utc':
                    if (empty($value)) $result = null;
                    else $result = Carbon::create($value)->tz('UTC');
                    break;

                case 'string_time24_hhmm': // for time formatted as 24h in hours:minutes (no seconds)
                    $result = substr((string)$value, 0, 5);
                    break;

                default:
                    $result = $value;
            }

            return $result;

        } catch (InvalidObjectAttributesException $ex) {
            throw $ex;

        } catch (Exception $ex) {
            $v = is_array($value) ? json_encode($value) : print_r($value, true);
            throw new InvalidObjectAttributesException(get_class($this) . " raised '" . $ex->getMessage() . "' for key '$key'. Value: " . $v);
        }
    }

    /**
     * Formats all current attributes forcing to call its magics 'setters'; hence in case the class overwrites one of
     * those setters it will be called. Otherwise, nothing will happen.
     * @return $this
     */
    public function formatAttributes(): static
    {
        foreach ($this->attributes as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }

}
