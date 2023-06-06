<?php

namespace App\Security;

namespace App\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class CustomPasswordEncoder implements PasswordEncoderInterface
{
    public function encodePassword(string $raw, ?string $salt): string
    {
        // No hashing or encoding is performed, return the raw password as-is
        return $raw;
    }

    public function isPasswordValid(string $encoded, string $raw, ?string $salt): bool
    {
        // Compare the encoded password with the raw password as-is
        return $encoded === $raw;
    }

    public function needsRehash(string $encoded): bool
    {
        // Since no hashing is performed, no rehashing is needed
        return false;
    }
}
