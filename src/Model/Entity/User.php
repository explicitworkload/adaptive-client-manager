<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'username' => true,
        'password' => true,
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'role' => true,
    ];

    protected array $_hidden = ['password'];

    protected function _setPassword(string $password): ?string
    {
        if ($password === '') {
            return null;
        }

        return (new DefaultPasswordHasher())->hash($password);
    }

    protected function _getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
