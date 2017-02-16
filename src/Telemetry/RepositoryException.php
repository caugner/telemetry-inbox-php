<?php

namespace Telemetry;

class RepositoryException extends \Exception {

  public function __construct($message, $code = 400, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

  public function customFunction() {
      echo "A custom function for this type of exception\n";
  }
}
