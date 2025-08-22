# Task & Employee Management System (Core PHP)

## Default Login

- **Email:** `admin@example.com`
- **Password:** `admin123`

---

## Roles & Panels

- **Admin**: Full access — Employees, Tasks, Assignments, Reports, Notifications.
- **Manager**: Manage Employees, Tasks, Assignments, and view Notifications.
- **Employee**: Sees assigned tasks on Dashboard (including who assigned them), can update task status, and view Notifications.

---

## Files

- `/index.php` — Login page
- `/admin_dashboard.php`, `/manager_dashboard.php`, `/employee_dashboard.php` — Role-based dashboards
- `/employees.php` — CRUD for users (Admin/Manager)
- `/tasks.php` — CRUD for tasks (Admin/Manager)
- `/assign.php` — Assign/Unassign employees to tasks
- `/task_update_status.php` — Update status for assigned tasks
- `/notifications.php` — In-app notifications
- `/reports.php` — Basic filters & overview (Admin)

---

## Database

Database name: `task_employee_core` (auto-created).

### Tables:

- **users**  
  `(id, full_name, email, password_hash, role, status, department, job_title, phone, profile_picture, created_at)`

- **tasks**  
  `(id, title, description, start_date, due_date, status, priority, created_by, created_at)`

- **task_assignments**  
  `(id, task_id, user_id, assigned_by, assigned_at)`

- **notifications**  
  `(id, user_id, message, is_read, created_at)`

- **teams**  
  `(id, name, created_by, created_at)`

- **team_members**  
  `(id, team_id, user_id, joined_at)`

---

## Notes

- Default passwords for new users:

  - Admin: `admin123`
  - Manager: `manager123`
  - Employee: `employee123`

- Employees can see who assigned their tasks on their dashboard.
- Managers can see who they've assigned tasks to and who assigned tasks in the assignment interface.
- Basic server-side validation and prepared statements are included.
- Clean dark UI with simple CSS — no frameworks.
- Database and tables are automatically initialized if not present.
