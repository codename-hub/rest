<?php
namespace codename\rest\context;

use codename\core\app;
use codename\core\context;

use codename\core\context\customContextInterface;

use codename\core\exception;

abstract class restApiContext extends context implements customContextInterface{
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
   * @inheritDoc
   */
  public function run()
  {
    if(!isset($_SERVER['REQUEST_URI'])) {
      return [];
    }
    $endpoints =  explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);

    // get rid of the first part of the uri (e.g. host, port, etc.)
    array_shift($endpoints);

    $shortName = (new \ReflectionClass($this))->getShortName();

    if(($entryPoint = array_shift($endpoints)) == $shortName) {
      $lookup = $entryPoint;
      $endpointComponents = [];

      while($endpoint = array_shift($endpoints)) {
        $lookup .= '_'.$endpoint;

        try {
          $class = app::getInheritedClass('context_'.$lookup);
        } catch (\Exception $e) {
          continue;
        }

        $endpointConfig = [
          'endpoint_components' => $endpoints
        ];

        $instance = new $class($endpointConfig);
        if($instance instanceof \codename\rest\context\restApiContext\apiEndpoint) {
          $instance->run();
          return;
        }
      }
    }

    throw new exception('EXCEPTION_RESTAPICONTEXT_INVALID_ENTRY_POINT', exception::$ERRORLEVEL_FATAL);
  }
}
