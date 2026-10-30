# Prescription and Pharmacy Order Guide

## What is the difference?

A **prescription** is created by a doctor for a patient. A **pharmacy order** is created when that patient shares an issued prescription with an approved pharmacy. Creating a prescription in the doctor panel does not, by itself, send an order to a pharmacy.

## 1. Create a prescription as a doctor

1. Sign in with a doctor account and open **Prescriptions → Create Prescription**.
2. Select the patient and their appointment.
3. Enter the follow-up date and the clinical information, including symptoms and at least one medication. For each medication, provide the medicine, strength, dosage, and duration. Add optional vitals, instructions, examination findings, investigations, and follow-up advice as needed.
4. The create form defaults the prescription status to **Issued**. Keep it issued if it should be available to share; a draft prescription cannot be shared with a pharmacy.
5. Submit the form to save the prescription. The prescription has a UUID that is used when sharing it with a pharmacy. The prescription PDF can be opened from the prescription's PDF link.

The form requires a patient, appointment, follow-up date, symptoms, and at least one medication.

## 2. Create the pharmacy order as the patient

The patient must be signed in through the API with a patient account. First, get the list of approved pharmacies:

```http
GET /api/v1/patient/pharmacies
Authorization: Bearer <patient-token>
Accept: application/json
```

Choose a pharmacy `id` from the response, then share the issued prescription using its UUID:

```http
POST /api/v1/patient/prescriptions/{prescription-uuid}/pharmacy
Authorization: Bearer <patient-token>
Accept: application/json
Content-Type: application/json

{
  "pharmacy_id": 123
}
```

On success, the API returns HTTP `201` with the new order's `id`, `status` (`received`), and `received_at`. This is the step that creates the pharmacy order.

The prescription must belong to the signed-in patient, have status `issued`, and not already have been shared with a pharmacy. The selected pharmacy must be approved. A prescription can only be shared once.

## 3. Process and dispense the order as a pharmacy

Use an authenticated, approved pharmacy account for these API calls.

List orders received by the signed-in pharmacy:

```http
GET /api/v1/pharmacies/prescriptions
Authorization: Bearer <pharmacy-token>
Accept: application/json
```

An order can optionally be moved from `received` to `processing`:

```http
PATCH /api/v1/pharmacies/prescriptions/{order-id}/process
Authorization: Bearer <pharmacy-token>
Accept: application/json
```

To dispense, use inventory item IDs belonging to that pharmacy. Retrieve them with `GET /api/v1/pharmacies/inventory`, then submit the dispensed items:

```http
PATCH /api/v1/pharmacies/prescriptions/{order-id}/dispense
Authorization: Bearer <pharmacy-token>
Accept: application/json
Content-Type: application/json

{
  "items": [
    {
      "inventory_item_id": 456,
      "quantity": 10
    }
  ]
}
```

Each item needs a distinct inventory item ID and a positive quantity. If the pharmacy has sufficient stock, the API records the dispense, deducts the quantities from inventory, and changes the order status to `dispensed`. Orders in either `received` or `processing` status can be dispensed. Insufficient stock prevents dispensing.

## Troubleshooting

- **Prescription cannot be shared:** confirm it is issued, belongs to the patient making the request, and has not already been shared.
- **Pharmacy is unavailable:** the pharmacy must be approved to appear in the patient directory and receive orders.
- **Pharmacy cannot access orders:** confirm the request uses an authenticated Pharmacy account and that the pharmacy is approved.
- **Dispensing fails:** check that every inventory item belongs to the signed-in pharmacy and has enough stock for the requested quantity.

## API route summary

| Action | Method and route | Account |
|---|---|---|
| List approved pharmacies | `GET /api/v1/patient/pharmacies` | Patient |
| Share prescription and create order | `POST /api/v1/patient/prescriptions/{prescription-uuid}/pharmacy` | Patient |
| List pharmacy orders | `GET /api/v1/pharmacies/prescriptions` | Approved Pharmacy |
| Start processing | `PATCH /api/v1/pharmacies/prescriptions/{order-id}/process` | Approved Pharmacy |
| Dispense order | `PATCH /api/v1/pharmacies/prescriptions/{order-id}/dispense` | Approved Pharmacy |
| List pharmacy inventory | `GET /api/v1/pharmacies/inventory` | Pharmacy |

All API routes require a valid bearer token. Do not share a patient's token or use another pharmacy's inventory IDs.
