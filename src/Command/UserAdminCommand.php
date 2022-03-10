<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\SymfonyUtils\Command\UserAdminBaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'user-admin',
    description: 'Administer user accounts',
    aliases: ['useradmin', 'admin']
)]
class UserAdminCommand extends UserAdminBaseCommand
{
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ) {
        parent::__construct($entityManager, $userRepository, User::ROLES);
    }
}
