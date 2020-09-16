<?php

namespace Codewiser\ResourceServer\Exceptions\RFC6750;

use Throwable;

/**
 * The access token provided is expired, revoked, malformed, or
    invalid for other reasons.  The resource SHOULD respond with
    the HTTP 401 (Unauthorized) status code.  The client MAY
    request a new access token and retry the protected resource
    request.
 *
 * @package Codewiser\ResourceServer\Exceptions\RFC6750
 */
class InvalidTokenException extends BearerTokenException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->getDefaultMessage(), 401, $previous);
    }

    protected function getDefaultMessage()
    {
        return "The access token provided is expired, revoked, malformed, or invalid for other reasons";
    }
}
