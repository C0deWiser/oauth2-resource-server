<?php


namespace Codewiser\ResourceServer\Facades;


use Codewiser\ResourceServer\Services\OAuthClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class OAuthClient
 * @package Codewiser\ResourceServer\Facades
 *
 * @method static AccessToken getAccessToken()
 * @method static OAuthClientService setReturnUrl(string $url)
 * @method static getReturnUrl(string $finally)
 * @method static string getAuthorizationUrl()
 * @method static OAuthClientService callback(Request $request)
 * @method static forgerAccessToken()
 * @method static boolean hasAccessToken()
 * @method static OAuthClientService setScope(string $scope)
 * @method static ResourceOwnerInterface getResourceOwner()
 */
class OAuthClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'OAuthClient';
    }
}