<?php

return [
    'oauth_server' => env('OAUTH_SERVER'),
    'client_id' => env('CLIENT_ID'),
    'client_secret' => env('CLIENT_SECRET'),
    'scopes' => env('SCOPE'),

    'redirect_uri' => env('REDIRECT_URI', rtrim(env('APP_URL'), '/') . '/oauth/callback'),
    'authorize_endpoint' => env('AUTHORIZE_ENDPOINT', 'oauth/authorize'),
    'token_endpoint' => env('TOKEN_ENDPOINT', 'oauth/token'),
    'resource_owner_endpoint' => env('RESOURCE_OWNER_ENDPOINT', 'api/user'),
    'introspection_endpoint' => env('INTROSPECTION_ENDPOINT', 'oauth/introspect'),
];
