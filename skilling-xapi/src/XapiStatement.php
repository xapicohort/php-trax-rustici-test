<?php

namespace Drupal\skilling_xapi;

use Drupal;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\skilling\SkillingClass\SkillingCurrentClass;
use Drupal\skilling\SkillingCurrentUser;
use Drupal\skilling_xapi\Exception\SkillingXapiEmptySettingException;
use Drupal\skilling_xapi\Exception\SkillingXapiMissingObjectException;
use Drupal\skilling_xapi\Exception\SkillingXapiMissingVerbException;
use Drupal\skilling_xapi\Exception\SkillingXapiUnknownVerbException;
use TinCan\Agent;
use TinCan\AgentAccount;
use TinCan\Context;
use TinCan\Extensions;
use TinCan\RemoteLRS;
use TinCan\Statement;
use TinCan\Util;
use TinCan\Verb;
use Drupal\skilling\Utilities as SkillingUtilities;

class XapiStatement {

  use StringTranslationTrait;

  /**
   * Drupal\skilling\SkillingCurrentUser definition.
   *
   * @var \Drupal\skilling\SkillingCurrentUser
   */
  protected $currentUser;

  /**
   * Current class service.
   *
   * @var SkillingCurrentClass
   */
  protected $currentClass;

  /**
   * @var \Drupal\skilling\Utilities
   */
  protected $skillingUtilities;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;


  protected $configSettings;

  /**
   * The entire xAPI statement.
   *
   * @var \TinCan\Statement
   */
  protected $statement = null;

  /**
   * The xAPI actor object.
   *
   * @var \TinCan\Agent
   */
  protected $actor = null;

  /**
   * xAPI verb.
   *
   * @var \TinCan\Verb();
   */
  protected $verb = null;

  /**
   * @var /TinCan\Activity()
   */
  protected $object = null;

  /**
   * The xAPI context object.
   *
   * @var \TinCan\Context
   */
  protected $context = null;

  /**
   * xAPI result.
   *
   * @var \TinCan\Result
   */
  protected $result = null;


  public function __construct(
    SkillingCurrentUser $currentUser,
    SkillingCurrentClass $currentClass,
    SkillingUtilities $skillingUtilities,
    ConfigFactory $configFactory
  ) {
    $this->currentUser = $currentUser;
    $this->currentClass = $currentClass;
    $this->skillingUtilities = $skillingUtilities;
    $this->configFactory = $configFactory;
    // Load the settings for this module.
    /** @var /Drupal/ImmutableConfig $settings */
    $this->configSettings = $this->configFactory->get(
      SkillingXapiConstants::SETTINGS_MAIN_KEY
    );
  }

  /**
   * Makes and saves a xAPI statement. Caller must have created object, and
   * maybe result, before calling this method.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiMissingObjectException
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiMissingVerbException
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiEmptySettingException
   */
  public function saveXapiStatement() {
    // Is there a verb?
    if (is_null($this->verb)) {
      throw new SkillingXapiMissingVerbException(
        'Verb must be set before saving xAPI statement.',
        __FILE__, __LINE__
      );
    }
    // Is there an object? Usually an activity.
    if (is_null($this->object)) {
      throw new SkillingXapiMissingObjectException(
        'Object must be set before saving xAPI statement.',
        __FILE__, __LINE__
      );
    }
    // Make invariant statement parts.
    $this->makeActorObject();
    $this->makeContextObject();
    // Build the statement.
    $this->statement = new Statement();
    $this->statement->setActor($this->actor);
    $this->statement->setVerb($this->verb);
    $this->statement->setObject($this->object);
    $this->statement->setContext($this->context);
    $timestamp = Util::getTimestamp();
    $this->statement->setTimestamp($timestamp);
    // Add result object, if there is one.
    if (! is_null($this->result)) {
      $this->statement->setResult($this->result);
    }
    // Make LRS connection.
    $lrsConnection = $this->makeLrsConnection();
    if (! is_null($lrsConnection)) {
      $response = $lrsConnection->saveStatement($this->statement);
      if (!$response->success) {
        // Log failure.
        $message = 'Failure calling LRS. ' . print_r($response->content, TRUE);
        Drupal::logger(SkillingXapiConstants::MODULE_NAME)->error($message);
      }
    }
    // Clear things that might be used in a future statement.
    $this->verb = null;
    $this->object = null;
    $this->result = null;
  }

  /**
   * Remember xAPI object. Reqiured.
   *
   * @param $objectIn
   */
  public function setXapiObject($objectIn) {
    $this->object = $objectIn;
  }

  /**
   * Remember a xAPI result object. Optional.
   *
   * @param $resultIn
   */
  public function setXapiResult($resultIn) {
    $this->result = $resultIn;
  }


  /**
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function makeActorObject() {
    // Actor is an agent, identified by account on this site.
    $xapiAccount = new AgentAccount();
    $userId = $this->currentUser->id();
    // xAPI user name is the user's uid.
    $xapiAccount->setName($userId);
    $userPageUrl = $this->currentUser->getDrupalUser()->toUrl()->setAbsolute()->toString();
    $xapiAccount->setHomePage($userPageUrl);
    // Make actor object, and add account info.
    $this->actor = new Agent();
    $this->actor->setAccount($xapiAccount);
  }

  /**
   * Set the xAPI verb. Required.
   *
   * @param string $verbName Internal name of the verb, defined in xAPI constants.
   *
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiUnknownVerbException
   */
  public function setXapiVerb($verbName) {
    $this->verb = new Verb();
    if ($verbName == SkillingXapiConstants::VERB_NAME_LOGIN) {
      $this->verb->setId('https://brindlewaye.com/xAPITerms/verbs/loggedin');
      $this->verb->setDisplay([
        'en-US' => 'logged in to',
      ]);
    }
    elseif ($verbName == SkillingXapiConstants::VERB_NAME_LOGOUT) {
      $this->verb->setId('https://brindlewaye.com/xAPITerms/verbs/loggedout');
      $this->verb->setDisplay([
        'en-US' => 'logged out of',
      ]);
    }
    elseif ($verbName == SkillingXapiConstants::VERB_NAME_OPENED) {
      $this->verb->setId('http://activitystrea.ms/schema/1.0/open');
      $this->verb->setDisplay([
        'en-US' => 'opened',
      ]);
    }
    elseif ($verbName == SkillingXapiConstants::VERB_NAME_CLOSED) {
      $this->verb->setId('http://activitystrea.ms/schema/1.0/close');
      $this->verb->setDisplay([
        'en-US' => 'closed',
      ]);
    }
    elseif ($verbName == SkillingXapiConstants::VERB_NAME_USE) {
      $this->verb->setId('http://activitystrea.ms/schema/1.0/use');
      $this->verb->setDisplay([
        'en-US' => 'use',
      ]);
    }
    elseif ($verbName == SkillingXapiConstants::VERB_NAME_WAS_AT) {
      $this->verb->setId('http://activitystrea.ms/schema/1.0/at');
      $this->verb->setDisplay([
        'en-US' => 'was at',
      ]);
    }
    else {
      throw new SkillingXapiUnknownVerbException(
        "Unknown verb: $verbName", __FILE__, __LINE__
      );
    }
  }

  /**
   * Make context object for xAPI statement, following Skilling standards.
   *
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiEmptySettingException
   */
  protected function makeContextObject() {
    /** @var \TinCan\Context $this->context */
    $this->context = new Context();
    $platform = $this->getSetting(SkillingXapiConstants::SETTING_PLATFORM);
    $this->context->setPlatform($platform);
    // Make extensions to context.
    $contextExtensions = new Extensions();
    if ($this->currentUser->isStudent()) {
      $contextExtensions->set(SkillingXapiConstants::CONTEXT_ID_HAS_STUDENT_ROLE, true);
    }
    $baseUrl = Drupal::request()->getSchemeAndHttpHost();
    $contextExtensions->set(SkillingXapiConstants::CONTEXT_ID_COURSE_SITE, $baseUrl);
    $className = $this->currentClass->getTitle();
    $contextExtensions->set(SkillingXapiConstants::CONTEXT_ID_CLASS_NAME, $className);
    $classId = $this->currentClass->getId();
    $contextExtensions->set(SkillingXapiConstants::CONTEXT_ID_CLASS_ID, $classId);
    // Save extensions into context.
    $this->context->setExtensions($contextExtensions);
  }

  /**
   * Get a setting from config for this module.
   *
   * @param string $settingName Which setting to get.
   * @param bool $allowEmpty If true, allow an empty value to be returned.
   *
   * @return array|mixed|null Setting.
   *
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiEmptySettingException
   */
  protected function getSetting($settingName, $allowEmpty = FALSE) {
    $settingValue = $this->configSettings->get($settingName);
    if (! $allowEmpty && ($settingValue == '' || is_null($settingValue))) {
      throw new SkillingXapiEmptySettingException(
        "Setting $settingName has no value", __FILE__, __LINE__
      );
    }
    return $settingValue;
  }

  /**
   * Make a connection to the LRS.
   *
   * @return \TinCan\RemoteLRS
   * @throws \Drupal\skilling_xapi\Exception\SkillingXapiEmptySettingException
   */
  protected function makeLrsConnection() {
    $endpointUrl = $this->getSetting(SkillingXapiConstants::SETTING_ENDPOINT_URL);
    $endpontVersion = $this->getSetting(SkillingXapiConstants::SETTING_XAPI_VERSION);
    $endpointUserName = $this->getSetting(SkillingXapiConstants::SETTING_USER_NAME);
    $endpointPassword = $this->getSetting(SkillingXapiConstants::SETTING_PASSWORD);
    $lrsConnection = new RemoteLRS(
      $endpointUrl,
      $endpontVersion,
      $endpointUserName,
      $endpointPassword
    );
    if (!$lrsConnection->about()->success) {
      // Log failure.
      $message = 'Failure connecting to LRS. ' . print_r($lrsConnection->about(), TRUE);
      Drupal::logger(SkillingXapiConstants::MODULE_NAME)->error($message);
      $lrsConnection = null;
    }
    return $lrsConnection;
  }


  public function scheduleRecordLogin() {

  }

  public function runScheduledRecordLogins() {

  }

}
