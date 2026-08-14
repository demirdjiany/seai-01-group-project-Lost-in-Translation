# translation service (student A)

Everyone else just needs this:

```php
require_once __DIR__ . '/translation/translation_service.php';

$result = run_translation_chain("It's raining cats and dogs");

if ($result['ok']) {
    $result['steps']; // steps[0] is the seed, last one is the final english
} else {
    $result['error']; // something failed, don't show the round, already logged
}
```

Pass a chain as the second argument if you want to override the default one,
e.g. `run_translation_chain($seed, ['en', 'fr', 'es', 'en'])`.

Nobody outside this folder should be calling the translation API directly.

## files

- `config.php` - api url, chain, quota limit, etc
- `helpers.php` - json read/write + logging, used by a few files below
- `translation_client.php` - the actual MyMemory calls, retries once
- `translation_cache.php` - get/save a hop by (text, from, to)
- `quota_tracker.php` - counts chars used today, stops before we hit the daily limit
- `chain_runner.php` - runs the whole chain hop by hop
- `translation_service.php` - the one function everyone calls, `run_translation_chain()`
- `test_chain.php` - `php test_chain.php` to sanity check it's still working

`storage/` has the cache/quota/log json files, they get created automatically.
Delete `storage/cache.json` if you want to force fresh translations.
