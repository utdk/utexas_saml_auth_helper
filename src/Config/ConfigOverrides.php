<?php

namespace Drupal\utexas_saml_auth_helper\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\utexas_saml_auth_helper\SamlAuthConfigurator;

/**
 * Configuration override.
 */
class ConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    $config_name = 'samlauth.authentication';
    $settings = SamlAuthConfigurator::getSettings(TRUE);
    if (in_array($config_name, $names)) {
      foreach ($settings as $key => $value) {
        $overrides[$config_name][$key] = $value;
      }
    }
    return $overrides;
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
