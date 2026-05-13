<?php

namespace Drupal\utexas_saml_auth_helper;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provided configuration for use with samlauth module.
 */
class SamlAuthConfigurator {

  /**
   * Helper function to get certificates.
   *
   * @param string $name
   *   The machine name of the cert to get, matching the samlauth config value.
   *
   * @return mixed
   *   A string representing the cert, or NULL.
   */
  public static function getCertificate($name) {
    // First see if the value is provided by a Pantheon Organizational secret.
    if (function_exists('pantheon_get_secret')) {
      $certificate = pantheon_get_secret($name) ?? NULL;
      if (!is_null($certificate)) {
        return $certificate;
      }
    }
    // The value is not found as a Pantheon Secret.
    \Drupal::logger('utexas_saml_auth_helper')->warning('The certificate for %name was not found', [
      '%name' => $name,
    ]);
    return NULL;
  }

  /**
   * Configuration associated with samlauth module configuration of OneLogin.
   *
   * @var array
   */
  public static $settings = [
    'login_link_title' => 'Sign in with UT EID',
    'login_menu_item_title' => 'Sign in with UT EID',
    'logout_menu_item_title' => 'Sign out with UT EID',
    'map_users_name' => TRUE,
    'map_users_mail' => FALSE,
    'map_users_roles' => ['anonymous'],
    'metadata_valid_secs' => '',
    'metadata_cache_http' => FALSE,
    'unique_id_attribute' => 'uid',
    'request_set_name_id_policy' => TRUE,
    'sync_name' => FALSE,
    'sync_mail' => TRUE,
    'user_name_attribute' => 'uid',
    'user_mail_attribute' => 'mail',
    'sp_entity_id' => '[DOMAIN]/onelogin',
    'sp_name_id_format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'sp_x509_certificate' => '',
    'sp_private_key' => '',
    'sp_key_cert_type' => '',
    'idp_certs' => [],
    'idp_entity_id' => 'https://enterprise.login.utexas.edu/idp/shibboleth',
    'idp_single_sign_on_service' => 'https://enterprise.login.utexas.edu/idp/profile/SAML2/Redirect/SSO',
    'idp_single_log_out_service' => 'https://enterprise.login.utexas.edu/idp/profile/Logout',
    'idp_change_password_service' => '',
    'security_metadata_sign' => FALSE,
    'security_authn_requests_sign' => FALSE,
    'security_logout_requests_sign' => FALSE,
    'security_logout_responses_sign' => FALSE,
    'security_nameid_encrypt' => FALSE,
    'security_signature_algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    'security_encryption_algorithm' => '',
    'security_messages_sign' => FALSE,
    'security_assertions_signed' => FALSE,
    'security_assertions_encrypt' => TRUE,
    'security_nameid_encrypted' => FALSE,
    'security_want_name_id' => FALSE,
    'security_request_authn_context' => FALSE,
    'security_lowercase_url_encoding' => FALSE,
    'security_logout_reuse_sigs' => FALSE,
    'security_allow_repeat_attribute_name' => TRUE,
    'strict' => TRUE,
    'use_proxy_headers' => FALSE,
    'use_base_url' => TRUE,
    'bypass_relay_state_check' => FALSE,
  ];

  /**
   * Custom validation for a user's name (UT EID).
   *
   * This function performs the following checks on a given name:
   * - is non-empty
   * - matches the regular pattern /^[a-z0-9][a-z0-9._-]{1,7}$/
   *
   * Borrowed from the UTLogin module.
   *
   * @param string $name
   *   The name to be checked.
   *
   * @return string|null
   *   NULL if the given name passes all checks, or an error string otherwise.
   */
  public static function validateName($name) {
    if (is_array($name)) {
      return;
    }
    // Check that the name is non-empty.
    if (!$name) {
      return t('You must enter a UT EID.');
    }

    // Check that the name matches the UT EID regular pattern.
    if (!preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $name)) {
      return t('The entered UT EID is not valid.');
    }
    return FALSE;
  }

  /**
   * Helper callback to modify configuration forms used with samlauth.
   */
  public static function populateSamlAuth(&$form) {
    $settings = SamlAuthConfigurator::$settings;
    $domain = \Drupal::request()->getSchemeAndHttpHost();
    foreach ($settings as $key => $value) {
      if (is_string($value)) {
        $newvalue = str_replace('[DOMAIN]', $domain, $value);
        $settings[$key] = $newvalue;
      }
    }
    $settings['sp_x509_certificate'] = SamlAuthConfigurator::getCertificate('sp_x509_certificate');
    $settings['sp_private_key'] = SamlAuthConfigurator::getCertificate('sp_private_key');
    $settings['idp_certs'] = SamlAuthConfigurator::getCertificate('idp_cert');
    $sp_key_cert_type = 'config_config';
    $idp_cert_type = 'config';
    $overrides = [
      'ui' => [
        'login_link_title' => $settings['login_link_title'],
        'login_menu_item_title' => $settings['login_menu_item_title'],
        'logout_menu_item_title' => $settings['logout_menu_item_title'],
      ],
      'user_info' => [
        'unique_id_attribute' => $settings['unique_id_attribute'],
        'unique_id_source' => 1,
        'linking' => [
          'map_users_name' => $settings['map_users_name'],
          'map_users_mail' => $settings['map_users_mail'],
          'map_users_roles' => $settings['map_users_roles'],
          'allow_all_roles' => TRUE,
        ],
        'nameid' => [
          'request_set_name_id_policy' => $settings['request_set_name_id_policy'],
        ],
        'sync_name' => $settings['sync_name'],
        'sync_mail' => $settings['sync_mail'],
        'user_name_attribute' => $settings['user_name_attribute'],
        'user_mail_attribute' => $settings['user_mail_attribute'],
      ],
      'caching' => [
        'metadata_cache_http' => $settings['metadata_cache_http'],
      ],
      'service_provider' => [
        'sp_entity_id' => $settings['sp_entity_id'],
        'sp_key_cert_type' => $sp_key_cert_type,
        'sp_private_key' => $settings['sp_private_key'],
        'sp_key_file' => $settings['sp_private_key'],
        'sp_cert_file' => $settings['sp_x509_certificate'],
        'sp_x509_certificate' => $settings['sp_x509_certificate'],
        'sp_new_cert_file' => '',
        'security_metadata_sign' => $settings['security_metadata_sign'],
        'caching' => [
          'metadata_cache_http' => $settings['metadata_cache_http'],
          'metadata_valid_secs' => $settings['metadata_valid_secs'],
        ],
      ],
      'identity_provider' => [
        'idp_entity_id' => $settings['idp_entity_id'],
        'idp_single_sign_on_service' => $settings['idp_single_sign_on_service'],
        'idp_single_log_out_service' => $settings['idp_single_log_out_service'],
        'idp_certs' => [
          'cert' => $settings['idp_certs'],
        ],
        'idp_cert_type' => $idp_cert_type,
      ],
      'construction' => [
        'security_authn_requests_sign' => $settings['security_authn_requests_sign'],
        'security_logout_requests_sign' => $settings['security_logout_requests_sign'],
        'security_logout_responses_sign' => $settings['security_logout_responses_sign'],
        'security_nameid_encrypt' => $settings['security_nameid_encrypt'],
        'request_set_name_id_policy' => $settings['request_set_name_id_policy'],
        'security_request_authn_context' => $settings['security_request_authn_context'],
        'sp_name_id_format' => $settings['sp_name_id_format'],
      ],
      'responses' => [
        'security_want_name_id' => $settings['security_want_name_id'],
        'security_allow_repeat_attribute_name' => $settings['security_allow_repeat_attribute_name'],
        'security_logout_reuse_sigs' => $settings['security_logout_reuse_sigs'],
        'strict' => $settings['strict'],
        'security_messages_sign' => $settings['security_messages_sign'],
        'security_assertions_signed' => $settings['security_assertions_signed'],
        'security_assertions_encrypt' => $settings['security_assertions_encrypt'],
        'security_nameid_encrypted' => $settings['security_nameid_encrypted'],
      ],
    ];
    foreach ($overrides as $group => $fields) {
      foreach ($fields as $field => $value) {
        if (is_array($value)) {
          foreach ($value as $k => $v) {
            if (isset($form[$group])) {
              $form[$group][$field][$k]['#disabled'] = TRUE;
              $form[$group][$field][$k]['#default_value'] = $v;
              $form[$group][$field][$k]['#description'] = t('This value is set by the utexas_saml_auth_helper module.');
            }
          }
        }
        else {
          if (isset($form[$group])) {
            $form[$group][$field]['#disabled'] = TRUE;
            $form[$group][$field]['#default_value'] = $value;
            $form[$group][$field]['#description'] = t('This value is set by the utexas_saml_auth_helper module');
          }
        }
      }
    }
    if (isset($form['user_info'])) {
      unset($form['user_info']['linking']['allow_all_roles']['#states']);
      unset($form['user_info']['user_name_attribute']['#states']);
      unset($form['user_info']['user_mail_attribute']['#states']);
    }
    if (isset($form['responses']['security_messages_sign'])) {
      unset($form['responses']['security_messages_sign']['#states']);
    }
    if (isset($form['service_provider'])) {
      $form['service_provider']['sp_key_cert_type']['#value'] = $sp_key_cert_type;
    }
    if (isset($form['identity_provider']['idp_certs'])) {
      $form['identity_provider']['idp_cert_type']['#value'] = $idp_cert_type;
      $form['identity_provider']['idp_certs']['#default_value'][0]['cert'] = $settings['idp_certs'];
      array_unshift($form['#submit'], ['Drupal\utexas_saml_auth_helper\SamlAuthConfigurator', 'formSubmit']);
    }
  }

  /**
   * Form submit handler for samlauth_configure_form.
   *
   * Removes sensitive credential values before saving the form so that they are
   * not stored in the database. The values will be provided by the Config
   * Override.
   *
   * This executes prior to the normal submit handler.
   */
  public static function formSubmit($form, FormStateInterface &$form_state) {
    $form_state->setValue('idp_certs', []);
    $form_state->setValue('sp_x509_certificate', '');
    $form_state->setValue('sp_private_key', '');
  }

  /**
   * Helper callback to modify user login/register forms.
   */
  public static function alterUserForms(&$form, FormStateInterface $form_state, $form_id) {
    // phpcs:ignore
    $account = $form_state->getFormObject()->getEntity();
    // These alterations don't apply to User 1.
    if ($account->id() == 1) {
      return;
    }
    // For non-registration forms, disable editing the username.
    if ($form_id == 'user_form') {
      $form['account']['name']['#disabled'] = TRUE;
      // Turn off Current password field's validation.
      $form_state->set('user_pass_reset', 1);
    }

    // Password is not required and may not be set (the field will be hidden).
    // NOTE: password already removed when *editing* the profile of an existing
    // user that has SAML enabled, so in this module we only need to disable it
    // for user registrations.
    $form['account']['pass']['#access'] = FALSE;
    $form['account']['pass']['#required'] = FALSE;
    $form['account']['current_pass']['#access'] = FALSE;
    $form['account']['name']['#title'] = t('Username (UT EID)');
    $form['account']['name']['#weight'] = -2;
    $form['account']['name']['#description'] = t("Most Texas affiliates' EIDs can be obtained via <a href='https://directory.utexas.edu/'>https://directory.utexas.edu/</a>.");

    $moduleHandler = \Drupal::service('module_handler');
    if (!$moduleHandler->moduleExists('externalauth')) {
      return;
    }
    if (isset($form['field_utexas_full_name'])) {
      if (in_array($form_id, ['user_register_form', 'user_form'])) {
        $form['field_utexas_full_name']['#disabled'] = TRUE;
        $description = $form['field_utexas_full_name']['widget'][0]['value']['#description'];
        $form['field_utexas_full_name']['widget'][0]['value']['#description'] = t('@description This is populated automatically by University of Texas account information. It can be changed by <a href="https://utexas.app.box.com/s/yg4c4u0dqjrmz8jej7d9ylrofpteo5rf">updating the preferred name in Workday</a>.', ['@description' => $description]);
      }
    }

    $manual_email = \Drupal::config('utexas_saml_auth_helper.settings')->get('manual_email');
    if (!$manual_email) {
      // Email address is not required.
      $form['account']['mail']['#required'] = FALSE;
      $form['account']['mail']['#disabled'] = TRUE;
      $form['account']['mail']['#weight'] = -1;
      $form['account']['mail']['#description'] = t('This field cannot be modified manually; it will be filled in from UT EID attributes when the user logs in. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive certain news or notifications by e-mail.');
      // The email address can't actually be blank, so we use a custom validate
      // function to set it to *something*.
      array_unshift($form['#validate'], [
        'Drupal\utexas_saml_auth_helper\SamlAuthConfigurator',
        'userAccountFormValidate',
      ]);
    }
  }

  /**
   * Form validation handler for user_account_form().
   *
   * This form validation handler runs before user_account_form_validate().
   *
   * It will a) check that the username is a valid UT EID, b) set the email
   * field using the EID and the IID domain (defaults to eid.utexas.edu), and c)
   * doule check that SAML authentication is enabled for the account.
   *
   * @see validateName()
   */
  public static function userAccountFormValidate($form, FormStateInterface &$form_state) {
    $name = $form_state->getValue('name');
    if ($error = SamlAuthConfigurator::validateName($name)) {
      $form_state->setErrorByName('name', $error);
    }
    $config = \Drupal::config('utexas_saml_auth_helper.settings');
    $form_state->setValue('mail', $name . '@' . $config->get('utexas_saml_auth_helper_iid_domain'));
  }

}
