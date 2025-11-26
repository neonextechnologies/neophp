<?php

namespace NeoPhp\Console;

/**
 * Console Output Handler
 */
class Output
{
    protected int $verbosity = 1;
    protected int $progressMax = 0;
    protected int $progressCurrent = 0;

    // Color codes
    const COLOR_RESET = "\033[0m";
    const COLOR_BLACK = "\033[30m";
    const COLOR_RED = "\033[31m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_BLUE = "\033[34m";
    const COLOR_MAGENTA = "\033[35m";
    const COLOR_CYAN = "\033[36m";
    const COLOR_WHITE = "\033[37m";
    const COLOR_GRAY = "\033[90m";

    // Background colors
    const BG_RED = "\033[41m";
    const BG_GREEN = "\033[42m";
    const BG_YELLOW = "\033[43m";
    const BG_BLUE = "\033[44m";

    /**
     * Write a message
     */
    public function write(string $message, bool $newLine = true): void
    {
        echo $message . ($newLine ? PHP_EOL : '');
    }

    /**
     * Write info message (cyan)
     */
    public function info(string $message): void
    {
        $this->write(self::COLOR_CYAN . $message . self::COLOR_RESET);
    }

    /**
     * Write success message (green)
     */
    public function success(string $message): void
    {
        $this->write(self::COLOR_GREEN . '✓ ' . $message . self::COLOR_RESET);
    }

    /**
     * Write error message (red)
     */
    public function error(string $message): void
    {
        $this->write(self::COLOR_RED . '✗ ' . $message . self::COLOR_RESET);
    }

    /**
     * Write warning message (yellow)
     */
    public function warning(string $message): void
    {
        $this->write(self::COLOR_YELLOW . '⚠ ' . $message . self::COLOR_RESET);
    }

    /**
     * Write comment (gray)
     */
    public function comment(string $message): void
    {
        $this->write(self::COLOR_GRAY . $message . self::COLOR_RESET);
    }

    /**
     * Write plain line
     */
    public function line(string $message): void
    {
        $this->write($message);
    }

    /**
     * Write new line
     */
    public function newLine(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->write('');
        }
    }

    /**
     * Ask a question
     */
    public function ask(string $question, ?string $default = null): string
    {
        $prompt = self::COLOR_CYAN . $question . self::COLOR_RESET;
        
        if ($default !== null) {
            $prompt .= self::COLOR_GRAY . " [{$default}]" . self::COLOR_RESET;
        }
        
        $prompt .= ': ';

        $this->write($prompt, false);
        $answer = trim(fgets(STDIN));

        return $answer ?: $default ?? '';
    }

    /**
     * Ask for confirmation
     */
    public function confirm(string $question, bool $default = false): bool
    {
        $defaultText = $default ? 'Y/n' : 'y/N';
        $prompt = self::COLOR_CYAN . $question . ' ' . self::COLOR_GRAY . "[$defaultText]" . self::COLOR_RESET . ': ';

        $this->write($prompt, false);
        $answer = strtolower(trim(fgets(STDIN)));

        if ($answer === '') {
            return $default;
        }

        return in_array($answer, ['y', 'yes', '1', 'true']);
    }

    /**
     * Ask with secret input (password)
     */
    public function secret(string $question): string
    {
        $prompt = self::COLOR_CYAN . $question . self::COLOR_RESET . ': ';
        $this->write($prompt, false);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $answer = stream_get_line(STDIN, 1024, PHP_EOL);
        } else {
            // Unix/Linux/Mac
            system('stty -echo');
            $answer = trim(fgets(STDIN));
            system('stty echo');
            $this->newLine();
        }

        return $answer;
    }

    /**
     * Ask with choices
     */
    public function choice(string $question, array $choices, $default = null): string
    {
        $this->line(self::COLOR_CYAN . $question . self::COLOR_RESET);
        
        foreach ($choices as $key => $choice) {
            $prefix = $key === $default ? '>' : ' ';
            $this->line("  {$prefix} [{$key}] {$choice}");
        }

        $prompt = self::COLOR_CYAN . 'Choose an option' . self::COLOR_RESET;
        if ($default !== null) {
            $prompt .= self::COLOR_GRAY . " [{$default}]" . self::COLOR_RESET;
        }
        $prompt .= ': ';

        $this->write($prompt, false);
        $answer = trim(fgets(STDIN));

        $selected = $answer ?: $default;

        return isset($choices[$selected]) ? $selected : $default;
    }

    /**
     * Display table
     */
    public function table(array $headers, array $rows): void
    {
        $columns = count($headers);
        $widths = array_fill(0, $columns, 0);

        // Calculate column widths
        foreach ($headers as $i => $header) {
            $widths[$i] = max($widths[$i], strlen($header));
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], strlen($cell));
            }
        }

        // Draw header
        $this->drawTableLine($widths, '┌', '┬', '┐');
        $this->drawTableRow($headers, $widths, '│');
        $this->drawTableLine($widths, '├', '┼', '┤');

        // Draw rows
        foreach ($rows as $row) {
            $this->drawTableRow($row, $widths, '│');
        }

        // Draw bottom
        $this->drawTableLine($widths, '└', '┴', '┘');
    }

    /**
     * Draw table line
     */
    protected function drawTableLine(array $widths, string $left, string $middle, string $right): void
    {
        $line = $left;
        foreach ($widths as $i => $width) {
            $line .= str_repeat('─', $width + 2);
            $line .= $i < count($widths) - 1 ? $middle : $right;
        }
        $this->line($line);
    }

    /**
     * Draw table row
     */
    protected function drawTableRow(array $cells, array $widths, string $separator): void
    {
        $line = $separator;
        foreach ($cells as $i => $cell) {
            $line .= ' ' . str_pad($cell, $widths[$i]) . ' ' . $separator;
        }
        $this->line($line);
    }

    /**
     * Start progress bar
     */
    public function progressStart(int $max): void
    {
        $this->progressMax = $max;
        $this->progressCurrent = 0;
        $this->drawProgress();
    }

    /**
     * Advance progress bar
     */
    public function progressAdvance(int $step = 1): void
    {
        $this->progressCurrent = min($this->progressCurrent + $step, $this->progressMax);
        $this->drawProgress();
    }

    /**
     * Finish progress bar
     */
    public function progressFinish(): void
    {
        $this->progressCurrent = $this->progressMax;
        $this->drawProgress();
        $this->newLine();
    }

    /**
     * Draw progress bar
     */
    protected function drawProgress(): void
    {
        $percent = $this->progressMax > 0 ? ($this->progressCurrent / $this->progressMax) * 100 : 0;
        $barWidth = 50;
        $filled = (int) ($percent / 100 * $barWidth);
        
        $bar = str_repeat('█', $filled) . str_repeat('░', $barWidth - $filled);
        
        $this->write("\r" . self::COLOR_GREEN . $bar . self::COLOR_RESET . 
            sprintf(' %3d%% (%d/%d)', $percent, $this->progressCurrent, $this->progressMax), false);
    }
}
