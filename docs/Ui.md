Got it! I’ll generate a **full set of UI text / labels / placeholders / buttons / table headers / validation messages** for **every page** of the School Management System, based on the backlog we just created. This will serve as a **reference for frontend development** so that developers have consistent wording.

The format will be **Markdown**, organized **per page**, with sections for **Fields, Table Columns, Buttons, Messages, Placeholders, Tabs, Validation Messages**.

---

```{"id":"89124","variant":"standard"}
# School Management System — UI Text Reference

---

# 🏫 EPIC 1 — Authentication

## Login Page
**Fields & Placeholders:**  
- Email: "Enter your email"  
- Password: "Enter your password"  
- Remember Me checkbox: "Remember Me"  

**Buttons:**  
- "Login"  
- "Forgot Password?"  

**Validation / Error Messages:**  
- "Email is required."  
- "Enter a valid email address."  
- "Password is required."  
- "Invalid credentials."  
- "Your account is locked due to too many failed attempts."

---

## Forgot Password Page
**Fields:**  
- Email: "Enter your registered email"  

**Buttons:**  
- "Send Reset Link"  

**Messages:**  
- "We have sent a password reset link to your email."  
- Validation: "Email is required.", "Enter a valid email address."

---

## Reset Password Page
**Fields:**  
- New Password: "Enter new password"  
- Confirm Password: "Confirm new password"  

**Buttons:**  
- "Reset Password"  

**Validation Messages:**  
- "Password is required."  
- "Password must be at least 8 characters, including uppercase, lowercase, number, and special character."  
- "Passwords do not match."  
- "Reset token is invalid or expired."

---

# 🏫 EPIC 2 — Students Module

## Student List Page
**Filters & Dropdowns:**  
- Class: "Select Class"  
- Section: "Select Section"  
- Gender: "Select Gender"  
- Status: "Active / Inactive"  
- Admission Year: "Select Year"  
- Search: "Search by name or admission number"

**Table Columns:**  
- "Select" (checkbox)  
- "Admission No"  
- "Name"  
- "Class / Section"  
- "Date of Birth"  
- "Gender"  
- "Guardian"  
- "Phone"  
- "Status"  
- "Actions"

**Buttons:**  
- "Add Student"  
- "Edit"  
- "View Profile"  
- "Delete"  
- "Bulk Export"  
- "Bulk Update"  

**Messages:**  
- "No students found."  
- "Are you sure you want to delete selected student(s)?"  
- "Student(s) deleted successfully."  
- Validation: "Please select at least one student for bulk actions."

---

## Student Profile Page
**Tabs:**  
- "Overview"  
- "Academic History"  
- "Attendance Summary"  
- "Exams & Marks"  
- "Fees"  
- "Documents"  
- "Timeline"  
- "Health & Notes"

**Fields (Overview Tab):**  
- Admission No: readonly  
- Name: input  
- Date of Birth: datepicker  
- Gender: dropdown  
- National ID: input  
- Class: dropdown  
- Section: dropdown  
- Guardian: dropdown  
- Address: textarea  
- Phone: input  
- Admission Date: datepicker  
- Status: dropdown

**Buttons:**  
- "Edit"  
- "Save"  
- "Upload Document"  
- "Promote Student"  

**Validation / Error Messages:**  
- "Name is required."  
- "Date of Birth cannot be in the future."  
- "Admission number already exists."  
- "File type not allowed."  
- "Please select a guardian."

---

# 🏫 EPIC 3 — Teachers Module

## Teacher List Page
**Filters:**  
- Subject: "Select Subject"  
- Status: "Active / Inactive"  
- Search: "Search by name or employee number"

**Table Columns:**  
- "Employee No"  
- "Name"  
- "Subjects"  
- "Phone"  
- "Hire Date"  
- "Status"  
- "Actions"

**Buttons:**  
- "Add Teacher"  
- "Edit"  
- "View Profile"  
- "Delete"  

**Messages / Validation:**  
- "Employee number must be unique."  
- "No teachers found."

---

## Teacher Profile Page
**Tabs:**  
- "Overview"  
- "Classes Assigned"  
- "Timetable"  
- "Attendance"  
- "Exams"  
- "Documents"

**Fields Overview Tab:**  
- Employee No: readonly  
- Name: input  
- Phone: input  
- Qualifications: textarea  
- Hire Date: datepicker  
- Status: dropdown  

**Buttons:**  
- "Save"  
- "Upload Document"  

**Validation Messages:**  
- "Name is required."  
- "Phone number invalid."  
- "Employee number already exists."

---

# 🏫 EPIC 4 — Academic Structure

## Classes & Sections
**Table Columns (Classes):**  
- "Class Name"  
- "Academic Year"  
- "Sections"  
- "Actions"

**Table Columns (Sections):**  
- "Section Name"  
- "Capacity"  
- "Room"  
- "Class Teacher"  
- "Actions"

**Buttons:**  
- "Add Class"  
- "Edit Class"  
- "Delete Class"  
- "Add Section"  
- "Edit Section"  
- "Delete Section"  

**Validation / Messages:**  
- "Class name is required."  
- "Section name is required."  
- "Capacity must be numeric."  
- "No overlapping academic years allowed."

---

# 🏫 EPIC 5 — Timetable

## Timetable Editor
**Grid Labels:**  
- Days: "Monday" → "Friday"  
- Periods: "Period 1" → "Period N"  

**Slot Modal Fields:**  
- Subject: dropdown  
- Teacher: dropdown  
- Room: input  

**Buttons:**  
- "Add Slot"  
- "Edit Slot"  
- "Delete Slot"  
- "Save Timetable"  
- "Clone Timetable"

**Validation / Messages:**  
- "End time must be after start time."  
- "Teacher is already assigned in this slot."  
- "Room conflict detected."

---

# 🏫 EPIC 6 — Attendance

## Attendance Register
**Filters:**  
- Class, Section, Date  

**Table Columns:**  
- "Student Name"  
- "Status" (Dropdown: Present / Absent / Late / Excused)  
- "Actions"

**Buttons:**  
- "Save Attendance"  
- "Bulk Mark Present"  
- "Bulk Mark Absent"  

**Validation / Messages:**  
- "Cannot mark attendance for future dates."  
- "Please select a student."  

---

# 🏫 EPIC 7 — Exams & Gradebook

## Exams List
**Table Columns:**  
- "Exam Title"  
- "Class"  
- "Section"  
- "Start Date"  
- "End Date"  
- "Published"  
- "Actions"

**Buttons:**  
- "Add Exam"  
- "Edit Exam"  
- "Delete Exam"  
- "Publish Exam"

**Validation / Messages:**  
- "Exam title required."  
- "Start date must be before end date."  
- "Marks must be numeric and within allowed range."

---

## Marks Entry
**Table Columns:**  
- "Student Name"  
- "Subject"  
- "Marks Obtained"  

**Buttons:**  
- "Save Marks"  
- "Publish Report Card"  

**Validation / Messages:**  
- "Marks cannot exceed maximum marks."  
- "Please enter numeric values."

---

# 🏫 EPIC 8 — Fees & Payments

## Fee Types
**Table Columns:**  
- "Fee Name"  
- "Amount"  
- "Applicable Classes"  
- "Actions"

**Buttons:**  
- "Add Fee Type"  
- "Edit"  
- "Delete"

## Invoices & Payments
**Table Columns:**  
- "Invoice No"  
- "Student"  
- "Fee Type"  
- "Amount"  
- "Paid"  
- "Status"  
- "Actions"

**Buttons:**  
- "Generate Invoice"  
- "Record Payment"  
- "View Details"  

**Validation / Messages:**  
- "Payment cannot exceed outstanding balance."  
- "Invoice created successfully."

---

# 🏫 EPIC 9 — Library

## Books Table
**Columns:**  
- "Title"  
- "Author"  
- "ISBN"  
- "Total Copies"  
- "Available Copies"  
- "Actions"

## Book Issues
**Fields:**  
- Student: dropdown  
- Issue Date: datepicker  
- Due Date: datepicker  

**Buttons:**  
- "Issue Book"  
- "Return Book"  
- "Reserve Book"  

**Validation / Messages:**  
- "No copies available to issue."  
- "Due date cannot be before issue date."

---

# 🏫 EPIC 10 — Parent Portal

## Dashboard
**Sections:**  
- "Select Child" dropdown  
- "Attendance Summary" table  
- "Latest Grades" table  
- "Invoices / Payments" table  
- "Message Teacher" form  

**Buttons:**  
- "Send Message"  

**Validation / Messages:**  
- "You are not linked to this student."  
- "Message cannot be empty."

---

# 🏫 EPIC 11 — Notifications

## Notification Composer
**Fields:**  
- Title: input  
- Body: rich text editor  
- Target: dropdown (All / Classes / Sections / Users)  
- Channels: checkboxes (In-app / Email / SMS)  
- Schedule: datetime picker  

**Buttons:**  
- "Preview"  
- "Send"  
- "Save Draft"  

**Validation / Messages:**  
- "Title is required."  
- "Body is required."  
- "Target is required."

---

# 🏫 EPIC 12 — Reports & Analytics

## Reports Page
**Filters:**  
- Class, Section, Exam, Date Range, Subject  

**Table Columns:**  
- Depends on report type (Attendance %, Grade Avg, Fee Collected, etc.)  

**Buttons:**  
- "Generate Report"  
- "Export CSV"  
- "Export PDF"  

**Messages:**  
- "No data available for selected filters."

---

# 🏫 EPIC 13 — Settings & Roles

## Settings
**Fields:**  
- School Name  
- Address  
- Academic Year Start/End  
- Term Name & Dates  
- Grading Scale  

**Buttons:**  
- "Save Settings"  

**Validation / Messages:**  
- "Academic years cannot overlap."  
- "Required fields missing."

## Roles & Permissions
**Table Columns:**  
- "Role Name"  
- "Permissions"  
- "Actions"

**Buttons:**  
- "Add Role"  
- "Edit Role"  
- "Assign Permissions"  

**Messages:**  
- "Role name required."  
- "Permissions updated successfully."

---

# 🏫 EPIC 14 — Audit Logs

## Audit Log Page
**Filters:**  
- User, Entity Type, Date Range, Action  

**Table Columns:**  
- "User"  
- "Action"  
- "Entity Type"  
- "Entity ID"  
- "Before"  
- "After"  
- "IP Address"  
- "Timestamp"

**Buttons:**  
- "Export CSV"  
- "Export PDF"  

**Messages:**  
- "No logs found for selected filters."

---

# ✅ Notes
- All **table headers, field labels, placeholders, and buttons** standardized across modules.  
- Validation messages **consistent with backend responses**.  
- Can be directly used by frontend developers to build forms, tables, modals, and notifications.  

---

I can also create a **mockup-like text UI** for each page with **ASCII tables, forms, and tab layout** so your frontend team can visualize the layout purely from text.  

Do you want me to do that next?
```
