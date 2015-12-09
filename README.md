KeystoneBundle
==============

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

A Symfony2 implementation of the [OpenStack Identity API v2.0][1],
built on top of it's security component.

You can use this bundle to add a token-based authentication
mechanism. The token is sent using an HTTP header.

More information about the protocol can be found here:
http://developer.openstack.org/api-ref-identity-v2.html

[1]: http://developer.openstack.org/api-ref-identity-v2.html


## Installation

```sh
composer require treehouselabs/keystone-bundle:^2.0
```


## Documentation

1. [Setup][doc-setup]
2. [Defining services][doc-services]
3. [Authenticating][doc-authenticating]

[doc-setup]:          /src/TreeHouse/KeystoneBundle/Resources/doc/01-setup.md
[doc-services]:       /src/TreeHouse/KeystoneBundle/Resources/doc/02-defining-services.md
[doc-authenticating]: /src/TreeHouse/KeystoneBundle/Resources/doc/03-authenticating.md


## Notes
This bundle does not cover the _complete_ OpenStack Indentity API spec. While
we are pretty confident about the quality of this implementation thus far, and
use it in production ourselves, we are not experts in this field. Use at your
own risk.


## Security

If you discover any security related issues, please email dev@treehouse.nl
instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


## Acknowledgements

Some parts are inspired by [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle).


## Credits

- [Peter Kruithof][link-author]
- [All Contributors][link-contributors]


[ico-version]: https://img.shields.io/packagist/v/treehouselabs/keystone-bundle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/treehouselabs/keystone-bundle/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/treehouselabs/keystone-bundle.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/treehouselabs/keystone-bundle.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/treehouselabs/keystone-bundle.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/treehouselabs/keystone-bundle
[link-travis]: https://travis-ci.org/treehouselabs/keystone-bundle
[link-scrutinizer]: https://scrutinizer-ci.com/g/treehouselabs/keystone-bundle/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/treehouselabs/keystone-bundle
[link-downloads]: https://packagist.org/packages/treehouselabs/keystone-bundle
[link-author]: https://github.com/treehouselabs
[link-contributors]: ../../contributors
