Shopify Client [![Build Status](https://travis-ci.org/dSpaceLabs/Shopify.svg?branch=master)](https://travis-ci.org/dSpaceLabs/Shopify)
==============

PHP Shopify Client for easy integration into your projects and apps

- PHP Client for working with the Shopify API
- Source code is well documented
- Heavily tested and maintained
- Production Shopify Apps are using

## Installation

```bash
composer require "dspacelabs/shopify:~0.1@dev"
```

## Usage

```php
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

```php
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
