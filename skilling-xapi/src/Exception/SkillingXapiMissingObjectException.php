<?php

namespace Drupal\skilling_xapi\Exception;

/**
 * Caller tried to use an unknown verb.
 */
class SkillingXapiMissingObjectException extends SkillingXapiException {

  /**
   * Constructs a SkillingXapiEmptyObjectException
   *
   * @param string $message
   *   Message reporting error.
   * @param string $file
   *   (optional) File name where the error happened.
   * @param int $line
   *   (optional) Line number where the error happened.
   */
  public function __construct($message, $file = NULL, $line = NULL) {
    $message = sprintf("xAPI object is empty. \nMessage: %s", $message);
    parent::__construct($message, $file, $line);
  }

}
