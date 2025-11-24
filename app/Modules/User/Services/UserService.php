<?php

namespace App\Modules\User\Services;

use NeoPhp\Core\Attributes\Injectable;
use App\Modules\User\Repositories\UserRepository;

#[Injectable]
class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->repository->findBy('email', $email);
    }

    public function create(array $data): int
    {
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->repository->update($id, $data) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id) > 0;
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }
}
