# AGENTS.md - working notes for 3Moons

## What this project is

3Moons is a fork of `yamilrh/2moons-v2.0` (PHP 8.2, Smarty 4, MariaDB).
It exists to reproduce the Niburuspace feature set on the proven 2Moons base,
with better stability and less clicking. It is not a Go rewrite.

## Reference repositories (read-only)

Use these instead of loading whole repos into context. Start with the files
listed in `docs/REFERENCES.md`.

- `C:\code\projects\2moons-v2.0` - pristine upstream. Consult for original
  behavior, templates, language strings, and schema.
- `C:\code\projects\neoxnova` - Go rewrite and the best available written
  reconstruction of Niburuspace behavior. The docs under
  `neoxnova/neoxnova/docs/` and the Go code under `neoxnova/neoxnova/internal/`
  are the spec oracle.
- `C:\code\projects\neoxnova-ops` - live captures (`captures/`), explorer data
  (`tools/explorer/data/`), and ops scripts. Treat HARs and secrets as private.

Never modify the reference checkouts from this repo. If you need a snippet,
port it and cite the source path in a comment or doc.

## Local development

```bash
docker compose up -d --build
docker compose logs -f web
docker compose down          # keeps the external DB volume
```

- Game: http://localhost:8080
- Admin: http://localhost:8080/admin.php
- DB: `localhost:13307`, database/user/password `2moons`
- PHP container: `docker compose exec web php -v`
- Lint a file: `docker compose exec web php -l path/to/file.php`
- Smoke test: `tools/smoke.sh` (requires curl; reads `BASE_URL`, `SMOKE_USER`,
  `SMOKE_PASS`).

The DB volume is external (`2moons-v20_yamilrh_db`) so restarts preserve state.
`includes/config.php` and `cache/` are gitignored.

## Conventions

- Match existing 2Moons patterns before inventing new ones.
- Use `HTTP::_GP('name', $default)` instead of raw `$_GET` / `$_POST` /
  `$_REQUEST`. Raw access is the main source of PHP 8 warnings and admin 503s.
- Guard `unserialize()` results (`is_array(...)` before `foreach`).
- Do not add a dependency unless it is already vendored or clearly necessary.
- Keep language strings in `language/en/` at minimum; mirror existing locale
  structure where practical.
- Avoid dynamic properties and PHP 8.1/8.2 deprecations.
- Keep diffs small and focused; one priority item per change set.
- Update `docs/BUGS.md` or `docs/PRIORITIES.md` when you fix or discover work.

## Testing checklist

1. `docker compose exec web php -l <changed files>`
2. `tools/smoke.sh`
3. Manually hit the changed page(s) in the browser.
4. Check `includes/error.log` and `docker compose logs web`.
5. Note any new warnings even if the page renders.

## Session handoff

Read `docs/HANDOFF.md` first. It records the current instance, the P0 fixes,
operational gotchas, and the exact P1 starting point (target-level queue).

## Where to start

Read `docs/PRIORITIES.md`. P0 stabilization is done (64/64 smoke pages pass).
Default order from here:

1. Target-level / multi-level queue UX.
3. Senate + Achievements/AP, then Academy, Arsenal, progression, black market,
   planetarium, collider.
4. Data parity (catalog, techtree, units, combat, expeditions).
5. UI reskin and report/message parity.
