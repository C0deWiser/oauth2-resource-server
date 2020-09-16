# Description

OAuth is an authorization server. It provides and validates tokens. It is the best solution to build distributed api infrastructure.

Infrastucture may consist of many api servers, called Resource Server. Every request those servers accept must contain authorization information — an `access_token`, issued by authorization server.

Every resource server is an oauth client. It has `client_id` and `client_secret` and may issue its own `access_token using` `client credentials` grant. Otherhand, it may be a personal `access_token`, issued by a user in a traditional way. After issuing `access_token` the server will use it to make requests to the neighbors (other resource servers in the same infrastructure).

When server recieves request with authorization information, it will intospect (see rfc7662) `access_token` from request. Api server calls oauth server and recieves from it information about given `access_token`. 

If token is valid and has appropriate scopes, the server will handle the request. If it is not, the server will reply with an error.

## RFC

* Token Introspection   
  https://tools.ietf.org/html/rfc7662 
* Bearer Token Usage  
  https://tools.ietf.org/html/rfc6750

## Prerequisite

Your OAuth server must implement rfc7662 (token introspection endpoint).

## Package contents

The package based on league/oauth2-client

### API Middleware

Middleware extracts authorization information from the request, calls oauth server to introspect token and then merge scope information into the request, passing it down to the controller.

### Exceptions

Package provides exception that replies according to rfc6750. You may throw this exception from the controller if `access_token` has insufficient scope.

### Request Builder

Helps to build requests with authorization information.

### ResourceServer Facade

Helps to issue, store and refresh tokens.

## Setup

Environment requires all standard OAuth client properties.

```env
OAUTH_SERVER=https://example.com
CLIENT_ID=123
CLIENT_SECRET=***
REDIRECT_URI=http://localhost/oauth/callback
AUTHORIZE_ENDPOINT=oauth/authorize
TOKEN_ENDPOINT=oauth/token
RESOURCE_OWNER_ENDPOINT=api/user
INTROSPECTION_ENDPOINT=oauth/token/info
SCOPE=read write
```

`REDIRECT_URI` is not required by package, but league/oauth2-client need it.
`SCOPE` is for default scopes for requested access tokens.

## Middleware

Register `ResourceServerMiddleware` with alias you like and protect api routes you want.

You may protect exact route with middleware, defining required scope.

```php
Route::get('resource', 'ApiController@list')->middleware('scope:read')
```

Otherwise you may protect group of routes with middleware and validate scope in controllers.

```php
class ApiController extends Controller
{
  public function list(Request $request)
  {
    ResourceServer::introspect($request)->validateScope('read');
    
    // Your code here
  }
}
```

## ResourceServer Facade

Facade manages access_token of your server and provides methods to instrospect external tokens.

```php
$accessToken = ResourceServer::getAccessToken();
```

Access token of your application is stored in cache for all lifetime. Use it to make requests to neighbor resource servers. 

```php
$authorizedRequestHeaders = [
  'Authorization' => ResourceServer::getAuthorizationHeader()
];
```

To introspect any token:

```php
$introspectedToken = ResourceServer::getIntrospectedToken($token);
```

Information about introspected token stored in cache for one day. So oauth server will not be overhitted.