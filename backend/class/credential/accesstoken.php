<?php
namespace codename\rest\credential;

/**
 * accesstoken credential
 *
 * this credential is used for accessing services protected by this authentication credential type
 *
 * @package core
 * @since 2018-02-22
 */
class accesstoken extends \codename\core\credential implements \codename\core\credential\credentialInterface {

  /**
   * validator name to be used for validating input data
   * @var string|null
   */
  protected static $validatorName = 'structure_credential_accesstoken';

  /**
   * @inheritDoc
   */
  public function getIdentifier(): string
  {
    return $this->get('accesskey');
  }

  /**
   * @inheritDoc
   */
  public function getAuthentication()
  {
    return $this->get('token');
  }

}
