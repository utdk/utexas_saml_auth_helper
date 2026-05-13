<?php

namespace Drupal\utexas_saml_auth_helper\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\utexas_saml_auth_helper\SamlAuthConfigurator;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Hook implementations.
 */
class Hooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if (!\Drupal::moduleHandler()->moduleExists('samlauth')) {
      return;
    }
    if ($form_id === 'samlauth_configure_form') {
      SamlAuthConfigurator::populateSamlAuth($form);
    }
    $user_forms = ['user_form', 'user_register_form'];
    if (in_array($form_id, $user_forms)) {
      SamlAuthConfigurator::alterUserForms($form, $form_state, $form_id);
    }
  }

  /**
   * Implements hook_user_logout().
   */
  #[Hook('user_logout')]
  public function userLogout(AccountInterface $account) {
    $moduleHandler = \Drupal::service('module_handler');
    // For every user but "user 1" when samlauth is active, sign out of SSO.
    if ($moduleHandler->moduleExists('samlauth') && $account->id() != 1) {
      $settings = SamlAuthConfigurator::$settings;
      $response = new RedirectResponse($settings['idp_single_log_out_service']);
      $response->send();
      return $response;
    }
  }

}
