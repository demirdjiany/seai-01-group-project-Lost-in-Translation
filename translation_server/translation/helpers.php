<?php

function tr_read_json_file($path) {
    if (!file_exists($path)) {
        return [];
    }

    $data = json_decode(file_get_contents($path), true);

    return is_array($data) ? $data : [];
}

function tr_write_json_file($path, $data) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function tr_cache_key($text, $from, $to) {
    return sha1($from . "|" . $to . "|" . trim($text));
}

function tr_log($log_file, $message) {
    $dir = dirname($log_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $line = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($log_file, $line, FILE_APPEND);
}
