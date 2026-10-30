# Bugs and triage

Status: `open`, `fixed`, `wontfix`, `needs-capture`.

## Current status (2026-10-29)

`tools/smoke.sh` with `ADMIN=1`: **107/107 pages pass** (58 game + 49 admin).
The P0-P6 bug/feature sweeps are complete for the crawled game/admin GET
surface, plus the admin global-message send and account-editor POST paths.
Remaining known work is feature-level (see `docs/PRIORITIES.md`), not
page-breaking.

## Defects

| ID | Status | Area | Symptom | Cause / file | Fix |
|---|---|---|---|---|---|
| B001 | fixed | Game / research | `foreach() argument must be of type array\|object, bool given`; page 503 | `ShowResearchPage::CheckLabSettingsInQueue()` used `$planet` instead of `$PLANET`; unguarded `unserialize()` | Use `$PLANET`, guard `is_array()`, array-safe element access |
| B002 | fixed | Admin (many) | Undefined array key `sid`, `mode`, `action`, `order`, `delete`, `view`; pages 503 | Raw `$_GET` / `$_REQUEST` in admin controllers | Replaced with `HTTP::_GP('key', default)` |
| B003 | fixed | Admin / active | `Unclosed '{' on line 21`; page 503 | Missing closing brace in `ShowActivePage.php` (PHP parse error, not Smarty) | Added the brace |
| B004 | fixed | Admin / statsupdate | `Unknown format specifier "t"` | `%t` in `sb_stats_update` language strings | Changed to `%s` (en/fr/pt/tr) |
| B005 | fixed | Admin / messagelist | Undefined `$Selected` in compiled template | `ShowMessageListPage` did not assign it | Assigned `Selected => $type` |
| B006 | fixed | Game / info | Warning on `page=information` / `playerCard` without `id` | No validation before array access | Friendly error via `printMessage()` |
| B007 | fixed | Routing | `page=officer` 503; real page is `page=officier` | No alias | Added `officer` / `officers` aliases in `game.php` |
| B008 | open | Admin / MySQL | Occasional MySQL errors on admin pages | Not reproduced in the P0 sweeps | Re-open with an exact page and SQL error |
| B009 | fixed | Admin / rights+universe | `$_GET['sid']` warning before exit | Raw superglobal | `HTTP::_GP('sid', '')` |
| B010 | fixed | Game / research | Research page warning (screenshot) | Same as B001 | Same as B001 |
| B011 | fixed | Admin / search | Undefined `$Searchmethod`; filter field named `fucki` | `SearchPage.tpl` variable/name mismatch | `$SearchMethod` + `name=fuki` |
| B012 | fixed | Admin / universe | `number_format(): Argument #1 must be ?float, string given` | `UniversePage.tpl` piped `uni_name` through `|number`; modifier was strictly typed | Removed `|number` from the name; modifier accepts scalars |
| B013 | fixed | Admin / search | Undefined `$RESULT` / `$OrderBYParse` on first load | No defaults before the search branches | Initialized defaults |
| B014 | fixed | Admin / bans+multiips | Undefined `$bo_select_title`, `$bo_order_id`, `$mip_ip` | Controllers did not assign language keys used by templates | Assigned them |
| B015 | fixed | Runtime / cache | `session_start(): ... /cache/sessions/... No such file` | Clearing cache removed `cache/sessions` and `cache/templates` | Recreated them; `.gitkeep` files now keep them in the repo |
| B016 | fixed | Game / research queue | Cancelling research 503s with `Undefined array key <techId>`; refund can use the wrong tech | `ShowResearchPage::CancelBuildingFromQueue()` used `$USER['b_tech_id']` and `$PLANET[$elementId]` (planet has no numeric tech keys) | Read the element/level from `$CurrentQueue[0]` and price with that level, matching the building queue. Also fixed the research `remove` time recompute (`getBuildingTime()` argument order) |
| B017 | fixed | Game+login / error page | Any friendly error page 503s; `Trying to access array offset on value of type bool` in `error.default.tpl` | `ShowErrorPage::printError()` passed `$fullSide` as the `$redirectButtons` argument | Pass `printMessage($Message, NULL, $redirect, $fullSide)` in game and login; fixed the same arg-order bug in `information`/`playerCard` |
| B018 | fixed | Game+login / board | `Undefined constant "FILTER_FLAG_SCHEME_REQUIRED"` | Flag removed in PHP 8; scheme is always required | Use `filter_var($url, FILTER_VALIDATE_URL)` |
| B019 | fixed | Game / fleetMissile | Missing/invalid target 503s on bool offsets | Target and target user fetched without guarding | Guard `$target`/`$targetUser`, default points/user rows |
| B020 | fixed | Game / alliance | `memberList`, `circular`, `admin`, `close` and invalid `info`/`apply` 503 on null alliance/rights | Rights only initialized for members; `isset()` is true for `false` DB rows | Initialize all rights to false; guard membership and `empty()` DB rows |
| B021 | fixed | Game / notes | `notes&mode=detail&id=<missing>` 503s in the template | Controller passed `false` to the template | Fall back to the new-note defaults |
| B022 | fixed | Game / buddyList | Invalid `request` / `accept` id 503s on false user rows | DB row used without checking | Show a friendly error / redirect back to the list |
| B023 | fixed | Game / raport | `mode=battlehall` with an invalid id 503s | Arrays read before the `isset()` check | Check `empty($reportData)` before reading it |
| B024 | fixed | Game / jumpgate | `sendFleet` continued with a missing target gate | `!isset($TargetGate)` is false for a `false` row | Use `empty()` |
| B025 | fixed | Game / routing | `fleetTable&mode=createACS` and `officier&mode=Update*` 503 with ArgumentCountError | Internal methods with required args are router-reachable | Made fleet-table ACS helpers protected (default controller `show`); made officer updaters private |
| B026 | fixed | Admin / log | `log&type=player`, `planet`, `settings`, `present` warnings; `type=detail` with bad id 503s | Missing `$USER` globals, uninitialized `$LogArray`, unguarded `unserialize()` | Added globals, defaults, and row/unserialize guards |
| B027 | fixed | Admin / cronjob | `detail`/`edit` with a bad id 503s; overview can warn | No row guard; `$LNG` not imported | Guard the row and import `$LNG` |
| B028 | fixed | Game / messages | PHP 8.2 `utf8_encode`/`utf8_decode` deprecations would 503 message read/write | Deprecated functions | Replaced with `mb_convert_encoding()` equivalents |
| B029 | fixed | Admin / globalmessage | Sending a global PM 503s with `Class "HTML_BBCodeParser2" not found` | Only `game.php` preloaded the BBCode parser | `BBCode::parse()` now self-loads the parser |
| B030 | fixed | Admin / accounteditor | POST with an unknown planet/user id 503s; resources log read `$before_dm['universe']` for a planet edit | Unguarded DB rows; wrong universe source | Guard every edited-row fetch and use the planet row's universe |
| B031 | fixed | Admin / clearcache | Visiting `clearcache` (and every smoke run) deleted the tracked `cache/*/.gitkeep` files | `ClearCache()` removed every non-directory entry | Exclude `.gitkeep` from the cache clear |
| B032 | fixed | Game / Senate | Governators were stuck at the 10% base; AP/DM prices used the old generic per-day values | `getFactors()` only added the base bonus and the old `UpdateExtra()` path had no level/AP model | Added achievements + AP, governator level columns, exact live AP/DM curves, `base + 1%/level` scaling and the `page=officier` governator panel |
| B033 | fixed | Game / DB | New black market/collider queries matched nothing because `planet_type` is an `enum('1','3')` | Numeric comparisons such as `planet_type = 3` compare the enum index, not the string | Quote enum values (`planet_type = '3'`) in the Collider accrual and Planetarium queries |
| B034 | fixed | Game / Fleet | Sending a fleet to an empty planet slot (colonization) or a missing moon/debris target logged PHP 8 warnings from `checkTarget()`; the warning output could corrupt the `OK` JSON and leave the Continue button stuck | `checkTarget()` read fields from a `false` DB row; Step 2/3 also handled missing target rows late | Guarded empty targets, return the proper no-moon/no-debris message, and normalize empty planet slots before mission checks |
| B035 | fixed | Game / Fleet | `fleetStep3` showed `Undefined array key "ownPlanet"` and could reject a valid launch | Step 1 never stored the origin planet in the fleet session | Store `ownPlanet` when creating the token and fall back to the current planet in Step 3 |
| B036 | fixed | Admin / Fleets | `admin.php?page=fleets` 503'd when at least one fleet was flying | `ShowFlyingFleetPage()` used `$USER['timezone']` without importing `$USER` | Added `$USER` to the function's `global` list |

## Admin crawl snapshot (2026-10-29, before P0)

Pages returning 503 with the stock code:

`rights`, `module`, `create`, `active`, `bans`, `messagelist`, `search`,
`statsupdate`, `news`, `universe`, `multiips`, `dump`.

All now pass the smoke test.

## Triage commands

```bash
# PHP syntax
docker compose exec web php -l includes/pages/game/ShowResearchPage.class.php

# Game smoke
tools/smoke.sh

# Game + admin smoke
ADMIN=1 tools/smoke.sh

# Error log
docker compose exec web sh -c 'tail -n 80 includes/error.log'
docker compose logs --tail=100 web
```

## Fix policy

- Prefer the smallest root-cause fix over per-page silencing.
- Do not hide warnings globally; fix the access.
- When a page genuinely needs a parameter, show a friendly error instead of a
  PHP warning page.
- Update this file when a bug is fixed, including the change reference.
