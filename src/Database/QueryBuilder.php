<?php

namespace NeoPhp\Database;

class QueryBuilder
{
    protected $db;
    protected $table;
    protected $modelClass;
    protected $wheres = [];
    protected $bindings = [];
    protected $orderBy = [];
    protected $limit;
    protected $offset;

    public function __construct(Database $db, string $table, string $modelClass)
    {
        $this->db = $db;
        $this->table = $table;
        $this->modelClass = $modelClass;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $results = $this->db->query($sql, $this->bindings);
        
        return array_map(function ($item) {
            $model = new $this->modelClass($item);
            $model->exists = true;
            return $model;
        }, $results);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        
        $result = $this->db->query($sql, $this->bindings);
        
        return (int) ($result[0]['count'] ?? 0);
    }

    protected function buildSelectQuery(): string
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
}
