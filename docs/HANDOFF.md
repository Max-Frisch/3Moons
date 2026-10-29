# Session handoff - 2026-10-29

## State

- Repo: `C:\code\projects\3Moons` (new git repo, branch `main`).
- Local instance: http://localhost:8080, login `admin` / `admin123`,
  admin panel `admin123`.
- DB: `localhost:13307`, database/user/password `2moons`.
- Docker: `3moons-web-1` + `3moons-db-1`; DB volume is external
  (`2moons-v20_yamilrh_db`) and survives `docker compose down`.
- Universe is open (`game_disable=0`) and set to English.
- Reference checkouts are pristine and read-only:
  `2moons-v2.0`, `neoxnova`, `neoxnova-ops`.

## Completed this session

- Created the 3Moons repo from `yamilrh/2moons-v2.0`.
- Added Docker dev stack (PHP 8.2/Apache + MariaDB 10.11).
- Added docs: `README.md`, `AGENTS.md`, `docs/PLAYBOOK.md`,
  `docs/PRIORITIES.md`, `docs/REFERENCES.md`, `docs/BUGS.md`.
- Added `tools/smoke.sh`; `ADMIN=1 tools/smoke.sh` passes **64/64** pages.
- Completed P0 stabilization. See `docs/BUGS.md` for the fixed list.

## Corrections to remember

- yamilrh uses the stock `gow` theme, **not** Niburu artwork. The mirrored
  Niburu art is in `neoxnova/neoxnova/internal/web/static/niburu/`.
- yamilrh already renders **governators** (upper half of `page=officier`) and
  **officers** (lower half). Governator scaling is stuck at the 10% base because
  the Achievement Points/achievement system is missing.
- 2Moons is the common ancestor of both yamilrh and neoxnova; neoxnova's spec
  is a reconstruction, not a clean-room design.

## Start here next session

**P1: target-level / multi-level queue** for buildings, research, and shipyard.
The live Niburu dialog takes a target level and queues the run up to it.

Files to read first:

- `includes/pages/game/ShowBuildingsPage.class.php`
  - `AddBuildingToQueue()`, `getQueueData()`, `show()`
- `includes/pages/game/ShowResearchPage.class.php`
  - `AddBuildingToQueue()`, `getQueueData()`, `show()`
- `includes/pages/game/ShowShipyardPage.class.php`
- `styles/templates/game/page.buildings.default.tpl`
- `styles/templates/game/page.research.default.tpl`
- `styles/templates/game/page.shipyard.default.tpl`
- `scripts/game/buildlist.js`, `research.js`, `shipyard.js`

Queue storage to understand:

- Planet building queue: `planets.b_building`, `planets.b_building_id`
  (serialized array of entries).
- Research queue: `users.b_tech`, `users.b_tech_queue`, `users.b_tech_id`,
  `users.b_tech_planet`.
- Shipyard queue: `planets.b_hangar`, `planets.b_hangar_id`.

Suggested first slice:

1. Add a numeric target input next to the existing build button.
2. POST the target level; loop the existing queue function until the target,
   resources, prerequisites, or queue cap stop it.
3. Keep the single-level button unchanged.
4. Show the queued range and the stop reason.
5. Test queue + cancel refund via the smoke script and manual UI.

## Operational gotchas

- Smarty compiles templates into the **root of `cache/`**, not only
  `cache/templates/`. After template edits:
  `find cache -maxdepth 1 -type f -name '*.php' -delete`
  (and optionally `find cache/templates -type f -delete`).
- Keep `cache/sessions/` and `cache/templates/` directories present
  (`.gitkeep` files are tracked).
- Admin pages that check `sid` need `&sid=<session id>`; `tools/smoke.sh`
  extracts it from the `2Moons` cookie.
- The admin search filter field is `fuki` (not `fucki`).
- `includes/config.php` is generated and gitignored.
- `includes/ENABLE_INSTALL_TOOL` is gitignored; create it only when running the
  installer, then remove it.
- The stock admin page `dump` is permission-gated; use `ADMIN=1` smoke only.

## Open items

- B008: occasional MySQL admin errors were not reproduced in the smoke pass;
  re-open with an exact page and SQL error.
- Messages/reports parity, data/catalog parity, combat parity, and the additive
  Niburu modules are tracked in `docs/PRIORITIES.md`.

## Git

- Baseline commit created at the end of this session.
- No remote configured yet.
