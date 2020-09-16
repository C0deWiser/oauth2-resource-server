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

The package based on league/oauth2-client and league/oauth2-server as it combines roles of oauth-client and resource-server.

### API Middleware

Middleware extracts authorization information from the request, calls oauth server to introspect token and then merge scope information into the request, passing it down to the controller.

### Exceptions

Package provides exception that replies according to rfc6750. You may throw this exception from the controller if `access_token` has insufficient scope.

### ResourceServer Facade

Helps to issue, store and refresh tokens.
