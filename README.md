Shopify Client
==============

PHP Shopify Client

NOTE: This project is under heavy development.

## Installation

```bash
composer require "dspacelabs/shopify:~0.1@dev"
```

## Usage

```php
$client = new \Dspace\Component\Shopify\Client($accessKey, $secretKey);

if (!$client->isValid($request->query->all())) {
    throw new \AccessDeniedError();
}

//$client->setShop('example.myshopify.com');
$client->setShop($request->query->getShop('shop'));
$client->setScopes('read_products,read_customers');

$accessToken = $client->getAccessToken($request->query->get('code'));
```
