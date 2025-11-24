<?php

namespace NeoPhp\Http;

class RedirectResponse extends Response
{
    protected $targetUrl;

    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        $this->targetUrl = $url;
        $headers['Location'] = $url;
        
        parent::__construct('', $status, $headers);
    }

    public function with(string $key, $value): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['_flash'][$key] = $value;
        
        return $this;
    }

    public function withErrors(array $errors, string $key = 'errors'): self
    {
        return $this->with($key, $errors);
    }

    public function withInput(array $input = null): self
    {
        $input = $input ?? $_POST ?? [];
        return $this->with('_old_input', $input);
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }
}
