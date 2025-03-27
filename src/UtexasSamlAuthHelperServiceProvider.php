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
    if ($container->hasDefinition('samlauth.event_subscriber.user_sync')) {
      $container->register('utexas_saml_auth_helper.event_subscriber.user_sync', 'Drupal\utexas_saml_auth_helper\EventSubscriber\UserFieldsEventSubscriber')
        ->addTag('event_subscriber');
      $container->register('utexas_saml_auth_helper.event_subscriber.redirect', 'Drupal\utexas_saml_auth_helper\EventSubscriber\RedirectSamlLogin')
        ->addTag('event_subscriber');
    }
  }

}
