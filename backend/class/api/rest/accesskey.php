<?php
namespace codename\rest\api\rest;

use \codename\core\app;

/**
 * Accesskey-Authentication-based
 * REST Client
 */
abstract class accesskey extends \codename\rest\api\rest {

  /**
   * @inheritDoc
   */
  protected function createAuthenticationCredential(array $data): \codename\core\credential {
    return new \codename\rest\credential\accesskey($data);
  }

  /**
   * @inheritDoc
   */
  protected function getAuthenticationHeaders(): array
  {
    return [
      'X-Accesskey' => $this->credential->getIdentifier(),
      'X-Secret'    => $this->credential->getAuthentication()
    ];
  }

}
