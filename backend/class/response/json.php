<?php

namespace codename\rest\response;

use codename\core\errorstack;
use codename\core\exception;
use codename\core\response;
use codename\core\sensitiveException;

/**
 * I handle all the data for a JSON response
 * @package core
 */
class json extends response\json
{

    /**
     * success state
     * @var int|bool
     */
    protected int|bool $success = 1;

    /**
     * [public description]
     * @var errorstack
     */
    protected errorstack $errorstack;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->errorstack = new errorstack('error');
    }

    /**
     * [reset description]
     * @return response [description]
     */
    public function reset(): response
    {
        $this->data = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function displayException(\Exception $e): void
    {
        $this->setStatus(self::STATUS_INTERNAL_ERROR);

        // log to stderr
        // NOTE: we log twice, as the second one might be killed
        // by memory exhaustion
        if ($e instanceof exception && !is_null($e->info)) {
            $info = print_r($e->info, true);
        } else {
            $info = '<none>';
        }

        error_log("[SAFE ERROR LOG] " . "{$e->getMessage()} (Code: {$e->getCode()}) in File: {$e->getFile()}:{$e->getLine()}, Info: $info");

        if (defined('CORE_ENVIRONMENT')) {
            $info = null;
            if ($e instanceof exception && !is_null($e->info)) {
                $info = $e->info;
            }

            $this->errorstack->addError($e->getMessage(), $e->getCode(), [
              'info' => $info,
              'trace' => !($e instanceof sensitiveException) ? $e->getTrace() : null,
            ]);
            $this->pushOutput();

            die();
        } else {
            // show exception ?
        }


        $this->pushOutput();
    }

    /**
     * {@inheritDoc}
     */
    public function pushOutput(): void
    {
        http_response_code($this->translateStatusToHttpStatus());

        // Set correct header
        $this->setHeader('Content-Type: application/json');

        $response = [
          'success' => $this->getSuccess(),
          'data' => $this->getData(),
        ];

        if (count($errors = $this->getErrors()) > 0) {
            $response['errors'] = $errors;
        }

        $json = json_encode($response);

        if (($jsonLastError = json_last_error()) !== JSON_ERROR_NONE) {
            $errorResponse = [
              'success' => 0,
              'errors' => [
                json_last_error_msg(),
              ],
            ];
            if ($jsonLastError === JSON_ERROR_UTF8) {
                $errorResponse['erroneous_data'] = self::utf8ize($this->getData());
            } elseif ($jsonLastError === JSON_ERROR_UNSUPPORTED_TYPE) {
                $errorResponse = $response; // simply provide the response data again and try via partial output
                $errorResponse['partial_output'] = true;
            }

            // enable partial output to overcome recursions and some type errors
            $json = json_encode($errorResponse, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }

        print_r($json);
    }

    /**
     * @return bool|int
     */
    public function getSuccess(): bool|int
    {
        return $this->translateStatus();
    }

    /**
     * {@inheritDoc}
     */
    protected function translateStatus(): int
    {
        $translate = [
          self::STATUS_SUCCESS => 1,
          self::STATUS_INTERNAL_ERROR => 0,
          self::STATUS_NOTFOUND => 0,
          self::STATUS_UNAUTHENTICATED => 0,
          self::STATUS_FORBIDDEN => 0,
          self::STATUS_REQUEST_SIZE_TOO_LARGE => 0,
          self::STATUS_BAD_REQUEST => 0,
        ];
        return $translate[$this->status];
    }

    /**
     * [getErrors description]
     * @return array [description]
     */
    public function getErrors(): array
    {
        return $this->errorstack->getErrors();
    }

    /**
     * @param mixed $mixed
     * @return mixed
     */
    protected static function utf8ize(mixed $mixed): mixed
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }
}
