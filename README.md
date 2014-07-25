TreeHouseKeystoneBundle
=======================

A Symfony2 implementation of the [OpenStack Identity Service API][1],
built on top of it's security component.

[![Build Status](https://travis-ci.org/treehouselabs/TreeHouseKeystoneBundle.svg)](https://travis-ci.org/treehouselabs/TreeHouseKeystoneBundle)

You can use this bundle to add a token-based authentication
mechanism. The token is sent using an HTTP header.

More information about the protocol can be found here:
http://docs.openstack.org/api/openstack-identity-service/2.0/

[1]: http://docs.openstack.org/api/openstack-identity-service/2.0/

## Notes
This bundle is a work in progress and does not cover the
_complete_ OpenStack Indentity Service API spec. While we
are pretty confident about the quality of this
implementation thus far, we are not experts in this field.

Use at your own risk!

## Documentation

1. [Setup][doc-setup]
2. [Defining services][doc-services]
3. [Authenticating][doc-authenticating]

[doc-setup]:          /src/TreeHouse/KeystoneBundle/Resources/doc/01-setup.md
[doc-services]:       /src/TreeHouse/KeystoneBundle/Resources/doc/02-defining-services.md
[doc-authenticating]: /src/TreeHouse/KeystoneBundle/Resources/doc/03-authenticating.md

## Acknowledgements
Some parts are inspired by [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle).

## Contribute
You can report problems, bugs, or feature requests to the
[issue tracker](https://github.com/treehouselabs/TreeHouseKeystoneBundle/issues).

If you have a patch or other update, feel free to send us a
[pull request](https://github.com/treehouselabs/TreeHouseKeystoneBundle/pulls).
