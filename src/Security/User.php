<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private $email;
    private $password;
    private $roles;

    public function __construct(string $email, string $password, array $roles = [])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    // Implement other required methods of UserInterface
    // ...

    public function eraseCredentials()
    {
        // If you have any sensitive data in the user object, remove it here
    }
}
