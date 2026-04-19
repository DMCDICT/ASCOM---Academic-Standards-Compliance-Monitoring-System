<?php
// super_admin-mis/school_calendar-content/school-calendar.php

global $conn;

// Greeting logic
$hour = (int) date('G');
if ($hour < 12) { $greeting = 'Good Morning'; }
elseif ($hour < 17) { $greeting = 'Good Afternoon'; }
else { $greeting = 'Good Evening'; }

// Initialize arrays for real data
$school_years_for_dropdown = [];
$currentTerm = [
    'academic_year' => 'No Active Term',
    'term_title' => 'No Active Term',
    'term_start' => 'N/A',
    'term_end' => 'N/A',
    'school_year_status' => 'Inactive',
];

// Resilient Data Fetching
try {
    $cols_raw = $conn->query("DESCRIBE school_years");
    if ($cols_raw) {
        $cols = [];
        while ($r = $cols_raw->fetch_assoc()) { $cols[] = $r['Field']; }
        
        $has_label = in_array('school_year_label', $cols);
        $has_status = in_array('status', $cols);
        $has_active = in_array('is_active', $cols);

        // Normalize SY Label
        $label_field = $has_label ? "school_year_label" : "CONCAT(year_start, '-', year_end)";
        // Normalize Status
        $status_field = $has_status ? "status" : ($has_active ? "CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END" : "'Active'");
        
        $sql_sy = "SELECT id, $label_field as school_year_label, $status_field as status, start_date, end_date FROM school_years ORDER BY $label_field DESC";
        $res_sy = $conn->query($sql_sy);
        if ($res_sy) {
            while($row = $res_sy->fetch_assoc()) { $school_years_for_dropdown[] = $row; }
        }

        // Active SY logic
        $active_where = $has_status ? "status = 'Active'" : ($has_active ? "is_active = 1" : "1=1");
        $sql_curr_sy = "SELECT $label_field as school_year_label FROM school_years WHERE $active_where ORDER BY start_date DESC LIMIT 1";
        $res_curr_sy = $conn->query($sql_curr_sy);
        $current_school_year = null;
        if ($res_curr_sy && $res_curr_sy->num_rows > 0) {
            $current_school_year = $res_curr_sy->fetch_assoc()['school_year_label'];
            if (preg_match('/(\d{4})/', $current_school_year, $matches)) {
                $curr_y = intval($matches[1]);
                $min_y = $curr_y - 5;
                $filtered = [];
                foreach ($school_years_for_dropdown as $sy) {
                    if (preg_match('/(\d{4})/', $sy['school_year_label'], $m)) {
                        $ys = intval($m[1]);
                        if ($sy['school_year_label'] === $current_school_year || ($ys >= $min_y && $ys <= $curr_y)) {
                            $filtered[] = $sy;
                        }
                    }
                }
                $school_years_for_dropdown = $filtered;
            }
        }
        
        // If no school years after filtering, show all school years
        if (empty($school_years_for_dropdown)) {
            $sql_sy = "SELECT id, $label_field as school_year_label, $status_field as status, start_date, end_date FROM school_years ORDER BY $label_field DESC";
            $res_sy = $conn->query($sql_sy);
            if ($res_sy) {
                $school_years_for_dropdown = [];
                while($row = $res_sy->fetch_assoc()) { $school_years_for_dropdown[] = $row; }
            }
        }

        // Active Term logic
        $sql_term = "SELECT st.title AS term_title, st.start_date AS term_start, st.end_date AS term_end, 
                            $label_field AS academic_year, $status_field AS school_year_status 
                     FROM school_terms AS st 
                     JOIN school_years AS sy ON st.school_year_id = sy.id 
                     WHERE CURDATE() BETWEEN st.start_date AND st.end_date 
                       AND $active_where LIMIT 1";
        $res_term = $conn->query($sql_term);
        if ($res_term && $res_term->num_rows > 0) { $currentTerm = $res_term->fetch_assoc(); }
    }
} catch (Exception $e) {}
?>

<div class="school-calendar-page-container">
    <!-- Greeting Section -->
    <div class="calendar-greeting">
        <div class="greeting-text">
            <h2><?php echo $greeting; ?>, Admin</h2>
            <p>Institutional calendar and academic term management.</p>
        </div>
    </div>

    <div class="calendar-layout-grid">
        <!-- Main Calendar Card -->
        <div class="premium-card calendar-card">
            <div class="calendar-header">
                <h3 class="month-year-display" id="currentMonthYear">Loading...</h3>
                <div class="calendar-controls">
                    <button id="todayBtn" class="today-btn">Today</button>
                    <button id="prevYearBtn" class="nav-btn" title="Previous Year">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg>
                    </button>
                    <button id="prevMonthBtn" class="nav-btn" title="Previous Month">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <button id="nextMonthBtn" class="nav-btn" title="Next Month">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <button id="nextYearBtn" class="nav-btn" title="Next Year">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline></svg>
                    </button>
                </div>
            </div>

            <div class="calendar-days-strip">
                <span class="day-name">Sun</span><span class="day-name">Mon</span><span class="day-name">Tue</span><span class="day-name">Wed</span><span class="day-name">Thu</span><span class="day-name">Fri</span><span class="day-name">Sat</span>
            </div>
            <div id="calendarGrid" class="calendar-cells"></div>
        </div>

        <!-- Management Options Card -->
        <div class="premium-card options-card">
            <div class="section-header">
                <div class="label-bar"></div>
                <div>
                    <h3>Quick Controls</h3>
                    <p>Add events and academic periods</p>
                </div>
            </div>

            <div class="options-grid">
                <button class="mgmt-btn" id="addTermBtn" onclick="openAddTermModal()">
                    <div class="mgmt-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                    </div>
                    <div class="mgmt-text">
                        <span class="mgmt-label">Add Term</span>
                        <span class="mgmt-desc">Create new academic period</span>
                    </div>
                </button>

                <button class="mgmt-btn" id="addSchoolYearOptionBtn" onclick="openAddSchoolYearModal()">
                    <div class="mgmt-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div class="mgmt-text">
                        <span class="mgmt-label">New School Year</span>
                        <span class="mgmt-desc">Define academic session</span>
                    </div>
                </button>

                <button class="mgmt-btn" id="addHolidayBtn" onclick="openAddHolidayModal()">
                    <div class="mgmt-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    </div>
                    <div class="mgmt-text">
                        <span class="mgmt-label">Add Holiday</span>
                        <span class="mgmt-desc">Mark no-class sessions</span>
                    </div>
                </button>

                <button class="mgmt-btn" id="addCustomEventBtn" onclick="openAddCustomEventModal()">
                    <div class="mgmt-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="mgmt-text">
                        <span class="mgmt-label">Custom Event</span>
                        <span class="mgmt-desc">Generic calendar mark</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="lower-sections-grid">
        <!-- School Years List -->
        <div class="premium-card">
            <div class="section-header">
                <div class="label-bar"></div>
                <div>
                    <h3>School Years</h3>
                    <p>Session history and registration</p>
                </div>
            </div>
            <ul class="data-list">
                <?php if (!empty($school_years_for_dropdown)): ?>
                    <?php foreach ($school_years_for_dropdown as $sy): ?>
                    <li class="data-item">
                        <div class="item-info">
                            <span class="item-title"><?php echo htmlspecialchars($sy['school_year_label']); ?></span>
                            <span class="item-subtitle"><?php echo date('M d, Y', strtotime($sy['start_date'])); ?> — <?php echo date('M d, Y', strtotime($sy['end_date'])); ?></span>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($sy['status']); ?>">
                            <?php echo $sy['status']; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="data-item placeholder-message">No school years defined.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Current Term Details -->
        <div class="premium-card">
            <div class="section-header">
                <div class="label-bar"></div>
                <div>
                    <h3>Active Term Details</h3>
                    <p>Information about the current period</p>
                </div>
            </div>
            <div class="current-term-details" style="background: rgba(12,75,52,0.02); border-radius: 12px; padding: 20px;">
                <h4 style="margin: 0 0 12px 0; color: #0C4B34; font-size: 16px; font-weight: 800;"><?php echo htmlspecialchars($currentTerm['term_title']); ?></h4>
                <div class="data-list">
                    <div class="data-item" style="padding: 10px 0;">
                        <span class="item-subtitle">Academic Year</span>
                        <span class="item-title"><?php echo htmlspecialchars($currentTerm['academic_year']); ?></span>
                    </div>
                    <div class="data-item" style="padding: 10px 0;">
                        <span class="item-subtitle">Timeframe</span>
                        <span class="item-title" style="font-size: 13px;"><?php echo htmlspecialchars($currentTerm['term_start'] . ' to ' . $currentTerm['term_end']); ?></span>
                    </div>
                    <div class="data-item" style="padding: 10px 0;">
                        <span class="item-subtitle">System Status</span>
                        <span class="status-badge status-<?php echo strtolower($currentTerm['school_year_status']); ?>"><?php echo $currentTerm['school_year_status']; ?></span>
                    </div>
                </div>
            </div>
            <button class="mgmt-btn" id="manageTermsButton" style="margin-top: 16px; width: 100%; justify-content: center; background: #0C4B34; color: white;">Manage All Terms</button>
        </div>
    </div>

    <!-- Event Lists Row -->
    <div class="lower-sections-grid" style="margin-top: 28px;">
        <div class="premium-card">
            <h4 style="font-size: 14px; font-weight: 800; color: #0C4B34; margin-bottom: 16px;">Events on <span id="selectedDateDisplay"></span></h4>
            <ul id="selectedDayEventsList" class="data-list">
                <li class="item-subtitle" style="font-style: italic;">Select a date to view details.</li>
            </ul>
        </div>
        <div class="premium-card">
            <h4 style="font-size: 14px; font-weight: 800; color: #0C4B34; margin-bottom: 16px;">Upcoming Agenda</h4>
            <ul id="upcomingEventsList" class="data-list">
                <li class="item-subtitle" style="font-style: italic;">No upcoming events found.</li>
            </ul>
        </div>
    </div>

    <script> const schoolEventsData = {}; </script>
    <?php 
    include 'school-calendar-modals.php'; 
    include __DIR__ . '/../modal_add_custom_event.php'; 
    ?>
</div>