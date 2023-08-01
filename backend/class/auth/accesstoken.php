<?php

namespace codename\rest\auth;

use codename\core\auth;
use codename\core\credential;
use codename\core\exception;
use LogicException;
use ReflectionException;

/**
 * Accesstoken based authentication
 * @package core
 * @since 2016-02-01
 */
abstract class accesstoken extends auth
{

    /**
     * exception thrown, if a wrong type was passed to authenticate()
     * @var string
     */
    public const EXCEPTION_REST_AUTH_ACCESSTOKEN_CREDENTIAL_INVALID = 'EXCEPTION_REST_AUTH_ACCESSTOKEN_CREDENTIAL_INVALID';

    /**
     * [EXCEPTION_REST_AUTH_MAKEHASH_CREDENTIAL_INVALID description]
     * @var string
     */
    public const EXCEPTION_REST_AUTH_MAKEHASH_CREDENTIAL_INVALID = 'EXCEPTION_REST_AUTH_MAKEHASH_CREDENTIAL_INVALID';

    /**
     * {@inheritDoc}
     * @param credential $credential
     * @return array
     * @throws exception
     */
    public function authenticate(credential $credential): array
    {
        if (!$credential instanceof \codename\rest\credential\accesstoken) {
            throw new exception(self::EXCEPTION_REST_AUTH_ACCESSTOKEN_CREDENTIAL_INVALID, exception::$ERRORLEVEL_ERROR);
        }

        // todo: validate!
        //

        return [];
    }

    /**
     * {@inheritDoc}
     * @param credential $credential
     * @return string
     * @throws exception
     */
    public function makeHash(credential $credential): string
    {
        if (!$credential instanceof \codename\rest\credential\accesstoken) {
            throw new exception(self::EXCEPTION_REST_AUTH_MAKEHASH_CREDENTIAL_INVALID, exception::$ERRORLEVEL_ERROR);
        }
        return $credential->getAuthentication(); // password_hash($credential->getAuthentication(), PASSWORD_BCRYPT);
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
        return new \codename\rest\credential\accesstoken($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function memberOf(string $groupName): bool
    {
        throw new LogicException('Not implemented'); // TODO
    }


}
