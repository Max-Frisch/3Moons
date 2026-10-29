# References - where to look without reading everything

Niburuspace is a live private server, not a public repository. The best written
reconstruction of its behavior lives in the neoxnova docs and Go code, backed by
HAR captures and explorer data in neoxnova-ops. Treat those as the spec, but
remember they are incomplete: anything marked "TBD", "approximate", or "needs
capture" in those docs is not authoritative.

## Reference repos

| Path | What it is | Use it for |
|---|---|---|
| `C:\code\projects\2moons-v2.0` | Pristine yamilrh/2moons-v2.0 checkout | Original 2Moons behavior, templates, language strings, schema |
| `C:\code\projects\neoxnova` | Go rewrite plus reverse-engineered Niburuspace spec | Formulas, catalogs, feature specs, artwork |
| `C:\code\projects\neoxnova-ops` | Live captures, explorer tooling, ops data | Raw page HTML, HARs, techtree/academy/achievement data |

Do not edit the reference checkouts. Copy data/formulas into 3Moons and cite the
source in the change or in `docs/BUGS.md` / `docs/PRIORITIES.md`.

## 3Moons code map (upstream 2Moons patterns)

| Need | Look at |
|---|---|
| Player page controller | `includes/pages/game/Show*Page.class.php` |
| Admin page controller | `includes/pages/adm/Show*Page.php` |
| Game template | `styles/templates/game/page.*.tpl` |
| Admin template | `styles/templates/adm/*.tpl` |
| Build/research/shipyard math | `includes/classes/class.BuildFunctions.php` |
| Fleet missions | `includes/classes/missions/`, `includes/classes/class.FlyingFleetHandler.php` |
| DB schema/seed | `install/install.sql` |
| Language strings | `language/en/*.php` |
| Routing | `game.php`, `admin.php`, `index.php` |

Useful greps:

```bash
rg -n "class Show.*Page" includes/pages/game
rg -n "function AddBuildingToQueue|b_building_id|b_tech_queue" includes
rg -n "HTTP::_GP|\$_GET|\$_POST|\$_REQUEST" includes/pages
```

## Niburuspace spec map

### Page inventory and general behavior

- `neoxnova/neoxnova/docs/BALANCE_DATA_NEEDED.md` - the broadest live-system
  inventory: rates, catalog, senate, achievements, premium, black market,
  planetarium, academy, online bonus.
- `neoxnova/neoxnova/docs/PLAYABILITY_FIXES_PLAN.md` - live page routes, black
  market hub, `/market` vs Arsenal, UI/playability gaps.
- `neoxnova-ops/tools/explorer/data/_*.html` - captured live pages by name
  (`_academy.html`, `_arsenal.html`, `_live_page_market.html`, ...).

### Catalog, techtree, units

- `neoxnova/neoxnova/internal/game/catalog.go` - structure/tech/ship/defense
  definitions and cost curves.
- `neoxnova/neoxnova/internal/game/techtree.go` - prerequisite graph.
- `neoxnova/neoxnova/testdata/catalog.json` - sanitized captured catalog.
- `neoxnova-ops/tools/explorer/data/techtree-graph.json` - 114-item live graph.
- `neoxnova-ops/tools/explorer/data/unit-info.json`,
  `unit_classes.json` - unit stats and weapon/armor classes.

### Academy

- `neoxnova/neoxnova/internal/game/academy.go` - 34-skill tree, costs, effects.
- `neoxnova/neoxnova/docs/BALANCE_DATA_NEEDED.md` (Academy section).
- `neoxnova-ops/tools/explorer/data/academy-map.json`,
  `_academy.html`, `_academy2.html`.
- 3Moons port: `includes/classes/class.Academy.php`, `page=academy`.

### Arsenal / Market

- `neoxnova/neoxnova/internal/game/arsenal.go` - 19-upgrade catalog.
- `neoxnova/neoxnova/docs/ARSENAL_UPGRADES_IMPLEMENTATION.md`.
- `neoxnova-ops/tools/explorer/data/arsenal-manual.txt`,
  `_arsenal.html`, `_arsenal_live2.html`, `_live_page_market.html`,
  `_live_page_arsenal.html`.

### Senate: Officers and Governators

- `neoxnova/neoxnova/internal/game/senate.go` - officer/governator catalogs.
- `neoxnova/neoxnova/testdata/senate_officers.txt` - tooltip descriptions.
- `neoxnova-ops/tools/explorer/data/officers-*.json`.
- Note: yamilrh already renders both on `page=officier` (upper = governators,
  lower = officers). The missing part is Achievement Points and the scaling.

### Achievements / progression / bonus / premium

- `neoxnova/neoxnova/testdata/achievements.json` - 47 definitions and rewards.
- `neoxnova-ops/tools/explorer/data/achievements.json`.
- `neoxnova/neoxnova/docs/BALANCE_DATA_NEEDED.md` - peaceful/combat level,
  online bonus, premium, academy points.
- `neoxnova/neoxnova/docs/BACKLOG.md` - what is still unimplemented.

### Combat

- `neoxnova/neoxnova/internal/game/combat.go` - calibrated combat resolver.
- `neoxnova/neoxnova/docs/COMBAT_MODEL.md`,
  `neoxnova/neoxnova/docs/COMBAT_FINDINGS.md`.
- `neoxnova-ops/tools/explorer/data/combat/`,
  `_combatmsgs_s1.html` ... `_combatmsgs_s3.html`.
- yamilrh currently uses OPBE (`includes/libs/opbe/`); parity requires porting
  formulas, not copying Go code blindly.

### Expeditions

- `neoxnova/neoxnova/internal/game/expedition.go`.
- `neoxnova/neoxnova/docs/EXPEDITIONS_CALIBRATION_PLAN.md`.
- `neoxnova-ops/tools/explorer/data/_MissionCaseExpedition.php`,
  `expeditions.json`, `expedition-runs.json`, `expedition-reports.json`.

### Moons, Collider, Empire, Jumpgate

- `neoxnova/neoxnova/docs/MOONS.md`,
  `neoxnova/neoxnova/docs/COLLIDER_EMPIRE_PLAYBOOK.md`.
- `neoxnova/neoxnova/internal/game/collider.go`,
  `neoxnova/neoxnova/internal/game/moon.go`.
- `neoxnova-ops/tools/explorer/data/jumpgate-1772.html`,
  `jumpgate-1772b.html`.

### Black market / Planetarium / relocation / fields

- `neoxnova/neoxnova/internal/game/blackmarket.go`,
  `relocation.go`, `fields.go`.
- `neoxnova/neoxnova/docs/BALANCE_DATA_NEEDED.md` (Black market B4,
  Planetarium, field expansion).
- `neoxnova-ops/docs/ARSENAL_LIVE_2026-10-06.md`,
  `neoxnova-ops/docs/EXPLORER.md`.

### Artwork and UI

- `neoxnova/neoxnova/internal/web/static/niburu/` - mirrored live artwork
  (`buildings/`, `ships/`, `defenses/`, `research/`, `icons/`, `bg/`).
  The README there says private/local use only; do not redistribute publicly.
- `neoxnova/neoxnova/docs/WEB_UI_RESKIN_PLAN.md`.
- `neoxnova-ops/captures/assets/`.

### Messages and reports

- `neoxnova/neoxnova/internal/game/messages.go`.
- `neoxnova/neoxnova/docs/BACKLOG.md` (messages/reports entries).
- Live message HTML in `neoxnova-ops/tools/explorer/data/_messages.html`,
  `_message.js`, `_mc*.html`.

## Confidence labels

When porting, label the source in the change:

- **Captured**: exact value/behavior from HAR or live page.
- **Reconstructed**: derived from neoxnova docs/code but not re-verified.
- **Inferred**: best guess; needs a capture.

This keeps later work honest and makes it easy to revisit uncertain numbers.
