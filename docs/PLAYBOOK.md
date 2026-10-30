# Playbook - 3Moons

## Objective

Turn the working yamilrh/2moons-v2.0 base into a stable, Niburuspace-like server:
same additive systems and quality-of-life, fewer PHP/MySQL defects, and no
carpal tunnel from single-level build clicks.

## Principles

1. Stabilize before adding features. A broken admin page blocks every later task.
2. Reuse the 2Moons base. Do not rewrite fleets, auth, alliances, or the UI shell.
3. Use neoxnova as the spec oracle, not as the runtime. Port formulas/data.
4. One priority item per change set. Keep diffs reviewable.
5. Every behavior gets a source label: Captured, Reconstructed, or Inferred
   (see `docs/REFERENCES.md`).
6. Keep the reference repos pristine. Copy, do not edit.

## Phase 0 - Stabilization (P0) - DONE 2026-10-29

Deliverables:

- Fix the research-page `foreach(bool)` warning (`ShowResearchPage`).
- Replace raw `$_GET` / `$_POST` / `$_REQUEST` access in page controllers with
  `HTTP::_GP(...)` defaults. This is the root cause of most admin 503s.
- Fix the `ActivePage.tpl` Smarty compile error.
- Fix `ShowStatUpdatePage` `%t` sprintf crash.
- Fix `ShowMessageListPage` missing `$Selected`.
- Add `tools/smoke.sh` and run it against all game and admin pages.

Exit: smoke test shows no `Fatal error`, `Warning:`, `Deprecated:`, or SQL
errors on the main game/admin pages; known exceptions are documented.

## Phase 1 - Build/research target-level UX (P1) - DONE 2026-10-29

The live Niburu dialog lets you choose a target level and queues all levels up
to it. 3Moons currently only queues one level per click.

Deliverables:

- Add a target-level input to the building, research, and shipyard dialogs.
- Queue each affordable level in order, stopping on resources/prereqs/queue cap.
- Keep the existing single-level button working.
- Show the queued range and the reason for stopping (resources, cap, prereq).
- Persist the target in the existing serialized queue format where possible.

Exit: from level 23, entering 79 queues the affordable run in one action; cancel
refunds correctly.

## Phase 2 - Senate, governators, achievements (P2) - DONE 2026-10-29

yamilrh already renders officers and governators on `page=officier`
(upper half = governators, lower half = officers). The missing piece is the
Achievement Points economy and scaling, so governators are effectively stuck at
the 10% base.

Deliverables:

- Achievement catalog and progress counters (47 definitions from the captures).
- Antimatter + AP rewards and System messages on level-up.
- Feed AP into governator levels (currently stuck at the 10% base).
- Verify officer costs/effects against `senate_officers.txt` and captures.

Exit: achievements award AP; governator bonuses scale above 10%; officer effects
match captured values.

## Phase 3 - Academy (P3) - DONE 2026-10-29

Deliverables:

- 34-skill, 3-branch tree with prerequisites and point costs.
- Academy points from the Online Bonus, donation, peaceful levels, achievements.
- Branch reset tiers (100% / 75% / 50% refund).
- Wire effects: combat, production, research, build time, fleet, cargo, colonies.

Exit: the tree matches `academy-map.json`; effects are observable in-game.

## Phase 4 - Arsenal and Market (P4) - DONE 2026-10-29

Deliverables:

- 19-upgrade catalog, per-level bonuses, activation.
- `page=arsenal` listing and `page=market` auction/lots.
- Upgrade finds from expeditions gated by fleet points.
- Sell/upgrade dialogs and lot rate/information panels.

Exit: an upgrade can be found, listed, bought, activated, and its bonus is
reflected in combat/economy.

## Phase 5 - Progression: peaceful, combat XP, bonus, premium (P5) - DONE 2026-10-29

Deliverables:

- Peaceful level curve and side-bar display.
- Combat XP and combat level from battle reports.
- Online Bonus button (DM + peaceful XP + academy points, premium multiplier).
- Premium page/pblist and the 24h new-account bonus.

Exit: peaceful/combat levels advance from real actions; bonus and premium
payouts match captured values.

## Phase 6 - Black market, Planetarium, Collider, Empire, Jumpgate (P6) - DONE 2026-10-29

Deliverables:

- `/blackmarket` hub and `page=port` layout.
- Merchant of Fleets scrap rates; DM instant building/tech levels.
- Voucher page (redemption can remain disabled until codes exist).
- Planetarium: relocation, DM field expansion, diameter/fields via debris.
- Moon Collider production and page.
- Empire matrix with filters and bulk reduce actions.
- Jumpgate page/flow parity.

Exit: each page is reachable, performs its action, and matches captured costs.

## Phase 7 - Data and combat parity (P7) - DONE 2026-10-30

Deliverables:

- Custom units, defenses, buildings, researches, and 114-node techtree.
- Rates, storage, production, debris, colony caps.
- Combat model and rapid fire (replace or calibrate OPBE).
- Expedition tables and report formats.
- Stardust currency, Planetarium diameter sink and expedition finds.
- Full expedition flavour catalogue.
- The eight Academy combat procs (1103/1108/1109/1110/1111/1303/1308/1311).
- Existing build costs intentionally stay at the 2Moons values (see HANDOFF).

Exit: catalog/techtree tests from neoxnova can be reproduced as 3Moons checks;
combat and expedition samples match the captures within stated tolerances.

## Phase 8 - UI, messages, reports (P8) - IN PROGRESS

Deliverables:

- Niburu skin/artwork (private/local use only) and card-grid layout.
- Generic message categories, archive/outbox, read/unread.
- Live-style combat/espionage/expedition report detail screens.

Exit: the game is visually and informationally close to the live client.

## Per-item workflow

1. Read the relevant reference (see `docs/REFERENCES.md`).
2. Write a one-paragraph spec with source labels.
3. Implement the smallest change that satisfies it.
4. Lint changed PHP files.
5. Run `tools/smoke.sh`.
6. Manually verify the page/action.
7. Update `docs/BUGS.md` and `docs/PRIORITIES.md`.

## Definition of done

- No new PHP warnings/deprecations on the touched path.
- No SQL errors.
- Existing behavior preserved (single-level actions, refunds, queue order).
- Source of any ported data/formula is cited.
- Docs updated.
