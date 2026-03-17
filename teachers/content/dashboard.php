<?php
// Get teacher information and department from session
$teacherName = 'Mr. Dummy Teacher';
$teacherTitle = 'Mr.';
$departmentCode = 'CCS'; // Default fallback
$departmentColor = '#C41E3A'; // Default red color

try {
    if (isset($_SESSION['user_id'])) {
        // Get teacher's information from session data
        if (isset($_SESSION['user_title']) && isset($_SESSION['user_first_name']) && isset($_SESSION['user_last_name'])) {
            $teacherTitle = $_SESSION['user_title'] ? $_SESSION['user_title'] . ' ' : '';
            $firstName = $_SESSION['user_first_name'] ?? '';
            $lastName = $_SESSION['user_last_name'] ?? '';
            $teacherName = $teacherTitle . $firstName . ' ' . $lastName;
        } else {
            // If session data is not available, load from database
            if (isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT title, first_name, last_name 
                        FROM users 
                        WHERE id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userInfo) {
                        $teacherTitle = $userInfo['title'] ? $userInfo['title'] . ' ' : '';
                        $firstName = $userInfo['first_name'] ?? '';
                        $lastName = $userInfo['last_name'] ?? '';
                        $teacherName = $teacherTitle . $firstName . ' ' . $lastName;
                        
                        // Set in session for future use
                        $_SESSION['user_title'] = $userInfo['title'];
                        $_SESSION['user_first_name'] = $userInfo['first_name'];
                        $_SESSION['user_last_name'] = $userInfo['last_name'];
                    }
                } catch (Exception $dbError) {
                    echo "<!-- DEBUG: User info database error: " . $dbError->getMessage() . " -->";
                }
            }
        }

        // Get department information from selected_role or load from database
        if (isset($_SESSION['selected_role']['department_code'])) {
            $departmentCode = $_SESSION['selected_role']['department_code'];
        }
        if (isset($_SESSION['selected_role']['department_color'])) {
            $departmentColor = $_SESSION['selected_role']['department_color'];
        } else {
            // If selected_role is not set, load from database
            // The database connection should already be available from content.php
            if (isset($pdo)) {
                try {
                    // Get user's department information from database
                    $stmt = $pdo->prepare("
                        SELECT d.department_code, d.color_code 
                        FROM users u 
                        JOIN departments d ON u.department_id = d.id 
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $deptInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($deptInfo) {
                        $departmentCode = $deptInfo['department_code'];
                        $departmentColor = $deptInfo['color_code'];
                        
                        // Set in session for future use
                        if (!isset($_SESSION['selected_role'])) {
                            $_SESSION['selected_role'] = [];
                        }
                        $_SESSION['selected_role']['department_code'] = $departmentCode;
                        $_SESSION['selected_role']['department_color'] = $departmentColor;
                    }
                } catch (Exception $dbError) {
                    echo "<!-- DEBUG: Database error: " . $dbError->getMessage() . " -->";
                }
            } else {
                echo "<!-- DEBUG: PDO connection not available -->";
                // Try to create a new connection
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=ascom_db", "root", "");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    echo "<!-- DEBUG: Created new PDO connection -->";
                    
                    // Now try the department query again
                    $stmt = $pdo->prepare("
                        SELECT d.department_code, d.color_code 
                        FROM users u 
                        JOIN departments d ON u.department_id = d.id 
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $deptInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($deptInfo) {
                        $departmentCode = $deptInfo['department_code'];
                        $departmentColor = $deptInfo['color_code'];
                        echo "<!-- DEBUG: Loaded from new connection - Code: " . $departmentCode . ", Color: " . $departmentColor . " -->";
                    }
                } catch (Exception $newDbError) {
                    echo "<!-- DEBUG: New connection failed: " . $newDbError->getMessage() . " -->";
                }
            }
        }
        
        // Debug: Check what's in the session
        echo "<!-- DEBUG: User ID: " . ($_SESSION['user_id'] ?? 'Not set') . " -->";
        echo "<!-- DEBUG: Department Code: " . $departmentCode . " -->";
        echo "<!-- DEBUG: Department Color: " . $departmentColor . " -->";
        echo "<!-- DEBUG: Teacher Name: " . $teacherName . " -->";
        echo "<!-- DEBUG: PDO available: " . (isset($pdo) ? 'Yes' : 'No') . " -->";
        echo "<!-- DEBUG: Session user_title: " . ($_SESSION['user_title'] ?? 'Not set') . " -->";
        echo "<!-- DEBUG: Session user_first_name: " . ($_SESSION['user_first_name'] ?? 'Not set') . " -->";
        echo "<!-- DEBUG: Session user_last_name: " . ($_SESSION['user_last_name'] ?? 'Not set') . " -->";
        echo "<!-- DEBUG: Session selected_role: " . print_r($_SESSION['selected_role'] ?? 'Not set', true) . " -->";
        echo "<!-- DEBUG: All session data: " . print_r($_SESSION, true) . " -->";
        
        // Force department from database if session is wrong
        if (isset($_SESSION['user_id']) && isset($pdo)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT d.department_code, d.color_code 
                    FROM users u 
                    JOIN departments d ON u.department_id = d.id 
                    WHERE u.id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $deptInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($deptInfo) {
                    $departmentCode = $deptInfo['department_code'];
                    $departmentColor = $deptInfo['color_code'];
                    echo "<!-- DEBUG: FORCED department from DB - Code: " . $departmentCode . ", Color: " . $departmentColor . " -->";
                }
            } catch (Exception $e) {
                echo "<!-- DEBUG: Force DB error: " . $e->getMessage() . " -->";
            }
        }
    }
} catch (Exception $e) {
    // Keep default values if there's an error
}
?>

<style>
/* Book requests styling to match department dean dashboard exactly */
.dashboard-section {
    margin-top: 30px;
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    width: 100%;
    box-sizing: border-box;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    align-self: flex-end;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0;
    padding: 0;
    transition: all 0.3s ease;
    align-items: center;
}

.header-left {
    flex: 1;
}

.section-description {
    color: #666;
    font-size: 14px;
    margin-top: 4px;
    font-family: 'TT Interphases', sans-serif;
}

.header-left h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.view-all-btn {
    display: inline-block;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0;
}

.view-all-btn:hover {
    background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
}

.nav-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-family: 'TT Interphases', sans-serif;
}

.nav-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

.nav-btn:disabled {
    background: #f0f0f0;
    color: #ccc;
    cursor: not-allowed;
    transform: none;
}

.section-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 20px;
    gap: 12px;
}

.collapse-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.collapse-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

.collapse-icon {
    font-size: 12px;
    font-weight: bold;
}

.request-count-badge {
    background: #ff4c4c;
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.collapsed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    margin: 10px 0;
}

.collapsed-header-left {
    flex: 1;
}

.collapsed-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.book-requests-container {
    background: transparent;
    border-radius: 12px;
    padding: 10px;
    margin: 10px 0;
    border: none;
    overflow: visible;
    position: relative;
    width: 100%;
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
}

.book-requests-grid {
    display: flex;
    gap: 15px;
    padding: 0;
    overflow: visible;
    flex-wrap: nowrap;
    align-items: stretch;
    min-height: 200px;
    justify-content: center;
    width: 100%;
    max-width: none;
    margin: 0 auto;
    margin-top: 20px !important;
}

.book-request-card {
    min-width: 250px;
    max-width: none;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    flex-shrink: 0;
    flex-basis: 250px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    margin: 0;
}

.book-request-card::before {
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

.book-request-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    border-color: #1976d2;
}

.book-request-card:hover::before {
    opacity: 1;
}

.request-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
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
    margin-bottom: 4px;
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
    white-space: nowrap;
    letter-spacing: 0.5px;
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

.status-badge {
    padding: 6px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    font-family: 'TT Interphases', sans-serif;
    white-space: nowrap;
}

.status-pending {
    background: #fff3e0;
    color: #ef6c00;
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

.request-date {
    font-size: 11px;
    color: #999;
    font-family: 'TT Interphases', sans-serif;
    text-align: center;
    margin-top: 8px;
    font-style: italic;
}

.collapsed-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
}

.expand-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.expand-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

.nav-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
}

.collapse-icon {
    width: 14px;
    height: 14px;
    object-fit: contain;
}

/* Color effects ONLY for the specific Book Requests icons */
.book-nav-btn .nav-icon,
.book-nav-btn .collapse-icon {
    filter: brightness(0) saturate(100%);
    transition: filter 0.2s ease;
}

/* Hover effects ONLY for Book Requests navigation and collapse/expand icons */
.book-nav-btn:hover .nav-icon,
.book-nav-btn:hover .collapse-icon {
    filter: brightness(0) saturate(100%) invert(1);
}

/* Book Requests Section - Full Width Layout */
.book-requests-container {
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    margin-top: 20px !important;
    box-sizing: border-box !important;
}

.book-requests-container .book-requests-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 20px !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.book-requests-container .book-request-card {
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    padding: 20px !important;
    box-sizing: border-box !important;
    transition: all 0.3s ease !important;
}

/* Courses Section Styling - Department Dean Style */
.courses-section {
    margin-top: 30px;
    width: 100%;
}

.courses-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding: 0;
}

.courses-header h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.courses-header p {
    margin: 0;
    color: #666;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    line-height: 1.4;
}

.courses-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    width: 100%;
    margin-top: 20px;
    margin-bottom: 20px;
    box-sizing: border-box;
}

.course-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 16px 16px 20px 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 190px;
    height: auto;
    box-sizing: border-box;
}

.course-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.course-header {
    flex: 1;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.course-code {
    background: #1976d2;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    margin-bottom: 4px;
}

.course-name {
    font-size: 13px;
    color: #666;
    font-family: 'TT Interphases', sans-serif;
    flex: 1;
    white-space: normal;
}

.book-compliance {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.book-compliance-left {
    display: flex;
    align-items: center;
    gap: 6px;
}

.book-info-icon {
    width: 16px;
    height: 16px;
    cursor: pointer;
    color: #666;
    transition: color 0.3s ease;
    position: relative;
}

.book-info-icon:hover {
    color: #1976d2;
}

.book-info-icon:hover .book-info-tooltip {
    opacity: 1;
    visibility: visible;
}

.book-info-tooltip {
    position: absolute;
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-family: 'TT Interphases', sans-serif;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    pointer-events: none;
    right: calc(100% + 15px);
    top: 50%;
    transform: translateY(-50%);
    width: 180px;
    word-wrap: break-word;
    white-space: normal;
    line-height: 1.3;
    text-align: left;
}

.book-info-tooltip::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -5px;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-left-color: #333;
}

.book-info-icon:hover .book-info-tooltip {
    opacity: 1;
    visibility: visible;
}

.book-indicator {
    font-size: 16px;
    font-weight: 700;
    color: #1976d2;
    font-family: 'TT Interphases', sans-serif;
}

.book-indicator.below-minimum {
    color: #d32f2f;
}

.book-label {
    font-size: 12px;
    color: #666;
    font-family: 'TT Interphases', sans-serif;
    font-weight: 500;
}

.status-compliant-btn {
    background: #e8f5e8;
    color: #2e7d32;
    cursor: default;
    opacity: 0.8;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
    box-sizing: border-box;
}

.status-non-compliant-btn {
    background: #ffebee;
    color: #c62828;
    cursor: default;
    opacity: 0.8;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
    box-sizing: border-box;
}

.course-actions {
    margin-top: auto;
    width: 100%;
    margin-left: 0;
    margin-right: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.course-view-details-btn {
    background: #1976d2;
    color: #ffffff;
    cursor: pointer;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: none;
    font-family: 'TT Interphases', sans-serif;
    border: none;
    width: 100%;
    text-align: center;
    display: block;
    transition: all 0.2s ease;
}

.course-view-details-btn:hover {
    background: #1565c0;
    box-shadow: 0 4px 10px rgba(21, 101, 192, 0.3);
    transform: translateY(-1px);
}

/* Course Details Modal (Teacher) */
.teacher-course-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.teacher-course-modal-box {
    background: #ffffff;
    border-radius: 12px;
    width: 90%;
    max-width: 650px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    padding: 24px 28px;
    box-sizing: border-box;
}

.teacher-course-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
}

.teacher-course-modal-title {
    margin: 0;
    color: #333;
    font-size: 1.4rem;
    font-family: 'TT Interphases', sans-serif;
}

.teacher-course-modal-subtitle {
    margin: 4px 0 0 0;
    color: #666;
    font-size: 0.9rem;
}

.teacher-course-modal-close {
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #666;
    padding: 4px 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.teacher-course-modal-close:hover {
    background: #f0f0f0;
}

.teacher-material-item {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.teacher-material-item:hover {
    border-color: #1976d2;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.15);
}

.teacher-material-title {
    margin: 0 0 4px 0;
    color: #333;
    font-size: 0.95rem;
    font-weight: 600;
}

.teacher-material-meta {
    margin: 0;
    color: #666;
    font-size: 0.85rem;
}

.teacher-material-type {
    display: inline-block;
    margin-top: 6px;
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 500;
}

.teacher-material-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.teacher-material-copy-btn {
    background: transparent;
    border: none;
    color: #1976d2;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.teacher-material-copy-btn:hover {
    background: #e3f2fd;
}

@media screen and (max-width: 1400px) {
    .courses-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media screen and (max-width: 1200px) {
    .courses-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media screen and (max-width: 768px) {
    .courses-container {
        grid-template-columns: 1fr;
    }
}

/* Cards automatically adjust to fill available space */
.book-requests-container .book-request-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

/* Responsive breakpoints for 4-card layout */
@media screen and (max-width: 1400px) {
    .book-requests-container .book-requests-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

@media screen and (max-width: 1200px) {
    .book-requests-container .book-requests-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media screen and (max-width: 768px) {
    .book-requests-container .book-requests-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

  <!-- Greeting Section -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; margin-top: 5px; width: 100%;">
    <div>
      <div style="font-size: 1.15rem; font-weight: bold; color: #053423;">Panagdait sa tanan ug sa tanang kabuhatan!</div>
      <div style="font-size: 1.05rem; color: #222;"><?php echo htmlspecialchars($teacherName); ?></div>
    </div>
    <div style="margin-left: auto;">
      <span style="display: inline-block; background: <?php echo htmlspecialchars($departmentColor); ?>; color: #fff; font-weight: bold; border-radius: 8px; padding: 8px 18px; font-size: 1.1rem; letter-spacing: 1px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);"><?php echo htmlspecialchars($departmentCode); ?></span>
    </div>
  </div>

  <!-- Overview Section -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 15px; margin-bottom: 15px;">
    <h2 class="main-page-title" style="padding-left: 0px; margin: 0;">Overview</h2>
    <div style="display: flex; align-items: center; gap: 10px;">
      <label for="academicTermSelect" style="font-size: 14px; color: #666; font-weight: 500; font-family: 'TT Interphases', sans-serif;">Academic Term:</label>
      <select id="academicTermSelect" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: 'TT Interphases', sans-serif; background: white; cursor: pointer; min-width: 200px;">
        <option value="">Loading...</option>
      </select>
    </div>
  </div>
  <div class="dashboard-container" style="margin-top: 15px;">
    <div class="box stat-box stat-box-row">
      <span class="stat-title">My Courses</span>
      <span class="stat-amount" id="statTotalCourses">0</span>
      </div>
    <div class="box stat-box stat-box-row">
      <span class="stat-title">Non-Compliant</span>
      <span class="stat-amount" id="statNonCompliant">0</span>
    </div>
    <div class="box stat-box stat-box-row">
      <span class="stat-title">Compliant Courses</span>
      <span class="stat-amount" id="statCompliant">0</span>
      </div>
    </div>

  <!-- My Book Requests Section -->
  <div class="dashboard-section">
    <div class="section-header">
      <div class="header-left">
        <h3>My Book Requests</h3>
        <div class="section-description">Your pending requests for books and reference materials</div>
      </div>
      <div class="header-actions">
        <a href="content.php?page=book-requests" class="view-all-btn">View All</a>
        <button class="nav-btn prev-btn book-nav-btn" id="prevBtn" onclick="showPreviousRequests()">
          <img src="../src/assets/icons/left-arrow-icon.png" alt="Previous" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
          <span style="display: none;">&lt;</span>
        </button>
        <button class="nav-btn next-btn book-nav-btn" id="nextBtn" onclick="showNextRequests()">
          <img src="../src/assets/icons/right-arrow-icon.png" alt="Next" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
          <span style="display: none;">&gt;</span>
        </button>
      </div>
    </div>

    <div class="book-requests-container">
      <div class="book-requests-grid" id="bookRequestsGrid">
        <!-- Cards will be dynamically generated by JavaScript -->
      </div>
      
      <!-- Hidden data for JavaScript -->
      <div id="allBookRequestsData" style="display: none;">
        <?php
        // Sample book requests data for teachers
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
          ]
        ];
        
        // Filter only pending requests
        $pendingRequests = array_filter($bookRequests, function($request) {
          return $request['status'] === 'PENDING';
        });
        
        $pendingRequests = array_values($pendingRequests); // Re-index array
        
        echo json_encode($pendingRequests);
        ?>
      </div>
    </div>

    <div class="section-footer">
      <button class="collapse-btn book-nav-btn" onclick="toggleSection()">
        <span>Collapse</span>
        <img src="../src/assets/icons/right-arrow-icon.png" alt="Collapse" class="collapse-icon" style="transform: rotate(-90deg);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
        <span style="display: none;">^</span>
      </button>
    </div>
  </div>

    <!-- My Courses Section -->
  <div class="courses-section">
    <div class="courses-header">
      <div>
        <h3>My Courses</h3>
        <p>Your assigned courses and their current status</p>
      </div>
    </div>

    <div class="courses-container" id="coursesContainer">
      <!-- Course cards will be dynamically generated by JavaScript -->
    </div>
    
    <!-- Hidden data for JavaScript -->
    <div id="allCoursesData" style="display: none;">
      <?php
      // Sample courses data for teachers
      $courses = [
        [
          'id' => 1,
          'course_code' => 'CS 101',
          'course_name' => 'Introduction to Computer Science',
          'term' => '1st Semester 2024-2025',
          'units' => 3,
          'schedule' => 'MWF 8:00-9:00 AM',
          'room' => 'CCS Lab 1',
          'students' => 45,
          'status' => 'COMPLIANT',
          'compliance_score' => 95,
          'books_compliant' => 5,
          'books_required' => 5
        ],
        [
          'id' => 2,
          'course_code' => 'CS 201',
          'course_name' => 'Data Structures and Algorithms',
          'term' => '1st Semester 2024-2025',
          'units' => 3,
          'schedule' => 'TTH 10:00-11:30 AM',
          'room' => 'CCS Lab 2',
          'students' => 38,
          'status' => 'NON_COMPLIANT',
          'compliance_score' => 65,
          'books_compliant' => 3,
          'books_required' => 5
        ],
        [
          'id' => 3,
          'course_code' => 'CS 301',
          'course_name' => 'Database Management Systems',
          'term' => '1st Semester 2024-2025',
          'units' => 3,
          'schedule' => 'MWF 2:00-3:00 PM',
          'room' => 'CCS Lab 3',
          'students' => 42,
          'status' => 'COMPLIANT',
          'compliance_score' => 88,
          'books_compliant' => 5,
          'books_required' => 5
        ],
        [
          'id' => 4,
          'course_code' => 'CS 401',
          'course_name' => 'Software Engineering',
          'term' => '2nd Semester 2024-2025',
          'units' => 3,
          'schedule' => 'TTH 1:00-2:30 PM',
          'room' => 'CCS Lab 4',
          'students' => 35,
          'status' => 'NON_COMPLIANT',
          'compliance_score' => 72,
          'books_compliant' => 2,
          'books_required' => 5
        ],
        [
          'id' => 5,
          'course_code' => 'CS 501',
          'course_name' => 'Artificial Intelligence',
          'term' => '2nd Semester 2024-2025',
          'units' => 3,
          'schedule' => 'MWF 4:00-5:00 PM',
          'room' => 'CCS Lab 5',
          'students' => 28,
          'status' => 'COMPLIANT',
          'compliance_score' => 92,
          'books_compliant' => 6,
          'books_required' => 5
        ],
        [
          'id' => 6,
          'course_code' => 'CS 601',
          'course_name' => 'Machine Learning',
          'term' => '2nd Semester 2024-2025',
          'units' => 3,
          'schedule' => 'TTH 3:00-4:30 PM',
          'room' => 'CCS Lab 6',
          'students' => 25,
          'status' => 'NON_COMPLIANT',
          'compliance_score' => 58,
          'books_compliant' => 1,
          'books_required' => 5
        ],
        [
          'id' => 7,
          'course_code' => 'CS 701',
          'course_name' => 'Advanced Programming',
          'term' => 'Summer 2025',
          'units' => 3,
          'schedule' => 'MWF 10:00-11:00 AM',
          'room' => 'CCS Lab 7',
          'students' => 22,
          'status' => 'COMPLIANT',
          'compliance_score' => 90,
          'books_compliant' => 5,
          'books_required' => 5
        ]
      ];
      echo json_encode($courses);
      ?>
          </div>
        </div>

    <!-- Course Details Modal (Teacher) -->
    <div id="teacherCourseDetailsModal" class="teacher-course-modal-overlay">
      <div class="teacher-course-modal-box">
        <div class="teacher-course-modal-header">
          <div>
            <h2 class="teacher-course-modal-title" id="teacherCourseTitle">Course Details</h2>
            <p class="teacher-course-modal-subtitle" id="teacherCourseSubtitle">Learning materials for this course</p>
          </div>
          <button class="teacher-course-modal-close" type="button" onclick="closeTeacherCourseDetailsModal()">&times;</button>
        </div>
        <div id="teacherCourseDetailsContent">
          <!-- Learning materials will be injected here -->
        </div>
      </div>
    </div>

<script>
  // Book Requests Variables
  let allRequests = [];
  let currentPage = 0;
  let requestsPerPage = 4;

  // Courses Variables
  let allCourses = [];
  let currentCoursePage = 0;
  let coursesPerPage = 4;

  // Sample learning materials per course (for modal)
  const teacherCourseMaterials = {
    1: {
      course_code: 'CS 101',
      course_name: 'Introduction to Computer Science',
      materials: [
        { title: 'Computer Science: An Overview', author: 'J. Glenn Brookshear', year: '2023', publisher: 'Pearson', type: 'Textbook' },
        { title: 'Foundations of Computing', author: 'A. Tanenbaum', year: '2022', publisher: 'Prentice Hall', type: 'Reference Book' }
      ]
    },
    2: {
      course_code: 'CS 201',
      course_name: 'Data Structures and Algorithms',
      materials: [
        { title: 'Algorithms', author: 'S. Dasgupta', year: '2021', publisher: 'McGraw-Hill', type: 'Textbook' },
        { title: 'Data Structures in Practice', author: 'M. Goodrich', year: '2020', publisher: 'Wiley', type: 'Reference Book' }
      ]
    },
    3: {
      course_code: 'CS 301',
      course_name: 'Database Management Systems',
      materials: [
        { title: 'Database System Concepts', author: 'A. Silberschatz', year: '2023', publisher: 'McGraw-Hill', type: 'Textbook' },
        { title: 'SQL Essentials', author: 'K. Brown', year: '2022', publisher: 'O\'Reilly', type: 'Online Resource' }
      ]
    },
    4: {
      course_code: 'CS 401',
      course_name: 'Software Engineering',
      materials: [
        { title: 'Software Engineering', author: 'I. Sommerville', year: '2020', publisher: 'Pearson', type: 'Textbook' },
        { title: 'Clean Code', author: 'R. Martin', year: '2019', publisher: 'Prentice Hall', type: 'Reference Book' }
      ]
    },
    5: {
      course_code: 'CS 501',
      course_name: 'Artificial Intelligence',
      materials: [
        { title: 'Artificial Intelligence: A Modern Approach', author: 'Russell & Norvig', year: '2021', publisher: 'Pearson', type: 'Textbook' },
        { title: 'Deep Learning', author: 'Goodfellow et al.', year: '2016', publisher: 'MIT Press', type: 'Reference Book' }
      ]
    },
    6: {
      course_code: 'CS 601',
      course_name: 'Machine Learning',
      materials: [
        { title: 'Pattern Recognition and Machine Learning', author: 'C. Bishop', year: '2019', publisher: 'Springer', type: 'Textbook' },
        { title: 'Hands-On Machine Learning', author: 'A. Géron', year: '2022', publisher: 'O\'Reilly', type: 'Reference Book' }
      ]
    },
    7: {
      course_code: 'CS 701',
      course_name: 'Advanced Programming',
      materials: [
        { title: 'Effective Java', author: 'J. Bloch', year: '2018', publisher: 'Addison-Wesley', type: 'Reference Book' },
        { title: 'Refactoring', author: 'M. Fowler', year: '2019', publisher: 'Addison-Wesley', type: 'Reference Book' }
      ]
    }
  };

  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing teacher dashboard...');
    
    // Load data from PHP
    const requestsData = document.getElementById('allBookRequestsData');
    
    console.log('Found elements:', { requestsData });
    
    if (requestsData) {
      allRequests = JSON.parse(requestsData.textContent);
      
      console.log('Loaded pending requests:', allRequests);
      console.log('Pending requests count:', allRequests.length);
      
      // Display current page
      displayCurrentPage();
      
      // Auto-collapse the section on page load
      setTimeout(() => {
        toggleSection();
      }, 100);
      
      console.log('Teacher dashboard initialization complete');
    } else {
      console.error('Failed to find required data elements');
    }

    // Load courses data from PHP
    const coursesData = document.getElementById('allCoursesData');
    
    if (coursesData) {
      allCourses = JSON.parse(coursesData.textContent);
      
      console.log('Loaded courses:', allCourses);
      console.log('Courses count:', allCourses.length);
      
      // Display all courses (no pagination)
      displayAllCourses();
      
      console.log('Courses initialization complete');
    } else {
      console.error('Failed to find courses data elements');
    }
    
    // Load academic terms dropdown
    loadAcademicTerms();
  });

  function displayCurrentPage() {
    const grid = document.getElementById('bookRequestsGrid');
    const start = currentPage * requestsPerPage;
    const end = start + requestsPerPage;
    const requestsToDisplay = allRequests.slice(start, end);

    grid.innerHTML = ''; // Clear existing cards

    requestsToDisplay.forEach(request => {
      const card = createRequestCard(request);
      grid.appendChild(card);
    });

    // Update navigation buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const totalRequests = allRequests.length;
    const totalPages = Math.ceil(totalRequests / requestsPerPage);

    // Hide prev button on first page
    if (currentPage === 0) {
      prevBtn.style.display = 'none';
    } else {
      prevBtn.style.display = 'inline-flex';
    }

    // Hide next button on last page
    if (currentPage >= totalPages - 1) {
      nextBtn.style.display = 'none';
    } else {
      nextBtn.style.display = 'inline-flex';
    }

    // Hide both buttons if there's only one page or no requests
    if (totalRequests <= requestsPerPage) {
      prevBtn.style.display = 'none';
      nextBtn.style.display = 'none';
    }
  }

  function showNextRequests() {
    currentPage++;
    displayCurrentPage();
  }

  function showPreviousRequests() {
    currentPage--;
    displayCurrentPage();
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
    const departmentColor = '<?php echo $_SESSION["selected_role"]["department_color"] ?? "#C41E3A"; ?>';
    
    const card = document.createElement('div');
    card.className = 'book-request-card';
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
        <div class="status-pending-btn">Pending</div>
      </div>
      <div class="request-date">Requested on: ${new Date().toLocaleDateString()}</div>
    `;
    
    return card;
  }

  function toggleSection() {
    console.log('toggleSection called');
    
    const section = document.querySelector('.dashboard-section');
    const container = section.querySelector('.book-requests-container');
    const footer = section.querySelector('.section-footer');
    const collapseBtn = section.querySelector('.collapse-btn');
    const headerActions = section.querySelector('.header-actions');
    
    console.log('Elements found:', { container, footer, collapseBtn, headerActions });
    console.log('Container current display:', container.style.display);
    console.log('allRequests length:', allRequests.length);
    
    // Check if container is currently hidden
    const isCurrentlyHidden = container.style.display === 'none';
    
    console.log('Is currently hidden:', isCurrentlyHidden);
    
    if (isCurrentlyHidden) {
      // Expand - show normal layout
      console.log('Expanding section...');
      container.style.display = 'block';
      footer.style.display = 'flex';
      
      // Remove the collapsed controls if they exist
      const existingCollapsedControls = section.querySelector('.collapsed-controls');
      if (existingCollapsedControls) {
        existingCollapsedControls.remove();
      }
      
      // Restore the navigation buttons
      headerActions.style.display = 'flex';
      
      console.log('Restored navigation buttons and removed collapsed controls');
    } else {
      // Collapse - just replace navigation buttons with red badge + expand button
      console.log('Collapsing section...');
      container.style.display = 'none';
      footer.style.display = 'none';
      
      // Hide the navigation buttons and replace with red badge + expand button
      headerActions.style.display = 'none';
      
      // Create simple red badge + expand button in the same header area
      const totalRequests = allRequests.length;
      const collapsedControls = document.createElement('div');
      collapsedControls.className = 'collapsed-controls';
      collapsedControls.innerHTML = `
        <div class="request-count-badge">${totalRequests}</div>
        <button class="expand-btn book-nav-btn" onclick="toggleSection()">
          <span>Expand</span>
          <img src="../src/assets/icons/right-arrow-icon.png" alt="Expand" class="collapse-icon" style="transform: rotate(90deg);">
        </button>
      `;
      
      // Insert the collapsed controls in the same header area
      const sectionHeader = section.querySelector('.section-header');
      sectionHeader.appendChild(collapsedControls);
      
      console.log('Replaced navigation with red badge + expand button in same header');
    }
  }

    // Courses Functions - Simple display like department dean
  function displayAllCourses() {
    const container = document.getElementById('coursesContainer');
    
    console.log(`Displaying all ${allCourses.length} courses`);
    
    // Clear the container
    container.innerHTML = '';
    
    // Add course cards
    allCourses.forEach(course => {
      const courseCard = createCourseCard(course);
      container.appendChild(courseCard);
    });
    
    // Setup tooltip positioning after courses are rendered
    setTimeout(() => {
      setupTooltipPositioning();
    }, 100);
  }

  function createCourseCard(course) {
    const card = document.createElement('div');
    card.className = 'course-card';
    
    const statusClass = course.status === 'COMPLIANT' ? 'status-compliant-btn' : 'status-non-compliant-btn';
    const statusText = course.status === 'COMPLIANT' ? 'Compliant' : 'Non-Compliant';
    
    // Book compliance indicator
    const booksCompliant = course.books_compliant || 0;
    const booksRequired = course.books_required || 5;
    const bookIndicator = `${booksCompliant}/${booksRequired}`;
    
    // Add red color class if books are below minimum (5)
    const indicatorClass = booksCompliant < 5 ? 'book-indicator below-minimum' : 'book-indicator';
    
    // Generate tooltip message based on compliance
    let tooltipMessage = '';
    if (booksCompliant >= booksRequired) {
      tooltipMessage = 'All required books are available';
    } else {
      const booksNeeded = booksRequired - booksCompliant;
      tooltipMessage = `Missing ${booksNeeded} book${booksNeeded > 1 ? 's' : ''} to meet requirements`;
    }
    
    card.innerHTML = `
      <div class="course-header">
        <div class="course-code">${course.course_code}</div>
        <div class="course-name">${course.course_name}</div>
      </div>
      
      <div class="book-compliance">
        <div class="book-compliance-left">
          <span class="${indicatorClass}">${bookIndicator}</span>
          <span class="book-label">Books</span>
        </div>
        <div class="book-info-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
          </svg>
          <div class="book-info-tooltip">${tooltipMessage}</div>
        </div>
      </div>
      
      <div class="course-actions">
        <div class="${statusClass}">${statusText}</div>
        <button class="course-view-details-btn" onclick="viewCourseDetails(${course.id})">View Details</button>
      </div>
    `;
    
    return card;
  }

  // Simple tooltip setup - using CSS hover
  function setupTooltipPositioning() {
    // Tooltips now work with simple CSS hover
    console.log('CSS hover tooltip setup complete');
  }

  function viewCourseDetails(courseId) {
    if (!courseId) return;

    const course = teacherCourseMaterials[courseId];
    if (!course) {
      alert('Learning materials not found for this course.');
      return;
    }

    const modal = document.getElementById('teacherCourseDetailsModal');
    const titleEl = document.getElementById('teacherCourseTitle');
    const subtitleEl = document.getElementById('teacherCourseSubtitle');
    const contentEl = document.getElementById('teacherCourseDetailsContent');

    if (!modal || !titleEl || !subtitleEl || !contentEl) return;

    titleEl.textContent = `${course.course_code} - ${course.course_name}`;
    subtitleEl.textContent = 'Learning materials under this course';

    // Store current viewing course ID for refresh
    window.currentViewingCourseId = courseId;
    
    if (!course.materials || course.materials.length === 0) {
      contentEl.innerHTML = '<p style="color: #666; font-size: 0.9rem;">No learning materials listed for this course yet.</p>';
    } else {
      // Always use APA 7th format as default
      contentEl.innerHTML = course.materials.map((mat, index) => {
        // Display in APA 7th format
        const citation = getAPA7thCitation(mat);
        
        return `
        <div class="teacher-material-item">
          <div class="teacher-material-header">
            <div>
              <h4 class="teacher-material-title">${mat.title}</h4>
              <p class="teacher-material-meta">${citation}</p>
            </div>
            <button type="button" class="teacher-material-copy-btn" onclick="copyMaterialCitation(${courseId}, ${index})">Copy</button>
          </div>
          <span class="teacher-material-type">${mat.type}</span>
        </div>
      `;
      }).join('');
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeTeacherCourseDetailsModal() {
    const modal = document.getElementById('teacherCourseDetailsModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }


  function getAPA7thCitation(mat) {
    // APA 7th Edition format: Author, A. A. (Year). Title. Publisher.
    let citation = '';
    
    if (mat.author && mat.year && mat.title) {
      // Format author name (Last, F. M.)
      let authorFormatted = mat.author;
      if (authorFormatted.includes(',')) {
        // Already formatted as "Last, First"
        authorFormatted = authorFormatted.trim();
      } else {
        // Format as "Last, F."
        const nameParts = authorFormatted.trim().split(' ');
        if (nameParts.length >= 2) {
          const lastName = nameParts[nameParts.length - 1];
          const firstName = nameParts[0];
          const firstInitial = firstName.charAt(0).toUpperCase();
          authorFormatted = `${lastName}, ${firstInitial}.`;
        }
      }
      
      citation = `${authorFormatted} (${mat.year}). ${mat.title}.`;
      
      if (mat.publisher) {
        citation += ` ${mat.publisher}.`;
      }
    } else {
      // Fallback if data is incomplete
      citation = mat.title || 'Untitled';
      if (mat.author) citation = `${mat.author}. ${citation}`;
      if (mat.year) citation += ` (${mat.year}).`;
      if (mat.publisher) citation += ` ${mat.publisher}.`;
    }
    
    return citation;
  }

  function copyMaterialCitation(courseId, materialIndex) {
    const course = teacherCourseMaterials[courseId];
    if (!course || !course.materials || !course.materials[materialIndex]) {
      alert('Unable to copy citation for this material.');
      return;
    }

    const mat = course.materials[materialIndex];
    // Always use APA 7th format as default
    const citation = getAPA7thCitation(mat);

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(citation)
        .then(() => {
          console.log('Citation copied to clipboard.');
        })
        .catch(() => {
          alert('Failed to copy citation. Please try again.');
        });
    } else {
      // Fallback for older browsers
      const tempInput = document.createElement('textarea');
      tempInput.value = citation;
      document.body.appendChild(tempInput);
      tempInput.select();
      try {
        document.execCommand('copy');
        console.log('Citation copied to clipboard.');
      } catch (e) {
        alert('Failed to copy citation. Please try again.');
      }
      document.body.removeChild(tempInput);
    }
  }

  // Close teacher course modal when clicking outside the box
  document.addEventListener('click', function(event) {
    const modal = document.getElementById('teacherCourseDetailsModal');
    if (!modal || modal.style.display !== 'flex') return;
    if (event.target === modal) {
      closeTeacherCourseDetailsModal();
    }
  });

  // Academic Term Dropdown Functions
  let currentTermId = null;
  
  function loadAcademicTerms() {
    const termSelect = document.getElementById('academicTermSelect');
    if (!termSelect) {
      console.error('Academic term select element not found');
      return;
    }
    
    // Fetch terms from API - use correct relative path
    fetch('api/get_academic_terms.php')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        console.log('Academic terms API response:', data);
        if (data.status === 'success' && data.terms && data.terms.length > 0) {
          // Clear existing options
          termSelect.innerHTML = '';
          
          // Add "All Terms" option
          const allTermsOption = document.createElement('option');
          allTermsOption.value = '';
          allTermsOption.textContent = 'All Terms';
          termSelect.appendChild(allTermsOption);
          
          // Add each term as an option
          data.terms.forEach(term => {
            const option = document.createElement('option');
            option.value = term.id;
            option.textContent = term.display_name;
            termSelect.appendChild(option);
          });
          
          // Set current term if available
          if (data.current_term) {
            termSelect.value = data.current_term.id;
            currentTermId = data.current_term.id;
          }
          
          // Load stats for selected term
          loadOverviewStats(currentTermId);
          
          // Add event listener for term change
          termSelect.addEventListener('change', function() {
            currentTermId = this.value || null;
            loadOverviewStats(currentTermId);
            filterCoursesByTerm(currentTermId);
          });
          
          console.log('Academic terms loaded successfully');
        } else {
          console.error('Failed to load academic terms:', data.message || 'No terms found');
          termSelect.innerHTML = '<option value="">No terms available</option>';
          // Still try to load stats even if no terms
          loadOverviewStats(null);
        }
      })
      .catch(error => {
        console.error('Error fetching academic terms:', error);
        termSelect.innerHTML = '<option value="">Error loading terms</option>';
        // Still try to load stats on error
        loadOverviewStats(null);
      });
  }
  
  function loadOverviewStats(termId) {
    const url = termId ? `api/get_overview_stats.php?term_id=${termId}` : 'api/get_overview_stats.php';
    
    fetch(url)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        console.log('Overview stats API response:', data);
        if (data.status === 'success' && data.stats) {
          // Update stat elements
          const totalEl = document.getElementById('statTotalCourses');
          const nonCompliantEl = document.getElementById('statNonCompliant');
          const compliantEl = document.getElementById('statCompliant');
          
          if (totalEl) totalEl.textContent = data.stats.total_courses || 0;
          if (nonCompliantEl) nonCompliantEl.textContent = data.stats.non_compliant_courses || 0;
          if (compliantEl) compliantEl.textContent = data.stats.compliant_courses || 0;
          
          console.log('Overview stats updated:', data.stats);
        } else {
          console.error('Failed to load overview stats:', data.message);
        }
      })
      .catch(error => {
        console.error('Error fetching overview stats:', error);
      });
  }
  
  function filterCoursesByTerm(termId) {
    // This function will filter the courses displayed based on the selected term
    // For now, we'll reload courses from the database via an API call
    // You may need to create an API endpoint to fetch courses filtered by term
    console.log('Filtering courses by term:', termId);
    
    // For now, just log - we'll need to implement course filtering
    // This might require updating the courses loading to fetch from database
    // instead of using the static data
  }

</script>