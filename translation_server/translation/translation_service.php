<?php

require_once __DIR__ . "/chain_runner.php";

function run_translation_chain($seed_text, $chain = null) {
    $config = require __DIR__ . "/config.php";

    $seed_text = trim($seed_text);
    if ($seed_text === "") {
        return ["ok" => false, "steps" => [], "error" => "seed text is empty"];
    }
    if (strlen($seed_text) > $config["max_seed_length"]) {
        return ["ok" => false, "steps" => [], "error" => "seed text exceeds " . $config["max_seed_length"] . " characters"];
    }

    $chain = $chain ?? $config["default_chain"];
    if (count($chain) < 2) {
        return ["ok" => false, "steps" => [], "error" => "chain must contain at least two languages"];
    }

    return run_chain($seed_text, $chain, $config);
}
