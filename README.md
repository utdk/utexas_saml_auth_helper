# UTexas SAML Authentication Helper

This is a Drupal module that provides UTexas-focused UI tweaks to the contributed [samlauth](https://drupal.org/project/samlauth) module.

End-user documentation can be found at https://drupalkit.its.utexas.edu/docs/

## List of behaviors
* Declares a dependency on the [samlauth](https://www.drupal.org/project/samlauth) module, causing that module and its dependency `externalauth` to be enabled when this module is enabled.
* Includes configuration defaults for:
  * `utexas_saml_auth_helper_iid_domain: eid.utexas.edu`
  * `mail_body` -- The rewritten "account created" email body
  * `mail_subject` -- The rewritten "account created" email subject
* Set user account creation password form element to #access=FALSE and #required=FALSE.
* Disable editing the username for already-created accounts.
* Set email form element to #disabled=TRUE and #required=FALSE.
* Add custom validation that will:
  * Confirm the name entered is a valid EID
    * The regex expression from the Drupal 7 module should be reused for this.
  * Set email to EID + "@" + `utexas_saml_auth_helper_iid_domain` configuration setting.
* Set required configuration for OneLogin library and disable editing of form fields
* Define a drush command for converting all eligible users to SAML login. With the switch from `simplesamlphp_auth` to `samlauth`, this is effectively outdated, as `samlauth` will automatically convert eligible user accounts that already exist in the system.
* If the `samlauth` module is enabled, redirect the legacy simplesamlphp_auth `/saml_login` path to `/saml/login`


## Configuring SAML user roles

The module `samlauth` includes a sub-module, `samlauth_user_roles` for assigning Drupal roles to users based on affiliations provided by the Identity Provider (IdP).

This form is located at `admin/config/people/saml/user-roles`

- For "SAML Attribute", enter `urn:mace:dir:attribute-def:utexasEduPersonAffiliation`
- For "Separator", enter `|`
- For value conversions, enter in the following format. To automatically grant the `Content Editor` role to users with the `staff-current` affiliation, enter `staff-current|Content editor`. A complete converion example from `simplesamlphp_auth` to `samlauth` for `pharmacy-intranet` is shown below:

### simplesamlphp_auth syntax

```
faculty_staff:urn:mace:dir:attribute-def:utexasEduPersonAffiliation,=,faculty-future;urn:mace:dir:attribute-def:utexasEduPersonAffiliation,=,faculty-current;urn:mace:dir:attribute-def:utexasEduPersonAffiliation,=,staff-future;urn:mace:dir:attribute-def:utexasEduPersonAffiliation,=,staff-current
```

### samlauth syntax

```
faculty-future|Faculty/Staff
faculty-current|Faculty/Staff
staff-future|Faculty/Staff
staff-current|Faculty/Staff
```