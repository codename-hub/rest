<?php

namespace codename\rest\credential;

use codename\core\credential;
use codename\core\credential\credentialExpiryInterface;
use codename\core\credential\credentialInterface;

/**
 * accesstoken credential
 *
 * this credential is used for accessing services protected by this authentication credential type
 *
 * @package core
 * @since 2018-02-22
 */
class accesstoken extends credential implements credentialInterface, credentialExpiryInterface
{

    /**
     * validator name to be used for validating input data
     * @var string|null
     */
    protected static $validatorName = 'structure_credential_accesstoken';

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->get('accesskey');
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthentication(): mixed
    {
        return $this->get('token');
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiry(): mixed
    {
        return $this->get('valid_until');
    }

}
