<?php
namespace codename\rest\request;

/**
 * I handle all the data for a HTTP request with Content-Type application/json
 * @package rest
 * @since 2017-06-27
 */
class json extends \codename\core\request {

    /**
     * @inheritDoc
     */
    public function __CONSTRUCT()
    {
      $this->datacontainer = new \jocoon\joBase\datacontainer(array());
      $this->addData($_GET);
      $this->addData($_POST);
      $body = file_get_contents('php://input');
      $data = json_decode($body, true);
      $this->addData($data);
      $this->setData('lang', "de_DE");
      return $this;
    }
}
