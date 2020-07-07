<?php
namespace codename\rest\context\restApiContext;

use codename\core\app;
use codename\core\bootstrapInstance;

/**
 * [abstract description]
 */
abstract class apiEndpoint extends bootstrapInstance implements \codename\rest\context\restContextInterface
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
  public function run() {
    $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
    $method = "method_{$httpMethod}";
    $this->$method();
  }

  /**
   * @inheritDoc
   */
  public function method_get()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_head()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_post()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_put()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_delete()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_options()
  {
    if(count($headers = $this->getAllowedHeaders()) > 0) {
      $this->getResponse()->setHeader('Access-Control-Allow-Headers: '.implode(', ', $headers));
    }
  }

  /**
   * [getAllowedHeaders description]
   * @return array [description]
   */
  protected function getAllowedHeaders () : array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function method_trace()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_patch()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

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
