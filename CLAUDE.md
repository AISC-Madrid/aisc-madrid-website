# AISC Madrid website

PHP + MariaDB, desplegado con Coolify. Las migraciones SQL viven en `migrations/` (numeradas, re-ejecutables) y se aplican a mano: primero en la BD de dev, y en prod antes de hacer merge a `main`.

## Agent skills

### Issue tracker

Issues y specs en GitHub Issues de `AISC-Madrid/aisc-madrid-website` (CLI `gh`). See `docs/agents/issue-tracker.md`.

### Triage labels

Etiquetas por defecto (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`). See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: `GLOSSARY.md` y `docs/adr/` en la raíz. See `docs/agents/domain.md`.
