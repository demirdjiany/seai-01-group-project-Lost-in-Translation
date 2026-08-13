<?php

// Central settings for the translation service.
// Change values here instead of editing logic in the other files.

return [
    // MyMemory free translation API (no key required).
    'api_endpoint' => 'https://api.mymemory.translated.net/get',

    // Passing an email raises MyMemory's daily quota from ~5,000 to ~50,000 characters.
    'contact_email' => 'mohanadbataineh2004@gmail.com',

    // Default chain: English -> six languages that mangle grammar heavily -> English.
    'default_chain' => ['en', 'ja', 'ar', 'fi', 'sw', 'hu', 'ko', 'en'],

    // How many extra attempts a single hop gets before the whole round is aborted.
    'max_retries_per_hop' => 1,

    // Stay safely under MyMemory's ~50,000 char/day cap.
    'daily_char_quota' => 45000,

    // Longest seed sentence we accept (matches MyMemory's 500 byte limit).
    'max_seed_length' => 120,

    // Where cache / quota / logs are stored.
    'cache_file' => __DIR__ . '/storage/cache.json',
    'quota_file' => __DIR__ . '/storage/quota.json',
    'log_file' => __DIR__ . '/storage/translation_log.txt',
];
