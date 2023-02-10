<?php

namespace Alexconesap\Commons\Exceptions;

use Throwable;

/**
 * Use this Exception to transport messages to the front-end users.
 *
 * It allows transporting 'parameters'.
 *
 * This Exception should be used when a 'normal' (logic) exception occurred such as an invalid
 * address provided for delivery an order, not availability of a requested service, etc.
 *
 * It must NOT be used to transport runtime/development/system Exceptions.
 *
 * @version 1
 */
abstract class BaseException extends \Exception
{

    private $parameters;
    private $status_code;

    /**
     * Customized constructor
     *
     * @param string $message Optional ("")
     * @param int $code Optional (0)
     * @param mixed $parameters Optional (null)
     * @param Throwable|null $previous Optional (null)
     */
    public function __construct(string $message = "", $code = 0, $parameters = null, Throwable $previous = null)
    {
        $int_code = is_numeric($code) ? $code : 0;

        parent::__construct($message, $int_code, $previous);

        $this->parameters = $parameters;
        $this->status_code = $code;
    }

    /**
     * Returns the parameters used to perform the API call
     * @return array|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the server error code
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

}
