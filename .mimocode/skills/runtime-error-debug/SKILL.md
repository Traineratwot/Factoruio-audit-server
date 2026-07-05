---
name: Runtime Error Debug
description: >
  Debug runtime errors in the Factorio Audit Laravel app. Use when the user
  pastes an error message (SQL, PHP exception, JS error, cURL error) and asks
  for a fix.
---

# Runtime Error Debug

## When to use

- User pastes an error message and asks for help
- User reports unexpected behavior with an error trace
- User sees a white screen or 500 error

## Procedure

### 1. Parse the error

Identify the error type:
- **SQL error** (SQLSTATE): look at constraint name, table, column
- **PHP exception**: look at class, method, line number
- **JS/TS error**: look at component, hook, file path
- **cURL error**: look at error code (28=timeout, 6=DNS, 7=connection refused)

### 2. Read the relevant code

For SQL errors like `Unique violation`:
- Read the model mentioned in the SQL
- Check for `upsert`, `firstOrCreate`, `create` patterns
- Check if the unique constraint is on `(mod_id, mod_version)` or `sha1`

For PHP exceptions:
- Read the file and line mentioned in the trace
- Check for null checks, type mismatches, missing relations

For cURL errors:
- Check `config/factorio.php` for timeout settings
- Check if the external service (Factorio API, WebSocket) is reachable

### 3. Common fixes in this project

**Report unique constraint** (`reports_sha1_unique` or `reports_mod_id_mod_version_unique`):
- Use `Report::updateOrCreate()` instead of `Report::create()`
- The unique constraint is on `(mod_id, mod_version)` AND `sha1`

**Undefined variable $mod**:
- Check `AuditJob.php` — the `$mod` variable must be fetched before use
- Pattern: `$mod = Mod::findOrFail($this->modId)`

**cURL timeout** on `mods.factorio.com`:
- Factorio API can be slow. Wrap in try/catch in batch commands
- Config: `FACTORIO_FULL_INFO_DELAY_MS` controls delay between requests

**Meilisearch not available**:
- Meilisearch is not in `docker-compose.dev.yml`
- Use `AuditController::search` (DB LIKE fallback) instead of `Mod::search()`

### 4. Verify the fix

```bash
# PHPStan
composer types:check

# Run tests
docker compose -f docker-compose.dev.yml exec -e APP_ENV=testing app php artisan test

# Manual test if needed
docker compose -f docker-compose.dev.yml exec app php artisan tinker
```
