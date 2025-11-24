<?php

namespace NeoPhp\Database\Drivers;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class MongoDBDriver extends DatabaseDriver
{
    protected $database;
    protected $transactionSession = null;

    public function connect(): void
    {
        try {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? '27017';
            $database = $this->config['database'] ?? 'test';
            $username = $this->config['username'] ?? '';
            $password = $this->config['password'] ?? '';

            if ($username && $password) {
                $uri = "mongodb://{$username}:{$password}@{$host}:{$port}";
            } else {
                $uri = "mongodb://{$host}:{$port}";
            }

            $this->connection = new Client($uri);
            $this->database = $this->connection->selectDatabase($database);
        } catch (\Exception $e) {
            throw new \Exception("MongoDB connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        // For MongoDB, "sql" is the collection name, params contain the query
        $collection = $this->database->selectCollection($sql);
        
        $filter = $params['filter'] ?? [];
        $options = $params['options'] ?? [];

        $cursor = $collection->find($filter, $options);
        
        $results = [];
        foreach ($cursor as $document) {
            $results[] = $this->documentToArray($document);
        }

        return $results;
    }

    public function execute(string $sql, array $params = []): bool
    {
        // For insert/update/delete operations
        $operation = $params['operation'] ?? 'insert';
        $collection = $this->database->selectCollection($sql);

        try {
            switch ($operation) {
                case 'insert':
                    $collection->insertOne($params['data']);
                    break;
                
                case 'insertMany':
                    $collection->insertMany($params['data']);
                    break;
                
                case 'update':
                    $collection->updateOne(
                        $params['filter'],
                        $params['update'],
                        $params['options'] ?? []
                    );
                    break;
                
                case 'updateMany':
                    $collection->updateMany(
                        $params['filter'],
                        $params['update'],
                        $params['options'] ?? []
                    );
                    break;
                
                case 'delete':
                    $collection->deleteOne($params['filter']);
                    break;
                
                case 'deleteMany':
                    $collection->deleteMany($params['filter']);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("MongoDB execute failed: " . $e->getMessage());
        }
    }

    public function insertOne(string $collection, array $data): string
    {
        $result = $this->database->selectCollection($collection)->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public function findOne(string $collection, array $filter = []): ?array
    {
        $document = $this->database->selectCollection($collection)->findOne($filter);
        
        if (!$document) {
            return null;
        }

        return $this->documentToArray($document);
    }

    public function find(string $collection, array $filter = [], array $options = []): array
    {
        return $this->query($collection, [
            'filter' => $filter,
            'options' => $options,
        ]);
    }

    protected function documentToArray($document): array
    {
        $array = [];
        
        foreach ($document as $key => $value) {
            if ($value instanceof ObjectId) {
                $array[$key] = (string) $value;
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    public function lastInsertId(): int
    {
        return 0; // MongoDB uses ObjectId instead
    }

    public function beginTransaction(): bool
    {
        $this->transactionSession = $this->connection->startSession();
        $this->transactionSession->startTransaction();
        return true;
    }

    public function commit(): bool
    {
        if ($this->transactionSession) {
            $this->transactionSession->commitTransaction();
            $this->transactionSession = null;
        }
        return true;
    }

    public function rollBack(): bool
    {
        if ($this->transactionSession) {
            $this->transactionSession->abortTransaction();
            $this->transactionSession = null;
        }
        return true;
    }

    public function disconnect(): void
    {
        $this->connection = null;
        $this->database = null;
    }
}
