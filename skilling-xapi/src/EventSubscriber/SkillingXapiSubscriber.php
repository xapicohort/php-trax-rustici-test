<?php /** @noinspection PhpUnusedParameterInspection */

namespace Drupal\skilling_xapi\EventSubscriber;

use Drupal;
use Drupal\skilling\SkillingConstants;
use Drupal\skilling_xapi\SkillingXapiConstants;
use Drupal\skilling_xapi\XapiStatement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Watch events.
 */
class SkillingXapiSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['routingRouteFinished'];
    return $events;
  }

  /**
   * This method is called when the routing.route_finished event is dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   What happened.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\skilling\Exception\SkillingException
   * @throws \Drupal\skilling\Exception\SkillingValueMissingException
   * @throws \Drupal\skilling\Exception\SkillingWrongTypeException
   */
  public function routingRouteFinished(GetResponseEvent $event) {
    // Run scheduled recording of logins to xAPI LRS.
    /** @var XapiStatement $xapiService */
    $xapiService = Drupal::service(SkillingXapiConstants::XAPI_SERVICE_NAME);
    $xapiService->runScheduledRecordLogins();
  }

  /**
   */
  protected function runScheduledRecordLogins() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = Drupal::service('config.factory');
    $settings = $configFactory->getEditable(SkillingConstants::SETTINGS_MAIN_KEY);
    $cacheClearScheduled = $settings->get(SkillingConstants::SETTING_KEY_SCHEDULE_CLEAR_CACHE);
  }}
