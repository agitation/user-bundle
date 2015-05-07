**Agitation** is an e-commerce framework, based on Symfony2, focussed on
extendability through plugged-in APIs, UIs, payment modules and other
components.

## AgitUserBundle

This bundle manages user accounts, as well as their authentication and
authorization.

While using the security features of Symfony2, this bundle does not use the
user/role/permission components offered by Symfony2. Instead, it provides a
basic `User` entity, along with `Role` and `Capability` entities which can be
assigned to a `User`.

Roles and capabilities can be introduces by third-party bundles, AgitUserBundle
doesn’t care. In fact, this bundle itself only knows two (special) roles, and no
capabilities at all.

This has, in our opinion, two major advantages over the auth features of Symfony2:
First, it allows for real fine-granular definitions of roles and their
capabilities. Second, bundles can define their own roles and capabilities and
require users accessing frontend pages, API calls and even internal components
to have certain roles/capabilities.
