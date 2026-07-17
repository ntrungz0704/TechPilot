#!/usr/bin/env python3
"""Validate governance contracts and changed-file scope locally or in CI."""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path
from typing import Any

# Running an entry script must not dirty the repository with scripts/__pycache__.
sys.dont_write_bytecode = True

from governance_lib import (
    ACTIVE_PATH,
    REQUIRED_GOVERNANCE_FILES,
    FrontMatterError,
    GovernanceError,
    Reporter,
    active_is_none,
    ambiguous_approval_lines,
    as_string_list,
    changed_files_between,
    extract_markdown_list,
    extract_reviewed_sha,
    find_repo_root,
    get_first,
    internal_link_issues,
    is_governance_path,
    is_placeholder,
    is_protected_governance_path,
    is_real_sha,
    lifecycle,
    load_decision_record,
    load_front_matter,
    matches_any,
    normalize_repo_path,
    owner_approval_reference,
    parse_front_matter_text,
    path_exists_at_revision,
    read_at_revision,
    ref_is_commit,
    resolve_commit,
    run_git,
    scalar_text,
    scan_conflict_markers,
    unresolved_placeholders,
    validate_active_state,
    validate_active_contract,
    validate_decision_record,
    validate_local_canonical_actionable,
    validate_plan_approval_and_assignments,
    validate_repo_path,
    validate_transition,
    working_tree_entries,
    working_tree_files,
)


def parse_args(argv: list[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Validate governance state and changed files against the active checkpoint"
    )
    parser.add_argument("--base", help="Base commit for CI/range validation")
    parser.add_argument("--head", help="Head commit for CI/range validation")
    parser.add_argument("--root", type=Path, help=argparse.SUPPRESS)
    args = parser.parse_args(argv)
    if bool(args.base) != bool(args.head):
        parser.error("--base and --head must be supplied together")
    return args


def _load_active(root: Path, revision: str | None) -> tuple[dict[str, Any], str]:
    if revision:
        text = read_at_revision(root, revision, ACTIVE_PATH)
        if text is None:
            raise FrontMatterError(f"{revision} does not contain {ACTIVE_PATH}")
        return parse_front_matter_text(text, f"{revision}:{ACTIVE_PATH}")
    return load_front_matter(root / ACTIVE_PATH)


def _check_required_files(root: Path, reporter: Reporter, revision: str | None) -> None:
    if revision:
        missing = [
            path for path in REQUIRED_GOVERNANCE_FILES if not path_exists_at_revision(root, revision, path)
        ]
    else:
        missing = [path for path in REQUIRED_GOVERNANCE_FILES if not (root / path).is_file()]
    if missing:
        reporter.blocked("REQUIRED_FILES", "Missing governance files: " + ", ".join(missing))
    else:
        reporter.passed("REQUIRED_FILES", "Required governance files are present")


def _check_transition(
    root: Path,
    active: dict[str, Any],
    reporter: Reporter,
    base: str | None,
) -> None:
    if not base:
        reporter.warning("LIFECYCLE_TRANSITION", "Working-tree mode has no historical transition range")
        return
    old_text = read_at_revision(root, base, ACTIVE_PATH)
    if old_text is None:
        reporter.passed("LIFECYCLE_TRANSITION", "Initial governance bootstrap introduces ACTIVE.md")
        return
    try:
        old_active, _ = parse_front_matter_text(old_text, f"{base}:{ACTIVE_PATH}")
    except FrontMatterError as exc:
        reporter.blocked("BASE_ACTIVE", str(exc))
        return
    old_id = scalar_text(old_active.get("checkpoint_id"))
    new_id = scalar_text(active.get("checkpoint_id"))
    old_status = lifecycle(old_active)
    new_status = lifecycle(active)
    if old_id == new_id:
        if validate_transition(old_status, new_status):
            reporter.passed(
                "LIFECYCLE_TRANSITION", f"Allowed transition: {old_status} -> {new_status}"
            )
        else:
            reporter.blocked(
                "LIFECYCLE_TRANSITION", f"Forbidden transition: {old_status} -> {new_status}"
            )
        return
    if old_status == "CLOSED" and new_status in {"DRAFT", "PLAN_REVIEW"}:
        reporter.passed("CHECKPOINT_ROTATION", f"Closed {old_id}; opened planning state for {new_id}")
    elif old_id == "NO_ACTIVE_CHECKPOINT" and new_status == "DRAFT":
        reporter.passed("CHECKPOINT_ROTATION", f"New checkpoint draft recorded: {new_id}")
    elif new_id == "NO_ACTIVE_CHECKPOINT" and old_status in {"MERGED", "CLOSED"}:
        reporter.passed("CHECKPOINT_ROTATION", f"Checkpoint {old_id} returned to no-active state")
    else:
        reporter.blocked(
            "CHECKPOINT_ROTATION",
            f"Checkpoint changed from {old_id}/{old_status} to {new_id}/{new_status} without a valid close/open sequence",
        )


def _check_changed_scope(
    active: dict[str, Any],
    changed: list[str],
    reporter: Reporter,
    *,
    authority_valid: bool,
) -> None:
    if not changed:
        reporter.warning("CHANGED_FILES", "No changed files were detected")
        return
    reporter.passed("CHANGED_FILES", f"Validating {len(changed)} changed file(s)")
    status = lifecycle(active)
    if active_is_none(active):
        for path in changed:
            if path == "techpilot" or path.startswith("techpilot/"):
                reporter.blocked(
                    "NO_ACTIVE_SOURCE_CHANGE",
                    f"NO_ACTIVE_CHECKPOINT cannot change product path: {path}",
                )
            elif not is_governance_path(path):
                reporter.blocked(
                    "BOOTSTRAP_SCOPE",
                    f"NO_ACTIVE_CHECKPOINT allows governance-only changes, not: {path}",
                )
        return

    product_changes = [
        path for path in changed if path == "techpilot" or path.startswith("techpilot/")
    ]
    product_capable = status in {
        "EXECUTING",
        "READY_FOR_REVIEW",
        "REWORK_REQUIRED",
        "GATE_PASS",
        "REVIEW_INVALIDATED",
        "MERGED",
        "CLOSED",
        "ROLLBACK_REQUIRED",
    }
    if product_changes and (not product_capable or not authority_valid):
        reporter.blocked(
            "PRODUCT_LIFECYCLE",
            f"Lifecycle {status} cannot carry product changes without canonical approvals: "
            + ", ".join(product_changes),
        )

    allowed = [item for item in as_string_list(active.get("allowed_paths")) if not is_placeholder(item)]
    forbidden = [
        item for item in as_string_list(active.get("forbidden_paths")) if not is_placeholder(item)
    ]
    if not allowed:
        reporter.blocked("ALLOWLIST", "Active checkpoint has no usable allowed_paths")
        return

    operational_records: set[str] = {ACTIVE_PATH}
    for field in (
        "contract_path",
        "plan_approval_ref",
        "governance_change_approval_ref",
        "handoff_path",
        "review_path",
        "evidence_path",
    ):
        value = scalar_text(active.get(field))
        if value and not is_placeholder(value):
            try:
                operational_records.add(validate_repo_path(value))
            except GovernanceError:
                pass
    for assignment_key in ("assigned_writer", "assigned_reviewer"):
        assignment = active.get(assignment_key)
        if not isinstance(assignment, dict):
            continue
        for field in ("approval_record", "role_assignment_ref"):
            value = scalar_text(assignment.get(field))
            if value and not is_placeholder(value):
                try:
                    operational_records.add(validate_repo_path(value))
                except GovernanceError:
                    pass

    def is_operational_record(path: str) -> bool:
        return any(
            path == record or path.startswith(record.rstrip("/") + "/")
            for record in operational_records
        )

    for path in changed:
        if is_operational_record(path):
            continue
        if matches_any(path, forbidden):
            reporter.blocked("FORBIDDEN_CHANGE", f"Changed file matches forbidden_paths: {path}")
        elif not matches_any(path, allowed):
            reporter.blocked("OUTSIDE_ALLOWLIST", f"Changed file is outside allowed_paths: {path}")


def _document_text(root: Path, active: dict[str, Any], field: str) -> tuple[str, str] | None:
    raw = scalar_text(active.get(field))
    if not raw or is_placeholder(raw):
        return None
    try:
        relative = validate_repo_path(raw)
    except GovernanceError:
        return None
    path = root / relative
    if not path.is_file():
        return None
    try:
        return relative, path.read_text(encoding="utf-8-sig")
    except OSError:
        return None


def _check_handoff(
    root: Path,
    active: dict[str, Any],
    changed: list[str],
    reporter: Reporter,
) -> None:
    if lifecycle(active) not in {
        "READY_FOR_REVIEW",
        "GATE_PASS",
        "REWORK_REQUIRED",
        "REVIEW_INVALIDATED",
        "MERGED",
        "CLOSED",
    }:
        return
    document = _document_text(root, active, "handoff_path")
    if document is None:
        reporter.blocked("HANDOFF", "Lifecycle requires an existing handoff_path")
        return
    relative, text = document
    listed: list[str] = []
    try:
        metadata, _ = parse_front_matter_text(text, relative)
    except FrontMatterError:
        metadata = {}
    listed.extend(
        normalize_repo_path(item)
        for item in as_string_list(
            get_first(metadata, "changed_files", "full_changed_file_list", "implementation.changed_files")
        )
        if not is_placeholder(item)
    )
    listed.extend(extract_markdown_list(text, ("changed files", "changed-file", "file đã thay đổi")))
    listed_set = {item for item in listed if item}

    implementation_changed = list(changed)
    base = scalar_text(active.get("base_commit"))
    candidate = scalar_text(active.get("candidate_commit"))
    if is_real_sha(base) and is_real_sha(candidate):
        if ref_is_commit(root, base) and ref_is_commit(root, candidate):
            implementation_changed = changed_files_between(root, base, candidate)

    operational = {relative, ACTIVE_PATH}
    review_path = scalar_text(active.get("review_path"))
    if review_path and not is_placeholder(review_path):
        operational.add(normalize_repo_path(review_path))
    evidence_path = scalar_text(active.get("evidence_path"))
    missing = []
    for path in implementation_changed:
        if path in operational:
            continue
        if evidence_path and not is_placeholder(evidence_path):
            evidence_prefix = normalize_repo_path(evidence_path).rstrip("/")
            if path == evidence_prefix or path.startswith(evidence_prefix + "/"):
                continue
        if path not in listed_set:
            missing.append(path)
    if missing:
        reporter.blocked(
            "HANDOFF_DIFF",
            "Handoff does not list changed files: " + ", ".join(sorted(missing)),
        )
    else:
        reporter.passed("HANDOFF_DIFF", "Handoff changed-file record matches implementation diff")


def _check_evidence(root: Path, active: dict[str, Any], reporter: Reporter) -> None:
    if lifecycle(active) not in {
        "READY_FOR_REVIEW",
        "GATE_PASS",
        "REWORK_REQUIRED",
        "REVIEW_INVALIDATED",
        "MERGED",
        "CLOSED",
    }:
        return
    raw = scalar_text(active.get("evidence_path"))
    if not raw or is_placeholder(raw):
        reporter.blocked("EVIDENCE", "Lifecycle requires evidence_path")
        return
    try:
        relative = validate_repo_path(raw)
    except GovernanceError as exc:
        reporter.blocked("EVIDENCE", str(exc))
        return
    path = root / relative
    if not path.exists():
        reporter.blocked("EVIDENCE", f"Evidence path does not exist: {relative}")
    elif path.is_dir() and not any(path.iterdir()):
        reporter.blocked("EVIDENCE", f"Evidence directory is empty: {relative}")
    else:
        reporter.passed("EVIDENCE", f"Evidence exists at {relative}")


def _review_decision(text: str) -> str | None:
    try:
        metadata, _ = parse_front_matter_text(text)
    except FrontMatterError:
        metadata = {}
    decision = scalar_text(get_first(metadata, "final_decision", "decision", "review.decision"))
    if decision:
        return decision.upper()
    match = re.search(
        r"(?im)^\s*(?:[-*]\s*)?(?:\*\*)?final decision(?:\*\*)?\s*:\s*`?([A-Z_]+)`?\s*$",
        text,
    )
    return match.group(1).upper() if match else None


def _check_review(root: Path, active: dict[str, Any], reporter: Reporter) -> None:
    if lifecycle(active) not in {"GATE_PASS", "REVIEW_INVALIDATED", "MERGED", "CLOSED"}:
        return
    document = _document_text(root, active, "review_path")
    if document is None:
        reporter.blocked("REVIEW", "Lifecycle requires an existing review_path")
        return
    relative, text = document
    reviewed = extract_reviewed_sha(text)
    active_reviewed = scalar_text(active.get("reviewed_commit"))
    if not reviewed:
        reporter.blocked("REVIEW_SHA", f"Review does not record reviewed_commit: {relative}")
    elif resolve_commit(root, reviewed) != resolve_commit(root, active_reviewed):
        reporter.blocked("REVIEW_SHA", "Review SHA does not match ACTIVE reviewed_commit")
    decision = _review_decision(text)
    if lifecycle(active) in {"GATE_PASS", "MERGED", "CLOSED"} and decision != "GATE_PASS":
        reporter.blocked("REVIEW_DECISION", "Gate lifecycle requires final_decision GATE_PASS")
    elif decision and decision not in {"GATE_PASS", "REWORK_REQUIRED", "BLOCKED"}:
        reporter.blocked("REVIEW_DECISION", f"Invalid Reviewer decision: {decision}")
    else:
        reporter.passed("REVIEW", "Independent review is bound to the recorded commit")


def _check_review_invalidation(
    root: Path,
    active: dict[str, Any],
    reporter: Reporter,
    head: str | None,
) -> None:
    if lifecycle(active) not in {"GATE_PASS", "MERGED", "CLOSED"}:
        return
    reviewed = scalar_text(active.get("reviewed_commit"))
    if not is_real_sha(reviewed) or not ref_is_commit(root, reviewed):
        return
    end = head or "HEAD"
    changed_after_review = changed_files_between(root, reviewed, end)
    if head is None:
        changed_after_review = sorted(set(changed_after_review).union(working_tree_files(root)))
    exempt_exact = {ACTIVE_PATH}
    review_path = scalar_text(active.get("review_path"))
    if review_path and not is_placeholder(review_path):
        exempt_exact.add(normalize_repo_path(review_path))
    evidence_path = scalar_text(active.get("evidence_path"))
    evidence_prefix = ""
    if evidence_path and not is_placeholder(evidence_path):
        evidence_prefix = normalize_repo_path(evidence_path).rstrip("/")
    review_sensitive = []
    for path in changed_after_review:
        if path in exempt_exact:
            continue
        if evidence_prefix and (path == evidence_prefix or path.startswith(evidence_prefix + "/")):
            continue
        review_sensitive.append(path)
    if review_sensitive:
        reporter.blocked(
            "REVIEW_INVALIDATION",
            "Review-sensitive content changed after reviewed_commit; lifecycle must be REVIEW_INVALIDATED: "
            + ", ".join(review_sensitive),
        )
    else:
        reporter.passed(
            "REVIEW_INVALIDATION",
            "Only exact gate metadata, review, or evidence paths changed after review",
        )


def _check_protected_governance(
    root: Path,
    active: dict[str, Any],
    changed: list[str],
    reporter: Reporter,
    base: str | None,
    revision: str | None,
) -> None:
    protected = [path for path in changed if is_protected_governance_path(path)]
    if not protected:
        return
    canonical_base = base or "HEAD"
    if not path_exists_at_revision(root, canonical_base, ACTIVE_PATH):
        reporter.passed(
            "GOVERNANCE_BOOTSTRAP",
            "Canonical base has no ACTIVE.md; one-time governance bootstrap is allowed",
        )
        return
    loaded = load_decision_record(
        root,
        active.get("governance_change_approval_ref"),
        reporter,
        revision=revision,
        label="GOVERNANCE_CHANGE_APPROVAL",
    )
    if loaded is None:
        reporter.blocked(
            "PROTECTED_GOVERNANCE",
            "Canonical governance already exists; every protected change needs a validated governance approval",
        )
        return
    reference, record, _ = loaded
    valid = validate_decision_record(
        root,
        record,
        active,
        reporter,
        code="GOVERNANCE_CHANGE_APPROVAL",
        expected_decision="GOVERNANCE_CHANGE_APPROVED",
        expected_scope="GOVERNANCE",
        expected_approver=active.get("governance_change_approved_by"),
        revision=revision,
    )
    approved_paths = as_string_list(record.get("approved_paths"))
    uncovered = [path for path in protected if not matches_any(path, approved_paths)]
    if not approved_paths or uncovered:
        reporter.blocked(
            "GOVERNANCE_CHANGE_APPROVAL",
            "approved_paths do not cover protected changes: " + ", ".join(uncovered or protected),
        )
        valid = False
    if valid:
        reporter.passed("PROTECTED_GOVERNANCE", f"Validated governance approval: {reference}")


def _check_approval_semantics(root: Path, reporter: Reporter) -> None:
    findings: list[str] = []
    candidates = [
        path
        for path in REQUIRED_GOVERNANCE_FILES
        if path.endswith((".md", ".yml", ".yaml")) and (root / path).is_file()
    ]
    for relative in candidates:
        try:
            text = (root / relative).read_text(encoding="utf-8-sig")
        except OSError:
            continue
        findings.extend(ambiguous_approval_lines(text, relative))
    if findings:
        reporter.blocked(
            "AMBIGUOUS_APPROVAL",
            "Bare 'Approved' is not a canonical decision: " + ", ".join(findings),
        )
    else:
        reporter.passed("APPROVAL_SEMANTICS", "No ambiguous bare approval record found")


def main(argv: list[str] | None = None) -> int:
    args = parse_args(argv or sys.argv[1:])
    reporter = Reporter()
    try:
        root = find_repo_root(args.root)
    except GovernanceError as exc:
        reporter.blocked("GIT_REPOSITORY", str(exc))
        reporter.render()
        return reporter.exit_code

    if args.base and args.head:
        if not ref_is_commit(root, args.base):
            reporter.blocked("BASE_REF", f"Not a commit: {args.base}")
        if not ref_is_commit(root, args.head):
            reporter.blocked("HEAD_REF", f"Not a commit: {args.head}")
        if reporter.has_blocks:
            reporter.render()
            return reporter.exit_code
        changed = changed_files_between(root, args.base, args.head)
        active_revision = args.head
        reporter.passed("VALIDATION_MODE", f"Commit range {args.base}..{args.head}")
    else:
        changed = working_tree_files(root)
        active_revision = None
        reporter.passed("VALIDATION_MODE", "Working tree, including untracked files")

    _check_required_files(root, reporter, active_revision)
    try:
        active, _ = _load_active(root, active_revision)
    except FrontMatterError as exc:
        reporter.blocked("ACTIVE_FRONTMATTER", str(exc))
        reporter.render()
        return reporter.exit_code
    reporter.passed("ACTIVE_FRONTMATTER", "ACTIVE.md front matter parsed safely")
    validate_active_state(active, root, reporter, ci=True)
    if args.base is None:
        validate_local_canonical_actionable(root, active, reporter)
    contract = validate_active_contract(root, active, reporter, revision=active_revision)
    plan = validate_plan_approval_and_assignments(
        root, active, reporter, revision=active_revision
    )
    authority_valid = contract is not None and plan is not None and not reporter.has_blocks

    placeholders = unresolved_placeholders(active)
    if placeholders:
        reporter.blocked(
            "BLOCKING_PLACEHOLDERS",
            "Actionable lifecycle contains unresolved fields: " + ", ".join(placeholders),
        )

    _check_transition(root, active, reporter, args.base)
    _check_changed_scope(active, changed, reporter, authority_valid=authority_valid)
    _check_handoff(root, active, changed, reporter)
    _check_evidence(root, active, reporter)
    _check_review(root, active, reporter)
    _check_review_invalidation(root, active, reporter, args.head)
    _check_protected_governance(
        root, active, changed, reporter, args.base, active_revision
    )
    _check_approval_semantics(root, reporter)

    conflicts = scan_conflict_markers(root)
    if conflicts:
        reporter.blocked("CONFLICT_MARKERS", "Merge conflict markers found at " + ", ".join(conflicts))
    else:
        reporter.passed("CONFLICT_MARKERS", "No unresolved merge conflict markers found")

    links = internal_link_issues(root)
    if links:
        reporter.blocked("INTERNAL_LINKS", "Broken internal links: " + "; ".join(links))
    else:
        reporter.passed("INTERNAL_LINKS", "Governance Markdown internal links resolve")

    success_message = None
    if args.base and active_is_none(active):
        success_message = "PASS — Structural validation passed; execution is not authorized"
    reporter.render(success_message=success_message)
    return reporter.exit_code


if __name__ == "__main__":
    raise SystemExit(main())
