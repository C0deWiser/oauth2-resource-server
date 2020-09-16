<?php

namespace Codewiser\ResourceServer\Exceptions\RFC6750;

use Throwable;

/**
 * The request requires higher privileges than provided by the
 * access token.  The resource server SHOULD respond with the HTTP
 * 403 (Forbidden) status code and MAY include the "scope"
 * attribute with the scope necessary to access the protected
 * resource.
 *
 * @package Codewiser\ResourceServer\Exceptions\RFC6750
 */
class InsufficientScopeException extends BearerTokenException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->getDefaultMessage(), 403, $previous);
    }

    protected function getDefaultMessage()
    {
        return "The request requires higher privileges than provided by the access token";
    }
}
