# TechPilot Workflow Agents

## Source of Truth Hierarchy

1. `docs/governance/` — Authority model, approval model, checkpoint lifecycle
2. `docs/workflow/WORKFLOW.md` — Multi-agent workflow and role boundaries
3. `checkpoints/STATE.json` — Machine-readable lifecycle state (canonical)
4. `checkpoints/CP03/` — Checkpoint 3 working directory
5. `docs/checkpoints/ACTIVE.md` — Human canonical lifecycle state (mirror)

## Active Checkpoint

| Field | Value |
|---|---|
| **ID** | CHECKPOINT_3 |
| **Title** | First-fold layout compaction and responsive hardening |
| **State** | ROADMAP_DEFINED |
| **Route** | Homepage (1366x768, scrollY=0) |

## Role Assignment

| Role | Agent | Permission |
|---|---|---|
| **Execution Writer** | DeepSeek (OpenCode) | `allowed_paths` in `.opencode/agents/deepseek-executor.md`. Forbidden: governance, CI, STATE.json |
| **Independent Reviewer** | Hermes (Copilot ACP / OpenCode) | Read-only. Output only `checkpoints/CP03/HERMES_VERIFICATION.json`. Never modify production code. |
| **Planning Authority** | ChatGPT (Web) | Draft contracts, architecture, risk register. Final semantic review. Never modify code. |

## Startup Procedure

Every session MUST run:

```
git status --short
git branch --show-current
git rev-parse HEAD
cat AGENTS.md
cat checkpoints/STATE.json
cat docs/workflow/WORKFLOW.md
cat .opencode/agents/<applicable-agent>.md
```

## Blocking Conditions

- STATE.json lifecycle does not permit the intended operation
- HEAD SHA does not match expected base/candidate/reviewed SHA
- Working tree has uncommitted changes from unknown source
- Multiple agents modifying the same files concurrently
- Governance files being modified without explicit human approval

## Forbidden Actions

- Self-approval, self-assignment, or lifecycle status change
- Commit, merge, push, deploy by AI (human-only)
- Modification of `docs/governance/`, `.github/workflows/`, `scripts/workflow/`, `checkpoints/STATE.json`, `checkpoints/STATE_HISTORY.jsonl`
- Modification of production files outside allowed_paths
