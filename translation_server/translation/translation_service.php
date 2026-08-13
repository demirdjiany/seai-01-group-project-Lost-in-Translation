<?php

// The ONLY file the rest of the team should require().
// It exposes one function, run_translation_chain(), and hides every
// implementation detail (API client, cache, quota tracker, retries).
//
// Usage:
//
//   require_once __DIR__ . '/translation/translation_service.php';
//   $result = run_translation_chain("It's raining cats and dogs");
//
//   if ($result['ok']) {
//       $result['steps']; // every hop, in order - see ChainRunner.php for the shape
//   } else {
//       $result['error']; // human readable reason, already logged
//   }

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/TranslationCache.php';
require_once __DIR__ . '/QuotaTracker.php';
require_once __DIR__ . '/TranslationClient.php';
require_once __DIR__ . '/ChainRunner.php';

function run_translation_chain(string $seedText, ?array $chain = null): array
{
    $config = require __DIR__ . '/config.php';

    $seedText = trim($seedText);
    if ($seedText === '') {
        return ['ok' => false, 'steps' => [], 'error' => 'seed text is empty'];
    }
    if (strlen($seedText) > $config['max_seed_length']) {
        return ['ok' => false, 'steps' => [], 'error' => 'seed text exceeds ' . $config['max_seed_length'] . ' characters'];
    }

    $chain = $chain ?? $config['default_chain'];
    if (count($chain) < 2) {
        return ['ok' => false, 'steps' => [], 'error' => 'chain must contain at least two languages'];
    }

    $client = new TranslationClient($config['api_endpoint'], $config['contact_email'], $config['max_retries_per_hop']);
    $cache = new TranslationCache($config['cache_file']);
    $quota = new QuotaTracker($config['quota_file'], $config['daily_char_quota']);
    $runner = new ChainRunner($client, $cache, $quota, $config['log_file']);

    try {
        $steps = $runner->run($seedText, $chain);
        return ['ok' => true, 'steps' => $steps, 'error' => null];
    } catch (ChainAbortedException $e) {
        return ['ok' => false, 'steps' => [], 'error' => $e->getMessage()];
    }
}
