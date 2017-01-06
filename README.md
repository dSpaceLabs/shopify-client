Shopify Client [![Build Status](https://travis-ci.org/dSpaceLabs/Shopify.svg?branch=master)](https://travis-ci.org/dSpaceLabs/Shopify)
==============

PHP Shopify Client for easy integration into your projects and apps

- PHP Client for working with the Shopify API
- Source code is well documented
- Heavily tested and maintained
- Production Shopify Apps are using
- Maintain a high standard of code quality [![Code Climate](https://codeclimate.com/github/dSpaceLabs/Shopify/badges/gpa.svg)](https://codeclimate.com/github/dSpaceLabs/Shopify)
- Private apps support

## Requirements

- PHP cURL extension
- PHP >= 5.4
  - See [Travis CI](https://travis-ci.org/dSpaceLabs/Shopify) for builds of each
    version
- [Shopify Partner Account](https://developers.shopify.com/?ref=dspace)

## Installation

```bash
composer require "dspacelabs/shopify:~0.1@dev"
```

## Usage

### Redirect user to Shopify to authorize your application

```php
<?php

use Dspacelabs\Component\Shopify\Client;

$client = new Client($accessKey, $secretKey);
if (!$client->isValid($request->query->all())) {
    throw new AccessDeniedError();
}

$client->setShop('example.myshopify.com'); // `example` is also acceptable

// List of scopes can be in the Client class
$client->setScopes(
    array(
        Client::SCOPE_WRITE_CUSTOMERS,
        Client::SCOPE_READ_CUSTOMERS
    )
);

$nonce = time(); // Save in session, used in callback action

$authorizationUri = $client->getAuthorizationUrl('https://example.com/shopify/callback', $nonce);
// redirect user to $authorizationUri
```

### Shopify redirects user back to your callback url

```php
<?php

use Dspacelabs\Component\Shopify\Client;

if (!$session->has('nonce')) {
    throw new AccessedDeniedError();
}

$client = new Client($accessKey, $secretKey);

// `isValid` takes array of query parameters, think $_GET, $_POST, etc.
// This example is using a Request object from the symfony/http-foundation
// library
if (!$client->isValid($request->query->all())) {
    throw new \AccessDeniedError();
}

$client->setShop('example.myshopify.com');

// Persist access token in database
$accessToken = $client->getAccessToken($request->query->get('code'));
```

### Making requests to Shopify

```php
<?php

use Dspacelabs\Component\Shopify\Client;

$client = new Client($accessKey, $secretKey):
$client
    ->setShop('example.myshopify.com')
    ->setAccessToken($accessToken);

$result = $client->call('GET', '/admin/customers.json');

// Process $result
```

### Recurring application charges

@todo

### Creating and using webhooks

@todo

### Private Apps

See [Generate private app credentials](https://help.shopify.com/api/guides/api-credentials#generate-private-app-credentials).

```php
<?php

use Dspacelabs\Component\Shopify\Client;

/**
 * The API Key and Password are generated for you on Shopify once you create
 * Your private app. Those are the credentials you need here.
 */
$client = new Client($apiKey, $password):
$client
    ->setPrivate(true)
    ->setShop('example.myshopify.com');
```

## Applications using this library

- [lvl67](http://www.lvl67.com)
