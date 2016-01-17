<?php

namespace Dspacelabs\Component\Shopify\Tests;

use Dspacelabs\Component\Shopify\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $client = new Client('AccessToken', 'SecretKey');
    }
}
