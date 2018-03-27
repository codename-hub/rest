<?php
namespace codename\rest\api\rest;

use \codename\core\app;

/**
 * Accesstoken-Authentication-based
 * REST Client
 */
abstract class accesstoken extends \codename\rest\api\rest {

  /**
   * @inheritDoc
   */
  protected function createAuthenticationCredential(array $data): \codename\core\credential {
    return new \codename\rest\credential\accesstoken($data);
  }

  /**
   * @inheritDoc
   */
  protected function getAuthenticationHeaders(): array
  {
    return [
      'X-Accesskey' => $this->credential->getIdentifier(),
      'X-Token'     => $this->credential->getAuthentication()
    ];
  }



}
