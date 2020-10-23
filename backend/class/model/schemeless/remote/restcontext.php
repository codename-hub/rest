<?php
namespace codename\rest\model\schemeless\remote;

use codename\core\exception;

/**
 * model for performing/wrapping remote api queries to restCruds
 */
abstract class restcontext extends \codename\rest\model\schemeless\remote {
  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    $params = [];

    if(count($this->filter) > 0) {
      //
      // RestContext filter/filter_like style
      //
      foreach($this->filter as $f) {
        if($f->operator === '=') {
          $params['filter'][$f->field->get()] = $f->value;
        } else if($f->operator === 'LIKE') {
          $params['filter_like'][$f->field->get()] = $f->value;
        } else if($f->operator === '<=') {
          $params['filter_lte'][$f->field->get()] = $f->value;
        } else if($f->operator === '<') {
          $params['filter_lt'][$f->field->get()] = $f->value;
        } else if($f->operator === '>') {
          $params['filter_gt'][$f->field->get()] = $f->value;
        } else if($f->operator === '>=') {
          $params['filter_gte'][$f->field->get()] = $f->value;
        }
      }
    }

    $result = $this->client->get($this->config->get('endpoint>query'), $params);

    if($result['success']) {
      return $result['data'][$result['data']['data_key'] ?? 'data'];
    } else {
      throw new exception('EXCEPTION_MODEL_SCHEMELESS_REMOTE_RESTCONTEXT_UNSUCCESSFUL', exception::$ERRORLEVEL_ERROR);
    }
  }
}
