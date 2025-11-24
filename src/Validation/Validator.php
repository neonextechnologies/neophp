<?php

namespace NeoPhp\Validation;

class Validator
{
    protected $data = [];
    protected $rules = [];
    protected $errors = [];
    protected $customMessages = [];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new static($data, $rules, $customMessages);
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($rulesArray as $rule) {
                $this->validateRule($field, $rule);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->validated();
    }

    public function fails(): bool
    {
        try {
            $this->validate();
            return false;
        } catch (ValidationException $e) {
            return true;
        }
    }

    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }

    protected function validateRule(string $field, string $rule): void
    {
        [$ruleName, $parameter] = $this->parseRule($rule);

        $value = $this->data[$field] ?? null;

        $method = 'validate' . ucfirst($ruleName);

        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $parameter)) {
                $this->addError($field, $ruleName, $parameter);
            }
        }
    }

    protected function parseRule(string $rule): array
    {
        if (strpos($rule, ':') !== false) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
            return [$ruleName, $parameter];
        }

        return [$rule, null];
    }

    protected function validateRequired(string $field, $value, $parameter): bool
    {
        return !empty($value) || $value === '0' || $value === 0;
    }

    protected function validateEmail(string $field, $value, $parameter): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin(string $field, $value, $parameter): bool
    {
        if (is_numeric($value)) {
            return $value >= (float) $parameter;
        }

        return strlen($value) >= (int) $parameter;
    }

    protected function validateMax(string $field, $value, $parameter): bool
    {
        if (is_numeric($value)) {
            return $value <= (float) $parameter;
        }

        return strlen($value) <= (int) $parameter;
    }

    protected function validateNumeric(string $field, $value, $parameter): bool
    {
        return is_numeric($value);
    }

    protected function validateInteger(string $field, $value, $parameter): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateString(string $field, $value, $parameter): bool
    {
        return is_string($value);
    }

    protected function validateArray(string $field, $value, $parameter): bool
    {
        return is_array($value);
    }

    protected function validateIn(string $field, $value, $parameter): bool
    {
        $values = explode(',', $parameter);
        return in_array($value, $values);
    }

    protected function validateUnique(string $field, $value, $parameter): bool
    {
        // Format: unique:table,column
        [$table, $column] = explode(',', $parameter);
        
        $db = app('db');
        $result = $db->query("SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?", [$value]);
        
        return ($result[0]['count'] ?? 0) == 0;
    }

    protected function validateConfirmed(string $field, $value, $parameter): bool
    {
        $confirmField = $field . '_confirmation';
        return isset($this->data[$confirmField]) && $value === $this->data[$confirmField];
    }

    protected function addError(string $field, string $rule, $parameter): void
    {
        $message = $this->getMessage($field, $rule, $parameter);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    protected function getMessage(string $field, string $rule, $parameter): string
    {
        $key = "{$field}.{$rule}";

        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$parameter}.",
            'max' => "The {$field} must not exceed {$parameter}.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'string' => "The {$field} must be a string.",
            'array' => "The {$field} must be an array.",
            'in' => "The selected {$field} is invalid.",
            'unique' => "The {$field} has already been taken.",
            'confirmed' => "The {$field} confirmation does not match.",
        ];

        return $messages[$rule] ?? "The {$field} is invalid.";
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

class ValidationException extends \Exception
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
