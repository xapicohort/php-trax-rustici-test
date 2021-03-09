<?php

namespace Drupal\skilling_xapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\skilling\Access\SkillingAjaxSecurityInterface;
use Drupal\skilling\Exception\SkillingException;
use Drupal\skilling_xapi\SkillingXapiConstants;
use Drupal\skilling_xapi\XapiStatement;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TinCan\Activity;
use TinCan\ActivityDefinition;
use TinCan\Result;

/**
 * Class SaveMoreClickController.
 */
class SaveMoreClickController extends ControllerBase {

  /**
   * Service to check AJAX calls.
   *
   * @var \Drupal\skilling\Access\SkillingAjaxSecurityInterface
   */
  protected $ajaxSecurityService;

  /**
   * @var \Drupal\skilling_xapi\XapiStatement
   */
  protected $xapiStatementService;

  /**
   * Constructs a new controller object.
   *
   * @param \Drupal\skilling\Access\SkillingAjaxSecurityInterface $ajaxSecurityService
   *   AJAX security checking service.
   * @param \Drupal\skilling_xapi\XapiStatement $xapiStatementService
   */
  public function __construct(
    SkillingAjaxSecurityInterface $ajaxSecurityService,
    XapiStatement $xapiStatementService
  ) {
    $this->ajaxSecurityService = $ajaxSecurityService;
    $this->xapiStatementService = $xapiStatementService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @noinspection PhpParamsInspection */
    return new static(
      $container->get('skilling.ajax_security'),
      $container->get('skilling_xapi.statement')
    );
  }

  /**
   * Save user's click on More button. Called via ajax.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response to the client.
   * @throws \Drupal\skilling\Exception\SkillingException
   */
  public function saveMoreClick(Request $request) {
    $passedBasicSecurity = $this->ajaxSecurityService->securityCheckAjaxRequest(
      $request,
      ['POST'],
      ['/skilling-xapi/save-more-click'],
      TRUE
    );
    if (!$passedBasicSecurity) {
      throw new SkillingException('Access denied, sec fail in xAPI save more click');
    }
    $buttonNumber = $request->get('buttonCount');
    if (!$buttonNumber || ! is_numeric($buttonNumber) || $buttonNumber < 0) {
      throw new SkillingException('Access denied, fail in xAPI save more click, button count bad: ' . $buttonNumber);
    }
    $buttonUse = strtolower(trim($request->get('buttonUse')));
    if (!$buttonUse || ($buttonUse != 'next' && $buttonUse != 'all') ) {
      throw new SkillingException('Access denied, fail in xAPI save more click, button use bad: ' . $buttonUse);
    }
    $buttonsOnPage = $request->get('buttonsOnPage');
    if (!$buttonsOnPage || ! is_numeric($buttonsOnPage) || $buttonsOnPage < 0) {
      throw new SkillingException('Access denied, fail in xAPI save more click, button on page bad: ' . $buttonsOnPage);
    }
    $nid = $request->get('nid');
    if (!$nid || ! is_numeric($nid) || $nid < 0) {
      throw new SkillingException('Access denied, fail in xAPI save more click, nid bad: ' . $nid);
    }
    // Get the alias of the node.
    $urlAlias = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);
    $succeed = true;
    try {
      // Set the verb.
      $this->xapiStatementService->setXapiVerb(
        SkillingXapiConstants::VERB_NAME_USE);
      // Set the object.
      // Make an activity definition.
      $activityDefinition = new ActivityDefinition();
      $activityDefinition->setType(SkillingXapiConstants::ACTIVITY_TYPE_INTERACTION);
      $activityDefinition->setName([
        'end-US' => 'Interaction',
      ]);
      $object = new Activity();
      $object->setId(SkillingXapiConstants::ACTIVITY_ID_PAUSE);
      $object->setDefinition($activityDefinition);

      $result = new Result();
      $result->setExtensions([
        SkillingXapiConstants::RESULT_EXTENSION_PAUSE_BUTTON_NUMBER => $buttonNumber,
        SkillingXapiConstants::RESULT_EXTENSION_PAUSE_BUTTON_USE => $buttonUse,
        SkillingXapiConstants::RESULT_EXTENSION_PAUSE_BUTTONS_ON_PAGE => $buttonsOnPage,
        SkillingXapiConstants::RESULT_EXTENSION_LESSON_URL => $urlAlias,
        SkillingXapiConstants::RESULT_EXTENSION_LESSON_ID => $nid,
      ]);
      $this->xapiStatementService->setXapiObject($object);
      $this->xapiStatementService->setXapiResult($result);
      $this->xapiStatementService->saveXapiStatement();
    } catch (Exception $e) {
      $succeed = false;
    }
    // Send result of response check to user.
    $result = [
      'status' => $succeed ? 'OK' : 'fail',
    ];
    return new JsonResponse($result);
  }

}
