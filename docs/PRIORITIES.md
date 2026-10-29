# Priorities - impact / effort / risk

Ranked for a playable, Niburuspace-like server on the 2Moons base. Effort is
relative (S = hours, M = days, L = multi-day), not calendar estimates.

| # | Item | Impact | Effort | Risk | Depends | Notes |
|---|---|---|---|---|---|---|
| P0 | PHP 8 stabilization + admin 503s | Very high | S-M | Low | - | DONE 2026-10-29. 64/64 smoke pages pass. |
| P0 | Smoke test for game + admin | High | S | Low | - | DONE. `tools/smoke.sh` (use `ADMIN=1` for admin). |
| P1 | Target-level / multi-level build+research+shipyard queue | Very high | M | Medium | P0 | DONE 2026-10-29. Buildings + research accept an absolute target level and loop the existing queue; stop reasons shown. Shipyard already queues batches by quantity; no level concept. |
| P2 | Bug sweep: game pages, admin pages, MySQL errors | High | M | Low | P0 | DONE 2026-10-29. 85/85 smoke pages. Game + admin GET surface crawled; error-page, board, alliance, missile, notes, buddy, report, log, cronjob, BBCode, utf8 and account-editor POST bugs fixed (B017-B030). B008 still needs an exact repro. |
| P3 | Senate completion: Achievements + AP + governator scaling | High | M-L | Medium | P0 | Officers + governators already render on `page=officier`; governator scaling is stuck at the 10% base because AP/achievements are missing. |
| P4 | Academy (27 skills, points, effects) | High | L | Medium | P3 | AP/points source overlaps achievements. |
| P5 | Arsenal + Market (19 upgrades, auction) | High | L | Medium | P0 | Large additive system; data already reconstructed. |
| P6 | Progression: peaceful level, combat XP, Bonus, Premium | High | M-L | Medium | P3 | Additive bonuses drive the Niburu feel. |
| P7 | Black market: Merchant, DM build/tech, vouchers | Medium | M | Medium | P0 | Mostly pages + simple transactions. |
| P8 | Planetarium: relocation, fields, diameter | Medium | M | Medium | P0 | Relocation/fields formulas are already known. |
| P9 | Moon Collider + Empire matrix + Jumpgate | Medium | M | Medium | P0 | Additive pages; Collider is moon-only. |
| P10 | Catalog/techtree/unit/data parity | Very high | L | High | - | Needed for real balance; can run in parallel with features. |
| P11 | Combat model parity (OPBE vs live) | Very high | L | High | P10 | Hardest technical item; capture-driven. |
| P12 | Expedition parity | High | M-L | Medium | P10 | Tables and report formats. |
| P13 | UI reskin + messages/reports parity | Medium | L | Low | P0 | Artwork already mirrored in neoxnova. |

## Recommended order

1. **P0 stabilization** (done): research warning, admin superglobals,
   Smarty/sprintf defects, smoke test.
2. **P1 target-level queue** (done): buildings and research queue to an absolute
   target level with a stop reason; research cancel/refund fixed (B016).
3. **P2 bug sweep** (done): clean the rest of the game/admin surface.
4. **P3/P4/P6**: Senate/achievements, Academy, progression - the Niburu identity.
5. **P5/P7/P8/P9**: Arsenal/market, black market, planetarium, collider/empire.
6. **P10-P12**: data/combat/expedition parity, interleaved with feature work.
7. **P13**: reskin and report polish.

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
