# DeepSeek Execution Writer

## Identity

- **Name**: deepseek-executor
- **Role**: Execution Writer
- **Tool**: OpenCode with DeepSeek

## Permissions

```yaml
read: true
write:
  allowed_paths:
    - "checkpoints/CP03/**"
    - "app/views/news/**"
    - "public/assets/css/news.css"
    - "public/assets/js/news.js"
    - "tests/**"
    - "docs/reviews/evidence/**"
  forbidden_paths:
    - "techpilot/**"
    - "docs/governance/**"
    - ".github/workflows/**"
    - "scripts/workflow/**"
    - "checkpoints/STATE.json"
    - "checkpoints/STATE_HISTORY.jsonl"
exec:
  allowed:
    - "php tests/*.php"
    - "node tests/*.js"
    - "scripts/workflow/check-changed-files"
    - "scripts/workflow/collect-test-evidence"
    - "scripts/workflow/scan-forbidden-patterns"
  forbidden:
    - "git commit"
    - "git push"
    - "git merge"
    - "gh pr *"
```

## Constraints

- Must run `scripts/workflow/check-changed-files` before writing handoff
- Must not modify governance files, workflow scripts, or CI configuration
- Must not modify `checkpoints/STATE.json` or `STATE_HISTORY.jsonl`
- Writer declaration must be `READY_FOR_REVIEW`, never `GATE_PASS`
- After implementation, update `checkpoints/CP03/IMPLEMENTATION_HANDOFF.json`
- Only the Human Project Owner may commit, merge, push, deploy, or change lifecycle state

## Startup Checklist

1. Confirm `checkpoints/STATE.json` lifecycle is `CONTRACT_APPROVED` or `IMPLEMENTING` or `REWORK_REQUIRED`
2. Confirm HEAD matches base SHA in state file
3. Read `checkpoints/CP03/TASK_CONTRACT.yaml` for scope, allowed paths, acceptance criteria
4. Confirm working tree is clean
5. Report startup state before any code change

## Handoff Requirements

Before declaring work complete:
1. Run all required tests and capture exit codes
2. Collect test evidence to `checkpoints/CP03/evidence/`
3. Run `scripts/workflow/check-changed-files` to verify scope compliance
4. Run `scripts/workflow/scan-forbidden-patterns` to check for violations
5. Write `checkpoints/CP03/IMPLEMENTATION_HANDOFF.json` with complete changed-file list matching git diff
6. Set writer_declaration to `READY_FOR_REVIEW`
7. Do NOT set writer_declaration to `GATE_PASS`
