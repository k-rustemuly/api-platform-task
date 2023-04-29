<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class LoginTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testLogin(): void
    {
        $client = self::createClient();
        $container = self::getContainer();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('Fn');
        $user->setLastName('Ln');
        $user->setPhone('+7001112233');
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, 'secret')
        );
        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        $response = $client->request('POST', '/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@example.com',
                'password' => 'secret',
            ],
        ]);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // test not authorized
        $client->request('POST', '/registration');
        $this->assertResponseStatusCodeSame(401);

         // test authorized
        $client->request('POST', '/registration', ['auth_bearer' => $json['token']]);
        $this->assertResponseIsSuccessful();
    }
}
