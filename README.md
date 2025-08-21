# Task & Employee Management (Core PHP)

Login with:

- **Email:** `admin@example.com`
- **Password:** `admin123`

## Roles & Panels

- **Admin**: Full access — Employees, Tasks, Assign, Reports, Notifications.
- **Manager**: Manage Employees, Tasks, Assignments, view Notifications.
- **Employee**: Sees assigned tasks on Dashboard (including who assigned them), can update task status, and view Notifications.

## Files

- `/index.php` — Login
- `/{role}_dashboard.php` — Role-based dashboards (admin_dashboard.php, manager_dashboard.php, employee_dashboard.php)
- `/employees.php` — CRUD for users (Admin/Manager)
- `/tasks.php` — CRUD for tasks (Admin/Manager)
- `/assign.php` — Assign/Unassign employees to tasks
- `/task_update_status.php` — Update status for assigned tasks
- `/notifications.php` — In-app notifications
- `/reports.php` — Basic filters & overview (Admin)

## Database

Database name: `task_employee_core` (auto-created). Tables:

- `users(id, full_name, email, password_hash, role, department, job_title, phone, created_at)`
- `tasks(id, title, description, start_date, due_date, status, created_by, created_at)`
- `task_assignments(id, task_id, user_id, assigned_by, assigned_at)`
- `notifications(id, user_id, message, is_read, created_at)`

## Notes

- New employees get default password `password123` (change via phpMyAdmin if needed).
- Basic server-side validation and prepared statements included.
- Clean dark UI with simple CSS — no frameworks.
- Employees can now see who assigned their tasks in their dashboard.
- Managers can see who they've assigned tasks to and who assigned tasks in the assignment interface.
