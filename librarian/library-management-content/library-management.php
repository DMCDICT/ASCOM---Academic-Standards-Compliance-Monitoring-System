<?php
// This file is included within the librarian system
// No need to start session or check authentication as it's handled by the parent system

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get department information from session
$departmentCode = 'CCS'; // Default fallback
$departmentColor = '#C41E3A'; // Default red color

// Since we're using hardcoded colors for sample data, no need for complex session handling

// Initial statistics (these will be updated by JavaScript)
$totalItems = 50;
$availableItems = 35;
$checkedOutItems = 10;
$reservedItems = 3;
$maintenanceItems = 2;
?>

<style>
/* Library Management Page Styles */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0px;
}

.main-page-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0 !important;
    padding: 0 !important;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
}

.page-description {
    font-size: 14px;
    margin: 5px 0 0px 0;
    line-height: 1.4;
    color: #666;
    font-family: 'TT Interphases', sans-serif;
}

.library-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.add-item-btn {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.add-item-btn:hover {
    background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
}

.import-btn {
    background: #f5f5f5;
    color: #666;
    border: 1px solid #e0e0e0;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    margin-right: 12px;
}

.import-btn:hover {
    background: #4caf50;
    color: white;
    border-color: #4caf50;
    transform: translateY(-2px);
}

.export-btn {
    background: #f5f5f5;
    color: #666;
    border: 1px solid #e0e0e0;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
}

.export-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-2px);
}

.library-table-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    overflow: hidden;
    margin-top: 20px;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0 24px;
    gap: 20px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 40px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    transition: border-color 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    opacity: 0.6;
}

.filter-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}


.course-group-row {
    background: #f8f9fa;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.course-group-row:hover {
    background: #e3f2fd;
}

.course-code-cell {
    font-weight: 600;
    color: #1976d2;
}

.course-title-cell {
    font-weight: 500;
    color: #333;
    max-width: 210px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.4;
    width: 210px;
}

.year-level-cell {
    font-weight: 500;
    color: #666;
    width: 70px;
    text-align: center;
}

.units-cell {
    font-weight: 500;
    color: #666;
    width: 50px;
    text-align: center;
}

.programs-cell {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
    align-items: center;
    min-width: 70px;
    max-width: 70px;
    overflow: hidden;
}

.program-badge {
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: white;
    display: inline-block;
    margin-right: 2px;
    white-space: nowrap;
    flex-shrink: 0;
}

.program-badge:last-child {
    margin-right: 0;
}

.additional-programs {
    cursor: help;
    position: relative;
    padding: 3px 6px;
    font-size: 10px;
    margin-left: 2px;
    flex-shrink: 0;
}

.additional-programs:hover {
    background-color: #5a6268 !important;
    transform: scale(1.05);
    transition: all 0.2s ease;
}

.book-count-cell {
    font-weight: 600;
    color: #1976d2;
}

.compliance-status-cell {
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
    width: 90px;
}

.compliance-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-family: 'TT Interphases', sans-serif;
    white-space: nowrap;
    min-width: 60px;
    text-align: center;
}

.compliance-badge.compliant {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.compliance-badge.non-compliant {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.term-year-cell {
    color: #666;
    font-size: 13px;
    min-width: 80px;
    max-width: 80px;
    width: 80px;
}

.library-table th.term-year-header {
    min-width: 80px;
    max-width: 80px;
    width: 80px;
}

.view-books-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
}

.view-books-btn:hover {
    background: #45a049;
}

.filter-btn {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.filter-btn:hover {
    background: #f5f5f5;
    border-color: #1976d2;
    color: #1976d2;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.filter-btn:active {
    transform: translateY(0);
}

.table-wrapper {
    overflow-x: hidden;
    margin: 20px 24px 0 24px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.library-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-family: 'TT Interphases', sans-serif;
    table-layout: fixed;
}

.library-table thead {
    background: #f8f9fa;
}

.library-table th {
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    font-size: 14px;
    border-bottom: 2px solid #e0e0e0;
    white-space: nowrap;
}

.library-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #555;
    vertical-align: middle;
}

.library-table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
}

.library-table tbody tr:hover {
    background: #f0f7ff;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-available {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-checked-out {
    background: #fff3e0;
    color: #f57c00;
}

.status-reserved {
    background: #e3f2fd;
    color: #1976d2;
}

.status-maintenance {
    background: #ffebee;
    color: #d32f2f;
}

.category-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-textbook {
    background: #e8f5e8;
    color: #2e7d32;
}

.category-reference {
    background: #e3f2fd;
    color: #1976d2;
}

.category-journal {
    background: #f3e5f5;
    color: #7b1fa2;
}

.category-research {
    background: #fff3e0;
    color: #f57c00;
}

.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-btn-small {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
}

.edit-btn {
    background: #2196F3;
    color: white;
}

.edit-btn:hover {
    background: #1976d2;
}

.delete-btn {
    background: #f44336;
    color: white;
}

.delete-btn:hover {
    background: #d32f2f;
}

.view-btn {
    background: #4CAF50;
    color: white;
}

.view-btn:hover {
    background: #45a049;
}

.table-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid #f0f0f0;
    position: relative;
}

.pagination-info {
    color: #666;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    position: absolute;
    left: 24px;
    top: 50%;
    transform: translateY(-50%);
    white-space: nowrap;
}

/* Responsive pagination for smaller screens */
@media (max-width: 768px) {
    .table-pagination {
        flex-direction: column;
        gap: 15px;
        text-align: center;
        position: relative;
    }
    
    .pagination-info {
        position: static;
        transform: none;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .pagination-center {
        order: 2;
    }
    
    .pagination-controls {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .page-numbers {
        justify-content: center;
    }
}

.pagination-center {
    display: flex;
    justify-content: center;
    align-items: center;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
    pointer-events: auto;
    flex-shrink: 0;
}

.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #666;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    position: relative;
    z-index: 1;
    pointer-events: auto;
}

.pagination-btn:hover:not(:disabled) {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

.pagination-btn:disabled {
    background: #f5f5f5;
    color: #ccc;
    cursor: not-allowed;
}

.page-numbers {
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
}

.page-number {
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #666;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    min-width: 40px;
    text-align: center;
    position: relative;
    z-index: 1;
    pointer-events: auto;
}

.page-number:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

.page-number.active {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

/* Filter Modal Styles */
.filter-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    min-width: 0;
}

.filter-label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
}

.filter-select {
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    background: white;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

.checkbox-container {
    max-height: 120px;
    overflow-y: auto;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 8px;
    background: white;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
}

.checkbox-item:hover {
    background: #f5f5f5;
}

.checkbox-item input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.checkbox-item span {
    color: #333;
    font-weight: 500;
}

/* Program Filter Button */
.program-filter-btn {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    transition: all 0.3s ease;
    position: relative;
    box-sizing: border-box;
    overflow: hidden;
    text-align: left;
}

.program-filter-btn:hover {
    border-color: #1976d2;
    background: #f8f9fa;
}

.program-filter-btn.active {
    border-color: #1976d2;
    background: #f0f7ff;
}

.dropdown-arrow {
    font-size: 12px;
    color: #666;
    transition: transform 0.3s ease;
}

.program-filter-btn.active .dropdown-arrow {
    transform: rotate(180deg);
}

.program-filter-btn span:first-child {
    flex: 1;
    min-width: 0;
    max-width: calc(100% - 30px);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-right: 8px;
}

/* Program Filter Modal */
.program-filter-modal {
    position: fixed;
    top: auto;
    left: auto;
    right: auto;
    width: 400px;
    max-width: 90vw;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    z-index: 99999;
    margin-top: 4px;
}

.program-search-container {
    position: relative;
    padding: 8px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.program-search-container .search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    opacity: 0.6;
}

.program-search-container input {
    width: calc(100% - 40px);
    padding: 10px 16px 10px 40px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    outline: none;
    box-sizing: border-box;
}

.program-search-container input:focus {
    border-color: #1976d2;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.1);
}

.program-list {
    max-height: 160px;
    overflow-y: auto;
    padding: 8px 0;
}

.program-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
}

.program-item:hover {
    background: #f5f5f5;
}

.program-item.selected {
    background: #e3f2fd;
    color: #1976d2;
}

.program-item input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
    width: 16px;
    height: 16px;
}

.program-item span {
    color: #333;
    font-weight: 500;
}

.program-item.selected span {
    color: #1976d2;
    font-weight: 600;
}

.program-actions {
    padding: 8px 12px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.clear-all-btn {
    background: none;
    border: none;
    color: #1976d2;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'TT Interphases', sans-serif;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.clear-all-btn:hover {
    background: #f0f7ff;
}

.apply-program-btn {
    background: #1976d2;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'TT Interphases', sans-serif;
    transition: background-color 0.2s ease;
    margin-left: 8px;
}

.apply-program-btn:hover {
    background: #1565c0;
}

/* Modal Close Button */
.close-button {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    padding: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
}

.close-button:hover {
    background: #f0f0f0;
    color: #333;
    transform: scale(1.1);
}

.program-message {
    padding: 20px;
    text-align: center;
    color: #666;
    font-style: italic;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px dashed #ccc;
    margin: 8px 16px;
}


.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.filter-apply-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-apply-btn:hover {
    background: #45a049;
    transform: translateY(-1px);
}

.filter-clear-btn {
    background: #f5f5f5;
    color: #666;
    border: 1px solid #e0e0e0;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-clear-btn:hover {
    background: #e0e0e0;
    color: #333;
}

.filter-close-btn {
    background: #666;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-close-btn:hover {
    background: #555;
}
</style>

<!-- Header Section -->
<div class="header-section">
    <div>
        <h1 class="main-page-title">Library Management</h1>
        <p class="page-description">Manage library inventory, circulation, and catalog records</p>
    </div>
    <div class="library-actions">
        <button class="import-btn" onclick="importLibraryData()">Import Data</button>
        <button class="export-btn" onclick="exportLibraryData()">Export Data</button>
    </div>
</div>

<!-- Library Table Container -->
<div class="library-table-container">
    <div class="table-controls">
        <div class="search-box">
            <input type="text" id="librarySearch" placeholder="Search by course code, title, or program..." onkeyup="filterLibraryItems()">
            <img src="../src/assets/icons/search-icon.png" alt="Search" class="search-icon">
        </div>
        <div class="filter-controls">
            <button class="filter-btn" onclick="openFilterModal()">
                <img src="../src/assets/icons/filter-icon.png" alt="Filter" style="width: 16px; height: 16px; margin-right: 8px;">
                Filter Options
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="library-table" id="libraryTable">
            <thead>
                <tr>
                    <th style="width: 90px;">Course Code</th>
                    <th style="width: 210px;">Course Title</th>
                    <th style="width: 50px;">Units</th>
                    <th style="width: 70px;">Programs</th>
                    <th class="term-year-header" style="width: 80px;">Term &<br>Academic Year</th>
                    <th style="width: 70px;">Year Level</th>
                    <th style="width: 70px;">Book<br>References</th>
                    <th style="width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody id="libraryTableBody">
                <!-- Course groups will be dynamically generated here -->
            </tbody>
        </table>
    </div>

    <div class="table-pagination">
        <div class="pagination-info">
            <span id="paginationInfo">Showing 1-10 of 50 items</span>
        </div>
        <div class="pagination-center">
            <div class="pagination-controls">
                <button class="pagination-btn" id="prevPageBtn" onclick="changePage(-1)">Previous</button>
                <div class="page-numbers" id="pageNumbers">
                    <!-- Page numbers will be generated here -->
                </div>
                <button class="pagination-btn" id="nextPageBtn" onclick="changePage(1)">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Course Book References Modal -->
<div id="courseBooksModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 1000px; width: 90vw; max-height: 90vh; overflow: hidden;">
        <div class="modal-header">
            <h2 id="courseBooksTitle">Course Book References</h2>
            <img class="close-button" src="../src/assets/icons/close-icon.png" alt="Close" onclick="closeCourseBooksModal()" onmouseover="this.src='../src/assets/icons/close-hover-icon.png';" onmouseout="this.src='../src/assets/icons/close-icon.png';" style="cursor: pointer; width: 40px; height: 40px;">
        </div>
        <div class="modal-content" style="padding: 12px 8px;">
            <div id="courseBooksContent">
                <!-- Book references will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Library Filter Modal -->
<div id="libraryFilterModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 800px; width: 90vw; max-height: 90vh; overflow: hidden;">
                    <div class="modal-header">
                        <h2>Filter Library Data</h2>
                        <button class="close-button" onclick="closeFilterModal()">×</button>
                    </div>
        <div class="modal-content" style="padding: 24px;">
            <div class="filter-grid">
                <!-- Row 1: Year Level and Academic Term -->
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Year Level</label>
                        <select id="filterYearLevel" class="filter-select">
                            <option value="all">All Year Levels</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Academic Term</label>
                        <select id="filterAcademicTerm" class="filter-select">
                            <option value="all">All Terms</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Department and Programs -->
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Department</label>
                        <select id="filterDepartment" class="filter-select" onchange="updateProgramsFilter()">
                            <option value="all">All Departments</option>
                            <option value="CCS">College of Computer Studies</option>
                            <option value="CBE">College of Business Education</option>
                            <option value="CAS">College of Arts and Sciences</option>
                            <option value="COE">College of Engineering</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Programs</label>
                        <button id="programFilterBtn" class="program-filter-btn" onclick="toggleProgramModal()">
                            <span id="programFilterText">Programs: Any</span>
                            <span class="dropdown-arrow">▲</span>
                        </button>
                        <div id="programFilterModal" class="program-filter-modal" style="display: none;">
                            <div class="program-search-container">
                                <img src="../src/assets/icons/search-icon.png" alt="Search" class="search-icon">
                                <input type="text" id="programSearch" placeholder="Search programs..." onkeyup="filterPrograms()">
                            </div>
                        <div id="programList" class="program-list">
                        </div>
                            <div class="program-actions">
                                <button class="clear-all-btn" onclick="clearProgramSelection()">Clear All</button>
                                <button class="apply-program-btn" onclick="toggleProgramModal()">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Filter Actions -->
            <div class="filter-actions">
                <button class="filter-apply-btn" onclick="applyFilters()">Apply Filters</button>
                <button class="filter-clear-btn" onclick="clearFilters()">Clear All</button>
                <button class="filter-close-btn" onclick="closeFilterModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__FILE__) . '/../modals/add_course_modal.php'; ?>
<?php include dirname(__FILE__) . '/../modals/import_data_modal.php'; ?>

<script>
// Library Management Variables
let allCourses = [];
let filteredCourses = [];
let currentLibraryPage = 0;
let libraryItemsPerPage = 10;
let currentYearFilter = 'all';
let totalPages = 0;
let totalRecords = 0;

// Filter state
let currentFilters = {
    yearLevel: 'all',
    academicTerm: 'all',
    department: 'all',
    program: ['all']
};

// Department to Programs mapping
const departmentPrograms = {
    'CCS': [
        { code: 'BSCS', name: 'Bachelor of Science in Computer Science' },
        { code: 'BSIT', name: 'Bachelor of Science in Information Technology' },
        { code: 'BSIS', name: 'Bachelor of Science in Information Systems' }
    ],
    'CBE': [
        { code: 'BSBA', name: 'Bachelor of Science in Business Administration' },
        { code: 'BSA', name: 'Bachelor of Science in Accountancy' },
        { code: 'BSHM', name: 'Bachelor of Science in Hospitality Management' }
    ],
    'CAS': [
        { code: 'BSPSY', name: 'Bachelor of Science in Psychology' },
        { code: 'ABCOMM', name: 'Bachelor of Arts in Communication' },
        { code: 'BSED', name: 'Bachelor of Science in Education' }
    ],
    'COE': [
        { code: 'BSCE', name: 'Bachelor of Science in Civil Engineering' },
        { code: 'BSEE', name: 'Bachelor of Science in Electrical Engineering' },
        { code: 'BSME', name: 'Bachelor of Science in Mechanical Engineering' }
    ]
};

// Sample library management data
const sampleLibraryItems = [
    {
        id: 1,
        title: "Computer Science Fundamentals",
        author: "Dr. Maria Garcia",
        isbn: "978-0123456789",
        category: "textbook",
        callNumber: "CS 001.1 G37 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-15",
        location: "Main Library - CS Section"
    },
    {
        id: 2,
        title: "Software Engineering Methodologies",
        author: "Dr. Sarah Johnson",
        isbn: "978-0123456790",
        category: "reference",
        callNumber: "CS 005.1 J64 2024",
        status: "checked-out",
        dueDate: "2024-02-15",
        addedDate: "2024-01-12",
        location: "Main Library - Reference"
    },
    {
        id: 3,
        title: "Machine Learning Applications",
        author: "Dr. Emily Davis",
        isbn: "978-0123456791",
        category: "research",
        callNumber: "CS 006.3 D38 2024",
        status: "reserved",
        dueDate: "2024-02-20",
        addedDate: "2024-01-14",
        location: "Main Library - Research Section"
    },
    {
        id: 4,
        title: "Data Structures and Algorithms",
        author: "Dr. Lisa Anderson",
        isbn: "978-0123456792",
        category: "textbook",
        callNumber: "CS 001.5 A53 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-13",
        location: "Main Library - CS Section"
    },
    {
        id: 5,
        title: "Operating System Design",
        author: "Dr. Rachel Green",
        isbn: "978-0123456793",
        category: "reference",
        callNumber: "CS 004.3 G74 2024",
        status: "maintenance",
        dueDate: null,
        addedDate: "2024-01-16",
        location: "Main Library - Reference"
    },
    {
        id: 6,
        title: "Computer Vision Techniques",
        author: "Dr. Nicole Rodriguez",
        isbn: "978-0123456794",
        category: "journal",
        callNumber: "CS 006.4 R63 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-17",
        location: "Main Library - Journal Section"
    },
    {
        id: 7,
        title: "Database Management Systems",
        author: "Dr. Michael Brown",
        isbn: "978-0123456795",
        category: "textbook",
        callNumber: "CS 004.2 B76 2024",
        status: "checked-out",
        dueDate: "2024-02-18",
        addedDate: "2024-01-18",
        location: "Main Library - CS Section"
    },
    {
        id: 8,
        title: "Network Security Protocols",
        author: "Dr. Jennifer Wilson",
        isbn: "978-0123456796",
        category: "reference",
        callNumber: "CS 004.6 W55 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-19",
        location: "Main Library - Reference"
    },
    {
        id: 9,
        title: "Artificial Intelligence Research",
        author: "Dr. David Lee",
        isbn: "978-0123456797",
        category: "research",
        callNumber: "CS 006.3 L44 2024",
        status: "reserved",
        dueDate: "2024-02-25",
        addedDate: "2024-01-20",
        location: "Main Library - Research Section"
    },
    {
        id: 10,
        title: "Web Development Frameworks",
        author: "Dr. Amanda Taylor",
        isbn: "978-0123456798",
        category: "textbook",
        callNumber: "CS 004.7 T39 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-21",
        location: "Main Library - CS Section"
    },
    {
        id: 11,
        title: "Cybersecurity Best Practices",
        author: "Dr. Robert Martinez",
        isbn: "978-0123456799",
        category: "reference",
        callNumber: "CS 004.6 M37 2024",
        status: "checked-out",
        dueDate: "2024-02-22",
        addedDate: "2024-01-22",
        location: "Main Library - Reference"
    },
    {
        id: 12,
        title: "Mobile App Development",
        author: "Dr. Susan Clark",
        isbn: "978-0123456800",
        category: "textbook",
        callNumber: "CS 004.5 C53 2024",
        status: "maintenance",
        dueDate: null,
        addedDate: "2024-01-23",
        location: "Main Library - CS Section"
    },
    {
        id: 13,
        title: "Cloud Computing Architecture",
        author: "Dr. James Wilson",
        isbn: "978-0123456801",
        category: "reference",
        callNumber: "CS 004.8 W55 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-24",
        location: "Main Library - Reference"
    },
    {
        id: 14,
        title: "Blockchain Technology",
        author: "Dr. Patricia Moore",
        isbn: "978-0123456802",
        category: "research",
        callNumber: "CS 006.3 M66 2024",
        status: "checked-out",
        dueDate: "2024-02-28",
        addedDate: "2024-01-25",
        location: "Main Library - Research Section"
    },
    {
        id: 15,
        title: "Software Testing Methodologies",
        author: "Dr. Christopher Lee",
        isbn: "978-0123456803",
        category: "textbook",
        callNumber: "CS 005.3 L44 2024",
        status: "available",
        dueDate: null,
        addedDate: "2024-01-26",
        location: "Main Library - CS Section"
    }
];

// Sample course data with book references
const sampleCourses = [
    {
        courseCode: "CS101",
        courseTitle: "Introduction to Computer Science",
        yearLevel: "1st",
        programs: [
            { code: "BSCS", name: "Bachelor of Science in Computer Science", color: "#14A338" },
            { code: "BSIT", name: "Bachelor of Science in Information Technology", color: "#4A7AF2" }
        ],
        bookCount: 4,
        bookReferences: [
            {
                id: 1,
                title: "Computer Science: An Overview",
                author: "Dr. Emily Davis",
                isbn: "978-0123456791",
                publisher: "CS Publications",
                year: "2024",
                edition: "4th Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 001.1 D38 2024"
            },
            {
                id: 2,
                title: "Programming Fundamentals",
                author: "Prof. David Wilson",
                isbn: "978-0123456792",
                publisher: "Code Press",
                year: "2023",
                edition: "1st Edition",
                availability: "reserved",
                location: "Main Library - CS Section",
                callNumber: "CS 001.3 W55 2023"
            },
            {
                id: 3,
                title: "Introduction to Algorithms",
                author: "Dr. Thomas Cormen",
                isbn: "978-0123456795",
                publisher: "MIT Press",
                year: "2022",
                edition: "4th Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 C67 2022"
            },
            {
                id: 4,
                title: "Data Structures and Algorithms",
                author: "Prof. Mark Allen",
                isbn: "978-0123456796",
                publisher: "Algorithm Press",
                year: "2023",
                edition: "3rd Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.7 A44 2023"
            }
        ]
    },
    {
        courseCode: "CS102",
        courseTitle: "Computer Programming 1 (C++)",
        yearLevel: "1st",
        programs: [
            { code: "BSCS", name: "Bachelor of Science in Computer Science", color: "#14A338" }
        ],
        bookCount: 4,
        bookReferences: [
            {
                id: 5,
                title: "C++ Programming Guide",
                author: "Dr. Mike Johnson",
                isbn: "978-0123456797",
                publisher: "Programming Press",
                year: "2023",
                edition: "2nd Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 J64 2023"
            },
            {
                id: 6,
                title: "Object-Oriented Programming in C++",
                author: "Prof. Lisa Chen",
                isbn: "978-0123456798",
                publisher: "Code Masters",
                year: "2022",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 C44 2022"
            },
            {
                id: 7,
                title: "C++ Data Structures and Algorithms",
                author: "Dr. David Wilson",
                isbn: "978-0123456799",
                publisher: "Algorithm Books",
                year: "2023",
                edition: "1st Edition",
                availability: "checked-out",
                location: "Main Library - CS Section",
                callNumber: "CS 005.7 W55 2023"
            },
            {
                id: 8,
                title: "Modern C++ Programming",
                author: "Prof. Emily Davis",
                isbn: "978-0123456800",
                publisher: "Modern Tech",
                year: "2023",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 D38 2023"
            }
        ]
    },
    {
        courseCode: "IT101",
        courseTitle: "Introduction to Information Technology",
        yearLevel: "1st",
        programs: [
            { code: "BSIT", name: "Bachelor of Science in Information Technology", color: "#4A7AF2" },
            { code: "BSIS", name: "Bachelor of Science in Information Systems", color: "#FF9800" }
        ],
        bookCount: 2,
        bookReferences: [
            {
                id: 9,
                title: "Introduction to Information Technology",
                author: "Dr. Sarah Johnson",
                isbn: "978-0123456789",
                publisher: "Tech Press",
                year: "2023",
                edition: "3rd Edition",
                availability: "available",
                location: "Main Library - IT Section",
                callNumber: "IT 001.1 J64 2023"
            },
            {
                id: 10,
                title: "Fundamentals of Computing",
                author: "Prof. Michael Brown",
                isbn: "978-0123456790",
                publisher: "Academic Press",
                year: "2022",
                edition: "2nd Edition",
                availability: "checked-out",
                location: "Main Library - IT Section",
                callNumber: "IT 001.2 B76 2022"
            }
        ]
    },
    {
        courseCode: "IT201",
        courseTitle: "Web Development Fundamentals",
        yearLevel: "2nd",
        programs: [
            { code: "BSIT", name: "Bachelor of Science in Information Technology", color: "#4A7AF2" }
        ],
        bookCount: 3,
        bookReferences: [
            {
                id: 11,
                title: "Web Development Fundamentals",
                author: "Dr. Jennifer Webber",
                isbn: "978-0123456812",
                publisher: "Web Press",
                year: "2023",
                edition: "2nd Edition",
                availability: "available",
                location: "Main Library - IT Section",
                callNumber: "IT 006.7 W43 2023"
            },
            {
                id: 12,
                title: "HTML5 and CSS3 Complete Guide",
                author: "Prof. Mark Styles",
                isbn: "978-0123456813",
                publisher: "Frontend Books",
                year: "2023",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - IT Section",
                callNumber: "IT 006.7 S85 2023"
            },
            {
                id: 13,
                title: "JavaScript Programming",
                author: "Dr. Sarah Script",
                isbn: "978-0123456814",
                publisher: "Script Press",
                year: "2022",
                edition: "3rd Edition",
                availability: "checked-out",
                location: "Main Library - IT Section",
                callNumber: "IT 006.7 S47 2022"
            }
        ]
    },
    {
        courseCode: "CS201",
        courseTitle: "Data Structures and Algorithms",
        yearLevel: "2nd",
        programs: [
            { code: "BSCS", name: "Bachelor of Science in Computer Science", color: "#14A338" }
        ],
        bookCount: 3,
        bookReferences: [
            {
                id: 14,
                title: "Data Structures and Algorithms",
                author: "Dr. Algorithm Expert",
                isbn: "978-0123456822",
                publisher: "Algorithm Press",
                year: "2023",
                edition: "2nd Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.7 A44 2023"
            },
            {
                id: 15,
                title: "Algorithm Design and Analysis",
                author: "Prof. Design Master",
                isbn: "978-0123456823",
                publisher: "Design Press",
                year: "2022",
                edition: "1st Edition",
                availability: "checked-out",
                location: "Main Library - CS Section",
                callNumber: "CS 005.7 D47 2022"
            },
            {
                id: 16,
                title: "Advanced Programming Techniques",
                author: "Dr. Advanced Coder",
                isbn: "978-0123456824",
                publisher: "Advanced Press",
                year: "2023",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 A38 2023"
            }
        ]
    },
    {
        courseCode: "IT301",
        courseTitle: "Database Management Systems",
        yearLevel: "3rd",
        programs: [
            { code: "BSIT", name: "Bachelor of Science in Information Technology", color: "#4A7AF2" }
        ],
        bookCount: 3,
        bookReferences: [
            {
                id: 17,
                title: "Advanced Database Management",
                author: "Dr. Alex Database",
                isbn: "978-0123456817",
                publisher: "Database Solutions",
                year: "2023",
                edition: "2nd Edition",
                availability: "available",
                location: "Main Library - IT Section",
                callNumber: "IT 005.7 D38 2023"
            },
            {
                id: 18,
                title: "Database Administration",
                author: "Prof. Maria Admin",
                isbn: "978-0123456818",
                publisher: "Admin Press",
                year: "2023",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - IT Section",
                callNumber: "IT 005.7 A35 2023"
            },
            {
                id: 19,
                title: "Data Warehousing and Mining",
                author: "Dr. Kevin Data",
                isbn: "978-0123456819",
                publisher: "Data Press",
                year: "2022",
                edition: "1st Edition",
                availability: "reserved",
                location: "Main Library - IT Section",
                callNumber: "IT 005.7 D38 2022"
            }
        ]
    },
    {
        courseCode: "CS401",
        courseTitle: "Software Engineering",
        yearLevel: "4th",
        programs: [
            { code: "BSCS", name: "Bachelor of Science in Computer Science", color: "#14A338" }
        ],
        bookCount: 4,
        bookReferences: [
            {
                id: 20,
                title: "Modern Software Engineering",
                author: "Dr. Modern Dev",
                isbn: "978-0123456820",
                publisher: "Modern Press",
                year: "2024",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 M63 2024"
            },
            {
                id: 21,
                title: "Legacy Software Systems",
                author: "Prof. Legacy Expert",
                isbn: "978-0123456821",
                publisher: "Legacy Press",
                year: "2018",
                edition: "3rd Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 L44 2018"
            },
            {
                id: 22,
                title: "Outdated Programming Methods",
                author: "Dr. Old School",
                isbn: "978-0123456822",
                publisher: "Old Press",
                year: "2017",
                edition: "2nd Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 O53 2017"
            },
            {
                id: 23,
                title: "Ancient Computer Science",
                author: "Prof. Ancient",
                isbn: "978-0123456823",
                publisher: "Ancient Press",
                year: "2016",
                edition: "1st Edition",
                availability: "available",
                location: "Main Library - CS Section",
                callNumber: "CS 005.1 A56 2016"
            }
        ]
    }
];

// Initialize Library Management
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Library Management page...');
    
    // Load data from database
    loadLibraryData();
    
    // Add specific event listeners for pagination to prevent interference
    document.addEventListener('click', function(e) {
        // Handle pagination button clicks specifically
        if (e.target.classList.contains('page-number') || e.target.classList.contains('pagination-btn')) {
            console.log('Pagination button clicked:', e.target);
            // Don't stop propagation, let the onclick handler work
            // e.stopPropagation();
        }
    }, true); // Use capture phase to ensure our handler runs first
});

// Function to fetch library data from database
async function loadLibraryData() {
    try {
        console.log('Loading library data from database...');
        
        // Build query parameters
        const params = new URLSearchParams({
            yearLevel: currentFilters.yearLevel,
            academicTerm: currentFilters.academicTerm,
            department: currentFilters.department,
            programs: JSON.stringify(currentFilters.program),
            page: currentLibraryPage + 1,
            limit: libraryItemsPerPage
        });
        
        const response = await fetch(`get_library_data.php?${params}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            allCourses = result.data;
            filteredCourses = result.data;
            
            // Calculate pagination based on merged courses, not individual records
            const groupedCourses = groupCoursesByInfo(allCourses);
            const mergedCourses = Object.values(groupedCourses);
            totalRecords = mergedCourses.length;
            totalPages = Math.ceil(totalRecords / libraryItemsPerPage);
            
            // Store merged courses globally for display function
            window.currentMergedCourses = mergedCourses;
            
            // Debug: Log the first course to see what data we're getting
            if (allCourses.length > 0) {
                console.log('First course data:', allCourses[0]);
                console.log('Program color from API:', allCourses[0].program_color);
                console.log('Total database courses:', allCourses.length);
                console.log('Total merged courses:', mergedCourses.length);
                console.log('Items per page:', libraryItemsPerPage);
                console.log('Total pages:', totalPages);
                console.log('Current page:', currentLibraryPage);
            }
            
            displayCourses();
            updateLibraryPagination();
        } else {
            console.error('Failed to load library data:', result.message);
            alert('Failed to load library data: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading library data:', error);
        alert('Error loading library data: ' + error.message);
    }
}

// Add Course Modal Function - LIBRARIAN
function openAddCourseModal() {
    console.log('Opening librarian add course modal...');
    
    try {
        // Show modal
        const modal = document.getElementById('librarianAddCourseModal');
        if (modal) {
            modal.style.display = 'flex';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            
            // Reset form
            const form = document.getElementById('librarianAddCourseForm');
            if (form) {
                form.reset();
            }
            
            // Reset program selection
            const selectedProgramsInput = document.getElementById('librarianSelectedProgramsInput');
            const programButtonText = document.getElementById('librarianProgramButtonText');
            if (selectedProgramsInput) selectedProgramsInput.value = '';
            if (programButtonText) programButtonText.textContent = 'Select Program(s) - No Program Selected';
            
            // Setup event listeners for form fields
            setupLibrarianFormEventListeners();
            
            // Run validation check
            setTimeout(() => {
                checkLibrarianCourseFormValidity();
            }, 100);
            
            console.log('Librarian add course modal opened successfully');
        } else {
            console.error('Librarian add course modal element not found');
        }
    } catch (error) {
        console.error('Error opening librarian add course modal:', error);
    }
}

function closeLibrarianAddCourseModal() {
    try {
        const modal = document.getElementById('librarianAddCourseModal');
        if (modal) {
            modal.style.display = 'none';
            
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.height = '';
            
            console.log('Librarian add course modal closed successfully');
        }
    } catch (error) {
        console.error('Error closing librarian add course modal:', error);
    }
}

// Librarian course form validation
function checkLibrarianCourseFormValidity() {
    const courseCode = document.getElementById('librarianCourseCode')?.value?.trim() || '';
    const courseName = document.getElementById('librarianCourseName')?.value?.trim() || '';
    const schoolTerm = document.getElementById('librarianSchoolTerm')?.value || '';
    const location = document.getElementById('librarianLocation')?.value || '';
    const schoolYear = document.getElementById('librarianSchoolYear')?.value || '';
    const yearLevel = document.getElementById('librarianYearLevel')?.value || '';
    const selectedPrograms = document.getElementById('librarianSelectedProgramsInput')?.value?.trim() || '';
    
    const createBtn = document.getElementById('librarianCreateCourseBtn');
    
    const isValid = courseCode && courseName && schoolTerm && location && schoolYear && yearLevel && selectedPrograms;
    
    if (createBtn) {
        createBtn.disabled = !isValid;
        if (isValid) {
            createBtn.style.backgroundColor = '#4CAF50';
            createBtn.style.cursor = 'pointer';
            createBtn.style.opacity = '1';
        } else {
            createBtn.style.backgroundColor = '#6c757d';
            createBtn.style.cursor = 'not-allowed';
            createBtn.style.opacity = '0.6';
        }
    }
    
    return isValid;
}

function setupLibrarianFormEventListeners() {
    const formFields = ['librarianCourseCode', 'librarianCourseName', 'librarianSchoolTerm', 'librarianLocation', 'librarianSchoolYear', 'librarianYearLevel'];
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.removeEventListener('input', checkLibrarianCourseFormValidity);
            field.removeEventListener('change', checkLibrarianCourseFormValidity);
            field.addEventListener('input', checkLibrarianCourseFormValidity);
            field.addEventListener('change', checkLibrarianCourseFormValidity);
        }
    });
}

// Program selection functions
function openLibrarianProgramSelectModal() {
    try {
        const modal = document.getElementById('librarianProgramSelectModal');
        if (modal) {
            modal.style.display = 'flex';
            
            // Clear search input
            const searchInput = document.getElementById('librarianProgramSearch');
            if (searchInput) {
                searchInput.value = '';
                filterLibrarianPrograms('');
            }
            
            // Focus on search input
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 100);
            
            console.log('Librarian program selection modal opened');
        } else {
            console.error('Librarian program selection modal not found');
        }
    } catch (error) {
        console.error('Error opening librarian program modal:', error);
    }
}

// Filter programs based on search input
function filterLibrarianPrograms(searchTerm) {
    const searchLower = searchTerm.toLowerCase().trim();
    const programItems = document.querySelectorAll('#librarianProgramsList .program-item');
    let visibleCount = 0;
    
    programItems.forEach(item => {
        const programName = item.querySelector('.program-name');
        if (programName) {
            const text = programName.textContent.toLowerCase();
            if (text.includes(searchLower)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        }
    });
    
    // Show message if no programs match
    const listContainer = document.getElementById('librarianProgramsList');
    let noResultsMsg = listContainer.querySelector('.no-results-message');
    
    if (visibleCount === 0 && searchLower !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message';
            noResultsMsg.style.padding = '20px';
            noResultsMsg.style.textAlign = 'center';
            noResultsMsg.style.color = '#666';
            listContainer.appendChild(noResultsMsg);
        }
        noResultsMsg.textContent = 'No programs found matching your search.';
        noResultsMsg.style.display = 'block';
    } else if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
}

function closeLibrarianProgramSelectModal() {
    const modal = document.getElementById('librarianProgramSelectModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmLibrarianProgramSelection() {
    const selectedPrograms = [];
    const selectedNames = [];
    
    const checkboxes = document.querySelectorAll('#librarianProgramSelectModal input[name="programs[]"]:checked');
    checkboxes.forEach(checkbox => {
        selectedPrograms.push(checkbox.value);
        selectedNames.push(checkbox.dataset.programName);
    });
    
    if (selectedPrograms.length === 0) {
        alert('Please select at least one program.');
        return;
    }
    
    // Update hidden input
    document.getElementById('librarianSelectedProgramsInput').value = selectedPrograms.join(',');
    
    // Update button text
    if (selectedNames.length === 1) {
        document.getElementById('librarianProgramButtonText').textContent = selectedNames[0];
    } else {
        document.getElementById('librarianProgramButtonText').textContent = `${selectedNames.length} Programs Selected`;
    }
    
    // Close modal
    closeLibrarianProgramSelectModal();
    
    // Trigger form validation after program selection
    setTimeout(() => {
        checkLibrarianCourseFormValidity();
    }, 100);
}

// Update confirm button based on selections
function updateLibrarianConfirmButton() {
    const checkboxes = document.querySelectorAll('#librarianProgramSelectModal input[name="programs[]"]');
    const confirmBtn = document.getElementById('librarianConfirmProgramBtn');
    
    if (confirmBtn) {
        const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
        confirmBtn.disabled = !hasSelection;
        if (hasSelection) {
            confirmBtn.style.backgroundColor = '#4CAF50';
            confirmBtn.style.cursor = 'pointer';
        } else {
            confirmBtn.style.backgroundColor = '#6c757d';
            confirmBtn.style.cursor = 'not-allowed';
        }
    }
}

// Success modal functions
function showLibrarianCourseSuccessModal(courseData) {
    const modal = document.getElementById('librarianCourseSuccessModal');
    if (modal) {
        document.getElementById('librarianSuccessCourseCode').textContent = courseData.course_code || '';
        document.getElementById('librarianSuccessCourseName').textContent = courseData.course_name || '';
        document.getElementById('librarianSuccessProgram').textContent = courseData.program_name || 'Selected Program';
        modal.style.display = 'flex';
    }
}

function closeLibrarianCourseSuccessModal() {
    const modal = document.getElementById('librarianCourseSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        resetLibrarianCourseForm();
        closeLibrarianAddCourseModal();
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
}

function resetLibrarianCourseForm() {
    try {
        const form = document.getElementById('librarianAddCourseForm');
        if (form) {
            form.reset();
        }
        document.getElementById('librarianCourseCode').value = '';
        document.getElementById('librarianCourseName').value = '';
        document.getElementById('librarianUnits').value = '';
        document.getElementById('librarianYearLevel').value = '';
        document.getElementById('librarianSelectedProgramsInput').value = '';
        document.getElementById('librarianProgramButtonText').textContent = 'Select Program(s) - No Program Selected';
        document.getElementById('librarianSchoolYear').value = '';
        document.getElementById('librarianSchoolTerm').value = '';
        const locationField = document.getElementById('librarianLocation');
        if (locationField) {
            locationField.value = '';
        }
        
        const createBtn = document.getElementById('librarianCreateCourseBtn');
        if (createBtn) {
            createBtn.disabled = true;
            createBtn.style.backgroundColor = '#6c757d';
        }
        console.log('Librarian course form reset successfully');
    } catch (error) {
        console.error('Error resetting librarian form:', error);
    }
}

// Error modal functions
function showLibrarianCourseErrorModal(errorMessage, errorDetails = null) {
    const modal = document.getElementById('librarianCourseErrorModal');
    if (modal) {
        document.getElementById('librarianErrorMessage').textContent = errorMessage;
        const errorDetailsDiv = document.getElementById('librarianErrorDetails');
        const errorDetailsText = document.getElementById('librarianErrorDetailsText');
        if (errorDetails && errorDetailsDiv && errorDetailsText) {
            errorDetailsText.textContent = errorDetails;
            errorDetailsDiv.style.display = 'block';
        } else {
            errorDetailsDiv.style.display = 'none';
        }
        modal.style.display = 'flex';
    }
}

function closeLibrarianCourseErrorModal() {
    const modal = document.getElementById('librarianCourseErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function retryLibrarianCourseCreation() {
    closeLibrarianCourseErrorModal();
    const courseCodeField = document.getElementById('librarianCourseCode');
    if (courseCodeField) {
        courseCodeField.focus();
    }
}

// Create course via API
function createLibrarianCourseWithDatabase(courseData) {
    console.log('Creating librarian course in database...');
    
    const createBtn = document.getElementById('librarianCreateCourseBtn');
    if (createBtn) {
        createBtn.disabled = true;
        createBtn.textContent = 'CREATING...';
        createBtn.style.backgroundColor = '#6c757d';
    }
    
    fetch('api/process_librarian_add_course.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(courseData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Librarian course creation response:', data);
        
        if (data.success) {
            console.log('Librarian course creation successful');
            showLibrarianCourseSuccessModal({
                course_code: courseData.course_code,
                course_name: courseData.course_name,
                program_name: data.program_name || 'Selected Program'
            });
        } else {
            console.log('Librarian course creation failed:', data.message);
            showLibrarianCourseErrorModal(
                data.message || 'Failed to create course. Please try again.',
                data.error_details || null
            );
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        showLibrarianCourseErrorModal('Network error occurred while creating the course.', error.toString());
    })
    .finally(() => {
        if (createBtn) {
            createBtn.disabled = false;
            createBtn.textContent = 'CREATE';
            createBtn.style.backgroundColor = '#4CAF50';
        }
    });
}

// Handle create button click
function handleLibrarianCreateButtonClick(event) {
    const createBtn = document.getElementById('librarianCreateCourseBtn');
    if (createBtn && createBtn.disabled) {
        event.preventDefault();
        event.stopPropagation();
        createBtn.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            createBtn.style.animation = '';
        }, 500);
    }
}

// Event listeners for program checkboxes
document.addEventListener('change', function(e) {
    if (e.target.matches('#librarianProgramSelectModal input[name="programs[]"]')) {
        updateLibrarianConfirmButton();
    }
});

// Add search input event listener for program filtering
document.addEventListener('input', function(e) {
    if (e.target.id === 'librarianProgramSearch') {
        filterLibrarianPrograms(e.target.value);
    }
});

// Display Courses Function
function displayCourses() {
    const tbody = document.getElementById('libraryTableBody');
    
    if (!tbody) {
        console.error('Table body not found');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!allCourses || allCourses.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                No courses found
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    // Get merged courses (either from filter or from all courses)
    let mergedCourses;
    if (typeof window.currentMergedCourses !== 'undefined') {
        // Use merged courses from filter
        mergedCourses = window.currentMergedCourses;
    } else {
        // Group courses by course code, title, year level, and semester
        const groupedCourses = groupCoursesByInfo(allCourses);
        mergedCourses = Object.values(groupedCourses);
    }
    
    // Apply pagination to merged courses
    const startIndex = currentLibraryPage * libraryItemsPerPage;
    const endIndex = startIndex + libraryItemsPerPage;
    const paginatedCourses = mergedCourses.slice(startIndex, endIndex);
    
    // Debug: Log pagination info
    console.log('Display Courses Debug:');
    console.log('- Total merged courses:', mergedCourses.length);
    console.log('- Current page:', currentLibraryPage);
    console.log('- Items per page:', libraryItemsPerPage);
    console.log('- Start index:', startIndex);
    console.log('- End index:', endIndex);
    console.log('- Courses to display:', paginatedCourses.length);
    
    // Display paginated merged courses
    paginatedCourses.forEach(courseGroup => {
        const row = document.createElement('tr');
        row.className = 'course-group-row';
        row.onclick = () => {
            window.location.href = `content.php?page=course-details&course_code=${encodeURIComponent(courseGroup.course_code)}`;
        };
        
        // Format year level with ordinal numbers
        let yearLevel = 'N/A';
        if (courseGroup.year_level) {
            const year = parseInt(courseGroup.year_level);
            if (year === 1) yearLevel = '1st Year';
            else if (year === 2) yearLevel = '2nd Year';
            else if (year === 3) yearLevel = '3rd Year';
            else if (year === 4) yearLevel = '4th Year';
            else yearLevel = `${year}th Year`;
        }
        
        // Format term and academic year (matching All Courses format)
        const term = courseGroup.term || 'N/A';
        const academicYearLabel = courseGroup.courses[0]?.academic_year_label || 'N/A';
        
        // Format term consistently (1st -> 1st Semester, etc.)
        let formattedTerm = term;
        if (term === '1st') formattedTerm = '1st Semester';
        else if (term === '2nd') formattedTerm = '2nd Semester';
        else if (term === 'summer') formattedTerm = 'Summer';
        
        // Create HTML structure matching All Courses: two-line display
        const termAndYearHTML = `
            <div style="font-weight: 600; color: #1976d2; margin-bottom: 2px; font-size: 13px;">${formattedTerm}</div>
            <div style="font-size: 11px; color: #6c757d; font-weight: 500;">${academicYearLabel}</div>
        `;
        
        // Format programs (multiple programs merged)
        const programHTML = formatMergedPrograms(courseGroup.programs, courseGroup.courses.length);
        
        // Calculate compliant book count using backend-provided field per course, summed in group
        // Falls back to 0 if not present
        let totalBooks = courseGroup.courses.reduce((sum, c) => {
            const n = typeof c.compliant_book_count === 'number' ? c.compliant_book_count : 0;
            return sum + n;
        }, 0);
        
        // Quality assurance format: X / 5 (Red if < 5, Green if >= 5) - matching department dean style
        const minRequiredBooks = 5;
        const isCompliant = totalBooks >= minRequiredBooks;
        const bookCountDisplay = `${totalBooks} / ${minRequiredBooks}`;
        const bookCountColor = isCompliant ? '#2e7d32' : '#FF4C4C'; // Green if compliant, Red if not
        const bookCountBg = isCompliant ? '#e8f5e8' : '#ffeaea'; // Light green or light red background
        
        // Helper function to escape HTML but preserve the actual course code value
        const escapeHtml = (text) => {
            if (!text) return 'N/A';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        row.innerHTML = `
            <td class="course-code-cell">${escapeHtml(courseGroup.course_code)}</td>
            <td class="course-title-cell">${escapeHtml(courseGroup.course_title)}</td>
            <td class="units-cell">${courseGroup.courses[0]?.units !== undefined && courseGroup.courses[0]?.units !== null ? courseGroup.courses[0].units : 0}</td>
            <td class="programs-cell">${programHTML}</td>
            <td class="term-year-cell">${termAndYearHTML}</td>
            <td class="year-level-cell">${yearLevel}</td>
            <td class="book-count-cell" style="text-align: center;">
                <span style="background: ${bookCountBg}; color: ${bookCountColor}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">
                    ${bookCountDisplay}
                </span>
            </td>
            <td>
                <button class="view-books-btn" onclick="event.stopPropagation(); window.location.href='content.php?page=course-details&course_code=${encodeURIComponent(courseGroup.course_code)}'">View Books</button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Group courses by course information (code, title, year level, semester)
function groupCoursesByInfo(courses) {
    const groups = {};
    
    courses.forEach(course => {
        // Create a unique key based on course info (excluding program)
        const key = `${course.course_code}_${course.course_title}_${course.year_level}_${course.term}`;
        
        if (!groups[key]) {
            groups[key] = {
                course_code: course.course_code,
                course_title: course.course_title,
                year_level: course.year_level,
                term: course.term,
                created_at: course.created_at || null, // Store created_at for sorting
                programs: [],
                courses: []
            };
        } else {
            // Update created_at to the newest one in the group
            if (course.created_at && groups[key].created_at) {
                const courseDate = new Date(course.created_at);
                const groupDate = new Date(groups[key].created_at);
                if (courseDate > groupDate) {
                    groups[key].created_at = course.created_at;
                }
            } else if (course.created_at && !groups[key].created_at) {
                groups[key].created_at = course.created_at;
            }
        }
        
        // Add program with color to the group if not already present
        const programInfo = {
            name: course.program_code || course.program || 'N/A',
            color: course.program_color || '#1976d2'
        };
        
        // Debug: Log the program color and full course data
        console.log('Course data:', course);
        console.log('Program:', programInfo.name, 'Color:', programInfo.color);
        
        // Check if program is already in the group
        const existingProgram = groups[key].programs.find(p => p.name === programInfo.name);
        if (!existingProgram) {
            groups[key].programs.push(programInfo);
        }
        
        // Add course to the group
        groups[key].courses.push(course);
    });
    
    // Convert groups object to array and sort by created_at DESC (newest first)
    const groupsArray = Object.values(groups);
    groupsArray.sort((a, b) => {
        if (!a.created_at && !b.created_at) return 0;
        if (!a.created_at) return 1; // Put items without created_at at the end
        if (!b.created_at) return -1;
        return new Date(b.created_at) - new Date(a.created_at); // DESC order
    });
    
    // Convert back to object to maintain compatibility
    const sortedGroups = {};
    groupsArray.forEach(group => {
        const key = `${group.course_code}_${group.course_title}_${group.year_level}_${group.term}`;
        sortedGroups[key] = group;
    });
    
    return sortedGroups;
}

// Format merged programs with + indicator
function formatMergedPrograms(programs, courseCount) {
    if (!programs || programs.length === 0) {
        return '<span class="program-badge" style="background-color: #666;">N/A</span>';
    }
    
    if (programs.length === 1) {
        const program = programs[0];
        const color = program.color || '#1976d2';
        return `<span class="program-badge" style="background-color: ${color};">${program.name}</span>`;
    }
    
    // Show first program + count of additional programs
    const additionalCount = programs.length - 1;
    const firstProgram = programs[0];
    const firstProgramColor = firstProgram.color || '#1976d2';
    const otherPrograms = programs.slice(1).map(p => p.name).join(', ');
    return `
        <span class="program-badge" style="background-color: ${firstProgramColor};">${firstProgram.name}</span>
        <span class="program-badge additional-programs" 
              style="background-color: #6c757d;" 
              title="${otherPrograms}">+${additionalCount}</span>
    `;
}

function updateLibraryPagination() {
    const startItem = currentLibraryPage * libraryItemsPerPage + 1;
    const endItem = Math.min((currentLibraryPage + 1) * libraryItemsPerPage, totalRecords);
    
    // Update pagination info
    const paginationInfo = document.getElementById('paginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${totalRecords} courses`;
    }
    
    // Update pagination buttons
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.disabled = currentLibraryPage === 0;
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentLibraryPage >= totalPages - 1;
    }
    
    // Update page numbers
    const pageNumbers = document.getElementById('pageNumbers');
    if (pageNumbers && totalPages > 1) {
        let pageNumbersHTML = '';
        const maxVisiblePages = Math.min(5, totalPages); // Don't show more buttons than total pages
        let startPage = Math.max(0, currentLibraryPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages - 1, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(0, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentLibraryPage;
            pageNumbersHTML += `<button class="page-number ${isActive ? 'active' : ''}" data-page="${i}" onclick="goToPage(${i})">${i + 1}</button>`;
        }
        
        pageNumbers.innerHTML = pageNumbersHTML;
        
        // Add fallback event listeners in case onclick doesn't work
        pageNumbers.querySelectorAll('.page-number').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const page = parseInt(this.getAttribute('data-page'));
                console.log('Fallback click handler triggered for page:', page);
                goToPage(page);
            });
            
            // Also add a direct onclick handler as backup
            button.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const page = parseInt(this.getAttribute('data-page'));
                console.log('Direct onclick handler triggered for page:', page);
                goToPage(page);
                return false;
            };
        });
    } else if (pageNumbers) {
        // Hide pagination if only 1 page or no pages
        pageNumbers.innerHTML = '';
    }
}

function changePage(direction) {
    const newPage = currentLibraryPage + direction;
    
    if (newPage >= 0 && newPage < totalPages) {
        currentLibraryPage = newPage;
        loadLibraryData();
    }
}

function goToPage(page) {
    console.log(`goToPage called with page: ${page}, currentLibraryPage: ${currentLibraryPage}, totalPages: ${totalPages}`);
    if (page >= 0 && page < totalPages) {
        currentLibraryPage = page;
        console.log(`Setting currentLibraryPage to: ${currentLibraryPage}`);
        loadLibraryData();
    } else {
        console.log(`Page ${page} is out of range (0-${totalPages-1})`);
    }
}

function filterLibraryItems() {
    const searchTerm = document.getElementById('librarySearch').value.toLowerCase();
    
    // Apply current filters first
    applyCourseFilters();
    
    // Then apply search filter
    if (searchTerm) {
        filteredCourses = filteredCourses.filter(course => {
            const matchesSearch = course.courseCode.toLowerCase().includes(searchTerm) ||
                                course.courseTitle.toLowerCase().includes(searchTerm) ||
                                course.programs.some(program => program.code.toLowerCase().includes(searchTerm));
            
            return matchesSearch;
        });
    }
    
    displayCourses();
    updateLibraryPagination();
}

function importLibraryData() {
    console.log('Opening import data modal...');
    if (typeof openImportDataModal === 'function') {
        openImportDataModal();
    } else {
        alert('Import feature is loading. Please try again in a moment.');
    }
}

function exportLibraryData() {
    console.log('Exporting library data...');
    alert('Library data export would start here. This would generate a CSV or Excel file with all library items.');
}

function openFilterModal() {
    console.log('Opening filter modal...');
    document.getElementById('libraryFilterModal').style.display = 'flex';
    
    // Use universal scroll prevention
    if (typeof preventBackgroundScroll === 'function') {
        preventBackgroundScroll();
    } else {
        document.body.style.overflow = 'hidden';
    }
    
    // Also add class to body as backup
    document.body.classList.add('modal-open');
    
    // Set current filter values
    document.getElementById('filterYearLevel').value = currentFilters.yearLevel;
    document.getElementById('filterAcademicTerm').value = currentFilters.academicTerm;
    document.getElementById('filterDepartment').value = currentFilters.department;
    
    // Update programs based on current department
    updateProgramsFilter();
}

function closeFilterModal() {
    console.log('Closing filter modal...');
    document.getElementById('libraryFilterModal').style.display = 'none';
    
    // Use universal scroll restoration
    if (typeof restoreBackgroundScroll === 'function') {
        restoreBackgroundScroll();
    } else {
        document.body.style.overflow = '';
    }
    
    // Remove class from body
    document.body.classList.remove('modal-open');
}

function updateProgramsFilter() {
    const departmentSelect = document.getElementById('filterDepartment');
    const programList = document.getElementById('programList');
    const programFilterText = document.getElementById('programFilterText');
    const selectedDepartment = departmentSelect.value;
    
    // Clear existing content
    programList.innerHTML = '';
    
    if (selectedDepartment === 'all') {
        // Show all programs from all departments when "All Departments" is selected
        const allPrograms = [];
        Object.values(departmentPrograms).forEach(deptPrograms => {
            allPrograms.push(...deptPrograms);
        });
        
        if (allPrograms.length === 0) {
            const messageDiv = document.createElement('div');
            messageDiv.id = 'programMessage';
            messageDiv.className = 'program-message';
            messageDiv.textContent = 'No programs available';
            programList.appendChild(messageDiv);
            programFilterText.textContent = 'Programs: Any';
        } else {
            // Add "All Programs" checkbox
            const allProgramItem = document.createElement('div');
            allProgramItem.className = 'program-item';
            allProgramItem.innerHTML = `
                <input type="checkbox" value="all" checked onchange="handleAllProgramsChange()">
                <span>All Programs</span>
            `;
            programList.appendChild(allProgramItem);
            
            // Add all programs from all departments
            allPrograms.forEach(program => {
                const programItem = document.createElement('div');
                programItem.className = 'program-item';
                programItem.innerHTML = `
                    <input type="checkbox" value="${program.code}" onchange="updateProgramSelection()">
                    <span>${program.name}</span>
                `;
                programList.appendChild(programItem);
            });
            
            updateProgramSelection();
        }
    } else if (departmentPrograms[selectedDepartment]) {
        // Add "All Programs" checkbox
        const allProgramItem = document.createElement('div');
        allProgramItem.className = 'program-item';
        allProgramItem.innerHTML = `
            <input type="checkbox" value="all" checked onchange="handleAllProgramsChange()">
            <span>All Programs</span>
        `;
        programList.appendChild(allProgramItem);
        
        // Add department-specific programs
        departmentPrograms[selectedDepartment].forEach(program => {
            const programItem = document.createElement('div');
            programItem.className = 'program-item';
            programItem.innerHTML = `
                <input type="checkbox" value="${program.code}" onchange="updateProgramSelection()">
                <span>${program.name}</span>
            `;
            programList.appendChild(programItem);
        });
        
        updateProgramSelection();
    }
}

function toggleProgramModal() {
    const modal = document.getElementById('programFilterModal');
    const btn = document.getElementById('programFilterBtn');
    
    if (modal.style.display === 'none' || modal.style.display === '') {
        // Position the modal below the button
        const btnRect = btn.getBoundingClientRect();
        modal.style.position = 'fixed';
        modal.style.top = (btnRect.bottom + 4) + 'px';
        modal.style.left = btnRect.left + 'px';
        modal.style.right = 'auto';
        modal.style.width = '400px';
        modal.style.maxWidth = '90vw';
        
        // Initialize program list when opening
        updateProgramsFilter();
        
        modal.style.display = 'block';
        btn.classList.add('active');
    } else {
        modal.style.display = 'none';
        btn.classList.remove('active');
    }
}

function updateProgramSelection() {
    const programList = document.getElementById('programList');
    const programFilterText = document.getElementById('programFilterText');
    const checkboxes = programList.querySelectorAll('input[type="checkbox"]');
    const allProgramsCheckbox = programList.querySelector('input[value="all"]');
    const specificPrograms = Array.from(checkboxes).filter(cb => cb.checked && cb.value !== 'all');
    
    // Handle "All Programs" logic
    if (allProgramsCheckbox) {
        if (specificPrograms.length > 0) {
            // If any specific program is selected, uncheck "All Programs"
            allProgramsCheckbox.checked = false;
        } else if (specificPrograms.length === 0 && !allProgramsCheckbox.checked) {
            // If nothing is selected, check "All Programs"
            allProgramsCheckbox.checked = true;
        }
    }
    
    // Update button text (only count specific programs, not "All Programs")
    if (allProgramsCheckbox && allProgramsCheckbox.checked) {
        programFilterText.textContent = 'Programs: Any';
    } else if (specificPrograms.length === 0) {
        programFilterText.textContent = 'Programs: Any';
    } else if (specificPrograms.length === 1) {
        const programName = specificPrograms[0].nextElementSibling.textContent;
        programFilterText.textContent = `Programs: ${programName}`;
    } else {
        programFilterText.textContent = `Programs: ${specificPrograms.length} selected`;
    }
    
    // Update visual selection state
    checkboxes.forEach(checkbox => {
        const item = checkbox.closest('.program-item');
        if (checkbox.checked) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
}

function filterPrograms() {
    const searchTerm = document.getElementById('programSearch').value.toLowerCase();
    const programItems = document.querySelectorAll('.program-item');
    
    programItems.forEach(item => {
        const programName = item.querySelector('span').textContent.toLowerCase();
        if (programName.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function handleAllProgramsChange() {
    const allProgramsCheckbox = document.getElementById('programList').querySelector('input[value="all"]');
    const specificCheckboxes = document.getElementById('programList').querySelectorAll('input[type="checkbox"]:not([value="all"])');
    
    if (allProgramsCheckbox.checked) {
        // If "All Programs" is checked, uncheck all specific programs
        specificCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }
    
    updateProgramSelection();
}

function clearProgramSelection() {
    const programList = document.getElementById('programList');
    const checkboxes = programList.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        if (checkbox.value === 'all') {
            checkbox.checked = true;
        } else {
            checkbox.checked = false;
        }
    });
    
    updateProgramSelection();
}

function applyFilters() {
    console.log('Applying filters...');
    
    // Get filter values
    currentFilters.yearLevel = document.getElementById('filterYearLevel').value;
    currentFilters.academicTerm = document.getElementById('filterAcademicTerm').value;
    currentFilters.department = document.getElementById('filterDepartment').value;
    
    // Get selected programs (checkboxes)
    const programList = document.getElementById('programList');
    const checkboxes = programList.querySelectorAll('input[type="checkbox"]:checked');
    const selectedPrograms = checkboxes.length > 0 ? Array.from(checkboxes).map(checkbox => checkbox.value) : ['all'];
    currentFilters.program = selectedPrograms;
    
    console.log('Current filters:', currentFilters);
    
    // Reset to first page and reload data from database
    currentLibraryPage = 0;
    loadLibraryData();
    
    // Close modal
    closeFilterModal();
}

function clearFilters() {
    console.log('Clearing all filters...');
    
    // Reset all filter selects
    document.getElementById('filterYearLevel').value = 'all';
    document.getElementById('filterAcademicTerm').value = 'all';
    document.getElementById('filterDepartment').value = 'all';
    
    // Reset programs to show "Select a department first" message
    const programList = document.getElementById('programList');
    const programFilterText = document.getElementById('programFilterText');
    programList.innerHTML = `
        <div id="programMessage" class="program-message">
            Select a department first
        </div>
    `;
    programFilterText.textContent = 'Programs: Any';
    
    // Reset filter state
    currentFilters = {
        yearLevel: 'all',
        academicTerm: 'all',
        department: 'all',
        program: ['all']
    };
    
    // Reset to first page and reload data
    currentLibraryPage = 0;
    loadLibraryData();
}

function applyCourseFilters() {
    console.log('Applying course filters...');
    
    // Start with all courses
    filteredCourses = [...allCourses];
    
    // Apply year level filter
    if (currentFilters.yearLevel !== 'all') {
        filteredCourses = filteredCourses.filter(course => 
            course.yearLevel === currentFilters.yearLevel
        );
    }
    
    // Apply program filter (multi-selection)
    if (!currentFilters.program.includes('all') && currentFilters.program.length > 0) {
        filteredCourses = filteredCourses.filter(course => 
            course.programs.some(program => currentFilters.program.includes(program.code))
        );
    }
    
    // Apply department filter (if no specific program selected)
    if (currentFilters.department !== 'all' && currentFilters.program.includes('all')) {
        // Filter by department based on program codes
        const departmentProgramCodes = departmentPrograms[currentFilters.department]?.map(p => p.code) || [];
        filteredCourses = filteredCourses.filter(course => 
            course.programs.some(program => departmentProgramCodes.includes(program.code))
        );
    }
    
    // Sort by created_at DESC (newest first), then by year level, term, course code, and program code
    filteredCourses.sort((a, b) => {
        // First sort by created_at DESC (newest to oldest)
        if (a.created_at && b.created_at) {
            const dateComparison = new Date(b.created_at) - new Date(a.created_at);
            if (dateComparison !== 0) return dateComparison;
        } else if (a.created_at && !b.created_at) {
            return -1; // Items with created_at come first
        } else if (!a.created_at && b.created_at) {
            return 1; // Items without created_at come last
        }
        
        // Then sort by year level
        const yearOrder = { '1st': 1, '2nd': 2, '3rd': 3, '4th': 4 };
        const yearComparison = yearOrder[a.yearLevel] - yearOrder[b.yearLevel];
        if (yearComparison !== 0) return yearComparison;
        
        // Then sort by term
        const termOrder = { '1st Semester': 1, '2nd Semester': 2, 'Summer Semester': 3 };
        const termComparison = (termOrder[a.term] || 999) - (termOrder[b.term] || 999);
        if (termComparison !== 0) return termComparison;
        
        // Then sort by course code
        const courseComparison = (a.course_code || '').localeCompare(b.course_code || '');
        if (courseComparison !== 0) return courseComparison;
        
        // Finally sort by program code
        return (a.program_code || '').localeCompare(b.program_code || '');
    });
    
    // Update display
    displayCourses();
    updateLibraryPagination();
    
    console.log(`Filtered to ${filteredCourses.length} courses`);
}

// Librarian form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('librarianAddCourseForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const courseData = {};
            for (let [key, value] of formData.entries()) {
                courseData[key] = value;
            }
            
            console.log('Librarian Course Data:', courseData);
            
            // Create course in database
            createLibrarianCourseWithDatabase(courseData);
        });
        
        // Add global input event listeners for form validation
        document.addEventListener('input', function(e) {
            if (e.target.matches('#librarianAddCourseModal input, #librarianAddCourseModal select')) {
                checkLibrarianCourseFormValidity();
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.matches('#librarianAddCourseModal input, #librarianAddCourseModal select')) {
                checkLibrarianCourseFormValidity();
            }
        });
    }
});
</script>