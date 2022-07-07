<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\DTO\UserDTO;
use App\Entity\User;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends AbstractTest
{
    private $serializer;
    private string $apiPath = '/api/v1';

    public function setUp(): void
    {
        $this->serializer = SerializerBuilder::create()->build();
        //$this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    private function getToken($user)
    {
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    public function testGetCurrentUserWithAuth(): void
    {
        $user = [
            'username' => 'user@test.ru',
            'password' => '123qwe'
        ];
        $token = $this->getToken($user);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            $this->apiPath . '/users/current',
            server: $headers,
        );

        $this->assertResponseOk();

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $userDto = $this->serializer->deserialize(
            $client->getResponse()->getContent(),
            UserDTO::class,
            'json'
        );

        $userRepository = self::getEntityManager()->getRepository(User::class);
        $actualUser = $userRepository->findOneBy(['email' => $user['username']]);

        self::assertEquals($actualUser->getEmail(), $userDto->username);
        self::assertEquals($actualUser->getRoles(), $userDto->roles);
        self::assertEquals($actualUser->getBalance(), $userDto->balance);
    }

    public function testGetCurrentUserWithInvalidToken(): void
    {
        $token = '123qwe';

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            $this->apiPath . '/users/current',
            server: $headers,
        );

        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json);

        self::assertEquals('401', $json['code']);
        self::assertEquals('Invalid JWT Token', $json['message']);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            $this->apiPath . '/users/current',
            server: $headers,
        );

        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json);

        self::assertEquals('401', $json['code']);
        self::assertEquals('JWT Token not found', $json['message']);
    }
}
