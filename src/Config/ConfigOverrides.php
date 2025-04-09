<?php

namespace Drupal\utexas_saml_auth_helper\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\utexas_saml_auth_helper\SamlAuthConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Configuration override.
 */
class ConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * The Symfony-provided request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    $config_name = 'samlauth.authentication';
    if (in_array($config_name, $names)) {
      $settings = $this->prepareSettings(SamlAuthConfigurator::$settings);
      foreach ($settings as $key => $value) {
        $overrides[$config_name][$key] = $value;
      }
    }
    return $overrides;
  }

  /**
   * Get prepared settings.
   */
  private function prepareSettings($settings) {
    if ($domain = $this->getDomain()) {
      foreach ($settings as $key => $value) {
        if (is_string($value)) {
          $newvalue = str_replace('[DOMAIN]', $domain, $value);
          $settings[$key] = $newvalue;
        }
      }
    }
    return $settings;
  }

  /**
   * Replicate security checks from Symfony\Component\HttpFoundation\Request.
   *
   * @return string|bool
   *   The site hostname, or FALSE if invalid.
   */
  private function getDomain() {
    $host = $_SERVER['HTTP_HOST'];
    // Trim and remove port number from host.
    // Host is lowercase as per RFC 952/2181.
    $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
    // As the host can come from the user, check that it does not contain
    // forbidden characters (see RFC 952 and RFC 2181) and use preg_replace()
    // instead of preg_match() to prevent DoS attacks with long host names.
    if ($host && '' == preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
      return 'https://' . $host;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'UtexasSamlAuthHelperOverrides';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
