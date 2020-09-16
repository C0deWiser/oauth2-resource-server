<?php


namespace Codewiser\ResourceServer\Services;

use Codewiser\ResourceServer\Exceptions\RFC6750\BearerTokenException;
use Codewiser\ResourceServer\Exceptions\RFC6750\InvalidRequestException;
use Codewiser\ResourceServer\Exceptions\RFC6750\InvalidTokenException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;

class ResourceServerService
{
    /**
     * @var ProviderWithRfc7662
     */
    protected $provider;

    public function __construct()
    {
        $options = [
            'clientId' => config('resource_server.client_id'),
            'clientSecret' => config('resource_server.client_secret'),
            'redirectUri' => config('resource_server.redirect_uri'),
            'urlAuthorize' => $this->getUrlAuthorize(),
            'urlAccessToken' => $this->getUrlAccessToken(),
            'urlResourceOwnerDetails' => $this->getUrlResourceOwnerDetails(),
            'urlIntrospectToken' => $this->getUrlTokenIntrospection()
        ];
        
        $this->provider = new ProviderWithRfc7662($options);
    }

    /**
     * OAuth user authorization URL.
     *
     * @return string
     */
    public function getUrlAuthorize()
    {
        return $this->getUrl(config('resource_server.authorize_endpoint'));
    }

    /**
     * OAuth grant token URL.
     *
     * @return string
     */
    public function getUrlAccessToken()
    {
        return $this->getUrl(config('resource_server.token_endpoint'));
    }

    /**
     * OAuth token owner info URL.
     *
     * @return string
     */
    public function getUrlResourceOwnerDetails()
    {
        return $this->getUrl(config('resource_server.resource_owner_endpoint'));
    }

    /**
     * OAuth token introspection URL.
     *
     * @return string
     */
    public function getUrlTokenIntrospection()
    {
        return $this->getUrl(config('resource_server.introspection_endpoint'));
    }

    protected function getUrl($endpoint)
    {
        $server = config('resource_server.oauth_server');
        if ($server && $endpoint) {
            return $server . '/' . $endpoint;
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $scope
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
    public function getAccessToken($scope = '')
    {
        $scope = $scope ?: $this->getDefaultScope();

        return $this->getCachedAccessToken($scope) ?:
            $this->cacheAccessToken(
                $scope,
                $this->provider->getAccessToken('client_credentials', ['scope' => $scope])
            );
    }

    protected function getDefaultScope()
    {
        return config('resource_server.scopes');
    }

    /**
     * @param $scope
     * @param string $key
     * @return AccessTokenInterface|null
     */
    protected function getCachedAccessToken($scope, $key = 'client_credentials_access_token')
    {
        $key = $key . md5($scope);

        if (Cache::has($key)) {
            /** @var AccessTokenInterface $accessToken */
            $accessToken = unserialize(Cache::get($key));
            if ($accessToken instanceof AccessTokenInterface && !$accessToken->hasExpired()) {
                return $accessToken;
            }
        }
        return null;
    }

    /**
     * @param $scope
     * @param AccessTokenInterface $accessToken
     * @param string $key
     * @return AccessTokenInterface
     */
    protected function cacheAccessToken($scope, AccessTokenInterface $accessToken, $key = 'client_credentials_access_token')
    {
        $key = $key . md5($scope);

        Cache::put($key, serialize($accessToken), Carbon::createFromTimestamp($accessToken->getExpires()));
        return $accessToken;
    }

    /**
     * @param string $token
     * @return IntrospectedToken|null
     */
    protected function getCachedTokenIntrospection($token)
    {
        if (Cache::has('ti_' . $token)) {
            /** @var IntrospectedToken $tokenIntrospection */
            $tokenIntrospection = unserialize(Cache::get('ti_' . $token));
            if ($tokenIntrospection instanceof IntrospectedToken && !$tokenIntrospection->hasExpired()) {
                return $tokenIntrospection;
            }
        }
        return null;
    }

    /**
     * @param string $token
     * @param IntrospectedToken $introspectedToken
     * @return IntrospectedToken
     */
    protected function cacheTokenIntrospection($token, IntrospectedToken $introspectedToken)
    {
        Cache::put('ti_' . $token,
            serialize($introspectedToken),
            now()->addDay()
        );
        return $introspectedToken;
    }

    /**
     * @param string $token
     * @return IntrospectedToken|null
     */
    protected function getIntrospectedToken($token)
    {

        try {
            return $this->getCachedTokenIntrospection($token) ?:
                $this->cacheTokenIntrospection(
                    $token, $this->provider->getIntrospectedToken($token, $this->getAccessToken())
                );
        } catch (IdentityProviderException $e) {
            return null;
        }
    }

    /**
     * Extract token from header or query parameter, introspect it, and go on
     *
     * @param Request $request
     * @return IntrospectedToken
     * @throws BearerTokenException
     */
    public function validate(Request $request)
    {
        $info = $this->introspect($request);

        if (!$info || !$info->isActive()) {
            throw new InvalidTokenException("Token not recognized or expired");
        }

        return $info;
    }

    /**
     * @param Request $request
     * @return IntrospectedToken|null
     * @throws BearerTokenException
     */
    public function introspect(Request $request)
    {
        if ($request->hasHeader('authorization')) {
            $header = $request->header('authorization');
            $token = \trim((string)\preg_replace('/^(?:\s+)?Bearer\s/', '', $header));
        } elseif ($request->has('access_token')) {
            $token = $request->get('access_token');
        } else {
            throw new InvalidRequestException('Missing authorization information. See https://tools.ietf.org/html/rfc6750#section-2');
        }

        return $this->getIntrospectedToken($token);
    }
}
