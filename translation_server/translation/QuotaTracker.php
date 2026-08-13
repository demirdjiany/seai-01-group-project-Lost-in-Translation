<?php

// Tracks how many characters we have sent to the translation API today,
// so we stop calling it before the daily quota runs out. Resets itself
// automatically at midnight.

require_once __DIR__ . '/helpers.php';

class QuotaTracker
{
    private string $quotaFile;
    private int $dailyLimit;

    public function __construct(string $quotaFile, int $dailyLimit)
    {
        $this->quotaFile = $quotaFile;
        $this->dailyLimit = $dailyLimit;
    }

    public function hasRoomFor(int $charCount): bool
    {
        return ($this->usedToday() + $charCount) <= $this->dailyLimit;
    }

    public function recordUsage(int $charCount): void
    {
        $data = $this->loadForToday();
        $data['used'] += $charCount;
        tr_write_json_file($this->quotaFile, $data);
    }

    public function usedToday(): int
    {
        return $this->loadForToday()['used'];
    }

    public function remainingToday(): int
    {
        return max(0, $this->dailyLimit - $this->usedToday());
    }

    private function loadForToday(): array
    {
        $data = tr_read_json_file($this->quotaFile);
        $today = date('Y-m-d');

        if (($data['date'] ?? null) !== $today) {
            $data = ['date' => $today, 'used' => 0];
        }

        return $data;
    }
}
