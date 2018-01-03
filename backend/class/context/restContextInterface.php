<?php
namespace codename\rest\context;

/**
 * Defines a HTTP Endpoint based on the REST Specification
 */
interface restContextInterface {

  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
  // https://de.wikipedia.org/wiki/Representational_State_Transfer

  /**
   * GET
   */
  public function method_get();

  /**
   * HEAD
   */
  public function method_head();

  /**
   * POST
   */
  public function method_post();

  /**
   * PUT
   */
  public function method_put();

  /**
   * DELETE
   */
  public function method_delete();

  /**
   * CONNTEXT
   */
  // public function method_connect();

  /**
   * OPTIONS
   */
  public function method_options();

  /**
   * TRACE
   */
  public function method_trace();

  /**
   * PATCH
   */
  public function method_patch();

}