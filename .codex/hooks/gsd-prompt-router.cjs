#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const substantivePromptPattern = /\b(add|audit|build|create|debug|execute|feature|fix|implement|integrate|migrate|phase|plan|refactor|review|roadmap|ship|verify|workflow)\b/i;
const alreadyRoutedPattern = /\$[a-z0-9-]*gsd|get-shit-done|\bgsd[-:\s]/i;
const likelyTinyPromptPattern = /^(explain|how|list|show|summarize|what|when|where|who|why)\b/i;

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
  const prompt = String(input.prompt ?? '').trim();

  if (
    prompt === '' ||
    alreadyRoutedPattern.test(prompt) ||
    (!substantivePromptPattern.test(prompt) && likelyTinyPromptPattern.test(prompt))
  ) {
    process.stdout.write(JSON.stringify({ continue: true }));

    return;
  }

  if (!substantivePromptPattern.test(prompt)) {
    process.stdout.write(JSON.stringify({ continue: true }));

    return;
  }

  const projectRoot = findGitRoot(input.cwd ?? process.cwd());

  if (!projectRoot) {
    process.stdout.write(JSON.stringify({ continue: true }));

    return;
  }

  const additionalContext = hasPlanningState(projectRoot)
    ? 'This prompt looks like substantive repository work. This repo already has project-local GSD planning state, so prefer `$gsd-progress` or `$gsd-project-default` to route into the correct `$gsd-*` workflow before implementing. Tiny one-off answers or micro-edits can still stay inline.'
    : 'This prompt looks like substantive repository work. This repo defaults to a project-local GSD workflow, so prefer `$gsd-project-default` or bootstrap with `$gsd-map-codebase` followed by `$gsd-new-project --auto` instead of creating a parallel planning system.';

  process.stdout.write(JSON.stringify({
    continue: true,
    hookSpecificOutput: {
      hookEventName: 'UserPromptSubmit',
      additionalContext,
    },
  }));
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
});
