<?php
namespace Drupal\skilling_xapi;

use Drupal\Core\StringTranslation\StringTranslationTrait;

class SkillingXapiConstants {

  use StringTranslationTrait;

  const MODULE_NAME = 'skilling_xapi';

  const XAPI_SERVICE_NAME = 'skilling_xapi.statement';

  // Verb names
  const VERB_NAME_LOGIN = 'login';
  const VERB_NAME_LOGOUT = 'logout';
  const VERB_NAME_OPENED = 'opened';
  const VERB_NAME_CLOSED = 'closed';
  const VERB_NAME_USE = 'use';
  const VERB_NAME_WAS_AT = 'was at';


  // Context extension ids.
  const CONTEXT_ID_HAS_STUDENT_ROLE = 'http://xapi.skilling.us/extension/hasstudentrole';
  const CONTEXT_ID_COURSE_SITE = 'http://xapi.skilling.us/extension/coursesite';
  const CONTEXT_ID_CLASS_NAME = 'http://xapi.skilling.us/extension/classname';
  const CONTEXT_ID_CLASS_ID = 'http://xapi.skilling.us/extension/classid';

  const ACTIVITY_ID_PAUSE = 'http://xapi.skilling.us/activity/pause';

  const ACTIVITY_TYPE_RESOURCE = 'http://id.tincanapi.com/activitytype/resource';
  const ACTIVITY_TYPE_COURSE = 'http://adlnet.gov/expapi/activities/course';
  const ACTIVITY_TYPE_INTERACTION = 'http://adlnet.gov/expapi/activities/interaction';
  const ACTIVITY_TYPE_LOCATION_IN_PAGE = 'http://xapi.skilling.us/activitytype/locationinpage';

  const RESULT_EXTENSION_LESSON_URL = 'http://xapi.skilling.us/resultextention/lessonurl';
  const RESULT_EXTENSION_LESSON_ID = 'http://xapi.skilling.us/resultextention/lessonid';

  const RESULT_EXTENSION_PAUSE_BUTTON_NUMBER = 'http://xapi.skilling.us/resultextention/pausebuttonnumber';
  const RESULT_EXTENSION_PAUSE_BUTTONS_ON_PAGE = 'http://xapi.skilling.us/resultextention/pausebuttonsonpage';
  const RESULT_EXTENSION_PAUSE_BUTTON_USE = 'http://xapi.skilling.us/resultextention/pausebuttonuse';

  const RESULT_EXTENSION_CONTENT_CONTAINER_LENGTH = 'http://xapi.skilling.us/resultextention/contentcontainerlength';
  const RESULT_EXTENSION_POSITION_FIRST_VISIBLE_ELEMENT = 'http://xapi.skilling.us/resultextention/positionfirstvisibleelement';
  const RESULT_EXTENSION_POSITION_LAST_VISIBLE_ELEMENT = 'http://xapi.skilling.us/resultextention/positionlastvisibleelement';


  // Names of configuration settings.
  const SETTINGS_MAIN_KEY = 'skilling_xapi.settings';

  const SETTING_ENDPOINT_URL = 'skilling_xapi_endpoint_url';
  const SETTING_USER_NAME = 'skilling_xapi_endpoint_user_name';
  const SETTING_PASSWORD = 'skilling_xapi_endpoint_password';
  const SETTING_PLATFORM = 'skilling_xapi_platform';
  const SETTING_XAPI_VERSION = 'skilling_xapi_version';
}
