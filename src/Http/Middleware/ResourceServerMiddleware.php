<?php

namespace Codewiser\ResourceServer\Http\Middleware;

use Codewiser\ResourceServer\Exceptions\RFC6750\BearerTokenException;
use Codewiser\ResourceServer\Facades\ResourceServer;
use Closure;
use Illuminate\Http\Request;

class ResourceServerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $scope
     * @return mixed
     * @throws BearerTokenException
     */
    public function handle(Request $request, Closure $next, $scope = '')
    {
        $info = ResourceServer::validateRequest($request);
        if ($scope) {
            $info->validateScope($scope);
        }
        return $next($request);
    }
}
