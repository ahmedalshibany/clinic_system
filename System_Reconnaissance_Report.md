# System Reconnaissance Map

## 1. Route Tree Structure
- **GET|HEAD** `//` (name: `login`)
  - Middleware: web, guest
- **GET|HEAD** `/api/medicines/search` (name: `api.medicines.search`)
  - Middleware: web, auth
- **GET|HEAD** `/api/patients/search` (name: `api.patients.search`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments` (name: `appointments.index`)
  - Middleware: web, auth
- **POST** `/appointments` (name: `appointments.store`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/calendar` (name: `appointments.calendar`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/create` (name: `appointments.create`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/events` (name: `appointments.events`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/queue` (name: `appointments.queue`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/{appointment}` (name: `appointments.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/appointments/{appointment}` (name: `appointments.update`)
  - Middleware: web, auth
- **DELETE** `/appointments/{appointment}` (name: `appointments.destroy`)
  - Middleware: web, auth
- **POST** `/appointments/{appointment}/check-in` (name: `appointments.check-in`)
  - Middleware: web, auth
- **POST** `/appointments/{appointment}/complete` (name: `appointments.complete`)
  - Middleware: web, auth
- **GET|HEAD** `/appointments/{appointment}/edit` (name: `appointments.edit`)
  - Middleware: web, auth
- **POST** `/appointments/{appointment}/no-show` (name: `appointments.no-show`)
  - Middleware: web, auth
- **POST** `/appointments/{appointment}/start` (name: `appointments.start`)
  - Middleware: web, auth
- **POST** `/appointments/{appointment}/vitals` (name: `nurse.vitals.store`)
  - Middleware: web, auth, role:nurse,doctor,admin
- **GET|HEAD** `/appointments/{appointment}/vitals/create` (name: `nurse.vitals.create`)
  - Middleware: web, auth, role:nurse,doctor,admin
- **GET|HEAD** `/dashboard` (name: `dashboard`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors` (name: `doctors.index`)
  - Middleware: web, auth
- **POST** `/doctors` (name: `doctors.store`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors/create` (name: `doctors.create`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors/{doctor}` (name: `doctors.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/doctors/{doctor}` (name: `doctors.update`)
  - Middleware: web, auth
- **DELETE** `/doctors/{doctor}` (name: `doctors.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors/{doctor}/available-slots/{date}` (name: `doctors.available-slots`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors/{doctor}/edit` (name: `doctors.edit`)
  - Middleware: web, auth
- **POST** `/doctors/{doctor}/leaves` (name: `doctors.leaves.store`)
  - Middleware: web, auth
- **DELETE** `/doctors/{doctor}/leaves/{leave}` (name: `doctors.leaves.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/doctors/{doctor}/schedule` (name: `doctors.schedule`)
  - Middleware: web, auth
- **PUT** `/doctors/{doctor}/schedule` (name: `doctors.schedule.update`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices` (name: `invoices.index`)
  - Middleware: web, auth
- **POST** `/invoices` (name: `invoices.store`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/create` (name: `invoices.create`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/{appointment}/create` (name: `invoices.create-from-appointment`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/{invoice}` (name: `invoices.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/invoices/{invoice}` (name: `invoices.update`)
  - Middleware: web, auth
- **DELETE** `/invoices/{invoice}` (name: `invoices.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/{invoice}/edit` (name: `invoices.edit`)
  - Middleware: web, auth
- **POST** `/invoices/{invoice}/payment` (name: `invoices.payment`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/{invoice}/pdf` (name: `invoices.pdf`)
  - Middleware: web, auth
- **GET|HEAD** `/invoices/{invoice}/print` (name: `invoices.print`)
  - Middleware: web, auth
- **POST** `/invoices/{invoice}/send` (name: `invoices.send`)
  - Middleware: web, auth
- **GET|HEAD** `/login` (name: `auth.login`)
  - Middleware: web, guest
- **POST** `/login` (name: `auth.attempt`)
  - Middleware: web, guest, throttle:5,1
- **POST** `/logout` (name: `logout`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records` (name: `medical-records.index`)
  - Middleware: web, auth
- **POST** `/medical-records` (name: `medical-records.store`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records/create` (name: `medical-records.create`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records/{medical_record}` (name: `medical-records.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/medical-records/{medical_record}` (name: `medical-records.update`)
  - Middleware: web, auth
- **DELETE** `/medical-records/{medical_record}` (name: `medical-records.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records/{medical_record}/edit` (name: `medical-records.edit`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records/{medical_record}/print-prescription` (name: `medical-records.print-prescription`)
  - Middleware: web, auth
- **GET|HEAD** `/medical-records/{medical_record}/print-report` (name: `medical-records.print-report`)
  - Middleware: web, auth
- **GET|HEAD** `/medicalrecords-/create/{patient}` (name: `medical-records.create`)
  - Middleware: web, auth
- **GET|HEAD** `/notifications` (name: `notifications.index`)
  - Middleware: web, auth
- **DELETE** `/notifications/clear-all` (name: `notifications.clear-all`)
  - Middleware: web, auth
- **GET|HEAD** `/notifications/latest` (name: `notifications.latest`)
  - Middleware: web, auth
- **POST** `/notifications/mark-all-read` (name: `notifications.mark-all-read`)
  - Middleware: web, auth
- **GET|HEAD** `/notifications/unread-count` (name: `notifications.unread-count`)
  - Middleware: web, auth
- **DELETE** `/notifications/{notification}` (name: `notifications.destroy`)
  - Middleware: web, auth
- **PATCH** `/notifications/{notification}/read` (name: `notifications.mark-as-read`)
  - Middleware: web, auth
- **GET|HEAD** `/patients` (name: `patients.index`)
  - Middleware: web, auth
- **POST** `/patients` (name: `patients.store`)
  - Middleware: web, auth
- **GET|HEAD** `/patients/create` (name: `patients.create`)
  - Middleware: web, auth
- **GET|HEAD** `/patients/{patient}` (name: `patients.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/patients/{patient}` (name: `patients.update`)
  - Middleware: web, auth
- **DELETE** `/patients/{patient}` (name: `patients.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/patients/{patient}/edit` (name: `patients.edit`)
  - Middleware: web, auth
- **POST** `/patients/{patient}/files` (name: `patients.upload-file`)
  - Middleware: web, auth
- **GET|HEAD** `/patients/{patient}/files/{file}` (name: `patients.download-file`)
  - Middleware: web, auth
- **DELETE** `/patients/{patient}/files/{file}` (name: `patients.delete-file`)
  - Middleware: web, auth
- **GET|HEAD** `/reports` (name: `reports.index`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/appointments` (name: `reports.appointments`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/export/excel/{report}` (name: `reports.export.excel`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/export/pdf/{report}` (name: `reports.export.pdf`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/outstanding` (name: `reports.outstanding`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/patients` (name: `reports.patients`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/revenue` (name: `reports.revenue`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/revenue/doctor` (name: `reports.revenue.doctor`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/reports/revenue/service` (name: `reports.revenue.service`)
  - Middleware: web, auth, role:admin,doctor
- **GET|HEAD** `/services` (name: `services.index`)
  - Middleware: web, auth
- **POST** `/services` (name: `services.store`)
  - Middleware: web, auth
- **GET|HEAD** `/services/create` (name: `services.create`)
  - Middleware: web, auth
- **GET|HEAD** `/services/{service}` (name: `services.show`)
  - Middleware: web, auth
- **PUT|PATCH** `/services/{service}` (name: `services.update`)
  - Middleware: web, auth
- **DELETE** `/services/{service}` (name: `services.destroy`)
  - Middleware: web, auth
- **GET|HEAD** `/services/{service}/edit` (name: `services.edit`)
  - Middleware: web, auth
- **GET|HEAD** `/settings` (name: `settings.index`)
  - Middleware: web, auth
- **POST** `/settings` (name: `settings.update`)
  - Middleware: web, auth
- **GET|HEAD** `/storage/{path}` (name: `storage.local`)
- **GET|HEAD** `/up` 
- **GET|HEAD** `/users` (name: `users.index`)
  - Middleware: web, auth, role:admin
- **POST** `/users` (name: `users.store`)
  - Middleware: web, auth, role:admin
- **GET|HEAD** `/users/create` (name: `users.create`)
  - Middleware: web, auth, role:admin
- **GET|HEAD** `/users/{user}` (name: `users.show`)
  - Middleware: web, auth, role:admin
- **PUT|PATCH** `/users/{user}` (name: `users.update`)
  - Middleware: web, auth, role:admin
- **DELETE** `/users/{user}` (name: `users.destroy`)
  - Middleware: web, auth, role:admin
- **GET|HEAD** `/users/{user}/edit` (name: `users.edit`)
  - Middleware: web, auth, role:admin
- **POST** `/users/{user}/reset-password` (name: `users.reset-password`)
  - Middleware: web, auth, role:admin
- **PATCH** `/users/{user}/toggle` (name: `users.toggle`)
  - Middleware: web, auth, role:admin

## 2. Page-by-Page Breakdown & Component Hierarchy
### appointments\calendar.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - appointments.events

### appointments\create.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - appointments.store
  - appointments.index

### appointments\index.blade.php
- **Forms**: 6
- **Buttons**: 13
- **Dropdowns**: 0
- **Modals**: 1
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - {{ $appointments->previousPageUrl() }}
  - {{ $appointments->nextPageUrl() }}
  - appointments.index
  - appointments.store
  - api.patients.search

### appointments\queue.blade.php
- **Forms**: 4
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - appointments.queue

### appointments\show.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - appointments.index

### auth\login.blade.php
- **Forms**: 1
- **Buttons**: 2
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - auth.attempt

### dashboard.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - appointments.index

### doctors\create.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - doctors.store
  - doctors.index

### doctors\edit.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - doctors.index

### doctors\index.blade.php
- **Forms**: 3
- **Buttons**: 7
- **Dropdowns**: 0
- **Modals**: 1
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - {{ $doctors->previousPageUrl() }}
  - {{ $doctors->nextPageUrl() }}
  - doctors.index
  - doctors.store

### doctors\schedule.blade.php
- **Forms**: 3
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - doctors.index

### doctors\show.blade.php
- **Forms**: 0
- **Buttons**: 2
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 4
- **Outbound Links / Navigation Paths**:
  - {{ route(

### invoices\create.blade.php
- **Forms**: 1
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - invoices.store

### invoices\edit.blade.php
- **Forms**: 1
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### invoices\form.blade.php
- **Forms**: 0
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - invoices.index

### invoices\index.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - invoices.create
  - invoices.index

### invoices\print.blade.php
- **Forms**: 0
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### invoices\show.blade.php
- **Forms**: 2
- **Buttons**: 5
- **Dropdowns**: 0
- **Modals**: 1
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### layouts\app.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### layouts\dashboard.blade.php
- **Forms**: 2
- **Buttons**: 8
- **Dropdowns**: 1
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - dashboard
  - patients.index
  - appointments.index
  - appointments.calendar
  - doctors.index
  - services.index
  - reports.index
  - settings.index
  - users.index
  - ... and 4 more.

### medical_records\create.blade.php
- **Forms**: 1
- **Buttons**: 2
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ url()->previous() }}
  - medical-records.store

### medical_records\edit.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### medical_records\form.blade.php
- **Forms**: 0
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - api.medicines.search

### medical_records\print_prescription.blade.php
- **Forms**: 0
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ url()->previous() }}

### medical_records\show.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### notifications\index.blade.php
- **Forms**: 4
- **Buttons**: 4
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ $notification->link }}
  - notifications.mark-all-read
  - notifications.clear-all

### notifications\partials\dropdown-items.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - notifications.index

### nurse\vitals\create.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### patients\create.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - patients.store
  - patients.index

### patients\edit.blade.php
- **Forms**: 1
- **Buttons**: 4
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### patients\index.blade.php
- **Forms**: 2
- **Buttons**: 6
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - {{ $patients->previousPageUrl() }}
  - {{ $patients->nextPageUrl() }}
  - patients.index
  - patients.create

### patients\show.blade.php
- **Forms**: 2
- **Buttons**: 11
- **Dropdowns**: 0
- **Modals**: 2
- **Tabs**: 5
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - appointments.create
  - patients.index

### reports\appointments.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### reports\index.blade.php
- **Forms**: 0
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - reports.revenue
  - reports.revenue.doctor
  - reports.revenue.service
  - reports.outstanding
  - reports.patients
  - reports.appointments

### reports\outstanding.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### reports\patients.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### reports\revenue.blade.php
- **Forms**: 1
- **Buttons**: 6
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(

### reports\revenue_doctor.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### reports\revenue_service.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0

### services\index.blade.php
- **Forms**: 4
- **Buttons**: 9
- **Dropdowns**: 0
- **Modals**: 2
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - services.index
  - services.store

### settings\index.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 4
- **Outbound Links / Navigation Paths**:
  - #clinic
  - #system
  - #appointments
  - #invoices
  - settings.update

### users\create.blade.php
- **Forms**: 1
- **Buttons**: 1
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - users.store

### users\index.blade.php
- **Forms**: 2
- **Buttons**: 3
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ route(
  - users.index
  - users.create

### welcome.blade.php
- **Forms**: 0
- **Buttons**: 0
- **Dropdowns**: 0
- **Modals**: 0
- **Tabs**: 0
- **Outbound Links / Navigation Paths**:
  - {{ url(
  - {{ route(
  - https://laravel.com/docs
  - https://laracasts.com
  - https://cloud.laravel.com
  - login
  - register

## 3. Structural Anomalies
### Orphan Pages/Routes (Accessible but not directly linked from templates)
- appointments.show (appointments/{appointment})
- appointments.update (appointments/{appointment})
- appointments.destroy (appointments/{appointment})
- appointments.check-in (appointments/{appointment}/check-in)
- appointments.complete (appointments/{appointment}/complete)
- appointments.edit (appointments/{appointment}/edit)
- appointments.no-show (appointments/{appointment}/no-show)
- appointments.start (appointments/{appointment}/start)
- nurse.vitals.store (appointments/{appointment}/vitals)
- nurse.vitals.create (appointments/{appointment}/vitals/create)
- doctors.create (doctors/create)
- doctors.show (doctors/{doctor})
- doctors.update (doctors/{doctor})
- doctors.destroy (doctors/{doctor})
- doctors.available-slots (doctors/{doctor}/available-slots/{date})
- doctors.edit (doctors/{doctor}/edit)
- doctors.leaves.store (doctors/{doctor}/leaves)
- doctors.leaves.destroy (doctors/{doctor}/leaves/{leave})
- doctors.schedule (doctors/{doctor}/schedule)
- doctors.schedule.update (doctors/{doctor}/schedule)
- invoices.create-from-appointment (invoices/{appointment}/create)
- invoices.show (invoices/{invoice})
- invoices.update (invoices/{invoice})
- invoices.destroy (invoices/{invoice})
- invoices.edit (invoices/{invoice}/edit)
- invoices.payment (invoices/{invoice}/payment)
- invoices.pdf (invoices/{invoice}/pdf)
- invoices.print (invoices/{invoice}/print)
- invoices.send (invoices/{invoice}/send)
- medical-records.create (medical-records/create)
- medical-records.show (medical-records/{medical_record})
- medical-records.update (medical-records/{medical_record})
- medical-records.destroy (medical-records/{medical_record})
- medical-records.edit (medical-records/{medical_record}/edit)
- medical-records.print-prescription (medical-records/{medical_record}/print-prescription)
- medical-records.print-report (medical-records/{medical_record}/print-report)
- medical-records.create (medicalrecords-/create/{patient})
- notifications.destroy (notifications/{notification})
- notifications.mark-as-read (notifications/{notification}/read)
- patients.show (patients/{patient})
- patients.update (patients/{patient})
- patients.destroy (patients/{patient})
- patients.edit (patients/{patient}/edit)
- patients.upload-file (patients/{patient}/files)
- patients.download-file (patients/{patient}/files/{file})
- patients.delete-file (patients/{patient}/files/{file})
- reports.export.excel (reports/export/excel/{report})
- reports.export.pdf (reports/export/pdf/{report})
- services.create (services/create)
- services.show (services/{service})
- services.update (services/{service})
- services.destroy (services/{service})
- services.edit (services/{service}/edit)
- storage.local (storage/{path})
- users.show (users/{user})
- users.update (users/{user})
- users.destroy (users/{user})
- users.edit (users/{user}/edit)
- users.reset-password (users/{user}/reset-password)
- users.toggle (users/{user}/toggle)
