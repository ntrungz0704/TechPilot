#!/usr/bin/env python3
"""Shared, dependency-free helpers for repository governance checks.

The YAML reader intentionally implements only the small, safe subset used by
the repository's Markdown front matter.  It does not construct Python objects,
resolve aliases, or execute YAML tags.
"""

from __future__ import annotations

import fnmatch
import json
import posixpath
import re
import subprocess
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path, PurePosixPath
from typing import Any, Iterable, Iterator, Sequence
from urllib.parse import unquote


ACTIVE_PATH = "docs/checkpoints/ACTIVE.md"

VALID_LIFECYCLES = {
    "DRAFT",
    "PLAN_REVIEW",
    "PLAN_APPROVED",
    "EXECUTING",
    "READY_FOR_REVIEW",
    "GATE_PASS",
    "MERGED",
    "CLOSED",
    "BLOCKED",
    "REWORK_REQUIRED",
    "REVIEW_INVALIDATED",
    "ROLLBACK_REQUIRED",
}

ACTIONABLE_LIFECYCLES = {
    "PLAN_APPROVED",
    "EXECUTING",
    "READY_FOR_REVIEW",
    "REWORK_REQUIRED",
    "GATE_PASS",
    "REVIEW_INVALIDATED",
    "MERGED",
    "CLOSED",
    "ROLLBACK_REQUIRED",
}

LIFECYCLE_TRANSITIONS = {
    "DRAFT": {"PLAN_REVIEW", "BLOCKED"},
    "PLAN_REVIEW": {"PLAN_APPROVED", "REWORK_REQUIRED", "BLOCKED"},
    "PLAN_APPROVED": {"EXECUTING", "BLOCKED"},
    "EXECUTING": {"READY_FOR_REVIEW", "BLOCKED"},
    "READY_FOR_REVIEW": {"GATE_PASS", "REWORK_REQUIRED", "BLOCKED"},
    "REWORK_REQUIRED": {"EXECUTING", "BLOCKED"},
    "GATE_PASS": {"MERGED", "REVIEW_INVALIDATED"},
    "REVIEW_INVALIDATED": {"EXECUTING", "BLOCKED"},
    "MERGED": {"CLOSED", "ROLLBACK_REQUIRED"},
    "ROLLBACK_REQUIRED": {"BLOCKED", "CLOSED"},
    "BLOCKED": {"DRAFT", "PLAN_REVIEW", "PLAN_APPROVED", "EXECUTING"},
    "CLOSED": set(),
}

ACTIVE_REQUIRED_KEYS = (
    "checkpoint_id",
    "phase_id",
    "title",
    "lifecycle_status",
    "planning_authority",
    "plan_approved_by",
    "plan_approval_ref",
    "assigned_writer",
    "assigned_reviewer",
    "release_authority",
    "base_commit",
    "candidate_commit",
    "reviewed_commit",
    "contract_path",
    "handoff_path",
    "review_path",
    "evidence_path",
    "allowed_paths",
    "forbidden_paths",
    "forbidden_changes",
    "writer_permission",
    "reviewer_permission",
    "required_tests",
    "required_evidence",
    "required_next_action",
    "blocking_reason",
    "last_updated",
    "governance_change_approved_by",
    "governance_change_approval_ref",
)

ASSIGNMENT_KEYS = (
    "member",
    "role",
    "tool",
    "assigned_by",
    "assigned_at",
    "approval_record",
    "role_assignment_ref",
)

CONTRACT_REQUIRED_KEYS = (
    "checkpoint_id",
    "phase_id",
    "title",
    "lifecycle_status",
    "planning_authority",
    "plan_approved_by",
    "plan_approval_ref",
    "assigned_writer",
    "assigned_reviewer",
    "base_commit",
    "contract_path",
    "allowed_paths",
    "forbidden_paths",
    "forbidden_changes",
    "writer_permission",
    "reviewer_permission",
    "required_tests",
    "required_evidence",
    "required_next_action",
    "last_updated",
)

CONTRACT_REQUIRED_HEADINGS = (
    "objective",
    "business outcome",
    "dependencies and entry conditions",
    "in scope",
    "out of scope",
    "technical contract",
    "mvc constraints",
    "existing conventions to preserve",
    "acceptance criteria",
    "required tests and evidence",
    "exit conditions",
    "stop conditions",
    "known risks",
    "human approval",
)

VAGUE_ACCEPTANCE_PHRASES = {
    "hoạt động tốt",
    "code sạch",
    "ui đẹp",
    "tối ưu",
    "không lỗi",
    "works well",
    "clean code",
    "looks good",
    "optimized",
    "no errors",
}

PLACEHOLDERS = {
    "",
    "UNASSIGNED",
    "UNRESOLVED",
    "NO_ACTIVE_CHECKPOINT",
    "HUMAN_PLAN_APPROVAL_REQUIRED",
    "TOOL_ADAPTER_REQUIRED",
    "NOT_APPLICABLE",
    "N/A",
    "NONE",
    "NULL",
    "CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT",
}

VAGUE_APPROVALS = {
    "APPROVED",
    "YES",
    "TRUE",
    "OK",
    "DONE",
    "HUMAN",
    "OWNER",
    "PROJECT OWNER",
}

REQUIRED_GOVERNANCE_FILES = (
    "README.md",
    "START_HERE.md",
    "AGENTS.md",
    "CONTRIBUTING.md",
    "ROADMAP.md",
    "docs/vision/PRODUCT_VISION.md",
    "docs/architecture/SYSTEM_ARCHITECTURE.md",
    "docs/architecture/adr/ADR-TEMPLATE.md",
    "docs/approvals/TEMPLATE.md",
    "docs/phases/PHASE-TEMPLATE.md",
    ACTIVE_PATH,
    "docs/checkpoints/TEMPLATE.md",
    "docs/handoffs/TEMPLATE.md",
    "docs/reviews/TEMPLATE.md",
    "docs/governance/AUTHORITY_MODEL.md",
    "docs/governance/ROLE_MODEL.md",
    "docs/governance/APPROVAL_MODEL.md",
    "docs/governance/CHECKPOINT_LIFECYCLE.md",
    "docs/governance/SESSION_PROTOCOL.md",
    "docs/governance/TOOL_INDEPENDENCE.md",
    "docs/governance/BRANCH_AND_RELEASE_RULES.md",
    "docs/governance/VIBE_CODER_PLAYBOOK.md",
    "docs/prompts/common/SESSION_START.md",
    "docs/prompts/common/WRITER_CONTRACT.md",
    "docs/prompts/common/REVIEWER_CONTRACT.md",
    "docs/prompts/adapters/ADAPTER_TEMPLATE.md",
    "docs/prompts/adapters/CODEX.md",
    "docs/prompts/OWNER_REVIEW.md",
    "docs/glossary/GLOSSARY.md",
    "scripts/governance_lib.py",
    "scripts/repo_doctor.py",
    "scripts/governance_check.py",
    ".github/CODEOWNERS",
    ".github/pull_request_template.md",
    ".github/workflows/governance.yml",
    ".github/workflows/governance-trusted.yml",
)

GOVERNANCE_PATTERNS = (
    "README.md",
    "START_HERE.md",
    "AGENTS.md",
    "CONTRIBUTING.md",
    "ROADMAP.md",
    ".gitignore",
    ".gitattributes",
    ".editorconfig",
    "docs/**",
    "scripts/**",
    ".github/**",
)

PROTECTED_GOVERNANCE_PATTERNS = (
    "AGENTS.md",
    "START_HERE.md",
    "docs/approvals/TEMPLATE.md",
    "docs/architecture/adr/ADR-TEMPLATE.md",
    "docs/checkpoints/TEMPLATE.md",
    "docs/evidence/README.md",
    "docs/governance/**",
    "docs/handoffs/TEMPLATE.md",
    "docs/prompts/**",
    "docs/reviews/TEMPLATE.md",
    "scripts/**",
    ".github/CODEOWNERS",
    ".github/pull_request_template.md",
    ".github/workflows/**",
)

_SHA_RE = re.compile(r"^[0-9a-fA-F]{7,40}$")
_KEY_RE = re.compile(r"^[A-Za-z0-9_.-]+$")
_CONFLICT_RE = re.compile(r"^(?:<<<<<<<(?: .*)?|=======|>>>>>>>(?: .*)?)$")
_MARKDOWN_LINK_RE = re.compile(r"(?<!!)\[[^\]]+\]\(([^)]+)\)")


class GovernanceError(RuntimeError):
    """Base error for a governance validation failure."""


class FrontMatterError(GovernanceError):
    """Raised when restricted YAML front matter is malformed or unsafe."""


@dataclass(frozen=True)
class _YamlToken:
    indent: int
    content: str
    line: int


@dataclass(frozen=True)
class Diagnostic:
    level: str
    code: str
    message: str


class Reporter:
    """Collect and render stable, human-readable diagnostics."""

    def __init__(self) -> None:
        self.items: list[Diagnostic] = []

    def passed(self, code: str, message: str) -> None:
        self.items.append(Diagnostic("PASS", code, message))

    def warning(self, code: str, message: str) -> None:
        self.items.append(Diagnostic("WARNING", code, message))

    def blocked(self, code: str, message: str) -> None:
        self.items.append(Diagnostic("BLOCKED", code, message))

    @property
    def has_blocks(self) -> bool:
        return any(item.level == "BLOCKED" for item in self.items)

    @property
    def exit_code(self) -> int:
        return 1 if self.has_blocks else 0

    def render(self, success_message: str | None = None) -> None:
        order = {"BLOCKED": 0, "WARNING": 1, "PASS": 2}
        for item in sorted(self.items, key=lambda value: order[value.level]):
            print(f"{item.level} — {item.code}: {item.message}")
        if self.has_blocks:
            print("BLOCKED — Không được tiếp tục")
        else:
            print(success_message or "PASS — Có thể bắt đầu")


def _strip_inline_comment(value: str) -> str:
    quote: str | None = None
    escaped = False
    depth = 0
    for index, char in enumerate(value):
        if escaped:
            escaped = False
            continue
        if quote == '"' and char == "\\":
            escaped = True
            continue
        if char in ("'", '"'):
            if quote is None:
                quote = char
            elif quote == char:
                quote = None
            continue
        if quote is None:
            if char in "[{":
                depth += 1
            elif char in "]}":
                depth -= 1
            elif char == "#" and depth == 0 and (index == 0 or value[index - 1].isspace()):
                return value[:index].rstrip()
    return value.rstrip()


def _split_top_level(value: str, delimiter: str = ",") -> list[str]:
    parts: list[str] = []
    quote: str | None = None
    escaped = False
    depth = 0
    start = 0
    for index, char in enumerate(value):
        if escaped:
            escaped = False
            continue
        if quote == '"' and char == "\\":
            escaped = True
            continue
        if char in ("'", '"'):
            if quote is None:
                quote = char
            elif quote == char:
                quote = None
            continue
        if quote is None:
            if char in "[{":
                depth += 1
            elif char in "]}":
                depth -= 1
                if depth < 0:
                    raise FrontMatterError("Flow collection has an unmatched closing bracket")
            elif char == delimiter and depth == 0:
                parts.append(value[start:index].strip())
                start = index + 1
    if quote is not None or depth != 0:
        raise FrontMatterError("Flow collection has an unterminated quote or bracket")
    parts.append(value[start:].strip())
    return parts


def _split_mapping(value: str, line: int) -> tuple[str, str]:
    quote: str | None = None
    escaped = False
    depth = 0
    for index, char in enumerate(value):
        if escaped:
            escaped = False
            continue
        if quote == '"' and char == "\\":
            escaped = True
            continue
        if char in ("'", '"'):
            if quote is None:
                quote = char
            elif quote == char:
                quote = None
            continue
        if quote is None:
            if char in "[{":
                depth += 1
            elif char in "]}":
                depth -= 1
            elif char == ":" and depth == 0:
                key = value[:index].strip()
                if not _KEY_RE.fullmatch(key):
                    raise FrontMatterError(f"Line {line}: unsupported mapping key {key!r}")
                return key, value[index + 1 :].strip()
    raise FrontMatterError(f"Line {line}: expected a 'key: value' mapping")


def _parse_scalar(value: str, line: int) -> Any:
    value = value.strip()
    if value == "":
        return None
    if value in {"|", ">", "|-", ">-", "|+", ">+"}:
        raise FrontMatterError(f"Line {line}: block scalars are not supported")
    if re.match(r"^[!&][^\s]+", value) or re.fullmatch(r"\*[A-Za-z_][\w.-]*", value):
        raise FrontMatterError(f"Line {line}: YAML tags, anchors, and aliases are not allowed")
    if value.startswith("["):
        if not value.endswith("]"):
            raise FrontMatterError(f"Line {line}: unterminated flow list")
        inner = value[1:-1].strip()
        return [] if not inner else [_parse_scalar(part, line) for part in _split_top_level(inner)]
    if value.startswith("{"):
        if not value.endswith("}"):
            raise FrontMatterError(f"Line {line}: unterminated flow mapping")
        inner = value[1:-1].strip()
        result: dict[str, Any] = {}
        if not inner:
            return result
        for part in _split_top_level(inner):
            key, raw = _split_mapping(part, line)
            if key in result:
                raise FrontMatterError(f"Line {line}: duplicate key {key!r}")
            result[key] = _parse_scalar(raw, line)
        return result
    if value.startswith('"'):
        try:
            parsed = json.loads(value)
        except json.JSONDecodeError as exc:
            raise FrontMatterError(f"Line {line}: invalid double-quoted scalar: {exc.msg}") from exc
        if not isinstance(parsed, str):
            raise FrontMatterError(f"Line {line}: quoted value must be a string")
        return parsed
    if value.startswith("'"):
        if len(value) < 2 or not value.endswith("'"):
            raise FrontMatterError(f"Line {line}: unterminated single-quoted scalar")
        return value[1:-1].replace("''", "'")
    lowered = value.lower()
    if lowered in {"true", "false"}:
        return lowered == "true"
    if lowered in {"null", "~"}:
        return None
    if re.fullmatch(r"[-+]?\d+", value):
        return int(value)
    if re.fullmatch(r"[-+]?(?:\d+\.\d*|\d*\.\d+)", value):
        return float(value)
    return value


def _tokenize_yaml(lines: Sequence[str], start_line: int) -> list[_YamlToken]:
    tokens: list[_YamlToken] = []
    for offset, raw_line in enumerate(lines):
        line_number = start_line + offset
        if "\t" in raw_line[: len(raw_line) - len(raw_line.lstrip(" \t"))]:
            raise FrontMatterError(f"Line {line_number}: tabs are not allowed for indentation")
        stripped = raw_line.lstrip(" ")
        if not stripped or stripped.startswith("#"):
            continue
        indent = len(raw_line) - len(stripped)
        if indent % 2 != 0:
            raise FrontMatterError(f"Line {line_number}: indentation must use multiples of two spaces")
        content = _strip_inline_comment(stripped)
        if not content:
            continue
        tokens.append(_YamlToken(indent, content, line_number))
    return tokens


def _parse_mapping_tokens(
    tokens: Sequence[_YamlToken], index: int, indent: int
) -> tuple[dict[str, Any], int]:
    result: dict[str, Any] = {}
    while index < len(tokens):
        token = tokens[index]
        if token.indent < indent:
            break
        if token.indent > indent:
            raise FrontMatterError(f"Line {token.line}: unexpected indentation")
        if token.content == "-" or token.content.startswith("- "):
            break
        key, raw_value = _split_mapping(token.content, token.line)
        if key in result:
            raise FrontMatterError(f"Line {token.line}: duplicate key {key!r}")
        index += 1
        if raw_value:
            result[key] = _parse_scalar(raw_value, token.line)
        elif index < len(tokens) and tokens[index].indent > indent:
            if tokens[index].indent != indent + 2:
                raise FrontMatterError(
                    f"Line {tokens[index].line}: nested value must be indented by two spaces"
                )
            result[key], index = _parse_node(tokens, index, indent + 2)
        else:
            result[key] = None
    return result, index


def _parse_sequence_tokens(
    tokens: Sequence[_YamlToken], index: int, indent: int
) -> tuple[list[Any], int]:
    result: list[Any] = []
    while index < len(tokens):
        token = tokens[index]
        if token.indent < indent:
            break
        if token.indent > indent:
            raise FrontMatterError(f"Line {token.line}: unexpected indentation")
        if not (token.content == "-" or token.content.startswith("- ")):
            break
        rest = token.content[1:].strip()
        index += 1
        if not rest:
            if index >= len(tokens) or tokens[index].indent != indent + 2:
                raise FrontMatterError(f"Line {token.line}: list item needs a nested value")
            value, index = _parse_node(tokens, index, indent + 2)
            result.append(value)
            continue
        try:
            key, raw_value = _split_mapping(rest, token.line)
            is_mapping_item = True
        except FrontMatterError:
            is_mapping_item = False
        if not is_mapping_item:
            result.append(_parse_scalar(rest, token.line))
            if index < len(tokens) and tokens[index].indent > indent:
                raise FrontMatterError(
                    f"Line {tokens[index].line}: scalar list item cannot have nested content"
                )
            continue
        item: dict[str, Any] = {}
        if raw_value:
            item[key] = _parse_scalar(raw_value, token.line)
        elif index < len(tokens) and tokens[index].indent == indent + 4:
            item[key], index = _parse_node(tokens, index, indent + 4)
        else:
            item[key] = None
        if index < len(tokens) and tokens[index].indent == indent + 2:
            continuation, index = _parse_mapping_tokens(tokens, index, indent + 2)
            duplicate = set(item).intersection(continuation)
            if duplicate:
                raise FrontMatterError(
                    f"Line {tokens[index - 1].line}: duplicate list mapping key {sorted(duplicate)[0]!r}"
                )
            item.update(continuation)
        result.append(item)
    return result, index


def _parse_node(tokens: Sequence[_YamlToken], index: int, indent: int) -> tuple[Any, int]:
    if index >= len(tokens) or tokens[index].indent != indent:
        line = tokens[index].line if index < len(tokens) else "EOF"
        raise FrontMatterError(f"Line {line}: invalid nested indentation")
    if tokens[index].content == "-" or tokens[index].content.startswith("- "):
        return _parse_sequence_tokens(tokens, index, indent)
    return _parse_mapping_tokens(tokens, index, indent)


def parse_front_matter_text(text: str, source: str = "<memory>") -> tuple[dict[str, Any], str]:
    """Parse restricted YAML front matter and return ``(metadata, body)``."""

    normalized = text.lstrip("\ufeff").replace("\r\n", "\n").replace("\r", "\n")
    lines = normalized.split("\n")
    if not lines or lines[0].strip() != "---":
        raise FrontMatterError(f"{source}: YAML front matter must start on the first line")
    closing = next((index for index in range(1, len(lines)) if lines[index].strip() == "---"), None)
    if closing is None:
        raise FrontMatterError(f"{source}: YAML front matter has no closing delimiter")
    tokens = _tokenize_yaml(lines[1:closing], 2)
    if not tokens:
        raise FrontMatterError(f"{source}: YAML front matter is empty")
    if tokens[0].indent != 0:
        raise FrontMatterError(f"Line {tokens[0].line}: top-level keys must not be indented")
    parsed, index = _parse_node(tokens, 0, 0)
    if index != len(tokens):
        raise FrontMatterError(f"Line {tokens[index].line}: could not parse front matter")
    if not isinstance(parsed, dict):
        raise FrontMatterError(f"{source}: top-level front matter must be a mapping")
    return parsed, "\n".join(lines[closing + 1 :])


def load_front_matter(path: Path) -> tuple[dict[str, Any], str]:
    try:
        text = path.read_text(encoding="utf-8-sig")
    except OSError as exc:
        raise FrontMatterError(f"Cannot read {path}: {exc}") from exc
    return parse_front_matter_text(text, path.as_posix())


def get_nested(data: dict[str, Any], dotted_path: str, default: Any = None) -> Any:
    current: Any = data
    for part in dotted_path.split("."):
        if not isinstance(current, dict) or part not in current:
            return default
        current = current[part]
    return current


def get_first(data: dict[str, Any], *paths: str, default: Any = None) -> Any:
    for path in paths:
        value = get_nested(data, path, default=None)
        if value is not None:
            return value
    return default


def scalar_text(value: Any) -> str:
    if value is None:
        return ""
    if isinstance(value, bool):
        return "true" if value else "false"
    return str(value).strip()


def as_string_list(value: Any) -> list[str]:
    if value is None:
        return []
    values = value if isinstance(value, list) else [value]
    result: list[str] = []
    for item in values:
        if isinstance(item, dict):
            preferred = get_first(item, "path", "file", "command", "name", "value")
            if preferred is not None:
                result.append(scalar_text(preferred))
            else:
                result.append(json.dumps(item, ensure_ascii=False, sort_keys=True))
        else:
            result.append(scalar_text(item))
    return [item for item in result if item]


def is_placeholder(value: Any) -> bool:
    return scalar_text(value).upper() in PLACEHOLDERS


def is_vague_approval(value: Any) -> bool:
    return scalar_text(value).upper() in VAGUE_APPROVALS


def find_repo_root(start: Path | None = None) -> Path:
    cwd = (start or Path.cwd()).resolve()
    completed = subprocess.run(
        ["git", "rev-parse", "--show-toplevel"],
        cwd=cwd,
        text=True,
        capture_output=True,
        check=False,
    )
    if completed.returncode != 0:
        raise GovernanceError("Current directory is not inside a Git repository")
    return Path(completed.stdout.strip()).resolve()


def run_git(root: Path, args: Sequence[str], check: bool = True) -> subprocess.CompletedProcess[str]:
    completed = subprocess.run(
        ["git", *args], cwd=root, text=True, capture_output=True, check=False
    )
    if check and completed.returncode != 0:
        detail = completed.stderr.strip() or completed.stdout.strip() or "unknown Git error"
        raise GovernanceError(f"git {' '.join(args)} failed: {detail}")
    return completed


def git_head(root: Path) -> str:
    return run_git(root, ["rev-parse", "HEAD"]).stdout.strip()


def git_branch(root: Path) -> str:
    return run_git(root, ["branch", "--show-current"]).stdout.strip()


def ref_is_commit(root: Path, ref: str) -> bool:
    if not ref:
        return False
    return run_git(root, ["rev-parse", "--verify", f"{ref}^{{commit}}"], check=False).returncode == 0


def resolve_commit(root: Path, ref: str) -> str | None:
    completed = run_git(root, ["rev-parse", "--verify", f"{ref}^{{commit}}"], check=False)
    return completed.stdout.strip() if completed.returncode == 0 else None


def changed_files_between(root: Path, base: str, head: str) -> list[str]:
    completed = run_git(
        root, ["diff", "--name-only", "-z", "--no-renames", base, head, "--"]
    )
    return sorted({normalize_repo_path(item) for item in completed.stdout.split("\0") if item})


def working_tree_entries(root: Path) -> list[tuple[str, str]]:
    completed = run_git(
        root, ["status", "--porcelain=v1", "-z", "--untracked-files=all"]
    )
    records = completed.stdout.split("\0")
    entries: list[tuple[str, str]] = []
    index = 0
    while index < len(records):
        record = records[index]
        index += 1
        if not record:
            continue
        if len(record) < 4:
            continue
        status = record[:2]
        path = normalize_repo_path(record[3:])
        entries.append((status, path))
        if "R" in status or "C" in status:
            index += 1  # porcelain -z emits the source path as the next record
    return entries


def working_tree_files(root: Path) -> list[str]:
    return sorted({path for _, path in working_tree_entries(root)})


def all_repository_files(root: Path) -> list[str]:
    completed = run_git(root, ["ls-files", "-z", "--cached", "--others", "--exclude-standard"])
    return sorted({normalize_repo_path(item) for item in completed.stdout.split("\0") if item})


def normalize_repo_path(value: str) -> str:
    path = value.strip().strip("`").replace("\\", "/")
    while path.startswith("./"):
        path = path[2:]
    path = path.lstrip("/")
    return PurePosixPath(path).as_posix() if path else ""


def validate_repo_path(value: str) -> str:
    raw = value.strip().strip("`")
    if not raw:
        raise GovernanceError("path is empty")
    if re.match(r"^[A-Za-z]:[\\/]", raw) or raw.startswith(("\\\\", "//")):
        raise GovernanceError(f"absolute path is not allowed: {value}")
    normalized = normalize_repo_path(raw)
    if any(part == ".." for part in PurePosixPath(normalized).parts):
        raise GovernanceError(f"path escapes the repository: {value}")
    if normalized == ".git" or normalized.startswith(".git/"):
        raise GovernanceError(f"Git internals cannot be governed as a writable path: {value}")
    return normalized


def path_matches(path: str, pattern: str) -> bool:
    normalized_path = normalize_repo_path(path)
    normalized_pattern = normalize_repo_path(pattern)
    if not normalized_pattern:
        return False
    if normalized_pattern.endswith("/**"):
        prefix = normalized_pattern[:-3].rstrip("/")
        return normalized_path == prefix or normalized_path.startswith(prefix + "/")
    if normalized_pattern.endswith("/"):
        prefix = normalized_pattern.rstrip("/")
        return normalized_path == prefix or normalized_path.startswith(prefix + "/")
    if any(character in normalized_pattern for character in "*?["):
        expression = ""
        index = 0
        while index < len(normalized_pattern):
            character = normalized_pattern[index]
            if character == "*":
                if index + 1 < len(normalized_pattern) and normalized_pattern[index + 1] == "*":
                    index += 2
                    if index < len(normalized_pattern) and normalized_pattern[index] == "/":
                        expression += "(?:.*/)?"
                        index += 1
                    else:
                        expression += ".*"
                    continue
                expression += "[^/]*"
            elif character == "?":
                expression += "[^/]"
            elif character == "[":
                closing = normalized_pattern.find("]", index + 1)
                if closing == -1:
                    expression += re.escape(character)
                else:
                    content = normalized_pattern[index + 1 : closing]
                    if content.startswith("!"):
                        content = "^" + content[1:]
                    expression += "[" + content.replace("\\", "\\\\") + "]"
                    index = closing
            else:
                expression += re.escape(character)
            index += 1
        return re.fullmatch(expression, normalized_path) is not None
    return normalized_path == normalized_pattern


def matches_any(path: str, patterns: Iterable[str]) -> bool:
    return any(path_matches(path, pattern) for pattern in patterns)


def is_governance_path(path: str) -> bool:
    return matches_any(path, GOVERNANCE_PATTERNS)


def is_protected_governance_path(path: str) -> bool:
    return matches_any(path, PROTECTED_GOVERNANCE_PATTERNS)


def path_exists_at_revision(root: Path, revision: str, path: str) -> bool:
    return run_git(root, ["cat-file", "-e", f"{revision}:{normalize_repo_path(path)}"], check=False).returncode == 0


def read_at_revision(root: Path, revision: str, path: str) -> str | None:
    completed = run_git(root, ["show", f"{revision}:{normalize_repo_path(path)}"], check=False)
    return completed.stdout if completed.returncode == 0 else None


def read_bytes_at_revision(root: Path, revision: str, path: str) -> bytes | None:
    completed = subprocess.run(
        ["git", "show", f"{revision}:{normalize_repo_path(path)}"],
        cwd=root,
        capture_output=True,
        check=False,
    )
    return completed.stdout if completed.returncode == 0 else None


def active_is_none(active: dict[str, Any]) -> bool:
    return scalar_text(active.get("checkpoint_id")).upper() == "NO_ACTIVE_CHECKPOINT"


def lifecycle(active: dict[str, Any]) -> str:
    return scalar_text(active.get("lifecycle_status")).upper()


def is_actionable(active: dict[str, Any]) -> bool:
    return lifecycle(active) in ACTIONABLE_LIFECYCLES


def is_real_sha(value: Any) -> bool:
    return bool(_SHA_RE.fullmatch(scalar_text(value)))


def is_full_sha(value: Any) -> bool:
    return bool(re.fullmatch(r"[0-9a-fA-F]{40}", scalar_text(value)))


def is_concrete_timestamp(value: Any) -> bool:
    raw = scalar_text(value)
    if not raw or is_placeholder(raw) or "T" not in raw:
        return False
    try:
        parsed = datetime.fromisoformat(raw.replace("Z", "+00:00"))
    except ValueError:
        return False
    return parsed.tzinfo is not None


def is_ancestor(root: Path, ancestor: str, descendant: str) -> bool:
    return (
        run_git(
            root,
            ["merge-base", "--is-ancestor", ancestor, descendant],
            check=False,
        ).returncode
        == 0
    )


def read_validation_text(root: Path, path: str, revision: str | None) -> str | None:
    relative = normalize_repo_path(path)
    if revision:
        return read_at_revision(root, revision, relative)
    file_path = root / relative
    if not file_path.is_file():
        return None
    try:
        return file_path.read_text(encoding="utf-8-sig")
    except OSError:
        return None


def _approval_path(raw_reference: Any) -> str | None:
    raw = scalar_text(raw_reference)
    if not raw or is_placeholder(raw):
        return None
    try:
        relative = validate_repo_path(raw)
    except GovernanceError:
        return None
    if not relative.startswith("docs/approvals/") or relative == "docs/approvals/TEMPLATE.md":
        return None
    return relative


def load_decision_record(
    root: Path,
    reference: Any,
    reporter: Reporter,
    *,
    revision: str | None,
    label: str,
) -> tuple[str, dict[str, Any], str] | None:
    relative = _approval_path(reference)
    if relative is None:
        reporter.blocked(
            f"{label}_REF",
            f"{label.lower()} reference must point below docs/approvals/ and cannot be TEMPLATE.md",
        )
        return None
    text = read_validation_text(root, relative, revision)
    if text is None:
        reporter.blocked(f"{label}_REF", f"Decision record does not exist at validation revision: {relative}")
        return None
    try:
        metadata, _ = parse_front_matter_text(text, relative)
    except FrontMatterError as exc:
        reporter.blocked(f"{label}_FRONTMATTER", str(exc))
        return None
    return relative, metadata, text


def validate_decision_record(
    root: Path,
    record: dict[str, Any],
    active: dict[str, Any],
    reporter: Reporter,
    *,
    code: str,
    expected_decision: str,
    expected_scope: str,
    expected_approver: Any,
    revision: str | None,
    expected_document_path: str | None = None,
) -> bool:
    valid = True

    def fail(message: str) -> None:
        nonlocal valid
        valid = False
        reporter.blocked(code, message)

    required_fields = (
        "decision_type",
        "scope_type",
        "scope_id",
        "checkpoint_id",
        "phase_id",
        "decided_object",
        "approved_by",
        "authority_role",
        "authority_tool",
        "approved_at",
        "approved_document_path",
        "approved_document_commit",
        "contract_path",
        "base_commit",
        "candidate_commit",
        "reviewed_commit",
        "allowed_next_action",
        "forbidden_actions",
        "conditions",
        "limitations",
    )
    missing = [field for field in required_fields if field not in record]
    if missing:
        fail("decision record is missing: " + ", ".join(missing))

    if scalar_text(record.get("decision_type")).upper() != expected_decision:
        fail(f"decision_type must be {expected_decision}")
    if scalar_text(record.get("scope_type")).upper() != expected_scope:
        fail(f"scope_type must be {expected_scope}")
    if scalar_text(record.get("checkpoint_id")) != scalar_text(active.get("checkpoint_id")):
        fail("checkpoint_id does not match ACTIVE")
    if scalar_text(record.get("phase_id")) != scalar_text(active.get("phase_id")):
        fail("phase_id does not match ACTIVE")
    expected_scope_id = (
        scalar_text(active.get("checkpoint_id"))
        if expected_scope == "CHECKPOINT"
        else "REPOSITORY_GOVERNANCE"
    )
    if scalar_text(record.get("scope_id")) != expected_scope_id:
        fail(f"scope_id must be {expected_scope_id}")
    if not scalar_text(record.get("decided_object")) or is_placeholder(record.get("decided_object")):
        fail("decided_object must be concrete")
    approver = scalar_text(record.get("approved_by"))
    if not approver or is_placeholder(approver) or approver != scalar_text(expected_approver):
        fail("approved_by must be the concrete authority recorded in ACTIVE")
    if scalar_text(record.get("authority_role")) != "Human Project Owner":
        fail("authority_role must be exactly Human Project Owner")
    if "authority_tool" not in record or not scalar_text(record.get("authority_tool")):
        fail("authority_tool is required; use NOT_APPLICABLE when no tool applies")
    if not is_concrete_timestamp(record.get("approved_at")):
        fail("approved_at must be an ISO-8601 timestamp with timezone")

    base = scalar_text(active.get("base_commit"))
    if scalar_text(record.get("base_commit")) != base or not is_full_sha(base):
        fail("base_commit must be the same full SHA as ACTIVE")

    document_path = scalar_text(record.get("approved_document_path"))
    if expected_document_path is not None and normalize_repo_path(document_path) != normalize_repo_path(
        expected_document_path
    ):
        fail("approved_document_path does not match the approved contract")
    try:
        document_path = validate_repo_path(document_path)
    except GovernanceError:
        fail("approved_document_path must be a safe repository path")
        document_path = ""

    document_commit = scalar_text(record.get("approved_document_commit"))
    validation_head = revision or "HEAD"
    if not is_full_sha(document_commit) or not ref_is_commit(root, document_commit):
        fail("approved_document_commit must be a full existing commit SHA")
    elif not is_ancestor(root, document_commit, validation_head):
        fail("approved_document_commit must be an ancestor of the validation head")
    elif document_path:
        approved_content = read_at_revision(root, document_commit, document_path)
        current_content = read_validation_text(root, document_path, revision)
        if approved_content is None or current_content is None or approved_content != current_content:
            fail("approved document content has changed since approved_document_commit")

    conditions = record.get("conditions")
    forbidden = record.get("forbidden_actions")
    limitations = record.get("limitations")
    if not isinstance(conditions, list) or not as_string_list(conditions):
        fail("conditions must be a nonempty YAML list")
    if not isinstance(forbidden, list) or not as_string_list(forbidden):
        fail("forbidden_actions must be a nonempty YAML list")
    if not isinstance(limitations, list) or not as_string_list(limitations):
        fail("limitations must be a nonempty YAML list")
    for field in ("candidate_commit", "reviewed_commit"):
        value = scalar_text(record.get(field))
        if value != "NOT_APPLICABLE" and (not is_full_sha(value) or not ref_is_commit(root, value)):
            fail(f"{field} must be NOT_APPLICABLE or a full existing commit SHA")
    next_action = get_first(record, "allowed_next_action", "required_next_action", "next_action")
    if not scalar_text(next_action) or is_placeholder(next_action):
        fail("allowed_next_action must be concrete")
    return valid


def _assignment_snapshot(record: dict[str, Any], kind: str) -> dict[str, Any] | None:
    candidates = (
        get_nested(record, f"assignments.{kind}"),
        get_nested(record, f"assigned_{kind}"),
        record.get("assignment"),
    )
    for candidate in candidates:
        if isinstance(candidate, dict):
            return candidate
    return None


def _assignment_matches(expected: dict[str, Any], actual: dict[str, Any]) -> bool:
    return all(
        scalar_text(expected.get(field)) == scalar_text(actual.get(field))
        for field in ("member", "role", "tool", "assigned_by", "assigned_at")
    )


def validate_plan_approval_and_assignments(
    root: Path,
    active: dict[str, Any],
    reporter: Reporter,
    *,
    revision: str | None,
) -> dict[str, Any] | None:
    """Validate canonical Human plan approval and independent role assignments."""

    if not is_actionable(active):
        return None
    contract_path = scalar_text(active.get("contract_path"))
    loaded = load_decision_record(
        root,
        active.get("plan_approval_ref"),
        reporter,
        revision=revision,
        label="PLAN_APPROVAL",
    )
    if loaded is None:
        return None
    plan_path, plan, _ = loaded
    plan_valid = validate_decision_record(
        root,
        plan,
        active,
        reporter,
        code="PLAN_APPROVAL",
        expected_decision="PLAN_APPROVED",
        expected_scope="CHECKPOINT",
        expected_approver=active.get("plan_approved_by"),
        revision=revision,
        expected_document_path=contract_path,
    )
    record_contract = scalar_text(plan.get("contract_path"))
    if record_contract and normalize_repo_path(record_contract) != normalize_repo_path(contract_path):
        reporter.blocked("PLAN_APPROVAL", "Approval contract_path does not match ACTIVE")
        plan_valid = False

    covers_assignments = plan.get("covers_assignments") is True
    for kind, active_key in (("writer", "assigned_writer"), ("reviewer", "assigned_reviewer")):
        assignment = active.get(active_key)
        if not isinstance(assignment, dict):
            continue
        for field in ("member", "tool", "assigned_by", "assigned_at"):
            value = assignment.get(field)
            if is_placeholder(value):
                reporter.blocked(
                    "ROLE_ASSIGNMENT",
                    f"{active_key}.{field} must be concrete for lifecycle {lifecycle(active)}",
                )
        if not is_concrete_timestamp(assignment.get("assigned_at")):
            reporter.blocked(
                "ROLE_ASSIGNMENT",
                f"{active_key}.assigned_at must be an ISO-8601 timestamp with timezone",
            )

        covered = False
        snapshot = _assignment_snapshot(plan, kind) if covers_assignments else None
        if snapshot is not None and _assignment_matches(assignment, snapshot):
            approval_record = normalize_repo_path(scalar_text(assignment.get("approval_record")))
            if approval_record != plan_path:
                reporter.blocked(
                    "ROLE_ASSIGNMENT",
                    f"{active_key}.approval_record must reference the covering plan approval",
                )
            else:
                covered = plan_valid
        if covered:
            reporter.passed("ROLE_ASSIGNMENT", f"Plan approval explicitly covers {kind} assignment")
            continue

        role_ref = assignment.get("role_assignment_ref")
        approval_record = assignment.get("approval_record")
        if scalar_text(role_ref) != scalar_text(approval_record):
            reporter.blocked(
                "ROLE_ASSIGNMENT",
                f"{active_key}.approval_record and role_assignment_ref must identify the same record",
            )
        role_loaded = load_decision_record(
            root,
            role_ref,
            reporter,
            revision=revision,
            label=f"{kind.upper()}_ASSIGNMENT",
        )
        if role_loaded is None:
            continue
        _, role_record, _ = role_loaded
        role_valid = validate_decision_record(
            root,
            role_record,
            active,
            reporter,
            code=f"{kind.upper()}_ASSIGNMENT",
            expected_decision="ROLE_ASSIGNMENT_APPROVED",
            expected_scope="CHECKPOINT",
            expected_approver=assignment.get("assigned_by"),
            revision=revision,
            expected_document_path=contract_path,
        )
        role_kind = scalar_text(
            get_first(role_record, "assignment.role_kind", "role_kind", "assignment_type")
        ).upper()
        role_snapshot = _assignment_snapshot(role_record, kind)
        if role_kind != kind.upper() or role_snapshot is None or not _assignment_matches(
            assignment, role_snapshot
        ):
            reporter.blocked(
                "ROLE_ASSIGNMENT",
                f"{kind} assignment approval does not match ACTIVE assignment metadata",
            )
            role_valid = False
        if role_valid:
            reporter.passed("ROLE_ASSIGNMENT", f"Validated independent {kind} assignment record")
    return plan if plan_valid else None


def validate_local_canonical_actionable(
    root: Path, active: dict[str, Any], reporter: Reporter
) -> None:
    """Block an uncommitted ACTIVE/approval edit from granting local authority."""

    if not is_actionable(active):
        return
    paths = {
        ACTIVE_PATH,
        scalar_text(active.get("contract_path")),
        scalar_text(active.get("plan_approval_ref")),
    }
    for key in ("assigned_writer", "assigned_reviewer"):
        assignment = active.get(key)
        if not isinstance(assignment, dict):
            continue
        for field in ("approval_record", "role_assignment_ref"):
            value = scalar_text(assignment.get(field))
            if value and not is_placeholder(value):
                paths.add(value)
    mismatched: list[str] = []
    for raw in sorted(paths):
        if not raw or is_placeholder(raw):
            mismatched.append(raw or "<empty-reference>")
            continue
        try:
            relative = validate_repo_path(raw)
        except GovernanceError:
            mismatched.append(raw)
            continue
        committed = read_bytes_at_revision(root, "HEAD", relative)
        try:
            current = (root / relative).read_bytes()
        except OSError:
            current = None
        if committed is None or current is None or committed != current:
            mismatched.append(relative)
    if mismatched:
        reporter.blocked(
            "UNCANONICAL_STATE",
            "Actionable authority records must be byte-identical to committed HEAD: "
            + ", ".join(mismatched),
        )
    else:
        reporter.passed("CANONICAL_STATE", "Actionable authority is committed and canonical")


def _markdown_headings(body: str) -> dict[str, tuple[int, int]]:
    headings: dict[str, tuple[int, int]] = {}
    for index, line in enumerate(body.splitlines()):
        match = re.match(r"^(#{2,6})\s+(.+?)\s*$", line)
        if not match:
            continue
        name = re.sub(r"\s+", " ", match.group(2).strip().strip("`* ")).casefold()
        headings[name] = (index, len(match.group(1)))
    return headings


def _acceptance_criteria(body: str) -> list[str]:
    lines = body.splitlines()
    start: int | None = None
    level = 0
    criteria: list[str] = []
    for index, line in enumerate(lines):
        heading = re.match(r"^(#{2,6})\s+(.+?)\s*$", line)
        if heading:
            name = re.sub(r"\s+", " ", heading.group(2).strip().strip("`* ")).casefold()
            current_level = len(heading.group(1))
            if start is not None and current_level <= level:
                break
            if name == "acceptance criteria":
                start = index
                level = current_level
            continue
        if start is None:
            continue
        match = re.match(r"^\s*\d+[.)]\s+(.+?)\s*$", line)
        if match:
            criteria.append(match.group(1).strip())
    return criteria


def _criterion_is_vague_only(value: str) -> bool:
    normalized = re.sub(r"[`*_.,:;!?()\[\]{}'-]", " ", value.casefold())
    normalized = re.sub(r"\s+", " ", normalized).strip()
    return normalized in VAGUE_ACCEPTANCE_PHRASES


def _criterion_is_measurable(value: str) -> bool:
    lowered = value.casefold()
    if _criterion_is_vague_only(value):
        return False
    if re.search(r"\d|`[^`]+`|[/\\]|\b(?:exit code|sha|status|path|file|command)\b", value, re.I):
        return True
    signals = (
        "must",
        "phải",
        "matches",
        "khớp",
        "exists",
        "tồn tại",
        "reject",
        "từ chối",
        "block",
        "chặn",
        "only",
        "chỉ",
        "at least",
        "ít nhất",
        "no ",
        "không ",
    )
    return any(signal in lowered for signal in signals)


def validate_active_contract(
    root: Path,
    active: dict[str, Any],
    reporter: Reporter,
    *,
    revision: str | None,
) -> dict[str, Any] | None:
    if not is_actionable(active):
        return None
    raw_path = scalar_text(active.get("contract_path"))
    try:
        relative = validate_repo_path(raw_path)
    except GovernanceError as exc:
        reporter.blocked("CONTRACT", f"Invalid contract_path: {exc}")
        return None
    text = read_validation_text(root, relative, revision)
    if text is None:
        reporter.blocked("CONTRACT", f"Active contract does not exist at validation revision: {relative}")
        return None
    try:
        contract, body = parse_front_matter_text(text, relative)
    except FrontMatterError as exc:
        reporter.blocked("CONTRACT_FRONTMATTER", str(exc))
        return None

    missing = [key for key in CONTRACT_REQUIRED_KEYS if key not in contract]
    if missing:
        reporter.blocked("CONTRACT_SCHEMA", "Active contract is missing: " + ", ".join(missing))

    scalar_matches = (
        "checkpoint_id",
        "phase_id",
        "planning_authority",
        "plan_approved_by",
        "plan_approval_ref",
        "base_commit",
    )
    for key in scalar_matches:
        if scalar_text(contract.get(key)) != scalar_text(active.get(key)):
            reporter.blocked("CONTRACT_MISMATCH", f"Contract {key} does not match ACTIVE")

    if scalar_text(contract.get("lifecycle_status")).upper() != "PLAN_REVIEW":
        reporter.blocked(
            "CONTRACT_STATUS",
            "Approved contract must remain the immutable PLAN_REVIEW document version while ACTIVE advances",
        )

    for key in ("assigned_writer", "assigned_reviewer"):
        expected = active.get(key)
        actual = contract.get(key)
        if not isinstance(expected, dict) or not isinstance(actual, dict):
            reporter.blocked("CONTRACT_MISMATCH", f"Contract {key} must be a mapping")
            continue
        for field in ASSIGNMENT_KEYS:
            if scalar_text(expected.get(field)) != scalar_text(actual.get(field)):
                reporter.blocked(
                    "CONTRACT_MISMATCH", f"Contract {key}.{field} does not match ACTIVE"
                )

    for key in (
        "allowed_paths",
        "forbidden_paths",
        "forbidden_changes",
        "required_tests",
        "required_evidence",
    ):
        if as_string_list(contract.get(key)) != as_string_list(active.get(key)):
            reporter.blocked("CONTRACT_MISMATCH", f"Contract {key} does not match ACTIVE")

    for key in ("contract_path", "writer_permission", "reviewer_permission", "required_next_action"):
        if scalar_text(contract.get(key)) != scalar_text(active.get(key)):
            reporter.blocked("CONTRACT_MISMATCH", f"Contract {key} does not match ACTIVE")

    headings = _markdown_headings(body)
    missing_headings = [heading for heading in CONTRACT_REQUIRED_HEADINGS if heading not in headings]
    if missing_headings:
        reporter.blocked(
            "CONTRACT_HEADINGS",
            "Active contract is missing body headings: " + ", ".join(missing_headings),
        )

    criteria = _acceptance_criteria(body)
    vague = [criterion for criterion in criteria if _criterion_is_vague_only(criterion)]
    measurable = [criterion for criterion in criteria if _criterion_is_measurable(criterion)]
    if vague:
        reporter.blocked(
            "ACCEPTANCE_CRITERIA",
            "Vague-only acceptance criteria are forbidden: " + "; ".join(vague),
        )
    if len(criteria) < 3 or len(measurable) < 3:
        reporter.blocked(
            "ACCEPTANCE_CRITERIA",
            "Active contract needs at least three numbered, measurable acceptance criteria",
        )
    else:
        reporter.passed(
            "ACTIVE_CONTRACT",
            f"Contract schema, headings, assignments, scope and {len(measurable)} measurable criteria validated",
        )
    return contract


def validate_active_state(
    active: dict[str, Any], root: Path, reporter: Reporter, *, ci: bool
) -> None:
    missing = [key for key in ACTIVE_REQUIRED_KEYS if key not in active]
    if missing:
        reporter.blocked("ACTIVE_KEYS", f"ACTIVE.md is missing: {', '.join(missing)}")
    else:
        reporter.passed("ACTIVE_KEYS", "ACTIVE.md contains every required governance key")

    status = lifecycle(active)
    if status not in VALID_LIFECYCLES:
        reporter.blocked("LIFECYCLE", f"Invalid lifecycle_status: {status or '<empty>'}")
    else:
        reporter.passed("LIFECYCLE", f"Lifecycle is {status}")

    no_active = active_is_none(active)
    if no_active and status != "DRAFT":
        reporter.blocked("NO_ACTIVE_STATE", "NO_ACTIVE_CHECKPOINT must use lifecycle_status DRAFT")

    assignments: dict[str, dict[str, Any]] = {}
    for key in ("assigned_writer", "assigned_reviewer"):
        value = active.get(key)
        if not isinstance(value, dict):
            reporter.blocked("ASSIGNMENT_SCHEMA", f"{key} must be a nested mapping")
            assignments[key] = {}
            continue
        assignments[key] = value
        missing_assignment = [field for field in ASSIGNMENT_KEYS if field not in value]
        if missing_assignment:
            reporter.blocked(
                "ASSIGNMENT_SCHEMA",
                f"{key} is missing: {', '.join(missing_assignment)}",
            )

    writer = scalar_text(assignments.get("assigned_writer", {}).get("member"))
    reviewer = scalar_text(assignments.get("assigned_reviewer", {}).get("member"))
    if writer and reviewer and not is_placeholder(writer) and not is_placeholder(reviewer):
        if writer.casefold() == reviewer.casefold():
            reporter.blocked("ROLE_INDEPENDENCE", "Writer and Reviewer must be different members")
        else:
            reporter.passed("ROLE_INDEPENDENCE", "Writer and Reviewer are independently assigned")
    elif no_active:
        message = "No Writer or Reviewer is assigned while there is no active checkpoint"
        reporter.warning("ROLE_ASSIGNMENT", message) if ci else reporter.blocked("ROLE_ASSIGNMENT", message)
    else:
        reporter.warning("ROLE_ASSIGNMENT", "Writer and/or Reviewer remains unassigned")

    writer_role = scalar_text(assignments.get("assigned_writer", {}).get("role"))
    reviewer_role = scalar_text(assignments.get("assigned_reviewer", {}).get("role"))
    if not is_placeholder(writer) and "writer" not in writer_role.casefold():
        reporter.blocked("WRITER_ROLE", "assigned_writer.role must identify an Execution Writer")
    if not is_placeholder(reviewer) and "reviewer" not in reviewer_role.casefold():
        reporter.blocked("REVIEWER_ROLE", "assigned_reviewer.role must identify an Independent Reviewer")

    for key in ("allowed_paths", "forbidden_paths", "forbidden_changes", "required_tests", "required_evidence"):
        value = active.get(key)
        if value is not None and not isinstance(value, list):
            reporter.blocked("ACTIVE_LIST", f"{key} must be a YAML list")

    for key in ("allowed_paths", "forbidden_paths"):
        for raw_path in as_string_list(active.get(key)):
            if is_placeholder(raw_path):
                continue
            try:
                validate_repo_path(raw_path)
            except GovernanceError as exc:
                reporter.blocked("PATH_SAFETY", f"{key}: {exc}")

    plan_approver = active.get("plan_approved_by")
    approval_required = is_actionable(active)
    if approval_required and (is_placeholder(plan_approver) or is_vague_approval(plan_approver)):
        reporter.blocked("PLAN_APPROVAL", "Approved lifecycle requires a specific Human approver")
    elif is_vague_approval(plan_approver):
        reporter.blocked("AMBIGUOUS_APPROVAL", "Do not use a bare or ambiguous approval value")
    if approval_required:
        codeowners = root / ".github/CODEOWNERS"
        try:
            codeowners_text = codeowners.read_text(encoding="utf-8-sig")
        except OSError:
            codeowners_text = ""
        if "@REPLACE_WITH_PROJECT_OWNER" in codeowners_text:
            reporter.blocked(
                "OWNER_HANDLE",
                "Actionable lifecycle is forbidden while CODEOWNERS has the Owner placeholder",
            )

    stage_needs_contract = not no_active
    stage_needs_handoff = status in {
        "READY_FOR_REVIEW",
        "GATE_PASS",
        "REWORK_REQUIRED",
        "REVIEW_INVALIDATED",
        "MERGED",
        "CLOSED",
    }
    stage_needs_review = status in {"GATE_PASS", "REVIEW_INVALIDATED", "MERGED", "CLOSED"}
    stage_needs_evidence = stage_needs_handoff
    reference_rules = (
        ("contract_path", stage_needs_contract),
        ("handoff_path", stage_needs_handoff),
        ("review_path", stage_needs_review),
        ("evidence_path", stage_needs_evidence),
    )
    for key, required in reference_rules:
        raw_value = scalar_text(active.get(key))
        if not raw_value or is_placeholder(raw_value):
            if required:
                reporter.blocked("REFERENCE", f"{key} is required for lifecycle {status}")
            continue
        try:
            relative = validate_repo_path(raw_value)
        except GovernanceError as exc:
            reporter.blocked("REFERENCE", f"{key}: {exc}")
            continue
        if required and not (root / relative).exists():
            reporter.blocked("REFERENCE", f"{key} does not exist: {relative}")

    if is_actionable(active):
        if not as_string_list(active.get("allowed_paths")):
            reporter.blocked("ALLOWLIST", "allowed_paths cannot be empty after PLAN_APPROVED")
        if not as_string_list(active.get("forbidden_paths")):
            reporter.blocked("FORBIDDEN_PATHS", "forbidden_paths cannot be empty after PLAN_APPROVED")
        if not as_string_list(active.get("required_tests")):
            reporter.blocked("REQUIRED_TESTS", "required_tests cannot be empty after PLAN_APPROVED")
        if not as_string_list(active.get("required_evidence")):
            reporter.blocked("REQUIRED_EVIDENCE", "required_evidence cannot be empty after PLAN_APPROVED")

    base = active.get("base_commit")
    candidate = active.get("candidate_commit")
    reviewed = active.get("reviewed_commit")
    if is_actionable(active):
        if not is_full_sha(base) or not ref_is_commit(root, scalar_text(base)):
            reporter.blocked("BASE_SHA", "base_commit must identify an existing full commit SHA")
    if status in {"GATE_PASS", "MERGED", "CLOSED"}:
        if not is_real_sha(candidate) or not ref_is_commit(root, scalar_text(candidate)):
            reporter.blocked("CANDIDATE_SHA", "candidate_commit must identify an existing commit")
        if not is_real_sha(reviewed) or not ref_is_commit(root, scalar_text(reviewed)):
            reporter.blocked("REVIEWED_SHA", "reviewed_commit must identify an existing commit")
        if is_real_sha(candidate) and is_real_sha(reviewed):
            if resolve_commit(root, scalar_text(candidate)) != resolve_commit(root, scalar_text(reviewed)):
                reporter.blocked("REVIEW_BINDING", "GATE_PASS must bind to the candidate commit")

    reason = scalar_text(active.get("blocking_reason"))
    if "GOVERNANCE_CONFLICT" in reason.upper():
        reporter.blocked("GOVERNANCE_CONFLICT", reason)


def validate_transition(old_status: str, new_status: str) -> bool:
    old = old_status.upper()
    new = new_status.upper()
    return old == new or new in LIFECYCLE_TRANSITIONS.get(old, set())


def scan_conflict_markers(root: Path, paths: Iterable[str] | None = None) -> list[str]:
    findings: list[str] = []
    candidates = sorted(set(paths if paths is not None else all_repository_files(root)))
    for relative in candidates:
        file_path = root / normalize_repo_path(relative)
        if not file_path.is_file():
            continue
        try:
            if file_path.stat().st_size > 2_000_000:
                continue
            raw = file_path.read_bytes()
        except OSError:
            continue
        if b"\0" in raw:
            continue
        text = raw.decode("utf-8", errors="replace")
        for number, line in enumerate(text.splitlines(), 1):
            if _CONFLICT_RE.fullmatch(line):
                findings.append(f"{normalize_repo_path(relative)}:{number}")
    return findings


def _case_exact_exists(root: Path, relative: str) -> bool:
    current = root
    for part in PurePosixPath(relative).parts:
        if part in {"", "."}:
            continue
        try:
            names = {entry.name for entry in current.iterdir()}
        except OSError:
            return False
        if part not in names:
            return False
        current = current / part
    return current.exists()


def internal_link_issues(root: Path, paths: Iterable[str] | None = None) -> list[str]:
    issues: list[str] = []
    if paths is None:
        candidates = [path for path in all_repository_files(root) if is_governance_path(path)]
    else:
        candidates = list(paths)
    for relative in sorted(set(candidates)):
        if not relative.lower().endswith(".md"):
            continue
        source = root / relative
        if not source.is_file():
            continue
        try:
            text = source.read_text(encoding="utf-8-sig")
        except (OSError, UnicodeDecodeError):
            continue
        for match in _MARKDOWN_LINK_RE.finditer(text):
            raw_target = match.group(1).strip()
            if raw_target.startswith("<") and raw_target.endswith(">"):
                raw_target = raw_target[1:-1]
            target = raw_target.split(maxsplit=1)[0].strip("'\"")
            if not target or target.startswith(("#", "http://", "https://", "mailto:", "app://")):
                continue
            if "<" in target or ">" in target or "REPLACE_" in target:
                continue
            target = unquote(target.split("#", 1)[0].split("?", 1)[0])
            if target.startswith("/"):
                combined = normalize_repo_path(target)
            else:
                combined = (PurePosixPath(relative).parent / target).as_posix()
            resolved = posixpath.normpath(combined.replace("\\", "/")).lstrip("/")
            if resolved == ".." or resolved.startswith("../"):
                issues.append(f"{relative}: link escapes repository: {raw_target}")
            elif not _case_exact_exists(root, resolved):
                issues.append(f"{relative}: missing or case-mismatched link: {raw_target}")
    return issues


def ambiguous_approval_lines(text: str, source: str) -> list[str]:
    findings: list[str] = []
    patterns = (
        re.compile(r"^\s*[A-Za-z _-]*(?:approval|decision|status)[A-Za-z _-]*:\s*['\"]?approved['\"]?\s*$", re.I),
        re.compile(r"^\s*[-*]?\s*\*\*[^*]*(?:approval|decision|status)[^*]*\*\*\s*:\s*approved\s*$", re.I),
    )
    for number, line in enumerate(text.splitlines(), 1):
        if any(pattern.fullmatch(line) for pattern in patterns):
            findings.append(f"{source}:{number}")
    return findings


def owner_approval_reference(active: dict[str, Any], contract_text: str = "") -> str | None:
    value = get_first(
        active,
        "owner_approval_reference",
        "human_approval.reference",
        "governance_change_approval.reference",
        "governance_change_approval_ref",
        "approval.owner_reference",
    )
    if value is not None and not is_placeholder(value) and not is_vague_approval(value):
        return scalar_text(value)
    match = re.search(
        r"(?im)^\s*(?:[-*]\s*)?(?:\*\*)?(?:owner|human)(?: governance)? approval reference(?:\*\*)?\s*:\s*(.+?)\s*$",
        contract_text,
    )
    if match:
        candidate = match.group(1).strip().strip("`* ")
        if not is_placeholder(candidate) and not is_vague_approval(candidate):
            return candidate
    return None


def extract_markdown_list(text: str, heading_terms: Sequence[str]) -> list[str]:
    """Extract bullet/code values from a matching Markdown section."""

    in_section = False
    values: list[str] = []
    terms = tuple(term.casefold() for term in heading_terms)
    for line in text.splitlines():
        if line.startswith("#"):
            heading = line.lstrip("#").strip().casefold()
            if in_section:
                break
            in_section = any(term in heading for term in terms)
            continue
        if not in_section:
            continue
        match = re.match(r"^\s*[-*]\s+(?:\[[ xX]\]\s*)?(.+?)\s*$", line)
        if match:
            value = match.group(1).strip().strip("`")
            if value and not value.startswith("<"):
                values.append(normalize_repo_path(value))
    return values


def extract_reviewed_sha(text: str) -> str | None:
    try:
        metadata, _ = parse_front_matter_text(text)
    except FrontMatterError:
        metadata = {}
    value = get_first(metadata, "reviewed_commit", "review.reviewed_commit", "reviewed_commit_sha")
    if is_real_sha(value):
        return scalar_text(value)
    match = re.search(
        r"(?im)^\s*(?:[-*]\s*)?(?:\*\*)?reviewed commit(?: sha)?(?:\*\*)?\s*:\s*`?([0-9a-f]{7,40})`?\s*$",
        text,
    )
    return match.group(1) if match else None


def flatten_values(value: Any) -> Iterator[Any]:
    if isinstance(value, dict):
        for child in value.values():
            yield from flatten_values(child)
    elif isinstance(value, list):
        for child in value:
            yield from flatten_values(child)
    else:
        yield value


def unresolved_placeholders(active: dict[str, Any]) -> list[str]:
    if not is_actionable(active):
        return []
    critical_paths = (
        "plan_approved_by",
        "plan_approval_ref",
        "assigned_writer.member",
        "assigned_writer.tool",
        "assigned_writer.assigned_by",
        "assigned_writer.assigned_at",
        "assigned_writer.approval_record",
        "assigned_reviewer.member",
        "assigned_reviewer.tool",
        "assigned_reviewer.assigned_by",
        "assigned_reviewer.assigned_at",
        "assigned_reviewer.approval_record",
        "contract_path",
        "base_commit",
    )
    result = [path for path in critical_paths if is_placeholder(get_nested(active, path))]
    if lifecycle(active) in {"READY_FOR_REVIEW", "GATE_PASS", "MERGED", "CLOSED"}:
        for path in ("handoff_path", "evidence_path"):
            if is_placeholder(get_nested(active, path)):
                result.append(path)
    if lifecycle(active) in {"GATE_PASS", "MERGED", "CLOSED"}:
        for path in ("candidate_commit", "reviewed_commit", "review_path"):
            if is_placeholder(get_nested(active, path)):
                result.append(path)
    return result
