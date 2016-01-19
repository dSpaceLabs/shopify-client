<?php

namespace Dspacelabs\Component\Shopify\Tests;

use Dspacelabs\Component\Shopify\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
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
}
