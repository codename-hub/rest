<?php
namespace codename\rest\model;

/**
 * Model interface
 * that states the current model is available through an external API
 */
interface exposesRemoteApiInterface {

  /**
   * returns the api endpoint the model it queryable through
   * @return string [description]
   */
  function getExposedApiEndpoint() : string;

}
