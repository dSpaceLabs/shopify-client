<?php

namespace Dspacelabs\Component\Shopify\Tests;

use Dspacelabs\Component\Shopify\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider shopSuccessProvider
     */
    public function testSetShop($shop, $expected)
    {
        $client = new Client('AccessToken', 'SecretKey');
        $this->assertNull($client->getShop());

        $client->setShop($shop);

        $this->assertSame($expected, $client->getShop());
    }

    public function shopSuccessProvider()
    {
        return array(
            array('example', 'example'),
            array('example.myshopify.com', 'example'),
        );
    }

    public function testScopes()
    {
        $client = new Client('AccessToken', 'SecretKey');
        $this->assertEmpty($client->getScopes(), 'By default the scopes should be an empty array');
        $this->assertInternalType('array', $client->getScopes(), 'Expected getScopes to return an array');

        $client->setScopes(array(Client::SCOPE_READ_CUSTOMERS));

        $this->assertCount(1, $client->getScopes());
        $this->assertInternalType('array', $client->getScopes());

        $client->addScope(Client::SCOPE_WRITE_CUSTOMERS);

        $this->assertCount(2, $client->getScopes());
        $this->assertInternalType('array', $client->getScopes());
    }

    public function testBasicAccessToken()
    {
        $client = new Client('AccessToken', 'SecretKey');

        $client->setAccessToken('test');
        $this->assertSame('test', $client->getAccessToken());
    }

    public function testGetAuthorizationUrl()
    {
        $client = new Client('AccessToken', 'SecretKey');
        $client
            ->setShop('example.myshopify.com')
            ->addScope(Client::SCOPE_READ_CUSTOMERS);

        $actual   = $client->getAuthorizationUrl('https://example.com', 1);
        $expected = 'https://example.myshopify.com/admin/oauth/authorize?client_id=AccessToken&scope=read_customers&redirect_uri=https%3A%2F%2Fexample.com&state=1';

        $this->assertSame($expected, $actual);
    }
}
