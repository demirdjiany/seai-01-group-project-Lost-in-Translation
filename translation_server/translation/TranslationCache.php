<?php

// Caches one hop's result by (text, source language, target language).
// If the exact same hop was ever translated before, we reuse it instead of
// calling the API again.

require_once __DIR__ . '/helpers.php';

class TranslationCache
{
    private string $cacheFile;

    public function __construct(string $cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    public function get(string $text, string $from, string $to): ?string
    {
        $data = tr_read_json_file($this->cacheFile);
        $key = tr_cache_key($text, $from, $to);

        return $data[$key]['translated'] ?? null;
    }

    public function set(string $text, string $from, string $to, string $translated): void
    {
        $data = tr_read_json_file($this->cacheFile);
        $key = tr_cache_key($text, $from, $to);

        $data[$key] = [
            'from' => $from,
            'to' => $to,
            'source' => $text,
            'translated' => $translated,
            'cached_at' => date('c'),
        ];

        tr_write_json_file($this->cacheFile, $data);
    }
}
