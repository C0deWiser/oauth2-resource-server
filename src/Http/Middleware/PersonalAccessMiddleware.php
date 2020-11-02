<?php


namespace Codewiser\ResourceServer\Http\Middleware;


use Closure;
use Codewiser\ResourceServer\Exceptions\RFC6750\InsufficientScopeException;
use Codewiser\ResourceServer\Facades\OAuthClient;
use Codewiser\ResourceServer\Facades\ResourceServer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class PersonalAccessMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $scope
     * @return RedirectResponse|Redirector|mixed
     * @throws InsufficientScopeException
     */
    public function handle(Request $request, Closure $next, $scope = '')
    {
        if ($accessToken = OAuthClient::getAccessToken()) {

            $info = ResourceServer::getIntrospectedToken($accessToken);

            if ($scope) {
                $info->validateScope($scope);
            }

        } else {

            OAuthClient::setReturnUrl($request->fullUrl());

            return redirect(OAuthClient::getAuthorizationUrl());
        }

        return $next($request);
    }
}