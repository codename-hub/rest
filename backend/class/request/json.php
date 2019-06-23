<?php
namespace codename\rest\request;

use codename\core\exception;

/**
 * I handle all the data for a HTTP request with Content-Type application/json
 * @package rest
 * @since 2017-06-27
 */
class json extends \codename\core\request implements \codename\core\request\filesInterface {

    /**
     * @inheritDoc
     */
    public function __CONSTRUCT()
    {
      $this->datacontainer = new \codename\core\datacontainer(array());
      $this->addData($_GET ?? []);



      //
      // NOTE: [CODENAME-446] HTTP Headers should be handled lowercase/case-insensitive
      //
      $headers = array_change_key_case(getallheaders(), CASE_LOWER);

      if(isset($headers['x-content-type']) && $headers['x-content-type'] == 'application/vnd.core.form+json+formdata') {
        //
        // special request content type defined by us.
        // which allows JSON+Formdata (Object data mixed with binary uploads)
        //
        $this->addData(json_decode($_POST['json'], true) ?? []);
        $this->addData($_POST['formdata'] ?? []);
        // add files?
        $this->files = static::normalizeFiles($_FILES)['formdata'] ?? [];
      } else if(!empty($_POST) || !empty($_FILES)) {
        //
        // "regular" post request
        //
        $this->files = static::normalizeFiles($_FILES) ?? [];
        $this->addData($_POST ?? []);

        //
        // pure json payload parts, if possible?
        //
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        $this->addData($data ?? []);
      } else {
        //
        // pure json payload
        // as fallback
        //
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        $this->addData($data ?? []);
      }

      //
      // Temporary solution:
      // If we're receiving a request that exceed a limit
      // defined through server config or php config
      // simply kill it with fire and 413.
      //
      if (($_SERVER['REQUEST_METHOD'] === 'POST')
          && empty($_POST)
          && empty($_FILES)
          && empty($data)
          && ($_SERVER['CONTENT_LENGTH'] > 0)
      ) {
        \codename\core\app::getResponse()->setStatus(\codename\core\response::STATUS_REQUEST_SIZE_TOO_LARGE);
        \codename\core\app::getResponse()->reset();
        \codename\core\app::getResponse()->pushOutput();
        exit();
      }

      $this->setData('lang', $this->getData('lang') ?? "de_DE");
      return $this;
    }

    /**
     * files from request
     * @var array
     */
    protected $files = [];

    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
      return $this->files;
    }

    /**
     * Normalize uploaded files
     *
     * Transforms each value into an UploadedFileInterface instance, and ensures
     * that nested arrays are normalized.
     *
     * @param array $files
     * @return array
     * @throws \InvalidArgumentException for unrecognized values
     */
    public static function normalizeFiles(array $files)
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            // if ($value instanceof \UploadedFileInterface) {
            //     $normalized[$key] = $value;
            //     continue;
            // }
            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
                continue;
            }
            if (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            }
            throw new \InvalidArgumentException('Invalid value in files specification');
        }
        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     * @return array  // |UploadedFileInterface
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }
        return $value;
        // return new UploadedFile(
        //     $value['tmp_name'],
        //     $value['size'],
        //     $value['error'],
        //     $value['name'],
        //     $value['type']
        // );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @param array $files
     * @return array // UploadedFileInterface[]
     */
    private static function normalizeNestedFileSpec(array $files = [])
    {
        $normalizedFiles = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }
        return $normalizedFiles;
    }
}
