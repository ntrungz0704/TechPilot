# Tool Adapter Template

Adapter chỉ mô tả cách một công cụ áp dụng governance. Adapter không tạo role, scope hoặc permission.

## Identity

- Tool name: UNRESOLVED
- Adapter version: UNRESOLVED
- Confirmed by: UNASSIGNED
- Last reviewed: UNRESOLVED

## Capability matrix

    tool:
      capabilities:
        read_repository: UNRESOLVED
        edit_files: UNRESOLVED
        run_commands: UNRESOLVED
        run_tests: UNRESOLVED
        create_commits: UNRESOLVED
        push_remote: UNRESOLVED
        parallel_agents: UNRESOLVED

    repository_permissions:
      read_repository: true
      edit_allowed_paths: false
      run_tests: false
      create_commits: false
      push_remote: false
      merge: false
      deploy: false

Capability không đồng nghĩa với permission.

## Operating instructions

- Read files: UNRESOLVED
- Run terminal commands: UNRESOLVED
- Limit writes to allowed paths: UNRESOLVED
- Apply Common Session Start: docs/prompts/common/SESSION_START.md
- Apply Writer contract: docs/prompts/common/WRITER_CONTRACT.md
- Apply Reviewer contract: docs/prompts/common/REVIEWER_CONTRACT.md
- Disable/prevent auto-commit: UNRESOLVED
- Disable/prevent auto-push: UNRESOLVED
- Produce handoff/review: UNRESOLVED

## Limitations and stop conditions

- Tool limitations: UNRESOLVED
- Missing capability response: BLOCKED
- Governance conflict response: BLOCKED — GOVERNANCE_CONFLICT

Valid session:

    Repository Governance
    + Assigned Role
    + Tool Adapter
    = Valid working session

