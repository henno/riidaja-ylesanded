<?php
/**
 * Extended Azure provider that allows clock skew (leeway) for token validation.
 * This is needed when the server clock is slightly ahead of Microsoft's servers.
 */

use TheNetworg\OAuth2\Client\Provider\Azure;

class AzureWithLeeway extends Azure
{
    /**
     * Leeway in seconds for token time validation (nbf/exp claims)
     * @var int
     */
    public $tokenLeeway = 300; // 5 minutes default

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['tokenLeeway'])) {
            $this->tokenLeeway = (int)$options['tokenLeeway'];
        }
        parent::__construct($options, $collaborators);
    }

    /**
     * Override validateTokenClaims to add leeway for clock skew
     */
    public function validateTokenClaims($tokenClaims)
    {
        if ($this->getClientId() != $tokenClaims['aud']) {
            throw new \RuntimeException('The client_id / audience is invalid!');
        }

        $now = time();
        // Add leeway: nbf can be up to $leeway seconds in the future
        // exp must be at least $leeway seconds in the past to be considered expired
        if ($tokenClaims['nbf'] > ($now + $this->tokenLeeway) || $tokenClaims['exp'] < ($now - $this->tokenLeeway)) {
            throw new \RuntimeException('The id_token is invalid!');
        }

        if ('common' == $this->tenant) {
            $this->tenant = $tokenClaims['tid'];
        }
    }
}
