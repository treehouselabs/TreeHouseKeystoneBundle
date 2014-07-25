Authenticating
==============

Now that the bundle is set up and your services are defined, you can create tokens
and use them to access your services.

## Creating a token

Send a post request with the following body, as a json-encoded string:

```json
{
    "auth": {
        "passwordCredentials": {
            "username": "acme",
            "password": "5up3rs3cr3t"
        }
    }
}
```

Example curl request:

```sh
curl -XPOST -d '{"auth":{"passwordCredentials":{"username":"acme","password":"5up3rs3cr3t"}}}' http://example.org/tokens
```

The result will be something like this:

```json
{
    "access": {
        "token": {
            "id": "8a8e13cb-13de-11e4-8ccf-525400bc33bf",
            "expires": "2014-07-25T10:32:05+0000"
        },
        "user": {
            "id": 1234,
            "username": "acme",
        },
        "serviceCatalog": [
            {
                "name": "api",
                "type": "compute",
                "endpoints": [
                    {
                        "adminUrl": "http://api.example.org",
                        "publicUrl":"http://api.example.org"
                    }
                ]
            }
        ]
    }
}
```

The token id is the thing you want here. You can use that to authenticate when using the api like so:

```sh
curl -H "X-Auth-Token: 8a8e13cb-13de-11e4-8ccf-525400bc33bf" http://api.example.org
```

Remember to use the public url from the service, which does not have to be the same as the token url,
or even the token domain.
