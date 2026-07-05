---
description: >
  Create or update AGENTS.md based on the current codebase structure, routes,
  models, and conventions. Use when the user asks to document the project for
  AI agents.
---

# Update AGENTS.md

Read the entire codebase structure and produce a comprehensive AGENTS.md file
at the project root. Include:

1. **Stack**: backend, frontend, database, search, queue, admin
2. **Project structure**: directory tree with key files
3. **Database schema**: all tables, columns, relationships
4. **Key architecture notes**: how services connect, API endpoints, conventions
5. **Routes**: all routes with controller methods
6. **Commands**: PHP and JS/TS commands for lint, test, build, dev
7. **Docker**: common make targets
8. **Frontend conventions**: component library, styling, state management
9. **Testing**: framework, database, commands
10. **Git hooks**: pre-commit behavior
11. **Environment**: required services and config

Read these files to gather information:
- `composer.json` — dependencies and scripts
- `package.json` — frontend dependencies and scripts
- `routes/web.php`, `routes/console.php` — all routes
- `app/Models/*.php` — all models and relationships
- `app/Http/Controllers/*.php` — all controllers
- `app/Filament/Resources/*/Resource.php` — admin resources
- `database/migrations/*.php` — all migrations
- `docker-compose.dev.yml` — Docker services
- `Makefile` — make targets
- `.eslintrc.*`, `prettier.config.*`, `phpstan.neon`, `pint.json` — linter config
- `phpunit.xml` — test config
- `AGENTS.md` (existing) — for reference and continuity

Output the AGENTS.md file with all sections filled in.
