# Release Workflow

Birza uses `CHANGELOG.md`, per-version release notes, semantic versioning, disciplined commits, and git tags to make every major project block traceable.

## Required Files

- [CHANGELOG.md](../../CHANGELOG.md): concise project-wide change history.
- [docs/releases](.): detailed release notes for each version.
- [docs/release-notes-template.md](../release-notes-template.md): copyable release notes template.
- [README.md](../../README.md): public entry point with release-management summary.

## Release Sections

Use these sections in `CHANGELOG.md` and release notes:

- `Added`
- `Changed`
- `Fixed`
- `Removed`
- `Security`
- `Deprecated`

Do not add random unstructured notes. If a change is important enough to remember later, put it in the correct section.

## Semantic Versioning

Use semantic versioning:

- `MAJOR`: breaking changes.
- `MINOR`: new features or completed major project blocks.
- `PATCH`: bug fixes.
- Pre-release `0.x` versions are allowed while the project is not stable.

Suggested progression:

- `0.1.0`: initial baseline.
- `0.2.0`: products module.
- `0.3.0`: seller cabinet.
- `0.4.0`: buyer cabinet.
- `0.5.0`: orders workflow.
- `0.6.0`: cart and checkout.
- `0.7.0`: admin moderation.
- `0.8.0`: notifications.
- `0.9.0`: production hardening.
- `1.0.0`: first stable release.

These examples can be adjusted based on real project progress.

## Major Block Rule

After every major block, write release notes before tagging. Major blocks include:

- Products
- Orders
- Seller cabinet
- Buyer cabinet
- Admin moderation
- Payments
- Notifications
- Cart
- Checkout
- Image pipeline
- Multilingual system
- Roles and permissions
- Database architecture
- Feature tests
- UI standardization
- Livewire refactoring
- Production hardening

Every major block release note must include:

- Summary
- Main changes
- Database changes
- New routes or pages
- New permissions
- New tests
- Breaking changes
- Migration steps
- Known issues
- Manual verification checklist

## Module-Specific Changelog Rules

Products block:

- Product model changes
- Catalog changes
- Seller product management
- Product images
- Product statuses
- Product tests

Orders block:

- Order statuses
- Order items
- Status history
- Buyer order pages
- Seller order pages
- Admin order pages
- Order tests

Seller cabinet block:

- Seller dashboard
- Seller product tools
- Seller order tools
- Seller permissions
- Seller notifications
- Seller tests

Buyer cabinet block:

- Buyer dashboard
- Cart
- Checkout
- Orders
- Favorites
- Reviews
- Buyer notifications
- Buyer tests

Admin moderation block:

- Admin dashboard
- User management
- Seller moderation
- Product moderation
- Order moderation
- Reports if supported
- Admin permissions
- Admin tests

Payments block:

- Payment methods
- Payment statuses
- Payment callbacks
- Payment security
- Refund logic
- Payment tests
- Configuration changes

Notifications block:

- Notification types
- Channels
- Email templates
- Database notifications
- Read/unread logic
- Notification tests

## Git Commit Discipline

Use focused commits. A major block should not mix unrelated refactors, formatting churn, and feature work unless they are required for the block.

Recommended order:

1. Implement the feature.
2. Add or update tests.
3. Update README or docs if needed.
4. Update `CHANGELOG.md`.
5. Create release notes for the block.
6. Run tests and build.
7. Commit changes.
8. Create a version tag when the block is complete and verified.

## Release Checklist

- [ ] All changes committed.
- [ ] `CHANGELOG.md` updated.
- [ ] Release notes created under `docs/releases/`.
- [ ] README updated if needed.
- [ ] Migrations tested from zero.
- [ ] Seeders tested.
- [ ] Full test suite passed.
- [ ] Frontend build passed.
- [ ] Manual pages checked.
- [ ] Security-sensitive routes checked.
- [ ] Version tag created.
- [ ] GitHub release created if used.

## Git Tags

Do not create tags automatically. Create a tag only after the block is complete and verified.

Create and push an annotated tag:

```bash
git tag -a v0.2.0 -m "Release v0.2.0: products module"
git push origin v0.2.0
```

List tags:

```bash
git tag
```

Delete a wrong local tag:

```bash
git tag -d v0.2.0
```

Delete a wrong remote tag:

```bash
git push origin --delete v0.2.0
```

## GitHub Releases

GitHub releases are optional for this repository. If used, create the GitHub release after the git tag exists and copy the matching release-note summary from `docs/releases/{version}.md`.
