<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@test.ru');

        $plainPassword = '123qwe';
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainPassword
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setBalance(31.415);
        $manager->persist($user);

        $superUser = new User();
        $superUser->setEmail('admin@test.ru');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $superUser,
            $plainPassword
        );
        $superUser->setPassword($hashedPassword);
        $superUser->setRoles(['ROLE_SUPER_ADMIN']);
        $superUser->setBalance(3141.5);

        $manager->persist($superUser);

        $manager->flush();
    }
}