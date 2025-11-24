<?php

namespace NeoPhp\Queue;

class Queue
{
    protected $driver;

    public function __construct(QueueDriver $driver)
    {
        $this->driver = $driver;
    }

    public function push(string $job, $data = []): bool
    {
        return $this->driver->push($job, $data);
    }

    public function pop(): ?array
    {
        return $this->driver->pop();
    }

    public function later(int $delay, string $job, $data = []): bool
    {
        return $this->driver->later($delay, $job, $data);
    }

    public function size(): int
    {
        return $this->driver->size();
    }
}

abstract class QueueDriver
{
    abstract public function push(string $job, $data = []): bool;
    abstract public function pop(): ?array;
    abstract public function later(int $delay, string $job, $data = []): bool;
    abstract public function size(): int;
}

class DatabaseQueueDriver extends QueueDriver
{
    protected $db;
    protected $table;

    public function __construct($db, string $table = 'jobs')
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function push(string $job, $data = []): bool
    {
        return $this->db->insert($this->table, [
            'queue' => 'default',
            'payload' => json_encode(['job' => $job, 'data' => $data]),
            'attempts' => 0,
            'available_at' => time(),
            'created_at' => time(),
        ]) > 0;
    }

    public function pop(): ?array
    {
        $jobs = $this->db->query(
            "SELECT * FROM {$this->table} WHERE available_at <= ? ORDER BY id ASC LIMIT 1",
            [time()]
        );

        if (empty($jobs)) {
            return null;
        }

        $job = $jobs[0];
        
        // Delete job
        $this->db->execute("DELETE FROM {$this->table} WHERE id = ?", [$job['id']]);

        return json_decode($job['payload'], true);
    }

    public function later(int $delay, string $job, $data = []): bool
    {
        return $this->db->insert($this->table, [
            'queue' => 'default',
            'payload' => json_encode(['job' => $job, 'data' => $data]),
            'attempts' => 0,
            'available_at' => time() + $delay,
            'created_at' => time(),
        ]) > 0;
    }

    public function size(): int
    {
        $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        return (int) ($result[0]['count'] ?? 0);
    }
}
