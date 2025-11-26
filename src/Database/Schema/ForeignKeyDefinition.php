<?php

namespace NeoPhp\Database\Schema;

/**
 * Foreign Key Definition
 */
class ForeignKeyDefinition
{
    protected string $column;
    protected ?string $references = null;
    protected ?string $on = null;
    protected string $onDelete = 'RESTRICT';
    protected string $onUpdate = 'RESTRICT';
    protected ?string $name = null;

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * Set referenced column
     */
    public function references(string $column): self
    {
        $this->references = $column;
        return $this;
    }

    /**
     * Set referenced table
     */
    public function on(string $table): self
    {
        $this->on = $table;
        return $this;
    }

    /**
     * Set on delete action
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    /**
     * Set on update action
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    /**
     * Set constraint name
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Cascade on delete
     */
    public function cascadeOnDelete(): self
    {
        return $this->onDelete('CASCADE');
    }

    /**
     * Cascade on update
     */
    public function cascadeOnUpdate(): self
    {
        return $this->onUpdate('CASCADE');
    }

    /**
     * Set null on delete
     */
    public function nullOnDelete(): self
    {
        return $this->onDelete('SET NULL');
    }

    /**
     * Convert to SQL
     */
    public function toSql(): string
    {
        if (!$this->references || !$this->on) {
            throw new \RuntimeException('Foreign key must specify references() and on()');
        }

        $name = $this->name ?? "fk_{$this->column}_{$this->on}_{$this->references}";

        return "CONSTRAINT `{$name}` FOREIGN KEY (`{$this->column}`) " .
               "REFERENCES `{$this->on}` (`{$this->references}`) " .
               "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
    }
}
