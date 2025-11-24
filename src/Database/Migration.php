<?php

namespace NeoPhp\Database;

class Migration
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    protected function createTable(string $table, callable $callback): void
    {
        $schema = new Schema($table);
        $callback($schema);
        
        $sql = $schema->toSql();
        $this->db->execute($sql);
    }

    protected function dropTable(string $table): void
    {
        $this->db->execute("DROP TABLE IF EXISTS {$table}");
    }

    protected function table(string $table, callable $callback): void
    {
        $schema = new Schema($table, true);
        $callback($schema);
        
        foreach ($schema->getCommands() as $sql) {
            $this->db->execute($sql);
        }
    }
}

class Schema
{
    protected $table;
    protected $columns = [];
    protected $commands = [];
    protected $isAltering = false;

    public function __construct(string $table, bool $isAltering = false)
    {
        $this->table = $table;
        $this->isAltering = $isAltering;
    }

    public function id(): self
    {
        $this->columns[] = "id INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} VARCHAR({$length})";
        } else {
            $this->columns[] = "{$name} VARCHAR({$length})";
        }
        return $this;
    }

    public function text(string $name): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} TEXT";
        } else {
            $this->columns[] = "{$name} TEXT";
        }
        return $this;
    }

    public function integer(string $name): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} INT";
        } else {
            $this->columns[] = "{$name} INT";
        }
        return $this;
    }

    public function boolean(string $name): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} TINYINT(1)";
        } else {
            $this->columns[] = "{$name} TINYINT(1)";
        }
        return $this;
    }

    public function timestamp(string $name): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} TIMESTAMP NULL";
        } else {
            $this->columns[] = "{$name} TIMESTAMP NULL";
        }
        return $this;
    }

    public function timestamps(): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        } else {
            $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        }
        return $this;
    }

    public function softDeletes(): self
    {
        if ($this->isAltering) {
            $this->commands[] = "ALTER TABLE {$this->table} ADD COLUMN deleted_at TIMESTAMP NULL";
        } else {
            $this->columns[] = "deleted_at TIMESTAMP NULL";
        }
        return $this;
    }

    public function foreign(string $column): self
    {
        // Simplified foreign key support
        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->commands[] = "ALTER TABLE {$this->table} DROP COLUMN {$name}";
        return $this;
    }

    public function toSql(): string
    {
        $columns = implode(", ", $this->columns);
        return "CREATE TABLE {$this->table} ({$columns}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}
