# Release Notes Template

Copy this template to `docs/releases/{version}.md` for every major project block.

## Metadata

- Version:
- Release date:
- Release type: major | minor | patch | pre-release
- Related commits:
- Related issues:
- Git tag:

## Summary

Briefly describe what changed and why this release exists.

## Main Changes

- List the most important implementation changes in plain language.

## Added

- List new features, screens, actions, models, jobs, notifications, tests, or docs.

## Changed

- List behavior, architecture, workflow, UI, API, or database changes.

## Fixed

- List bug fixes or regressions resolved by this release.

## Removed

- List deleted features, routes, configuration, dependencies, or data structures.

## Security

- List security-sensitive changes, authorization updates, validation hardening, or secret/config handling changes.

## Deprecated

- List functionality that still exists but should no longer be used.

## Database Changes

- Migrations:
- New tables:
- Changed tables:
- Removed tables:
- New indexes:
- Foreign key changes:
- Seeder changes:
- Data migration steps:

## Configuration Changes

- New `.env` keys:
- Updated config files:
- Queue/cache/session/storage implications:
- External service changes:

## New Routes Or Pages

- Public:
- Buyer:
- Seller:
- Admin:
- API:

## New Permissions

- Policies:
- Gates:
- Roles:
- Admin-only actions:
- Seller-only actions:
- Buyer-only actions:

## Testing Notes

- New tests:
- Updated tests:
- Test command:
- Result:
- Coverage gaps:

## Deployment Notes

- Required commands:
- Migration order:
- Build command:
- Cache/config steps:
- Manual deployment checks:

## Breaking Changes

- List any behavior, API, database, configuration, or deployment changes that require downstream work.

## Migration Steps

1. Document every required step to move from the previous release to this release.

## Known Issues

- List incomplete work, accepted limitations, or follow-up items.

## Rollback Notes

- Database rollback:
- Code rollback:
- Config rollback:
- Data recovery concerns:

## Manual Verification Checklist

- [ ] Main happy path checked.
- [ ] Empty states checked.
- [ ] Validation failures checked.
- [ ] Authorization failures checked.
- [ ] Mobile/responsive layout checked if UI changed.
- [ ] Security-sensitive routes checked.
- [ ] Logs checked for new errors.
