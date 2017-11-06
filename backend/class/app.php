<?php
namespace codename\rest;
use codename\core\exception;

/**
 * core app class
 * for creating restful applications
 * based on core framework
 */
class app extends \codename\core\app {

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT()
  {
    // force json response
    $classname = "\\codename\\core\\response\\" . 'json';
    self::$instances['response'] = new $classname();

    // self-inject
    self::injectApp(array(
      'vendor' => 'codename',
      'app' => 'rest',
      'namespace' => '\\codename\\rest'
    ));

    $value = parent::__CONSTRUCT();
    return $value;
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    $qualifier = self::getEndpointQualifier();
    $this->getRequest()->addData($qualifier);

    // run normally
    parent::run();

    // print_r($this->getRequest()->getData());
  }

  /**
   * @inheritDoc
   */
  protected function doShow(): \codename\core\app
  {
    return $this;
  }

  /**
   * @inheritDoc
   * overridden output method
   * omit templating engines and stuff.
   */
  protected function doOutput()
  {
    // ?
    header('Content-Type: application/json');
    print_r(json_encode(app::getResponse()->getData()));
  }

  /**
   * Return the endpoint target of the request
   * @example $host/v1/context/view/<action>/?...
   * @return string
   */
  public static function getEndpointQualifier() : array {
      $endpoints =  explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);

      // get rid of the first part of the uri (e.g. host, port, etc.)
      array_shift($endpoints);

      if(count($endpoints) > 3) {
        throw new exception("CORE_REST_APP_TOO_MANY_ENDPOINT_QUALIFIERS", exception::$ERRORLEVEL_FATAL, $endpoints);
      }

      $ret = array();

      // get context, if defined
      $i = 0;
      if(isset($endpoints[$i]) && !empty($endpoints[$i])) {
        $ret['context'] = $endpoints[$i];
      }

      // get view, if defined
      $i = 1;
      if(isset($endpoints[$i]) && !empty($endpoints[$i])) {
        $ret['view'] = $endpoints[$i];
      }

      // get action, if defined
      $i = 2;
      if(isset($endpoints[$i]) && !empty($endpoints[$i])) {
        $ret['action'] = $endpoints[$i];
      }

      // cancel, if there are more than 3 parts
      return $ret;
  }

}