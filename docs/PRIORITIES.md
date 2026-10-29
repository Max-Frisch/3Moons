# Priorities - impact / effort / risk

Phase numbers match `docs/PLAYBOOK.md`. Ranked for a playable, Niburuspace-like
server on the 2Moons base. Effort is relative (S = hours, M = days, L =
multi-day), not calendar estimates.

| Phase | Item | Impact | Effort | Risk | Depends | Notes |
|---|---|---|---|---|---|---|
| P0 | PHP 8 stabilization + admin 503s | Very high | S-M | Low | - | DONE 2026-10-29. 94/94 smoke pages pass. |
| P0 | Smoke test for game + admin | High | S | Low | - | DONE. `tools/smoke.sh` (use `ADMIN=1` for admin). |
| P1 | Target-level / multi-level build+research+shipyard queue | Very high | M | Medium | P0 | DONE 2026-10-29. Buildings + research accept an absolute target level and loop the existing queue; stop reasons shown. Shipyard already queues batches by quantity; no level concept. |
| P2 | Senate: achievements, AP, governator scaling | High | M-L | Medium | P0 | DONE 2026-10-29. 47 achievements + `page=achievements`, AP/antimatter rewards + system messages, `users` AP/antimatter/gov-level columns, exact live governator AP/DM curves and `base + 1%/level` scaling. Daily/peaceful/battle/moon/expo/Arsenal progress sources remain for P4/P5; missing Niburu ids (conveyors, ships 221-228, defenses 412-419) remain for P7. |
| P3 | Academy (34 skills, points, effects) | High | L | Medium | P2 | DONE 2026-10-29. Captured 34-node, 3-branch tree, point costs, prereqs, target-level upgrades, branch resets, `page=academy`, factor/fleet-cost/fuel/IRN wiring. Academy points come from achievements for now; Online Bonus/donation/peaceful sources land in P5. |
| P4 | Arsenal and Market (19 upgrades, auction) | High | L | Medium | P0 | DONE 2026-10-29. 19-upgrade catalog, activation rules, expedition drops by fleet-point tier, `page=arsenal`, `page=market` auction (list/buy/remove/72h expiry), combat/production/engine wiring. Multi-weapon attack still uses the primary class and conveyors stay catalog-only until P7. |
| P5 | Progression: peaceful level, combat XP, Bonus, Premium | High | M-L | Medium | P2 | DONE 2026-10-29. Peaceful online curve + step bonuses, battle-level/combat XP, `page=bonus`, `page=premium` with captured pblist costs, 24h new-account premium, and exp/mine/build/queue/fuel wiring. Expedition/moon/collider/conveyor premium items are stored for P6-P8. |
| P6 | Black market, Planetarium, Collider, Empire, Jumpgate | Medium | L | Medium | P0 | DONE 2026-10-29. Six-tile `page=port` hub, instant DM building/tech markets, real voucher redemption, Planetarium fields + relocation (cooldown/attack lock), moon Collider with account-wide DM accrual/premium/combat multipliers, full Empire matrix with production/filters, and level-scaled Jumpgate recharge. Planetarium Stardust diameter sink remains for P7/P8. |
| P7 | Data, combat and expedition parity | Very high | L | High | - | Catalog/techtree/units first (can run in parallel with features), then combat calibration and expedition tables. Combat parity is the hardest technical item. |
| P8 | UI reskin + messages/reports parity | Medium | L | Low | P0 | Artwork already mirrored in neoxnova. |

## Recommended order

1. **P0 stabilization** (done).
2. **P1 target-level queue** (done).
3. **P2 Senate/achievements** (done).
4. **P3 Academy** (done).
5. **P4/P5**: Arsenal/market and progression - the remaining Niburu identity
   pieces.
6. **P6** (done): black market, Planetarium, Collider, Empire, Jumpgate.
7. **P7-P8**: data/combat/expedition parity, then reskin and report polish.

## Feasibility notes

- The 2Moons base already has the hard primitives: serialized build/tech queues,
  fleet events, missions, moons, alliances, admin, Smarty templates.
- Most missing Niburu systems are additive pages + tables + effects, not deep
  engine changes. That is why this route is cheaper than finishing neoxnova.
- The two genuinely hard areas are **combat parity** (OPBE vs live) and **exact
  balance data**; both require captures and should be treated as ongoing tracks.
- yamilrh has no automated test suite. Add small smoke checks first; add focused
  PHP tests only where a bug is subtle.
- Avoid broad refactors. Every hour spent modernizing PHP is an hour not spent
  on Niburu features.

## Quick wins

- Replace raw superglobals with `HTTP::_GP` defaults.
- Guard all `unserialize()` calls with `is_array()`.
- Add missing template variables (`$Selected`, etc.).
- Fix format strings (`%t`) and Smarty syntax.
- Make `information` / `playerCard` tolerate a missing `id`.
- Alias `page=officer` to `page=officier`.
