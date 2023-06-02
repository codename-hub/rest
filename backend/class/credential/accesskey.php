<?php

namespace codename\rest\credential;

use codename\core\credential;
use codename\core\credential\credentialInterface;

/**
 * accesskey credential
 *
 * this credential should be used for retrieving accesstokens (!)
 *
 * @package core
 * @since 2018-02-22
 */
class accesskey extends credential implements credentialInterface
{

    /**
     * validator name to be used for validating input data
     * @var string|null
     */
    protected static $validatorName = 'structure_credential_accesskey';

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
        return $this->get('secret');
    }

}
