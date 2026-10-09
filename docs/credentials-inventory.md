# Credentials & External Dependency Inventory

Phase 0 deliverable (2.0 Rebuild Plan). Lists every external service/secret
this app depends on and where it's configured today — no secret values are
recorded here. See the 2.0 Rebuild Plan doc for the full audit and the
hotfix tracking this inventory supports.

| Dependency | Current location | Documented in `.env.example`? | Notes |
|---|---|---|---|
| App encryption key (`APP_KEY`) | `.env` | Yes (blank, generated via `php artisan key:generate`) | Standard Laravel key, rotated per environment. |
| MySQL database credentials | `.env` (`DB_HOST`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD`) | Yes (commented out, sqlite is the example default) | Production uses MySQL; `.env` itself is gitignored. |
| Google service-account key (Sheets API) | `storage/credentials.json`, hardcoded path via `storage_path('credentials.json')` in all 4 `GoogleSheetService*` classes | **No** — not referenced anywhere in `.env.example` or `config/services.php` | **Needs rotation** (hotfix #7) — was previously not excluded by `.gitignore`. An orphaned, unused duplicate key also exists at `storage/credentials22.json` and should be deleted. Phase 4 moves the path to config/env. |
| OpenRouter API key (AI Chat) | Read via a bare `env('OPENROUTER_API_KEY')` call directly inside `AIChatController` | **No** — undocumented, a fresh checkout has no way to discover this is needed | Bare `env()` calls outside `config/*.php` silently break if `php artisan config:cache` is ever run. Phase 4 moves this into `config/services.php`. |
| Mail (order confirmation emails) | `.env` (`MAIL_MAILER`/`MAIL_HOST`/etc.) | Yes (defaults to `log` driver) | Production SMTP credentials, if any, live only in the server's `.env`. |
| Rep plaintext passwords | `reps.password` column | N/A — not a credential file, a DB column | **Must stop being written** (hotfix #5) and the column dropped in Phase 1; it is not used by any login flow. |

## Action items tracked elsewhere

- Hotfix #7 (rotate the Google service-account key, delete the orphaned
  duplicate, add `storage/credentials*.json` to `.gitignore`) — `.gitignore`
  fix already applied in this repo as part of Phase 0's baseline commit.
- Phase 4 moves the Google credentials path and the OpenRouter key into
  `config/services.php` + documented `.env` entries.
