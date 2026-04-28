# Course Syllabus Workflow Documentation

## Overview
This document describes the complete workflow for managing programs, courses, and course syllabi in the ASCOM system.

## Roles and Responsibilities

### Dean
- Creates and manages programs
- Assigns one Program Head per program
- Creates and manages courses within programs
- Assigns teachers to courses
- Reviews and gives final approval to teacher syllabi

### Program Head
- Manages programs and courses assigned by the dean
- Provides program details and course outlines
- Reviews teacher course syllabi
- Approves or requests revisions on syllabi
- Teachers can have dual roles (Teacher + Program Head)

### Teacher
- Writes course syllabi for assigned courses
- Submits syllabi for approval
- Revises syllabi based on feedback
- If also a Program Head, can switch between roles

---

## Complete Workflow

### Phase 1: Program Setup (Dean)
```
Dean creates Program
    ↓
Dean assigns Program Head (one per program)
    ↓
Program Head is now linked to the program
```

### Phase 2: Course Creation (Dean)
```
Dean creates Course within a Program
    ↓
Dean assigns Teacher to Course
    ↓
Teacher is notified of assignment
```

### Phase 3: Syllabus Creation (Teacher)
```
Teacher logs in
    ↓
Navigates to "Course Syllabus" section
    ↓
Selects assigned course
    ↓
Fills out syllabus form with:
    - Course Description
    - Expected Course Outcomes (PEO)
    - Books, E-books, Web Resources
    - Learning Plan (weekly breakdown)
    - Exam Schedules (Prelim, Midterm, Prefinal, Final)
    - Grading System
    - Course Requirements & Expectations
    - Remote/Online Classroom Policies
    - References (APA 7 format)
    ↓
Teacher saves as draft OR submits for approval
```

### Phase 4: Program Head Review
```
Program Head reviews submitted syllabi
    ↓
Approves or Requests Revision
    ↓
If approved → Status becomes "PH Approved" → Moves to Dean
If revision → Teacher revises and resubmits
```

### Phase 5: Dean Final Approval
```
Dean reviews "PH Approved" syllabi
    ↓
Gives final approval OR requests revision
    ↓
If approved → Status becomes "Dean Approved" → Complete!
If revision → Teacher revises → Goes through review again
```

---

## Syllabus Contents

### Required Fields
1. **Course Description** - Detailed description of the course
2. **Expected Course Outcomes (PEO)** - Program Educational Objectives
3. **Books** - Textbooks with title, author, ISBN, publisher, year, edition
4. **E-books** - Digital resources with URL and access date
5. **Web Resources** - Online articles with URL and description
6. **Learning Plan** - Weekly breakdown with topics, activities, assessments
7. **Exam Schedules** - For each exam:
   - Prelim Examination
   - Midterm Examination
   - Prefinal Examination
   - Final Examination
   - Each includes: duration (minutes), weeks covered, topics
8. **Grading System** - Components with percentages (must total 100%)
9. **Course Requirements** - Attendance, projects, labs, etc.
10. **Course Expectations** - What teacher expects from students
11. **Remote/Online Classroom Policies** - Online learning guidelines
12. **References** - APA 7th Edition format

---

## Database Schema Changes

### New Tables
- `course_syllabi` - Stores all syllabus data

### Modified Tables
- `programs` - Added `created_by` field
- `courses` - Added `created_by`, `owner_type`, `syllabus_status`, `last_syllabus_update`
- `course_assignments` - Added syllabus tracking fields
- `roles` - Added `program_head` role

### Syllabus Status Values
```
draft → submitted → ph_review → ph_approved → dean_approved
                  ↓
           revision_requested (can go back to draft)
```

---

## File Locations

### Database Scripts
- `database/migration_add_syllabus_system.sql` - Complete migration script

### Teacher Portal
- `teachers/content/course-syllabus.php` - Syllabus management page
- `teachers/content/program-head-dashboard.php` - PH dashboard
- `teachers/modals/syllabus_modal.php` - Full syllabus form
- `teachers/api/save_syllabus.php` - Save draft
- `teachers/api/submit_syllabus.php` - Submit for approval
- `teachers/api/get_assigned_courses.php` - Get courses
- `teachers/api/get_syllabus_data.php` - Load syllabus
- `teachers/api/ph_approve_syllabus.php` - PH approval
- `teachers/api/ph_request_revision.php` - PH revision
- `teachers/api/get_program_head_data.php` - PH dashboard data

### Dean Portal
- `department-dean/syllabus-management-content/syllabus-management.php` - Main interface
- `department-dean/api/get_syllabus_summary.php` - Stats summary
- `department-dean/api/get_syllabus_details.php` - Detailed view
- `department-dean/api/dean_approve_syllabus.php` - Dean approval
- `department-dean/api/dean_request_revision.php` - Dean revision

---

## API Endpoints Summary

### Teacher APIs
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/get_assigned_courses.php` | GET | Get teacher's assigned courses with syllabus status |
| `api/get_syllabus_data.php` | GET | Load existing syllabus for a course |
| `api/save_syllabus.php` | POST | Save syllabus as draft |
| `api/submit_syllabus.php` | POST | Submit syllabus for approval |
| `api/ph_approve_syllabus.php` | POST | Program head approves |
| `api/ph_request_revision.php` | POST | Program head requests revision |
| `api/get_program_head_data.php` | GET | Get PH dashboard data |

### Dean APIs
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/get_syllabus_summary.php` | GET | Get all syllabus stats |
| `api/get_syllabus_details.php` | GET | Get detailed syllabus info |
| `api/dean_approve_syllabus.php` | POST | Dean gives final approval |
| `api/dean_request_revision.php` | POST | Dean requests revision |

---

## Dual Interface for Program Head Teachers

Teachers who are also Program Heads will see additional navigation:
1. **Course Syllabus** - For writing syllabi (teacher role)
2. **Program Head** - For managing programs and reviewing syllabi (PH role)

They can switch between roles using the role switching functionality.

---

## Testing Checklist

### Database
- [ ] Run `database/migration_add_syllabus_system.sql`
- [ ] Verify all tables are created correctly
- [ ] Check foreign keys are working

### Dean Workflow
- [ ] Create a program
- [ ] Assign a program head
- [ ] Create courses
- [ ] Assign teachers to courses
- [ ] View syllabus management dashboard
- [ ] Approve a PH-approved syllabus

### Program Head Workflow
- [ ] View program dashboard
- [ ] See assigned program and courses
- [ ] Review submitted syllabi
- [ ] Approve a syllabus
- [ ] Request revision on a syllabus

### Teacher Workflow
- [ ] View assigned courses
- [ ] Open syllabus modal
- [ ] Fill in all syllabus fields
- [ ] Save as draft
- [ ] Submit syllabus
- [ ] See revision feedback
- [ ] Revise and resubmit