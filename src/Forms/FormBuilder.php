<?php

namespace NeoPhp\Forms;

use NeoPhp\Metadata\MetadataRepository;

/**
 * Form Builder
 * Generate HTML forms from metadata
 * Inspired by Laravel Form Builder & Symfony Forms
 */
class FormBuilder
{
    protected MetadataRepository $metadata;
    protected array $options = [];
    protected string $method = 'POST';
    protected ?string $action = null;
    protected array $values = [];
    protected array $errors = [];

    public function __construct(MetadataRepository $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Create form from model metadata
     */
    public function make(string $model, array $options = []): string
    {
        $this->options = $options;
        $this->method = $options['method'] ?? 'POST';
        $this->action = $options['action'] ?? '';
        $this->values = $options['values'] ?? [];
        $this->errors = $options['errors'] ?? [];

        $metadata = $this->metadata->getModelMetadata($model);
        
        $html = $this->openForm();
        
        foreach ($metadata['fields'] as $fieldName => $fieldMeta) {
            if ($fieldMeta['hidden'] || $fieldMeta['primary']) {
                continue;
            }
            
            $html .= $this->buildField($fieldName, $fieldMeta);
        }
        
        $html .= $this->buildSubmitButton();
        $html .= $this->closeForm();
        
        return $html;
    }

    /**
     * Open form tag
     */
    protected function openForm(): string
    {
        $attributes = [
            'method' => $this->method === 'GET' ? 'GET' : 'POST',
            'action' => $this->action,
            'class' => $this->options['class'] ?? 'form'
        ];

        if (isset($this->options['enctype'])) {
            $attributes['enctype'] = $this->options['enctype'];
        } elseif ($this->hasFileField()) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $html = '<form ' . $this->buildAttributes($attributes) . '>';
        
        // CSRF token
        if (function_exists('csrf_field') && $this->method !== 'GET') {
            $html .= csrf_field();
        }
        
        // Method spoofing for PUT/PATCH/DELETE
        if (in_array($this->method, ['PUT', 'PATCH', 'DELETE'])) {
            $html .= '<input type="hidden" name="_method" value="' . $this->method . '">';
        }
        
        return $html;
    }

    /**
     * Close form tag
     */
    protected function closeForm(): string
    {
        return '</form>';
    }

    /**
     * Build field HTML
     */
    protected function buildField(string $name, array $meta): string
    {
        $value = $this->values[$name] ?? $meta['default'] ?? '';
        $error = $this->errors[$name] ?? [];
        
        $html = '<div class="form-group' . ($error ? ' has-error' : '') . '">';
        $html .= $this->buildLabel($name, $meta);
        $html .= $this->buildInput($name, $meta, $value);
        $html .= $this->buildError($name, $error);
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Build label
     */
    protected function buildLabel(string $name, array $meta): string
    {
        $label = $meta['label'] ?? ucfirst(str_replace('_', ' ', $name));
        $required = !$meta['nullable'] ? '<span class="required">*</span>' : '';
        
        return '<label for="' . $name . '">' . $label . $required . '</label>';
    }

    /**
     * Build input
     */
    protected function buildInput(string $name, array $meta, mixed $value): string
    {
        $inputType = $meta['inputType'] ?? 'text';
        
        return match($inputType) {
            'textarea' => $this->textarea($name, $value, $meta),
            'select' => $this->select($name, $value, $meta),
            'checkbox' => $this->checkbox($name, $value, $meta),
            'radio' => $this->radio($name, $value, $meta),
            default => $this->input($inputType, $name, $value, $meta)
        };
    }

    /**
     * Build input field
     */
    protected function input(string $type, string $name, mixed $value, array $meta): string
    {
        $attributes = [
            'type' => $type,
            'name' => $name,
            'id' => $name,
            'value' => $value,
            'class' => 'form-control',
            'placeholder' => $meta['placeholder'] ?? ''
        ];

        if (!$meta['nullable']) {
            $attributes['required'] = 'required';
        }

        if ($meta['min'] !== null) {
            $attributes['min'] = $meta['min'];
        }

        if ($meta['max'] !== null) {
            $attributes['max'] = $meta['max'];
        }

        if ($meta['pattern'] !== null) {
            $attributes['pattern'] = $meta['pattern'];
        }

        if ($type === 'file' && $meta['mimes']) {
            $attributes['accept'] = implode(',', array_map(fn($m) => '.' . $m, $meta['mimes']));
        }

        return '<input ' . $this->buildAttributes($attributes) . '>';
    }

    /**
     * Build textarea
     */
    protected function textarea(string $name, mixed $value, array $meta): string
    {
        $attributes = [
            'name' => $name,
            'id' => $name,
            'class' => 'form-control',
            'rows' => $meta['rows'] ?? 4,
            'placeholder' => $meta['placeholder'] ?? ''
        ];

        if (!$meta['nullable']) {
            $attributes['required'] = 'required';
        }

        return '<textarea ' . $this->buildAttributes($attributes) . '>' . htmlspecialchars($value) . '</textarea>';
    }

    /**
     * Build select
     */
    protected function select(string $name, mixed $value, array $meta): string
    {
        $options = $meta['enum'] ?? $meta['options'] ?? [];
        
        $attributes = [
            'name' => $name,
            'id' => $name,
            'class' => 'form-control'
        ];

        if (!$meta['nullable']) {
            $attributes['required'] = 'required';
        }

        $html = '<select ' . $this->buildAttributes($attributes) . '>';
        
        if ($meta['nullable']) {
            $html .= '<option value="">-- Select --</option>';
        }
        
        foreach ($options as $optionValue => $optionLabel) {
            $selected = $value == $optionValue ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($optionValue) . '"' . $selected . '>';
            $html .= htmlspecialchars($optionLabel);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }

    /**
     * Build checkbox
     */
    protected function checkbox(string $name, mixed $value, array $meta): string
    {
        $attributes = [
            'type' => 'checkbox',
            'name' => $name,
            'id' => $name,
            'value' => '1'
        ];

        if ($value) {
            $attributes['checked'] = 'checked';
        }

        return '<input ' . $this->buildAttributes($attributes) . '>';
    }

    /**
     * Build radio
     */
    protected function radio(string $name, mixed $value, array $meta): string
    {
        $options = $meta['enum'] ?? $meta['options'] ?? [];
        $html = '';
        
        foreach ($options as $optionValue => $optionLabel) {
            $attributes = [
                'type' => 'radio',
                'name' => $name,
                'id' => $name . '_' . $optionValue,
                'value' => $optionValue
            ];

            if ($value == $optionValue) {
                $attributes['checked'] = 'checked';
            }

            $html .= '<label class="radio-inline">';
            $html .= '<input ' . $this->buildAttributes($attributes) . '> ';
            $html .= htmlspecialchars($optionLabel);
            $html .= '</label>';
        }
        
        return $html;
    }

    /**
     * Build error message
     */
    protected function buildError(string $name, array|string $error): string
    {
        if (empty($error)) {
            return '';
        }

        $errors = is_array($error) ? $error : [$error];
        $html = '<div class="help-block text-danger">';
        
        foreach ($errors as $msg) {
            $html .= '<p>' . htmlspecialchars($msg) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Build submit button
     */
    protected function buildSubmitButton(): string
    {
        $text = $this->options['submit'] ?? 'Submit';
        $class = $this->options['submitClass'] ?? 'btn btn-primary';
        
        return '<div class="form-group"><button type="submit" class="' . $class . '">' . $text . '</button></div>';
    }

    /**
     * Build HTML attributes
     */
    protected function buildAttributes(array $attributes): string
    {
        $html = [];
        
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            
            if ($value === true) {
                $html[] = $key;
            } else {
                $html[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        
        return implode(' ', $html);
    }

    /**
     * Check if form has file field
     */
    protected function hasFileField(): bool
    {
        return isset($this->options['hasFile']) && $this->options['hasFile'];
    }
}
