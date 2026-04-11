<?php
// super_admin-mis/school_calendar-content/school-calendar.php
// ENABLE REAL DATABASE QUERIES

global $conn; // Access the global connection variable

// Initialize arrays for real data
$school_years_for_dropdown = [];
$currentTerm = null;

// Check if school_years table exists
$table_check = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($table_check->num_rows === 0) {
    // Create school_years table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS school_years (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_year_label VARCHAR(50) UNIQUE NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table)) {
    }
}

// Check if school_terms table exists
$terms_table_check = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($terms_table_check->num_rows === 0) {
    // Create school_terms table if it doesn't exist
    $create_terms_table = "CREATE TABLE IF NOT EXISTS school_terms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        school_year_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_terms_table)) {
    }
}

// Fetch school years from database - check column names first
try {
    // Initialize the array first
    $school_years_for_dropdown = [];
    
    $columns_check = $conn->query("DESCRIBE school_years");
    if ($columns_check === false) {
        throw new Exception("Failed to describe school_years table");
    }
    
    $columns = [];
    while ($row = $columns_check->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    // Initialize SQL query variable
    $sql_sy = "";

    if (in_array('school_year_label', $columns)) {
        // New structure - get current school year first
        $current_year_sql = "SELECT school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
        $current_result = $conn->query($current_year_sql);
        $current_school_year = null;
        
        if ($current_result && $current_result->num_rows > 0) {
            $current_row = $current_result->fetch_assoc();
            $current_school_year = $current_row['school_year_label'];
        }
        
        if ($current_school_year && !empty($current_school_year)) {
            // Extract year from current school year (e.g., "A.Y. 2025 - 2026" -> 2025)
            // Handle the "A.Y. YYYY - YYYY" format
            if (preg_match('/(\d{4})/', $current_school_year, $matches)) {
                $current_year = intval($matches[1]);
            } else {
                $current_year = intval(explode('-', $current_school_year)[0]);
            }
            $min_year = $current_year - 5; // 5 years behind
            
            // Get all school years, but we'll filter them in PHP
            $sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
        } else {
            // Fallback if no current school year found
            $sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
        }
    } else {
        // Old structure - use existing columns
        $current_year_sql = "SELECT CONCAT(year_start, '-', year_end) as school_year_label FROM school_years WHERE is_active = 1 ORDER BY start_date DESC LIMIT 1";
        $current_result = $conn->query($current_year_sql);
        $current_school_year = null;
        
        if ($current_result && $current_result->num_rows > 0) {
            $current_row = $current_result->fetch_assoc();
            $current_school_year = $current_row['school_year_label'];
        }
        
        if ($current_school_year && !empty($current_school_year)) {
            // Extract year from current school year (e.g., "A.Y. 2025 - 2026" -> 2025)
            // Handle the "A.Y. YYYY - YYYY" format
            if (preg_match('/(\d{4})/', $current_school_year, $matches)) {
                $current_year = intval($matches[1]);
            } else {
                $current_year = intval(explode('-', $current_school_year)[0]);
            }
            $min_year = $current_year - 5; // 5 years behind
            
            // Get all school years, but we'll filter them in PHP
            $sql_sy = "SELECT id, CONCAT(year_start, '-', year_end) as school_year_label, 
                       CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as status,
                       start_date, end_date 
                       FROM school_years ORDER BY year_start DESC";
        } else {
            // Fallback if no current school year found
            $sql_sy = "SELECT id, CONCAT(year_start, '-', year_end) as school_year_label, 
                       CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as status,
                       start_date, end_date 
                       FROM school_years ORDER BY year_start DESC";
        }
    }

    // Only execute query if it's not empty
    if (!empty($sql_sy)) {
        $result_sy = $conn->query($sql_sy);
        if ($result_sy && $result_sy->num_rows > 0) {
            while($row = $result_sy->fetch_assoc()) {
                $school_years_for_dropdown[] = $row;
            }
        }
        
        // Filter school years based on current year logic
        if (isset($current_school_year) && $current_school_year && !empty($current_school_year) && isset($min_year)) {
            $filtered_school_years = [];
            
            foreach ($school_years_for_dropdown as $sy) {
                $year_label = $sy['school_year_label'];
                if (!empty($year_label)) {
                    // Extract year from school year label (e.g., "A.Y. 2025 - 2026" -> 2025)
                    // Handle the "A.Y. YYYY - YYYY" format
                    if (preg_match('/(\d{4})/', $year_label, $matches)) {
                        $year_start = intval($matches[1]);
                    } else {
                        $year_start = intval(explode('-', $year_label)[0]);
                    }
                    
                    // Include if:
                    // 1. It's the current school year
                    // 2. It's within 5 years behind the current year (but not future years)
                    if ($year_label === $current_school_year || 
                        ($year_start >= $min_year && $year_start <= $current_year)) {
                        $filtered_school_years[] = $sy;
                    }
                    
                    // Debug logging
                             ($year_label === $current_school_year || ($year_start >= $min_year && $year_start <= $current_year) ? "INCLUDED" : "EXCLUDED"));
                }
            }
            
            $school_years_for_dropdown = $filtered_school_years;
        }
    }
} catch (Exception $e) {
    // Fallback to dummy data
    $school_years_for_dropdown = [
        [
            'id' => 1,
            'school_year_label' => '2023-2024',
            'status' => 'Active',
            'start_date' => '2023-08-01',
            'end_date' => '2024-05-31',
        ]
    ];
}

// Fetch the Currently Active Term from the Database - only if tables exist
$currentTerm = [
    'academic_year' => 'No Active Term',
    'term_title' => 'No Active Term',
    'term_start' => 'N/A',
    'term_end' => 'N/A',
    'school_year_status' => 'Inactive',
];

// Only try to fetch active term if school_terms table exists
try {
    $terms_check = $conn->query("SHOW TABLES LIKE 'school_terms'");
    if ($terms_check->num_rows > 0) {
        // Check if school_years has the required columns
        $sy_columns_check = $conn->query("DESCRIBE school_years");
        $sy_columns = [];
        while ($row = $sy_columns_check->fetch_assoc()) {
            $sy_columns[] = $row['Field'];
        }
        
        if (in_array('school_year_label', $sy_columns)) {
            $sql_term = "SELECT
                        st.title AS term_title,
                        st.start_date AS term_start,
                        st.end_date AS term_end,
                        sy.school_year_label AS academic_year,
                        sy.status AS school_year_status
                    FROM
                        school_terms AS st
                    JOIN
                        school_years AS sy ON st.school_year_id = sy.id
                    WHERE
                        CURDATE() BETWEEN st.start_date AND st.end_date
                        AND sy.status = 'Active'
                    LIMIT 1";
        } else {
            // Old structure
            $sql_term = "SELECT
                        st.title AS term_title,
                        st.start_date AS term_start,
                        st.end_date AS term_end,
                        CONCAT(sy.year_start, '-', sy.year_end) AS academic_year,
                        CASE WHEN sy.is_active = 1 THEN 'Active' ELSE 'Inactive' END AS school_year_status
                    FROM
                        school_terms AS st
                    JOIN
                        school_years AS sy ON st.school_year_id = sy.id
                    WHERE
                        CURDATE() BETWEEN st.start_date AND st.end_date
                        AND sy.is_active = 1
                    LIMIT 1";
        }

        $stmt = $conn->prepare($sql_term);
        if ($stmt) {
            $stmt->execute();
            $result_term = $stmt->get_result();
            
            if ($result_term && $result_term->num_rows > 0) {
                $currentTerm = $result_term->fetch_assoc();
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    // Keep default currentTerm values
}
?>

<div class="school-calendar-page-container">
    <div class="header-row">
        <h2 class="main-page-title" style="padding-left: 0px;">School Calendar Management</h2> 
    </div>

    <div class="calendar-content-wrapper">
        <div class="calendar-section box-panel"> 
            <div class="calendar-header">
                <div class="calendar-nav-group"><button id="prevYearBtn" class="calendar-nav-btn calendar-nav-year-btn"><img src="../src/assets/icons/previous-II-icon.png" alt="Previous Year" class="calendar-nav-icon" data-default-src="../src/assets/icons/previous-II-icon.png" data-hover-src="../src/assets/icons/previous-II-hover-icon.png"></button><button id="prevMonthBtn" class="calendar-nav-btn"><img src="../src/assets/icons/previous-I-icon.png" alt="Previous Month" class="calendar-nav-icon" data-default-src="../src/assets/icons/previous-I-icon.png" data-hover-src="../src/assets/icons/previous-I-hover-icon.png"></button></div>
                <h3 id="currentMonthYear"></h3> 
                <button id="todayBtn" class="calendar-nav-btn calendar-today-btn">Today</button> 
                <div class="calendar-nav-group"><button id="nextMonthBtn" class="calendar-nav-btn"><img src="../src/assets/icons/next-I-icon.png" alt="Next Month" class="calendar-nav-icon" data-default-src="../src/assets/icons/next-I-icon.png" data-hover-src="../src/assets/icons/next-I-hover-icon.png"></button><button id="nextYearBtn" class="calendar-nav-btn calendar-nav-year-btn"><img src="../src/assets/icons/next-II-icon.png" alt="Next Year" class="calendar-nav-icon" data-default-src="../src/assets/icons/next-II-icon.png" data-hover-src="../src/assets/icons/next-II-hover-icon.png"></button></div>
            </div>
            <div class="calendar-days-header"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
            <div id="calendarGrid" class="calendar-grid"></div>
        </div>

        <div class="calendar-options-section box-panel"> 
            <h3>Management Options</h3>
            <div class="options-list">
                <button class="option-button" id="addTermBtn"><img src="../src/assets/icons/add-term-icon.png" alt="Add Term Icon"><span>Add Term</span></button>
                
                <button class="option-button" id="addSchoolYearOptionBtn">
                    <img src="../src/assets/icons/add-term-icon.png" alt="Add School Year Icon">
                    <span>Add School Year</span>
                </button>

                <button class="option-button" id="addHolidayBtn"><img src="../src/assets/icons/add-holiday-icon.png" alt="Add Holiday Icon"><span>Add Holiday</span></button>
                <button class="option-button" id="scheduleMaintenanceBtn"><img src="../src/assets/icons/maintenance-icon.png" alt="Maintenance Icon"><span>Schedule Maintenance</span></button>
                <button class="option-button" id="addCustomEventBtn"><img src="../src/assets/icons/add-event-icon.png" alt="Add Custom Event Icon"><span>Add Custom Event</span></button>
            </div>
        </div>
    </div>

    <div class="management-row-wrapper">

        <div class="school-years-management-section box-panel"> 
            <div class="school-terms-header">
                <div>
                    <h3>School Years Management</h3>
                    <p>View and manage academic years.</p>
                </div>
                </div>
            <div class="school-years-list">
                <?php if (!empty($school_years_for_dropdown)): ?>
                    <ul>
                        <?php foreach ($school_years_for_dropdown as $sy): ?>
                            <li>
                                <div class="sy-main-info">
                                    <span><?php echo htmlspecialchars($sy['school_year_label']); ?></span>
                                    <span class="status-badge status-<?php echo strtolower(htmlspecialchars($sy['status'])); ?>">
                                        <?php echo htmlspecialchars($sy['status']); ?>
                                    </span>
                                </div>
                                <div class="sy-date-info">
                                    <span>
                                        Starts: <?php echo date_format(date_create($sy['start_date']), 'F j, Y'); ?>
                                    </span>
                                    <span>
                                        Ends: <?php echo date_format(date_create($sy['end_date']), 'F j, Y'); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="placeholder-message">No school years have been created yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="school-terms-section box-panel"> 
            <div class="school-terms-header">
                <div>
                    <h3>School Terms Management</h3>
                    <p>View and manage academic terms.</p>
                </div>
                <button class="add-dept-btn" id="manageTermsButton">Manage All Terms</button>
            </div>
            <div class="current-term-details box-panel">
                <h4>Current Term: <span id="currentTermName"><?php echo htmlspecialchars($currentTerm['term_title']); ?></span></h4>
                <p><strong>School Year:</strong> <span id="currentTermYear"><?php echo htmlspecialchars($currentTerm['academic_year']); ?></span></p>
                <p><strong>Dates:</strong> <span id="currentTermDates"><?php echo htmlspecialchars($currentTerm['term_start'] . ' - ' . $currentTerm['term_end']); ?></span></p>
                <p><strong>Status:</strong> <span id="currentTermStatus"><?php echo htmlspecialchars($currentTerm['school_year_status']); ?></span></p>
            </div>
        </div>

    </div>

    <div class="calendar-events-display-row">
        <div class="events-selected-day-list box-panel"><h4>Events on <span id="selectedDateDisplay"></span></h4><ul id="selectedDayEventsList"><li class="placeholder-message">Select a date on the calendar to view its events.</li></ul></div>
        <div class="upcoming-events-section box-panel"> <h4>Upcoming Events <span id="upcomingEventsCurrentDateLabel"></span></h4><ul id="upcomingEventsList"><li class="placeholder-message">No upcoming events found for today or future.</li></ul></div>
    </div>

    <script>
        const schoolEventsData = {}; 
    </script>

    <?php 
    // Debug: Check if school_years_for_dropdown is set
    if (isset($school_years_for_dropdown)) {
    } else {
    }
    
    include 'school-calendar-modals.php'; 
    include __DIR__ . '/../modal_add_custom_event.php'; 
    ?>

</div>