<?php

namespace Drupal\utexas_saml_auth_helper;

/**
 * Provided configuration for use with samlauth module.
 */
class SamlAuthConfigurator {

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
    'sp_x509_certificate' => 'file:sites/default/files/private/saml/assets/cert/sp-cert.crt',
    'sp_private_key' => 'file:sites/default/files/private/saml/assets/cert/sp-key.pem',
    'sp_key_cert_type' => '',
    'idp_entity_id' => 'https://enterprise.login.utexas.edu/idp/shibboleth',
    'idp_single_sign_on_service' => 'https://enterprise.login.utexas.edu/idp/profile/SAML2/Redirect/SSO',
    'idp_single_log_out_service' => 'https://enterprise.login.utexas.edu/idp/profile/Logout',
    'idp_change_password_service' => '',
    'idp_certs' => ['file:sites/default/files/private/saml/assets/cert/idp-cert-prod.crt'],
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
   * Get prepared settings.
   */
  public static function getSettings() {
    $domain = \Drupal::request()->getSchemeAndHttpHost();
    $settings = self::$settings;
    foreach ($settings as $key => $value) {
      if (is_string($value)) {
        $newvalue = str_replace('[DOMAIN]', $domain, $value);
        $settings[$key] = $newvalue;
      }
    }
    return $settings;
  }

}
