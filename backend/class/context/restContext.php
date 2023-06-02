<?php

namespace codename\rest\context;

use codename\core\context;
use codename\core\exception;
use codename\rest\response\json;
use LogicException;

/**
 * abstract REST-Context
 * just provides function-based access to override HTTP-Method Responses
 */
abstract class restContext extends context implements restContextInterface
{
    /**
     * @throws exception
     */
    public function __construct()
    {
        // reset response data
        // this is a data-only context
        $response = $this->getResponse();
        if ($response instanceof json) {
            $response->reset();
        }
    }

    /**
     * [view_default description]
     * @return void [type] [description]
     */
    public function view_default(): void
    {
        // empty
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
     */
    public function method_trace(): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function method_options(): void
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
}
