# ASCOM Course Syllabus System - Test Guide

## Quick Test Flow

### Test Setup (Already Done)
- Database migration completed ✅
- Test data created:
  - Course CS101 (id=1) - Introduction to Computer Science
  - Teacher "Test" (id=10) - Assigned to CS101 + Program Head of BSCS
  - Dean "CCS DEAN" (id=8) - Dean of CCS department

---

## Testing the Complete Workflow

### Step 1: Teacher Creates Syllabus

1. Login as teacher (employee: TCH999, password: test123)
2. Navigate to **"Course Syllabus"** 
3. Click on "Create Syllabus" for CS101
4. Fill in the form:
   - Course Description: "Introduction to fundamental programming concepts..."
   - Expected Outcomes: "Students will be able to write basic programs..."
   - Add a book (e.g., "Python Programming" by Smith)
   - Add learning plan (Week 1: Variables, Week 2: Control Structures)
   - Add exam schedules (Prelim: Week 4, Midterm: Week 8)
   - Add grading (Exams 40%, Projects 30%, Quizzes 20%, Participation 10%)
   - Add requirements: "Attendance, lab exercises, final project"
   - Add remote policies: "Camera on for discussions"
5. Click **"Submit for Approval"**
6. Check status changes to "Submitted"

---

### Step 2: Program Head Reviews

1. Login as teacher who is also PH (same TCH999)
2. The navigation should show **"Program Head"** option
3. Go to **"Program Head"** dashboard
4. You should see CS101 in "Syllabus Review Queue"
5. Click **Approve** or **Request Revision**
6. If approved, status becomes "PH Approved"

---

### Step 3: Dean Final Approval

1. Login as dean (employee: 000001)
2. Navigate to **"Syllabus Management"**
3. You should see CS101 in "Pending Your Approval"
4. Click **View** to see the full syllabus
5. Click **Approve** to give final approval
6. Status becomes "Dean Approved" - Complete!

---

## API Testing (Optional)

### Check Teacher's Assigned Courses
```bash
curl "http://localhost:8080/teachers/api/get_assigned_courses.php"
```

### Get Syllabus Data
```bash
curl "http://localhost:8080/teachers/api/get_syllabus_data.php?course_id=1"
```

### Save Syllabus (as Teacher)
```bash
curl -X POST "http://localhost:8080/teachers/api/save_syllabus.php" \
  -H "Content-Type: application/json" \
  -d '{"course_id":1,"teacher_id":10,"course_description":"Test","status":"draft"}'
```

### Submit Syllabus (as Teacher)
```bash
curl -X POST "http://localhost:8080/teachers/api/submit_syllabus.php" \
  -H "Content-Type: application/json" \
  -d '{"course_id":1,"teacher_id":10,"course_description":"Test description..."}'
```

### PH Approve (as Program Head)
```bash
curl -X POST "http://localhost:8080/teachers/api/ph_approve_syllabus.php" \
  -H "Content-Type: application/json" \
  -d '{"syllabus_id":1}'
```

### Dean Approve (as Dean)
```bash
curl -X POST "http://localhost:8080/department-dean/api/dean_approve_syllabus.php" \
  -H "Content-Type: application/json" \
  -d '{"syllabus_id":1,"comments":"Approved for this semester"}'
```

---

## Troubleshooting

### "No Courses Assigned" when Teacher
- Check course_assignments table has the teacher assigned to courses

### "Program Head" option not showing in Teacher navigation
- Check program_heads table has the teacher assigned to a program with is_active=1

### "Syllabus Management" not showing in Dean navigation
- Check the navigation update was applied to content.php

### Database errors during API calls
- Check course_syllabi table exists: `SHOW TABLES LIKE 'course_syllabi';`

---

## Test Credentials (Already Set Up)

| Role | Employee No | Password |
|------|-------------|----------|
| Dean CCS | 000001 | test123 |
| Teacher + PH | TCH999 | test123 |

---

## Manual Testing Checklist

- [ ] Teacher can access Course Syllabus page
- [ ] Teacher can see assigned courses listed
- [ ] Teacher can open syllabus modal
- [ ] Teacher can fill in all syllabus fields
- [ ] Teacher can save as draft
- [ ] Teacher can submit for approval
- [ ] Teacher (who is also PH) sees Program Head nav option
- [ ] Program Head can see pending syllabi
- [ ] Program Head can approve syllabus
- [ ] Program Head can request revision
- [ ] Dean can see Syllabus Management
- [ ] Dean can view submitted syllabi
- [ ] Dean can give final approval
- [ ] Dean can request revision
- [ ] Status updates correctly throughout the workflow

---

## Need Help?

Check the main workflow documentation: `COURSE_SYLLABUS_WORKFLOW.md`

Database migration: `database/migration_add_syllabus_system_fixed.sql`