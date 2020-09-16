<?php


namespace Codewiser\ResourceServer\Services;


use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use UnexpectedValueException;

class ProviderWithRfc7662 extends GenericProvider
{
    protected $urlIntrospectToken;

    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
            'urlResourceOwnerDetails',
            'urlIntrospectToken'
        ];
    }

    /**
     * @param $tokenToIntrospect
     * @param $tokenToAuthorize
     * @return IntrospectedToken
     * @throws IdentityProviderException
     */
    public function getIntrospectedToken($tokenToIntrospect, $tokenToAuthorize)
    {
        $options = ['headers' => ['content-type' => 'application/x-www-form-urlencoded']];
        $options['body'] = $this->buildQueryString(['token' => $tokenToIntrospect]);

        $request = $this->getAuthenticatedRequest('POST', $this->urlIntrospectToken, $tokenToAuthorize, $options);

        $response = $this->getParsedResponse($request);
        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        return new IntrospectedToken($response);
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return [
            "Authorization" => "Bearer {$token}"
        ];
    }
}
