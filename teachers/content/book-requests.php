<?php
// book-requests.php for Teachers
// This file displays all book requests with filtering options

// Get teacher information and department from session
$teacherName = 'Mr. Dummy Teacher';
$teacherTitle = 'Mr.';
$departmentCode = 'CCS';
$departmentColor = '#C41E3A'; // Default red color

try {
    if (isset($_SESSION['user_id'])) {
        // Get teacher's information from session data
        if (isset($_SESSION['user_title']) && isset($_SESSION['user_first_name']) && isset($_SESSION['user_last_name'])) {
            $teacherTitle = $_SESSION['user_title'] ? $_SESSION['user_title'] . ' ' : '';
            $firstName = $_SESSION['user_first_name'] ?? '';
            $lastName = $_SESSION['user_last_name'] ?? '';
            $teacherName = $teacherTitle . $firstName . ' ' . $lastName;
        }

        // Get department information from selected_role
        if (isset($_SESSION['selected_role']['department_code'])) {
            $departmentCode = $_SESSION['selected_role']['department_code'];
        }
        if (isset($_SESSION['selected_role']['department_color'])) {
            $departmentColor = $_SESSION['selected_role']['department_color'];
        }
    }
} catch (Exception $e) {
    // Keep default values if there's an error
}
?>

<!-- Book Requests - View All Page -->
<div class="back-navigation">
    <button class="back-button" onclick="window.location.href='content.php?page=dashboard'">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back">
        Back to Dashboard
    </button>
</div>

<div class="header-section">
    <div class="header-content">
        <h1 class="main-page-title">Book Requests</h1>
        <p class="page-description">View and manage all your book and reference material requests</p>
    </div>
    <div class="filter-buttons">
        <button class="filter-btn active" onclick="filterRequests('PENDING')">Pending</button>
        <button class="filter-btn" onclick="filterRequests('APPROVED')">Approved</button>
        <button class="filter-btn" onclick="filterRequests('REJECTED')">Rejected</button>
    </div>
</div>

<div class="reference-requests-container">
    <div class="reference-requests-grid" id="allRequestsGrid">
        <!-- All requests for the selected status will be displayed here -->
    </div>
</div>

<!-- Floating Navigation Buttons -->
<div class="floating-nav">
    <button class="nav-btn high-btn" onclick="scrollToSection('high')">
        <span class="btn-text">High</span>
        <span class="btn-count" id="floatingHighCount">0</span>
    </button>
    <button class="nav-btn medium-btn" onclick="scrollToSection('medium')">
        <span class="btn-text">Medium</span>
        <span class="btn-count" id="floatingMediumCount">0</span>
    </button>
    <button class="nav-btn low-btn" onclick="scrollToSection('low')">
        <span class="btn-text">Low</span>
        <span class="btn-count" id="floatingLowCount">0</span>
    </button>
</div>

<style>
/* Back navigation styling to match All Courses page */
.back-navigation {
    margin-bottom: 20px;
}

.back-button {
    background: #1976d2;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-button:hover {
    background: #1565c0;
}

.back-button img {
    width: 16px;
    height: 16px;
}

/* Header section with filter buttons */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0px;
}

.header-content {
    flex: 1;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #666;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
}

.filter-btn:hover {
    background: #f5f5f5;
    border-color: #1976d2;
    color: #1976d2;
}

.filter-btn.active {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

/* Main page title styling to match All Courses page */
.main-page-title {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin: 0 !important;
    padding: 0 !important;
    font-family: 'TT Interphases', sans-serif;
    line-height: 1.2;
}

.page-description {
    font-size: 14px;
    color: #666;
    margin: 5px 0 0px 0;
    font-family: 'TT Interphases', sans-serif;
    line-height: 1.4;
}

/* Complete card styling for View All page */
.reference-requests-container {
    margin-top: 20px;
    width: 100%;
    max-width: none;
}

.priority-section {
    display: none;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    scroll-margin-top: 20px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 15px;
}

.priority-indicator {
    display: none;
}

.priority-indicator.high {
    color: #FF4C4C;
}

.priority-indicator.medium {
    color: #FFA500;
}

.priority-indicator.low {
    color: #4CAF50;
}

.request-count {
    background: #e9ecef;
    color: #495057;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    min-width: 30px;
    text-align: center;
}

.section-content {
    padding: 20px;
    transition: all 0.3s ease;
    min-height: 120px;
}

.reference-requests-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-top: 20px;
    width: 100%;
}

.reference-request-card {
    width: 100%;
    min-width: 250px;
    padding: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
}

.reference-request-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1976d2, #42a5f5, #90caf9);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.reference-request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    border-color: #1976d2;
}

.request-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 5px;
    padding-bottom: 5px;
}

.requester-info {
    flex: 1;
}

.requester-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 4px;
    font-family: 'TT Interphases', sans-serif;
}

.faculty-department {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0px;
    font-family: 'TT Interphases', sans-serif;
    padding: 0;
    display: inline-block;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    font-family: 'TT Interphases', sans-serif;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.priority-high {
    background: #FF4C4C;
    color: white;
}

.priority-medium {
    background: #FFA500;
    color: white;
}

.priority-low {
    background: #4CAF50;
    color: white;
}

.course-info {
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f5f5f5;
    padding: 8px 12px;
    border-radius: 6px;
}

.course-code {
    background: #1976d2;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.course-name {
    font-size: 13px;
    color: #666;
    font-family: 'TT Interphases', sans-serif;
    flex: 1;
}

.request-summary {
    margin-bottom: 16px;
    flex: 1;
}

.request-type {
    font-size: 11px;
    color: #666;
    margin-bottom: 8px;
    font-family: 'TT Interphases', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
}

.material-title {
    font-size: 13px;
    color: #333;
    font-family: 'Georgia', serif;
    line-height: 1.4;
    font-style: italic;
}

.request-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.status-pending-btn {
    background: #fff3e0;
    color: #ef6c00;
    cursor: default;
    opacity: 0.8;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
}

.status-approved-btn {
    background: #e8f5e8;
    color: #2e7d32;
    cursor: default;
    opacity: 0.8;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
}

.status-rejected-btn {
    background: #ffebee;
    color: #c62828;
    cursor: default;
    opacity: 0.8;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
}

.request-date {
    font-size: 11px;
    color: #999;
    font-family: 'TT Interphases', sans-serif;
    text-align: center;
    margin-top: 8px;
    font-style: italic;
}

.no-requests-message {
    text-align: center;
    color: #666;
    font-size: 16px;
    padding: 40px 20px;
    font-family: 'TT Interphases', sans-serif;
    font-style: italic;
}

/* Floating navigation buttons styling */
.floating-nav {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: none;
}

#mediumSection,
#lowSection {
    display: none;
    gap: 10px;
    z-index: 1000;
}

.nav-btn {
    background: white;
    color: #666;
    border: 2px solid #e0e0e0;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    min-width: 80px;
    justify-content: center;
}

.nav-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.nav-btn.high-btn:hover {
    background: #FF4C4C;
    color: white;
    border-color: #FF4C4C;
}

.nav-btn.medium-btn:hover {
    background: #FFA500;
    color: white;
    border-color: #FFA500;
}

.nav-btn.low-btn:hover {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

.nav-btn .btn-text {
    font-family: 'TT Interphases', sans-serif;
}

.nav-btn .btn-count {
    background: #f0f0f0;
    color: #666;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.nav-btn.high-btn:hover .btn-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.nav-btn.medium-btn:hover .btn-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.nav-btn.low-btn:hover .btn-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Responsive breakpoints */
@media screen and (max-width: 1400px) {
    .reference-requests-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media screen and (max-width: 1200px) {
    .reference-requests-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media screen and (max-width: 768px) {
    .reference-requests-grid {
        grid-template-columns: 1fr;
    }
    
    .floating-nav {
        right: 15px;
        gap: 8px;
    }
    
    .nav-btn {
        padding: 8px 12px;
        min-width: 60px;
    }
    
    .btn-text {
        font-size: 12px;
    }
}
</style>

<!-- Hidden data for JavaScript -->
<div id="allBookRequestsData" style="display: none;">
    <?php
    // Sample book requests data for teachers (including all statuses)
    $bookRequests = [
        [
            'id' => 1,
            'book_title' => 'Advanced Database Systems',
            'author_first' => 'John',
            'author_last' => 'Smith',
            'publication_year' => '2023',
            'edition' => '3rd',
            'publisher' => 'McGraw-Hill',
            'isbn' => '978-0071234567',
            'course_code' => 'CS 301',
            'course_name' => 'Database Management',
            'status' => 'PENDING',
            'priority' => 'HIGH',
            'justification' => 'Required for advanced database concepts',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 2,
            'book_title' => 'Software Engineering Principles',
            'author_first' => 'Sarah',
            'author_last' => 'Johnson',
            'publication_year' => '2024',
            'edition' => '2nd',
            'publisher' => 'Pearson',
            'isbn' => '978-0137890123',
            'course_code' => 'CS 401',
            'course_name' => 'Software Engineering',
            'status' => 'PENDING',
            'priority' => 'MEDIUM',
            'justification' => 'Core textbook for software development',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 3,
            'book_title' => 'Machine Learning Fundamentals',
            'author_first' => 'Michael',
            'author_last' => 'Chen',
            'publication_year' => '2023',
            'edition' => '1st',
            'publisher' => 'MIT Press',
            'isbn' => '978-0262345678',
            'course_code' => 'CS 501',
            'course_name' => 'Machine Learning',
            'status' => 'PENDING',
            'priority' => 'HIGH',
            'justification' => 'Essential for AI course curriculum',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 4,
            'book_title' => 'Computer Networks and Security',
            'author_first' => 'Emily',
            'author_last' => 'Davis',
            'publication_year' => '2024',
            'edition' => '4th',
            'publisher' => 'Wiley',
            'isbn' => '978-1119876543',
            'course_code' => 'CS 302',
            'course_name' => 'Network Security',
            'status' => 'PENDING',
            'priority' => 'LOW',
            'justification' => 'Updated material for cybersecurity course',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 5,
            'book_title' => 'Data Structures and Algorithms in Java',
            'author_first' => 'Robert',
            'author_last' => 'Lafore',
            'publication_year' => '2023',
            'edition' => '5th',
            'publisher' => 'Sams Publishing',
            'isbn' => '978-0134855684',
            'course_code' => 'CS 202',
            'course_name' => 'Data Structures',
            'status' => 'PENDING',
            'priority' => 'HIGH',
            'justification' => 'Essential textbook for data structures course',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 6,
            'book_title' => 'Operating System Concepts',
            'author_first' => 'Abraham',
            'author_last' => 'Silberschatz',
            'publication_year' => '2023',
            'edition' => '10th',
            'publisher' => 'Wiley',
            'isbn' => '978-1118063330',
            'course_code' => 'CS 303',
            'course_name' => 'Operating Systems',
            'status' => 'PENDING',
            'priority' => 'MEDIUM',
            'justification' => 'Core textbook for operating systems course',
            'request_date' => date('Y-m-d')
        ],
        [
            'id' => 7,
            'book_title' => 'Computer Organization and Design',
            'author_first' => 'David',
            'author_last' => 'Patterson',
            'publication_year' => '2022',
            'edition' => '6th',
            'publisher' => 'Morgan Kaufmann',
            'isbn' => '978-0128201091',
            'course_code' => 'CS 303',
            'course_name' => 'Computer Architecture',
            'status' => 'APPROVED',
            'priority' => 'MEDIUM',
            'justification' => 'Hardware architecture reference',
            'request_date' => date('Y-m-d', strtotime('-5 days'))
        ],
        [
            'id' => 8,
            'book_title' => 'Software Engineering: Principles and Practice',
            'author_first' => 'Hans',
            'author_last' => 'van Vliet',
            'publication_year' => '2024',
            'edition' => '4th',
            'publisher' => 'Wiley',
            'isbn' => '978-1118967624',
            'course_code' => 'CS 402',
            'course_name' => 'Advanced Software Engineering',
            'status' => 'APPROVED',
            'priority' => 'LOW',
            'justification' => 'Advanced software development practices',
            'request_date' => date('Y-m-d', strtotime('-3 days'))
        ],
        [
            'id' => 9,
            'book_title' => 'Machine Learning: A Probabilistic Perspective',
            'author_first' => 'Kevin',
            'author_last' => 'Murphy',
            'publication_year' => '2023',
            'edition' => '2nd',
            'publisher' => 'MIT Press',
            'isbn' => '978-0262018029',
            'course_code' => 'CS 502',
            'course_name' => 'Machine Learning',
            'status' => 'REJECTED',
            'priority' => 'HIGH',
            'justification' => 'Core ML textbook for graduate course',
            'request_date' => date('Y-m-d', strtotime('-7 days'))
        ],
        [
            'id' => 10,
            'book_title' => 'Computer Graphics: Principles and Practice',
            'author_first' => 'John',
            'author_last' => 'Hughes',
            'publication_year' => '2022',
            'edition' => '4th',
            'publisher' => 'Addison-Wesley',
            'isbn' => '978-0321399526',
            'course_code' => 'CS 304',
            'course_name' => 'Computer Graphics',
            'status' => 'REJECTED',
            'priority' => 'MEDIUM',
            'justification' => 'Graphics programming reference',
            'request_date' => date('Y-m-d', strtotime('-10 days'))
        ]
    ];
    
    echo json_encode($bookRequests);
    ?>
</div>

<script>
// Book Requests Variables
let allRequests = [];
let currentStatusFilter = 'PENDING';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing book requests page...');
    
    // Load data from PHP
    const requestsData = document.getElementById('allBookRequestsData');
    
    if (requestsData) {
        allRequests = JSON.parse(requestsData.textContent);
        console.log('Loaded all requests:', allRequests);
        
        // Display pending requests by default
        displayAllRequests();
        
        console.log('Book requests page initialization complete');
    } else {
        console.error('Failed to find required data elements');
    }
});

function displayAllRequests() {
    // Initialize with sample data if not already set
    if (!window.allRequests) {
        window.allRequests = allRequests;
    }
    
    // Set the Pending button as active and filter to show only pending requests
    const pendingButton = document.querySelector('.filter-btn[onclick*="PENDING"]');
    if (pendingButton) {
        pendingButton.classList.add('active');
    }
    filterRequests('PENDING');
}

function filterRequests(status) {
    // Update active button state for status filters
    document.querySelectorAll('.filter-btn[onclick*="PENDING"], .filter-btn[onclick*="APPROVED"], .filter-btn[onclick*="REJECTED"]').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Find and activate the button for the given status
    const targetButton = document.querySelector(`.filter-btn[onclick*="${status}"]`);
    if (targetButton) {
        targetButton.classList.add('active');
    }
    
    currentStatusFilter = status;
    displayRequestsByPriority();
}

function displayRequestsByPriority() {
    // Filter requests based on current status (single free-form grid)
    const filteredRequests = allRequests.filter(request => request.status === currentStatusFilter);
    
    const grid = document.getElementById('allRequestsGrid');
    if (!grid) return;

    // Clear existing cards
    grid.innerHTML = '';

    if (filteredRequests.length === 0) {
        grid.innerHTML = '<div class="no-requests-message">No requests found for this status.</div>';
        return;
    }

    // Create cards for each request
    filteredRequests.forEach(request => {
        const card = createRequestCard(request);
        grid.appendChild(card);
    });
}

function displayPrioritySection(priority, requests) {
    const grid = document.getElementById(`${priority}PriorityGrid`);
    const count = document.getElementById(`${priority}Count`);
    const section = document.getElementById(`${priority}Section`);
    
    if (!grid || !count || !section) return;
    
    // Update count
    count.textContent = requests.length;
    
    // Clear existing cards
    grid.innerHTML = '';
    
    if (requests.length === 0) {
        // Show no requests message
        grid.innerHTML = '<div class="no-requests-message">No ' + priority + ' priority requests found.</div>';
        section.style.display = 'block';
    } else {
        // Create cards for each request
        requests.forEach(request => {
            const card = createRequestCard(request);
            grid.appendChild(card);
        });
        section.style.display = 'block';
    }
}

function updateFloatingNavCounts(highCount, mediumCount, lowCount) {
    document.getElementById('floatingHighCount').textContent = highCount;
    document.getElementById('floatingMediumCount').textContent = mediumCount;
    document.getElementById('floatingLowCount').textContent = lowCount;
}

function scrollToSection(priority) {
    const header = document.getElementById(`${priority}Header`);
    if (header) {
        // Calculate the exact position to scroll to
        const headerPosition = header.offsetTop - 20; // 20px offset from top
        
        // Smooth scroll to the header position
        window.scrollTo({
            top: headerPosition,
            behavior: 'smooth'
        });
    }
}

function createRequestCard(request) {
    // Generate simplified APA citation for display
    let apaCitation = '';
    if (request.author_first && request.author_last && request.publication_year) {
        const editionText = request.edition && request.edition !== '1st' ? ` (${request.edition} ed.)` : '';
        apaCitation = `${request.author_last}, ${request.author_first.charAt(0)}. (${request.publication_year}). ${request.book_title}${editionText}.`;
    } else {
        apaCitation = request.book_title;
    }
    
    // Get department code and color from session or use default
    const departmentCode = '<?php echo $_SESSION["selected_role"]["department_code"] ?? "CCS"; ?>';
    const departmentColor = '<?php echo $_SESSION["selected_role"]["department_color"] ?? "#1976d2"; ?>';
    
    // Determine status button class and text
    let statusClass = '';
    let statusText = '';
    switch(request.status) {
        case 'PENDING':
            statusClass = 'status-pending-btn';
            statusText = 'Pending';
            break;
        case 'APPROVED':
            statusClass = 'status-approved-btn';
            statusText = 'Approved';
            break;
        case 'REJECTED':
            statusClass = 'status-rejected-btn';
            statusText = 'Rejected';
            break;
    }
    
    const card = document.createElement('div');
    card.className = 'reference-request-card';
    card.setAttribute('data-request-id', request.id);
    
    card.innerHTML = `
        <div class="request-header">
            <div class="requester-info">
                <div class="requester-name"><?php echo htmlspecialchars($teacherName); ?></div>
                <div class="faculty-department" style="color: <?php echo $departmentColor; ?>;"><?php echo $departmentCode; ?> FACULTY</div>
            </div>
        </div>
        
        <div class="course-info">
            <div class="course-code">${request.course_code}</div>
            <div class="course-name">${request.course_name}</div>
        </div>
        
        <div class="request-summary">
            <div class="material-title">${apaCitation}</div>
        </div>
        
        <div class="request-footer">
            <div class="${statusClass}">${statusText}</div>
        </div>
        <div class="request-date">Requested on: ${new Date(request.request_date).toLocaleDateString()}</div>
    `;
    
    return card;
}
</script>