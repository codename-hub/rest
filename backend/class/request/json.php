<?php
namespace codename\rest\request;

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

      $headers = getallheaders();
      if(isset($headers['X-Content-Type']) && $headers['X-Content-Type'] == 'application/vnd.core.form+json+formdata') {
        $this->addData(json_decode($_POST['json'], true) ?? []);
        $this->addData($_POST['formdata'] ?? []);
        // add files?
        $this->files = static::normalizeFiles($_FILES)['formdata'] ?? [];
      } else {
        $this->addData($_POST ?? []);
      }

      // print_r($this);
      // \codename\core\app::getResponse()->setData('dbg', [
      //   'data' => $this->getData(),
      //   'files' => $this->getFiles()
      // ]);

      $body = file_get_contents('php://input');
      $data = json_decode($body, true);
      $this->addData($data ?? []);
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
