<?php

namespace NeoPhp\Auth;

class Role
{
    protected $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function create(string $name, array $permissions = []): int
    {
        $roleId = $this->db->insert('roles', [
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        foreach ($permissions as $permission) {
            $this->attachPermission($roleId, $permission);
        }
        
        return $roleId;
    }
    
    public function attachPermission(int $roleId, string $permission): void
    {
        $permissionId = $this->getOrCreatePermission($permission);
        
        $this->db->insert('role_permissions', [
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
    
    public function detachPermission(int $roleId, string $permission): void
    {
        $permissionId = $this->getPermissionId($permission);
        
        if ($permissionId) {
            $this->db->execute(
                "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?",
                [$roleId, $permissionId]
            );
        }
    }
    
    public function hasPermission(int $roleId, string $permission): bool
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM role_permissions rp
             JOIN permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = ? AND p.name = ?",
            [$roleId, $permission]
        );
        
        return ($result[0]['count'] ?? 0) > 0;
    }
    
    public function getPermissions(int $roleId): array
    {
        return $this->db->query(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?",
            [$roleId]
        );
    }
    
    protected function getOrCreatePermission(string $permission): int
    {
        $result = $this->db->query(
            "SELECT id FROM permissions WHERE name = ?",
            [$permission]
        );
        
        if (!empty($result)) {
            return $result[0]['id'];
        }
        
        return $this->db->insert('permissions', [
            'name' => $permission,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    protected function getPermissionId(string $permission): ?int
    {
        $result = $this->db->query(
            "SELECT id FROM permissions WHERE name = ?",
            [$permission]
        );
        
        return $result[0]['id'] ?? null;
    }
}

// Add to Auth class
trait HasRoles
{
    public function assignRole(string $roleName): void
    {
        $role = $this->db->query("SELECT id FROM roles WHERE name = ?", [$roleName]);
        
        if (!empty($role)) {
            $this->db->insert('user_roles', [
                'user_id' => $this->id(),
                'role_id' => $role[0]['id']
            ]);
        }
    }
    
    public function hasRole(string $roleName): bool
    {
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
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM user_roles ur
             JOIN role_permissions rp ON ur.role_id = rp.role_id
             JOIN permissions p ON rp.permission_id = p.id
             WHERE ur.user_id = ? AND p.name = ?",
            [$this->id(), $permission]
        );
        
        return ($result[0]['count'] ?? 0) > 0;
    }
    
    public function getRoles(): array
    {
        return $this->db->query(
            "SELECT r.* FROM roles r
             JOIN user_roles ur ON r.id = ur.role_id
             WHERE ur.user_id = ?",
            [$this->id()]
        );
    }
}
