<?php

namespace NeoPhp\Contracts;

/**
 * Validator Interface
 * Pure contract for validation operations
 */
interface ValidatorInterface
{
    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules, array $messages = []): bool;

    /**
     * Check if validation fails
     */
    public function fails(): bool;

    /**
     * Check if validation passes
     */
    public function passes(): bool;

    /**
     * Get validation errors
     */
    public function errors(): array;

    /**
     * Get validated data
     */
    public function validated(): array;

    /**
     * Add custom validation rule
     */
    public function extend(string $rule, callable $callback): void;
}
