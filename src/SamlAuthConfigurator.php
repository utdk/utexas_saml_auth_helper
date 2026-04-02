<?php

namespace Drupal\utexas_saml_auth_helper;

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

}
