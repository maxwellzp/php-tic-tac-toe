<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserFactory $userFactory,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $plainPassword = 'password';

        $userX = $this->userFactory->create('player1@example.com', $plainPassword);
        $userX->setPassword($this->userPasswordHasher->hashPassword($userX, $plainPassword));
        $manager->persist($userX);

        $userO = $this->userFactory->create('player2@example.com', $plainPassword);
        $userO->setPassword($this->userPasswordHasher->hashPassword($userO, $plainPassword));
        $manager->persist($userO);

        $manager->flush();
    }
}
