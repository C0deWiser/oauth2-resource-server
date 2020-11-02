<?php

namespace Codewiser\ResourceServer\Exceptions;

class IdentityProviderException extends \League\OAuth2\Client\Provider\Exception\IdentityProviderException
{
    protected $description;
    protected $uri;

    /**
     * @param string $message
     * @param int $code
     * @param array|string $response The response body
     * @param string $description
     * @param string $uri
     */
    public function __construct($message, $code, $response, $description = '', $uri = '')
    {
        $this->description = $description;
        $this->uri = $uri;

        parent::__construct($message, $code, $response);
    }

    /**
     * Returns the exception's description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the exception's URI.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
