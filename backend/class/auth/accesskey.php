<?php

namespace codename\rest\auth;

use codename\core\auth;
use codename\core\credential;
use codename\core\exception;
use LogicException;
use ReflectionException;

/**
 * Accesskey based authentication
 * @package core
 * @since 2016-02-01
 */
abstract class accesskey extends auth
{

    /**
     * exception thrown, if a wrong type was passed to authenticate()
     * @var string
     */
    public const EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID = 'EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID';

    /**
     * {@inheritDoc}
     * @param credential $credential
     * @return array
     * @throws exception
     */
    public function authenticate(credential $credential): array
    {
        if (!$credential instanceof \codename\rest\credential\accesskey) {
            throw new exception(self::EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID, exception::$ERRORLEVEL_ERROR);
        }

        // todo: validate!
        //

        return [];
    }

    /**
     * {@inheritDoc}
     * @param array $parameters
     * @return credential
     * @throws ReflectionException
     * @throws exception
     */
    public function createCredential(array $parameters): credential
    {
        return new \codename\rest\credential\accesskey($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function memberOf(string $groupName): bool
    {
        throw new LogicException('Not implemented'); // TODO
    }


}
