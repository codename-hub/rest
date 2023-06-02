<?php

namespace codename\rest\validator\structure\credential;

use codename\core\validator\structure;

/**
 * [accesskey description]
 */
class accesskey extends structure
{

    /**
     * required array keys
     * @var string[]
     */
    public $arrKeys = [
      'accesskey',
      'secret',
    ];

}
