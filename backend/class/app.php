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
    if(self::isRestClient()) {
      // this app class requires mod_rewrite and correct rewriting settings
      \codename\core\ui\app::setUrlGenerator(new \codename\core\generator\restUrlGenerator());

      // force json response
      self::$instances['response'] = new \codename\rest\response\json();

      // force json request
      self::$instances['request'] = new \codename\rest\request\json();
    }
    
    // self-inject
    self::injectApp(array(
      'vendor' => 'codename',
      'app' => 'rest',
      'namespace' => '\\codename\\rest'
    ));

    parent::__CONSTRUCT();
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    if(self::isRestClient()) {
      $qualifier = self::getEndpointQualifier();
      $this->getRequest()->addData($qualifier);
    }
    // run normally
    parent::run();
  }

  /**
   * @inheritDoc
   */
  protected function mainRun()
  {
    if($this->getContext() instanceof \codename\core\context\customContextInterface) {
      $this->doContextRun();
    } else {
      $this->doAction()->doView();
      // HTTP API Endpoint-specific method running
      if(self::isRestClient()) {
        $this->doMethod();
      }
    }
    $this->doShow()->doOutput();
  }

  /**
   * @inheritDoc
   */
  protected function doShow(): \codename\core\app
  {
    if(self::isRestClient()) {
      // rest client output does NOT provide "show"
      return $this;
    } else {
      // Fallback to default output (no rest client)
      return parent::doShow();
    }
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
   * returns true, if client is requesting via REST protocol (e.g. no HTML output)
   * @return bool [description]
   */
  protected static function isRestClient() : bool {
    if(self::$overrideIsRestClient !== null) {
      return self::$overrideIsRestClient;
    } else {
      //
      // NOTE: possible bad request behaviour with unknown accept-header which causes a text-exception to occur -> FE output
      // It is also possible we need to check for lowercase header (http_accept) due to HTTP2 specification
      //
      return (app::getRequest() instanceof \codename\core\request\cli) ? false : !(strpos($_SERVER['HTTP_ACCEPT'] ?? '','text/html') !== false);
    }
  }

  /**
   * overrides the app::isRestClient() result, if !== null
   * @var bool
   */
  public static $overrideIsRestClient = null;

  /**
   * overrides the app::isRestClient() value
   * @param bool|null   $state [true/false overrides, null resets]
   */
  public static function setOverrideIsRestClient(?bool $state) {
    self::$overrideIsRestClient = $state;
  }

  /**
   * handle authentication
   * @return bool
   */
  protected function authenticate() : bool {
    return app::getAuth()->isAuthenticated();
  }

  /**
   * @inheritDoc
   */
  protected function handleAccess(): bool
  {
    if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

        if(!($this->getContext() instanceof \codename\rest\context\restContextInterface)) {
          // this a REST Preflight request. Kill it.
          self::getResponse()->pushOutput();
          exit();
        } else {
          $this->getContext()->method_options();
          self::getResponse()->pushOutput();
          exit();
        }

    }

    if($this->getContext() instanceof \codename\core\context\customContextInterface) {
      $isPublic = $this->getContext()->isPublic();
    } else {
      $isPublic = self::getConfig()->get("context>{$this->getRequest()->getData('context')}>view>{$this->getRequest()->getData('view')}>public") === true;
    }

    $isAuthenticated = null;
    if(!$isPublic) {
      // perform authentication
      if(!$this->authenticate()) {
        // authentication_error
        self::getResponse()->setStatus(\codename\core\response::STATUS_UNAUTHENTICATED);
        self::getResponse()->setData('dbg_rest::handleAccess_1', true);
        $isAuthenticated = false;
      } else {
        $isAuthenticated = true;
      }
    }

    $isAllowed = $this->getContext()->isAllowed();

    if(!$isAllowed && !$isPublic) {
        self::getHook()->fire(\codename\core\hook::EVENT_APP_RUN_FORBIDDEN);

        if($isAuthenticated) {
          self::getResponse()->setStatus(\codename\core\response::STATUS_FORBIDDEN);
          // self::getResponse()->setData('session_debug', [
          //   'sess' => self::getSession()->getData(),
          //   'is_allowed' => $isAllowed,
          //   'is_public' => $isPublic
          // ]);
        } else {
          self::getResponse()->setStatus(\codename\core\response::STATUS_UNAUTHENTICATED);
          self::getResponse()->setData('dbg_rest::handleAccess_2', true);
        }

        self::getResponse()->pushOutput();
        exit();
        return false;
    } else {

      if(!$isPublic) {
        if(!$isAuthenticated) {
          self::getResponse()->setStatus(\codename\core\response::STATUS_UNAUTHENTICATED);
          self::getResponse()->setData('dbg_rest::handleAccess_3', true);
          self::getResponse()->pushOutput();
          exit();
        }
      }
      // self::getResponse()->setData('auth_debug', [
      //   'context::isAllowed' => $isAllowed,
      //   'public' => $isPublic
      // ]);

      // if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      //     // this a REST Preflight request. Kill it.
      //     self::getResponse()->pushOutput();
      //     exit();
      // }
      return true;
    }
  }

  /**
   * @inheritDoc
   * overridden output method
   * omit templating engines and stuff.
   */
  protected function doOutput()
  {
    // Fallback to default output, if client is not a REST client
    if(!self::isRestClient()) {
      parent::doOutput();
      return;
    }

    app::getResponse()->pushOutput();
    return;

    // ?
    // app::getResponse()->setHeader('Content-Type: application/json');
    //
    // $response = array(
    //   'success' => app::getResponse()->getSuccess(),
    //   'data' => app::getResponse()->getData()
    // );
    //
    // if(count($errors = app::getResponse()->getErrors()) > 0) {
    //   $response['errors'] = $errors;
    // }
    //
    // $json = json_encode($response);
    //
    // if(json_last_error() !== JSON_ERROR_NONE) {
    //   $errorResponse = [
    //     'success' => 0,
    //     'errors' => [
    //       json_last_error_msg()
    //     ]
    //   ];
    //   $json = json_encode($errorResponse);
    // }
    //
    // print_r($json);
  }

  /**
   * Return the endpoint target of the request
   * @example $host/v1/context/view/<action>/?...
   * @return array
   */
  public static function getEndpointQualifier() : array {
      if(!isset($_SERVER['REQUEST_URI'])) {
        return [];
      }
      $endpoints =  explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);

      // get rid of the first part of the uri (e.g. host, port, etc.)
      array_shift($endpoints);

      // if(count($endpoints) > 3) {
      //   throw new exception("CORE_REST_APP_TOO_MANY_ENDPOINT_QUALIFIERS", exception::$ERRORLEVEL_FATAL, $endpoints);
      // }

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
