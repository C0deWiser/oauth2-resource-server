<?php

namespace Codewiser\ResourceServer\Facades;

use Codewiser\ResourceServer\Services\IntrospectedToken;
use Codewiser\ResourceServer\Services\ResourceServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Class ResourceServer
 * @package Codewiser\ResourceServer\Facades
 *
 * @method static IntrospectedToken validateRequest(Request $request)
 * @method static AccessTokenInterface getAccessToken()
 * @method static forgetAccessToken()
 * @method static IntrospectedToken getIntrospectedToken(AccessTokenInterface $token)
 * @method static ResourceServerService setScope(string $scope)
 * @method static ResourceOwnerInterface getTokenOwner(Request $request)
 */
class ResourceServer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ResourceServer';
    }
}
