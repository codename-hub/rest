<?php

namespace codename\rest;

use codename\core\context\customContextInterface;
use codename\core\exception;
use codename\core\generator\restUrlGenerator;
use codename\core\hook;
use codename\core\request\cli;
use codename\core\response;
use codename\core\response\json;
use codename\rest\context\restContextInterface;
use ReflectionException;

/**
 * core app class
 * for creating restful applications
 * based on core framework
 */
class app extends \codename\core\app
{

    /**
     * [EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND description]
     * @var string
     */
    public const EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND = 'EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND';
    /**
     * overrides the app::isRestClient() result, if !== null
     * @var null|bool
     */
    public static ?bool $overrideIsRestClient = null;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        if (self::isRestClient()) {
            // this app class requires mod_rewrite and correct rewriting settings
            \codename\core\ui\app::setUrlGenerator(new restUrlGenerator());

            // force json response
            self::$instances['response'] = new \codename\rest\response\json();

            // force json request
            self::$instances['request'] = new request\json();
        }

        // self-inject
        self::injectApp([
          'vendor' => 'codename',
          'app' => 'rest',
          'namespace' => '\\codename\\rest',
        ]);

        parent::__construct();
    }

    /**
     * returns true, if client is requesting via REST protocol (e.g. no HTML output)
     * @return bool|null
     */
    protected static function isRestClient(): ?bool
    {
        if (self::$overrideIsRestClient !== null) {
            return self::$overrideIsRestClient;
        } else {
            //
            // NOTE: possible bad request behaviour with unknown accept-header which causes a text-exception to occur -> FE output
            // It is also possible we need to check for lowercase header (http_accept) due to HTTP2 specification
            //

            if (app::getRequest() instanceof cli) {
                // Definitely a CLI client
                return false;
            }
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                // Prefer JSON response, we assume a REST Client
                return true;
            }
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'text/html')) {
                // No explicit JSON requested, but includes text/html - assume regular browser
                return false;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                // OPTIONS request (typical for XHRs, so assume a REST Client)
                return true;
            }

            // Unknown state, falsy
            return null;
        }
    }

    /**
     * overrides the app::isRestClient() value
     * @param bool|null $state [true/false overrides, null resets]
     */
    public static function setOverrideIsRestClient(?bool $state): void
    {
        self::$overrideIsRestClient = $state;
    }

    /**
     * Replaces the response object
     * to be used for facade emulation
     *
     * @param response $response [description]
     * @return response              [description]
     */
    public static function setResponse(response $response): response
    {
        return self::$instances['response'] = $response;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function run(): void
    {
        if (static::isRestClient()) {
            $qualifier = self::getEndpointQualifier();
            static::getRequest()->addData($qualifier);
        }
        // run normally
        parent::run();
    }

    /**
     * Return the endpoint target of the request
     * @return array
     * @example $host/v1/context/view/<action>/?...
     */
    public static function getEndpointQualifier(): array
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return [];
        }
        $endpoints = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);

        // get rid of the first part of the uri (e.g. host, port, etc.)
        array_shift($endpoints);

        $ret = [];

        // get context, if defined
        $i = 0;
        if (!empty($endpoints[$i])) {
            $ret['context'] = $endpoints[$i];
        }

        // get view, if defined
        $i = 1;
        if (!empty($endpoints[$i])) {
            $ret['view'] = $endpoints[$i];
        }

        // get action, if defined
        $i = 2;
        if (!empty($endpoints[$i])) {
            $ret['action'] = $endpoints[$i];
        }

        // cancel, if there are more than 3 parts
        return $ret;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function mainRun(): void
    {
        if ($this->getContext() instanceof customContextInterface) {
            $this->doContextRun();
        } else {
            $this->doAction()->doView();
            // HTTP API Endpoint-specific method running
            if (self::isRestClient()) {
                $this->doMethod();
            }
        }
        $this->doShow()->doOutput();
    }

    /**
     * performs HTTP-Method based routines
     * @return \codename\core\app [description]
     * @throws ReflectionException
     * @throws exception
     */
    protected function doMethod(): \codename\core\app
    {
        if ($this->getContext() instanceof restContextInterface) {
            $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);

            $method = "method_$httpMethod";

            if (!method_exists($this->getContext(), $method)) {
                throw new exception(self::EXCEPTION_DOMETHOD_REQUESTEDMETHODFUNCTIONNOTFOUND, exception::$ERRORLEVEL_ERROR, $method);
            }

            $this->getContext()->$method();
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     * overridden output method
     * omit templating engines and stuff.
     */
    protected function doOutput(): void
    {
        // Fallback to default output, if client is not a REST client
        if (!self::isRestClient()) {
            parent::doOutput();
            return;
        }

        app::getResponse()->pushOutput();
    }

    /**
     * {@inheritDoc}
     */
    protected function doShow(): \codename\core\app
    {
        if (static::isRestClient() || (static::getResponse() instanceof json)) {
            // rest client output does NOT provide "show"
            return $this;
        } else {
            // Fallback to default output (no rest client)
            return parent::doShow();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function handleAccess(): bool
    {
        $context = $this->getContext();
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if ($context instanceof restContextInterface) {
                $context->method_options();
            }
            self::getResponse()->pushOutput();
            exit();
        }

        if ($context instanceof customContextInterface) {
            $isPublic = $context->isPublic();
        } else {
            $isPublic = self::getConfig()->get('context>' . static::getRequest()->getData('context') . '>view>' . static::getRequest()->getData('view') . '>public') === true;
        }

        $isAuthenticated = null;
        if (!$isPublic) {
            // perform authentication
            if (!$this->authenticate()) {
                // authentication_error
                self::getResponse()->setStatus(response::STATUS_UNAUTHENTICATED);
                $isAuthenticated = false;
            } else {
                $isAuthenticated = true;
            }
        }

        $isAllowed = $this->getContext()->isAllowed();

        if (!$isAllowed && !$isPublic) {
            self::getHook()->fire(hook::EVENT_APP_RUN_FORBIDDEN);

            if ($isAuthenticated) {
                self::getResponse()->setStatus(response::STATUS_FORBIDDEN);
            } else {
                self::getResponse()->setStatus(response::STATUS_UNAUTHENTICATED);
            }

            self::getResponse()->pushOutput();
            exit();
        } else {
            if (!$isPublic) {
                if (!$isAuthenticated) {
                    self::getResponse()->setStatus(response::STATUS_UNAUTHENTICATED);
                    self::getResponse()->pushOutput();
                    exit();
                }
            }
            return true;
        }
    }

    /**
     * handle authentication
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    protected function authenticate(): bool
    {
        return app::getAuth()->isAuthenticated();
    }

}
