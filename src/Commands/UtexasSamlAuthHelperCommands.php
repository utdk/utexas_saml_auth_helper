<?php

namespace Drupal\utexas_saml_auth_helper\Commands;

use Drush\Commands\DrushCommands;
use Drupal\user\Entity\User;

/**
 * Provide the ability to register existing users in the authmap table.
 */
class UtexasSamlAuthHelperCommands extends DrushCommands {

  /**
   * Entrypoint to the drush command.
   *
   * @param string $input
   *   The type of operation: "all", "choose", "list" or a valid username.
   *
   * @command saml:convert
   * @aliases usc,saml-convert
   */
  public function convert($input = 'choose') {
    $results = [];
    switch ($input) {
      case 'all':
        $results = $this->convertAllEligibleUsers();
        break;

      case 'choose':
        $results = $this->iterate();
        break;

      case 'list':
        $results = $this->list();
        break;

      default:
        if (!($user = user_load_by_name($input))) {
          // The user doesn't exist in the system.
          throw new \Exception(dt('"@input" is not a valid choice or username. Provide "list" (list all eligible users), "all" (convert all users), "choose" (iterate through individually), or a valid username.', ['@input' => $input]));
        }
        elseif ($user->id() == 1) {
          throw new \Exception(dt('The administrative user cannot be converted to SAML login.'));
        }
        else {
          // Validate as a possible EID using a regular expression match.
          if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $user->getAccountName())) {
            // It is a user in the system.
            $results[] = $this->convertUser($user->id(), $user->getAccountName());
          }
          else {
            throw new \Exception(dt('"input" is a user in the system but does not appear to be a valid EID.', ['@input' => $input]));
          }
        }
        break;
    }

    // Print informative message about what happened.
    $this->results($results);
  }

  /**
   * Load all active users with the authenticated user role, excepting user 1.
   */
  private function getEligibleUsers() {
    $eligible = [];
    $query = \Drupal::entityQuery('user')
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->condition('uid', '1', '!=');
    $users = $query->execute();
    if (!empty($users)) {
      $accounts = User::loadMultiple(array_keys($users));
      foreach ($accounts as $uid => $user) {
        // Check if the username is a possible EID.
        $name = $user->getAccountName();
        if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $name)) {
          $eligible[$uid] = $name;
        }
      }
    }
    return $eligible;
  }

  /**
   * Database manipulation. Merge new info into the authmap table.
   */
  private function convertUser($uid = NULL, $name = NULL) {
    $connection = \Drupal::database();
    if (isset($uid) && isset($name) && $uid != 1) {
      $connection->merge('authmap')
        ->keys(['uid' => $uid, 'provider' => 'samlauth'])
        ->fields(['authname' => $name, 'data' => ''])
        ->execute();
      return $name;
    }
  }

  /**
   * Callback for the "all" argument. Automatically converts all eligible users.
   */
  private function convertAllEligibleUsers() {
    $converted = [];
    $eligible = $this->getEligibleUsers();
    if (!empty($eligible)) {
      foreach ($eligible as $uid => $name) {
        $converted[] = $this->convertUser($uid, $name);
      }
    }
    return dt('The following accounts have been converted to SAML login: @accounts', ['@accounts' => PHP_EOL . implode(PHP_EOL, $converted)], 'success');
  }

  /**
   * Print a list of eligible users.
   */
  private function list() {
    $eligible = $this->getEligibleUsers();
    return dt('The following accounts are eligible for saml login: @accounts', ['@accounts' => PHP_EOL . implode(PHP_EOL, array_values($eligible))], 'success');
  }

  /**
   * Callback for the "choose" argument. Conditionally convert eligible users.
   */
  private function iterate() {
    $converted = [];
    $eligible = $this->getEligibleUsers();
    if (!empty($eligible)) {
      foreach ($eligible as $uid => $name) {
        $choice = $this->io()->confirm(dt('Convert "@user" ?', ['@user' => $name]));
        if ($choice == 'yes') {
          $this->convertUser($uid, $name);
          $converted[] = $name;
        }
      }
    }
    return dt('The following accounts have been converted to SAML login: @accounts', ['@accounts' => PHP_EOL . implode(PHP_EOL, $converted)], 'success');
  }

  /**
   * Print informative messages.
   */
  private function results($results = []) {
    if (empty($results)) {
      $this->output()->writeln(dt('No accounts on this site were eligible for conversion. No changes have been made.'), 'success');
    }
    else {
      $this->output()->writeln($results);
    }
  }

}
