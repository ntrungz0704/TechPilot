# Multi-Agent Workflow Infrastructure

## Authority Model

```
SOURCE_OF_TRUTH=GITHUB
PRODUCTION_CODE_WRITER=DEEPSEEK
INDEPENDENT_VERIFIER=HERMES
FINAL_SEMANTIC_AUTHORITY=CHATGPT
MACHINE_ENFORCEMENT=SCRIPTS_AND_CI
```

## Role-to-Tool Mapping

| Role | Tool | Responsibility |
|---|---|---|
| Planning Authority | ChatGPT (Web) | Roadmap, architecture, risk classification, task contract, final semantic review |
| Execution Writer | OpenCode + DeepSeek | Implementation, focused testing, rework, Git operations, draft PR maintenance |
| Independent Reviewer | Copilot ACP / Hermes | Read complete diff, verify scope, rerun tests, validate handoff claims, return VERIFIED or REWORK_REQUIRED |
| Gate Authority | Scripts + CI | Lifecycle transitions, changed-file checks, forbidden-pattern scans, exit-code evidence, focused and full regression tests, approval invalidation on HEAD change |

## State Machine

```
ROADMAP_DEFINED
  -> CONTRACT_DRAFTED       (ChatGPT drafts contract, architecture, risk register)
  -> CONTRACT_APPROVED       (Human approves; base SHA frozen)

CONTRACT_APPROVED
  -> IMPLEMENTING            (DeepSeek starts implementation)

IMPLEMENTING
  -> CI_PENDING              (DeepSeek pushes; CI triggered)

CI_PENDING -> CI_FAILED      (CI scripts report failure)
CI_FAILED -> IMPLEMENTING    (DeepSeek reworks)
CI_PENDING -> CI_GREEN       (All checks pass)

CI_GREEN
  -> WAITING_FOR_HERMES      (Handoff ready for independent review)

WAITING_FOR_HERMES
  -> REWORK_REQUIRED         (Hermes finds scope/test violations)
  -> HERMES_VERIFIED         (Hermes confirms handoff)

REWORK_REQUIRED
  -> IMPLEMENTING            (DeepSeek reworks)

HERMES_VERIFIED
  -> WAITING_FOR_SEMANTIC_REVIEW  (ChatGPT final review)

WAITING_FOR_SEMANTIC_REVIEW
  -> SEMANTIC_CHANGES_REQUIRED    (ChatGPT finds semantic/architecture issues)
  -> SEMANTIC_APPROVED            (ChatGPT approves)

SEMANTIC_CHANGES_REQUIRED
  -> IMPLEMENTING                 (Back to DeepSeek)

SEMANTIC_APPROVED
  -> MERGE_READY                  (Human can merge)
  -> (Any implementation commit invalidates HERMES_VERIFIED or SEMANTIC_APPROVED)

MERGE_READY
  -> MERGED                       (Human merges PR)
  -> CLOSED                       (Checkpoint complete)

MERGED -> CLOSED
```

Any new implementation commit after `HERMES_VERIFIED` or `SEMANTIC_APPROVED` invalidates those approvals and reverts state to `IMPLEMENTING` or `CI_PENDING`.

## File Layout

```
checkpoints/
  STATE.json              Machine-readable lifecycle state
  STATE_HISTORY.jsonl     Append-only transition log
  CP03/                   Checkpoint 3 working directory
    TASK_CONTRACT.yaml
    ARCHITECTURE.md
    RISK_REGISTER.yaml
    IMPLEMENTATION_HANDOFF.json
    HERMES_VERIFICATION.json
    SEMANTIC_REVIEW.md
    evidence/             Test evidence, screenshots

docs/workflow/
  WORKFLOW.md             This file
  RISK_MODEL.md           Risk classification model
  schemas/
    task-contract.schema.json
    implementation-handoff.schema.json
    checkpoint-state.schema.json

scripts/workflow/
  validate-contract       Validate contract YAML against JSON schema
  transition-state        Apply and validate state transition
  check-changed-files     Compare git diff against allowlist/forbidden paths
  scan-forbidden-patterns Grep for blocked keywords and patterns
  verify-handoff          Validate handoff completeness against schema
  collect-test-evidence   Run required tests, capture exit codes
  verify-review-sha       Confirm HEAD matches reviewed SHA

.opencode/agents/
  deepseek-executor.md    OpenCode agent definition for DeepSeek (Writer)
  hermes-reviewer.md      OpenCode agent definition for Hermes (Reviewer)
```

## Role Boundaries

- **DeepSeek (Writer)** may: create/change files in `allowed_paths`; run tests; collect evidence; create commits; maintain draft PR
- **DeepSeek (Writer)** may NOT: change governance/CI/workflow files; modify `checkpoints/STATE.json` or `STATE_HISTORY.jsonl`; self-review; merge
- **Hermes (Reviewer)** may: read full diff; run tests; write `HERMES_VERIFICATION.json`; report `VERIFIED` or `REWORK_REQUIRED`
- **Hermes (Reviewer)** may NOT: modify production code; modify governance/workflow; self-assign writer role
- **ChatGPT** may: draft contracts; perform semantic review; never modify code
- **Scripts** may: enforce transitions; validate state; block on violations; never modify production code
- **Human** is the only authority for: plan approval, merge, release, CLOSED state, governance changes

## Integration with Existing Governance

This workflow operates under the authority model defined in `docs/governance/`. The existing `docs/checkpoints/ACTIVE.md` remains the human canonical state. `checkpoints/STATE.json` is the machine-readable mirror, updated by workflow scripts but always reflecting the same lifecycle status.
