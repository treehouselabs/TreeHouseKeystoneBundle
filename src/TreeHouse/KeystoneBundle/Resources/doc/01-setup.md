## Installation

Add dependency:

```
composer require treehouselabs/keystone-bundle:~1.0
```

Enable bundle:

```php
$bundles[] = new TreeHouse\KeystoneBundle\TreeHouseKeystoneBundle();
```

## Configuration

Configure the user class and services:

```yaml
# app/config/config.yml
tree_house_keystone:
  user_class: Acme\DemoBundle\Entity\User
  services:
    api:
      type: compute
      endpoint: http://api.acme.org/
```

Enable the routing (needed to obtain a token):

```yaml
# app/config/routing.yml
tree_house_keystone:
  resource: @TreeHouseKeystoneBundle/Resources/config/routing.yml
```

Configure firewalls for the token route and the part for which token-authentication is needed:

```yaml
# app/config/security.yml
security:
  firewalls:
    api_tokens:
      pattern:   ^/api/tokens
      stateless: true
      keystone-user: ~

    api:
      pattern:   ^/api/
      provider:  main
      stateless: true
      simple_preauth:
        authenticator: tree_house.keystone.token_authenticator
```

That's it, now `/api/tokens` will expect authentication using a POST request with json data.
When the authentication succeeds, a token is returned. This token can be used to access the api
in our example, by sending it as a header. This is explained in the next chapter.

