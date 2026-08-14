<?php

require_once __DIR__ . "/helpers.php";

function get_cached_translation($text, $from, $to, $cache_file) {
    $cache = tr_read_json_file($cache_file);
    $key = tr_cache_key($text, $from, $to);

    return $cache[$key]["translated"] ?? null;
}

function save_cached_translation($text, $from, $to, $translated, $cache_file) {
    $cache = tr_read_json_file($cache_file);
    $key = tr_cache_key($text, $from, $to);

    $cache[$key] = [
        "from" => $from,
        "to" => $to,
        "source" => $text,
        "translated" => $translated,
        "cached_at" => date("c"),
    ];

    tr_write_json_file($cache_file, $cache);
}
