# Lab & Equipment Manager 

A PHP MVC web application for managing university lab equipment, borrowing requests, check-in/check-out records, maintenance schedules, damage reports, and penalty payments.

## Run with Laragon

1. Copy the `lab_equipment_manager` folder to `C:\laragon\www\`.
2. Start Laragon Apache/Nginx and MySQL.
3. Open `http://localhost/lab_equipment_manager/`.
4. The app imports `database/lab_equipment_manager.sql` automatically if the database does not exist.


## Self Registration

Students and technicians can register without a registration code. Student accounts must provide a Student ID, and students log in using Student ID + password. Admin and technician accounts log in using email + password.

Admin can manage registration codes from the sidebar.

## Main Modules

- Departments
- Users
- Registration Codes
- Labs
- Equipment Categories
- Suppliers
- Equipment
- Borrow Requests
- Borrow Records
- Maintenance Schedules
- Maintenance Logs
- Damage Reports
- Penalty Payments

## Main Business Rules

- Student and technician self-registration does not require a registration code. Student accounts must provide a unique Student ID.
- Duplicate user email and student ID are blocked.
- Equipment can only be borrowed when its status is `available`.
- Expected return time must be later than start time.
- Overlapping borrow requests for the same equipment are blocked.
- Approving a request, check-out, check-in, damaged return, and maintenance actions update related records and equipment status.


## UI/CSS Note
This version includes an inline CSS fallback in the PHP views. The interface will still load correctly even when Laragon uses `localhost`, `.test`, `:8888`, or a different document root.
