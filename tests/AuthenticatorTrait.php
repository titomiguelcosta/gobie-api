<?php

declare(strict_types=1);

namespace App\Tests;

trait AuthenticatorTrait
{
    static array $cache = [];

    public function authenticate(string $username, string $password = '1234567'): string
    {
        if (array_key_exists($username, self::$cache)) {
            return self::$cache[$username];
        }

        $response = static::createClient()->request(
            'POST',
            '/users/auth',
            [
                'headers' => [
                    'content-type' => 'application/json'
                ],
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();

        self::$cache[$username] = $token = $response->toArray()['token'];

        return $token;
    }
}
