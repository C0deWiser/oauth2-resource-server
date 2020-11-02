<?php


namespace Codewiser\ResourceServer\Services;

use Codewiser\ResourceServer\Exceptions\OauthResponseException;
use Codewiser\ResourceServer\Exceptions\RFC6750\BearerTokenException;
use Codewiser\ResourceServer\Exceptions\RFC6750\InvalidRequestException;
use Codewiser\ResourceServer\Exceptions\RFC6750\InvalidTokenException;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;

class ResourceServerService extends AbstractService
{
    /**
     * @var ProviderWithRfc7662
     */
    protected $provider;

    public function __construct($client_id, $client_secret, $default_scope, $url_authorize, $url_grant, $url_resource_owner, $url_introspect)
    {
        parent::__construct($client_id, $client_secret, $default_scope, $url_authorize, $url_grant, $url_resource_owner);

        $options = $this->provider_options + [
            'urlIntrospectToken' => $url_introspect
        ];

        $this->provider = new ProviderWithRfc7662($options);
    }

    /**
     * Get Client Credentials Access Token.
     *
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
    public function getAccessToken()
    {
        $options = array_merge(
            ['scope' => $this->getDefaultScope()],
            (array)$this->options
        );

        return $this->getCachedAccessToken($options['scope']) ?:
            $this->cacheAccessToken(
                $options['scope'],
                $this->provider->getAccessToken('client_credentials', $options)
            );
    }

    /**
     * @param string $scope
     * @param string $key
     * @return AccessTokenInterface|null
     */
    protected function getCachedAccessToken($scope, $key = 'client_access_token')
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
    protected function cacheAccessToken($scope, AccessTokenInterface $accessToken, $key = 'client_access_token')
    {
        $key = $key . md5($scope);

        Cache::put($key, serialize($accessToken), Carbon::createFromTimestamp($accessToken->getExpires()));
        return $accessToken;
    }

    public function forgetAccessToken($scope, $key = 'client_access_token')
    {
        $key = $key . md5($scope);

        Cache::forget($key);
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
     * @param string|AccessTokenInterface $token
     * @return IntrospectedToken
     * @throws IdentityProviderException
     */
    public function getIntrospectedToken($token)
    {
        if ($token instanceof AccessTokenInterface) {
            $token = $token->getToken();
        }

        try {
            return $this->getCachedTokenIntrospection($token) ?:
                $this->cacheTokenIntrospection(
                    $token, $this->provider->getIntrospectedToken($token, $this->getAccessToken())
                );
        } catch (IdentityProviderException $e) {
            $this->forgetAccessToken($this->getDefaultScope());
            throw $e;
        }
    }

    /**
     * Extract token from header or query parameter, introspect it, and go on
     *
     * @param Request $request
     * @return IntrospectedToken
     * @throws BearerTokenException
     */
    public function validateRequest(Request $request)
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
        if ($token = $request->bearerToken()) {

        } elseif ($token = $request->get('access_token')) {

        } else {
            throw new InvalidRequestException('Missing authorization information. See https://tools.ietf.org/html/rfc6750#section-2');
        }

        return $this->getIntrospectedToken($token);
    }
}
