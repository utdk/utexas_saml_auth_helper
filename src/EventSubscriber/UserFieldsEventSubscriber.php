<?php

namespace Drupal\utexas_saml_auth_helper\EventSubscriber;

use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Synchronizes SAML attributes into user fields during login.
 */
class UserFieldsEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[SamlauthEvents::USER_SYNC][] = ['utexasUserSync'];
    return $events;
  }

  /**
   * Saves configured SAML attribute values into user fields.
   *
   * @param \Drupal\samlauth\Event\SamlauthUserSyncEvent $event
   *   The event being dispatched.
   */
  public function utexasUserSync(SamlauthUserSyncEvent $event) {
    $field_name = 'field_utexas_full_name';
    $account = $event->getAccount();
    if ($account->hasField($field_name)) {
      $attributes = $event->getAttributes();
      $saml_name = $attributes['full_name'];
      if (isset($saml_name[0]) && mb_strlen($saml_name[0])) {
        $account->set('field_utexas_full_name', $saml_name[0]);
        $event->markAccountChanged();
      }
    }
  }

}
