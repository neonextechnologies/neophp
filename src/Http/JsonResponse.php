<?php

namespace NeoPhp\Http;

class JsonResponse extends Response
{
    public function __construct($data, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        
        parent::__construct(json_encode($data), $status, $headers);
    }

    public static function success($data, string $message = 'Success', int $status = 200): self
    {
        return new self([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, $errors = null): self
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return new self($response, $status);
    }

    public static function created($data, string $message = 'Created'): self
    {
        return self::success($data, $message, 201);
    }

    public static function noContent(): self
    {
        return new self(null, 204);
    }
}
