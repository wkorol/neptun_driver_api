<?php

declare(strict_types=1);

namespace App\User\Domain;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private Uuid $id;
    private string $email;
    private string $password;
    /**
     * @var string[]
     */
    private array $roles;

    /**
     * @param string[] $roles
     */
    public function __construct(string $email, array $roles = ['ROLE_USER'])
    {
        $this->id = Uuid::v4();
        $this->email = $email;
        $this->roles = $roles;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function promoteToAdmin(): void
    {
        $this->setRoles(['ROLE_ADMIN']);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
