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
      self::STATUS_NOTFOUND => 0,
      self::STATUS_UNAUTHENTICATED => 0,
      self::STATUS_FORBIDDEN => 0,
      self::STATUS_REQUEST_SIZE_TOO_LARGE => 0,
      self::STATUS_BAD_REQUEST => 0,
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

    // log to stderr
    // NOTE: we log twice, as the second one might be killed
    // by memory exhaustion
    if($e instanceof \codename\core\exception && !is_null($e->info)) {
      $info = print_r($e->info, true);
    } else {
      $info = '<none>';
    }

    error_log("[SAFE ERROR LOG] "."{$e->getMessage()} (Code: {$e->getCode()}) in File: {$e->getFile()}:{$e->getLine()}, Info: {$info}");
    // error_log(print_r($e, true), 0);

    if(defined('CORE_ENVIRONMENT')
      // && CORE_ENVIRONMENT != 'production'
    ) {
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
        'trace' => !($e instanceof \codename\core\sensitiveException) ? $e->getTrace() : null
      ));
      $this->pushOutput();

      die();
    } else {
      // show exception ?
    }


    $this->pushOutput();
  }

  /**
   * @inheritDoc
   */
  public function pushOutput()
  {
    http_response_code($this->translateStatusToHttpStatus());

    // Set correct header
    $this->setHeader('Content-Type: application/json');

    $response = array(
      'success' => $this->getSuccess(),
      'data' => $this->getData()
    );

    if(count($errors = $this->getErrors()) > 0) {
      $response['errors'] = $errors;
    }

    $json = json_encode($response);

    if(($jsonLastError = json_last_error()) !== JSON_ERROR_NONE) {
      $errorResponse = [
        'success' => 0,
        'errors' => [
          json_last_error_msg()
        ]
      ];
      if($jsonLastError === JSON_ERROR_UTF8) {
        $errorResponse['erroneous_data'] = self::utf8ize($this->getData());
      } else if($jsonLastError === JSON_ERROR_UNSUPPORTED_TYPE) {
        $errorResponse = $response; // simply provide the response data again and try via partial output
        $errorResponse['partial_output'] = true;
      }

      // enable partial output to overcome recursions and some type errors
      $json = json_encode($errorResponse, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    print_r($json);
  }

  /**
   * [utf8ize description]
   * @param  [type] $mixed [description]
   * @return [type]        [description]
   */
  protected static function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = self::utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
  }
}
