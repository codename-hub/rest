<?php

namespace codename\rest\api\rest;

use codename\core\credential;
use codename\core\exception;
use codename\rest\api\rest;
use ReflectionException;

/**
 * Accesskey-Authentication-based
 * REST Client
 */
abstract class accesskey extends rest
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
        return new \codename\rest\credential\accesskey($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthenticationHeaders(): array
    {
        return [
          'X-Accesskey' => $this->credential->getIdentifier(),
          'X-Secret' => $this->credential->getAuthentication(),
        ];
    }

}
