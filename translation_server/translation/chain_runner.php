<?php

require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/translation_cache.php";
require_once __DIR__ . "/quota_tracker.php";
require_once __DIR__ . "/translation_client.php";

function run_chain($seed_text, $chain, $config) {
    $steps = [["lang" => $chain[0], "text" => $seed_text, "from_cache" => false]];
    $current_text = $seed_text;

    for ($i = 0; $i < count($chain) - 1; $i++) {
        $from = $chain[$i];
        $to = $chain[$i + 1];

        $step = run_one_hop($current_text, $from, $to, $config);

        if (!$step["ok"]) {
            tr_log($config["log_file"], "CHAIN ABORTED: hop $from->$to failed: " . $step["error"]);
            return ["ok" => false, "steps" => [], "error" => $step["error"]];
        }

        $steps[] = ["lang" => $to, "text" => $step["text"], "from_cache" => $step["from_cache"]];
        $current_text = $step["text"];
    }

    return ["ok" => true, "steps" => $steps, "error" => null];
}

function run_one_hop($text, $from, $to, $config) {
    $cached = get_cached_translation($text, $from, $to, $config["cache_file"]);
    if ($cached !== null) {
        return ["ok" => true, "text" => $cached, "from_cache" => true];
    }

    if (!quota_has_room(strlen($text), $config["quota_file"], $config["daily_char_quota"])) {
        return ["ok" => false, "error" => "daily quota exceeded"];
    }

    $result = translate_hop($text, $from, $to, $config);
    if (!$result["ok"]) {
        return $result;
    }

    quota_record_usage(strlen($text), $config["quota_file"]);
    save_cached_translation($text, $from, $to, $result["text"], $config["cache_file"]);

    return ["ok" => true, "text" => $result["text"], "from_cache" => false];
}
