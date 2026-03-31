---
name: "gsd-project-default"
description: "Use in this repository when a task should default to the project-local Get Shit Done workflow. Routes the request to the correct `$gsd-*` command, keeps planning state in `.planning/`, and avoids parallel planning systems."
metadata:
  short-description: "Route repo work into the project-local GSD workflow."
---

<objective>
Route the current request into the correct project-local GSD workflow for this repository.
</objective>

<context>
- Upstream GSD skills live in `.codex/skills/gsd-*`
- Upstream GSD workflows live in `.codex/get-shit-done/workflows/`
- Project-local GSD state lives in `.planning/`
- Repository rules live in `AGENTS.md` and `CLAUDE.md`
</context>

<process>
1. Read `AGENTS.md`, `CLAUDE.md` if present, and `.planning/PROJECT.md`, `.planning/ROADMAP.md`, `.planning/STATE.md` when they exist.
2. If GSD planning state does not exist yet:
   - Existing codebase: start with `$gsd-map-codebase`
   - Then bootstrap project planning with `$gsd-new-project --auto`
3. If planning state exists, start with `$gsd-progress` unless the user already named a specific `$gsd-*` workflow.
4. Route by user intent:
   - Discovery or architecture: `$gsd-discuss-phase`
   - Planning: `$gsd-plan-phase`
   - Implementation: `$gsd-execute-phase`
   - Tiny work: `$gsd-fast` or `$gsd-quick`
   - Audit or test coverage: `$gsd-verify-work`, `$gsd-add-tests`, or `$gsd-validate-phase`
   - Shipping or PR prep: `$gsd-ship`
5. If routing is ambiguous or the planning state is messy, spawn the `project-gsd-coordinator` agent.
6. Keep all planning artifacts repo-local under `.planning/`. Do not suggest a global GSD install.
</process>

<response_contract>
- Name the recommended `$gsd-*` command explicitly.
- Briefly explain why that command is the right next step.
- Call out the next checkpoint the user should expect.
- If the task is genuinely too small for GSD, say so explicitly and handle it inline.
</response_contract>
