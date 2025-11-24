<?php

namespace NeoPhp\Database;

abstract class Model
{
    protected static $table;
    protected static $primaryKey = 'id';
    protected static $timestamps = true;
    protected static $connection;

    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function setConnection(Database $db): void
    {
        static::$connection = $db;
    }

    protected static function getConnection(): Database
    {
        if (!static::$connection) {
            static::$connection = app('db');
        }

        return static::$connection;
    }

    public static function all(): array
    {
        $results = static::getConnection()->all(static::$table);
        
        return array_map(function ($item) {
            return new static($item);
        }, $results);
    }

    public static function find($id): ?self
    {
        $result = static::getConnection()->find(static::$table, $id, static::$primaryKey);
        
        if (!$result) {
            return null;
        }

        $model = new static($result);
        $model->exists = true;
        $model->original = $result;
        
        return $model;
    }

    public static function where(string $column, $operator, $value = null): QueryBuilder
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $builder = new QueryBuilder(static::getConnection(), static::$table, static::class);
        return $builder->where($column, $operator, $value);
    }

    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        
        return $model;
    }

    public function save(): bool
    {
        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            
            if (!$this->exists) {
                $this->attributes['created_at'] = $now;
            }
            
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    protected function performInsert(): bool
    {
        $id = static::getConnection()->insert(static::$table, $this->attributes);
        
        $this->setAttribute(static::$primaryKey, $id);
        $this->exists = true;
        $this->original = $this->attributes;
        
        return true;
    }

    protected function performUpdate(): bool
    {
        $id = $this->attributes[static::$primaryKey];
        $where = static::$primaryKey . ' = ?';
        
        $updated = static::getConnection()->update(
            static::$table,
            $this->attributes,
            $where,
            [$id]
        );

        $this->original = $this->attributes;
        
        return $updated > 0;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $id = $this->attributes[static::$primaryKey];
        $where = static::$primaryKey . ' = ?';
        
        $deleted = static::getConnection()->delete(static::$table, $where, [$id]);
        
        $this->exists = false;
        
        return $deleted > 0;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->attributes);
    }

    public function fresh(): ?self
    {
        if (!$this->exists) {
            return null;
        }

        return static::find($this->attributes[static::$primaryKey]);
    }
}
