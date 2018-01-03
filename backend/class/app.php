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
    // this app class requires mod_rewrite and correct rewriting settings
    \codename\core\ui\app::setUrlGenerator(new \codename\core\generator\restUrlGenerator());

    // force json response
    self::$instances['response'] = new \codename\rest\response\json();

    // force json request
    self::$instances['request'] = new \codename\rest\request\json();

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
  }

  /**
   * @inheritDoc
   */
  protected function mainRun()
  {
    // HTTP API Endpoint-specific method running

    $this->doMethod();

    parent::mainRun();
  }

  /**
   * @inheritDoc
   */
  protected function doShow(): \codename\core\app
  {
    return $this;
  }

  /**
   * performs HTTP-Method based routines
   * @return \codename\core\app [description]
   */
  protected function doMethod(): \codename\core\app
  {

    if($this->getContext() instanceof \codename\rest\context\restContextInterface) {
      $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);

      $method = "method_{$httpMethod}";

      if (!method_exists($this->getContext(), $method)) {
          throw new \codename\core\exception(self::EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND, \codename\core\exception::$ERRORLEVEL_ERROR, $method);
      }

      $this->getContext()->$method();
    }
    return $this;
  }

  /**
   * [EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND description]
   * @var string
   */
  const EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND = 'EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND';

  /**
   * @inheritDoc
   * overridden output method
   * omit templating engines and stuff.
   */
  protected function doOutput()
  {
    // ?
    app::getResponse()->setHeader('Content-Type: application/json');

    $response = array(
      'success' => app::getResponse()->getSuccess(),
      'data' => app::getResponse()->getData()
    );

    if(count($errors = app::getResponse()->getErrors()) > 0) {
      $response['errors'] = $errors;
    }

    print_r(json_encode($response));
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