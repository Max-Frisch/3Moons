# Session handoff - 2026-10-29

## State

- Repo: `C:\code\projects\3Moons` (branch `main`), remote
  `origin https://github.com/Max-Frisch/3Moons.git`.
- Local instance: http://localhost:8080, login `admin` / `admin123`,
  admin panel `admin123`.
- DB: `localhost:13307`, database/user/password `2moons`.
- Docker: `3moons-web-1` + `3moons-db-1`; DB volume is external
  (`2moons-v20_yamilrh_db`) and survives `docker compose down`.
- Universe is open (`game_disable=0`) and set to English.
- Reference checkouts are pristine and read-only:
  `2moons-v2.0`, `neoxnova`, `neoxnova-ops`.

## Completed this session

- P2 bug sweep over the game + admin surface (B017-B030):
  - Fixed the friendly-error page: `ShowErrorPage::printError()` passed
    `$fullSide` as `$redirectButtons`, so every `printMessage` error 503'd.
    Fixed in game and login; also fixed the same arg-order bug in
    `information` / `playerCard`.
  - Fixed `page=board` on game + login (`FILTER_FLAG_SCHEME_REQUIRED` was
    removed in PHP 8).
  - Guarded missing/invalid input on fleet missiles, alliance subpages,
    note details, buddy requests, battle reports and jump-gate send.
  - Closed router exposure of internal FleetTable/Officier methods.
  - Fixed admin log subpages (globals, `$LogArray`, unguarded detail), cronjob
    detail/edit with a bad id, global-message BBCode loading, and
    account-editor POST rows.
  - Replaced PHP 8.2-deprecated `utf8_encode` / `utf8_decode` with
    `mb_convert_encoding()` in message read/write.
- Extended `tools/smoke.sh` to 85 pages, including invalid-input and admin
  subpage regressions. `ADMIN=1 tools/smoke.sh` passes **85/85**.
- B008 (occasional admin MySQL errors) was not reproduced during the sweep.

## Manual verification done

- `ADMIN=1 tools/smoke.sh`: 85/85 pass.
- `game.php?page=nonexistent`, `information` without id, `playerCard` with a
  bad id, `fleetMissile` without a target, invalid alliance/notes/buddy/report
  ids: all render the friendly error instead of 503.
- Admin `log&type=player|planet|settings|present` and bad `detail` ids: 200.
- Admin cronjob `detail`/`edit` with a bad id: 200.
- Admin global message send (PM mode): sends and returns success; the message
  renders in the game inbox.
- Admin account editor POST with unknown planet/user ids: friendly message,
  no warnings. A valid planet resource edit writes the log row with the right
  universe.
- No new lines in `includes/error.log`; no PHP warnings on the crawled pages.

## Start here next session

**P3: Senate completion** - Achievements + AP + governator scaling. Officers
and governators already render on `page=officier`; the governator scaling is
stuck at the 10% base because AP/achievements are missing.

B008 is still open and needs an exact page/SQL error.

Then P4 Academy, P6 progression, P5 Arsenal/market.

## Operational gotchas

- Smarty compiles templates into the **root of `cache/`**, not only
  `cache/templates/`. After template edits:
  `docker compose exec web sh -c "find cache -maxdepth 1 -type f -name '*.php' -delete; find cache/templates -type f ! -name '.gitkeep' -delete"`.
  Keep the `.gitkeep` files in place; `ClearCache()` now preserves them.
- Keep `cache/sessions/` and `cache/templates/` directories present
  (`.gitkeep` files are tracked).
- Admin pages that check `sid` need `&sid=<session id>`; `tools/smoke.sh`
  extracts it from the `2Moons` cookie.
- The admin search filter field is `fuki` (not `fucki`).
- `includes/config.php` is generated and gitignored.
- `includes/ENABLE_INSTALL_TOOL` is gitignored; create it only when running the
  installer, then remove it.
- The stock admin page `dump` is permission-gated; use `ADMIN=1` smoke only.
- The queue notice uses `$_SESSION['queueNotice']`; it is read once in `show()`
  and cleared so a refresh does not repeat it.
- When testing admin POSTs by hand, use a fresh cookie jar and log into the game
  first, then POST `admin_pw` to `admin.php`. Mixing an old game/admin jar can
  leave the session invalid and redirect to `index.php?code=3`.

## Open items

- B008: occasional MySQL admin errors were not reproduced in the P0 or P2
  sweeps; re-open with an exact page and SQL error.
- Messages/reports parity, data/catalog parity, combat parity, and the additive
  Niburu modules are tracked in `docs/PRIORITIES.md`.

## Git

- Remote configured; P2 commit pushed to `origin/main`.
