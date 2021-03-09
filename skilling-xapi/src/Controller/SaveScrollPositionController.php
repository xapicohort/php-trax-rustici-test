<?php

namespace Drupal\skilling_xapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\skilling\Access\SkillingAjaxSecurityInterface;
use Drupal\skilling\Exception\SkillingException;
use Drupal\skilling\SkillingCurrentUser;
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
 * Class SaveScrollPositionController.
 */
class SaveScrollPositionController extends ControllerBase {

  /**
   * The Skilling current user service.
   *
   * @var \Drupal\skilling\SkillingCurrentUser
   */
  protected $skillingCurrentUser;

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
   * @param \Drupal\skilling\SkillingCurrentUser $skilling_current_user
   *   Skilling current user service.
   * @param \Drupal\skilling\Access\SkillingAjaxSecurityInterface $ajaxSecurityService
   *   AJAX security checking service.
   * @param \Drupal\skilling_xapi\XapiStatement $xapiStatementService
   */
  public function __construct(
    SkillingCurrentUser $skilling_current_user,
    SkillingAjaxSecurityInterface $ajaxSecurityService,
    XapiStatement $xapiStatementService
  ) {
    $this->skillingCurrentUser = $skilling_current_user;
    $this->ajaxSecurityService = $ajaxSecurityService;
    $this->xapiStatementService = $xapiStatementService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @noinspection PhpParamsInspection */
    return new static(
      $container->get('skilling.skilling_current_user'),
      $container->get('skilling.ajax_security'),
      $container->get('skilling_xapi.statement')
    );
  }

  /**
   * Save current scroll position in lesson. Called by ajax.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response to the client.
   *
   * @throws \Drupal\skilling\Exception\SkillingException
   */
  public function saveScrollPosition(Request $request) {
    // Only students.
    if ($this->skillingCurrentUser->isStudent()) {
      $passedBasicSecurity = $this->ajaxSecurityService->securityCheckAjaxRequest(
        $request,
        ['POST'],
        ['/skilling-xapi/save-scroll-position'],
        TRUE
      );
      if (!$passedBasicSecurity) {
        throw new SkillingException(
          'Access denied, sec fail in xAPI save scroll position'
        );
      }
      $countAllElements = $request->get('countAllElements');
      if (!$countAllElements || ! is_numeric($countAllElements) || $countAllElements < 0) {
        throw new SkillingException(
          'Access denied, fail in xAPI save scroll position, countAllElements bad: ' . $countAllElements
        );
      }
      $positionFirstVisibleElement = strtolower(trim($request->get('firstVisibleElementIndex')));
      if (! is_numeric($positionFirstVisibleElement) || $positionFirstVisibleElement < 0 ) {
        throw new SkillingException(
          'Access denied, fail in xAPI save scroll position, positionFirstVisibleElement bad: ' . $positionFirstVisibleElement
        );
      }
//      $positionLastVisibleElement = $request->get('lastVisibleElementIndex');
//      if (! is_numeric($positionLastVisibleElement) || $positionLastVisibleElement < 0) {
//        throw new SkillingException(
//          'Access denied, fail in xAPI save scroll position, positionLastVisibleElement bad: ' . $positionLastVisibleElement
//        );
//      }
      $nid = $request->get('nid');
      if (!$nid || ! is_numeric($nid) || $nid < 0) {
        throw new SkillingException(
          'Access denied, fail in xAPI save scroll position, nid bad: ' . $nid
        );
      }
      // Get the alias of the node.
      $urlAlias = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);
      $succeed = true;
      try {
        // Set the verb.
        $this->xapiStatementService->setXapiVerb(
          SkillingXapiConstants::VERB_NAME_WAS_AT);
        // Set the object.
        // Make an activity definition.
        $activityDefinition = new ActivityDefinition();
        $activityDefinition->setType(SkillingXapiConstants::ACTIVITY_TYPE_LOCATION_IN_PAGE);
        $activityDefinition->setName([
          'end-US' => 'Location in page',
        ]);
        $object = new Activity();
        $object->setId($urlAlias);
        $object->setDefinition($activityDefinition);

        $result = new Result();
        $result->setExtensions([
          SkillingXapiConstants::RESULT_EXTENSION_CONTENT_CONTAINER_LENGTH => $countAllElements,
          SkillingXapiConstants::RESULT_EXTENSION_POSITION_FIRST_VISIBLE_ELEMENT => $positionFirstVisibleElement,
//          SkillingXapiConstants::RESULT_EXTENSION_POSITION_LAST_VISIBLE_ELEMENT => $positionLastVisibleElement,
          SkillingXapiConstants::RESULT_EXTENSION_LESSON_URL => $urlAlias,
          SkillingXapiConstants::RESULT_EXTENSION_LESSON_ID => $nid,
        ]);
        $this->xapiStatementService->setXapiObject($object);
        $this->xapiStatementService->setXapiResult($result);
        $this->xapiStatementService->saveXapiStatement();
      } catch (Exception $e) {
        $succeed = false;
      }
    }
    // Send result of response check to user.
    $result = [
      'status' => $succeed ? 'OK' : 'fail',
    ];
    return new JsonResponse($result);
  }

}
