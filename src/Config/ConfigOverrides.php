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
    $request = $this->requestStack->getCurrentRequest();
    if (!is_null($request)) {
      $domain = $request->getSchemeAndHttpHost();
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
