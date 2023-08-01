<?php

namespace codename\rest\model\schemeless;

use codename\core\model\modelInterface;
use codename\core\model\schemeless\json;
use codename\rest\api\rest;

/**
 * model for performing/wrapping remote api queries
 */
abstract class remote extends json implements modelInterface
{

    /**
     * [protected description]
     * @var rest
     */
    protected rest $client;

    /**
     * [setRestClient description]
     * @param rest $client [description]
     */
    protected function setRestClient(rest $client): void
    {
        $this->client = $client;
    }

}
