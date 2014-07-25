Defining services
=================

You can define services which an authenticated user can use. A service could be something like
an api, or an object store. Each service has at least one endpoint at which it can be reached.

There are a couple of ways you can define endpoints:

```yaml
tree_house_keystone:
  user_class: Acme\DemoBundle\Entity\User
  services:
    # Shortest notation: just a simple type/url. The public and admin url will be the same.
    api:
      type: compute
      endpoint: https://api.example.org/

    # Same as above, but with multiple endpoints
    api2:
      type: compute
      endpoint:
        - http://api.example.org/
        - https://api.example.org/

    # A simple endpoint but with different public/admin urls
    cdn:
      type: object-store
      endpoint: { public_url: http://examplecdn.org/, admin_url: https://admin.example.org/ }

    # Same as above, but supplied as an array
    cdn2:
      type: object-store
      endpoint:
        - { public_url: http://examplecdn.org/, admin_url: https://admin.example.org/ }

    # Same as above, but supplied as an array, with multiple endpoints
    cdn3:
      type: object-store
      endpoint:
        -
          public_url: http://cdn.example.org/
          admin_url: https://admin.example.org/
        -
          public_url: http://examplecdn.org/
          admin_url: https://admin.examplecdn.org/
```

When a user requests a token, all their associated services are returned with it. This exposes
the available services for the user, as well as the endpoints they can use. With this mechanism
you can for example release a new api version, at a new endpoint (ie: `/api/v2/`), and all users
will automatically use the new api.

Users can be linked to a service via a role or an expression (if you have the
[Expression Language][el] component installed):

```yaml
tree_house_keystone:
  user_class: Acme\DemoBundle\Entity\User
  services:
    api:
      type: compute
      endpoint: https://api.example.org/
      role: ROLE_USER
    cdn:
      type: object-store
      endpoint: https://cdn.example.org/
      expression: 'is_authenticated() and user.isSuperAdmin()'
```

Now a regular user will only get the api service with their token, while the super admins also
get the cdn service.

## NOTE: This is NOT authorization method!

Please note that this only restricts the discovery of services to authenticated users. It does
not provide authorization for your services: you still have to do that yourself! The token only
authenticates users, meaning it checks if the user's credentials are valid. Authorization is
the process that checks if a user is allowed to do or access something. Read more about this
in the [security chapter][security]
of the Symfony documentation.

## Public url vs admin url
A service has both a public url and an admin url. The [Keystone specification][keystone-spec] describes this as:

> **publicUrl**: The URL of the public-facing endpoint for the service
> (e.g., http://192.168.206.130:9292 or http://192.168.206.130:8774/v2/%(tenant_id)s)
>
> **adminUrl**: The URL for the admin endpoint for the service. The Keystone and EC2 services use different
> endpoints for adminurl and publicurl, but for other services these endpoints will be the same.

The admin endpoint could be different if you want to separate for instance read/write access.
You'd still have to implement authorization for the different parts of your application, but
this way you could separate the routes, or even domains. For most use cases though they will be the same.

[el]: http://symfony.com/doc/current/components/expression_language/index.html
[security]: http://symfony.com/doc/current/book/security.html#how-security-works-authentication-and-authorization
[keystone-spec]: http://docs.openstack.org/grizzly/openstack-compute/install/apt/content/elements-of-keystone-service-catalog-entry.html
