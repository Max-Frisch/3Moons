# Bugs and triage

Status: `open`, `fixed`, `wontfix`, `needs-capture`.

## Current status (2026-10-29)

`tools/smoke.sh` with `ADMIN=1`: **64/64 pages pass** (28 game + 36 admin).
The P0 stabilization pass is complete. Remaining known work is feature-level
(see `docs/PRIORITIES.md`), not page-breaking.

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
| B008 | open | Admin / MySQL | Occasional MySQL errors on admin pages | Not reproduced in the smoke pass | Re-open with an exact page and SQL error |
| B009 | fixed | Admin / rights+universe | `$_GET['sid']` warning before exit | Raw superglobal | `HTTP::_GP('sid', '')` |
| B010 | fixed | Game / research | Research page warning (screenshot) | Same as B001 | Same as B001 |
| B011 | fixed | Admin / search | Undefined `$Searchmethod`; filter field named `fucki` | `SearchPage.tpl` variable/name mismatch | `$SearchMethod` + `name=fuki` |
| B012 | fixed | Admin / universe | `number_format(): Argument #1 must be ?float, string given` | `UniversePage.tpl` piped `uni_name` through `|number`; modifier was strictly typed | Removed `|number` from the name; modifier accepts scalars |
| B013 | fixed | Admin / search | Undefined `$RESULT` / `$OrderBYParse` on first load | No defaults before the search branches | Initialized defaults |
| B014 | fixed | Admin / bans+multiips | Undefined `$bo_select_title`, `$bo_order_id`, `$mip_ip` | Controllers did not assign language keys used by templates | Assigned them |
| B015 | fixed | Runtime / cache | `session_start(): ... /cache/sessions/... No such file` | Clearing cache removed `cache/sessions` and `cache/templates` | Recreated them; `.gitkeep` files now keep them in the repo |

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
