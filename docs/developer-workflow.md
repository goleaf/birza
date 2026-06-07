# Developer Workflow

Use this workflow for normal development and future Codex tasks.

## Standard Flow

1. Create or switch to a focused branch.
2. Inspect the relevant routes, models, migrations, seeders, factories, tests, policies, and docs.
3. Make the smallest coherent change.
4. Keep controllers and Livewire components thin.
5. Move reusable business logic to actions, services, or model scopes.
6. Validate and authorize user input.
7. Add or update PHPUnit tests for behavior changes.
8. Run focused tests.
9. Run `npm run build` for frontend-impacting changes.
10. Update README/docs when behavior or architecture changes.
11. Update `CHANGELOG.md`.
12. Create release notes for major blocks.
13. Stage only prompt-owned files in dirty worktrees.
14. Commit with a clear message.

## Commands

```bash
php artisan optimize:clear
php artisan route:list --except-vendor
php artisan migrate:status --no-interaction
php artisan test --compact
npm run build
```

After PHP code changes:

```bash
vendor/bin/pint --dirty --format agent
```

## Focused Test Examples

```bash
php artisan test --compact tests/Feature/Marketplace
php artisan test --compact tests/Feature/Seeders
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
php artisan test --compact tests/Feature/Images/ProductImagePipelineTest.php
php artisan test --compact tests/Unit/Policies/ProductPolicyTest.php tests/Unit/Policies/OrderPolicyTest.php
```

## Commit Message Examples

```text
refactor: standardize role architecture
test: add feature coverage for core flows
security: harden marketplace authorization
perf: audit and optimize marketplace queries
docs: add project documentation
```

## Documentation Rules

Update these when behavior changes:

- `README.md` for main setup, behavior, feature, role, or command changes.
- `docs/*.md` for architecture, database, frontend, security, performance, testing, seeding, production, or workflow changes.
- `CHANGELOG.md` for every notable block.
- `docs/releases/{version}.md` or an unreleased module note for major blocks.

## Dirty Worktree Rules

This repository can have many unrelated local changes. Before committing:

```bash
git status --short
git diff --cached --name-only
git diff --cached --stat
git diff --check
```

Stage only files that belong to the current task. Do not revert unrelated user or prior-task changes.

## GSD Planning

For multi-step work, use the project-local GSD workflow under `.planning/`. Start with `$gsd-progress` or `$gsd-project-default` instead of creating a separate ad hoc planning system.
