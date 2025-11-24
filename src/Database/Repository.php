<?php

namespace NeoPhp\Database;

abstract class Repository
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(int $id): ?array
    {
        return $this->db->find($this->table, $id, $this->primaryKey);
    }

    public function findBy(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1";
        $results = $this->db->query($sql, [$value]);
        
        return $results[0] ?? null;
    }

    public function findAll(): array
    {
        return $this->db->all($this->table);
    }

    public function findWhere(string $where, array $params = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        return $this->db->query($sql, $params);
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $where = "{$this->primaryKey} = ?";
        return $this->db->update($this->table, $data, $where, [$id]);
    }

    public function delete(int $id): int
    {
        $where = "{$this->primaryKey} = ?";
        return $this->db->delete($this->table, $where, [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params);
        
        return (int) ($result[0]['count'] ?? 0);
    }

    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    public function paginate(int $perPage = 15, int $page = null): \NeoPhp\Pagination\Paginator
    {
        $page = $page ?? (int) ($_GET['page'] ?? 1);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $this->count();
        
        // Get items
        $items = $this->db->query(
            "SELECT * FROM {$this->table} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        return new \NeoPhp\Pagination\Paginator($items, $total, $perPage, $page);
    }

    protected function query(string $sql, array $params = []): array
    {
        return $this->db->query($sql, $params);
    }

    protected function execute(string $sql, array $params = []): bool
    {
        return $this->db->execute($sql, $params);
    }
}
