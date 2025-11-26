<?php

namespace NeoPhp\Contracts;

/**
 * Mailer Interface
 * Pure contract for email operations
 */
interface MailerInterface
{
    /**
     * Send an email
     */
    public function send(array $to, string $subject, string $body, array $options = []): bool;

    /**
     * Queue an email for later sending
     */
    public function queue(array $to, string $subject, string $body, array $options = []): bool;

    /**
     * Send email using a view template
     */
    public function sendView(array $to, string $subject, string $view, array $data = []): bool;

    /**
     * Set from address
     */
    public function from(string $email, string $name = ''): self;

    /**
     * Add recipient
     */
    public function to(string $email, string $name = ''): self;

    /**
     * Add CC recipient
     */
    public function cc(string $email, string $name = ''): self;

    /**
     * Add BCC recipient
     */
    public function bcc(string $email, string $name = ''): self;

    /**
     * Add attachment
     */
    public function attach(string $path, string $name = ''): self;

    /**
     * Get driver name
     */
    public function getDriverName(): string;
}
