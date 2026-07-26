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
|------|-------|------------|
| **Execution Writer (CP03)** | DeepSeek (OpenCode) | `allowed_paths` in `.opencode/agents/deepseek-executor.md`. Write-only. Never commits. |
| **Independent Reviewer** | Hermes (Copilot ACP / OpenCode) | Read-only. Output only `checkpoints/CP03/HERMES_VERIFICATION.json`. Never modify production code. |
| **Planning Authority** | ChatGPT (Web) | Draft contracts, architecture, risk register. Final semantic review. Never modify code. |
| **Workflow Remediation** | DeepSeek (OpenCode) | `write.allowed` in `.opencode/agents/workflow-remediation-executor.md`. Commit/push only with human-approved execution marker. |

## Administrator-Authorized Product Image Migration

The repository administrator may authorize Codex directly to perform a product-image
migration. When that authorization is given in the active conversation, Codex is the
execution owner for that migration and may modify the necessary application, database,
asset, script, test, and documentation files; create a dedicated migration branch; and
commit and push the resulting changes.

This override applies only to the explicitly authorized migration. It does not permit
force-push, commit amendment, rebase, reset, merge, changing checkpoint lifecycle
files, or deletion of an image before its replacement, references, and manifest have
been verified. Existing unrelated working-tree changes must be preserved unless the
administrator explicitly identifies them as in scope.

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

An administrator-authorized product-image migration is not blocked by the CP03
lifecycle or CP03 path allowlist; it must still preserve unrelated changes and follow
the safety conditions in the administrator override above.

## Forbidden Actions

- Self-approval, self-assignment, or lifecycle status change
- Commit, merge, push, deploy by DeepSeek CP03 Execution Writer (human-only)
- Workflow Remediation agent commits only with validated execution marker
- Modification of `docs/governance/`, `.github/workflows/`, `scripts/workflow/`, `checkpoints/STATE.json`, `checkpoints/STATE_HISTORY.jsonl`
- Modification of production files outside allowed_paths
- force-push, commit --amend, rebase, reset, merge by any AI agent
