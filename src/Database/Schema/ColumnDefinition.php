<?php

namespace NeoPhp\Database\Schema;

/**
 * Column Definition
 */
class ColumnDefinition
{
    protected string $name;
    protected string $type;
    protected mixed $length = null;
    protected bool $nullable = false;
    protected mixed $default = null;
    protected bool $unsigned = false;
    protected bool $autoIncrement = false;
    protected bool $primary = false;
    protected bool $unique = false;
    protected ?string $comment = null;
    protected ?string $after = null;
    protected bool $first = false;
    protected bool $useCurrent = false;
    protected bool $useCurrentOnUpdate = false;

    public function __construct(string $name, string $type, mixed $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    /**
     * Make column nullable
     */
    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    /**
     * Set default value
     */
    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Make column unsigned
     */
    public function unsigned(bool $value = true): self
    {
        $this->unsigned = $value;
        return $this;
    }

    /**
     * Make column auto increment
     */
    public function autoIncrement(bool $value = true): self
    {
        $this->autoIncrement = $value;
        return $this;
    }

    /**
     * Make column primary key
     */
    public function primary(bool $value = true): self
    {
        $this->primary = $value;
        return $this;
    }

    /**
     * Make column unique
     */
    public function unique(bool $value = true): self
    {
        $this->unique = $value;
        return $this;
    }

    /**
     * Add column comment
     */
    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Place column after another
     */
    public function after(string $column): self
    {
        $this->after = $column;
        return $this;
    }

    /**
     * Place column first
     */
    public function first(bool $value = true): self
    {
        $this->first = $value;
        return $this;
    }

    /**
     * Use CURRENT_TIMESTAMP as default
     */
    public function useCurrent(bool $value = true): self
    {
        $this->useCurrent = $value;
        return $this;
    }

    /**
     * Use CURRENT_TIMESTAMP on update
     */
    public function useCurrentOnUpdate(bool $value = true): self
    {
        $this->useCurrentOnUpdate = $value;
        return $this;
    }

    /**
     * Convert to SQL
     */
    public function toSql(): string
    {
        $sql = "`{$this->name}` {$this->type}";

        // Add length/precision
        if ($this->length !== null) {
            if (is_array($this->length)) {
                $sql .= '(' . implode(',', $this->length) . ')';
            } elseif ($this->type === 'ENUM') {
                $values = array_map(fn($v) => "'{$v}'", $this->length);
                $sql .= '(' . implode(',', $values) . ')';
            } else {
                $sql .= "({$this->length})";
            }
        }

        // Add unsigned
        if ($this->unsigned) {
            $sql .= ' UNSIGNED';
        }

        // Add nullable
        if (!$this->nullable && !$this->primary) {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }

        // Add default
        if ($this->default !== null) {
            if (is_string($this->default)) {
                $sql .= " DEFAULT '{$this->default}'";
            } elseif (is_bool($this->default)) {
                $sql .= ' DEFAULT ' . ($this->default ? '1' : '0');
            } else {
                $sql .= " DEFAULT {$this->default}";
            }
        }

        // Add CURRENT_TIMESTAMP
        if ($this->useCurrent) {
            $sql .= ' DEFAULT CURRENT_TIMESTAMP';
        }

        if ($this->useCurrentOnUpdate) {
            $sql .= ' ON UPDATE CURRENT_TIMESTAMP';
        }

        // Add auto increment
        if ($this->autoIncrement) {
            $sql .= ' AUTO_INCREMENT';
        }

        // Add comment
        if ($this->comment) {
            $sql .= " COMMENT '{$this->comment}'";
        }

        return $sql;
    }
}
