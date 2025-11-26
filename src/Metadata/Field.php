<?php

namespace NeoPhp\Metadata;

use Attribute;

/**
 * Field Attribute
 * Define field metadata for model properties
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Field
{
    public function __construct(
        public string $name,
        public string $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public mixed $default = null,
        public bool $unique = false,
        public bool $primary = false,
        public bool $autoIncrement = false,
        public bool $unsigned = false,
        public ?int $precision = null,
        public ?int $scale = null,
        public ?array $enum = null,
        public ?string $comment = null,
        // UI/Form metadata
        public ?string $label = null,
        public ?string $placeholder = null,
        public ?string $inputType = null,
        public bool $searchable = false,
        public bool $sortable = false,
        public bool $filterable = false,
        public bool $hidden = false,
        // Validation metadata
        public ?array $validation = null,
        public ?int $min = null,
        public ?int $max = null,
        public ?string $pattern = null,
        // File upload metadata
        public ?array $mimes = null,
        public ?int $maxFileSize = null,
        // Foreign key metadata
        public ?string $foreignTable = null,
        public ?string $foreignKey = null,
        public ?string $onDelete = null,
        public ?string $onUpdate = null
    ) {}

    /**
     * Get validation rules from field metadata
     */
    public function getValidationRules(): array
    {
        $rules = $this->validation ?? [];

        if (!$this->nullable && !in_array('nullable', $rules)) {
            $rules[] = 'required';
        }

        if ($this->unique) {
            $rules[] = 'unique';
        }

        if ($this->type === 'email') {
            $rules[] = 'email';
        }

        if ($this->type === 'integer' || $this->type === 'int') {
            $rules[] = 'integer';
        }

        if ($this->type === 'float' || $this->type === 'decimal') {
            $rules[] = 'numeric';
        }

        if ($this->min !== null) {
            $rules[] = "min:{$this->min}";
        }

        if ($this->max !== null) {
            $rules[] = "max:{$this->max}";
        }

        if ($this->enum) {
            $rules[] = 'in:' . implode(',', $this->enum);
        }

        if ($this->mimes) {
            $rules[] = 'mimes:' . implode(',', $this->mimes);
        }

        if ($this->maxFileSize) {
            $rules[] = "max:{$this->maxFileSize}";
        }

        return $rules;
    }

    /**
     * Get input type for form rendering
     */
    public function getInputType(): string
    {
        if ($this->inputType) {
            return $this->inputType;
        }

        return match($this->type) {
            'email' => 'email',
            'password' => 'password',
            'text', 'longtext' => 'textarea',
            'integer', 'int', 'bigint' => 'number',
            'float', 'decimal' => 'number',
            'boolean', 'bool' => 'checkbox',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'time' => 'time',
            'file' => 'file',
            'enum' => 'select',
            default => 'text'
        };
    }
}
