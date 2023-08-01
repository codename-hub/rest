<?php

namespace codename\rest\context;

/**
 * Defines an HTTP Endpoint based on the REST Specification
 */
interface restContextInterface
{

    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
    // https://de.wikipedia.org/wiki/Representational_State_Transfer

    /**
     * GET
     */
    public function method_get(): void;

    /**
     * HEAD
     */
    public function method_head(): void;

    /**
     * POST
     */
    public function method_post(): void;

    /**
     * PUT
     */
    public function method_put(): void;

    /**
     * DELETE
     */
    public function method_delete(): void;

    /**
     * CONTEXT
     */
    // public function method_connect(): void;

    /**
     * OPTIONS
     */
    public function method_options(): void;

    /**
     * TRACE
     */
    public function method_trace(): void;

    /**
     * PATCH
     */
    public function method_patch(): void;

}