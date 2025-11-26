<?php

namespace NeoPhp\Database\Schema;

/**
 * Schema Blueprint
 * Defines table structure
 */
class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected bool $creating = false;
    protected string $engine = 'InnoDB';
    protected string $charset = 'utf8mb4';
    protected string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Mark as create operation
     */
    public function create(): self
    {
        $this->creating = true;
        return $this;
    }

    /**
     * Add an ID column (auto-increment primary key)
     */
    public function id(string $column = 'id'): ColumnDefinition
    {
        return $this->bigIncrements($column);
    }

    /**
     * Add a big integer auto-increment column
     */
    public function bigIncrements(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'BIGINT');
        $col->unsigned()->autoIncrement()->primary();
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add an integer auto-increment column
     */
    public function increments(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'INT');
        $col->unsigned()->autoIncrement()->primary();
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a string column
     */
    public function string(string $column, int $length = 255): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'VARCHAR', $length);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a text column
     */
    public function text(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'TEXT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a medium text column
     */
    public function mediumText(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'MEDIUMTEXT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a long text column
     */
    public function longText(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'LONGTEXT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add an integer column
     */
    public function integer(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'INT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a big integer column
     */
    public function bigInteger(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'BIGINT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a tiny integer column
     */
    public function tinyInteger(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'TINYINT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a small integer column
     */
    public function smallInteger(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'SMALLINT');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a decimal column
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'DECIMAL', [$precision, $scale]);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a float column
     */
    public function float(string $column, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'FLOAT', [$precision, $scale]);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a double column
     */
    public function double(string $column, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'DOUBLE', [$precision, $scale]);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a boolean column
     */
    public function boolean(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'TINYINT', 1);
        $col->default(0);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add an enum column
     */
    public function enum(string $column, array $values): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'ENUM', $values);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a date column
     */
    public function date(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'DATE');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a datetime column
     */
    public function dateTime(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'DATETIME');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a timestamp column
     */
    public function timestamp(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'TIMESTAMP');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add timestamps (created_at, updated_at)
     */
    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable()->useCurrent();
        $this->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
    }

    /**
     * Add soft deletes (deleted_at)
     */
    public function softDeletes(string $column = 'deleted_at'): ColumnDefinition
    {
        return $this->timestamp($column)->nullable();
    }

    /**
     * Add a JSON column
     */
    public function json(string $column): ColumnDefinition
    {
        $col = new ColumnDefinition($column, 'JSON');
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Add a UUID column
     */
    public function uuid(string $column = 'id'): ColumnDefinition
    {
        return $this->string($column, 36);
    }

    /**
     * Add a foreign key
     */
    public function foreign(string $column): ForeignKeyDefinition
    {
        $fk = new ForeignKeyDefinition($column);
        $this->foreignKeys[] = $fk;
        return $fk;
    }

    /**
     * Add an index
     */
    public function index(string|array $columns, ?string $name = null): self
    {
        $columns = (array) $columns;
        $name = $name ?? $this->createIndexName('index', $columns);
        
        $this->indexes[] = [
            'type' => 'INDEX',
            'name' => $name,
            'columns' => $columns,
        ];

        return $this;
    }

    /**
     * Add a unique index
     */
    public function unique(string|array $columns, ?string $name = null): self
    {
        $columns = (array) $columns;
        $name = $name ?? $this->createIndexName('unique', $columns);
        
        $this->indexes[] = [
            'type' => 'UNIQUE',
            'name' => $name,
            'columns' => $columns,
        ];

        return $this;
    }

    /**
     * Add a primary key
     */
    public function primary(string|array $columns, ?string $name = null): self
    {
        $columns = (array) $columns;
        
        $this->indexes[] = [
            'type' => 'PRIMARY KEY',
            'name' => $name,
            'columns' => $columns,
        ];

        return $this;
    }

    /**
     * Drop a column
     */
    public function dropColumn(string|array $columns): self
    {
        $columns = (array) $columns;
        
        foreach ($columns as $column) {
            $this->columns[] = ['drop' => $column];
        }

        return $this;
    }

    /**
     * Rename a column
     */
    public function renameColumn(string $from, string $to): self
    {
        $this->columns[] = ['rename' => [$from, $to]];
        return $this;
    }

    /**
     * Set engine
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Set charset
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Set collation
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    /**
     * Convert blueprint to SQL
     */
    public function toSql(): string
    {
        if (!$this->creating) {
            throw new \RuntimeException('Use toStatements() for ALTER operations');
        }

        $sql = "CREATE TABLE `{$this->table}` (\n";
        
        $definitions = [];
        
        // Add columns
        foreach ($this->columns as $column) {
            if ($column instanceof ColumnDefinition) {
                $definitions[] = '  ' . $column->toSql();
            }
        }

        // Add indexes
        foreach ($this->indexes as $index) {
            $columns = '`' . implode('`, `', $index['columns']) . '`';
            
            if ($index['type'] === 'PRIMARY KEY') {
                $definitions[] = "  PRIMARY KEY ({$columns})";
            } else {
                $name = $index['name'];
                $definitions[] = "  {$index['type']} `{$name}` ({$columns})";
            }
        }

        // Add foreign keys
        foreach ($this->foreignKeys as $fk) {
            $definitions[] = '  ' . $fk->toSql();
        }

        $sql .= implode(",\n", $definitions);
        $sql .= "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation};";

        return $sql;
    }

    /**
     * Convert blueprint to ALTER statements
     */
    public function toStatements(): array
    {
        $statements = [];

        foreach ($this->columns as $column) {
            if ($column instanceof ColumnDefinition) {
                $statements[] = "ALTER TABLE `{$this->table}` ADD COLUMN " . $column->toSql();
            } elseif (isset($column['drop'])) {
                $statements[] = "ALTER TABLE `{$this->table}` DROP COLUMN `{$column['drop']}`";
            } elseif (isset($column['rename'])) {
                [$from, $to] = $column['rename'];
                $statements[] = "ALTER TABLE `{$this->table}` RENAME COLUMN `{$from}` TO `{$to}`";
            }
        }

        return $statements;
    }

    /**
     * Create index name
     */
    protected function createIndexName(string $type, array $columns): string
    {
        return $this->table . '_' . implode('_', $columns) . '_' . $type;
    }
}
