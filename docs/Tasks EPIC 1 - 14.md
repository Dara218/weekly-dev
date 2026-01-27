# School Management System — Full Backlog / User Stories

**Version:** 1.0
**Author:** Generated
**Format:** Markdown — suitable for dev backlog and sprint planning

---

# 🏗️ EPIC 0 — Project Setup

## Phase 0.1 — Backend Setup

* Initialize Laravel project
* Configure `.env` for DB, cache, mail
* Install packages: Sanctum, Spatie Roles & Permissions, Excel export
* Setup authentication scaffolding
* Initialize Git repository
* Configure Docker for PHP, DB, Redis
* Setup CI/CD: linting, tests, build

## Phase 0.2 — Frontend Setup

* Initialize React/Vue project
* Setup routing (React Router / Vue Router)
* Setup global state (Redux / Vuex / Pinia)
* Configure Axios for API calls
* Install Tailwind CSS
* Setup environment variables

## Phase 0.3 — QA & DevOps

* Setup ESLint / Prettier
* Setup PHPUnit / Jest / Vitest
* Configure test coverage reports
* Create initial README with folder structure

---

# 🏗️ EPIC 1 — Database & Authentication

## Phase 1.1 — Database & Migration

* Tables: users, students, parents, teachers, classes, sections, subjects, timetable_entries, attendance_records, exams, exam_marks, fee_types, invoices, payments, books, book_issues, notifications, audit_logs
* Define indexes and foreign keys
* Soft deletes where appropriate
* Seeders for sample data
* Document ERD in Markdown

## Phase 1.2 — Authentication Module

**Pages:** Login, Logout, Forgot Password, Reset Password

* **Roles:** Admin, Teacher, Student, Parent
* **Text-based UI:** Email, Password, Submit button, Forgot Password link, Reset Password inputs
* **Validation:** Required, email format, password rules
* **Backend Tasks:** `/api/login`, `/api/logout`, `/api/password/forgot`, `/api/password/reset`, RBAC middleware, token auth
* **Frontend Tasks:** Forms, validation, API integration, error handling, role-based redirects
* **Edge Cases:** Wrong credentials, locked user, expired reset token
* **Acceptance Criteria:** Successful login, token stored, redirects based on role

---

# 🏗️ EPIC 2 — Student Module

## Page: Student List

* **Roles:** Admin, Teacher
* **Text UI:** Filters (Class, Section, Gender, Status, Admission Year), Search, Table (Admission No, Name, Class/Section, DOB, Guardian, Phone, Status, Actions), Bulk Actions (Export, Delete, Assign Section)
* **Backend Tasks:** `GET /api/students`, `POST /api/students`, `PUT /api/students/{id}`, `DELETE /api/students/{id}`, Bulk Export, Soft Delete, Permissions check
* **Frontend Tasks:** Page component, filters, search, table, pagination, bulk actions, API integration, error handling
* **Validation:** Filters valid, bulk actions disabled if none selected
* **Edge Cases:** Invalid filter → 422, delete linked student → reject, empty table
* **Acceptance Criteria:** Correct data rendered, search & filter functional, bulk actions work

## Page: Student Profile

* **Roles:** Admin, Teacher
* **Text UI:** Tabs: Overview, Academic History, Attendance, Exams & Marks, Fees, Documents, Health Notes; Display immutable fields (Admission No, Name, DOB); Editable fields (Address, Phone, Guardian)
* **Backend Tasks:** `GET /api/students/{id}`, `PUT /api/students/{id}`, File upload, Promotion API
* **Frontend Tasks:** Tab component, editable form, file upload, API integration
* **Validation:** DOB not future, age per class, file type/size
* **Edge Cases:** Unauthorized → 403, invalid upload, stale updates → 409
* **Acceptance Criteria:** Tabs load data, edits save correctly, uploads retrievable

---

# 🏗️ EPIC 3 — Teacher Module

## Page: Teacher List

* **Roles:** Admin
* **Text UI:** Filters: Subject, Status; Search; Table: Employee No, Name, Subjects, Phone, Hire Date, Status, Actions
* **Backend Tasks:** CRUD APIs `/api/teachers`, soft deletes, bulk export
* **Frontend Tasks:** List page component, filters, search, table, pagination, API integration
* **Validation:** Unique employee number
* **Edge Cases:** Delete teacher assigned to timetable → warning
* **Acceptance Criteria:** Correct data, search & filter functional

## Page: Teacher Profile

* **Text UI:** Tabs: Overview, Classes Assigned, Timetable, Attendance, Exams, Documents
* **Backend Tasks:** `GET /api/teachers/{id}`, `PUT /api/teachers/{id}`, File upload
* **Frontend Tasks:** Tab navigation, forms, API integration
* **Validation:** Unique employee number, valid phone
* **Acceptance Criteria:** Data rendered correctly, edits saved, file upload functional

---

# 🏗️ EPIC 4 — Academic Structure

## Page: Classes & Sections

* **Roles:** Admin
* **Text UI:** Class table (Name, Academic Year, Sections), Section table (Name, Capacity, Room, Class Teacher)
* **Backend Tasks:** CRUD `/api/classes` & `/api/sections`, validation for overlapping years, capacity check
* **Frontend Tasks:** List pages, forms, table, pagination, API integration
* **Validation:** Unique class per academic year, section capacity
* **Acceptance Criteria:** Classes/sections created, updated, deleted with constraints enforced

## Page: Subjects

* **Text UI:** Table: Name, Code, Classes, Primary Teacher, Max Marks; Form to add/edit subject
* **Backend Tasks:** CRUD `/api/subjects`, assignment to classes & teacher
* **Frontend Tasks:** Table component, forms, multi-select for classes
* **Validation:** Unique code, max marks numeric
* **Acceptance Criteria:** Subjects managed correctly

---

# 🏗️ EPIC 5 — Timetable Module

## Page: Timetable Editor

* **Roles:** Admin, Teacher
* **Text UI:** Grid (Days × Periods), Slot modal: Subject, Teacher, Room; Drag & Drop support
* **Backend Tasks:** `/api/timetable` CRUD, conflict detection (teacher, room)
* **Frontend Tasks:** Timetable grid, modal, drag & drop, API integration
* **Validation:** End > Start, no conflicts
* **Acceptance Criteria:** Timetable saved, conflicts rejected

---

# 🏗️ EPIC 6 — Attendance Module

## Page: Attendance Register

* **Roles:** Teacher
* **Text UI:** Filters (Class, Section, Date); Table: Student, Status (Present, Absent, Late, Excused); Bulk Save Button
* **Backend Tasks:** `/api/attendance` CRUD, period-based validation
* **Frontend Tasks:** Attendance table, dropdowns, bulk save, API integration
* **Validation:** No future dates unless override
* **Acceptance Criteria:** Attendance recorded, error handled

## Page: Attendance Reports

* **Roles:** Admin, Teacher
* **Text UI:** Filters (Class, Section, Date Range); Table/Charts
* **Backend Tasks:** `/api/reports/attendance`
* **Frontend Tasks:** Charts, export buttons
* **Acceptance Criteria:** Data accurate, export functional

---

# 🏗️ EPIC 7 — Exam & Gradebook

## Page: Exams List & Create

* **Roles:** Admin, Teacher
* **Text UI:** Table: Exam Name, Class, Section, Dates, Published; Form to create
* **Backend Tasks:** CRUD `/api/exams`
* **Frontend Tasks:** Table, form, API integration
* **Validation:** Dates valid, unique exam name per class
* **Acceptance Criteria:** Exams created, updated, published

## Page: Marks Entry

* **Text UI:** Table: Student, Subject, Marks; Bulk save
* **Backend Tasks:** `/api/exams/{id}/marks/bulk`, validate 0 ≤ marks ≤ max
* **Frontend Tasks:** Entry table, inline editing
* **Acceptance Criteria:** Marks saved, published report generated

---

# 🏗️ EPIC 8 — Fees & Payments

## Page: Fee Types & Invoice

* **Roles:** Admin, Accountant
* **Text UI:** Fee types table, Add/Edit; Invoice table, Create invoice modal
* **Backend Tasks:** `/api/fees/types`, `/api/invoices`, `/api/payments`, discounts, scholarships
* **Frontend Tasks:** Tables, forms, API integration
* **Validation:** Payments ≤ outstanding, partial payments update status
* **Acceptance Criteria:** Fee types, invoices, payments managed

---

# 🏗️ EPIC 9 — Library Module

## Page: Books & Issues

* **Roles:** Admin, Librarian
* **Text UI:** Books table: Title, Author, Copies; Issue modal: Student, Due Date
* **Backend Tasks:** CRUD `/api/books`, `/api/book-issues`, return, fine calculation
* **Frontend Tasks:** Tables, forms, API integration
* **Validation:** Block issue if no copies, allow reservations
* **Acceptance Criteria:** Books issued, returned, fines calculated

---

# 🏗️ EPIC 10 — Parent Portal

## Page: Parent Dashboard

* **Roles:** Parent
* **Text UI:** Child selector, Attendance summary, Latest Grades, Invoices, Message Teacher
* **Backend Tasks:** `/api/parents/{id}/children`, `/api/students/{id}/summary`
* **Frontend Tasks:** Dashboard component, API integration
* **Validation:** Only linked children visible
* **Acceptance Criteria:** Correct data shown, actions allowed

---

# 🏗️ EPIC 11 — Notifications & Announcements

## Page: Notification Composer

* **Roles:** Admin
* **Text UI:** Title, Body, Target (All/Classes/Sections), Channels, Schedule
* **Backend Tasks:** `/api/announcements`, `/api/notifications?user_id=`
* **Frontend Tasks:** Form, preview, API integration
* **Acceptance Criteria:** Notification created, sent or scheduled

---

# 🏗️ EPIC 12 — Reports & Analytics

## Page: Reports

* **Roles:** Admin, Teacher
* **Text UI:** Filters, Tables, Charts, Export CSV/PDF
* **Backend Tasks:** `/api/reports/*`
* **Frontend Tasks:** Charts, export buttons
* **Acceptance Criteria:** Data correct, export functional

---

# 🏗️ EPIC 13 — Settings & Roles

## Page: School & Academic Settings

* **Roles:** Admin
* **Text UI:** School info, Academic years, Terms, Grading scales, Holidays
* **Backend Tasks:** `/api/settings` GET/PUT
* **Frontend Tasks:** Forms, tabs, validation
* **Acceptance Criteria:** Settings updated, constraints enforced

## Page: Roles & Permissions

* **Roles:** Admin
* **Text UI:** Roles table, Permission tree, Assign roles to users
* **Backend Tasks:** CRUD `/api/roles`, assign roles
* **Frontend Tasks:** Role management component
* **Acceptance Criteria:** Roles created, permissions applied immediately

---

# 🏗️ EPIC 14 — Audit & Activity Logs

## Page: Audit Logs

* **Roles:** Admin, Auditor
* **Text UI:** Table: User, Action, Entity, Before/After, IP, Timestamp; Filters
* **Backend Tasks:** `/api/audit-logs`, retention policies
* **Frontend Tasks:** Table, filters, export
* **Acceptance Criteria:** Logs viewable, filtered, exported

---

# ✅ Notes

* All endpoints require **token auth** (API) or **CSRF** (web).
* All pages have **RBAC checks**.
* Frontend and backend are **separate projects**; tasks are divided accordingly.
* Validation, error messages, edge cases, and acceptance criteria included per page.

---

This document can serve as your **full project backlog** for planning sprints, assigning tasks, and tracking development progress.

---

I can also make a **version with explicit frontend component hierarchy and folder structure per module**, fully ready for developers to start coding.

Do you want me to do that next?
