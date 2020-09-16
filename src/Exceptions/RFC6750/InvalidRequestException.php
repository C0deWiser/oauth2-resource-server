<?php

namespace Codewiser\ResourceServer\Exceptions\RFC6750;

use Throwable;

/**
 * The request is missing a required parameter, includes an
    unsupported parameter or parameter value, repeats the same
    parameter, uses more than one method for including an access
    token, or is otherwise malformed.  The resource server SHOULD
    respond with the HTTP 400 (Bad Request) status code.
 *
 * @package Codewiser\ResourceServer\Exceptions\RFC6750
 */
class InvalidRequestException extends BearerTokenException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->getDefaultMessage(), 400, $previous);
    }

    protected function getDefaultMessage()
    {
        return "The request is missing a required parameter";
    }
}
