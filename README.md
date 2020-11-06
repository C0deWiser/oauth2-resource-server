# Description

OAuth is an authorization server. It provides and validates tokens. 
It is the best solution to build distributed api infrastructure.

Infrastructure may consist of many api servers, called Resource Server. 
Every request those servers accept must contain authorization information — an `access_token` issued by authorization server.

Every resource server is an OAuth client. It has `client_id` and `client_secret` 
and may issue its own `access_token` using `client credentials` grant. 
Otherhand, it may be a personal `access_token`, issued by a user in a traditional way. 
After issuing `access_token` the server will use it to make requests to the neighbors (other resource servers in the same infrastructure),
or to provide access to the local resources.

When server receives request with authorization information, 
it will introspect (see [rfc7662](https://tools.ietf.org/html/rfc7662)) `access_token` from request. 
Api server calls OAuth server and receives from it information about given `access_token`. 

If token is valid and has appropriate scopes, the server will handle the request. 
If it is not, the server will reply with an error.

## RFC

* Token Introspection   
  https://tools.ietf.org/html/rfc7662 
* Bearer Token Usage  
  https://tools.ietf.org/html/rfc6750
  
The package based on league/oauth2-client

## Prerequisite

Your OAuth server must implement rfc7662 (token introspection endpoint).
Take a look at [ipunkt/laravel-oauth-introspection](https://packagist.org/packages/ipunkt/laravel-oauth-introspection).

## Installation

```
composer require codewiser/oauth2-resource-server
```

Publish package config.

```
php artisan vendor:publish --provider="Codewiser\ResourceServer\Providers\ResourceServerServiceProvider"
```

## Setup

An environment requires all standard OAuth client properties.

```dotenv
OAUTH_SERVER=https://oauth.example.com
CLIENT_ID=123
CLIENT_SECRET=***
SCOPE="read write"
```

`SCOPE` is for default scopes for requested access tokens.

Next are optional and has default values.

```dotenv
REDIRECT_URI=oauth/callback
AUTHORIZE_ENDPOINT=oauth/authorize
TOKEN_ENDPOINT=oauth/token
RESOURCE_OWNER_ENDPOINT=api/user
INTROSPECTION_ENDPOINT=oauth/introspect
```

You may provide full URLs or only paths.

## Facades and Middlewares

### ResourceServer

`ResourceServer` is a layer of OAuth-client, 
that takes responsibility to keep `Client Credentials Access Token` 
and to protect API resources.

```php
$accessToken = ResourceServer::getAccessToken();
```

This will return cached (or newly issued) Client Access Token. 
Use it call other API servers.

Token may be sent as `Athorization` header 
(see [rfc6750#section-2.1](https://tools.ietf.org/html/rfc6750#section-2.1)),    
as `access_token` body parameter 
(see [rfc6750#section-2.2](https://tools.ietf.org/html/rfc6750#section-2.2)) or  
as `access_token` query parameter 
(see [rfc6750#section-2.3](https://tools.ietf.org/html/rfc6750#section-2.3)).

Then your server receives API request with Bearer token, 
it should introspect token on OAuth-server.

```php
$introspected = ResourceServer::getIntrospectedToken($request->bearerToken());
```

In a simple way you may protect the routes with `ResourceServerMiddleware`.
Define it in `app/Http/Kernel.php` in way you like.

```php
protected $routeMiddleware = [
    'scope' => \Codewiser\ResourceServer\Http\Middleware\ResourceServerMiddleware::class,
];
``` 

And than protect you route.

```php
Route::get('resource', 'ApiController@list')->middleware('scope:read');

class ApiController extends Controller
{
  public function list(Request $request)
  {
     // Get user profile from OAuth server
     $owner = ResourceServer::getTokenOwner($request);
    
    // Your code here
  }
}
```

Otherwise you may protect group of routes with middleware 
and validate scope in controllers.

```php
Route::get('resource', 'ApiController@list')->middleware('scope');

class ApiController extends Controller
{
  public function list(Request $request)
  {
    ResourceServer::introspect($request)
        ->validateScope('read');
    
    // Your code here
  }
}
```



If request were not validated, the throwed exception renders proper response 
(according to [rfc6750](https://tools.ietf.org/html/rfc6750)).

### OAuthClient

`OAuthClient` is a layer of OAuth-client, 
that takes responsibility to authorize users and keeps their `Personal Access Token`.

```php
if (!OAuthClient::hasAccessToken()) {
    
    // Will remeber current page to get user back here.
    OAuthClient::setReturnUrl($request->fullUrl());
    
    // Set required scopes
    OAuthClient::setScope('read write email etc');

    return redirect(OAuthClient::getAuthorizationUrl())
}
```

Authorization server will return user back to `CallbackController`. 
You may use built-in or define new one.

```php
try {
    
    // Callback will exchange authorization_code to access_token and stores it into session.
    OAuthClient::callback($request);
    
    // Then return user back to the page we previously stores.
    return redirect(OAuthClient::getReturnUrl('/'));
} catch (\Throwable $e) {

}
```

So, if we have `Personal Access Token` we should provide requested information to the user.

```php
if (OAuthClient::hasAccessToken()) {
    
    ResourceServer::getIntrospectedToken(OAuthClient::getAccessToken())
        ->validateScope('read');
    
    // Your code here
}
```

In a simple way you may protect the routes with `PersonalAccessMiddleware`.
Define it in `app/Http/Kernel.php` in way you like.

```php
protected $routeMiddleware = [
    'private' => \Codewiser\ResourceServer\Http\Middleware\PersonalAccessMiddleware::class,
];
``` 

And than protect you route.

```php
Route::get('profile', 'PersonalController@show')->middleware('private:read')
```

If user has no `Personal Access Token` he or she will be redirected to Authorization Server.

## Cache

All tokens are cached locally for a limited time.