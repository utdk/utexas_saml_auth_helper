<?php

namespace Drupal\utexas_saml_auth_helper;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Registers the UserFieldsEventSubscriber if `samlauth` is enabled.
 */
class UtexasSamlAuthHelperServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // This is needed only on sites using samlauth.
    $saml_enabled = \Drupal::moduleHandler()->moduleExists('samlauth');
    if ($saml_enabled === TRUE) {
      $container->register('utexas_saml_auth_helper.event_subscriber.user_sync', 'Drupal\utexas_saml_auth_helper\EventSubscriber\UserFieldsEventSubscriber')
        ->addTag('event_subscriber');
    }
  }

}
