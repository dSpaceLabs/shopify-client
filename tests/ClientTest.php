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
}
