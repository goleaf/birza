#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

async function readInput() {
  const chunks = [];

  for await (const chunk of process.stdin) {
    chunks.push(chunk);
  }

  const raw = chunks.join('').trim();

  if (raw === '') {
    return {};
  }

  try {
    return JSON.parse(raw);
  } catch {
    return {};
  }
}

function findGitRoot(startDirectory) {
  let currentDirectory = path.resolve(startDirectory);

  while (true) {
    if (fs.existsSync(path.join(currentDirectory, '.git'))) {
      return currentDirectory;
    }

    const parentDirectory = path.dirname(currentDirectory);

    if (parentDirectory === currentDirectory) {
      return null;
    }

    currentDirectory = parentDirectory;
  }
}

function hasPlanningState(projectRoot) {
  return [
    '.planning/PROJECT.md',
    '.planning/ROADMAP.md',
    '.planning/STATE.md',
  ].some((relativePath) => fs.existsSync(path.join(projectRoot, relativePath)));
}

async function main() {
  const input = await readInput();
  const projectRoot = findGitRoot(input.cwd ?? process.cwd());

  if (!projectRoot) {
    process.stdout.write(JSON.stringify({ continue: true }));

    return;
  }

  const additionalContext = hasPlanningState(projectRoot)
    ? 'Project-local GSD is installed and `.planning/` already exists in this repository. For multi-step work, prefer `$gsd-progress` or `$gsd-project-default` to route into the correct `$gsd-*` workflow before creating ad hoc plans. Keep planning state in `.planning/` and follow the repository instructions from `AGENTS.md` / `CLAUDE.md`.'
    : 'Project-local GSD is installed in this repository but planning state is not initialized yet. For multi-step work, prefer the local GSD workflow: run `$gsd-map-codebase` for the existing codebase, then `$gsd-new-project --auto`. Use `$gsd-project-default` when you need repo-specific routing.';

  process.stdout.write(JSON.stringify({
    continue: true,
    hookSpecificOutput: {
      hookEventName: 'SessionStart',
      additionalContext,
    },
  }));
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
});
