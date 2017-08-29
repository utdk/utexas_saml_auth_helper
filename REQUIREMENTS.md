# Requirements Overview

Before this module begins any development work, we want to make sure we have a clear idea of the scope of the module and what will be required to properly implement it.


* We'll need to determine the procedure for proper installation and configuration for the contrib simplesamlphp_auth
  * Our composer.json in our distribution will have to include a reference to the [`simplesamlphp` repo](https://github.com/simplesamlphp/simplesamlphp).
  * We'll need to define simplesamlphp configuration settings and abstract that away from users. The configuration will have to be placed in /vendor/simplesamlphp/simplesamphp/config. We can use the configuration from our D7 implementation, which is under /private/simplesamlphp-1.11.0/config *NEED TO CONFIRM THIS WILL WORK AS EXPECTED*. Since this config isn't provided by default, we'll want to include it somewhere and copy it to the appropriate place after composer installs it.
  * We'll need to include the appropriate default Drupal settings in some config yml files, that can be placed in the utexas_saml_auth_helper config/install directory.

* We'll want to implement hook_form_FORM_ID_alter to do some of the stuff we're doing in the D7 module on our user registration/edit form, specifically:

  * Enforce SAML option and remove the password field for new registrations
  * Disable editing the username on previously made accounts
  * Remove email field and make it not required
  * Override the email validation since there won't be one.
  *Implement some custom username validation to ensure username as an EID. We can likely reuse the regex logic already in our D7 utexas_saml_auth_helper.
* We'll want to add a drush command for converting users to SAML login, which is a feature of the D7 utexas_saml_auth_helper.
