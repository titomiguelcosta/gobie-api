<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase as TestCase;

class StatusControllerTest extends TestCase
{
    public function testEndpoint(): void
    {
        static::createClient()->request(
            'GET',
            '/status'
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'status' => 'ok',
        ]);
    }
}
