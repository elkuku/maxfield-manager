<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setUserIdentifier('user@example.com')
            ->setRole(User::ROLES['user']);

        $manager->persist($user);

        $adminUser = (new User())
            ->setUserIdentifier('admin@example.com')
            ->setRole(User::ROLES['admin']);

        $manager->persist($adminUser);

        $manager->flush();
    }
}
