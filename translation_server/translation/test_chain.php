<?php

require_once __DIR__ . '/translation_service.php';

$seed = "It's raining cats and dogs";

echo 'Seed: ' . $seed . PHP_EOL;
echo 'Running chain...' . PHP_EOL . PHP_EOL;

$result = run_translation_chain($seed);

if (!$result['ok']) {
    echo 'FAILED: ' . $result['error'] . PHP_EOL;
    exit(1);
}

foreach ($result['steps'] as $step) {
    $source = $step['from_cache'] ? 'cache' : 'api';
    echo str_pad(strtoupper($step['lang']), 4) . "[{$source}] " . $step['text'] . PHP_EOL;
}

echo PHP_EOL . 'Done.' . PHP_EOL;
