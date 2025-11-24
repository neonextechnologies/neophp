<?php

namespace NeoPhp\Auth;

use NeoPhp\Database\Database;

class Auth
{
    protected $db;
    protected $user = null;
    protected $userModel = 'App\\Models\\User';

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->loadUserFromSession();
    }

    protected function loadUserFromSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $this->user = $this->db->find('users', $_SESSION['user_id']);
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->db->query(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );

        if (empty($user)) {
            return false;
        }

        $user = $user[0];

        if (password_verify($password, $user['password'])) {
            $this->login($user);
            return true;
        }

        return false;
    }

    public function login(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $this->user = $user;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user_id']);
        $this->user = null;
        
        session_destroy();
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function guest(): bool
    {
        return $this->user === null;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user['id'] ?? null;
    }

    public function register(array $data): ?int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->insert('users', $data);
    }

    public function hasRole(string $roleName): bool
    {
        if (!$this->check()) {
            return false;
        }

        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM user_roles ur
             JOIN roles r ON ur.role_id = r.id
             WHERE ur.user_id = ? AND r.name = ?",
            [$this->id(), $roleName]
        );
        
        return ($result[0]['count'] ?? 0) > 0;
    }
    
    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }

        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM user_roles ur
             JOIN role_permissions rp ON ur.role_id = rp.role_id
             JOIN permissions p ON rp.permission_id = p.id
             WHERE ur.user_id = ? AND p.name = ?",
            [$this->id(), $permission]
        );
        
        return ($result[0]['count'] ?? 0) > 0;
    }
    
    public function assignRole(string $roleName): void
    {
        if (!$this->check()) {
            return;
        }

        $role = $this->db->query("SELECT id FROM roles WHERE name = ?", [$roleName]);
        
        if (!empty($role)) {
            $this->db->insert('user_roles', [
                'user_id' => $this->id(),
                'role_id' => $role[0]['id']
            ]);
        }
    }

    public function getRoles(): array
    {
        if (!$this->check()) {
            return [];
        }

        return $this->db->query(
            "SELECT r.* FROM roles r
             JOIN user_roles ur ON r.id = ur.role_id
             WHERE ur.user_id = ?",
            [$this->id()]
        );
    }
}
