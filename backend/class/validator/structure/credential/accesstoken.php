<?php

namespace codename\rest\validator\structure\credential;

use codename\core\validator\structure;

/**
 * [accesstoken description]
 */
class accesstoken extends structure
{

    /**
     * required array keys
     * @var string[]
     */
    public $arrKeys = [
      'accesskey',
      'token',
      'valid_until',
    ];

}
