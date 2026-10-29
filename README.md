# 3Moons

3Moons is a working fork of [`yamilrh/2moons-v2.0`](https://github.com/yamilrh/2moons-v2.0)
(PHP 8.2, Smarty 4, MariaDB) aimed at **Niburuspace-like gameplay with fewer bugs**.

The goal is not a clean-room rewrite. The goal is to take the already-complete
2Moons base game, stabilize it, and add the custom Niburuspace systems and
quality-of-life features that make the live server enjoyable to play.

## Status

- Base game: complete (buildings, research, shipyard, fleets, missions, moons,
  alliances, chat, messages, admin, multi-universe, battle simulator, ...).
- Stabilization: P0 complete - all game/admin pages pass the smoke test.
- Building and research queues accept an absolute target level; the queue stops
  at the level cap, field limit, queue cap, or missing resources and reports why.
- Niburuspace modules: mostly absent; see [docs/PRIORITIES.md](docs/PRIORITIES.md).
- Local dev: Docker Compose, no XAMPP and no host PHP changes required.

## Current local instance

- Game: http://localhost:8080
- Admin: http://localhost:8080/admin.php
- Login: `admin` / `admin123`
- Admin password: `admin123`
- DB: `localhost:13307`, database/user/password `2moons`

The instance is running from this repo on port 8080. The database is the
external volume `2moons-v20_yamilrh_db`, so it survives `docker compose down`.

## Quick start

```bash
docker compose up -d --build
# game:  http://localhost:8080
# admin: http://localhost:8080/admin.php
# db:    localhost:13307  (db/user/pass: 2moons)
```

The local install is seeded by the stock 2Moons installer. The generated
`includes/config.php` is gitignored. The database lives in the external Docker
volume `2moons-v20_yamilrh_db`, so `docker compose down` does not delete it;
use `docker compose down -v` only when you intentionally want a clean install.

If the installer is needed again:

```bash
touch includes/ENABLE_INSTALL_TOOL
# open http://localhost:8080/install/
rm includes/ENABLE_INSTALL_TOOL
```

## Layout

| Path | Purpose |
|---|---|
| `includes/pages/game/` | Player-facing page controllers (`Show*Page.class.php`) |
| `includes/pages/adm/` | Admin page controllers |
| `includes/classes/` | Core classes: builds, fleets, missions, sessions, DB, templates |
| `styles/templates/game/` | Smarty templates for the game |
| `styles/templates/adm/` | Smarty templates for the admin panel |
| `language/en/` | English language strings (other locales mirror this) |
| `install/install.sql` | Full database schema and seed data |
| `scripts/game/` | Client-side JS |

## Documentation

- [docs/REFERENCES.md](docs/REFERENCES.md) - where the upstream code and the
  Niburuspace reverse-engineering live, and which files to read first.
- [docs/PLAYBOOK.md](docs/PLAYBOOK.md) - the phased action plan.
- [docs/PRIORITIES.md](docs/PRIORITIES.md) - ranked impact/effort/risk list.
- [docs/BUGS.md](docs/BUGS.md) - known defects and triage notes.
- [docs/HANDOFF.md](docs/HANDOFF.md) - current session state and next steps.
- [AGENTS.md](AGENTS.md) - working conventions for AI/human contributors.

## References (read-only)

- `C:\code\projects\2moons-v2.0` - pristine upstream checkout.
- `C:\code\projects\neoxnova` - Go rewrite plus the reconstructed Niburuspace spec.
- `C:\code\projects\neoxnova-ops` - live captures, explorer tooling, ops data.

Do not edit the reference checkouts from this project. Copy formulas, data, or
artwork into 3Moons deliberately, and record the source in the relevant doc.
