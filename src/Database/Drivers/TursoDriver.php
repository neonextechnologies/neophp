<?php

namespace NeoPhp\Database\Drivers;

class TursoDriver extends DatabaseDriver
{
    public function connect(): void
    {
        $url = $this->config['url'] ?? '';
        $token = $this->config['auth_token'] ?? '';

        if (empty($url)) {
            throw new \Exception("Turso URL is required");
        }

        // Turso uses libSQL (SQLite fork) over HTTP
        $this->connection = [
            'url' => rtrim($url, '/'),
            'token' => $token,
        ];
    }

    public function query(string $sql, array $params = []): array
    {
        $response = $this->executeRequest($sql, $params);
        
        if (isset($response['error'])) {
            throw new \Exception("Turso query failed: " . $response['error']);
        }

        return $response['results'] ?? [];
    }

    public function execute(string $sql, array $params = []): bool
    {
        $response = $this->executeRequest($sql, $params);
        
        if (isset($response['error'])) {
            throw new \Exception("Turso execute failed: " . $response['error']);
        }

        return true;
    }

    protected function executeRequest(string $sql, array $params = []): array
    {
        $url = $this->connection['url'] . '/v2/pipeline';
        $token = $this->connection['token'];

        $payload = [
            'requests' => [
                [
                    'type' => 'execute',
                    'stmt' => [
                        'sql' => $sql,
                        'args' => $this->convertParams($params),
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Turso HTTP error: {$httpCode}");
        }

        $result = json_decode($response, true);
        
        if (!isset($result['results'][0])) {
            return [];
        }

        return $this->formatResults($result['results'][0]);
    }

    protected function convertParams(array $params): array
    {
        $converted = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $converted[] = ['type' => 'integer', 'value' => strval($param)];
            } elseif (is_float($param)) {
                $converted[] = ['type' => 'float', 'value' => $param];
            } elseif (is_null($param)) {
                $converted[] = ['type' => 'null'];
            } else {
                $converted[] = ['type' => 'text', 'value' => strval($param)];
            }
        }

        return $converted;
    }

    protected function formatResults(array $result): array
    {
        if (!isset($result['response']['result']['rows'])) {
            return [];
        }

        $columns = $result['response']['result']['cols'] ?? [];
        $rows = $result['response']['result']['rows'] ?? [];
        
        $formatted = [];
        
        foreach ($rows as $row) {
            $formattedRow = [];
            
            foreach ($columns as $index => $column) {
                $value = $row[$index] ?? null;
                
                if (is_array($value) && isset($value['type'])) {
                    $formattedRow[$column] = $value['value'] ?? null;
                } else {
                    $formattedRow[$column] = $value;
                }
            }
            
            $formatted[] = $formattedRow;
        }

        return $formatted;
    }

    public function lastInsertId(): int
    {
        $result = $this->query("SELECT last_insert_rowid() as id");
        return (int) ($result[0]['id'] ?? 0);
    }

    public function beginTransaction(): bool
    {
        return $this->execute("BEGIN TRANSACTION");
    }

    public function commit(): bool
    {
        return $this->execute("COMMIT");
    }

    public function rollBack(): bool
    {
        return $this->execute("ROLLBACK");
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }
}
