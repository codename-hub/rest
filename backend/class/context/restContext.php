<?php
namespace codename\rest\context;

use codename\core\context;

/**
 * abstract REST-Context
 * just provides a function-based access to override HTTP-Method Responses
 */
abstract class restContext extends context implements restContextInterface
{
  /**
   *
   */
  public function __construct()
  {
    // reset response data
    // this is a data-only context
    if($this->getResponse() instanceof \codename\rest\response\json) {
      $this->getResponse()->reset();
    }
  }

  /**
   * [view_default description]
   * @return [type] [description]
   */
  public function view_default () {
    // empty
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
  public function method_trace()
  {
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_options()
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
}
