<?php
namespace codename\rest\response;
use codename\core\errorstack;

/**
 * I handle all the data for a JSON response
 * @package core
 */
class json extends \codename\core\response\json {

  /**
   * success state
   * @var bool
   */
  protected $success = 1;

  /**
   * [public description]
   * @var errorstack
   */
  protected $errorstack;

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT()
  {
    parent::__CONSTRUCT();
    $this->errorstack = new errorstack('error');
  }

  /**
   * @inheritDoc
   */
  protected function translateStatus()
  {
    $translate = array(
      self::STATUS_SUCCESS => 1,
      self::STATUS_INTERNAL_ERROR => 0,
      self::STATUS_NOTFOUND => 0
    );
    return $translate[$this->status];
  }

  /**
   * [getSuccess description]
   * @return bool [description]
   */
  public function getSuccess() {
    return $this->translateStatus();
  }

  /**
   * [getErrors description]
   * @return array [description]
   */
  public function getErrors() : array {
    return $this->errorstack->getErrors();
  }

  /**
   * [reset description]
   * @return \codename\core\response [description]
   */
  public function reset(): \codename\core\response {
    $this->data = [];
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function displayException(\Exception $e)
  {
    $this->setStatus(self::STATUS_INTERNAL_ERROR);

    if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT != 'production') {
      /* echo $formatter->getColoredString("Hicks", 'red') . chr(10);
      echo $formatter->getColoredString("{$e->getMessage()} (Code: {$e->getCode()})", 'yellow') . chr(10) . chr(10);

      if($e instanceof \codename\core\exception && !is_null($e->info)) {
        echo $formatter->getColoredString("Information", 'cyan') . chr(10);
        echo chr(10);
        print_r($e->info);
        echo chr(10);
      }

      echo $formatter->getColoredString("Stacktrace", 'cyan') . chr(10);
      echo chr(10);
      print_r($e->getTrace());
      echo chr(10);*/

      // print_r(json_encode($e));

      $info = null;
      if($e instanceof \codename\core\exception && !is_null($e->info)) {
        $info = $e->info;
      }

      $this->errorstack->addError($e->getMessage(), $e->getCode(), array(
        'info' => $info,
        'trace' => $e->getTrace()
      ));
      $this->pushOutput();

      die();
    } else {
      // show exception ?
    }

  }

  /**
   * @inheritDoc
   */
  public function pushOutput()
  {
    // Set correct header
    $this->setHeader('Content-Type: application/json');

    $response = array(
      'success' => $this->getSuccess(),
      'data' => $this->getData()
    );

    if(count($errors = $this->getErrors()) > 0) {
      $response['errors'] = $errors;
    }

    print_r(json_encode($response));
  }

}
