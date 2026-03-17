# Book References Database Implementation

## Overview
This document describes the book references database system for managing book references associated with courses.

## Database Table Structure

### Table Name: `book_references`

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) | Primary key (auto increment) |
| `course_id` | INT(11) | Foreign key to courses table |
| `title` | VARCHAR(255) | Book reference title |
| `isbn` | VARCHAR(20) | ISBN number (optional) |
| `publisher` | VARCHAR(150) | Publisher name (optional) |
| `copyright_year` | VARCHAR(10) | Copyright year (optional) |
| `edition` | VARCHAR(50) | Edition information (optional) |
| `location` | VARCHAR(255) | Physical location (optional) |
| `call_number` | VARCHAR(100) | Library call number (optional) |
| `created_by` | INT(11) | User ID who created this reference |
| `requested_by` | INT(11) | User ID who requested this reference |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last update timestamp |

### Relationships
- **Foreign Key**: `course_id` → `courses.id` (CASCADE on delete)
- **Foreign Key**: `created_by` → `users.id` (SET NULL on delete)
- **Foreign Key**: `requested_by` → `users.id` (SET NULL on delete)

### Indexes
- Primary key on `id`
- Index on `course_id` for faster lookups
- Index on `created_by` for filtering by creator
- Index on `requested_by` for filtering by requester

## Files Created

### 1. Database Files
- **`database/create_book_references_table.sql`**
  - SQL script to create the book_references table
  - Can be executed directly in phpMyAdmin or MySQL client

- **`setup_book_references_table.php`**
  - Web-based setup script
  - Run once to create the table with visual feedback
  - Access via: `http://localhost/your-path/department-dean/setup_book_references_table.php`

### 2. API Endpoint
- **`api/get_book_references.php`**
  - Fetches book references for a specific course
  - **Parameters**: `course_code` (required)
  - **Returns**: JSON with references array

  **Example Usage:**
  ```javascript
  fetch('api/get_book_references.php?course_code=IT101')
    .then(response => response.json())
    .then(data => {
      console.log(data.references);
    });
  ```

  **Response Format:**
  ```json
  {
    "status": "success",
    "references": [
      {
        "id": 1,
        "title": "Introduction to Information Technology",
        "isbn": "978-0123456789",
        "publisher": "Tech Press",
        "copyright_year": "2023",
        "edition": "3rd Edition",
        "location": "Main Library - IT Section",
        "call_number": "IT 001.1 J64 2023",
        "created_by": 5,
        "requested_by": 12,
        "created_by_name": "Dr. Sarah Johnson",
        "requested_by_name": "Prof. Michael Brown",
        "created_at": "2024-01-15 10:30:00",
        "updated_at": "2024-01-15 10:30:00"
      }
    ],
    "count": 1
  }
  ```

### 3. Updated Files
- **`all_courses-content/all-courses.php`**
  - Updated to show empty book references list by default
  - Sample data commented out for reference
  - Updated `createBookReferenceHTML()` function to display new fields:
    - Created by / Requested by information
    - ISBN, Publisher, Copyright, Edition
    - Location and Call Number
  - Changed "Request Book" button to "Edit" button

## Setup Instructions

### Step 1: Create the Database Table
Choose one of these methods:

**Method A: Using the Web Setup Script (Recommended)**
1. Open your browser
2. Navigate to: `http://localhost/your-path/department-dean/setup_book_references_table.php`
3. The script will automatically create the table and show you the results
4. You'll see a success message with table statistics

**Method B: Using SQL Script**
1. Open phpMyAdmin
2. Select your database
3. Go to SQL tab
4. Copy and paste the contents of `database/create_book_references_table.sql`
5. Click "Go" to execute

**Method C: Using MySQL Command Line**
```bash
mysql -u your_username -p your_database < database/create_book_references_table.sql
```

### Step 2: Verify Installation
1. Check in phpMyAdmin that the `book_references` table exists
2. Verify all columns and foreign keys are created
3. The table should be empty initially

### Step 3: Test the API
1. Open browser developer tools (F12)
2. Navigate to All Courses page
3. Click on any course to open the Course Information modal
4. The book references section should show: "No book references available for this course."

## Current State

### What's Working
✅ Database table structure created  
✅ Foreign key relationships established  
✅ API endpoint to fetch book references  
✅ Course Information modal shows empty list  
✅ Updated display to show new fields  

### What's Empty/TODO
- No book references data exists yet (table is empty)
- Add Book Reference functionality (not yet implemented)
- Edit Book Reference functionality (not yet implemented)
- Delete Book Reference functionality (not yet implemented)
- AJAX integration to load references dynamically (needs implementation)

## Future Enhancements

### To Implement Next:
1. **Add Book Reference Modal**
   - Form to add new book references
   - Fields for all book information
   - Auto-populate created_by with current user
   - Dropdown to select requested_by user

2. **Edit Book Reference**
   - Modal to edit existing references
   - Pre-fill form with existing data
   - Update functionality

3. **Delete Book Reference**
   - Confirmation modal
   - Soft delete option (add is_active field)

4. **AJAX Integration**
   - Load references dynamically when modal opens
   - No page refresh needed
   - Real-time updates

5. **Search & Filter**
   - Search book references by title, ISBN, publisher
   - Filter by year, edition
   - Sort by various fields

6. **Bulk Operations**
   - Import book references from CSV
   - Export to CSV/Excel
   - Bulk delete

7. **Integration with Library System**
   - Link to library catalog
   - Real-time availability checking
   - Reservation system

## Notes
- All sample/dummy book reference data has been removed
- The system is now ready for real data to be added
- Database table uses UTF-8 encoding for international character support
- Timestamps are automatically managed by MySQL
- Foreign key constraints ensure data integrity

## Support
If you encounter any issues during setup, please check:
1. Database connection is working (`includes/db_connection.php`)
2. Courses table exists (required for foreign key)
3. Users table exists (required for foreign key)
4. MySQL user has CREATE and ALTER privileges

