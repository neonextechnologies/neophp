<?php

namespace NeoPhp\Schedule;

class Schedule
{
    protected static $events = [];

    public static function command(string $command): ScheduleEvent
    {
        $event = new ScheduleEvent($command);
        static::$events[] = $event;
        return $event;
    }

    public static function call(callable $callback): ScheduleEvent
    {
        $event = new ScheduleEvent($callback);
        static::$events[] = $event;
        return $event;
    }

    public static function run(): void
    {
        foreach (static::$events as $event) {
            if ($event->isDue()) {
                $event->run();
            }
        }
    }

    public static function getEvents(): array
    {
        return static::$events;
    }
}

class ScheduleEvent
{
    protected $command;
    protected $expression = '* * * * *';
    protected $timezone = 'UTC';
    protected $description = '';

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function cron(string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }

    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    public function everyFiveMinutes(): self
    {
        return $this->cron('*/5 * * * *');
    }

    public function everyTenMinutes(): self
    {
        return $this->cron('*/10 * * * *');
    }

    public function everyFifteenMinutes(): self
    {
        return $this->cron('*/15 * * * *');
    }

    public function everyThirtyMinutes(): self
    {
        return $this->cron('0,30 * * * *');
    }

    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    public function daily(): self
    {
        return $this->cron('0 0 * * *');
    }

    public function dailyAt(string $time): self
    {
        [$hour, $minute] = explode(':', $time);
        return $this->cron("{$minute} {$hour} * * *");
    }

    public function weekly(): self
    {
        return $this->cron('0 0 * * 0');
    }

    public function monthly(): self
    {
        return $this->cron('0 0 1 * *');
    }

    public function timezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isDue(): bool
    {
        $date = new \DateTime('now', new \DateTimeZone($this->timezone));
        
        return $this->expressionMatches($this->expression, $date);
    }

    public function run(): void
    {
        if (is_callable($this->command)) {
            call_user_func($this->command);
        } elseif (is_string($this->command)) {
            // Run CLI command
            exec("php " . $this->command);
        }
    }

    protected function expressionMatches(string $expression, \DateTime $date): bool
    {
        $segments = explode(' ', $expression);
        
        if (count($segments) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $dayOfWeek] = $segments;

        return $this->matchSegment($minute, $date->format('i'))
            && $this->matchSegment($hour, $date->format('H'))
            && $this->matchSegment($day, $date->format('d'))
            && $this->matchSegment($month, $date->format('m'))
            && $this->matchSegment($dayOfWeek, $date->format('w'));
    }

    protected function matchSegment(string $segment, string $value): bool
    {
        if ($segment === '*') {
            return true;
        }

        if (strpos($segment, '/') !== false) {
            [$range, $step] = explode('/', $segment);
            return ((int) $value % (int) $step) === 0;
        }

        if (strpos($segment, ',') !== false) {
            return in_array($value, explode(',', $segment));
        }

        if (strpos($segment, '-') !== false) {
            [$start, $end] = explode('-', $segment);
            return (int) $value >= (int) $start && (int) $value <= (int) $end;
        }

        return $segment === $value;
    }
}
