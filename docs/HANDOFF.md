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
- DB schema is now revision **6** (`install/migrations/migration_6.sql`).

## Completed this session

- Bug sweep over the game + admin surface (P0 follow-up, B017-B030):
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
- Extended `tools/smoke.sh` to 94 pages, including achievements, academy, invalid-input
  and admin subpage regressions. `ADMIN=1 tools/smoke.sh` passes **94/94**.
- P2 Senate completion (Achievements + AP + governator scaling):
  - Added the 47-definition achievement catalog, per-user progress table,
    reward curves, system messages and the `page=achievements` UI.
  - Added `achievement_points`, `achievement_points_spent`, `antimatter` and
    the seven `gov_level_70x` columns to `users` (migration 5).
  - Governator levels now scale the effects in `getFactors()` exactly as live:
    `base + 0.01 * level` (Weapons 55 => +65%, Building 50 => -60%).
  - Governator AP/DM prices are the captured live curves
    (Weapons 55 => 2066 AP, Shield 55 => 2670 AP, Building 50 => 2348 AP).
  - `page=officier` now has a real governator panel: current level, effect at
    level, DM/day price, AP price, 1-7 day hire and irreversible AP improve.
  - Added `tests/achievements_math.php` (14 checks) for requirements, rewards
    and governator prices.
- B008 (occasional admin MySQL errors) was not reproduced during the sweep.

- P3 Academy:
  - Ported the captured 34-node, 3-branch tree from
    `neoxnova/internal/game/academy.go` and `academy-map.json`; cost curves
    reproduce all captured anchors (Weaponry 19 => 26, Standardisation (Fleet)
    17 => 435, Heavy Armour 8 => 214, ...).
  - Added `users.academy_points` / `academy_points_spent`, the `academy` skill
    table (migration 6) and `page=academy`.
  - Upgrade accepts an absolute target level and spends the affordable run;
    prerequisites, max level and partial purchases are enforced. Branch resets
    use the captured tiers: 100% for 10000 Antimatter, 75% for 200000 DM, 50%
    free.
  - Effects wired into `getFactors()` (attack/shield/armour, mine/energy
    production, research time, storage, cargo, fleet speed, fleet slots,
    planets), fleet build cost, fuel consumption and IRN research levels.
  - Academy points currently come from achievements (Reconstructed); the live
    Online Bonus, donation and peaceful-level sources land with P5.
  - Added `tests/academy_math.php` (55 checks) for the catalog, anchors,
    prerequisites, affordability and effect aggregation.

## Manual verification done

- `ADMIN=1 tools/smoke.sh`: 94/94 pass.
- `docker compose exec -T web php tests/achievements_math.php`: 14/14 pass.
- `docker compose exec -T web php tests/academy_math.php`: 55/55 pass.
- Academy upgrade (`1101` target 5, then target 20 with 2 points): spent the
  affordable run, stopped at level 6 and wrote the partial notice.
- Academy branch reset (50% free tier): returned 5 points and removed the rows.
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
- Achievement pages: all seven groups return 200 and show live-captured
  requirement/reward values.
- Governator improve: `id=701` from level 0 spent 50 AP and stored level 1.
  Governator hire: 2 days at level 1 cost 88,000 DM and extended `dm_attack`
  by 48 hours.
- Achievement level-up wrote a type-4 system message
  (`Reached: ... Received: ... Antimatter and ... Achievement Points`).
- No new lines in `includes/error.log`; no PHP warnings on the crawled pages.

## Data confidence / still not data-driven

- **Captured (exact)**: governator ids, max levels, AP/DM price bases and
  factors, per-level 1% effect step; achievement requirement steps and unit
  growth factors; reward curves calibrated against the live star badges in
  `HumanAIShare` (e.g. Metal Miner 26 = 6988, Terraformer 11 = 343,
  Light defense 48 = 14102, Imperial fleet 21 = 56257).
- **Reconstructed**: reward curves for achievements with no high-level star
  anchor (Raider, both daily Victory/expo, Moon destroyer, Mon maker,
  Victory Hostal, Fighter). These use category-typical growth and are marked in
  the catalog.
- **Still guesstimated / not wired**:
  - Peaceful level and battle level do not exist yet (P5), so General and
    Veteran achievements show progress 0.
  - Daily wins/expo counters are not reset or tracked; only `users.wons` is
    wired (Raider/Fighter).
  - Moon destroy/make, expedition total, DM-found and Arsenal upgrades have no
    event counters yet (P4/P5/P7), so those misc achievements stay at 0.
  - The following Niburu ids do not exist in the 3Moons catalog yet (P7), so
    their achievements cannot progress: conveyors 71-73; ships 221, 222 and
    225-228; defenses 412-419. Requirements are displayed with the captured
    names but progress remains 0.
  - Academy skills without a modelled effect stay text-only: 1103/1108/1109/
    1110/1111 combat procs, 1208/1209/1210 defense/fusion/solar, 1304-1307/
    1309/1310 defense recovery/domes, 1303/1311 double-shield procs and 1308
    rapid-fire reduction. `1112` contributes its fuel reduction only (the -2%
    armour line is not modelled).
  - Reward curves above the highest screenshot anchor are extrapolated. A
    boosted local admin Terraformer (level 100) would otherwise generate an
    unrealistic AP total; the test admin was reset to Terraformer 20 for sane
    numbers.

## Start here next session

**P4: Arsenal and Market** - 19-upgrade catalog, expedition finds, listing,
auction lots and activation, then wire bonuses into combat/economy.

B008 is still open and needs an exact page/SQL error.

Then P5 progression (Online Bonus is the main source of Academy points),
P6 black market/planetarium/collider, P7 catalog parity.

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
- Schema upgrades: bump `DB_VERSION_REQUIRED` in `includes/dbtables.php` and add
  `install/migrations/migration_N.sql`; existing installs must run
  `install/index.php?mode=upgrade` (or apply the SQL manually).
- Achievements are evaluated once per non-AJAX ingame request in `common.php`.
  Progress writes are skipped unless a level or metric changed.

## Open items

- B008: occasional MySQL admin errors were not reproduced in the P0
  sweeps; re-open with an exact page and SQL error.
- Messages/reports parity, data/catalog parity, combat parity, and the additive
  Niburu modules are tracked in `docs/PRIORITIES.md`.

## Git

- Remote configured; P2 and P3 commits pushed to `origin/main`
  (`24c8190` Academy, `58bd0be` phase docs).
