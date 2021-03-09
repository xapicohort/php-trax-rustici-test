<?php

namespace Drupal\skilling_xapi\Exception;

use Drupal\Component\Utility\Html;

/**
 * Something went wrong in Skilling code.
 */
class SkillingXapiException extends \Exception {

  /**
   * Constructor.
   *
   * @param string $message
   *   Explanation.
   * @param string $file
   *   (optional) File error was in.
   * @param int $line
   *   (optional) Line error was in.
   */
  public function __construct($message, $file = NULL, $line = NULL) {
    $message = Html::escape($message);
    $message = sprintf('Skilling xAPI exception: %s', $message);
    if (!is_null($file)) {
      $message .= "\nFile: " . $file;
    }
    if (!is_null($line)) {
      $message .= "\nLine: " . $line;
    }
    parent::__construct($message);
  }
}
