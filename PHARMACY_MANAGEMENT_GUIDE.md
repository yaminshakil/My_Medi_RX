# Pharmacy Management Guide

This guide explains how Medi Rx handles pharmacy onboarding, inventory management, prescription fulfillment, and billing. Use it with the operational rules in your organization and the system's pharmacy APIs.

## 1. Overview

The pharmacy module supports four linked stages:

1. Pharmacy registration and approval
2. License verification and operational review
3. Inventory management and stock controls
4. Prescription-order receiving, processing, and dispensing

A pharmacy cannot receive patient prescription orders until it is approved and associated with an active pharmacy account. Admin users manage the approval lifecycle, while pharmacy staff manage inventory and fulfillment.

## 2. Roles and access

### Admin
- Review new pharmacy registrations
- Verify pharmacy profile details and address
- Review pharmacy license submissions
- Approve, reject, or suspend pharmacies
- View pharmacy-wide inventory and stock health
- Manage cross-pharmacy inventory reporting

### Pharmacy owner / pharmacy staff
- Maintain the pharmacy profile and address
- Upload and review licenses
- Manage medicine inventory
- Adjust stock quantities
- Receive and fulfill prescription orders
- Download bills for dispensed orders

### Patient
- Search for approved pharmacies
- Share a valid issued prescription with a selected pharmacy
- Review the resulting order status

## 3. Register and approve a pharmacy

### Registration
1. Sign in with an admin account and open the pharmacy management area.
2. Create or assign the pharmacy owner user.
3. Record the pharmacy name, contact details, description, and address.
4. Save the record. The pharmacy is created with status `pending` and placed in the review queue.

### Verification workflow
Admin review includes:
- Pharmacy profile completeness and contact details
- Address accuracy
- Ownership association
- Required pharmacy licenses
- Current verification status

A pharmacy can only be moved to `approved` when:
- it has at least one verified, current license, and
- all pending licenses have been reviewed.

If a pharmacy does not meet the requirement, the admin sees a validation error and the status remains unchanged.

### Approval status values
- `pending` — new registration awaiting review
- `approved` — pharmacy can receive and process orders
- `rejected` — pharmacy was not accepted
- `suspended` — pharmacy is temporarily blocked

Approved pharmacies can appear in the patient directory and receive shared prescriptions.

## 4. License management

Each pharmacy can have multiple license records. Licenses must be reviewed and marked as verified before the pharmacy can be approved.

Typical license review actions include:
- record the license number
- select the license type
- upload the supporting document
- set expiry date
- verify or reject the document

The system enforces the rule that approved pharmacies must have a valid current license on file. This prevents approval of an unlicensed or expired pharmacy.

## 5. Inventory management

The pharmacy inventory screen gives each pharmacy a central control point for stock. It supports:
- item search by medicine name, generic name, strength, or pharmacy name
- stock-status filters (`all`, `low`, `out`, `expiring`, `expired`)
- per-page paging
- stock summary cards for total items, total units, low stock, and expiring inventory

### Inventory record fields
Each inventory item stores:
- `pharmacy_id`
- `medicine_name`
- `generic_name`
- `strength`
- `regular_price`
- `discount_percentage`
- `stock_quantity`
- `reorder_level`
- `expiry_date`

### Pricing rules
- Prices are recorded per unit
- The application currency is pulled from system settings
- `discount_percentage` must be between 0 and 100
- The discounted selling price is calculated when the bill is generated
- Price values are snapshotted when dispensed so historical bills remain accurate

### Reorder and stock alerts
- `low stock` occurs when `stock_quantity <= reorder_level`
- `out of stock` occurs when quantity is 0
- `expiring soon` covers items expiring within the next 30 days
- `expired` covers items past the expiry date

### Add, edit, and adjust stock
Pharmacy staff can:
- create a new inventory item
- update medicine details and pricing
- adjust stock up or down with a quantity value
- remove items if required by policy

Stock adjustments should be used when medicines are received, returned, or corrected after a counting error. This keeps the item record aligned with the physical stock on hand.

## 6. CSV import and export

Pharmacy staff can import and export inventory data from the admin inventory management screen.

### Required CSV columns
The template uses this header order:

`pharmacy_id, medicine_name, generic_name, strength, regular_price, discount_percentage, stock_quantity, reorder_level, expiry_date`

### Import behavior
- Imports validate every row before changing records
- A matching item is identified by pharmacy, medicine name, generic name, and strength
- Matching stock levels are replaced with the CSV quantity
- Other item details and pricing values are updated from the CSV
- Pharmacy users can only import records for their own pharmacy
- Admin users may import records for multiple pharmacies

### Export behavior
- Inventory can be exported to a CSV template for review and bulk updates
- Keep the column order and headers unchanged to avoid import validation failures

## 7. Prescription sharing and pharmacy orders

The pharmacy order process begins when a patient shares an issued prescription with a pharmacy.

### Step 1: Doctor creates the prescription
A doctor creates a prescription with patient details, symptoms, prescribed medicines, dosage, and follow-up information. The prescription must be marked `issued` before it can be shared with a pharmacy.

### Step 2: Patient selects an approved pharmacy
The patient must use an authenticated patient account and call the pharmacy directory API to view approved pharmacies. Then the patient shares the issued prescription using the prescription UUID and selected `pharmacy_id`.

The system validates that:
- the prescription belongs to the signed-in patient
- the prescription status is `issued`
- the prescription has not already been shared
- the selected pharmacy is approved

A successful request creates a pharmacy order with order status `received`.

### Step 3: Pharmacy receives and processes the order
Approved pharmacy staff can list new orders and then start processing the request. Orders may move from `received` to `processing` before dispensing.

### Step 4: Pharmacy dispenses medicine
The pharmacy selects inventory items from its own stock and submits the quantity to dispense. The system validates each item ID and ensures enough stock is available.

If the stock is sufficient:
- the dispense is recorded
- inventory is deducted
- the order status changes to `dispensed`
- a PDF bill is generated for the pharmacy

## 8. Billing and invoice generation

The system creates a bill immediately after a successful dispense.

### Bill contents
The generated bill includes:
- pharmacy name, address, phone, and email
- patient and prescription details
- dispensed inventory items
- regular unit price
- discount percentage
- discounted unit price
- totals

### Important pricing rule
The pricing snapshot is captured at dispense time. Later price changes in inventory are not applied retroactively to an existing bill. This preserves an accurate record of what the patient was charged at the time of fulfilment.

## 9. Operational checklist

Use this checklist before handing the module to daily users:

- Pharmacy owner accounts are assigned correctly
- Approved pharmacies have at least one current verified license
- Inventory re-order thresholds are set for each medicine
- Price fields are filled in consistently
- Expiry dates are reviewed regularly
- Stock quantities are reconciled with physical inventory
- PDF bill access is restricted to the authorized pharmacy

## 10. Troubleshooting

### Prescription cannot be shared
Check that:
- the prescription is in `issued` status
- the prescription belongs to the patient account making the request
- the prescription was not already shared
- the pharmacy is approved

### Pharmacy cannot receive orders
Check that:
- the user is authenticated as a pharmacy user
- the pharmacy is approved
- the pharmacy has valid current licenses

### Dispensing fails
Check that:
- the inventory item belongs to the same pharmacy
- the quantity does not exceed available stock
- the selected item is valid and active

### Inventory import fails
Check that:
- headers and column order match the import template
- values are valid numeric values where required
- pharmacy IDs and medicines match the pharmacy's inventory record set

## 11. API route summary

| Action | Method and route | Account |
|---|---|---|
| List approved pharmacies | `GET /api/v1/patient/pharmacies` | Patient |
| Share prescription with pharmacy | `POST /api/v1/patient/prescriptions/{prescription-uuid}/pharmacy` | Patient |
| List pharmacy orders | `GET /api/v1/pharmacies/prescriptions` | Approved pharmacy |
| Start processing order | `PATCH /api/v1/pharmacies/prescriptions/{order-id}/process` | Approved pharmacy |
| Dispense order | `PATCH /api/v1/pharmacies/prescriptions/{order-id}/dispense` | Approved pharmacy |
| Download order bill | `GET /api/v1/pharmacies/prescriptions/{order-id}/bill` | Approved pharmacy |
| List inventory | `GET /api/v1/pharmacies/inventory` | Pharmacy |

All pharmacy API requests require a valid bearer token. Never share a patient token or use another pharmacy's inventory item IDs.

## 12. Recommended operational practice

The pharmacy module works best when the pharmacy staff keeps the following disciplines in place:

- review expiry dates weekly
- update reorder levels after seasonal demand changes
- reconcile inventory at the end of each shift
- verify license expiry before renewing approvals
- maintain a clear audit trail for stock adjustments and dispenses

This keeps the module aligned with legal compliance, financial accuracy, and patient safety.
