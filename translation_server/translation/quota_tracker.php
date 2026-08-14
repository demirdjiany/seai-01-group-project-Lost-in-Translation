<?php

require_once __DIR__ . "/helpers.php";

function quota_load_today($quota_file) {
    $data = tr_read_json_file($quota_file);
    $today = date("Y-m-d");

    if (($data["date"] ?? null) !== $today) {
        $data = ["date" => $today, "used" => 0];
    }

    return $data;
}

function quota_has_room($char_count, $quota_file, $daily_limit) {
    $data = quota_load_today($quota_file);

    return ($data["used"] + $char_count) <= $daily_limit;
}

function quota_record_usage($char_count, $quota_file) {
    $data = quota_load_today($quota_file);
    $data["used"] += $char_count;

    tr_write_json_file($quota_file, $data);
}
