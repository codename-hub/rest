<?php
namespace codename\rest\auth;

use codename\core\exception;

/**
 * Accesskey based authentication
 * @package core
 * @since 2016-02-01
 */
abstract class accesskey extends \codename\core\auth {

  /**
   * exception thrown, if a wrong type was passed to authenticate()
   * @var string
   */
  const EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID = 'EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID';

  /**
   * @inheritDoc
   */
  public function authenticate(\codename\core\credential $credential): array
  {
    if(!$credential instanceof \codename\rest\credential\accesskey) {
      throw new exception(self::EXCEPTION_REST_AUTH_ACCESSKEY_CREDENTIAL_INVALID, exception::$ERRORLEVEL_ERROR);
    }

    // todo: validate!
    //

    return [];
  }

  /**
   * @inheritDoc
   */
  public function createCredential(array $parameters): \codename\core\credential
  {
    return new \codename\rest\credential\accesskey($parameters);
  }

  /**
   * @inheritDoc
   */
  public function memberOf(string $groupName): bool
  {
    throw new \LogicException('Not implemented'); // TODO
  }


}
