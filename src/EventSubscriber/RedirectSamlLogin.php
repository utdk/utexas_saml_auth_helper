<?php

namespace Drupal\utexas_saml_auth_helper\EventSubscriber;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Redirects /saml_login to /saml/login (legacy transition from simplesamlphp).
 *
 * THIS CAN BE REMOVED AFTER ALL SITES TRANSITION FROM SIMPLESAMLPHP.
 */
class RedirectSamlLogin implements EventSubscriberInterface {

  /**
   * Performs redirect logic.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The RequestEvent to process.
   */
  public function redirect(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    if ($path == '/saml_login') {
      try {
        // Only perform redirection if the `samlauth` module is active.
        \Drupal::service('router.route_provider')->getRouteByName('samlauth.saml_controller_login');
        $event->setResponse(new CacheableRedirectResponse('/saml/login'));
      }
      catch (RouteNotFoundException $exception) {
        return NULL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['redirect', 33];
    return $events;
  }

}
