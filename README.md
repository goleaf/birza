# Birza

Birza is a Laravel marketplace with buyer, seller, and admin surfaces built with Blade, Livewire, Eloquent, and a Vite/Tailwind asset pipeline. The project is still in active development and should use pre-`1.0.0` versions until the first stable release is verified.

## Documentation

- [Changelog](CHANGELOG.md)
- [Project documentation](docs/README.md)
- [Release notes](docs/releases/README.md)
- [Release notes template](docs/release-notes-template.md)

## Release Management

Every important project block must be traceable through the changelog, release notes, tests, docs, commits, and a version tag after verification.

Use [CHANGELOG.md](CHANGELOG.md) for all notable changes. Keep the `Unreleased` section current while work is in progress, and use the standard sections `Added`, `Changed`, `Fixed`, `Removed`, `Security`, and `Deprecated`.

Release notes live in [docs/releases](docs/releases/README.md). Major blocks such as products, orders, seller cabinet, buyer cabinet, admin moderation, payments, notifications, cart, checkout, image pipeline, multilingual system, roles and permissions, database architecture, feature tests, UI standardization, Livewire refactoring, and production hardening need a dedicated release note.

Version numbers follow semantic versioning:

- `MAJOR`: breaking changes.
- `MINOR`: new features or completed major project blocks.
- `PATCH`: bug fixes.
- Pre-release `0.x` versions are used while the project is not stable.

Suggested project progression can be adjusted based on real progress:

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

Before a release, confirm that changes are committed, `CHANGELOG.md` is updated, release notes exist, README/docs are updated where needed, migrations and seeders work from zero, the full test suite passes, the frontend build passes, manual pages are checked, security-sensitive routes are checked, the version tag is created, and a GitHub release is created if this project uses GitHub releases.

Create tags only after a release block is complete and verified:

```bash
git tag -a v0.2.0 -m "Release v0.2.0: products module"
git push origin v0.2.0
```

Useful tag commands:

```bash
git tag
git tag -d v0.2.0
git push origin --delete v0.2.0
```

Future major implementation blocks should follow this sequence:

1. Implement the feature.
2. Add or update tests.
3. Update README or docs if needed.
4. Update `CHANGELOG.md`.
5. Create release notes for the block.
6. Run tests and build.
7. Commit changes.
8. Create a version tag when the block is complete and verified.
