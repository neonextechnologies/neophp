<?php

namespace NeoPhp\Auth;

class JWT
{
    protected $secret;
    protected $algorithm = 'HS256';

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function encode(array $payload, int $ttl = 3600): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        $signature = $this->base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->secret, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

class ApiAuth
{
    protected $jwt;
    protected $db;

    public function __construct(JWT $jwt, $db)
    {
        $this->jwt = $jwt;
        $this->db = $db;
    }

    public function attempt(array $credentials): ?string
    {
        $user = $this->db->query(
            "SELECT * FROM users WHERE email = ?",
            [$credentials['email'] ?? '']
        );

        if (empty($user)) {
            return null;
        }

        $user = $user[0];

        if (!password_verify($credentials['password'] ?? '', $user['password'])) {
            return null;
        }

        return $this->jwt->encode([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);
    }

    public function user(string $token): ?array
    {
        $payload = $this->jwt->decode($token);

        if (!$payload || !isset($payload['user_id'])) {
            return null;
        }

        $user = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$payload['user_id']]
        );

        return $user[0] ?? null;
    }

    public function check(string $token): bool
    {
        return $this->jwt->decode($token) !== null;
    }

    public function refresh(string $token, int $ttl = 3600): ?string
    {
        $payload = $this->jwt->decode($token);

        if (!$payload) {
            return null;
        }

        unset($payload['iat'], $payload['exp']);

        return $this->jwt->encode($payload, $ttl);
    }
}
