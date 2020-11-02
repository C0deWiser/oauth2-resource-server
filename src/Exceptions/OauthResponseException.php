<?php

namespace Codewiser\ResourceServer\Exceptions;

class OauthResponseException extends IdentityProviderException
{
    public function __construct($message, $description = '', $uri = '')
    {
        parent::__construct($message, 0, null, $description, $uri);
    }
}
