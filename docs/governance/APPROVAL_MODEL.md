# Mô hình phê duyệt

## Nguyên tắc

AI tạo plan chỉ có thể là `DRAFT` hoặc `PLAN_REVIEW`. Chỉ Human Project Owner mới
được chuyển thành `PLAN_APPROVED`.

“OK trong chat”, emoji, memory của agent, tên file có chữ `LOCKED` hoặc checkbox
tự đánh dấu không đủ làm approval nếu chưa được ghi canonical và truy vết được.

## Canonical approval record bắt buộc

```yaml
decision_type: PLAN_APPROVED
scope_type: CHECKPOINT
checkpoint_id: <checkpoint-id>
decided_object: <exact object being decided>
approved_by: <human identity>
authority_role: Human Project Owner
authority_tool: NOT_APPLICABLE
approved_at: <ISO-8601 timestamp>
approved_document_path: <path>
approved_document_commit: <full SHA>
contract_path: <checkpoint contract path>
base_commit: <full SHA>
candidate_commit: NOT_APPLICABLE
reviewed_commit: NOT_APPLICABLE
covers_assignments: false
allowed_next_action: <specific action>
forbidden_actions:
  - <specific forbidden action>
conditions:
  - <specific condition>
```

Mọi record cũng phải có `scope_id`, `phase_id`, `decided_object`,
`candidate_commit`, `reviewed_commit`, `authority_tool` và `limitations`.
`PLAN_APPROVED` có thể ghi `NOT_APPLICABLE` cho candidate/reviewed/tool, nhưng
không được bỏ key.

Record phải là file riêng dưới `docs/approvals/`, không được trỏ tới
`docs/approvals/TEMPLATE.md`. Thiếu identity, authority role, timestamp có
timezone, path, full SHA, conditions, forbidden actions hoặc next action thì dùng
`HUMAN_PLAN_APPROVAL_REQUIRED`.

Validator chỉ công nhận approval khi:

1. Restricted YAML front matter parse được và decision/scope đúng loại.
2. Checkpoint, approver, contract và base commit khớp ACTIVE.
3. `approved_document_commit` tồn tại, là ancestor của validation head và chứa
   đúng byte content của contract/document hiện tại.
4. Approval record tồn tại trong chính revision đang được validate.
5. Ở local, ACTIVE, contract và mọi approval/assignment record cấp quyền phải
   byte-identical với committed `HEAD`; uncommitted edit không thể tự cấp quyền.

## Freeze protocol cho checkpoint contract

Approval record không thể tham chiếu an toàn tới chính commit chưa được tạo. Vì
vậy contract và approval dùng quy trình hai commit:

1. Planning Authority hoàn thiện contract ở trạng thái **PLAN_REVIEW**.
2. Human tạo commit document candidate; commit này chưa cấp quyền execution.
3. Human review đúng byte content tại commit đó.
4. Một approval record ở commit sau trỏ approved document commit về commit
   document candidate; ACTIVE mới được chuyển thành **PLAN_APPROVED**.
5. Contract giữ nguyên **PLAN_REVIEW** như document version bất biến. Lifecycle
   vận hành tiếp theo chỉ thay đổi trong ACTIVE.

Nếu contract đổi dù chỉ một byte, approval binding thất bại và plan phải quay lại
review. Không ghi **PLAN_APPROVED** trực tiếp vào contract; đây là trạng thái
canonical của ACTIVE sau khi approval record hợp lệ đã được commit.

## Assignment approval

Mỗi `assigned_writer` và `assigned_reviewer` phải ghi đủ:

```yaml
member:
role:
tool:
assigned_by:
assigned_at:
approval_record:
role_assignment_ref:
```

Ở lifecycle actionable, metadata phải cụ thể và do Human Project Owner gán. Có
hai cách hợp lệ:

- plan approval đặt `covers_assignments: true` và snapshot Writer/Reviewer khớp
  tuyệt đối; `approval_record` của role trỏ tới plan approval; hoặc
- mỗi role dùng một record `ROLE_ASSIGNMENT_APPROVED` riêng và
  `approval_record` bằng `role_assignment_ref`.

Writer và Reviewer vẫn phải khác nhau. Tool chỉ là metadata, không phải authority.

## Governance change approval

Trong mục này, “protected governance” chỉ core policy, template, validator và CI.
Operational record như ACTIVE, checkpoint contract, approval instance, handoff,
review và evidence dùng authority/lifecycle validator riêng; chúng không cần một
approval governance thứ hai chỉ để ghi một transition hợp lệ.

Sau lần bootstrap đầu tiên, protected governance file mới hay cũ đều chỉ được
đổi khi ACTIVE trỏ tới record `GOVERNANCE_CHANGE_APPROVED` hợp lệ. Record phải có
`scope_type: GOVERNANCE` và `approved_paths` bao phủ từng changed path. Text tự do
hoặc một reference tùy ý không thay thế decision record.

## Các loại approval không thay thế nhau

- Product Vision approval không tự approve roadmap.
- Roadmap approval không tự approve phase.
- Phase approval không tự approve từng checkpoint.
- Checkpoint plan approval không phải implementation gate.
- `GATE_PASS` không phải merge/release approval.
- Merge không tự chứng minh phase đã `CLOSED`.

## Approval gắn với phiên bản

Approval áp dụng cho đúng document version/commit. Khi scope, acceptance,
architecture constraint hoặc dependency thay đổi:

1. Chuyển plan về `PLAN_REVIEW`.
2. Ghi change request và diff.
3. Human review lại.
4. Ghi approval record mới.

Không sửa tài liệu đã duyệt rồi giữ nguyên approval cũ.

## Gate review gắn với commit SHA

Review hợp lệ phải ghi:

- Candidate branch.
- Candidate commit SHA.
- Reviewed commit SHA.
- Contract/handoff/evidence path.
- Test rerun và findings.
- Gate decision.

Nếu `GATE_PASS`:

```text
This Gate PASS applies only to reviewed commit: <SHA>.
Any subsequent source change invalidates this review.
```

Source thay đổi sau review phải chuyển canonical state sang
`REVIEW_INVALIDATED` và review lại. Documentation-only change chỉ được miễn nếu
contract/reviewer xác định rõ nó không thay đổi source, build, test hay scope; nếu
không chắc, review lại.

## Release approval

Trước merge/release, Human Project Owner phải xác nhận:

- HEAD/candidate SHA đúng bằng reviewed SHA.
- CI và required evidence hợp lệ.
- Không có source change sau review.
- Scope và Human conditions đã thỏa.
- Rollback và destructive action đã được đánh giá nếu cần.

Chỉ Human thực hiện commit/merge/push/deploy/publish/release/rollback và ghi
`MERGED`/`CLOSED`.

## Baseline hiện tại

Các tài liệu chưa được theo dõi tại `techpilot/design/phase-0/` có lời khẳng định
đã `LOCKED`, nhưng chưa có canonical approval record. Chúng là planning input
`UNVERIFIED`, không phải `PLAN_APPROVED`.
