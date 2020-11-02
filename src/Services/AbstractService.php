<?php


namespace Codewiser\ResourceServer\Services;


abstract class AbstractService
{

    protected $provider_options;
    protected $options = [];
    private $default_scope;

    public function __construct($client_id, $client_secret, $default_scope, $url_authorize, $url_grant, $url_resource_owner)
    {
        // It is to concrete interface

        $this->provider_options = [
            'clientId' => $client_id,
            'clientSecret' => $client_secret,
            'urlAuthorize' => $url_authorize,
            'urlAccessToken' => $url_grant,
            'urlResourceOwnerDetails' => $url_resource_owner
        ];

        $this->default_scope = $default_scope;
    }

    protected function getDefaultScope()
    {
        return $this->default_scope;
    }

    /**
     * Override default scopes, that will be used next authorization.
     *
     * @param string $scope
     * @return static
     */
    public function setScope($scope)
    {
        $this->options['scope'] = $scope;
        return $this;
    }
}