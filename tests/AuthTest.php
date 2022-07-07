<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends AbstractTest
{
    private $serializer;
    private string $apiPath = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = SerializerBuilder::create()->build();
        //$this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    public function testAuthWithExistingUser(): void
    {
        $user = [
            'username' => 'user@test.ru',
            'password' => '123qwe'
        ];

        $client = self::getClient();
        $client->request(
            'POST',
            $this->apiPath . '/auth',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseOk();

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['token']);
    }

    public function testAuthWithNotExistingUser(): void
    {
        $user = [
            'username' => 'user123@test.rul',
            'password' => '123qwe'
        ];

        $client = self::getClient();
        $client->request(
            'POST',
            $this->apiPath . '/auth',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['code']);
        self::assertNotEmpty($json['message']);

        self::assertEquals('401', $json['code']);
        self::assertEquals('Invalid credentials.', $json['message']);
    }

    public function testRegistrationSuccessful(): void
    {
        $user = [
            'username' => 'user1@test.rul',
            'password' => '123qwe'
        ];

        $client = self::getClient();
        $client->request(
            'POST',
            $this->apiPath . '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_CREATED);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['token']);
        self::assertNotEmpty($json['roles']);

        self::assertContains('ROLE_USER', $json['roles']);
    }

    public function testRegistrationValidationErrors(): void
    {
        $user = [
            'username' => 'user1test.ru',
            'password' => '123qwe'
        ];

        $client = self::getClient();

        $client->request(
            'POST',
            $this->apiPath . '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['errors']);
        self::assertNotEmpty($json['errors']['username']);

        self::assertContains("The email \"user1test.ru\" is not a valid email.", $json['errors']['username']);

        $user = [
            'username' => 'user1@test.ru',
            'password' => '123'
        ];

        $client = self::getClient();

        $client->request(
            'POST',
            $this->apiPath . '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['errors']);
        self::assertNotEmpty($json['errors']['password']);

        self::assertContains("The password must be at least 6 characters.", $json['errors']['password']);

        $user = [
            'username' => '',
            'password' => ''
        ];

        $client->request(
            'POST',
            $this->apiPath . '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['errors']);
        self::assertNotEmpty($json['errors']['password']);
        self::assertNotEmpty($json['errors']['username']);

        self::assertContains("The password must be at least 6 characters.", $json['errors']['password']);
        self::assertContains("The password field can't be blank.", $json['errors']['password']);
        self::assertContains("The username field can't be blank.", $json['errors']['username']);

        $user = [
            'username' => 'user@test.ru',
            'password' => '123qwe'
        ];

        $client->request(
            'POST',
            $this->apiPath . '/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->serializer->serialize($user, 'json')
        );

        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);

        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['errors']);
        self::assertNotEmpty($json['errors']['username']);

        self::assertContains("User user@test.ru already exists.", $json['errors']['username']);
    }
}
