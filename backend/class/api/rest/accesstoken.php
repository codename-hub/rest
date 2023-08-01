<?php

namespace codename\rest\api\rest;

use codename\core\credential;
use codename\core\exception;
use codename\rest\api\rest;
use ReflectionException;

/**
 * Accesstoken-Authentication-based
 * REST Client
 */
abstract class accesstoken extends rest
{

    /**
     * {@inheritDoc}
     * @param array $data
     * @return credential
     * @throws ReflectionException
     * @throws exception
     */
    protected function createAuthenticationCredential(array $data): credential
    {
        return new \codename\rest\credential\accesstoken($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthenticationHeaders(): array
    {
        return [
          'X-Accesskey' => $this->credential->getIdentifier(),
          'X-Token' => $this->credential->getAuthentication(),
        ];
    }


}
