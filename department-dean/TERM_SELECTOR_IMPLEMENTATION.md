# Academic Term Selector Implementation

## Overview
Added an academic term selector to the Department Dean dashboard that allows filtering data by academic term (e.g., "1st Semester 2024-2025").

## Features Implemented

### 1. **Dropdown Selector**
- Displays all available academic terms in the format: "Term Name School-Year"
- Example: "1st Semester 2024-2025", "2nd Semester 2024-2025", "Summer Semester 2024-2025"
- Automatically selects the current active term on page load
- Selection persists across page refreshes using sessionStorage

### 2. **Current Term Button**
- Quick action button to select the currently active term
- Shows an alert if no current term is active in the system

### 3. **Visual Feedback**
- Beautiful notification appears when a term is selected
- Shows the selected term name
- Automatically disappears after 3 seconds with smooth animation

### 4. **Location**
Placed below the Department Code badge, above the "Overview" section for optimal visibility and functionality.

## Files Modified

### 1. `department-dean/api/get_academic_terms.php` (NEW)
- API endpoint to fetch academic terms with school year information
- Returns current active term
- Can be used by other components if needed

### 2. `department-dean/dashboard-content/dashboard.php` (MODIFIED)
- Added PHP code to fetch academic terms from database
- Added CSS styles for term selector UI
- Added HTML markup for dropdown and button
- Added JavaScript functionality for term selection and persistence

## Database Tables Used

### `school_terms`
- `id` - Term ID
- `title` - Term name (e.g., "1st Semester", "2nd Semester", "Summer Semester")
- `school_year_id` - Foreign key to school_years
- `start_date` - Term start date
- `end_date` - Term end date
- `status` - 'Active' or 'Inactive'

### `school_years`
- `id` - School year ID
- `school_year_label` - Year label (e.g., "2024-2025")
- `start_date` - Year start date
- `end_date` - Year end date
- `status` - 'Active' or 'Inactive'

## JavaScript Functions Available

### `getSelectedTermId()`
Returns the currently selected term ID.

### `getSelectedTerm()`
Returns the complete term object with all properties.

### `handleTermChange(termId)`
Handles term selection change and updates the UI.

### `selectCurrentTerm()`
Selects the current active term.

## Future Enhancements

The current implementation stores the selected term but doesn't filter dashboard data yet. To implement data filtering:

1. Modify the database queries in dashboard.php to include `WHERE` clauses filtering by the selected term
2. Add AJAX functionality to refresh dashboard data without page reload
3. Filter course material requests by term
4. Filter faculty and program statistics by term

Example query modification:
```php
// Add to existing queries
$selectedTermId = $_SESSION['selected_term_id'] ?? null;

if ($selectedTermId) {
    // Add term filtering to queries
    $query .= " AND term_id = ?";
    $params[] = $selectedTermId;
}
```

## Testing

To test the implementation:
1. Navigate to the Department Dean dashboard
2. You should see the term selector below the Department Code
3. Select different terms from the dropdown
4. Click "Current Term" to jump to the active term
5. Refresh the page - your selection should persist
6. Check browser console for debug information

## Notes

- The selected term is stored in `sessionStorage` and persists across page refreshes within the same session
- The term selector automatically selects the current active term on first load
- If no active term exists, it falls back to the most recent term
- The implementation uses TT Interphases font to match the existing design system
- Colors match the existing blue theme (#1976d2)

