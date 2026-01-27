Below is a relationship-focused view of the School Management System (SMS) database,
derived from `docs/Database Schema (Full Table List + Fields).md`, expressed using
**Laravel-style relationship terms** (`hasMany`, `belongsTo`, `hasOne`, etc.).

For each table:
- **Outgoing relationships** = how this model relates to others from its perspective
- **Incoming relationships** = how other models relate back to this one

---

## 1. Users & Authentication Module

### 1.1 `users`

- **Primary key**
  - `users.id`

- **Outgoing relationships (Laravel-style)**
  - `User` **hasOne** `Parent` via `parents.user_id`
  - `User` **hasOne** `Student` via `students.user_id`
  - `User` **hasOne** `Teacher` via `teachers.user_id`
  - `User` **hasMany** `Message` (sent messages) via `messages.sender_id`
  - `User` **hasMany** `Message` (received messages) via `messages.receiver_id`
  - `User` **hasMany** `Announcement` via `announcements.created_by`

- **Incoming relationships**
  - `Parent` **belongsTo** `User` via `parents.user_id`
  - `Student` **belongsTo** `User` via `students.user_id`
  - `Teacher` **belongsTo** `User` via `teachers.user_id`
  - `Message` **belongsTo** `User` as `sender` via `messages.sender_id`
  - `Message` **belongsTo** `User` as `receiver` via `messages.receiver_id`
  - `Announcement` **belongsTo** `User` via `announcements.created_by`

---

### 1.2 `parents`

- **Primary key**
  - `parents.id`

- **Outgoing relationships**
  - `Parent` **belongsTo** `User` via `parents.user_id`
  - `Parent` **hasMany** `Student` via `students.parent_id`

- **Incoming relationships**
  - `User` **hasOne** `Parent` via `parents.user_id`
  - `Student` **belongsTo** `Parent` via `students.parent_id`

---

### 1.3 `students`

- **Primary key**
  - `students.id`

- **Outgoing relationships**
  - `Student` **belongsTo** `User` via `students.user_id`
  - `Student` **belongsTo** `Parent` via `students.parent_id`
  - `Student` **belongsTo** `Class` via `students.class_id`
  - `Student` **belongsTo** `Section` via `students.section_id`
  - `Student` **hasMany** `AttendanceDetail` via `attendance_details.student_id`
  - `Student` **hasMany** `Grade` via `grades.student_id`
  - `Student` **hasMany** `Invoice` via `invoices.student_id`
  - `Student` **hasMany** `IssuedBook` via `issued_books.issued_to_student_id`

- **Incoming relationships**
  - `User` **hasOne** `Student` via `students.user_id`
  - `Parent` **hasMany** `Student` via `students.parent_id`
  - `Class` **hasMany** `Student` via `students.class_id`
  - `Section` **hasMany** `Student` via `students.section_id`
  - `AttendanceDetail` **belongsTo** `Student` via `attendance_details.student_id`
  - `Grade` **belongsTo** `Student` via `grades.student_id`
  - `Invoice` **belongsTo** `Student` via `invoices.student_id`
  - `IssuedBook` **belongsTo** `Student` via `issued_books.issued_to_student_id`

---

### 1.4 `teachers`

- **Primary key**
  - `teachers.id`

- **Outgoing relationships**
  - `Teacher` **belongsTo** `User` via `teachers.user_id`
  - `Teacher` **hasMany** `Section` via `sections.teacher_id`
  - `Teacher` **hasMany** `Subject` via `subjects.teacher_id`
  - `Teacher` **hasMany** `Timetable` via `timetables.teacher_id`
  - `Teacher` **hasMany** `AttendanceRecord` via `attendance_records.marked_by`

- **Incoming relationships**
  - `User` **hasOne** `Teacher` via `teachers.user_id`
  - `Section` **belongsTo** `Teacher` via `sections.teacher_id`
  - `Subject` **belongsTo** `Teacher` via `subjects.teacher_id`
  - `Timetable` **belongsTo** `Teacher` via `timetables.teacher_id`
  - `AttendanceRecord` **belongsTo** `Teacher` via `attendance_records.marked_by`

---

## 2. Academic Module

### 2.1 `classes`

- **Primary key**
  - `classes.id`

- **Outgoing relationships**
  - `Class` **hasMany** `Section` via `sections.class_id`
  - `Class` **hasMany** `Subject` via `subjects.class_id`
  - `Class` **hasMany** `Student` via `students.class_id`
  - `Class` **hasMany** `Timetable` via `timetables.class_id`
  - `Class` **hasMany** `AttendanceRecord` via `attendance_records.class_id`

- **Incoming relationships**
  - `Section` **belongsTo** `Class` via `sections.class_id`
  - `Subject` **belongsTo** `Class` via `subjects.class_id`
  - `Student` **belongsTo** `Class` via `students.class_id`
  - `Timetable` **belongsTo** `Class` via `timetables.class_id`
  - `AttendanceRecord` **belongsTo** `Class` via `attendance_records.class_id`

---

### 2.2 `sections`

- **Primary key**
  - `sections.id`

- **Outgoing relationships**
  - `Section` **belongsTo** `Class` via `sections.class_id`
  - `Section` **belongsTo** `Teacher` via `sections.teacher_id`
  - `Section` **hasMany** `Student` via `students.section_id`
  - `Section` **hasMany** `Timetable` via `timetables.section_id`
  - `Section` **hasMany** `AttendanceRecord` via `attendance_records.section_id`

- **Incoming relationships**
  - `Class` **hasMany** `Section` via `sections.class_id`
  - `Teacher` **hasMany** `Section` via `sections.teacher_id`
  - `Student` **belongsTo** `Section` via `students.section_id`
  - `Timetable` **belongsTo** `Section` via `timetables.section_id`
  - `AttendanceRecord` **belongsTo** `Section` via `attendance_records.section_id`

---

### 2.3 `subjects`

- **Primary key**
  - `subjects.id`

- **Outgoing relationships**
  - `Subject` **belongsTo** `Class` via `subjects.class_id`
  - `Subject` **belongsTo** `Teacher` via `subjects.teacher_id`
  - `Subject` **hasMany** `Timetable` via `timetables.subject_id`
  - `Subject` **hasMany** `ExamSubject` via `exam_subjects.subject_id`

- **Incoming relationships**
  - `Class` **hasMany** `Subject` via `subjects.class_id`
  - `Teacher` **hasMany** `Subject` via `subjects.teacher_id`
  - `Timetable` **belongsTo** `Subject` via `timetables.subject_id`
  - `ExamSubject` **belongsTo** `Subject` via `exam_subjects.subject_id`

---

## 3. Timetable Module

### 3.1 `timetables`

- **Primary key**
  - `timetables.id`

- **Outgoing relationships**
  - `Timetable` **belongsTo** `Class` via `timetables.class_id`
  - `Timetable` **belongsTo** `Section` via `timetables.section_id`
  - `Timetable` **belongsTo** `Subject` via `timetables.subject_id`
  - `Timetable` **belongsTo** `Teacher` via `timetables.teacher_id`

- **Incoming relationships**
  - `Class` **hasMany** `Timetable` via `timetables.class_id`
  - `Section` **hasMany** `Timetable` via `timetables.section_id`
  - `Subject` **hasMany** `Timetable` via `timetables.subject_id`
  - `Teacher` **hasMany** `Timetable` via `timetables.teacher_id`

---

## 4. Attendance Module

### 4.1 `attendance_records`

- **Primary key**
  - `attendance_records.id`

- **Outgoing relationships**
  - `AttendanceRecord` **belongsTo** `Class` via `attendance_records.class_id`
  - `AttendanceRecord` **belongsTo** `Section` via `attendance_records.section_id`
  - `AttendanceRecord` **belongsTo** `Teacher` via `attendance_records.marked_by`
  - `AttendanceRecord` **hasMany** `AttendanceDetail` via `attendance_details.attendance_id`

- **Incoming relationships**
  - `Class` **hasMany** `AttendanceRecord` via `attendance_records.class_id`
  - `Section` **hasMany** `AttendanceRecord` via `attendance_records.section_id`
  - `Teacher` **hasMany** `AttendanceRecord` via `attendance_records.marked_by`
  - `AttendanceDetail` **belongsTo** `AttendanceRecord` via `attendance_details.attendance_id`

---

### 4.2 `attendance_details`

- **Primary key**
  - `attendance_details.id`

- **Outgoing relationships**
  - `AttendanceDetail` **belongsTo** `AttendanceRecord` via `attendance_details.attendance_id`
  - `AttendanceDetail` **belongsTo** `Student` via `attendance_details.student_id`

- **Incoming relationships**
  - `AttendanceRecord` **hasMany** `AttendanceDetail` via `attendance_details.attendance_id`
  - `Student` **hasMany** `AttendanceDetail` via `attendance_details.student_id`

---

## 5. Examination Module

### 5.1 `exams`

- **Primary key**
  - `exams.id`

- **Outgoing relationships**
  - `Exam` **hasMany** `ExamSubject` via `exam_subjects.exam_id`

- **Incoming relationships**
  - `ExamSubject` **belongsTo** `Exam` via `exam_subjects.exam_id`

---

### 5.2 `exam_subjects`

- **Primary key**
  - `exam_subjects.id`

- **Outgoing relationships**
  - `ExamSubject` **belongsTo** `Exam` via `exam_subjects.exam_id`
  - `ExamSubject` **belongsTo** `Subject` via `exam_subjects.subject_id`
  - `ExamSubject` **hasMany** `Grade` via `grades.exam_subject_id`

- **Incoming relationships**
  - `Exam` **hasMany** `ExamSubject` via `exam_subjects.exam_id`
  - `Subject` **hasMany** `ExamSubject` via `exam_subjects.subject_id`
  - `Grade` **belongsTo** `ExamSubject` via `grades.exam_subject_id`

---

### 5.3 `grades`

- **Primary key**
  - `grades.id`

- **Outgoing relationships**
  - `Grade` **belongsTo** `ExamSubject` via `grades.exam_subject_id`
  - `Grade` **belongsTo** `Student` via `grades.student_id`

- **Incoming relationships**
  - `ExamSubject` **hasMany** `Grade` via `grades.exam_subject_id`
  - `Student` **hasMany** `Grade` via `grades.student_id`

---

## 6. Fees Module

### 6.1 `fee_types`

- **Primary key**
  - `fee_types.id`

- **Outgoing relationships**
  - `FeeType` **hasMany** `InvoiceItem` via `invoice_items.fee_type_id`

- **Incoming relationships**
  - `InvoiceItem` **belongsTo** `FeeType` via `invoice_items.fee_type_id`

---

### 6.2 `invoices`

- **Primary key**
  - `invoices.id`

- **Outgoing relationships**
  - `Invoice` **belongsTo** `Student` via `invoices.student_id`
  - `Invoice` **hasMany** `InvoiceItem` via `invoice_items.invoice_id`
  - `Invoice` **hasMany** `Payment` via `payments.invoice_id`

- **Incoming relationships**
  - `Student` **hasMany** `Invoice` via `invoices.student_id`
  - `InvoiceItem` **belongsTo** `Invoice` via `invoice_items.invoice_id`
  - `Payment` **belongsTo** `Invoice` via `payments.invoice_id`

---

### 6.3 `invoice_items`

- **Primary key**
  - `invoice_items.id`

- **Outgoing relationships**
  - `InvoiceItem` **belongsTo** `Invoice` via `invoice_items.invoice_id`
  - `InvoiceItem` **belongsTo** `FeeType` via `invoice_items.fee_type_id`

- **Incoming relationships**
  - `Invoice` **hasMany** `InvoiceItem` via `invoice_items.invoice_id`
  - `FeeType` **hasMany** `InvoiceItem` via `invoice_items.fee_type_id`

---

### 6.4 `payments`

- **Primary key**
  - `payments.id`

- **Outgoing relationships**
  - `Payment` **belongsTo** `Invoice` via `payments.invoice_id`

- **Incoming relationships**
  - `Invoice` **hasMany** `Payment` via `payments.invoice_id`

---

## 7. Library Module

### 7.1 `books`

- **Primary key**
  - `books.id`

- **Outgoing relationships**
  - `Book` **hasMany** `IssuedBook` via `issued_books.book_id`

- **Incoming relationships**
  - `IssuedBook` **belongsTo** `Book` via `issued_books.book_id`

---

### 7.2 `issued_books`

- **Primary key**
  - `issued_books.id`

- **Outgoing relationships**
  - `IssuedBook` **belongsTo** `Book` via `issued_books.book_id`
  - `IssuedBook` **belongsTo** `Student` via `issued_books.issued_to_student_id`

- **Incoming relationships**
  - `Book` **hasMany** `IssuedBook` via `issued_books.book_id`
  - `Student` **hasMany** `IssuedBook` via `issued_books.issued_to_student_id`

---

## 8. Communication Module

### 8.1 `announcements`

- **Primary key**
  - `announcements.id`

- **Outgoing relationships**
  - `Announcement` **belongsTo** `User` via `announcements.created_by`

- **Incoming relationships**
  - `User` **hasMany** `Announcement` via `announcements.created_by`

---

### 8.2 `messages`

- **Primary key**
  - `messages.id`

- **Outgoing relationships**
  - `Message` **belongsTo** `User` as `sender` via `messages.sender_id`
  - `Message` **belongsTo** `User` as `receiver` via `messages.receiver_id`

- **Incoming relationships**
  - `User` **hasMany** `Message` (sent) via `messages.sender_id`
  - `User` **hasMany** `Message` (received) via `messages.receiver_id`

---

## 9. High-Level Relationship Summary (Laravel Terms)

- **User-centric**
  - `User` **hasOne** `Student`, **hasOne** `Teacher`, **hasOne** `Parent`
  - `User` **hasMany** `Message` (sent and received)
  - `User` **hasMany** `Announcement`

- **Student-centric**
  - `Student` **belongsTo** `User`, `Parent`, `Class`, `Section`
  - `Student` **hasMany** `AttendanceDetail`, `Grade`, `Invoice`, `IssuedBook`

- **Teacher-centric**
  - `Teacher` **belongsTo** `User`
  - `Teacher` **hasMany** `Section`, `Subject`, `Timetable`, `AttendanceRecord`

- **Class/Section/Subject**
  - `Class` **hasMany** `Section`, `Subject`, `Student`, `Timetable`, `AttendanceRecord`
  - `Section` **belongsTo** `Class`, `Teacher`; **hasMany** `Student`, `Timetable`, `AttendanceRecord`
  - `Subject` **belongsTo** `Class`, `Teacher`; **hasMany** `Timetable`, `ExamSubject`

- **Assessment**
  - `Exam` **hasMany** `ExamSubject`
  - `ExamSubject` **belongsTo** `Exam`, `Subject`; **hasMany** `Grade`
  - `Grade` **belongsTo** `ExamSubject`, `Student`

- **Fees**
  - `FeeType` **hasMany** `InvoiceItem`
  - `Invoice` **belongsTo** `Student`; **hasMany** `InvoiceItem`, `Payment`
  - `InvoiceItem` **belongsTo** `Invoice`, `FeeType`
  - `Payment` **belongsTo** `Invoice`

- **Library**
  - `Book` **hasMany** `IssuedBook`
  - `IssuedBook` **belongsTo** `Book`, `Student`

# **Database Relationships — School Management System**

This document provides a comprehensive overview of all table relationships in the School Management System database schema.

---

## **Relationship Types**

- **One-to-Many (1:N)**: One record in the parent table relates to many records in the child table
- **Many-to-One (N:1)**: Many records in the child table relate to one record in the parent table
- **Foreign Key (FK)**: The field that establishes the relationship

---

## **1. Users & Authentication Module**

### **users** Table

#### **One-to-Many Relationships:**

1. **users → students**
   - **Relationship**: One user can have many student records
   - **Foreign Key**: `students.user_id` → `users.id`
   - **Type**: 1:N

2. **users → teachers**
   - **Relationship**: One user can have many teacher records
   - **Foreign Key**: `teachers.user_id` → `users.id`
   - **Type**: 1:N

3. **users → parents**
   - **Relationship**: One user can have many parent records
   - **Foreign Key**: `parents.user_id` → `users.id`
   - **Type**: 1:N

4. **users → announcements**
   - **Relationship**: One user can create many announcements
   - **Foreign Key**: `announcements.created_by` → `users.id`
   - **Type**: 1:N

5. **users → messages (as sender)**
   - **Relationship**: One user can send many messages
   - **Foreign Key**: `messages.sender_id` → `users.id`
   - **Type**: 1:N

6. **users → messages (as receiver)**
   - **Relationship**: One user can receive many messages
   - **Foreign Key**: `messages.receiver_id` → `users.id`
   - **Type**: 1:N

---

### **parents** Table

#### **Many-to-One Relationships:**

1. **parents → users**
   - **Relationship**: Many parent records belong to one user
   - **Foreign Key**: `parents.user_id` → `users.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

2. **parents → students**
   - **Relationship**: One parent can have many students (children)
   - **Foreign Key**: `students.parent_id` → `parents.id`
   - **Type**: 1:N

---

### **students** Table

#### **Many-to-One Relationships:**

1. **students → users**
   - **Relationship**: Many student records belong to one user
   - **Foreign Key**: `students.user_id` → `users.id`
   - **Type**: N:1

2. **students → parents**
   - **Relationship**: Many students belong to one parent
   - **Foreign Key**: `students.parent_id` → `parents.id`
   - **Type**: N:1

3. **students → classes**
   - **Relationship**: Many students belong to one class
   - **Foreign Key**: `students.class_id` → `classes.id`
   - **Type**: N:1

4. **students → sections**
   - **Relationship**: Many students belong to one section
   - **Foreign Key**: `students.section_id` → `sections.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

5. **students → attendance_details**
   - **Relationship**: One student can have many attendance records
   - **Foreign Key**: `attendance_details.student_id` → `students.id`
   - **Type**: 1:N

6. **students → grades**
   - **Relationship**: One student can have many grade records
   - **Foreign Key**: `grades.student_id` → `students.id`
   - **Type**: 1:N

7. **students → invoices**
   - **Relationship**: One student can have many invoices
   - **Foreign Key**: `invoices.student_id` → `students.id`
   - **Type**: 1:N

8. **students → issued_books**
   - **Relationship**: One student can have many book issues
   - **Foreign Key**: `issued_books.issued_to_student_id` → `students.id`
   - **Type**: 1:N

---

### **teachers** Table

#### **Many-to-One Relationships:**

1. **teachers → users**
   - **Relationship**: Many teacher records belong to one user
   - **Foreign Key**: `teachers.user_id` → `users.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

2. **teachers → sections**
   - **Relationship**: One teacher can be assigned to many sections (as class teacher)
   - **Foreign Key**: `sections.teacher_id` → `teachers.id`
   - **Type**: 1:N

3. **teachers → subjects**
   - **Relationship**: One teacher can teach many subjects
   - **Foreign Key**: `subjects.teacher_id` → `teachers.id`
   - **Type**: 1:N

4. **teachers → timetables**
   - **Relationship**: One teacher can have many timetable entries
   - **Foreign Key**: `timetables.teacher_id` → `teachers.id`
   - **Type**: 1:N

5. **teachers → attendance_records**
   - **Relationship**: One teacher can mark many attendance records
   - **Foreign Key**: `attendance_records.marked_by` → `teachers.id`
   - **Type**: 1:N

---

## **2. Academic Module**

### **classes** Table

#### **One-to-Many Relationships:**

1. **classes → sections**
   - **Relationship**: One class can have many sections
   - **Foreign Key**: `sections.class_id` → `classes.id`
   - **Type**: 1:N

2. **classes → subjects**
   - **Relationship**: One class can have many subjects
   - **Foreign Key**: `subjects.class_id` → `classes.id`
   - **Type**: 1:N

3. **classes → students**
   - **Relationship**: One class can have many students
   - **Foreign Key**: `students.class_id` → `classes.id`
   - **Type**: 1:N

4. **classes → timetables**
   - **Relationship**: One class can have many timetable entries
   - **Foreign Key**: `timetables.class_id` → `classes.id`
   - **Type**: 1:N

5. **classes → attendance_records**
   - **Relationship**: One class can have many attendance records
   - **Foreign Key**: `attendance_records.class_id` → `classes.id`
   - **Type**: 1:N

---

### **sections** Table

#### **Many-to-One Relationships:**

1. **sections → classes**
   - **Relationship**: Many sections belong to one class
   - **Foreign Key**: `sections.class_id` → `classes.id`
   - **Type**: N:1

2. **sections → teachers**
   - **Relationship**: Many sections can be assigned to one teacher (class teacher)
   - **Foreign Key**: `sections.teacher_id` → `teachers.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

3. **sections → students**
   - **Relationship**: One section can have many students
   - **Foreign Key**: `students.section_id` → `sections.id`
   - **Type**: 1:N

4. **sections → timetables**
   - **Relationship**: One section can have many timetable entries
   - **Foreign Key**: `timetables.section_id` → `sections.id`
   - **Type**: 1:N

5. **sections → attendance_records**
   - **Relationship**: One section can have many attendance records
   - **Foreign Key**: `attendance_records.section_id` → `sections.id`
   - **Type**: 1:N

---

### **subjects** Table

#### **Many-to-One Relationships:**

1. **subjects → classes**
   - **Relationship**: Many subjects belong to one class
   - **Foreign Key**: `subjects.class_id` → `classes.id`
   - **Type**: N:1

2. **subjects → teachers**
   - **Relationship**: Many subjects can be taught by one teacher
   - **Foreign Key**: `subjects.teacher_id` → `teachers.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

3. **subjects → exam_subjects**
   - **Relationship**: One subject can appear in many exam subjects
   - **Foreign Key**: `exam_subjects.subject_id` → `subjects.id`
   - **Type**: 1:N

4. **subjects → timetables**
   - **Relationship**: One subject can have many timetable entries
   - **Foreign Key**: `timetables.subject_id` → `subjects.id`
   - **Type**: 1:N

---

## **3. Timetable Module**

### **timetables** Table

#### **Many-to-One Relationships:**

1. **timetables → classes**
   - **Relationship**: Many timetable entries belong to one class
   - **Foreign Key**: `timetables.class_id` → `classes.id`
   - **Type**: N:1

2. **timetables → sections**
   - **Relationship**: Many timetable entries belong to one section
   - **Foreign Key**: `timetables.section_id` → `sections.id`
   - **Type**: N:1

3. **timetables → subjects**
   - **Relationship**: Many timetable entries belong to one subject
   - **Foreign Key**: `timetables.subject_id` → `subjects.id`
   - **Type**: N:1

4. **timetables → teachers**
   - **Relationship**: Many timetable entries belong to one teacher
   - **Foreign Key**: `timetables.teacher_id` → `teachers.id`
   - **Type**: N:1

---

## **4. Attendance Module**

### **attendance_records** Table

#### **Many-to-One Relationships:**

1. **attendance_records → classes**
   - **Relationship**: Many attendance records belong to one class
   - **Foreign Key**: `attendance_records.class_id` → `classes.id`
   - **Type**: N:1

2. **attendance_records → sections**
   - **Relationship**: Many attendance records belong to one section
   - **Foreign Key**: `attendance_records.section_id` → `sections.id`
   - **Type**: N:1

3. **attendance_records → teachers**
   - **Relationship**: Many attendance records are marked by one teacher
   - **Foreign Key**: `attendance_records.marked_by` → `teachers.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

4. **attendance_records → attendance_details**
   - **Relationship**: One attendance record can have many attendance details (one per student)
   - **Foreign Key**: `attendance_details.attendance_id` → `attendance_records.id`
   - **Type**: 1:N

---

### **attendance_details** Table

#### **Many-to-One Relationships:**

1. **attendance_details → attendance_records**
   - **Relationship**: Many attendance details belong to one attendance record
   - **Foreign Key**: `attendance_details.attendance_id` → `attendance_records.id`
   - **Type**: N:1

2. **attendance_details → students**
   - **Relationship**: Many attendance details belong to one student
   - **Foreign Key**: `attendance_details.student_id` → `students.id`
   - **Type**: N:1

---

## **5. Examination Module**

### **exams** Table

#### **One-to-Many Relationships:**

1. **exams → exam_subjects**
   - **Relationship**: One exam can have many exam subjects
   - **Foreign Key**: `exam_subjects.exam_id` → `exams.id`
   - **Type**: 1:N

---

### **exam_subjects** Table

#### **Many-to-One Relationships:**

1. **exam_subjects → exams**
   - **Relationship**: Many exam subjects belong to one exam
   - **Foreign Key**: `exam_subjects.exam_id` → `exams.id`
   - **Type**: N:1

2. **exam_subjects → subjects**
   - **Relationship**: Many exam subjects belong to one subject
   - **Foreign Key**: `exam_subjects.subject_id` → `subjects.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

3. **exam_subjects → grades**
   - **Relationship**: One exam subject can have many grades (one per student)
   - **Foreign Key**: `grades.exam_subject_id` → `exam_subjects.id`
   - **Type**: 1:N

---

### **grades** Table

#### **Many-to-One Relationships:**

1. **grades → exam_subjects**
   - **Relationship**: Many grades belong to one exam subject
   - **Foreign Key**: `grades.exam_subject_id` → `exam_subjects.id`
   - **Type**: N:1

2. **grades → students**
   - **Relationship**: Many grades belong to one student
   - **Foreign Key**: `grades.student_id` → `students.id`
   - **Type**: N:1

---

## **6. Fees Module**

### **fee_types** Table

#### **One-to-Many Relationships:**

1. **fee_types → invoice_items**
   - **Relationship**: One fee type can appear in many invoice items
   - **Foreign Key**: `invoice_items.fee_type_id` → `fee_types.id`
   - **Type**: 1:N

---

### **invoices** Table

#### **Many-to-One Relationships:**

1. **invoices → students**
   - **Relationship**: Many invoices belong to one student
   - **Foreign Key**: `invoices.student_id` → `students.id`
   - **Type**: N:1

#### **One-to-Many Relationships:**

2. **invoices → invoice_items**
   - **Relationship**: One invoice can have many invoice items
   - **Foreign Key**: `invoice_items.invoice_id` → `invoices.id`
   - **Type**: 1:N

3. **invoices → payments**
   - **Relationship**: One invoice can have many payments (partial payments)
   - **Foreign Key**: `payments.invoice_id` → `invoices.id`
   - **Type**: 1:N

---

### **invoice_items** Table

#### **Many-to-One Relationships:**

1. **invoice_items → invoices**
   - **Relationship**: Many invoice items belong to one invoice
   - **Foreign Key**: `invoice_items.invoice_id` → `invoices.id`
   - **Type**: N:1

2. **invoice_items → fee_types**
   - **Relationship**: Many invoice items belong to one fee type
   - **Foreign Key**: `invoice_items.fee_type_id` → `fee_types.id`
   - **Type**: N:1

---

### **payments** Table

#### **Many-to-One Relationships:**

1. **payments → invoices**
   - **Relationship**: Many payments belong to one invoice
   - **Foreign Key**: `payments.invoice_id` → `invoices.id`
   - **Type**: N:1

---

## **7. Library Module**

### **books** Table

#### **One-to-Many Relationships:**

1. **books → issued_books**
   - **Relationship**: One book can have many issue records
   - **Foreign Key**: `issued_books.book_id` → `books.id`
   - **Type**: 1:N

---

### **issued_books** Table

#### **Many-to-One Relationships:**

1. **issued_books → books**
   - **Relationship**: Many issued book records belong to one book
   - **Foreign Key**: `issued_books.book_id` → `books.id`
   - **Type**: N:1

2. **issued_books → students**
   - **Relationship**: Many issued book records belong to one student
   - **Foreign Key**: `issued_books.issued_to_student_id` → `students.id`
   - **Type**: N:1

---

## **8. Communication Module**

### **announcements** Table

#### **Many-to-One Relationships:**

1. **announcements → users**
   - **Relationship**: Many announcements are created by one user
   - **Foreign Key**: `announcements.created_by` → `users.id`
   - **Type**: N:1

---

### **messages** Table

#### **Many-to-One Relationships:**

1. **messages → users (as sender)**
   - **Relationship**: Many messages are sent by one user
   - **Foreign Key**: `messages.sender_id` → `users.id`
   - **Type**: N:1

2. **messages → users (as receiver)**
   - **Relationship**: Many messages are received by one user
   - **Foreign Key**: `messages.receiver_id` → `users.id`
   - **Type**: N:1

---

## **Summary: Relationship Count by Table**

| Table | Incoming Relationships | Outgoing Relationships | Total |
|-------|----------------------|----------------------|-------|
| **users** | 0 | 6 | 6 |
| **parents** | 1 | 1 | 2 |
| **students** | 4 | 4 | 8 |
| **teachers** | 1 | 4 | 5 |
| **classes** | 0 | 5 | 5 |
| **sections** | 2 | 3 | 5 |
| **subjects** | 2 | 2 | 4 |
| **timetables** | 4 | 0 | 4 |
| **attendance_records** | 3 | 1 | 4 |
| **attendance_details** | 2 | 0 | 2 |
| **exams** | 0 | 1 | 1 |
| **exam_subjects** | 2 | 1 | 3 |
| **grades** | 2 | 0 | 2 |
| **fee_types** | 0 | 1 | 1 |
| **invoices** | 1 | 2 | 3 |
| **invoice_items** | 2 | 0 | 2 |
| **payments** | 1 | 0 | 1 |
| **books** | 0 | 1 | 1 |
| **issued_books** | 2 | 0 | 2 |
| **announcements** | 1 | 0 | 1 |
| **messages** | 2 | 0 | 2 |

---

## **Key Relationship Patterns**

### **Central Hub Tables:**
- **users**: Central authentication table with 6 outgoing relationships
- **students**: Core entity with 8 total relationships (4 incoming, 4 outgoing)
- **classes**: Academic structure hub with 5 outgoing relationships

### **Bridge/Junction Tables:**
- **timetables**: Connects classes, sections, subjects, and teachers
- **attendance_details**: Connects attendance_records and students
- **exam_subjects**: Connects exams and subjects
- **grades**: Connects exam_subjects and students
- **invoice_items**: Connects invoices and fee_types
- **payments**: Connects invoices (for partial payment tracking)

### **Hierarchical Structures:**
- **users → parents/teachers/students**: User role extensions
- **classes → sections → students**: Academic hierarchy
- **exams → exam_subjects → grades**: Examination structure
- **invoices → invoice_items**: Invoice composition

---

**Document Version**: 1.0  
**Last Updated**: Based on Database Schema (Full Table List + Fields).md
