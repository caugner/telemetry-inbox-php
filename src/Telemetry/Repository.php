<?php
namespace Telemetry;

class Repository {

  const UUID_PATTERN = '@^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$@';
  const MAX_SIZE = 1024 * 1024;
  const REQUIRED_FIELDS = ['id', 'type', 'active', 'uptime', 'counter', 'histogram', 'timing', 'value'];

  private $path;

  public function __construct(string $path) {
    $this->path = $path;
  }

  private function makeReadable() {
    if (!is_dir($this->path) && !mkdir($this->path) || !is_readable($this->path)) {
      throw new \Exception('Repository is currently not available for reading.');
    }
  }

  private function makeWritable() {
    if (!is_dir($this->path) && !mkdir($this->path) || !is_writable($this->path)) {
      throw new \Exception('Repository is currently not available for writing.');
    }
  }

  public function count() {
    $count = 0;
    foreach ($this->files() as $filepath) {
      $count++;
    }
    return $count;
  }

  public function objects() {
    foreach ($this->contents() as $content) {
      try {
        $object = json_decode($content);
        yield $object;
      } catch (Exception $e) {
        // Ignore.
      }
    }
  }

  public function contents() {
    foreach ($this->files() as $file) {
      yield file_get_contents($item);
    }
  }

  private function files() {
    $this->makeReadable();
    if (is_dir($this->path)) {
      $fh = opendir($this->path);
      while ($file = readdir($fh)) {
        if (is_file($this->path . '/' . $file)) {
          yield $this->path . '/' . $file;
        }
      }
    }
  }

  public function add($id, $source) {
    $this->makeWritable();
    if (!$this->isValidId($id)) {
      throw new RepositoryException('ID is not a valid UUID.', 400);
    }

    $content = file_get_contents($source, false, null, 0, self::MAX_SIZE + 1);

    if (strlen($content) > self::MAX_SIZE) {
        throw new RepositoryException('Data size exceeds maximum size.', 413);
    }

    $json = json_decode($content);
    if ($json == null) {
      throw new RepositoryException('Data is not valid json.', 415);
    }

    $missing_keys = [];
    foreach (self::REQUIRED_FIELD as $field) {
      if (!isset($json->$field)) {
        $missingKeys[] = $field;
      }
    }

    if (!empty($missing_keys)) {
      throw new RepositoryException('Data is missing keys: ' . implode(', ', $missing_keys), 400);
    }

    if ($json->id != $id) {
      throw new RepositoryException('ID in data does not match ID in url.', 400);
    }

    try {
      if (!is_dir($this->path)) {
        mkdir($this->path);
      }

      $filepath = sprintf("%s/%s.json", $this->path, $id);
      if (file_exists($filepath)) {
        throw new RepositoryException('Data already received.', 200);
      }

      file_put_contents($dataset_path, $data);
    } catch (Exception $e) {
      throw new RepositoryException('Failed to persist data.', 500);
    }
  }

  private function isValidId($id) {
    return preg_match(self::UUID_PATTERN, $id);
  }
}
?>
