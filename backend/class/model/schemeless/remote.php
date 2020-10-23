<?php
namespace codename\rest\model\schemeless;
use \codename\core\model;
use \codename\core\app;
use codename\core\exception;

/**
 * model for performing/wrapping remote api queries
 */
abstract class remote extends \codename\core\model\schemeless\json implements \codename\core\model\modelInterface {

  /**
   * [protected description]
   * @var \codename\rest\api\rest
   */
  protected $client = null;

  /**
   * [setRestClient description]
   * @param \codename\rest\api\rest $client [description]
   */
  protected function setRestClient(\codename\rest\api\rest $client) {
    $this->client = $client;
  }

  // /**
  //  * @inheritDoc
  //  */
  // protected function loadConfig(): \codename\core\config
  // {
  //   return new \codename\core\config([]);
  // }

  /**
   * @inheritDoc
   */
  private function obsoleteInternalQuery(string $query, array $params = array())
  {
    $params = [];

    if(count($this->filter) > 0) {
      //
      // RestCrud-Style
      //
      // foreach($this->filter as $f) {
      //   $params['filter'][] = [
      //     'field' => $f->field->get(),
      //     'value' => $f->value,
      //     'operator' => $f->operator,
      //   ];
      // }

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
      throw new exception('EXCEPTION_MODEL_REMOTERESTAPIMODEL_UNSUCCESSFUL', exception::$ERRORLEVEL_ERROR);
    }
  }

}
