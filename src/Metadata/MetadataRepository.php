<?php

namespace NeoPhp\Metadata;

use NeoPhp\Contracts\MetadataInterface;
use NeoPhp\Contracts\CacheInterface;

/**
 * Metadata Repository
 * Parse and cache model metadata from attributes
 * Core of metadata-driven architecture
 */
class MetadataRepository implements MetadataInterface
{
    protected ?CacheInterface $cache;
    protected array $metadata = [];
    protected string $cachePrefix = 'metadata:';
    protected int $cacheTtl = 3600;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Get table metadata
     */
    public function getTableMetadata(string $table): array
    {
        // Search for model with this table name
        foreach ($this->metadata as $model => $data) {
            if ($data['table'] === $table) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Get model metadata
     */
    public function getModelMetadata(string $model): array
    {
        $cacheKey = $this->cachePrefix . $model;

        // Check cache first
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        // Check in-memory cache
        if (isset($this->metadata[$model])) {
            return $this->metadata[$model];
        }

        // Parse metadata from class
        $metadata = $this->parseFromClass($model);
        
        // Store in caches
        $this->metadata[$model] = $metadata;
        if ($this->cache) {
            $this->cache->set($cacheKey, $metadata, $this->cacheTtl);
        }

        return $metadata;
    }

    /**
     * Get field metadata
     */
    public function getFieldMetadata(string $table, string $field): array
    {
        $tableMetadata = $this->getTableMetadata($table);
        return $tableMetadata['fields'][$field] ?? [];
    }

    /**
     * Get validation rules from metadata
     */
    public function getValidationRules(string $model): array
    {
        $metadata = $this->getModelMetadata($model);
        $rules = [];

        foreach ($metadata['fields'] as $fieldName => $fieldMeta) {
            if (isset($fieldMeta['validation']) && !empty($fieldMeta['validation'])) {
                $rules[$fieldName] = is_array($fieldMeta['validation']) 
                    ? implode('|', $fieldMeta['validation'])
                    : $fieldMeta['validation'];
            }
        }

        return $rules;
    }

    /**
     * Get relationships from metadata
     */
    public function getRelationships(string $model): array
    {
        $metadata = $this->getModelMetadata($model);
        return $metadata['relationships'] ?? [];
    }

    /**
     * Parse metadata from class attributes
     */
    public function parseFromClass(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflection = new \ReflectionClass($class);
        
        $metadata = [
            'class' => $class,
            'table' => $this->parseTableName($reflection),
            'connection' => null,
            'fields' => [],
            'relationships' => [],
            'indexes' => [],
            'primaryKey' => 'id'
        ];

        // Parse table attribute
        $tableAttrs = $reflection->getAttributes(Table::class);
        if (!empty($tableAttrs)) {
            $table = $tableAttrs[0]->newInstance();
            $metadata['table'] = $table->name;
            $metadata['connection'] = $table->connection;
        }

        // Parse field attributes from properties
        foreach ($reflection->getProperties() as $property) {
            $fieldAttrs = $property->getAttributes(Field::class);
            
            foreach ($fieldAttrs as $attr) {
                $field = $attr->newInstance();
                $fieldName = $field->name;
                
                $metadata['fields'][$fieldName] = [
                    'name' => $fieldName,
                    'type' => $field->type,
                    'length' => $field->length,
                    'nullable' => $field->nullable,
                    'default' => $field->default,
                    'unique' => $field->unique,
                    'primary' => $field->primary,
                    'autoIncrement' => $field->autoIncrement,
                    'unsigned' => $field->unsigned,
                    'precision' => $field->precision,
                    'scale' => $field->scale,
                    'enum' => $field->enum,
                    'comment' => $field->comment,
                    'label' => $field->label ?? ucfirst(str_replace('_', ' ', $fieldName)),
                    'placeholder' => $field->placeholder,
                    'inputType' => $field->getInputType(),
                    'searchable' => $field->searchable,
                    'sortable' => $field->sortable,
                    'filterable' => $field->filterable,
                    'hidden' => $field->hidden,
                    'validation' => $field->getValidationRules(),
                    'min' => $field->min,
                    'max' => $field->max,
                    'pattern' => $field->pattern,
                    'mimes' => $field->mimes,
                    'maxFileSize' => $field->maxFileSize,
                    'foreignTable' => $field->foreignTable,
                    'foreignKey' => $field->foreignKey,
                    'onDelete' => $field->onDelete,
                    'onUpdate' => $field->onUpdate
                ];

                if ($field->primary) {
                    $metadata['primaryKey'] = $fieldName;
                }
            }
        }

        // Parse relationship attributes from methods/properties
        $relationTypes = [
            HasOne::class,
            HasMany::class,
            BelongsTo::class,
            BelongsToMany::class,
            MorphTo::class,
            MorphOne::class,
            MorphMany::class
        ];

        foreach ($reflection->getMethods() as $method) {
            foreach ($relationTypes as $relationType) {
                $attrs = $method->getAttributes($relationType);
                if (!empty($attrs)) {
                    $relation = $attrs[0]->newInstance();
                    $relationName = $method->getName();
                    
                    $metadata['relationships'][$relationName] = [
                        'type' => basename(str_replace('\\', '/', $relationType)),
                        'model' => $relation->model ?? null,
                        'foreignKey' => $relation->foreignKey ?? null,
                        'localKey' => $relation->localKey ?? $relation->ownerKey ?? 'id',
                        'pivotTable' => $relation->pivotTable ?? null,
                        'relatedKey' => $relation->relatedKey ?? null,
                        'name' => $relation->name ?? null
                    ];
                }
            }
        }

        return $metadata;
    }

    /**
     * Parse table name from class name if not defined
     */
    protected function parseTableName(\ReflectionClass $reflection): string
    {
        $className = $reflection->getShortName();
        
        // Convert PascalCase to snake_case and pluralize
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        
        // Simple pluralization
        if (!str_ends_with($tableName, 's')) {
            $tableName .= 's';
        }

        return $tableName;
    }

    /**
     * Cache metadata
     */
    public function cache(string $key, array $metadata): void
    {
        $cacheKey = $this->cachePrefix . $key;
        $this->metadata[$key] = $metadata;
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $metadata, $this->cacheTtl);
        }
    }

    /**
     * Clear metadata cache
     */
    public function clearCache(?string $key = null): void
    {
        if ($key !== null) {
            $cacheKey = $this->cachePrefix . $key;
            unset($this->metadata[$key]);
            if ($this->cache) {
                $this->cache->delete($cacheKey);
            }
        } else {
            $this->metadata = [];
            if ($this->cache) {
                $this->cache->clear();
            }
        }
    }

    /**
     * Get all cached metadata
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set cache TTL
     */
    public function setCacheTtl(int $ttl): void
    {
        $this->cacheTtl = $ttl;
    }
}
