# Requirements Overview

This file aims to outline the required architecture for the utexas_saml_auth_helper Drupal 8 module.

* *utexas_saml_auth_helper.info.yml*
  * Declare a dependency on the [simplesamlphp_auth](https://www.drupal.org/project/simplesamlphp_auth) module.

* *config/install/utexas_saml_auth_helper.settings.yml*
  * This will contain our custom configuration definitions and defaults. Specifically:
    * `utexas_saml_auth_helper_iid_domain: eid.utexas.edu`
  
* *utexas_saml_auth_helper.module*
  * `hook_form_FORM_ID_alter` for the user registration/edit form.
    * Modify form element for "This is a SAML account" to "This is a UTLogin account"
    * Default the "This is a UTLogin account" option to true, and disable the element.
    * Set password form element to #access=FALSE and #required=FALSE.
    * Disable editing the username for non-admins.
    * Set email form element to #disabled=TRUE and #required=FALSE.
    * Add custom validation that will:
      * Confirm the name entered is a valid EID
        * The regex expression from the Drupal 7 module should be reused for this.
      * Set email to EID + "@" + `utexas_saml_auth_helper_iid_domain` configuration setting.
      
* *utexas_saml_auth_helper.permissions.yml*
  * Define permission 'change saml authentication setting'.

* *utexas_saml_auth_helper.install*
  * Set configuration for simplesamlphp_auth
    * Users being auto-provisioned set to FALSE
    * Only user 1 can login via Drupal
  * Set config for 'Site Manager' role (user.role.site_manager) to add the 'change saml authentication setting' permission.
  * Modify the config for `user.mail` (the new user registration email) to include proper verbiage for UT EID based accounts.
  * `hook_uninstall`
    * Clean `authmap` table of any records with module = utexas_saml_auth_helper
    * Reset configuration for simplesamlphp_auth described above
    
* *utexas_saml_auth_helper.drush.inc*
  * Define a drush command for converting all eligible users to SAML login
    * This should provide a way for a developer to pass a particular username or "all" to be converted to SAML login.
    * Much of the logic from the D7 module can be reused here. The entity and database querying functions will need to be updated to use the Drupal 8 equivalent services to load the user entities, and to perform a database merge to the authmap table.

