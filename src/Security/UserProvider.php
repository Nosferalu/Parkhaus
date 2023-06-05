<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $query = 'SELECT * FROM Parker WHERE email = :email';
        $user = $this->connection->fetchAssociative($query, ['email' => $identifier]);

        if (!$user) {
            throw new AuthenticationException(sprintf('User "%s" not found.', $identifier));
        }

        $roles = isset($user['roles']) ? explode(',', $user['roles']) : [];
        $roles[] = 'ROLE_USER'; // Add the ROLE_USER role

        return new User($user['email'], $user['password'], $roles);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
