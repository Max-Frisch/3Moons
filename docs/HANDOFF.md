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
- DB schema is now revision **9** (`install/migrations/migration_9.sql`).

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
- Extended `tools/smoke.sh` to 98 pages, including achievements, academy,
  arsenal, market, invalid-input and admin subpage regressions.
  `ADMIN=1 tools/smoke.sh` passes **98/98**.
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

- P4 Arsenal and Market:
  - Ported the captured 19-upgrade catalog, per-activation values, 10/50/250k
    fleet-point tiers, light/medium/heavy drop pools, flat 10% combat-win drop
    chance, activation chances (100% first 10, -2%/success, 75% floor,
    -10% value on failure).
  - Added `account_upgrades`, `upgrade_items`, `market_lots`,
    `market_purchases`, plus `users.arsenal_found` / `arsenal_activated`
    (migration 7) and `page=arsenal` / `page=market`.
  - Market supports listing 1-25 drawings at an Antimatter unit rate, buying,
    cancelling own lots and 72h expiry with automatic returns. Buy/remove/expire
    use locked transactions.
  - Expedition case-4 combat wins now roll one drawing: tier comes from the sent
    fleet's metal+crystal hull value; the item lands in the inventory and writes
    `arsenal_found`.
  - Combat wiring: OPBE applies each unit's primary weapon class, armour class
    and shield class upgrades additively to attack/shield/hull; engine upgrades
    scale the matching ship speed; production upgrades 17/18/19 add to the
    matching metal/crystal/deuterium mine output. Conveyors 14/15/16 remain
    catalog-only until the conveyor buildings (71-73) land in P7.
  - Achievements `Upgrade finder` / `Upgrade engeneer` now consume the two new
    counters.
  - Added `tests/arsenal_math.php` (48 checks) for catalog, tiers, drop pools,
    activation chances and per-unit class bonus aggregation.

- P5 Progression:
  - Added `includes/classes/class.Progression.php`: peaceful online-time curve
    (`37n^2 - 57.3n + 4076`), step bonuses (mines/research/energy +1%/level,
    fleet slots /10, planet fields /5, moon fields /12, factories 10/25/50) and
    the Online Bonus payout (DM `7000 + 630*level`, 10% peaceful progress,
    Academy points along the captured `(3,1)..(77,8.5)` line).
  - Added battle level/combat XP: `round(10700 * 1.07^level)` thresholds
    (fitted exactly to the live level tooltips) and `defender destroyed value /
    635,000,000` combat XP (captures split into this base and a 2.5x event
    group). XP is awarded on wins in attacks, moon destruction and expedition
    combat, and the report message appends "Received N combat experience".
  - Online time accrues per ingame request (15s minimum gap, 300s cap) and the
    peaceful/battle levels now feed `General`/`Veteran` achievements. Overview
    shows both levels.
  - Added `page=bonus` and `page=premium`. The premium catalog, pblist growth
    factors and `premium.js` cost/duration-discount formula are captured
    verbatim; purchases are stored in `premium_bonuses` and timed.
  - Wired premium resource/storage bonuses, build+research speed, queue slots,
    peaceful XP, combat XP, Bonus-button payout/cooldown and fuel reduction.
    Expedition/moon/collider/conveyor/alliance-bank entries are purchasable and
    stored for P6-P8.
  - New accounts receive the captured 24h premium start: +1000% resources,
    +100% build/research speed, +5 queue slots and +100% peaceful experience.
  - Added migration 8 (`peaceful_seconds`, `peaceful_seen_at`, `combat_xp`,
    `bonus_button_ready_at`, `bonus_last_click_at`, `premium_bonuses`).
  - Added `tests/progression_math.php` (63 checks) for curve anchors, battle
    thresholds, combat XP, bonus payout and the premium cost model.


- P6 Black market, Planetarium, Collider, Empire and Jumpgate:
  - `page=port` is the live six-tile Black market hub (Resource Trader,
    Merchant of Fleets, Planetarium, Buildings Market, Technology Market,
    Voucher System); the existing trader/merchant pages are reused.
  - Merchant defaults now match the captured `merchant_rate = 50%`; the
    Resource Trader fee is 250 DM and the M:C:D ratio remains 1:2:4.
  - `page=dmbuild` / `page=dmtech` buy one instant building/research level for
    `ceil((metal+crystal+deuterium)/1000)` Dark Matter. Prerequisites, max
    levels and planet fields are enforced; queues are not touched.
  - `page=reward2` is a real voucher redemption backed by `vouchers`
    (code, resources/currencies, max uses, expiry, single-use locking). Codes
    are issued out-of-band by SQL/admin.
  - `page=planet` (Planetarium) buys fields with the captured geometric curve
    (`200 * 1.1^n`), tracks `fields_bought`, and teleports planets using
    `15000/1000/2500` DM per galaxy/system/position. Inter-system moves are
    limited to one per hour, same-system moves are unlimited, the destination
    must be empty, the moon follows its planet and a 15-minute attack lock is
    written (`attack_locked_until`).
  - `page=kollaider` is a moon-only Collider (sprite 69) bought with
    Antimatter (`round(850 * 1.35^(L-1))`). Captured DM/h anchors are
    4,752 / 10,454 / 17,250; the provisional quadratic extrapolates above L3.
    Production is accrued account-wide per request into `users.darkmatter`
    (skipped in vacation mode) and applies the combat-level +3%/level and
    premium `prem_prod_from_colly` multipliers.
  - `page=imperium` now shows every owned celestial in creation order with
    resources, hourly production (including Collider DM/h), fields, all 25+
    building rows, ships, defenses, Sigma totals and All/Building/Fleet/Defense
    filters.
  - Jumpgate recharge is now level-scaled: `gate_wait_time * 0.75^(L-1)`
    (live L1 40 min, L2 30 min, L6 ~9m30s), checked on both the source and
    target gates. The source moon must have a gate. Mission 1/2/9 from a
    recently teleported planet is blocked by the attack lock.
  - Added `tests/blackmarket_math.php` (34 checks) and `tests/collider_math.php`
    (17 checks).

## Manual verification done

- `ADMIN=1 tools/smoke.sh`: 98/98 pass.
- `docker compose exec -T web php tests/achievements_math.php`: 14/14 pass.
- `docker compose exec -T web php tests/academy_math.php`: 55/55 pass.
- `docker compose exec -T web php tests/arsenal_math.php`: 48/48 pass.
- `docker compose exec -T web php tests/progression_math.php`: 63/63 pass.
- `docker compose exec -T web php tests/blackmarket_math.php`: 34/34 pass.
- `docker compose exec -T web php tests/collider_math.php`: 17/17 pass.
- `ADMIN=1 tools/smoke.sh`: 107/107 pass (adds port, dmbuild, dmtech, planet, reward2, kollaider, collider).
- Online Bonus claim: admin level 0 paid 7,000 DM + 1 Academy point and added
  424 peaceful seconds; the cooldown was written.
- Premium purchase: 1% storage for 1 day cost 1 Antimatter, wrote the row and
  showed as active; test row refunded afterwards.
- New-account premium grant inserted the four captured 24h rows
  (res 1000, build speed 100, queue 5, peaceful XP 100).
- Combat XP: 500b defender losses awarded 787 XP; `combat_xp=10700` showed
  combat level 1, and the Veteran achievement reported current progress 1.
- Premium factor wiring: resource +10.0, build/research -0.5 time factor,
  queue +5, fuel -10%, combat XP +55%, Bonus button x4.
- Arsenal activation: two light-armour activations reached level 2 / +1.2%,
  consumed both drawings and wrote `arsenal_activated = 2`.
- Market: listed 1 laser drawing at 700 Antimatter (seller inventory dropped),
  a second account bought it (buyer -700, seller +700, drawing transferred,
  purchase row written), self-buy was rejected, remove returned the drawing and
  a forced expired lot returned automatically on the next page load.
- Expedition drop path exercised through `Arsenal::addItems()`: a tier-1 roll
  returned code 1, the drawing landed in the inventory and `arsenal_found`
  incremented.
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
- Collider: built level 1 on a test moon for 850 Antimatter; one hour of
  elapsed production added exactly 4,752 Dark Matter and advanced
  `collider_dm_at`; the Empire matrix showed 4,752 DM/h.
- Buildings market: bought Moon base level 1 for 160 DM (level-1 cost); level
  and `field_current` incremented. Technology market: bought Spy Tech 13->14
  for 22,925 DM.
- Planetarium: bought +2 fields for 420 DM (saved 411 after same-second
  accrual); teleported 1:1:2 -> 1:1:3 (2,500 DM), the moon followed, the
  attack lock was written and a second inter-system move was refused with the
  cooldown; moving back within the same system was allowed.
- Voucher: `TESTCODE` redeemed once for 123 DM / 7 Antimatter / resources, then
  `uses` incremented and the code remained usable once more.
- Jumpgate: at L1 with `last_jump_time = now-2000` the info card showed 400 s;
  at L2 with `now-1200` it showed 600 s (2,400 s base x0.75).
- Test moon/voucher and the admin DM/AM/fields/coordinates were restored after
  verification.
- No new lines in `includes/error.log`; no PHP warnings on the crawled pages.

## Data confidence / still not data-driven

- **Captured (exact)**: governator ids, max levels, AP/DM price bases and
  factors, per-level 1% effect step; achievement requirement steps and unit
  growth factors; reward curves calibrated against the live star badges in
  `HumanAIShare` (e.g. Metal Miner 26 = 6988, Terraformer 11 = 343,
  Light defense 48 = 14102, Imperial fleet 21 = 56257). Arsenal catalog,
  per-activation values, tier gates, drop pools, activation chances and unit
  card classes are captured from the live pages/fixture.
- **Reconstructed**: reward curves for achievements with no high-level star
  anchor (Raider, both daily Victory/expo, Moon destroyer, Mon maker,
  Victory Hostal, Fighter). These use category-typical growth and are marked in
  the catalog.
- **Still guesstimated / not wired**:
  - Peaceful level and battle level do not exist yet (P5), so General and
    Veteran achievements show progress 0.
  - Daily wins/expo counters are not reset or tracked; only `users.wons` is
    wired (Raider/Fighter).
  - Moon destroy/make, expedition total and DM-found have no event counters yet
    (P5/P7), so those misc achievements stay at 0. Arsenal found/activated now
    feed Upgrade finder / Upgrade engeneer.
  - The following Niburu ids do not exist in the 3Moons catalog yet (P7), so
    their achievements cannot progress: conveyors 71-73; ships 221, 222 and
    225-228; defenses 412-419. Requirements are displayed with the captured
    names but progress remains 0.
  - Arsenal combat attack uses each unit's **primary** weapon class only.
    Multi-weapon units (213/216/225-228, 411-419) will need per-component
    weapon classes in the P7 combat rework for exact numbers.
  - Conveyor upgrades 14/15/16 are catalog/activation/market complete but have
    no production effect yet because conveyor buildings 71-73 do not exist.
  - Academy skills without a modelled effect stay text-only: 1103/1108/1109/
    1110/1111 combat procs, 1208/1209/1210 defense/fusion/solar, 1304-1307/
    1309/1310 defense recovery/domes, 1303/1311 double-shield procs and 1308
    rapid-fire reduction. `1112` contributes its fuel reduction only (the -2%
    armour line is not modelled).
  - Reward curves above the highest screenshot anchor are extrapolated. A
    boosted local admin Terraformer (level 100) would otherwise generate an
    unrealistic AP total; the test admin was reset to Terraformer 20 for sane
    numbers.
  - Collider production is captured only for levels 1-3; levels 4+ use the
    provisional quadratic (547L^2 + 4061L + 144). The combat +3%/level and
    premium `prem_prod_from_colly` multipliers are hooks, not yet captured.
  - Planetarium relocation/fields are captured. The separate diameter upgrade
    (100B metal + 50B crystal debris + 1 Stardust for +276..414 km / +12..18
    fields) is not wired because 3Moons has no Stardust currency yet.
  - Voucher codes have a working redemption path but no admin UI; issue them
    with SQL into `vouchers` until the admin giveaway flow is extended.

## Start here next session

**P7: catalog, combat and expedition parity** - conveyor buildings 71-73,
missing Niburu ships/defenses, multi-weapon attack components, combat
calibration and expedition tables. B008 is still open and needs an exact
page/SQL error. Then P8 reskin/reports.

P6 follow-ups: multi-weapon Collider/Arsenal combat classes remain P7; the
Planetarium diameter/Stardust sink is not wired because Stardust is not part
of the 3Moons economy yet.

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

- Remote configured; P2-P5 commits pushed to `origin/main` (P5 commit added
  after this handoff).
