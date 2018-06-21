<?php
namespace codename\rest\model\schemeless\remote;
use \codename\core\model;
use \codename\core\app;
use codename\core\exception;

/**
 * model for performing/wrapping remote api queries to restCruds
 */
abstract class restcrud extends \codename\rest\model\schemeless\remote {
  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    $params = [];

    if(count($this->filter) > 0) {
      //
      // RestCrud-Style
      //
      foreach($this->filter as $f) {
        $params['filter'][] = [
          'field' => $f->field->get(),
          'value' => $f->value,
          'operator' => $f->operator,
        ];
      }
    }

    $result = $this->client->get($this->config->get('endpoint>query'), $params);

    if($result['success']) {
      return $result['data'][$result['data']['data_key'] ?? 'data'];
    } else {
      throw new exception('EXCEPTION_MODEL_SCHEMELESS_REMOTE_RESTCRUD_UNSUCCESSFUL', exception::$ERRORLEVEL_ERROR);
    }
  }
}
