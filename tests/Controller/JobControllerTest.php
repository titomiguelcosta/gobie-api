<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase as TestCase;
use App\Tests\AuthenticatorTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class JobControllerTest extends TestCase
{
    use RefreshDatabaseTrait;
    use AuthenticatorTrait;

    public function testEndpoint(): void
    {
        $token = $this->authenticate('admin');

        static::createClient()->request(
            'GET',
            '/jobs',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'hydra:totalItems' => 4,
        ]);
    }
}
