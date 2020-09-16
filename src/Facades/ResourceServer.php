<?php


namespace Codewiser\ResourceServer\Facades;


use Codewiser\ResourceServer\Services\IntrospectedToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Class ResourceServer
 * @package Codewiser\ResourceServer\Facades
 *
 * @method static IntrospectedToken validate(Request $request)
 * @method static IntrospectedToken introspect(Request $request)
 * @method static AccessTokenInterface getAccessToken(string $scope = null)
 */
class ResourceServer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ResourceServer';
    }
}
