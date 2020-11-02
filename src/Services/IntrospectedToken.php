<?php


namespace Codewiser\ResourceServer\Services;


use Codewiser\ResourceServer\Exceptions\RFC6750\InsufficientScopeException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;

class IntrospectedToken
{
    /**
     * @var boolean
     */
    protected $active;
    /**
     * @var string
     */
    protected $scope;
    protected $client_id;
    protected $username;
    protected $token_type;
    /**
     * @var integer
     */
    protected $exp;
    /**
     * @var integer
     */
    protected $iat;
    /**
     * @var integer
     */
    protected $nbf;
    protected $sub;
    protected $aud;
    protected $jti;
    protected $iss;

    public function __construct(array $options = [])
    {
        if (empty($options['active'])) {
            throw new InvalidArgumentException('Required option not passed: "active"');
        }

        $this->active = $options['active'];

        foreach (['scope', 'client_id', 'username', 'token_type', 'aud', 'jti', 'sub', 'iss'] as $item) {
            if (!empty($options[$item])) {
                $this->$item = $options[$item];
            }
        }

        foreach (['exp', 'iat', 'nbf'] as $item) {
            if (isset($options[$item])) {
                if (!is_numeric($options[$item])) {
                    throw new InvalidArgumentException($item . ' value must be an integer');
                }
                $this->$item = $options[$item];
            }
        }
    }

    public function toArray()
    {
        $data = [
            'active' => $this->active
        ];

        if ($this->active) {
            foreach (['scope', 'client_id', 'username', 'token_type', 'aud', 'jti', 'sub', 'iss', 'exp', 'iat', 'nbf'] as $item) {
                $data[$item] = $this->$item;
            }
        }

        return $data;
    }

    /**
     * Boolean indicator of whether or not the presented token
     * is currently active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * List of scopes associated with this token
     *
     * @return string[]
     */
    public function getScopes()
    {
        return explode(' ', $this->scope);
    }

    /**
     * A JSON string containing a space-separated list of
     * scopes associated with this token
     *
     * @return string|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Client identifier for the OAuth 2.0 client that
     * requested this token
     *
     * @return string|null
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Human-readable identifier for the resource owner who
     * authorized this token
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Type of the token
     *
     * @return string|null
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token will expire,
     * as defined in JWT [RFC7519]
     *
     * @return Carbon|null
     */
    public function getExpiredAt()
    {
        return $this->exp ? Carbon::createFromTimestamp($this->exp) : null;
    }

    /**
     * Checks if this token has expired.
     *
     * @return boolean true if the token has expired, false otherwise.
     * @throws RuntimeException if 'expires' is not set on the token.
     */
    public function hasExpired()
    {
        $expires = $this->exp;

        if (empty($expires)) {
            throw new RuntimeException('"exp" is not set on the token');
        }

        return $expires < time();
    }

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token was
     * originally issued, as defined in JWT [RFC7519]
     *
     * @return Carbon|null
     */
    public function getIssuedAt()
    {
        return $this->iat ? Carbon::createFromTimestamp($this->iat) : null;
    }

    /**
     * Integer timestamp, measured in the number of seconds
     * since January 1 1970 UTC, indicating when this token is not to be
     * used before, as defined in JWT [RFC7519]
     *
     * @return Carbon|null
     */
    public function getNotBefore()
    {
        return $this->nbf ? Carbon::createFromTimestamp($this->nbf) : null;
    }

    /**
     * Subject of the token, as defined in JWT [RFC7519].
     * Usually a machine-readable identifier of the resource owner who
     * authorized this token
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->sub;
    }

    /**
     * Service-specific string identifier or list of string
     * identifiers representing the intended audience for this token, as
     * defined in JWT [RFC7519].
     *
     * @return string|null
     */
    public function getAudience()
    {
        return $this->aud;
    }

    /**
     * String identifier for the token, as defined in JWT
     * [RFC7519].
     *
     * @return string|null
     */
    public function getTokenIdentifier()
    {
        return $this->jti;
    }

    /**
     * String representing the issuer of this token, as
     * defined in JWT [RFC7519].
     *
     * @return string|null
     */
    public function getIssuer()
    {
        return $this->iss;
    }

    /**
     * @param $scope
     * @throws InsufficientScopeException
     */
    public function validateScope($scope)
    {
        if (!in_array($scope, $this->getScopes())) {
            throw new InsufficientScopeException("Scope '{$scope}' required.");
        }
    }
}
