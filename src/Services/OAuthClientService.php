<?php


namespace Codewiser\ResourceServer\Services;


use Codewiser\ResourceServer\Exceptions\OauthResponseException;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class OAuthClientService extends AbstractService
{

    /**
     * @var GenericProvider
     */
    protected $provider;

    /**
     * @var ResourceServerContext
     */
    protected $context;

    public function __construct($client_id, $client_secret, $default_scope, $url_authorize, $url_grant, $url_resource_owner, $redirect_uri)
    {
        parent::__construct($client_id, $client_secret, $default_scope, $url_authorize, $url_grant, $url_resource_owner);

        $options = $this->provider_options + [
            'redirectUri' => $redirect_uri,
        ];

        $this->provider = new GenericProvider($options);

        $this->context = new ResourceServerContext();
    }

    /**
     * Get authorization URL.
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        $options = $this->options + ['scope' => $this->getDefaultScope()];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->context->state = $this->provider->getState();
        $this->context->response_type = 'code';

        return $url;
    }


    /**
     * Remember URL to return user to after authorization is complete.
     *
     * @param string $returnPath
     * @return static
     */
    public function setReturnUrl($returnPath)
    {
        $this->context->return_path = $returnPath;

        return $this;
    }

    /**
     * Get previously stored return URL.
     *
     * @param string $finally
     * @return string
     */
    public function getReturnUrl($finally)
    {
        $returnPath = $this->context->return_path ?: $finally;

        return $returnPath;
    }

    /**
     * Proceed OAuth 2.0 server callback.
     *
     * @param Request $request
     * @return static
     * @throws IdentityProviderException
     * @throws OauthResponseException
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            throw new OauthResponseException(
                $request->get('error'),
                $request->get('error_description'),
                $request->get('error_uri')
            );
        }

        if ($request->has('state')) {

            if (!$this->context->restoreContext($request->get('state'))) {
                // Fake
                exit('Invalid state');
            }

            if ($this->context->response_type == 'code' && $request->has('code')) {

                $access_token = $this->grantAuthorizationCode($request->get('code'));

                $this->setAccessToken($access_token);

            } else {

                exit('Invalid request');
            }
        }

        return $this;
    }

    protected function setAccessToken(AccessTokenInterface $accessToken)
    {
        $this->context->access_token = serialize($accessToken);
    }

    /**
     * Get Personal Access Token.
     *
     * @return AccessToken|null
     */
    public function getAccessToken()
    {
        $accessToken = isset($this->context->access_token) ? unserialize($this->context->access_token) : null;

        if ($accessToken && (!is_object($accessToken) || !($accessToken instanceof AccessTokenInterface))) {
            $accessToken = null;
        }

        return $accessToken;
    }

    /**
     * Forget Personal Access Token.
     */
    public function forgerAccessToken()
    {
        unset($this->context->access_token);
    }

    /**
     * Check if Personal Access Token obtained and stored in user session.
     *
     * @return bool
     */
    public function hasAccessToken()
    {
        return !!$this->getAccessToken();
    }

    /**
     * Get Resource Owner.
     *
     * @return ResourceOwnerInterface|null
     */
    public function getResourceOwner()
    {
        if ($accessToken = $this->getAccessToken()) {
            return $this->provider->getResourceOwner($accessToken);
        } else {
            return null;
        }
    }


    /**
     * @param $code
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
    protected function grantAuthorizationCode($code)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
    }
}