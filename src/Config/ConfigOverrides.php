<?php

namespace Drupal\utexas_saml_auth_helper\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

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
    if (in_array($config_name, $names)) {
      $host = \Drupal::request()->getSchemeAndHttpHost();
      $overrides[$config_name]['login_link_title'] = 'Sign in with UT EID';
      $overrides[$config_name]['sp_entity_id'] = $host . '/onelogin';
    }
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'UtNewsTeaserImageOverride';
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
