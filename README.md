# VOX Smart Engine 2.3 — Campaign Scheduling Backend

This is a PHP 7.4+ (compatible with Source 1 target runtime) / MySQL add-on foundation for **legitimate social-media content publishing campaigns**.
It implements the backend planning logic represented by the OrganicSMM screenshot in a safe, transparent form:

- S-curve campaign pacing for publishing real content
- audience peak-hour weighting from analytics supplied by the connected platform
- configurable quiet hours / overnight slowdown
- campaign schedule preview before activation
- per-task status, execution history, pause and resume
- bounded queue claiming to prevent duplicate workers

It does **not** create, buy, simulate, or disguise likes, views, followers, subscribers, comments, reposts, or other artificial engagement. It does not implement anti-detection timing or randomized engagement quantities.

## Important integration status

This package is a backend module, not a drop-in replacement for the entire Source 1 application. It has no platform OAuth credentials and no production Instagram, Facebook, YouTube, TikTok, or Telegram publisher adapters. Those adapters must use each platform's official, currently permitted APIs and account permissions.

## Requirements

- PHP 7.4+ (compatible with Source 1 target runtime)
- PDO + PDO_MYSQL
- MySQL 8.0+ / MariaDB 10.5+
- A cron process or supervisor to run the worker
- HTTPS and authenticated access through the host application's existing session system

## Installation

1. Back up Source 1 and its database.
2. Copy `src/` into a namespaced location in Source 1, adapting its autoloading convention.
3. Import `database/001_vox_campaign_engine.sql`.
4. Copy `config.example.php` to a location outside the public web root and set database credentials.
5. Integrate `public/campaign-preview.php` behind Source 1 authentication and CSRF protection. Do not expose it as a public unauthenticated endpoint.
6. Configure a worker command under a process supervisor or cron. The included `bin/worker.php` is a safe queue skeleton; it deliberately requires a configured official publisher adapter before it can publish anything.

## Preview API

`POST /campaign-preview.php` accepts JSON:

```json
{
  "platform": "instagram",
  "goal": "publish_content",
  "timezone": "Asia/Kolkata",
  "start_at": "2026-11-20 09:00:00",
  "end_at": "2026-11-23 21:00:00",
  "items": 8,
  "quiet_hours": {"start": "22:00", "end": "07:00"},
  "peak_windows": [{"start": "18:00", "end": "21:00", "weight": 1.5}],
  "curve": "s_curve"
}
```

The endpoint returns planned publishing slots only. It does not create engagement or execute platform actions.

## Scheduling behaviour

- S-curve allocates genuine content publishing tasks gradually across the campaign.
- Peak-hour weighting places more tasks in audience-active windows, when supported by actual account analytics.
- Quiet hours defer publishing tasks to the next permitted window.
- The planner never changes the quantity of likes, views, followers, subscribers, comments, or reposts.
- Preview output is an estimate. Platform rate limits, review states, API availability and user approval can change actual execution.

## Production checklist

- Enforce customer ownership / tenant isolation on every campaign query.
- Add CSRF protection to state-changing routes.
- Store OAuth refresh tokens encrypted with a managed key; never commit secrets.
- Validate platform scopes and media requirements.
- Use official platform APIs only; check current platform terms and API access before enabling each action.
- Add idempotency keys and provider-side duplicate protection.
- Add rate-limit handling, retry backoff and dead-letter handling.
- Run database migrations in staging before production.
- Do not claim "zero bans", "undetectable", or guaranteed reach/results.
