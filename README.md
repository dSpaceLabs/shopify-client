Shopify Client [![Build Status](https://travis-ci.org/dSpaceLabs/Shopify.svg?branch=master)](https://travis-ci.org/dSpaceLabs/Shopify)
==============

PHP Shopify Client for easy integration into your projects and apps

- PHP Client for working with the Shopify API
- Source code is well documented
- Heavily tested and maintained
- Production Shopify Apps are using

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

$authorizationUri = $client->getAccessToken('https://example.com/shopify/callback', $nonce);
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

## Applications using this library

- [Customer Grading](https://apps.shopify.com/customer-grading?utm_source=github&utm_medium=link&utm_campaign=social)
