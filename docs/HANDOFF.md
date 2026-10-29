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

- P1 target-level queue for buildings and research:
  - New `target` input beside the normal build/research button. Blank or `0`
    keeps the original single-level behavior.
  - The server loops the existing queue function until the absolute target
    level is reached or a limit stops it (max level, queue cap, missing fields,
    missing resources, busy lab, unavailable element).
  - A one-shot flash notice reports the queued range and the stop reason.
  - Shipyard already queues batches by quantity; no target-level change needed.
- Fixed B016: research cancel/refund 503 and wrong element/level; research
  queue-tail time recompute also corrected.
- Added queue language strings to all 8 locales.
- Smoke test still passes **64/64** pages (`ADMIN=1 tools/smoke.sh`).

## Manual verification done

- Buildings: target 29 on Metal Mine queued levels 28-29; notice showed the
  range. A too-high target queued to the 15-entry cap and reported
  "build queue is full".
- Research: target 7 on Spy Technology queued levels 6-7. Single-level add and
  queue removal both still work.
- Cancel/refund works for both queues; no PHP warnings in `includes/error.log`.
- Test state was cancelled/cleared. Metal Mine is now level 28 and Spy
  Technology level 7 because two queued levels completed during testing.

## Start here next session

**P2: bug sweep** over the game and admin surface. Crawl every page, fix PHP 8
warnings, and document exceptions. B008 (occasional admin MySQL errors) is
still open and needs an exact page/SQL error.

Then P3/P4/P6: Senate/Achievements/AP, Academy, progression.

## Operational gotchas

- Smarty compiles templates into the **root of `cache/`**, not only
  `cache/templates/`. After template edits:
  `docker compose exec web sh -c "find cache -maxdepth 1 -type f -name '*.php' -delete; find cache/templates -type f -delete"`.
  Do not delete `cache/templates/.gitkeep`; restore it with
  `git restore cache/templates/.gitkeep` if a cache wipe removes it.
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

## Open items

- B008: occasional MySQL admin errors were not reproduced in the smoke pass;
  re-open with an exact page and SQL error.
- Messages/reports parity, data/catalog parity, combat parity, and the additive
  Niburu modules are tracked in `docs/PRIORITIES.md`.

## Git

- Remote configured; P1 commit pushed to `origin/main`.
