<?php

namespace Drupal\skilling_xapi\Exception;

/**
 * Caller tried to access a setting that is MT.
 */
class SkillingXapiEmptySettingException extends SkillingXapiException {

  /**
   * Constructs a SkillingXapiEmptySettingException
   *
   * @param string $message
   *   Message reporting error.
   * @param string $file
   *   (optional) File name where the error happened.
   * @param int $line
   *   (optional) Line number where the error happened.
   */
  public function __construct($message, $file = NULL, $line = NULL) {
    $message = sprintf("Unknown Skilling xAPI module setting. \nMessage: %s", $message);
    parent::__construct($message, $file, $line);
  }

}
