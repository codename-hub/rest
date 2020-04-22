<?php
namespace codename\rest\context\restApiContext;

use codename\core\app;
use codename\core\bootstrapInstance;

/**
 * [abstract description]
 */
abstract class apiEndpoint extends bootstrapInstance
{
  /**
   * [protected description]
   * @var \codename\core\config
   */
  protected $endpointConfig = null;

  /**
   * [__construct description]
   * @param array $endpointConfig [description]
   */
  public function __construct(array $endpointConfig)
  {
    $this->endpointConfig = new \codename\core\config($endpointConfig);
  }

  /**
   * [public description]
   * @return void
   */
  public abstract function run();

  /**
   * [isPublic description]
   * @return bool [description]
   */
  public abstract function isPublic () : bool;

  /**
   *
   * {@inheritDoc}
   * @see \codename\core\cache_interface::get($group, $key)
   */
  public function isAllowed() : bool {
      $identity = app::getSession()->identify();

      if(!$identity) {
          return false;
      }

      return $identity;
  }
}
