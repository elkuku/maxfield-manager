<?php

namespace App\DataFixtures;

use App\Entity\Maxfield;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setIdentifier('user');
        $manager->persist($user);

        $manager->persist(
            (new User())
                ->setIdentifier('admin')
                ->setRoles([User::ROLES['admin']])
        );

        $manager->persist(
            (new Maxfield())
                ->setName('test')
                ->setOwner($user)
        );

        $manager->flush();
    }
}
