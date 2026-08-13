<?php

// Small generic helpers shared by more than one file in this folder.
// No business logic here - just reading/writing JSON files and logging.

function tr_read_json_file(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $handle = fopen($path, 'r');
    if (!$handle) {
        return [];
    }

    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($contents, true);

    return is_array($data) ? $data : [];
}

function tr_write_json_file(string $path, array $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $handle = fopen($path, 'c');
    if (!$handle) {
        return;
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($handle, LOCK_UN);
    fclose($handle);
}

// One cache entry = one exact text translated from one language to another.
function tr_cache_key(string $text, string $from, string $to): string
{
    return sha1($from . '|' . $to . '|' . trim($text));
}

function tr_log(string $logFile, string $message): void
{
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
