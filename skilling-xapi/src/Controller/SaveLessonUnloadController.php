<?php

namespace Drupal\skilling_xapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\skilling\Access\FilterUserInputInterface;
use Drupal\skilling\Access\SkillingAjaxSecurityInterface;
use Drupal\skilling\Exception\SkillingException;
use Drupal\skilling\SkillingCurrentUser;
use Drupal\skilling\SkillingParser\SkillingParser;
use Drupal\skilling_xapi\Exception\SkillingXapiException;
use Drupal\skilling_xapi\SkillingXapiConstants;
use Drupal\skilling_xapi\XapiStatement;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\skilling\SkillingConstants;
use Drupal\skilling\Utilities as SkillingUtilities;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Html;
use TinCan\Activity;
use TinCan\ActivityDefinition;
use TinCan\ContextActivities;
use TinCan\Result;

/**
 * Class SaveLessonUnloadController.
 */
class SaveLessonUnloadController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Skilling current user service.
   *
   * @var \Drupal\skilling\SkillingCurrentUser
   */
  protected $skillingCurrentUser;

  /**
   * The Skilling utilities service.
   *
   * @var \Drupal\Skilling\Utilities
   */
//  protected $skillingUtilities;

  /**
   * Service to check AJAX calls.
   *
   * @var \Drupal\skilling\Access\SkillingAjaxSecurityInterface
   */
  protected $ajaxSecurityService;

  /**
   * Service to filter user input.
   *
   * @var \Drupal\skilling\Access\FilterUserInputInterface
   */
//  protected $filterInputService;

  /**
   * @var \Drupal\skilling_xapi\XapiStatement
   */
  protected $xapiStatementService;

  /**
   * Constructs a new controller object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\skilling\SkillingCurrentUser $skilling_current_user
   *   Skilling current user service.
   * @param \Drupal\skilling\Utilities $skillingUtilities
   *   Skilling utilities service.
   * @param \Drupal\skilling\SkillingParser\SkillingParser $parser
   *   Skilling parser.
   * @param \Drupal\skilling\Access\SkillingAjaxSecurityInterface $ajaxSecurityService
   *   AJAX security checking service.
   * @param \Drupal\skilling\Access\FilterUserInputInterface $filterInputService
   *   Input filter service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    SkillingCurrentUser $skilling_current_user,
//    SkillingUtilities $skillingUtilities,
//    SkillingParser $parser,
    SkillingAjaxSecurityInterface $ajaxSecurityService,
    XapiStatement $xapiStatementService
//    FilterUserInputInterface $filterInputService,
//    ModuleHandlerInterface $moduleHandler
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->skillingCurrentUser = $skilling_current_user;
//    $this->skillingUtilities = $skillingUtilities;
//    $this->parser = $parser;
    $this->ajaxSecurityService = $ajaxSecurityService;
    $this->xapiStatementService = $xapiStatementService;
//    $this->filterInputService = $filterInputService;
//    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @noinspection PhpParamsInspection */
    return new static(
      $container->get('entity_type.manager'),
      $container->get('skilling.skilling_current_user'),
//      $container->get('skilling.utilities'),
//      $container->get('skilling.skillingparser'),
      $container->get('skilling.ajax_security'),
      $container->get('skilling_xapi.statement')
//      $container->get('skilling.filter_user_input'),
//      $container->get('module_handler')
    );
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response to the client.
   *
   * @throws \Drupal\skilling\Exception\SkillingException
   */
  public function saveLessonUnload(Request $request) {
    // Only students.
    if ($this->skillingCurrentUser->isStudent()) {
      $passedBasicSecurity = $this->ajaxSecurityService->securityCheckAjaxRequest(
        $request,
        ['POST'],
        ['/skilling-xapi/save-lesson-unload'],
        TRUE
      );
      if (!$passedBasicSecurity) {
        throw new SkillingException(
          'Access denied, sec fail in xAPI save lesson unload'
        );
      }
      $nid = $request->get('nid');
      if (!$nid || ! is_numeric($nid) || $nid < 0) {
        throw new SkillingException(
          'Access denied, fail in xAPI save lesson unload, nid bad: ' . $nid
        );
      }
      // Get the alias of the node.
//      $urlAlias = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);
      $succeed = true;
      try {
        // Load the node.
        /** @var \Drupal\node\NodeInterface $node */
        $node = $this->entityTypeManager->getStorage('node')->load($nid);
        if (! $node) {
          throw new SkillingException("SaveLessonUnload: nid $nid not found.");
        }
        // Set the verb.
        $this->xapiStatementService->setXapiVerb(
          SkillingXapiConstants::VERB_NAME_CLOSED);
        // Set the object.
        // Make an activity definition.
        $activityDefinition = new ActivityDefinition();
        $activityDefinition->setType(SkillingXapiConstants::ACTIVITY_TYPE_RESOURCE);
        $lessonTitle = $node->getTitle();
        $activityDefinition->setName([
          'en-US' => $lessonTitle,
        ]);
        // Description is body summary.
        $summary = $node->body->summary;
        if ($summary == '' || $summary == NULL) {
          $summary = "(Empty)";
        }
        $activityDefinition->setDescription([
          'en-US' => $summary
        ]);
        // Activity id is URL.
        $lessonUrl = $node->toUrl()->setAbsolute()->toString();
        $object = new Activity();
        $object->setId($lessonUrl);
        $object->setDefinition($activityDefinition);
        // Set the object.
        $this->xapiStatementService->setXapiObject($object);
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
