<?php

namespace Drupal\utexas_saml_auth_helper\Hook;

use Drupal\Core\Entity\EntityInterface;
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
  public function formAlter(&$form, FormStateInterface $form_state, string $form_id) {
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
   * Implements hook_ENTITY_TYPE_create().
   */
  #[Hook('user_insert')]
  public function userInsert(EntityInterface $user) {
    // When a new Drupal account is created,
    // explicitly add an authmap entry for this account. For the default
    // configuration where no roles are allowed to use Drupal login when
    // associated with a SAML account, this step is required to *prevent* local
    // login. Without this, SAML-login eligibility and the authmap entry would
    // be added upon successful SAML login, but not before, meaning that until
    // the account had been signed in via SAML, the account would be eligible
    // for local login. We don't want this for security reasons. See #69.
    // When sites need to create local accounts, the current policy is that they
    // should temporarily disable this module, which would then also bypass this
    // hook and a local-login account could be created.
    $authname = $user->getAccountName();
    $externalauth = \Drupal::service('externalauth.externalauth');
    $externalauth->linkExistingAccount($authname, 'samlauth', $user);
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
