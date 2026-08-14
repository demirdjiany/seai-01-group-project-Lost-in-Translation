<?php

return [
    'api_endpoint' => 'https://api.mymemory.translated.net/get',
    'contact_email' => 'mohanadbataineh2004@gmail.com',
    'default_chain' => ['en', 'ja', 'ar', 'fi', 'sw', 'hu', 'ko', 'en'],
    'max_retries_per_hop' => 1,
    'daily_char_quota' => 45000,
    'max_seed_length' => 120,
    'cache_file' => __DIR__ . '/storage/cache.json',
    'quota_file' => __DIR__ . '/storage/quota.json',
    'log_file' => __DIR__ . '/storage/translation_log.txt',
];
