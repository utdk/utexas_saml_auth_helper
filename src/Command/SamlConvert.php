<?php

namespace Drupal\utexas_saml_auth_helper\Command;

use Drupal\user\Entity\User;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provide the ability to register existing users in the authmap table.
 */
#[AsCommand(
  name: 'saml:convert',
  description: 'Register existing users in the authmap table.',
  usages: [
    'saml:convert all (automatically converts all eligible users)',
    'saml:convert list (lists all eligible usernames)',
    'saml:convert choose (interactive prompt to convert individual users)',
    'saml:convert <username> (convert a specific username)',
  ],
)]
class SamlConvert {

  /**
   * Provide the ability to register existing users in the authmap table.
   */
  public function __invoke(InputInterface $input, OutputInterface $output, #[Argument] string $user_input): int {
    switch ($user_input) {
      case 'all':
        $result = $this->convertAllEligibleUsers($output);
        break;

      case 'choose':
        $result = $this->choose($input, $output);
        break;

      case 'list':
        $result = $this->list($output);
        break;

      default:
        $result = $this->convertUser($user_input, $output);

        break;
    }
    return $result;
  }

  /**
   * Merge new info into the authmap table.
   */
  private function convertUser(string $input, OutputInterface $output) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $input]);
    if (!($user)) {
      // The user doesn't exist in the system.
      $output->writeln('The input is not a valid choice or username. Provide "list" (list all eligible users), "all" (convert all users), "choose" (iterate through individually), or a valid username.');
      return Command::FAILURE;
    }
    $user = reset($user);
    if ($user->id() == 1) {
      $output->writeln("The administrative user cannot be converted to SAML login.");
      return Command::FAILURE;
    }
    else {
      $name = $user->getAccountName();
      // Validate as a possible EID using a regular expression match.
      if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $name)) {
        $externalauth = \Drupal::service('externalauth.externalauth');
        $externalauth->linkExistingAccount($name, 'samlauth', $user);
        $output->writeln("$input converted to SAML Authentication.");
      }
      else {
        $output->writeln("$input does not appear to be a valid EID.");
        return Command::FAILURE;
      }
    }

    return Command::SUCCESS;
  }

  /**
   * Load all active users with the authenticated user role, excepting user 1.
   */
  private function getEligibleUsers() {
    $eligible = [];
    // phpcs:ignore
    $query = \Drupal::entityQuery('user')
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->condition('uid', '1', '!=');
    $users = $query->execute();
    if (!empty($users)) {
      // phpcs:ignore
      $accounts = User::loadMultiple(array_keys($users));
      foreach (array_values($accounts) as $user) {
        // Check if the username is a possible EID.
        $name = $user->getAccountName();
        if (preg_match('/^[a-z0-9][a-z0-9._-]{1,7}$/', $name)) {
          $eligible[] = $user->id();
        }
      }
    }
    return $eligible;
  }

  /**
   * Callback for the "all" argument. Automatically converts all eligible users.
   */
  private function convertAllEligibleUsers(OutputInterface $output) {
    $eligible = $this->getEligibleUsers();
    if (!empty($eligible)) {
      foreach ($eligible as $uid) {
        $user = User::load($uid);
        $name = $user->getAccountName();
        $this->convertUser($name, $output);
      }
    }
    return Command::SUCCESS;
  }

  /**
   * Print a list of eligible users.
   */
  private function list(OutputInterface $output) {
    $eligible = $this->getEligibleUsers();
    $output->writeln("The following accounts are eligible for saml login:");
    foreach ($eligible as $uid) {
      $user = User::load($uid);
      $name = $user->getAccountName();
      $output->writeln($name);
    }
    return Command::SUCCESS;
  }

  /**
   * Callback for the "choose" argument. Conditionally convert eligible users.
   */
  private function choose(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    $eligible = $this->getEligibleUsers();
    if (!empty($eligible)) {
      foreach ($eligible as $uid) {
        $user = User::load($uid);
        $name = $user->getAccountName();
        $choice = $io->choice("Convert $name?", [
          'yes' => 'Yes',
          'no' => 'No',
        ],
        'yes');
        if ($choice == 'yes') {
          $this->convertUser($name, $output);
        }
      }
    }
    return Command::SUCCESS;
  }

}
