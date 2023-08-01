<?php

namespace codename\rest\context;

use codename\core\app;
use codename\core\context;
use codename\core\context\customContextInterface;
use codename\core\exception;
use codename\core\response;
use codename\rest\context\restApiContext\apiEndpoint;
use codename\rest\response\json;
use ReflectionClass;
use ReflectionException;

abstract class restApiContext extends context implements customContextInterface
{
    /**
     * @throws exception
     */
    public function __construct()
    {
        // reset response data
        // this is a data-only context
        if ($this->getResponse() instanceof json) {
            $this->getResponse()->reset();
        }
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    public function run(): void
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        $endpoints = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);

        // get rid of the first part of the uri (e.g. host, port, etc.)
        array_shift($endpoints);

        $shortName = (new ReflectionClass($this))->getShortName();

        if (($entryPoint = array_shift($endpoints)) == $shortName) {
            do {
                $lookup = $entryPoint . '_' . implode('_', $endpoints);

                try {
                    $class = $this->getApiEndpointClass('context_' . $lookup);
                } catch (\Exception) {
                    continue;
                }

                $endpointConfig = [
                  'endpoint_components' => $endpoints,
                ];

                $instance = new $class($endpointConfig);
                if ($instance instanceof apiEndpoint) {
                    if (strtolower($_SERVER['REQUEST_METHOD'] ?? '') === 'options') {
                        $instance->method_options();
                        return;
                    }
                    if (!$instance->isPublic()) {
                        if (!app::getAuth()->isAuthenticated()) {
                            $this->getResponse()->setStatus(response::STATUS_UNAUTHENTICATED);
                            return;
                        }
                        if (!$instance->isAllowed()) {
                            $this->getResponse()->setStatus(response::STATUS_FORBIDDEN);
                            return;
                        }
                    }
                    $instance->run();
                    return;
                }
            } while ($endpoints = array_slice($endpoints, 0, -1));
        }

        throw new exception('EXCEPTION_RESTAPICONTEXT_INVALID_ENTRY_POINT', exception::$ERRORLEVEL_FATAL);
    }

    /**
     * [getApiEndpointClass description]
     * @param string $classname [description]
     * @return string            [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function getApiEndpointClass(string $classname): string
    {
        $classname = str_replace('_', '\\', $classname);
        $file = str_replace('\\', '/', $classname);
        foreach (app::getAppstack() as $parentapp) {
            // do not traverse, check for current app
            if ($parentapp['app'] == app::getApp()) {
                $filename = CORE_VENDORDIR . $parentapp['vendor'] . '/' . $parentapp['app'] . '/backend/class/' . $file . '.php';
                if (app::getInstance('filesystem_local')->fileAvailable($filename)) {
                    $namespace = $parentapp['namespace'] ?? '\\' . $parentapp['vendor'] . '\\' . $parentapp['app'];
                    return $namespace . '\\' . $classname;
                }
            }
        }
        throw new exception('EXCEPTION_RESTAPICONTEXT_INVALID_ENDPOINT', exception::$ERRORLEVEL_FATAL);
    }
}
