# TechPilot Workflow Agents

## Source of Truth Hierarchy

1. `docs/governance/` — Authority model, role model, approval model, checkpoint lifecycle
2. `docs/workflow/WORKFLOW.md` — Multi-agent workflow state machine and role boundaries
3. `checkpoints/STATE.json` — Machine-readable current lifecycle state
4. `checkpoints/CP03/` — Checkpoint 3 working directory
5. `docs/checkpoints/ACTIVE.md` — Human canonical lifecycle state

## Active Checkpoint

- **ID**: CHECKPOINT_3
- **Title**: First-fold layout compaction and responsive hardening
- **State**: `ROADMAP_DEFINED` (see `checkpoints/STATE.json`)

## Agent Roles

| Agent | Role | Permission |
|---|---|---|
| DeepSeek (OpenCode) | Execution Writer | Write: `checkpoints/CP03/`, `app/views/news/`, `public/assets/css/news.css`, `public/assets/js/news.js`, `tests/**`, `docs/reviews/evidence/**`. Forbidden: governance, workflow scripts, CI, `checkpoints/STATE.json` |
| Hermes (Copilot ACP) | Independent Reviewer | Read-only. Write only `checkpoints/CP03/HERMES_VERIFICATION.json`. Never modify production code. |
| ChatGPT (Web) | Planning Authority / Semantic Authority | Draft contracts, architecture, risk register. Final semantic review. Never modify code directly. |

## Startup Procedure

1. `git status --short`
2. `git branch --show-current`
3. `git rev-parse HEAD`
4. Read this file (`AGENTS.md`)
5. Read `checkpoints/STATE.json`
6. Read `docs/workflow/WORKFLOW.md`
7. Read applicable agent definition in `.opencode/agents/`
8. Confirm role assignment before any write operation

## Blocking Conditions

- `checkpoints/STATE.json` lifecycle status is not valid for the intended operation
- HEAD SHA does not match expected base/candidate/reviewed SHA
- Working tree has uncommitted changes from unknown source
- Multiple agents modifying the same files concurrently
- Governance files are being modified without explicit human approval

## Forbidden Actions

- Self-approval, self-assignment, or lifecycle status change without human approval
- Commit, merge, push, deploy by AI (human-only actions)
- Modification of `docs/governance/`, `.github/workflows/`, `scripts/workflow/`, `checkpoints/STATE.json`, `checkpoints/STATE_HISTORY.jsonl`
- Modification of production files outside `allowed_paths`
