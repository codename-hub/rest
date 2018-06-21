<?php
namespace codename\rest\context;

use codename\core\exception;

/**
 * Defines a HTTP Endpoint based on the REST Specification
 */
abstract class restcrud extends \codename\core\context implements restContextInterface {

  /**
   * Overwrite what model to use in the CRUD generator
   * @var string
   */
  protected $modelName = null;

  /**
   * Overwrite the name of the app the requested model is located
   * @var string
   */
  protected $modelApp = null;

  /**
   * Holds the model for this CRUD generator
   * @var \codename\core\model
   */
  protected $model = null;

  /**
   * instantiate a new instance of this restcrud
   */
  public function __construct()
  {
    // reset response data
    // this is a data-only context
    $this->getResponse()->reset();
  }

  /**
   * implement this function and return your model
   * @return \codename\core\model
   */
  public abstract function getModelInstance() : \codename\core\model;

  /**
   * default view.
   */
  public function view_default() {

  }

  /**
   * @inheritDoc
   */
  public function method_get()
  {
    if($this->getRequest()->isDefined('id')) {
      // SINGLE ENTRY

      $data = $this->getModelInstance()->entryLoad($this->getRequest()->getData('id'))->getData();

      // set output data
      $this->getResponse()->addData($data);

    } else {
      // List - no PKEY defined

      $model = $this->getModelInstance();

      // apply filters requested
      if($this->getRequest()->isDefined('filter')) {
        foreach($this->getRequest()->getData('filter') as $filter) {
          $model->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
        }
      }

      $data = $model->search()->getResult();

      // reset and set
      $this->getResponse()->addData($data);
    }
  }

  /**
   * @inheritDoc
   */
  public function method_head()
  {
    // META
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_post()
  {
    // CREATE / custom stuff?
    $model = $this->getModelInstance();

    $model->entryMake($this->getRequest()->getData());
    $model->entrySave();
  }

  /**
   * @inheritDoc
   */
  public function method_put()
  {
    if($this->getRequest()->isDefined('id')) {
      // update existing entry

      $model = $this->getModelInstance();

      $model->entryLoad($this->getRequest()->getData('id'));
      $model->entryUpdate($this->getRequest()->getData());
      $model->entrySave();

      $this->getResponse()->setData('debug', $model->getData());
      // set output data
      // $this->getResponse()->addData($data);

    } else {

      // create a new entry
      // may contain a primary key value, though
      $model = $this->getModelInstance();

      $model->entryMake($this->getRequest()->getData());
      $model->entrySave();

      // reset and set
      // $this->getResponse()->addData($data);
    }
  }

  /**
   * @inheritDoc
   */
  public function method_delete()
  {
    // DELETE
    // throw new \LogicException('Not implemented'); // TODO
    if($this->getRequest()->isDefined('id')) {
      $model = $this->getModelInstance();
      $model->entryLoad($this->getRequest()->getData('id'));
      $model->entryDelete();
    } else {
      // error: no id provided
      throw new exception(self::EXCEPTION_REST_METHOD_NO_ID_PROVIDED, exception::$ERRORLEVEL_ERROR);
    }
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
    // show methods available
    throw new \LogicException('Not implemented'); // TODO
  }

  /**
   * @inheritDoc
   */
  public function method_patch()
  {
    // EDIT
    if($this->getRequest()->isDefined('id')) {
      $model = $this->getModelInstance();
      $model->entryLoad($this->getRequest()->getData('id'));
      $model->entryUpdate($this->getRequest()->getData());
      $model->entrySave();
    } else {
      // error: no id provided
      throw new exception(self::EXCEPTION_REST_METHOD_NO_ID_PROVIDED, exception::$ERRORLEVEL_ERROR);
    }
  }

  /**
   * [EXCEPTION_REST_METHOD_NO_ID_PROVIDED description]
   * @var string
   */
  const EXCEPTION_REST_METHOD_NO_ID_PROVIDED = 'EXCEPTION_REST_METHOD_NO_ID_PROVIDED';

}
