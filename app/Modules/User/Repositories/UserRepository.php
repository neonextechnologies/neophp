<?php

namespace App\Modules\User\Repositories;

use NeoPhp\Database\Repository;
use NeoPhp\Core\Attributes\Injectable;

#[Injectable]
class UserRepository extends Repository
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findActive(): array
    {
        return $this->findWhere('status = ?', ['active']);
    }
}
