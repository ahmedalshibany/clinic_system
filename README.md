# Clinic Management System - Project Documentation

## 1. Project Overview
This is an **internal clinic management system** designed for a single clinic. It is **not** a SaaS application and is **not** multi-tenant.
The system solves the problem of managing patient data, appointments, medical records, and billing in a unified interface.
It prioritizes:
- **Fast patient processing** (check-in to check-out flow)
- **Data integrity** (strict foreign key constraints)
- **Role-based security** (clear separation of duties)

## 2. Core Principles
- **Appointment-Centric**: Almost all clinical actions (medical records, invoices) should stem from an appointment.
- **Single Clinic Scope**: No support for multiple branches or tenant isolation. Global settings apply to the entire instance.
- **Strict Typing**: Database schemas use strict enums and foreign keys. Do not bypass these constraints.
- **Separation of Concerns**: Medical data (Diagnosis, Prescriptions) is distinct from Financial data (Invoices, Payments), though they are linked via the Appointment.

## 3. User Roles
The system currently supports the following distinct roles in the database (`users` table):

- **Admin**
  - **Responsibilities**: Full system access, user management, global settings, revenue reports, deleting sensitive data.
  - **Exclusions**: Should not create medical records (conceptually, though technically possible if role checks allow).

- **Receptionist**
  - **Responsibilities**: Patient registration, scheduling appointments, checking patients in, generating invoices, recording payments.
  - **Exclusions**: Cannot view or edit medical/clinical details (Diagnosis, Prescriptions) beyond the printed scope. Cannot manage system users.

- **Doctor**
  - **Responsibilities**: Viewing their schedule, performing visits, writing medical notes, diagnoses, and prescriptions.
  - **Exclusions**: Should not handle payments or modify system configuration.

- **Nurse**
  - *Note: This role is not currently implemented in the database schema or authentication logic.*
  - Future implementation should focus on: Triaging patients, recording vitals, and preparing patients for the doctor.

## 4. Operational Workflow (From → To)

1.  **Patient Registration**: Receptionist creates a new Patient profile (`PatientController`).
2.  **Appointment**: Receptionist schedules a visit (`AppointmentController`). Status starts as `pending` or `scheduled`.
3.  **Check-in**: Patient arrives. Receptionist marks appointment as `waiting` (checked in).
4.  **Visit Start**: Doctor sees the patient in the queue and starts the visit. Status becomes `in_progress`.
5.  **Medical Actions**: Doctor records notes, diagnosis, and medicines in `MedicalRecords`.
6.  **Visit Completion**: Doctor marks the appointment as `completed`.
7.  **Billing**: Receptionist creates an Invoice (`InvoiceController`) linked to the Appointment.
8.  **Payment**: Partial or full payments are recorded against the Invoice. Status updates to `paid` or `partial`.

## 5. Visit Lifecycle
The `appointments` table uses a strict Enum for status. Transitions typically flow forward:

1.  **pending** / **scheduled**: Initial state when booked.
2.  **confirmed**: Optional state for confirmed bookings.
3.  **waiting**: Patient is in the waiting room (Checked In).
4.  **in_progress**: Patient is currently with the Doctor. *Locks the slot.*
5.  **completed**: Visit is finished.
    *   **cancelled**: Appointment did not happen (admin/receptionist action).
    *   **no_show**: Patient did not arrive.

**Locking Rules**:
- Completed appointments should be treated as read-only for non-admins.
- Invoices linked to completed appointments generally lock the fee in the appointment record.

## 6. Billing Logic (High-Level)
- **Invoices**: Can be standalone or linked to an Appointment (`appointment_id`).
- **Generation**: Typically generated *after* the medical service is complete, but can be generated *before* (e.g., consultation fee).
- **Snapshots**: Invoice items are snapshot copies of service prices at the time of creation. Changing the master Service price does *not* affect existing invoices.
- **Access Control**: Only Admins and Receptionists handle Invoices and Payments. Doctors view revenue reports but do not process transactions.

## 7. UI / UX Direction
- **Framework**: Laravel Blade (Server-Side Rendering).
- **Styling**: Bootstrap 5 + Custom CSS (`style.css`, `davinci.css`).
- **Icons**: FontAwesome.
- **Support**:
    - **RTL/LTR**: Native support via `dir` attribute on `<html>` tag. All UI components must mirror correctly.
    - **Dark/Light Mode**: Supported via `data-theme` attribute on `<body>`. All colors must use CSS variables (e.g., `var(--bg-primary)`).
- **Consistency**: Use existing Blade layouts (`layouts.app`) and components. Do not introduce new CSS frameworks (e.g., Tailwind) without refactoring the entire view layer.

## 8. System Boundaries (What This Project Is NOT)
- **NOT Multi-Tenant**: Do not add `tenant_id` columns.
- **NOT an API Backend**: While there are some AJAX routes, this is primarily a Monolithic MVC app. Do not detach the frontend.
- **NOT Insurance Compliant**: No HL7/FHIR integration or complex insurance claim logic currently exists.

## 9. Project Structure (High-Level)
- `app/Models`: Eloquent models (Strict types).
- `app/Http/Controllers`: Logic for each resource.
- `database/migrations`: Source of truth for schema.
- `resources/views`: Blade templates organized by resource (e.g., `patients/`, `appointments/`).
- `public/css`: Custom styles. `davinci.css` handles high-level themes.

## 10. Change & Contribution Rules
- **Proposals**: Analyze the database schema (`migrations`) first.
- **Routes**: Define all web routes in `routes/web.php`. Keep API routes minimal and secured by session/auth.
- **Pages**: New pages must extend `layouts.app` and support both LTR and RTL.
- **Restrictions**:
    - Do not hardcode text; use localization files (if implemented) or prepare for it.
    - Do not use raw SQL queries in Controllers; use Eloquent scopes.

## 11. AI Usage Instructions
**Strict Guidelines for AI Agents:**
1.  **Read Before Write**: Always read the `README.md` and `migrations` before proposing changes.
2.  **Respect Boundaries**: Do not add features that violate the "Single Clinic" scope.
3.  **UI Integrity**: When modifying Views, verify that changes look correct in **both** Light/Dark modes and LTR/RTL layouts.
4.  **Schema Compliance**: Check `database/migrations` for Enums. Do not try to insert invalid status strings.
5.  **Context**: You are working on an *active* system. Do not wipe databases or reset migrations without explicit user permission.
