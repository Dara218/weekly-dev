Perfect! I’ll create **ASCII-style text UI mockups** for **all main pages**. These will include **forms, tables, tabs, buttons, and layout**, so your frontend team can visualize the interface without design software. I’ll continue using **the same per-page structure** from before.

---

````{"id":"89125","variant":"standard"}
# School Management System — ASCII Text UI Mockups

---

# 🏫 EPIC 1 — Authentication

## Login Page

```
+-------------------------------------+
|          SCHOOL LOGIN               |
+-------------------------------------+
| Email:    [_____________________]   |
| Password: [_____________________]   |
| [ ] Remember Me                     |
|                                     |
| [ Login ]   [ Forgot Password? ]    |
+-------------------------------------+
| Error: Invalid credentials           |
+-------------------------------------+
```

---

## Forgot Password Page

```
+-------------------------------------+
|       FORGOT PASSWORD               |
+-------------------------------------+
| Email: [_____________________]      |
|                                     |
| [ Send Reset Link ]                 |
+-------------------------------------+
| Message: Reset link sent to your email |
+-------------------------------------+
```

---

## Reset Password Page

```
+-------------------------------------+
|       RESET PASSWORD                |
+-------------------------------------+
| New Password:       [_____________] |
| Confirm Password:   [_____________] |
|                                     |
| [ Reset Password ]                  |
+-------------------------------------+
| Errors: Passwords do not match       |
+-------------------------------------+
```

---

# 🏫 EPIC 2 — Students Module

## Student List Page

```
+--------------------------------------------------------------------------------+
| STUDENTS                                                                       |
+--------------------------------------------------------------------------------+
| Filter: Class [__]  Section [__]  Gender [__]  Status [__]  Year [__]  Search [_____]  |
+--------------------------------------------------------------------------------+
| [ Add Student ] [ Bulk Export ] [ Bulk Update ]                                |
+----+------------+----------------+---------------+--------+--------+--------+--------+
|Sel | Admission# | Name           | Class/Section | DOB    | Gender | Guardian| Status |
+----+------------+----------------+---------------+--------+--------+--------+--------+
|[ ] | 2024001    | John Doe       | 1A            | 2010-01-05 | M | Jane D | Active |
|[ ] | 2024002    | Alice Smith    | 2B            | 2009-06-12 | F | Bob S  | Active |
+----+------------+----------------+---------------+--------+--------+--------+--------+
| Actions: [View Profile] [Edit] [Delete]                                        |
+--------------------------------------------------------------------------------+
```

---

## Student Profile Page

```
+---------------------------------------------+
| STUDENT PROFILE: John Doe                   |
+---------------------------------------------+
| Tabs: [Overview] [Academic History] [Attendance] [Exams] [Fees] [Documents] [Timeline] [Health] |
+---------------------------------------------+
| Overview:                                   |
| Admission #: 2024001                        |
| Name: John Doe                              |
| DOB: 2010-01-05                             |
| Gender: M                                   |
| Class: 1                                     |
| Section: A                                  |
| Guardian: Jane Doe                           |
| Phone: 09123456789                           |
| Status: Active                               |
| [ Edit ] [ Save ] [ Promote Student ]        |
| Documents: [Upload Document]                 |
+---------------------------------------------+
```

---

# 🏫 EPIC 3 — Teachers Module

## Teacher List Page

```
+------------------------------------------------------------+
| TEACHERS                                                   |
+------------------------------------------------------------+
| Filter: Subject [__]  Status [__]  Search [_________]      |
+------------------------------------------------------------+
| [ Add Teacher ]                                            |
+----+------------+----------------+--------+-----------+--------+
|Emp#| Name       | Subjects       | Phone  | Hire Date | Status |
+----+------------+----------------+--------+-----------+--------+
|T001| Mary Jane  | Math, Physics  | 0912.. | 2018-03-12| Active |
|T002| Peter Pan  | English        | 0921.. | 2020-05-01| Active |
+----+------------+----------------+--------+-----------+--------+
| Actions: [View Profile] [Edit] [Delete]                   |
+------------------------------------------------------------+
```

---

## Teacher Profile Page

```
+---------------------------------------------+
| TEACHER PROFILE: Mary Jane                  |
+---------------------------------------------+
| Tabs: [Overview] [Classes Assigned] [Timetable] [Attendance] [Exams] [Documents] |
+---------------------------------------------+
| Overview:                                   |
| Employee #: T001                             |
| Name: Mary Jane                              |
| Phone: 09123456789                           |
| Qualifications: B.Sc Math                     |
| Hire Date: 2018-03-12                         |
| Status: Active                                |
| [ Edit ] [ Save ] [ Upload Document ]         |
+---------------------------------------------+
```

---

# 🏫 EPIC 4 — Classes & Sections

```
+---------------------------------------------+
| CLASSES                                     |
+---------------------------------------------+
| [ Add Class ]                               |
+----------------+---------------+------------+
| Class Name     | Academic Year | Sections   |
+----------------+---------------+------------+
| Grade 1        | 2024-2025     | 1A, 1B     |
| Grade 2        | 2024-2025     | 2A, 2B     |
+----------------+---------------+------------+
| Actions: [Edit] [Delete]                   |
+---------------------------------------------+
| SECTIONS                                     |
+----------------+---------+------+------------+
| Section Name   | Capacity| Room | Class Teacher |
+----------------+---------+------+------------+
| 1A             | 30      | R101 | Mary Jane   |
| 1B             | 28      | R102 | Peter Pan   |
+----------------+---------+------+------------+
| Actions: [Edit] [Delete]                   |
+---------------------------------------------+
```

---

# 🏫 EPIC 5 — Timetable

```
+-------------------------- TIMETABLE ---------------------------+
| Class: 1A   Week: 01/02 Mar                                     |
+----------+-----------+-----------+-----------+-----------+-------+
| Period   | Monday    | Tuesday   | Wednesday | Thursday  | Friday|
+----------+-----------+-----------+-----------+-----------+-------+
| 1        | Math      | English   | Science   | Math      | PE    |
| 2        | English   | Math      | History   | Science   | Music |
| 3        | History   | Geography | Math      | English   | Art   |
+----------+-----------+-----------+-----------+-----------+-------+
| [Add Slot] [Edit Slot] [Delete Slot] [Save Timetable] [Clone]   |
+-----------------------------------------------------------------+
```

---

# 🏫 EPIC 6 — Attendance

```
+------------------- ATTENDANCE -------------------+
| Class: 1A   Date: 2025-11-26                     |
+----------------+-----------+-----------+---------+
| Student Name   | Status    | Notes     | Actions |
+----------------+-----------+-----------+---------+
| John Doe       | Present   |           | [Edit] |
| Alice Smith    | Absent    | Sick      | [Edit] |
+----------------+-----------+-----------+---------+
| [Save Attendance] [Bulk Mark Present] [Bulk Mark Absent] |
+------------------------------------------------------------+
```

---

# 🏫 EPIC 7 — Exams & Gradebook

```
+----------------- EXAMS -----------------+
| [Add Exam] [Edit] [Delete] [Publish]    |
+------------+-------+--------+------------+
| Exam Title | Class | Section | Dates    |
+------------+-------+--------+------------+
| Midterm    | 1A    | A      | 01-05 Mar |
+------------+-------+--------+------------+

Marks Entry:

+----------------- MARKS ENTRY -----------------+
| Student Name | Subject  | Marks Obtained       |
+--------------+---------+---------------------+
| John Doe     | Math    | 85                  |
| Alice Smith  | English | 78                  |
+--------------+---------+---------------------+
| [Save Marks] [Publish Report Card]           |
+---------------------------------------------+
```

---

# 🏫 EPIC 8 — Fees & Payments

```
+----------------- INVOICES -----------------+
| [Generate Invoice] [Record Payment]        |
+-----------+---------+---------+--------+---------+
| Invoice # | Student | Fee Type| Amount | Status  |
+-----------+---------+---------+--------+---------+
| INV001    | John D  | Tuition | 5000   | Pending |
| INV002    | Alice S | Tuition | 5000   | Paid    |
+-----------+---------+---------+--------+---------+
```

---

# 🏫 EPIC 9 — Library

```
+----------------- BOOKS -----------------+
| [Add Book] [Issue Book] [Return Book]  |
+--------+-----------+--------+-------+-------+
| Title  | Author    | ISBN   | Total | Available |
+--------+-----------+--------+-------+-------+
| Math 1 | Jane Doe  | 12345  | 5     | 2     |
+--------+-----------+--------+-------+-------+
```

---

# 🏫 EPIC 10 — Parent Portal

```
+----------------- PARENT DASHBOARD -----------------+
| Select Child: [John Doe]                             |
+----------------+--------+--------+-----------------+
| Attendance     | Grades | Invoices | Message Teacher|
+----------------+--------+--------+-----------------+
| Present: 18/20 | Math:85| INV001  | [Send Message] |
| Absent: 2      | Eng:78 | INV002  |                 |
+----------------+--------+--------+-----------------+
```

---

# 🏫 EPIC 11 — Notifications

```
+----------------- NEW NOTIFICATION -----------------+
| Title: [____________________]                      |
| Body:  [____________________]                      |
| Target: [All | Classes | Sections | Users]        |
| Channels: [x] In-app  [x] Email  [ ] SMS           |
| Schedule: [YYYY-MM-DD HH:MM]                       |
| [Preview] [Send] [Save Draft]                      |
+----------------------------------------------------+
```

---

# 🏫 EPIC 12 — Reports

```
+----------------- REPORTS -----------------+
| Filters: Class [__] Section [__] Exam [__] Date From [__] To [__] Subject [__] |
| [Generate Report] [Export CSV] [Export PDF]                                   |
+----------------- DATA TABLE -----------------+
| Student | Attendance % | Avg Marks | Fee Paid | Status |
|---------|--------------|-----------|---------|--------|
| John D  | 90           | 85        | Yes     | Active |
+---------+--------------+-----------+---------+--------+
```

---

# 🏫 EPIC 13 — Settings & Roles

```
+----------------- SETTINGS -----------------+
| School Name: [_________________]           |
| Address:     [_________________]           |
| Academic Year Start: [____] End: [____]   |
| Term 1: [Name][Start][End]                 |
| Grading Scale: [A-F]                       |
| [Save Settings]                             |
+-------------------------------------------+

+----------------- ROLES & PERMISSIONS -----------------+
| Role Name  | Permissions                 | Actions      |
+------------+----------------------------+--------------+
| Admin      | All                        | [Edit]       |
| Teacher    | Students.View, Exams.Edit  | [Edit]       |
+------------+----------------------------+--------------+
| [Add Role]                                     |
+------------------------------------------------+
```

---

# 🏫 EPIC 14 — Audit Logs

```
+----------------- AUDIT LOGS -----------------+
| Filters: User [__] Entity [__] Action [__] Date From [__] To [__] |
| [Export CSV] [Export PDF]                                       |
+----------------- LOG TABLE -----------------+
| User      | Action | Entity  | Before | After | IP       | Timestamp |
|-----------|--------|---------|--------|-------|----------|-----------|
| Mary J    | Edit   | Student | ...    | ...   | 192.168..| 2025-11-26 |
+-----------+--------+---------+--------+-------+----------+-----------+
```

---

This ASCII-style UI shows **all forms, tables, tabs, filters, buttons, and messages**. You can use it as a **text-based design spec** for frontend developers to implement UI components and layouts.

---

If you want, I can **also create a full “frontend component spec”**, mapping **each UI element to a reusable React/Vue component** with **props, state, events**, which is perfect for splitting the frontend/backend project.  

Do you want me to generate that next?
````
