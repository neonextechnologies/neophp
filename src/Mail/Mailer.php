<?php

namespace NeoPhp\Mail;

class Mailer
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'mail',
            'from' => ['address' => 'noreply@neophp.local', 'name' => 'NeoPhp'],
        ], $config);
    }

    public function to($address, string $name = ''): Message
    {
        $message = new Message($this->config);
        return $message->to($address, $name);
    }

    public function send(Message $message): bool
    {
        return $message->send();
    }
}

class Message
{
    protected $config;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $from;
    protected $replyTo;
    protected $subject;
    protected $body;
    protected $isHtml = true;
    protected $attachments = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->from = $config['from'];
    }

    public function to($address, string $name = ''): self
    {
        if (is_array($address)) {
            $this->to = array_merge($this->to, $address);
        } else {
            $this->to[] = ['address' => $address, 'name' => $name];
        }
        return $this;
    }

    public function cc($address, string $name = ''): self
    {
        $this->cc[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function bcc($address, string $name = ''): self
    {
        $this->bcc[] = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function from($address, string $name = ''): self
    {
        $this->from = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function replyTo($address, string $name = ''): self
    {
        $this->replyTo = ['address' => $address, 'name' => $name];
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body, bool $isHtml = true): self
    {
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    public function attach(string $path, string $name = ''): self
    {
        $this->attachments[] = ['path' => $path, 'name' => $name ?: basename($path)];
        return $this;
    }

    public function send(): bool
    {
        if (empty($this->to) || !$this->subject || !$this->body) {
            return false;
        }

        $headers = $this->buildHeaders();
        $to = $this->formatAddress($this->to[0]);

        return mail($to, $this->subject, $this->body, implode("\r\n", $headers));
    }

    protected function buildHeaders(): array
    {
        $headers = [];

        if ($this->from) {
            $headers[] = 'From: ' . $this->formatAddress($this->from);
        }

        if ($this->replyTo) {
            $headers[] = 'Reply-To: ' . $this->formatAddress($this->replyTo);
        }

        if ($this->isHtml) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        $headers[] = 'MIME-Version: 1.0';

        return $headers;
    }

    protected function formatAddress(array $address): string
    {
        if (empty($address['name'])) {
            return $address['address'];
        }

        return "\"{$address['name']}\" <{$address['address']}>";
    }
}
