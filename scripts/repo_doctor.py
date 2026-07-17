#!/usr/bin/env python3
"""Pre-flight repository governance doctor.

Local mode is intentionally strict: no active checkpoint and unexplained dirty
product files stop a working session.  CI mode keeps malformed or conflicting
governance blocking, but reports bootstrap operational sentinels as warnings.
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

# Running an entry script must not dirty the repository with scripts/__pycache__.
sys.dont_write_bytecode = True

from governance_lib import (
    ACTIVE_PATH,
    REQUIRED_GOVERNANCE_FILES,
    FrontMatterError,
    GovernanceError,
    Reporter,
    active_is_none,
    as_string_list,
    find_repo_root,
    git_branch,
    git_head,
    get_nested,
    is_governance_path,
    is_placeholder,
    is_real_sha,
    lifecycle,
    load_front_matter,
    matches_any,
    normalize_repo_path,
    ref_is_commit,
    resolve_commit,
    scalar_text,
    scan_conflict_markers,
    unresolved_placeholders,
    validate_active_state,
    validate_active_contract,
    validate_local_canonical_actionable,
    validate_plan_approval_and_assignments,
    validate_repo_path,
    working_tree_entries,
)


def parse_args(argv: list[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Check whether a governed work session may start")
    parser.add_argument(
        "--ci",
        action="store_true",
        help="CI mode: keep bootstrap sentinels as warnings while blocking malformed state",
    )
    parser.add_argument("--root", type=Path, help=argparse.SUPPRESS)
    return parser.parse_args(argv)


def _check_required_files(root: Path, reporter: Reporter) -> None:
    missing = [path for path in REQUIRED_GOVERNANCE_FILES if not (root / path).is_file()]
    if missing:
        reporter.blocked("REQUIRED_FILES", f"Missing governance files: {', '.join(missing)}")
    else:
        reporter.passed("REQUIRED_FILES", "Required governance files are present")


def _check_worktree(
    root: Path, active: dict, reporter: Reporter, *, authority_valid: bool
) -> None:
    entries = working_tree_entries(root)
    if not entries:
        reporter.passed("WORKTREE", "Working tree is clean")
        return
    product_or_unknown = sorted({path for _, path in entries if not is_governance_path(path)})
    governance = sorted({path for _, path in entries if is_governance_path(path)})
    if product_or_unknown:
        status = lifecycle(active)
        allowed = [item for item in as_string_list(active.get("allowed_paths")) if not is_placeholder(item)]
        forbidden = [
            item for item in as_string_list(active.get("forbidden_paths")) if not is_placeholder(item)
        ]
        permitted = (
            status in {"EXECUTING", "REWORK_REQUIRED"}
            and authority_valid
            and allowed
            and all(matches_any(path, allowed) and not matches_any(path, forbidden) for path in product_or_unknown)
        )
        if permitted:
            reporter.passed(
                "DIRTY_SOURCE_SCOPE",
                "Dirty implementation files are within the canonical execution allowlist",
            )
        else:
            reporter.blocked(
                "DIRTY_SOURCE",
                "Dirty files outside an authorized EXECUTING/REWORK_REQUIRED allowlist: "
                + ", ".join(product_or_unknown),
            )
    if governance:
        reporter.warning(
            "DIRTY_GOVERNANCE",
            "Uncommitted governance files are present: " + ", ".join(governance),
        )


def _check_allowed_path_targets(root: Path, active: dict, reporter: Reporter) -> None:
    for raw in as_string_list(active.get("allowed_paths")):
        if is_placeholder(raw):
            continue
        try:
            relative = validate_repo_path(raw)
        except GovernanceError:
            continue  # validate_active_state already reports the exact safety error
        if any(character in relative for character in "*?["):
            stable_prefix = relative.split("*", 1)[0].split("?", 1)[0].rstrip("/")
            parent = root / (stable_prefix or ".")
            if not parent.exists():
                reporter.warning("ALLOWLIST_TARGET", f"Allowlist parent does not yet exist: {raw}")
        elif not (root / relative).exists():
            reporter.warning(
                "ALLOWLIST_TARGET",
                f"Allowlisted path does not yet exist and must be created within checkpoint scope: {raw}",
            )


def _check_contract_consistency(root: Path, active: dict, reporter: Reporter) -> None:
    if active_is_none(active):
        return
    contract_value = scalar_text(active.get("contract_path"))
    if not contract_value or is_placeholder(contract_value):
        return
    try:
        contract_path = root / validate_repo_path(contract_value)
    except GovernanceError:
        return
    if not contract_path.is_file():
        return
    try:
        contract, _ = load_front_matter(contract_path)
    except FrontMatterError as exc:
        reporter.blocked("CONTRACT_FRONTMATTER", str(exc))
        return
    active_id = scalar_text(active.get("checkpoint_id"))
    contract_id = scalar_text(contract.get("checkpoint_id"))
    if contract_id and not is_placeholder(contract_id) and contract_id != active_id:
        reporter.blocked(
            "GOVERNANCE_CONFLICT",
            f"ACTIVE checkpoint {active_id} conflicts with contract checkpoint {contract_id}",
        )
    contract_status = scalar_text(
        contract.get("lifecycle_status", contract.get("lifecycle", contract.get("status", "")))
    ).upper()
    expected_contract_status = "PLAN_REVIEW" if lifecycle(active) in {
        "PLAN_APPROVED",
        "EXECUTING",
        "READY_FOR_REVIEW",
        "REWORK_REQUIRED",
        "GATE_PASS",
        "REVIEW_INVALIDATED",
        "MERGED",
        "CLOSED",
        "ROLLBACK_REQUIRED",
    } else lifecycle(active)
    if contract_status and contract_status != expected_contract_status:
        reporter.blocked(
            "GOVERNANCE_CONFLICT",
            f"Contract lifecycle {contract_status} must remain the immutable {expected_contract_status} document version",
        )


def _check_single_executing_checkpoint(root: Path, active: dict, reporter: Reporter) -> None:
    executing_ids: set[str] = set()
    if lifecycle(active) == "EXECUTING":
        executing_ids.add(scalar_text(active.get("checkpoint_id")) or ACTIVE_PATH)
    checkpoint_dir = root / "docs/checkpoints"
    if checkpoint_dir.is_dir():
        for path in checkpoint_dir.glob("*.md"):
            if path.name in {"ACTIVE.md", "TEMPLATE.md"}:
                continue
            try:
                metadata, _ = load_front_matter(path)
            except FrontMatterError as exc:
                reporter.blocked("CHECKPOINT_FRONTMATTER", str(exc))
                continue
            status = scalar_text(
                metadata.get("lifecycle_status", metadata.get("lifecycle", metadata.get("status", "")))
            ).upper()
            if status == "EXECUTING":
                executing_ids.add(scalar_text(metadata.get("checkpoint_id")) or path.as_posix())
    if len(executing_ids) > 1:
        reporter.blocked(
            "MULTIPLE_EXECUTING",
            "More than one checkpoint is EXECUTING: " + ", ".join(sorted(executing_ids)),
        )
    else:
        reporter.passed("SINGLE_WRITER", "At most one checkpoint has source-write authority")


def _check_head_compatibility(root: Path, active: dict, reporter: Reporter) -> None:
    current_head = git_head(root)
    status = lifecycle(active)
    candidate = scalar_text(active.get("candidate_commit"))
    reviewed = scalar_text(active.get("reviewed_commit"))
    if status in {"READY_FOR_REVIEW", "GATE_PASS"} and is_real_sha(candidate):
        resolved = resolve_commit(root, candidate)
        if resolved != current_head:
            reporter.blocked(
                "STALE_CANDIDATE",
                f"Local HEAD {current_head} is not candidate_commit {resolved or candidate}",
            )
    if status == "GATE_PASS" and is_real_sha(reviewed):
        resolved = resolve_commit(root, reviewed)
        if resolved != current_head:
            reporter.blocked(
                "STALE_REVIEW",
                f"Local HEAD {current_head} differs from reviewed_commit {resolved or reviewed}",
            )


def main(argv: list[str] | None = None) -> int:
    args = parse_args(argv or sys.argv[1:])
    reporter = Reporter()
    try:
        root = find_repo_root(args.root)
    except GovernanceError as exc:
        reporter.blocked("GIT_REPOSITORY", str(exc))
        reporter.render()
        return reporter.exit_code

    reporter.passed("GIT_REPOSITORY", f"Repository root: {root}")
    try:
        head = git_head(root)
        branch = git_branch(root)
    except GovernanceError as exc:
        reporter.blocked("GIT_STATE", str(exc))
        reporter.render()
        return reporter.exit_code
    reporter.passed("GIT_HEAD", head)
    if branch:
        reporter.passed("GIT_BRANCH", branch)
    elif args.ci:
        reporter.warning("GIT_BRANCH", "CI checkout uses detached HEAD")
    else:
        reporter.blocked("GIT_BRANCH", "Detached HEAD is not allowed for a local work session")

    _check_required_files(root, reporter)

    active_path = root / ACTIVE_PATH
    if not active_path.is_file():
        reporter.blocked("ACTIVE", f"Missing {ACTIVE_PATH}")
        conflicts = scan_conflict_markers(root)
        if conflicts:
            reporter.blocked("CONFLICT_MARKERS", ", ".join(conflicts))
        reporter.render()
        return reporter.exit_code

    try:
        active, _ = load_front_matter(active_path)
    except FrontMatterError as exc:
        reporter.blocked("ACTIVE_FRONTMATTER", str(exc))
        reporter.render()
        return reporter.exit_code
    reporter.passed("ACTIVE_FRONTMATTER", "ACTIVE.md front matter parsed safely")
    validate_active_state(active, root, reporter, ci=args.ci)

    validate_local_canonical_actionable(root, active, reporter)
    contract = validate_active_contract(root, active, reporter, revision=None)
    plan = validate_plan_approval_and_assignments(root, active, reporter, revision=None)
    authority_valid = contract is not None and plan is not None and not reporter.has_blocks
    _check_worktree(root, active, reporter, authority_valid=authority_valid)

    if active_is_none(active):
        message = "No checkpoint is active; Human planning and assignment are required"
        reporter.warning("NO_ACTIVE_CHECKPOINT", message) if args.ci else reporter.blocked(
            "NO_ACTIVE_CHECKPOINT", message
        )

    placeholders = unresolved_placeholders(active)
    if placeholders:
        reporter.blocked(
            "BLOCKING_PLACEHOLDERS",
            "Actionable lifecycle contains unresolved fields: " + ", ".join(placeholders),
        )

    _check_allowed_path_targets(root, active, reporter)
    _check_contract_consistency(root, active, reporter)
    _check_single_executing_checkpoint(root, active, reporter)
    _check_head_compatibility(root, active, reporter)

    base = scalar_text(active.get("base_commit"))
    if is_real_sha(base) and not ref_is_commit(root, base):
        reporter.blocked("BASE_SHA", f"base_commit is not present locally: {base}")

    last_updated = scalar_text(active.get("last_updated"))
    if not last_updated or is_placeholder(last_updated):
        reporter.warning("LAST_UPDATED", "ACTIVE.md has no concrete last_updated value")

    conflicts = scan_conflict_markers(root)
    if conflicts:
        reporter.blocked("CONFLICT_MARKERS", "Merge conflict markers found at " + ", ".join(conflicts))
    else:
        reporter.passed("CONFLICT_MARKERS", "No unresolved merge conflict markers found")

    codeowners = root / ".github/CODEOWNERS"
    if codeowners.is_file() and "@REPLACE_WITH_PROJECT_OWNER" in codeowners.read_text(
        encoding="utf-8-sig"
    ):
        reporter.warning(
            "OWNER_HANDLE",
            "CODEOWNERS still requires the Human Project Owner's GitHub handle",
        )

    success_message = None
    if args.ci and active_is_none(active):
        success_message = "PASS — Structural validation passed; execution is not authorized"
    reporter.render(success_message=success_message)
    return reporter.exit_code


if __name__ == "__main__":
    raise SystemExit(main())
