# Risk Classification Model

## Severity Levels

| Level | Definition | Required Action |
|---|---|---|
| **Critical** | Breaks core functionality, data loss, security vulnerability, production outage | Stop all work. Escalate to Human Project Owner immediately. |
| **High** | Major feature broken, significant UX degradation, regression in approved scope | Block checkpoint. Rework required before any merge. |
| **Medium** | Minor UX issue, edge case uncovered, non-blocking scope violation | Document in handoff. May proceed but must fix before GATE_PASS. |
| **Low** | Cosmetic, documentation gap, non-functional preference | Note as known limitation. May defer to future checkpoint. |

## Risk Categories

| Category | Examples | Typical Severity |
|---|---|---|
| **SCOPE_CREEP** | Changes outside `allowed_paths`, unapproved features, extra dependencies | High / Critical |
| **GOVERNANCE_VIOLATION** | Agent self-approval, commit without permission, modifying governance files | Critical |
| **ARCHITECTURE_DRIFT** | MVC violation, wrong layer, breaking existing convention | Medium / High |
| **TEST_REGRESSION** | Existing tests fail, required tests not run, insufficient coverage | High |
| **DATA_INTEGRITY** | Database migration issues, data loss, schema mismatch | Critical |
| **ACCESSIBILITY** | Keyboard trap, missing ARIA, broken screen reader flow | Medium |
| **RESPONSIVE_BREAKAGE** | Layout broken at a viewport, overflow, content hidden | Medium / High |
| **PERFORMANCE** | Unnecessary re-renders, large asset sizes, blocking JS | Low / Medium |
| **EVIDENCE_GAP** | Missing test output, unverifiable claims, handoff not matching diff | High |
| **SHA_MISMATCH** | HEAD does not match reviewed/candidate SHA | Critical |

## Risk Register Format (per checkpoint)

```yaml
risks:
  - id: CP03-RISK-001
    category: RESPONSIVE_BREAKAGE
    description: Category drawer may not reset correctly across all viewport transitions
    severity: medium
    likelihood: probable
    mitigation: Add matchMedia listener tests for each breakpoint boundary
    owner: deepseek
    status: open
```
