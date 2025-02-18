# UTexas SAML Authentication Helper

This is a Drupal module that provides UTexas-focused UI tweaks to the contributed [simplesamlphp_auth](https://drupal.org/project/simplesamlphp_auth) module.

Full documentation can be found at https://drupalkit.its.utexas.edu/docs/

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