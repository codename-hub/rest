<?php

namespace codename\rest\context\restApiContext;

use codename\core\app;
use codename\core\bootstrapInstance;
use codename\core\config;
use codename\core\exception;
use codename\rest\context\restContextInterface;
use LogicException;
use ReflectionException;

/**
 * [abstract description]
 */
abstract class apiEndpoint extends bootstrapInstance implements restContextInterface
{
    /**
     * [protected description]
     * @var config
     */
    protected config $endpointConfig;

    /**
     * [__construct description]
     * @param array $endpointConfig [description]
     */
    public function __construct(array $endpointConfig)
    {
        $this->endpointConfig = new config($endpointConfig);
    }

    /**
     * [public description]
     * @return void
     */
    public function run(): void
    {
        $httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $method = "method_$httpMethod";
        $this->$method();
    }

    /**
     * {@inheritDoc}
     */
    public function method_get(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_head(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_post(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_put(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_delete(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     * @throws exception
     */
    public function method_options(): void
    {
        if (count($headers = $this->getAllowedHeaders()) > 0) {
            $this->getResponse()->setHeader('Access-Control-Allow-Headers: ' . implode(', ', $headers));
        }
    }

    /**
     * [getAllowedHeaders description]
     * @return array [description]
     */
    protected function getAllowedHeaders(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function method_trace(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_patch(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * [isPublic description]
     * @return bool [description]
     */
    abstract public function isPublic(): bool;

    /**
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\cache_interface::get($group, $key)
     */
    public function isAllowed(): bool
    {
        $identity = app::getSession()->identify();

        if (!$identity) {
            return false;
        }

        return $identity;
    }
}
