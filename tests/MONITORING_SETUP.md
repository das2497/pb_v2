# Monitoring & Error Alerting (Phase 7)

Before this, the app had **no error monitoring at all**: a single,
never-rotated `storage/logs/laravel.log` and an empty
`bootstrap/app.php`'s `withExceptions()` callback. Regressions were only
discovered when a shop/rep reported something broken. This closes that gap
with two additive, zero-behavior-change pieces.

## What changed

1. **Log rotation** - `LOG_STACK` is now `daily,slack` instead of `single`.
   Logs now live in `storage/logs/laravel-YYYY-MM-DD.log`, one file per day,
   kept for 14 days (`LOG_DAILY_DAYS`, Laravel's default) and pruned
   automatically. The old `storage/logs/laravel.log` is no longer written to
   - it's pre-existing data (currently ~340MB, grown unbounded under the old
   `single` driver since this project started) and is left untouched here;
   archive or delete it whenever convenient, it's not read by anything.

2. **Slack alerting on error+** - `config/logging.php`'s `slack` channel is
   wired into the stack, but **only once `LOG_SLACK_WEBHOOK_URL` is set**.
   Left blank (the default), the stack's `channels` list filters `slack`
   out entirely - logging behaves exactly as before, just with rotation.
   This matters: Monolog's Slack handler throws a cURL error on *every*
   log call if given an empty webhook URL, which would otherwise take down
   logging app-wide the moment this shipped. Confirmed by testing both
   states directly (empty URL → no exception; URL set → channel included).

   The alert threshold is `LOG_SLACK_LEVEL` (default `error`), deliberately
   separate from `LOG_LEVEL` (`debug`, controls file verbosity) - Laravel's
   exception handler reports real uncaught exceptions at `error` by default,
   so this default actually catches them without flooding Slack with every
   debug/info/notice line the app already produces.

3. **Structured context on every reported exception** -
   `bootstrap/app.php`'s `withExceptions()` now merges `url`, `user_id` and
   `role` into every exception log entry (file and Slack alike), via
   Laravel 11's `$exceptions->context()`. Verified by simulating a real
   unhandled exception through the actual exception handler - the log line
   came out with `{"url":"...","user_id":null,"role":null,"exception":...}`.
   Before this, a log line gave you a stack trace and nothing about who hit
   it or on what URL.

## Enabling Slack alerts

1. In Slack: create an app at api.slack.com/apps -> "From scratch" -> pick
   the workspace.
2. Under **Incoming Webhooks**, toggle it on, then "Add New Webhook to
   Workspace" and pick the channel alerts should post to.
3. Copy the generated URL (`https://hooks.slack.com/services/...`).
4. Set `LOG_SLACK_WEBHOOK_URL` to that URL in `.env` (locally) and in
   production's environment config. That's the only step needed - the
   channel activates itself once the URL is non-empty.
5. Optional: `LOG_SLACK_USERNAME` / `LOG_SLACK_EMOJI` cosmetics,
   `LOG_SLACK_LEVEL` to widen/narrow what reaches Slack (e.g. `warning` to
   also catch non-fatal issues, at the cost of more noise).

## Verifying it

```
php artisan tinker --execute="app(\Illuminate\Contracts\Debug\ExceptionHandler::class)->report(new \Exception('test'));"
```

then check `storage/logs/laravel-<today>.log` for the entry (and the Slack
channel, if a webhook URL is set).

## Already in place (not added here)

Laravel 11's default health-check route, `GET /up` (see `bootstrap/app.php`,
`health: '/up'`), already exists and returns 200 when the app is up - point
an uptime monitor (UptimeRobot, Pingdom, a cron `curl`, etc.) at it for
"is the site even reachable" alerting; that's a separate concern from the
error/exception alerting above.
