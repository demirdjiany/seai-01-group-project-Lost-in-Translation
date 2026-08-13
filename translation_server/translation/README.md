# Translation Service (Student A)

Everyone else only ever needs this:

```php
require_once __DIR__ . '/translation/translation_service.php';

$result = run_translation_chain("It's raining cats and dogs");

if ($result['ok']) {
    $result['steps'];
    // [
    //   ['lang' => 'en', 'text' => "It's raining cats and dogs", 'from_cache' => false],
    //   ['lang' => 'ja', 'text' => '...', 'from_cache' => false],
    //   ...
    //   ['lang' => 'en', 'text' => '...final mangled result...', 'from_cache' => true/false],
    // ]
} else {
    $result['error']; // e.g. "hop ar->fi failed: ..." - log it, don't show the round to players
}
```

Rules:
- Nothing outside this folder calls the translation API. Everything goes through `run_translation_chain()`.
- `steps[0]` is always the original English seed. `steps[count-1]` is always the final English
  result — that pair is what feeds the mangle score.
- Pass a second argument to override the chain, e.g. the "control experiment" from the brief:
  `run_translation_chain($seed, ['en', 'fr', 'es', 'en'])`.
- If `ok` is `false`, `steps` is always empty. Never show a half-finished chain to a player.
- Seeds over 120 characters or empty strings are rejected before any API call is made.

## How it fails safely

Each hop gets one retry. If it still fails (network error, MyMemory reporting an error inside a
200 response, an empty translation, or the daily quota being exhausted), the whole round is
aborted, the reason is appended to `storage/translation_log.txt`, and `run_translation_chain()`
returns `ok => false`. No partial chain is ever returned.

## Files

| File | Responsibility |
|---|---|
| `config.php` | All settings in one place (API URL, chain, quota limit, file paths) |
| `helpers.php` | Small functions shared by the files below (JSON read/write with file locking, cache key, logging) |
| `TranslationClient.php` | Talks to the MyMemory API for a single hop, retries once |
| `TranslationCache.php` | Looks up / stores one hop's result by (text, from-lang, to-lang) |
| `QuotaTracker.php` | Counts characters sent today, blocks calls once the daily budget is spent |
| `ChainRunner.php` | Walks the seed through every hop, cache first, API second, collects every step |
| `translation_service.php` | The single function teammates call: `run_translation_chain()` |
| `test_chain.php` | `php test_chain.php` — sanity-checks the whole thing against the live API |

## Storage

`storage/` holds three auto-created files:
- `cache.json` — every hop ever translated. Delete it to force fresh translations.
- `quota.json` — characters used today, resets automatically on a new date.
- `translation_log.txt` — one line per aborted round.

None of these need to exist beforehand; the code creates them on first use.

## Notes for whoever wires up round generation

- Generating a round is slow (up to 7 sequential API calls the first time a seed is used) — the
  brief asks for a loading state on the frontend for exactly this reason.
- Re-running the same seed sentence again is fast and free: every hop comes straight from cache.
- The quota is set to 45,000 chars/day (MyMemory's cap is ~50,000 with the `de` email param, we
  leave headroom). Check `QuotaTracker::remainingToday()` if you want to surface quota state
  anywhere.
