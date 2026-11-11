# Blood Bank Module — Operator Guide

This guide explains how to use the Blood Bank screens in Medi Rx. It describes the software workflow; it is **not** a clinical protocol. Follow your hospital's approved donor-screening, patient-identification, compatibility-testing, storage, issue, and transfusion procedures. The software's compatibility suggestions do not replace laboratory crossmatching or clinical judgment.

## 1. Access and preparation

1. Sign in with an account that is linked to a hospital, or an application administrator account.
2. Open `/blood-bank` in the same application. Use the tabs at the top to open **Overview**, **Blood units**, **Issue log**, **Emergency requests**, or **Donors**.
3. Before operating the module, ask your system administrator to confirm that the correct hospital and location are configured, active storage units exist, and the patient and physician records needed by staff are available.
4. For donor broadcasts, the hospital needs latitude and longitude. Donor records need accurate locations and a working email address or a linked account with a registered Firebase device token. The relevant queue worker must be running for queued delivery.
5. The module's expiry and emergency-request cleanup runs through the Laravel scheduler. Your administrator should ensure the scheduler is running in production.

## Patient blood-bank and donor network

1. Patients open **Blood bank & donors** from their dashboard. The directory lists verified hospitals that have an active storage unit assigned to them. The map uses the hospital's saved coordinates; patients can call the hospital directly from its listing.
2. The live availability table counts only available, unexpired units in active storage units assigned to that hospital. Existing storage units have no hospital assignment until staff select one in **Storage units**; assign existing units before relying on patient-facing counts.
3. Patients can join the donor network with their blood group, contact number, and location. A patient must explicitly opt in, and an authorized staff member must verify the profile before it can receive alerts. Donors can pause or resume alerts from the same screen.
4. To request blood, patients select a listed blood bank, blood group, component, urgency, and search radius. The request searches for compatible, verified, available donors near that bank. It does not replace a clinician's assessment or laboratory crossmatch.
5. Donors with a linked account and registered Firebase device token receive a push alert; email is also sent when available. Queued notifications require a running queue worker and valid Firebase configuration for push delivery.
6. Patients see accepted donors in their request history and can call them from the app. A donor's phone number is not shown to the patient until that donor accepts. Patients can cancel their own open requests.

## 2. Overview dashboard

The **Overview** shows available stock by blood group and component, units nearing expiry, open emergency requests, and units issued today. It also links to recently issued units.

- Stock colors indicate healthy, low, or critical stock. With the default thresholds, low stock is 10 units (6 for platelets) or fewer; critical stock is 4 units (2 for platelets) or fewer.
- The dashboard refreshes stock automatically. It uses live updates when broadcasting is configured and otherwise polls about once per minute.
- **Register unit** starts a stock entry. **Issue a unit** starts the issue workflow.
- A **Request donors** action appears on critical stock cells and opens an urgent donor request with default settings.

## 3. Register a blood unit

1. Open **Blood units** and choose **Register unit**.
2. Enter the bag/barcode number, batch number, blood group, component, volume, collection date and time, and storage unit.
3. Enter the expiry date from the approved product label or local procedure. If left blank, the module calculates a suggested shelf life from collection time: whole blood 35 days, packed red cells 42 days, platelets 5 days, or plasma 365 days. Always verify the resulting date against the actual product and storage requirements.
4. Select **Quarantine** when the unit must not yet be available for issue. Submit the form.
5. Find the unit in the **Blood units** list to review its status, location, and expiry.

Bag numbers must be unique. Volume must be between 10 and 1,000 ml. Only active storage units can be selected.

### Quarantine, release, and discard

- Quarantined units are excluded from available stock. After the required screening and release authorization have been completed outside this screen, use **Release** to make a quarantined unit available.
- Use **Discard** for an eligible unit that must be removed from stock. Enter a reason; the record is retained.
- Issued units cannot be discarded through this screen. The issue log is an audit trail and has no delete action.
- Use the list filters to search by bag or batch number and narrow results by group, component, status, storage unit, or expiry.

## 4. Issue a unit to a patient

1. Open **Issue log** and choose **Issue a unit**.
2. Search for the recipient by name, medical record number (MRN), or clinical file number, then select the correct patient. Confirm the displayed identity and recorded blood group against your hospital's identification procedure.
3. Select the required component. The module lists available, unexpired units and orders them by expiry. When the patient's group is recorded, it initially shows units compatible according to the module's configured rules.
4. Select the correct unit. If no suitable unit appears, check the patient record, component, and stock status; do not bypass clinical checks.
5. Enter the attending physician and clinical indication. Ward/bed is optional.
6. If authorized staff deliberately select an incompatible unit—or the patient's group is not recorded—the form requires an override reason. This reason is stored permanently in the issue record. Use an override only under hospital policy and with the required clinical authorization.
7. Submit **Issue unit**. The unit becomes issued and the system records the recipient, physician, issuing user, time, indication, and any override reason. A unit cannot be issued twice.
8. Open the resulting issue record or find it in **Issue log**. After bedside care, record transfusion start/end times and whether a reaction occurred. If a reaction is recorded, enter the required reaction details, then save the outcome.

The compatibility list is an aid, not a crossmatch result. Whole blood and packed red cells use red-cell compatibility rules; platelets and plasma use different rules. Confirm all products using your hospital's approved transfusion process.

## 5. Manage donors

1. Open **Donors** and choose **Add donor**.
2. Enter the donor's name, blood group, and accurate latitude/longitude. Phone, email, city, and last donation date can also be recorded.
3. Use **Use my current location** only if the browser/device is physically at the donor's location. Otherwise enter the donor's actual coordinates.
4. Save the donor. New donors are unverified and are not eligible for emergency requests until an authorized staff member confirms the record and selects **Verify**.
5. Use search and the blood-group/verification filters to find donor records. **Remove** removes verification.

Emergency alerts use email when an email address is present, and may also use Firebase push for donors with linked accounts and registered device tokens (unless another channel is configured). Keep contact details accurate and verify donor consent and availability under local policy. Patients manage their own alert availability in the patient donor network.

## 6. Send and follow an emergency donor request

1. Open **Emergency requests** and enter the patient's blood group, component, number of units (1–50), urgency, search radius, and an optional note for donors.
2. The default radius is 15 km; the configured maximum is 100 km. Confirm the hospital's location and the radius before sending.
3. Choose **Broadcast to donors** and confirm the prompt. The system creates a request and alerts eligible nearby donors. By default, eligibility requires a verified and available donor, a compatible blood group, usable location coordinates within the radius, and at least 56 days since the donor's last whole-blood donation.
4. Open the request reference to review the number of donors alerted, their distance, and their pending/accepted/declined responses. Donors can respond through the signed links in their email; no donor login is required. Requests accept replies for up to 24 hours by default.
5. Coordinate directly with donors and clinical staff; an accepted response is not confirmation that a donation has been completed or that blood is ready for use.
6. When the need is met, choose **Mark fulfilled**. If the request is no longer needed, choose **Cancel request**. Closing a request stops further responses, but the current interface does not send donors a cancellation notification—contact them separately when appropriate.

If no eligible donors are found, the request detail page says so. Check the radius, hospital and donor coordinates, donor verification/availability, group compatibility, donation date, and email configuration before creating a new request with a wider radius. An increased radius cannot exceed the configured maximum.

## 7. API access

Base URL: `/api/v1/blood-bank`. Every endpoint below requires an `auth:api` bearer token and blood-bank access. Send `Accept: application/json`; send `Content-Type: application/json` with JSON request bodies. Replace example IDs with your actual record IDs. The signed donor accept/decline links remain on the web route; they are not staff API endpoints.

Successful single-record and dashboard responses use this envelope:

```json
{
  "success": true,
  "message": "Blood unit registered.",
  "data": {}
}
```

Paginated list responses use `data` as an array and include page metadata:

```json
{
  "success": true,
  "message": "Results loaded.",
  "data": [],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "last_page": 1,
    "total": 0
  }
}
```

Send this header on every request:

```http
Accept: application/json
```

Authenticate using the bearer token returned by your application's API authentication flow in the `Authorization` header. For requests with a JSON body, also send `Content-Type: application/json`.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/dashboard` | Stock matrix, summary, expiring units, and recent issues |
| GET | `/lookups` | Blood groups, components, statuses, urgency levels, physicians, and active storage units |
| GET | `/patients?search=...` | Search patients by name, MRN, or clinical file number |
| GET, POST | `/bags` | List/filter blood bags or register a unit |
| PATCH | `/bags/{bag}/release` | Release a quarantined unit |
| PATCH | `/bags/{bag}/discard` | Discard an eligible unit; JSON body requires `reason` |
| GET | `/issues` | List/filter transfusion issue records |
| GET | `/issues/available-bags?component=...&patient_id=...` | Find eligible units for an issue; `include_all=1` includes incompatible groups |
| POST | `/issues` | Issue a unit to a patient and record the attending physician |
| GET | `/issues/{issue}` | Read an issue and transfusion outcome |
| PATCH | `/issues/{issue}/outcome` | Record transfusion start/end and reaction details |
| GET, POST | `/emergency` | List requests or broadcast an emergency donor request |
| GET | `/emergency/{emergencyRequest}` | Read a request and invited donor responses |
| PATCH | `/emergency/{emergencyRequest}/fulfill` | Mark a request fulfilled |
| PATCH | `/emergency/{emergencyRequest}/cancel` | Cancel a request |
| GET, POST | `/donors` | List/filter donors or register a donor |
| PATCH | `/donors/{donor}/verify` | Set `verified` to `true` or `false` |

### Dashboard and lookups

```http
GET /api/v1/blood-bank/dashboard
```

The `data` object contains `stock` (the full blood-group/component matrix and totals), `summary`, `expiring`, `recent_issues`, and `emergency_defaults`. Example summary:

```json
{
  "success": true,
  "message": "Blood-bank dashboard loaded.",
  "data": {
    "summary": {
      "available_units": 24,
      "expiring_count": 2,
      "open_requests": 1,
      "issued_today": 3
    },
    "emergency_defaults": {
      "radius_km": 15,
      "max_radius_km": 100
    }
  }
}
```

```http
GET /api/v1/blood-bank/lookups
GET /api/v1/blood-bank/patients?search=MRN-1001
```

Lookups return `groups`, `components`, `statuses`, `urgencies`, `physicians`, and active `storage_units`. Patient search returns up to 20 matches with `id`, `name`, `mrn`, `clinical_file_no`, and `blood_group`.

### Register and track blood units

Register a unit with `POST /api/v1/blood-bank/bags`. `bag_number`, `batch_number`, `blood_group`, `component`, `volume_ml`, `collected_at`, and an active `storage_unit_id` are required. `donor_id`, `expires_at`, and `quarantine` are optional. If `expires_at` is omitted, the module suggests an expiry from the component shelf life. Valid groups are `A+`, `B+`, `AB+`, `O+`, `A-`, `B-`, `AB-`, and `O-`; component values are `whole_blood`, `packed_red_cells`, `platelets`, and `plasma`.

```http
POST /api/v1/blood-bank/bags
```

```json
{
  "bag_number": "BAG-2026-001",
  "batch_number": "BATCH-2026-05",
  "donor_id": 12,
  "blood_group": "O+",
  "component": "packed_red_cells",
  "volume_ml": 300,
  "collected_at": "2026-10-30T09:00:00+06:00",
  "expires_at": "2026-12-11T09:00:00+06:00",
  "storage_unit_id": 3,
  "quarantine": false
}
```

Example `201 Created` response (`id`, timestamps, expiry hours, and labels are generated):

```json
{
  "success": true,
  "message": "Blood unit registered.",
  "data": {
    "id": 41,
    "bag_number": "BAG-2026-001",
    "batch_number": "BATCH-2026-05",
    "blood_group": "O+",
    "component": "packed_red_cells",
    "component_label": "Packed red cells",
    "volume_ml": 300,
    "collected_at": "2026-10-30T09:00:00+06:00",
    "expires_at": "2026-12-11T09:00:00+06:00",
    "hours_left": 960,
    "expiry_state": "ok",
    "status": "available",
    "storage_unit": "Fridge A",
    "donor": null,
    "discard_reason": null
  }
}
```

List and filter with query parameters. Supported bag filters include `search` (bag or batch number), `blood_group`, `component`, `status`, `expiry` (`expired` or `expiring`), `storage_unit_id`, `sort`, `direction`, and `per_page`. Sort columns are restricted to `expires_at`, `collected_at`, `bag_number`, `blood_group`, and `created_at`; direction is `asc` or `desc`. List pagination defaults to 20 items (15 for emergency requests) and allows at most 100 per page.

```http
GET /api/v1/blood-bank/bags?blood_group=O%2B&component=packed_red_cells&status=available&per_page=10
```

Quarantine release and discard use `PATCH /api/v1/blood-bank/bags/{bag}/release` and `/discard`. Discard requires a JSON reason:

```http
PATCH /api/v1/blood-bank/bags/41/discard
```

```json
{"reason": "Temperature excursion"}
```

Both endpoints return the updated blood-bag resource in `data`. Only quarantined units can be released; issued or already-discarded units cannot be discarded.

### Search patients, find units, and issue blood

Search patients by name, MRN, or clinical file number:

```http
GET /api/v1/blood-bank/patients?search=MRN-1001
```

Find available units for a patient and component:

```http
GET /api/v1/blood-bank/issues/available-bags?patient_id=17&component=packed_red_cells
```

The response `data` is an array ordered with compatible units first and earliest expiry first. Each row includes `id`, `bag_number`, `batch_number`, `blood_group`, `expires_at`, `hours_left`, and `compatible`. Use `include_all=1` to include incompatible blood groups; the issue action still requires a documented override reason for an incompatible or untyped recipient.

Create an issue with `POST /api/v1/blood-bank/issues`. `blood_bag_id`, `patient_id`, `physician_id`, and `indication` are required. `ward` and `emergency_request_id` are optional.

```http
POST /api/v1/blood-bank/issues
```

```json
{
  "blood_bag_id": 41,
  "patient_id": 17,
  "physician_id": 8,
  "indication": "Surgical blood loss",
  "ward": "Ward 3",
  "emergency_request_id": null
}
```

Example `201 Created` response:

```json
{
  "success": true,
  "message": "Blood unit issued and logged.",
  "data": {
    "id": 9,
    "issue_number": "ISS-261101-X7K2P",
    "issued_at": "2026-11-01T10:15:00+06:00",
    "bag": {
      "id": 41,
      "bag_number": "BAG-2026-001",
      "batch_number": "BATCH-2026-05",
      "blood_group": "O+",
      "component_label": "Packed red cells",
      "storage_unit": "Fridge A"
    },
    "patient": {
      "id": 17,
      "name": "Example Patient",
      "mrn": "MRN-1001",
      "clinical_file_no": "CF-1001",
      "blood_group": "O+"
    },
    "physician": {
      "id": 8,
      "name": "Dr. Example",
      "department": "Surgery"
    },
    "issued_by": "Blood Bank",
    "ward": "Ward 3",
    "indication": "Surgical blood loss",
    "compatibility_override_reason": null,
    "transfusion_started_at": null,
    "transfusion_completed_at": null,
    "reaction_noted": false,
    "reaction_notes": null
  }
}
```

If compatibility needs an override, add `compatibility_override_reason` to the issue payload. The reason is retained in the audit record. A unit can only be issued once and cannot be expired.

Record the bedside outcome with `PATCH /api/v1/blood-bank/issues/{issue}/outcome`:

```json
{
  "transfusion_started_at": "2026-11-01 10:30:00",
  "transfusion_completed_at": "2026-11-01 12:00:00",
  "reaction_noted": false
}
```

When `reaction_noted` is `true`, `reaction_notes` is required. The endpoint returns the updated issue resource. Use `GET /issues` to search/filter the audit log (`search`, `component`, `physician_id`, `from`, `to`, `per_page`) and `GET /issues/{issue}` to retrieve one record.

For example, after saving an outcome the response includes the updated outcome fields:

```json
{
  "success": true,
  "message": "Transfusion outcome saved.",
  "data": {
    "id": 9,
    "issue_number": "ISS-261101-X7K2P",
    "transfusion_started_at": "2026-11-01T10:30",
    "transfusion_completed_at": "2026-11-01T12:00",
    "reaction_noted": false,
    "reaction_notes": null
  }
}
```

### Register donors and broadcast emergencies

Register donors with `POST /api/v1/blood-bank/donors`. `name`, `blood_group`, `latitude`, and `longitude` are required; `phone`, `email`, `city`, and `last_donated_at` are optional. Donors start unverified and must be verified before eligibility for broadcasts.

```http
POST /api/v1/blood-bank/donors
```

```json
{
  "name": "Example Donor",
  "phone": "+8801700000000",
  "email": "donor@example.test",
  "blood_group": "O+",
  "latitude": 23.781,
  "longitude": 90.407,
  "city": "Dhaka",
  "last_donated_at": "2026-07-01"
}
```

Example `201 Created` response:

```json
{
  "success": true,
  "message": "Donor registered.",
  "data": {
    "id": 12,
    "name": "Example Donor",
    "phone": "+8801700000000",
    "email": "donor@example.test",
    "blood_group": "O+",
    "latitude": 23.781,
    "longitude": 90.407,
    "city": "Dhaka",
    "last_donated_at": "2026-07-01",
    "is_verified": false,
    "verified_at": null,
    "is_available": true
  }
}
```

Verify or remove verification with `PATCH /api/v1/blood-bank/donors/{donor}/verify`:

```json
{"verified": true}
```

The response uses the same donor resource; `is_verified` becomes `true` and `verified_at` is set.

List donors with `GET /donors`; optional filters are `search`, `blood_group`, `verified`, and `per_page`.

Broadcast an emergency request with `POST /api/v1/blood-bank/emergency`. The authenticated user's hospital record must have latitude and longitude configured. Required fields are `blood_group`, `component`, `units_needed` (1–50), and `urgency` (`critical`, `urgent`, or `standard`). `radius_km` (1–100) and `note` are optional.

```http
POST /api/v1/blood-bank/emergency
```

```json
{
  "blood_group": "O+",
  "component": "packed_red_cells",
  "units_needed": 2,
  "urgency": "urgent",
  "radius_km": 15,
  "note": "Contact the blood bank desk on arrival."
}
```

Example `201 Created` response:

```json
{
  "success": true,
  "message": "Emergency request broadcast to 1 donor(s).",
  "data": {
    "id": 5,
    "reference": "EMR-261101-A1B2C",
    "hospital": "Example General Hospital",
    "blood_group": "O+",
    "component_label": "Packed red cells",
    "units_needed": 2,
    "urgency": "urgent",
    "status": "open",
    "radius_km": 15,
    "note": "Contact the blood bank desk on arrival.",
    "donors_notified_count": 1,
    "accepted_count": 0,
    "broadcast_at": "2026-11-01T10:15:00+06:00",
    "expires_at": "2026-11-02T10:15:00+06:00"
  }
}
```

If no eligible donors are found, the request is still created with `201`; `donors_notified_count` is `0` and the response message says no eligible donors were found. Eligible donors must be verified, available, compatible, nearby, and not too recently donated.

Use `GET /emergency` with optional `status` and `per_page` filters to list requests. `GET /emergency/{emergencyRequest}` returns `data.request` and invited `data.donors` (including distance and pending/accepted/declined response). Close a request with `PATCH /emergency/{emergencyRequest}/fulfill` or `/cancel`; both return the updated emergency-request resource. Only open requests can be closed.

Example request-detail response:

```json
{
  "success": true,
  "message": "Emergency request loaded.",
  "data": {
    "request": {
      "id": 5,
      "reference": "EMR-261101-A1B2C",
      "status": "open",
      "donors_notified_count": 1,
      "accepted_count": 0
    },
    "donors": [
      {
        "id": 12,
        "name": "Example Donor",
        "phone": "+8801700000000",
        "blood_group": "O+",
        "distance_km": 0.04,
        "response": "pending",
        "responded_at": null
      }
    ]
  }
}
```

### Errors and status codes

Validation errors return HTTP `422` with Laravel's `message` and field-specific `errors`, for example:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "blood_group": ["The selected blood group is invalid."]
  }
}
```

Unauthenticated requests return HTTP `401`; authenticated users without blood-bank access receive HTTP `403`. Business-rule conflicts (such as releasing a non-quarantined unit or closing an already closed request) return HTTP `409`:

```json
{
  "success": false,
  "message": "Only quarantined units can be released."
}
```

## 8. Expiry and routine checks

- Review **Expiring soon** on the dashboard and use the expiry filter in **Blood units** to plan follow-up.
- The daily expiry sweep marks expired units unavailable and expires stale emergency requests. It runs at 06:00 when the Laravel scheduler is active.
- Staff with a hospital association receive expiry notifications when units are expired or nearing expiry. Email notifications are queued, so a working queue worker is also required.
- Check physical labels, storage conditions, and local temperature logs independently; this module is not a substitute for them.

## Troubleshooting

| What you see | What to check |
|---|---|
| Access denied | Sign in and ask an administrator to confirm the account is linked to a hospital or is an application administrator. |
| No storage unit in the registration form | Ask an administrator to create or activate a storage unit. |
| Patient or physician is missing | Ask an administrator to add or correct the relevant module record. |
| No usable unit appears for issue | Confirm the selected patient and component, then check whether stock is quarantined, expired, issued, or otherwise unavailable. |
| Emergency request cannot be sent | Confirm the hospital is linked correctly and has latitude/longitude configured. |
| No donors are alerted, or email is not received | Review donor verification, availability, compatibility, donation date, coordinates, and email; confirm mail and queue services are working. |
| Expired stock or expiry email has not updated | Ask an administrator to check the scheduler, queue worker, and the latest run of `blood-bank:check-expiry`. |
