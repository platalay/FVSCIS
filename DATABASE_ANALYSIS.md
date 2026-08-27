# FVSCIS Database Analysis

**Scope:** read-only analysis of the FVSCIS MySQL application schema and source-code usage.

**Analysis date:** 27 August 2026

**Important limitation:** The application database connection works for normal application queries, but the configured MySQL account could not be used from the CLI for metadata inspection (`Access denied ... to database 'fvscis'`). A web-runtime metadata probe also returned HTTP 500 before producing metadata. Therefore, exact live `information_schema` values for column types, defaults, indexes, comments, and foreign-key constraints could not be independently exported in this pass. No schema, data, migration, or application source was changed. Items marked **DB verification required** must not be treated as confirmed facts.

## Database Overview

- Primary application database: MySQL, accessed by `mysqli` through `private/database_functions.php` and `private/initialize.php`.
- Application ORM: `DatabaseObject` in `private/classes/databaseobject.class.php`.
- External databases are separate PostgreSQL sources: e-License is initialized and used by `Elicense`/`ElicensePort`; FI connection code is currently disabled in `initialize.php`.
- The application source contains 24 concrete MySQL ORM table mappings, listed below. The runtime public certificate page previously returned 25,092 rows from `fv_sanitation_certification_old`; this confirms that table is populated and actively queryable.
- The source uses a lightweight ORM and frequently builds SQL with escaped values. It does not provide a reliable schema migration model from which DB constraints can be inferred.

### Evidence quality

| Evidence | Meaning |
|---|---|
| CONFIRMED BY DB APPLICATION QUERY | A normal application query against the table succeeded or the table is used by a working route |
| CONFIRMED BY SOURCE | Table/columns/relationship are declared or referenced in source code |
| INFERRED | Relationship follows naming and application behavior but no DB FK metadata was available |
| DB VERIFICATION REQUIRED | Must be checked with `SHOW CREATE TABLE`, `SHOW INDEX`, `DESCRIBE`, or `information_schema` using a metadata-authorized account |

## Table Inventory

The following inventory is the source-backed inventory. Exact physical PK/FK/index/type/default/comment values require the metadata access described above.

| Table Name | Purpose | Primary Key | Main Foreign Keys | Used By | Status |
|---|---|---|---|---|---|
| `inspection_requests` | Inspection request transaction | `id` (source convention) | `department_id`, `department_group_id`, `created_by`, port IDs; FK status DB verification required | `InspectionRequest`, fisherman/inspectofficer/signer endpoints | ACTIVE |
| `fv_sanitation_certification_old` | Issued/manual certificate record and status snapshot | `id` (source convention) | `fisherman_id`, `evaluation_agency`, `signing_unit`, `responsible_unit`; likely references, DB FK verification required | `FvSanitationCertificationOld`, public search, officer/signer certificate pages | ACTIVE |
| `inspection_attachments` | Request-level uploaded documents | `id` (source convention) | `request_id`, `created_by`; DB FK verification required | `InspectionAttachment`, request upload/delete endpoints | ACTIVE |
| `fv_certificate_attachments` | Certificate-level uploaded documents | `id` intended PK; historical schema defect documented | `certificate_id`, `created_by`; DB FK verification required | `FvCertificateAttachment`, manual certificate endpoints | ACTIVE / schema risk |
| `inspection_applicant_info` | Applicant and form-1 information | `id` (source convention) | `request_id`; DB FK verification required | `InspectionApplicantInfo`, applicant form endpoints and PDF generation | ACTIVE |
| `inspection_form_structure` | Structure inspection detail | `id` (source convention) | `request_id`, item references; DB FK verification required | `InspectionFormStructure`, structure autosave/table endpoints | ACTIVE |
| `inspection_form_material` | Material/equipment inspection detail | `id` (source convention) | `request_id`, item references; DB FK verification required | `InspectionFormMaterial`, material autosave/table endpoints | ACTIVE |
| `inspection_form_crew` | Crew inspection detail | `id` (source convention) | `request_id`, item references; DB FK verification required | `InspectionFormCrew`, crew autosave/table endpoints | ACTIVE |
| `inspection_form_water_and_ice` | Water and ice inspection detail | `id` (source convention) | `request_id`, item references; DB FK verification required | `InspectionFormWaterAndIce`, water/ice autosave/table endpoints | ACTIVE |
| `inspection_form_preservation` | Preservation inspection detail | `id` (source convention) | `request_id`, item references; DB FK verification required | `InspectionFormPreservation`, preservation autosave/table endpoints | ACTIVE |
| `inspection_form_status` | Per-request form/document status and token | `id` (source convention) | `request_id`; DB FK verification required | form status, PDF generation and locking | ACTIVE |
| `inspection_main_items` | Inspection checklist master | `id` (source convention) | section/category references are code-level or local values; DB verification required | inspection form pages and evaluation rules | ACTIVE |
| `inspection_fail_items` | Failure criteria/detail master | `id` (source convention) | main item references; DB FK verification required | inspection form pages and evaluation rules | ACTIVE |
| `inspection_logs` | Audit/workflow log | `id` (source convention) | `inspection_request_id`, `action_id`, `created_by`; certificate audit uses polymorphic `entity_type/entity_id` | `InspectionLog`, request log and manual certificate audit endpoints | ACTIVE |
| `log_actions` | Action-code reference/master | `id` (source convention) | referenced by `inspection_logs.action_id` in code; DB FK verification required | `LogAction`, all audit/notification-producing workflows | ACTIVE |
| `notifications` | User notification transaction | `id` (source convention) | recipient user/entity/action IDs are code-level links; DB FK verification required | `Notification`, all role dashboards and workflow endpoints | ACTIVE |
| `officer` | Officer/user account master | `id` (source convention) | `departments_id`, `usertype_id`, approved/created-by IDs; DB FK verification required | `Officer`, login, role portals and notifications | ACTIVE |
| `fisherman` | Fisherman/user account master | `id` (source convention) | none confirmed; certificate/request links use IDs in code | `Fisherman`, login, fisherman portal and requests | ACTIVE |
| `user_types` | User role/type reference | `id` (source convention) | referenced by `officer.usertype_id` in code; DB FK verification required | `UserType`, login role mapping/admin screens | ACTIVE |
| `departments` | Evaluation/operating department master | `id` (source convention) | `parent_department`, `data_owner_id`; DB FK verification required | `Department`, officer scopes, request/certificate scope | ACTIVE |
| `department_groups` | Department/signing/responsible-unit master | `id` (source convention) | `officer_id`, `responsible_unit`; DB FK verification required | `DepartmentGroup`, department and signer scope logic | ACTIVE |
| `document_counters` | Document-number sequence transaction/master | `id` (source convention) | none confirmed | `DocumentCounter`, certificate/PDF issuance | ACTIVE |
| `province` | Geographic reference | `id` (source convention) | none confirmed | `Province`, port forms and lookups | ACTIVE |
| `amphur` | Geographic reference | `id` (source convention) | province reference by code or field; DB FK verification required | `Amphur`, port forms and lookups | ACTIVE |
| `tambon` | Geographic reference | `id` (source convention) | amphur reference by code or field; DB FK verification required | `Tambon`, port forms and lookups | ACTIVE |

### Source-declared columns

The ORM declarations are the strongest source-level column inventory currently available. They are not a substitute for the physical schema because the ORM does not prove SQL type, nullability, default, comment, generated status, or DB constraint.

- `fv_sanitation_certification_old`: `id`, `vessel_name`, `ship_code`, `fisherman_id`, `vessel_mark`, `license_number`, `license_status`, `gear_type`, `owner_name`, `certificate_number`, `request_date`, `signature_date`, `effective_date`, `expiration_date`, `status`, `vessel_status`, `evaluation_agency`, `signing_unit`, `temporary_reason`, `responsible_unit`, `certificate_status`, `remark`, `type`.
- `inspection_requests`: `id`, `ship_code`, `vessel_name`, `vessel_mark`, `license_number`, `license_status`, `gear_type`, `owner_name`, `contact_phone`, `department_id`, `department_group_id`, `data_owner_id`, `port_province_id`, `port_amphur_id`, `port_tambon_id`, `port_license_no`, `port_name`, `inspect_date_start`, `inspect_date_end`, `confirmed_inspect_date`, `is_confirm`, `confirm_agreement`, `inspection_form_type`, `cold_room_flag`, `status`, `is_manual_case`, `is_submitted`, `submitted_at`, `created_at`, `updated_at`, `created_by`, `updated_by`, `created_ip`, `updated_ip`, `approved_by`, `approved_at`, `effective_date`, `expire_at`, `approval_note`, `approved_ip`, `actual_inspect_date`, `is_complete`.
- `inspection_attachments`: `id`, `request_id`, `attachment_type`, `file_path`, `file_name`, `file_type`, `file_size`, `created_by`, `created_at`.
- `fv_certificate_attachments`: `id`, `certificate_id`, `attachment_type`, `file_path`, `file_name`, `file_type`, `file_size`, `created_by`, `created_at`.
- `departments`: `id`, `name`, `parent_department`, `data_owner_id`, address fields, `phone`, `fax`, `email`, `note`.
- `department_groups`: `id`, `name`, `note`, `officer_id`, `responsible_unit`.
- `officer`: identity/login/profile fields, `departments_id`, `usertype_id`, approval, token, and audit timestamp/IP fields.
- `fisherman`: identity/login/profile/citizen fields and audit/token fields; exact list should be copied from `Fisherman::$db_columns` during DB-authorized verification.
- `inspection_logs`: `id`, `inspection_request_id`, `entity_type`, `entity_id`, `action_id`, `note`, `old_values`, `new_values`, `created_at`, `updated_at`, `created_by`, `actor_role`, `created_ip`, `updated_ip`.
- `log_actions`: `id`, `code`, `description_th`, `description_en`, `category`, `is_visible`.
- `user_types`: `id`, `code`, `name_th`, `name_en`.
- `document_counters`: `id`, `doc_type`, `year`, `running`, `updated_at`.

The five inspection form classes, `InspectionApplicantInfo`, `InspectionMainItem`, `InspectionFailItem`, `Notification`, and geographic classes declare additional fields in their respective `private/classes/*.class.php` files. These should be included in the final dictionary only after comparing the declarations with `DESCRIBE` output.

## Table Relationships

### Confirmed or strongly evidenced application relationships

| Parent | Child | Link field | Evidence | DB FK status |
|---|---|---|---|---|
| `inspection_requests` | all five `inspection_form_*` tables | `request_id` | `find_by_request_id`, autosave/load/table endpoints | DB verification required |
| `inspection_requests` | `inspection_applicant_info` | `request_id` | `InspectionApplicantInfo::find_by_request_id` and form saves | DB verification required |
| `inspection_requests` | `inspection_attachments` | `request_id` | upload/list/delete methods | DB verification required |
| `inspection_requests` | `inspection_form_status` | `request_id` | status/token lookup and PDF generation | DB verification required |
| `inspection_requests` | `inspection_logs` | `inspection_request_id` | request workflow logging and request logs | DB verification required |
| `fv_sanitation_certification_old` | `fv_certificate_attachments` | `certificate_id` | certificate attachment list/add/delete | DB verification required |
| `inspection_logs` | `log_actions` | `action_id` | `LEFT JOIN log_actions` and `LogAction::find_by_code` | DB verification required |
| `officer` | `departments` | `officer.departments_id = departments.id` | login scope, department lookup, authorization | DB verification required |
| `officer` | `user_types` | `officer.usertype_id = user_types.id` | role mapping and admin UI | DB verification required |
| `departments` | `department_groups` | `departments.parent_department = department_groups.id` | department hierarchy lookup | DB verification required |
| `department_groups` | `department_groups` | `responsible_unit` | responsible-unit scope lookup | DB verification required |
| `inspection_requests` | `fv_sanitation_certification_old` | request ID is not stored; shared `ship_code` and approval fields carry data forward | signer approval copies request fields into certificate | No FK; code-level/data-copy relationship |
| `fisherman` | `inspection_requests` | `inspection_requests.created_by` | request creation and fisherman portal | No FK confirmed; code-level relationship |
| `fisherman` | `fv_sanitation_certification_old` | `fisherman_id` | certificate model field and signer/manual flows | DB verification required |
| `inspection_main_items` | `inspection_fail_items` | item/main-item identifier | inspection form/evaluation lookup | DB verification required |

### Master / transaction / detail classification

- **Master/reference:** `departments`, `department_groups`, `user_types`, `province`, `amphur`, `tambon`, `inspection_main_items`, `inspection_fail_items`, `log_actions`.
- **User master:** `officer`, `fisherman`.
- **Transactions:** `inspection_requests`, `fv_sanitation_certification_old`, `inspection_logs`, `notifications`, `document_counters`.
- **Detail/child:** `inspection_applicant_info`, all five `inspection_form_*` tables, `inspection_form_status`.
- **Attachments:** `inspection_attachments` (request), `fv_certificate_attachments` (certificate).
- **Legacy-named but active:** `fv_sanitation_certification_old`. The name contains `old`, but it is used by current manual certificate pages, signer views, public search, notifications, and audit functionality.

## Actual Code Usage

### ORM and classes

Each concrete `DatabaseObject` class declares a `static $table_name`. The following mappings were found in `private/classes/`:

`Amphur` -> `amphur`; `Department` -> `departments`; `DepartmentGroup` -> `department_groups`; `DocumentCounter` -> `document_counters`; `Fisherman` -> `fisherman`; `FvCertificateAttachment` -> `fv_certificate_attachments`; `FvSanitationCertificationOld` -> `fv_sanitation_certification_old`; `InspectionApplicantInfo` -> `inspection_applicant_info`; `InspectionAttachment` -> `inspection_attachments`; `InspectionFailItem` -> `inspection_fail_items`; `InspectionFormCrew` -> `inspection_form_crew`; `InspectionFormMaterial` -> `inspection_form_material`; `InspectionFormPreservation` -> `inspection_form_preservation`; `InspectionFormStatus` -> `inspection_form_status`; `InspectionFormStructure` -> `inspection_form_structure`; `InspectionFormWaterAndIce` -> `inspection_form_water_and_ice`; `InspectionLog` -> `inspection_logs`; `InspectionMainItem` -> `inspection_main_items`; `InspectionRequest` -> `inspection_requests`; `LogAction` -> `log_actions`; `Notification` -> `notifications`; `Officer` -> `officer`; `Province` -> `province`; `Tambon` -> `tambon`; `UserType` -> `user_types`.

`DatabaseObject` supplies generic `find_by_id`, `find_all`, `save`, `delete`, and query helpers. Therefore generic CRUD usage exists even where a table has few custom methods.

### Certificate workflow usage

- `public/fisherman/ajax/request_inspection.php`: inserts/updates `inspection_requests`, `inspection_applicant_info`, `inspection_attachments`, `inspection_logs`, and `notifications` for request creation.
- `public/inspectofficer/ajax/autosave_structure.php`, `autosave_material.php`, `autosave_crew.php`, `autosave_waterice.php`, `autosave_preservation.php`: save the five inspection detail tables and consult `inspection_form_status`.
- `InspectionEvaluation::check_vessel_pass()`: reads inspection detail/form data and applies PASS/FAIL/conditional rules; status transitions are stored on `inspection_requests`.
- `public/inspectofficer/generate_pdf.php` and related PDF endpoints: read `inspection_requests`, `inspection_form_status`, applicant/detail tables, departments, and write document/status/log data.
- `public/signer/ajax/approve_request.php`: reads and locks `inspection_requests`, issues a document number through `document_counters`, then creates `fv_sanitation_certification_old` from request/approval values.
- `public/signer/ajax/confirm_fail.php`: reads the request and updates failure-related request/certificate state; it contains direct SQL against `fv_sanitation_certification_old` and should be reviewed when the final dictionary is built.
- `public/inspectofficer/ajax/create_fvscisold.php`: inserts manual certificate rows and `fv_certificate_attachments`; creates manual certificate audit rows in `inspection_logs`.
- `public/inspectofficer/ajax/update_fvscisold.php`: updates working certificate rows, adds certificate attachments, and writes update audit events.
- `public/inspectofficer/ajax/delete_fvscisold.php` and `fvscisold_attachment_delete.php`: delete certificate/attachment records and write delete audit events.
- `public/inspectofficer/ajax/get_manual_certificate_audit.php`: reads `inspection_logs` joined to `log_actions` using `entity_type='manual_certificate'` and `entity_id=certificate.id`.
- `public/index.php` and `public/ajax/public_certificates.php`: read only `fv_sanitation_certification_old`; public search uses server-side count/data queries and does not call e-License.

### Status semantics

- `inspection_requests.status` is a workflow status: `pending`, `cancelled`, `inspecting`, `passed`, `failed`, `conditional`, `completed`.
- `fv_sanitation_certification_old.status` is a certificate/record status used by current code as `active`, `inactive`, `fail`, and potentially `pending`/`pass`.
- `fv_sanitation_certification_old.certificate_status` is a separate human-facing certificate type/result text such as `สร. 3`, `สร. 3 EU`, and temporary variants. It must not be treated as the same field as `status`.
- Current helper logic considers a working manual certificate to be `status='active'` and `expiration_date >= current date`. This is an application rule, not a DB constraint.
- `EXPIRED` is currently derived in UI logic from an active record whose expiration date has passed; it is not confirmed as a physical stored `status` value.

## Certificate Workflow Tables

### Core path

```text
fisherman / officer
        |
        v
inspection_requests
        |
        +--> inspection_applicant_info
        +--> inspection_attachments
        +--> inspection_form_status
        +--> inspection_form_structure
        +--> inspection_form_material
        +--> inspection_form_crew
        +--> inspection_form_water_and_ice
        +--> inspection_form_preservation
        |
        +--> inspection_logs / notifications
        |
        v
inspection evaluation: passed / conditional / failed
        |
        v
signer approval + document_counters
        |
        v
fv_sanitation_certification_old
        |
        +--> fv_certificate_attachments
        +--> inspection_logs (polymorphic manual_certificate audit)
```

### Key data observations

- Vessel identity fields are duplicated between `inspection_requests` and `fv_sanitation_certification_old`: `ship_code`, `vessel_name`, `vessel_mark`, `license_number`, `license_status`, `gear_type`, and `owner_name`. This is a deliberate snapshot/certificate pattern in current code, but there is no request FK in the certificate table.
- `fv_sanitation_certification_old.type` distinguishes manual (`0`) and online (`1`) in application convention. DB enum/check constraint was not verified.
- Certificate department scope is stored in `evaluation_agency`, `signing_unit`, and `responsible_unit`. Current audit authorization compares certificate `evaluation_agency` with authenticated officer `departments_id`.
- Attachments are split by parent aggregate: request attachments use `request_id`; certificate attachments use `certificate_id`.
- Audit logs use two models: normal request logs use `inspection_request_id`; manual certificate audit uses `entity_type='manual_certificate'`, `entity_id`, and `inspection_request_id=0`. This is a polymorphic link and can create orphan audit rows if parent IDs are deleted.

## Legacy / Unused Tables

### Legacy or legacy-named

- `fv_sanitation_certification_old`: legacy name, but **ACTIVE** in current source and runtime. Do not classify as unused solely because of `old`.
- `inspection_form_*` tables may represent the current form implementation despite older naming conventions; all five are actively referenced by forms, autosave endpoints, and PDF generation.
- `databaseobjectFi` and FI PostgreSQL support are dead/future-use code in the current bootstrap, not MySQL tables.

### Not proven unused

No MySQL table can be safely classified `UNUSED` from the available evidence. The source search found all 24 ORM tables, but a table may be accessed by raw SQL, migrations, SQL views, scheduled jobs, or code outside the searched PHP paths.

The following remain **UNKNOWN / DB verification required** rather than UNUSED:

- any physical table not represented by a current ORM class;
- old backup tables created by previous migrations;
- tables used only by deployment scripts or external jobs;
- columns declared in schema but absent from ORM declarations.

## Structural Issues

1. **Metadata visibility is insufficient for a complete dictionary.** The current account can serve application queries but could not be used to export metadata. A least-privilege metadata-read account or DBA export is required.
2. **Foreign keys are not confirmed.** Most relationships are enforced only by naming and application queries. If physical FKs are absent, deletion can leave orphan detail, attachment, notification, or log rows.
3. **Certificate/request snapshot duplication.** Vessel and owner fields are copied from request to certificate, so later changes to source data will not update the issued snapshot. This may be correct business behavior but should be documented explicitly.
4. **Polymorphic audit link.** `inspection_logs.entity_type/entity_id` has no single relational parent by design. It cannot be protected by a normal FK to both requests and certificates and needs application-level retention rules.
5. **Attachment ID history.** `FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql` documents that `fv_certificate_attachments.id` previously lacked a primary key and auto increment. Whether that migration has been applied in the live DB must be verified with `SHOW CREATE TABLE`.
6. **Status is overloaded across aggregates.** Request workflow status and certificate status use different vocabularies. UI-derived `expired` is not a stored value and should not be confused with `inactive`.
7. **Department fields are integer links without confirmed constraints.** `evaluation_agency`, `signing_unit`, and `responsible_unit` can become stale or orphaned if department rows change. Current code also has mapping logic in `Officer::map_departments_id()` used by one responsible-unit page.
8. **Potential audit orphaning on delete.** Certificate delete operations remove the business row while audit history is intentionally retained. This is useful for audit, but certificate IDs then refer to deleted parents and require retention documentation.
9. **ORM/schema drift risk.** `InspectionLog` declares `updated_by` as a property but does not include it in its declared DB column list; this and similar mismatches must be checked against `DESCRIBE` before final dictionary publication.
10. **Direct SQL alongside ORM.** Some signer and endpoint code performs direct SQL updates in addition to model persistence, increasing the risk that source declarations and actual writes diverge.
11. **No confirmed database-level status validation.** Allowed status values are implemented in PHP constants/helpers; DB enum/check constraints were not available for verification.

## Recommendations

1. Obtain a read-only metadata export from a DB account permitted to run:
   - `SHOW TABLES`
   - `DESCRIBE table_name`
   - `SHOW CREATE TABLE table_name`
   - `SHOW INDEX FROM table_name`
   - `information_schema.COLUMNS`, `STATISTICS`, `KEY_COLUMN_USAGE`, and `REFERENTIAL_CONSTRAINTS`.
2. Reconcile every physical column with each ORM `$db_columns` list and mark: source-used, raw-SQL-used, schema-only, or unknown.
3. Produce a relationship matrix with three separate flags: physical FK, application FK, and no evidence.
4. Measure orphan candidates read-only before proposing constraints:
   - attachments whose parent request/certificate does not exist;
   - applicant/form/status rows whose request does not exist;
   - department/user/action IDs with no parent;
   - audit rows whose polymorphic parent no longer exists.
5. Confirm the live state of `fv_certificate_attachments.id` before any future migration. Do not rerun the migration blindly.
6. Document snapshot semantics for certificate fields copied from requests and decide whether issued certificates are immutable records.
7. Keep public certificate search read-only and independent of e-License, as currently implemented.
8. Before adding indexes, capture query timings and current indexes from the live DB. Candidate indexes should be approved separately; no index recommendation here is an ALTER instruction.
9. Review the account's metadata permissions with the DBA rather than granting broad write access.
10. Rotate credentials found in local configuration if they have been exposed outside the local machine. This is operational security work, not a database schema change.

## Proposed Data Dictionary Structure

The next document should contain one section per physical table with:

| Field | Description |
|---|---|
| Table name | Exact physical name |
| Business purpose | Confirmed purpose and aggregate role |
| Classification | Master / reference / transaction / detail / attachment / legacy |
| Column name | Exact physical name |
| Data type | Exact `COLUMN_TYPE` |
| Nullable | `YES`/`NO` |
| Default | Exact DB default, including expression or NULL |
| Auto increment | Yes/no |
| Primary key | Key name and column order |
| Foreign key | Constraint name, parent table/column, update/delete rule |
| Indexes | Index name, uniqueness, column order, type |
| Comment | DB comment, if any |
| ORM property | Matching class/property |
| Source usage | Class, endpoint, form/modal, AJAX/API |
| Read/write usage | SELECT / INSERT / UPDATE / DELETE |
| Status vocabulary | Allowed/observed values and meaning |
| Null/legacy notes | Observed data and compatibility notes |
| Data quality risks | Orphan, duplicate, stale, or type risks |
| Evidence | DB export, source path, or runtime query |

Each table section should also include:

1. A `SHOW CREATE TABLE` excerpt or controlled metadata export reference.
2. A PK/FK/index diagram or relationship table.
3. Read/write endpoints and the exact fields they touch.
4. Observed row count and representative NULL/value distributions, collected read-only.
5. Explicit distinction between stored status and status derived by application logic.

## Final Assessment

The application source clearly shows an active certificate workflow centered on `inspection_requests`, five inspection detail tables, `inspection_form_status`, attachments, signer issuance, and `fv_sanitation_certification_old`. The current code also confirms separate request and certificate attachment aggregates and a polymorphic audit model.

A complete physical Data Dictionary cannot honestly be finalized from this environment until a metadata-authorized read-only DB export is available. This report therefore records all source-confirmed relationships and usage while leaving exact physical type/FK/index/default/comment claims explicitly unverified. No database, data, migration, or source-code changes were made.
