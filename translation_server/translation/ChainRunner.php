<?php

// Runs a seed sentence through every hop of the language chain, one at a time.
// Uses the cache first, only calls the API when a hop was never seen before.

require_once __DIR__ . '/helpers.php';

class ChainAbortedException extends Exception
{
}

class ChainRunner
{
    private TranslationClient $client;
    private TranslationCache $cache;
    private QuotaTracker $quota;
    private string $logFile;

    public function __construct(TranslationClient $client, TranslationCache $cache, QuotaTracker $quota, string $logFile)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->quota = $quota;
        $this->logFile = $logFile;
    }

    /**
     * $chain looks like ['en', 'ja', 'ar', 'fi', 'sw', 'hu', 'ko', 'en'].
     *
     * Returns every step in order, e.g.:
     * [
     *   ['lang' => 'en', 'text' => 'seed text', 'from_cache' => false],
     *   ['lang' => 'ja', 'text' => '...',        'from_cache' => false],
     *   ...
     * ]
     *
     * Throws ChainAbortedException (and logs the reason) if any hop cannot be completed.
     * A caller must never show a partial result to a player.
     */
    public function run(string $seedText, array $chain): array
    {
        $steps = [['lang' => $chain[0], 'text' => $seedText, 'from_cache' => false]];
        $currentText = $seedText;

        for ($i = 0; $i < count($chain) - 1; $i++) {
            $from = $chain[$i];
            $to = $chain[$i + 1];

            try {
                $step = $this->runOneHop($currentText, $from, $to);
            } catch (TranslationApiException $e) {
                $reason = "hop {$from}->{$to} failed: " . $e->getMessage();
                tr_log($this->logFile, "CHAIN ABORTED: {$reason}");
                throw new ChainAbortedException($reason);
            }

            $steps[] = $step;
            $currentText = $step['text'];
        }

        return $steps;
    }

    private function runOneHop(string $text, string $from, string $to): array
    {
        $cached = $this->cache->get($text, $from, $to);
        if ($cached !== null) {
            return ['lang' => $to, 'text' => $cached, 'from_cache' => true];
        }

        if (!$this->quota->hasRoomFor(strlen($text))) {
            throw new TranslationApiException('daily quota exceeded');
        }

        $translated = $this->client->translate($text, $from, $to);

        $this->quota->recordUsage(strlen($text));
        $this->cache->set($text, $from, $to, $translated);

        return ['lang' => $to, 'text' => $translated, 'from_cache' => false];
    }
}
