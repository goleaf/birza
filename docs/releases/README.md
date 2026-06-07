# Release Workflow

Birza uses `CHANGELOG.md`, per-version release notes, semantic versioning, focused commits, and annotated git tags to keep major work traceable.

No local git tags or GitHub releases were found during the 2026-06-07 documentation audit. Treat existing files in this directory as release-note documentation until tags/releases are intentionally created.

## Required Files

- [CHANGELOG.md](../../CHANGELOG.md): concise project-wide change history.
- [docs/releases](.): detailed release notes for versions or major unreleased blocks.
- [docs/release-notes-template.md](../release-notes-template.md): copyable release notes template.
- [README.md](../../README.md): entry point with release-management summary.

## Semantic Versioning

- `MAJOR`: breaking changes.
- `MINOR`: new features or completed major project blocks.
- `PATCH`: bug fixes.
- `0.x`: active-development releases before first stable `1.0.0`.

Do not tag a release just because files changed. Tag only after tests, build, migrations, docs, and manual checks are verified.

## Changelog Sections

Use Keep a Changelog sections:

- `Added`
- `Changed`
- `Fixed`
- `Removed`
- `Security`
- `Deprecated`

Do not add unstructured release notes to the changelog.

## Major Block Rule

Create or update release notes after every major block:

- role architecture
- UI standardization
- database architecture
- factories and seeders
- feature tests
- security hardening
- performance optimization
- notifications
- cart and checkout
- image pipeline
- product moderation
- production hardening

Every major block release note should include:

- summary
- main changes
- database changes
- new routes or pages
- new permissions
- new tests
- breaking changes
- migration steps
- known issues
- manual verification checklist

## Release Checklist

- [ ] Working tree reviewed for unrelated changes.
- [ ] Tests pass.
- [ ] Frontend build passes.
- [ ] Migrations tested from zero in a non-production environment.
- [ ] Seeders tested.
- [ ] README updated.
- [ ] Relevant docs updated.
- [ ] `CHANGELOG.md` updated.
- [ ] Release notes created or updated.
- [ ] Manual pages checked.
- [ ] Security-sensitive routes checked.
- [ ] Production environment notes checked.
- [ ] Version tag created only after verification.
- [ ] GitHub release created only if the project is using GitHub releases.

## Tag Commands

Create and push an annotated tag:

```bash
git tag -a v0.1.0 -m "Release v0.1.0"
git push origin v0.1.0
```

List tags:

```bash
git tag
```

Delete a wrong local tag:

```bash
git tag -d v0.1.0
```

Delete a wrong remote tag:

```bash
git push origin --delete v0.1.0
```

## GitHub Releases

GitHub releases are optional. If used:

1. Create and push the git tag.
2. Create the GitHub release from that tag.
3. Copy the summary and highlights from `docs/releases/{version}.md`.
4. Link migration and production notes.

Do not claim a GitHub release exists until it is visible through GitHub or `gh release list`.
