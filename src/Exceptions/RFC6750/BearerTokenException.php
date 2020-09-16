<?php

namespace Codewiser\ResourceServer\Exceptions\RFC6750;

use Exception;
use Illuminate\Support\Str;

/**
 * When a request fails, the resource server responds using the
 * appropriate HTTP status code (typically, 400, 401, 403, or 405) and
 * includes error codes in the response.
 *
 * If the request lacks any authentication information (e.g., the client
 * was unaware that authentication is necessary or attempted using an
 * unsupported authentication method), the resource server SHOULD NOT
 * include an error code or other error information.
 *
 * @package Codewiser\ResourceServer\Exceptions\RFC6750
 *
 * @example
 * HTTP/1.1 401 Unauthorized
 * WWW-Authenticate: Bearer realm="example"
 *
 * @example
 * HTTP/1.1 401 Unauthorized
 * WWW-Authenticate: Bearer realm="example",
 * error="invalid_token",
 * error_description="The access token expired"
 */
abstract class BearerTokenException extends Exception
{
    protected function getName()
    {
        return Str::snake(Str::replaceLast('Exception', '', class_basename($this)));
    }

    protected function getRealm()
    {
        return Str::snake(Str::lower(env('APP_NAME')));
    }

    public function toArray()
    {
        return [
            'realm' => $this->getRealm(),
            'error' => $this->getName(),
            'error_description' => $this->getMessage()
        ];
    }

    public function getResponseHeaders()
    {
        $data = [];
        foreach ($this->toArray() as $key => $value) {
            $data[] = $key . '="' . $value . '"';
        }

        return [
            "WWW-Authenticate" => "Bearer " . implode(', ', $data)
        ];
    }

    public function render($request)
    {
        return response()
            ->json(
                $this->toArray(),
                $this->getCode(),
                $this->getResponseHeaders()
            );
    }
}
