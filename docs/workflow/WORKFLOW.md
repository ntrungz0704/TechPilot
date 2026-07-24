# Multi-Agent Workflow Infrastructure

## Authority Chain

```
SOURCE_OF_TRUTH = GITHUB
PRODUCTION_CODE_WRITER = DEEPSEEK
INDEPENDENT_VERIFIER = HERMES
FINAL_SEMANTIC_AUTHORITY = CHATGPT
MACHINE_ENFORCEMENT = SCRIPTS_AND_CI
```

## Role-to-Tool Mapping

| Role | Tool | Key Files |
|---|---|---|
| Planning Authority | ChatGPT (Web) | `checkpoints/CP03/TASK_CONTRACT.yaml`, `ARCHITECTURE.md`, `RISK_REGISTER.yaml`, `SEMANTIC_REVIEW.md` |
| Execution Writer | DeepSeek (OpenCode) | `checkpoints/CP03/IMPLEMENTATION_HANDOFF.json` |
| Independent Reviewer | Hermes (ACP / OpenCode) | `checkpoints/CP03/HERMES_VERIFICATION.json` |
| Gate Automation | Scripts + CI | `scripts/workflow/*`, `.github/workflows/news-module-ci.yml` |

## State Machine

```
ROADMAP_DEFINED          Planning Authority drafts contract, architecture, risk
  -> CONTRACT_DRAFTED
  -> CONTRACT_APPROVED   Human approves; base_sha frozen

CONTRACT_APPROVED
  -> IMPLEMENTING        Writer implements on feature branch

IMPLEMENTING
  -> CI_PENDING          Writer pushes; CI triggered
  -> CI_FAILED           CI gate fails
  -> CI_GREEN            All required tests + gates pass

CI_GREEN
  -> WAITING_FOR_HERMES  Handoff ready for independent review

WAITING_FOR_HERMES
  -> REWORK_REQUIRED     Hermes finds violations
  -> HERMES_VERIFIED     Hermes confirms handoff

HERMES_VERIFIED
  -> WAITING_FOR_SEMANTIC_REVIEW   ChatGPT final review
    -> SEMANTIC_CHANGES_REQUIRED   Back to Writer
    -> SEMANTIC_APPROVED           ChatGPT approves

SEMANTIC_APPROVED
  -> MERGE_READY         Human may merge
  -> MERGED              Human merges
  -> CLOSED              Checkpoint complete
```

**Approval invalidation**: Any implementation commit after HERMES_VERIFIED or SEMANTIC_APPROVED resets to IMPLEMENTING and clears reviewed_sha/candidate_sha.

## Scripts

| Script | Purpose | Expected Failure Mode |
|---|---|---|
| `validate-contract` | Parse and validate contract YAML against schema | Fail if parser missing, YAML malformed, or schema validation fails |
| `transition-state` | Validate and apply state machine transition | Fail if transition invalid, fail to write STATE.json, fail to append history |
| `check-changed-files` | Enforce allowed_paths and forbidden_paths against full diff | Fail if any changed file outside allowed or inside forbidden |
| `scan-forbidden-patterns` | Scan diff for blocked keywords and patterns | Fail if any forbidden pattern found |
| `verify-handoff` | Validate handoff matches git diff and SHA constraints | Fail if changed_files mismatch, candidate_sha mismatch, or writer_declaration invalid |
| `collect-test-evidence` | Run required_tests from contract, capture exit codes | Fail if required test fails or missing |
| `verify-review-sha` | Confirm HEAD matches reviewed SHA; invalidate on mismatch | Fail if HEAD does not match reviewed_sha at review-required lifecycle |

## Workflow Layout

```
checkpoints/STATE.json              Machine-readable lifecycle
checkpoints/STATE_HISTORY.jsonl     Append-only transition log
checkpoints/CP03/                   Checkpoint 3 working directory
docs/workflow/                      Workflow documentation + schemas
scripts/workflow/                   Fail-closed lifecycle scripts
tests/workflow/                     Positive and negative regression tests
.opencode/agents/                   Machine-readable agent definitions
.github/workflows/news-module-ci.yml CI with checkpoint-gate job
```

## Fail-Closed Principle

All workflow scripts must:
- Exit nonzero on any validation failure
- Exit nonzero if required tool (php, jq, node) is missing
- NOT accept placeholder data as valid
- NOT silently skip enforcement steps
- Log clear failure reason to stdout/stderr
