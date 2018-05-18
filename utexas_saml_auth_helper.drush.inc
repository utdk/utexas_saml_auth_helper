<?php

/**
 * @file
 * Drush integration for the utexas_saml_auth_helper module.
 */

/**
 * Implements hook_drush_command().
 *
 * Defines a single "saml-convert" command.
 */
function utexas_saml_auth_helper_drush_command() {

  $items['saml-convert'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'description' => 'Demonstrate how Drush commands work.',
    'aliases' => array('usc'),
    'arguments' => array(
      'type' => 'The type of operation: "all", "choose", or a valid username.',
    ),
  );

  return $items;
}

/**
 * Controller callback. Convert users to SAML login.
 *
 * @param string $type
 *    One of three acceptable arguments as listed above.
 */
function drush_utexas_saml_auth_helper_saml_convert($type = 'choose') {
  // Check for correct argument.
  $converted = array();
  $correct_args = array('all', 'choose');
  if ($type == 'all') {
    $converted = _utexas_saml_auth_helper_convert_eligible_users();
  }
  elseif ($type == 'choose') {
    $converted = _utexas_saml_auth_helper_iterate();
  }
  elseif (!in_array($type, $correct_args)) {
    if (!($user = user_load_by_name($type))) {
      // The user doesn't exist in the system.
      return drush_set_error(dt('"@type" is not a valid argument or username. Provide the parameter "all" (convert all users), "choose" (iterate through individually), or a valid username.', array('@type' => $type)));
    }
    elseif ($user->uid == 1) {
      return drush_set_error(dt('The administrative user cannot be converted to SAML login.'));
    }
    else {
      // Validate as a possible EID using a regular expression match.
      if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $user->name)) {
        // It is a user in the system.
        $converted[] = _utexas_saml_auth_helper_convert($user->uid, $user->name);
      }
    }
  }
  // Print informative messages about what happened.
  _utexas_saml_auth_helper_results($converted);
}

/**
 * Database manipulation. Merge new info into the authmap table.
 */
function _utexas_saml_auth_helper_convert($uid = NULL, $name = NULL) {
  if (isset($uid) && isset($name) && $uid != 1) {
    db_merge('authmap')
      ->key(array(
        'uid' => $uid,
        'module' => 'simplesamlphp_auth',
      ))
      ->fields(array(
        'authname' => $name,
      ))
      ->execute();
    return $name;
  }
}

/**
 * Callback for the "all" argument. Automatically converts all eligible users.
 */
function _utexas_saml_auth_helper_convert_eligible_users() {
  $converted = array();
  $eligible = _utexas_saml_auth_helper_get_eligible_users();
  if (!empty($eligible)) {
    foreach ($eligible as $user) {
      $converted[] = _utexas_saml_auth_helper_convert($user->uid, $user->name);
    }
  }
  return $converted;
}

/**
 * Load all active users with the authenticated user role, excepting user 1.
 */
function _utexas_saml_auth_helper_get_eligible_users() {
  $eligible = array();
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'user')
    ->propertyCondition('status', 1)
    ->propertyCondition('uid', '1', '!=');
  $result = $query->execute();
  if (!empty($result['user'])) {
    $accounts = user_load_multiple(array_keys($result['user']));
    foreach ($accounts as $uid => $user) {
      // Check if the username is a possible EID.
      if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $user->name)) {
        $eligible[$uid] = $user;
      }
    }
  }
  return $eligible;
}

/**
 * Callback for the "choose" argument. Conditionally convert eligible users.
 */
function _utexas_saml_auth_helper_iterate() {
  $converted = array();
  $options = array(
    'yes' => dt('Yes'),
  );
  $eligible = _utexas_saml_auth_helper_get_eligible_users();

  if (!empty($eligible)) {
    foreach ($eligible as $user) {
      // Check regex for EID.
      $choice = drush_choice($options, dt('Convert username "@user" ?', array('@user' => $user->name)));
      if ($choice == 'yes') {
        _utexas_saml_auth_helper_convert($user->uid, $user->name);
        $converted[] = $user->name;
      }
    }
  }
  return $converted;
}

/**
 * Print informative messages.
 */
function _utexas_saml_auth_helper_results($converted = array()) {
  if (empty($converted)) {
    drush_log(dt('No accounts on this site were eligible for conversion. No changes have been made.'), 'success');
  }
  else {
    drush_log(dt('The following accounts have been converted to SAML login: @accounts.', array('@accounts' => implode(', ', $converted))), 'success');
  }
}
