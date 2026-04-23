<?php
// Determine the dean's department code to load the faculty roster
$deptCode = 'CCS'; // Default fallback

try {
	if (isset($_SESSION['selected_role']['department_code'])) {
		$deptCode = $_SESSION['selected_role']['department_code'];
	}
} catch (Exception $e) {
	// Keep default value if there's an error
}
?>

<style>
/* Faculty Page - DESIGN.md Aligned Styles */
.faculty-load-section {
	margin-top: 20px;
}

/* Section Header Pattern */
.departments-header {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	margin-bottom: 16px;
	width: 100%;
}

.departments-header h3 {
	font-size: 16px;
	font-weight: 800;
	color: #0C4B34;
	margin: 0 0 4px 0;
	font-family: 'TT Interphases', sans-serif;
}

.departments-header p {
	font-size: 12px;
	color: rgba(17, 24, 39, 0.5);
	margin: 0;
	font-weight: 600;
}

/* Card Container with Animation */
.faculty-load-container {
	background: #ffffff;
	border-radius: 18px;
	border: 1px solid rgba(12, 75, 52, 0.14);
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
	padding: 22px 24px;
	transition: all 0.28s cubic-bezier(.4, 0, .2, 1);
	animation: fadeSlideUp 0.45s ease-out both;
}

.faculty-load-container:hover {
	transform: translateY(-3px);
	box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
	border-color: rgba(12, 75, 52, 0.25);
}

/* Search Bar - DESIGN.md Pattern */
.user-search-bar {
	display: flex;
	align-items: center;
	background-color: #FFFFFF;
	height: 44px;
	padding: 0 14px;
	border-radius: 12px;
	border: 1px solid #e0e0e0;
	transition: border-color 0.2s ease;
}

.user-search-bar:focus-within {
	border-color: #0C4B34;
}

.user-search-bar .magnifier-icon {
	width: 18px;
	height: 18px;
	opacity: 0.5;
	flex-shrink: 0;
}

.user-search-bar input {
	border: none;
	outline: none;
	flex: 1;
	font-size: 14px;
	font-family: 'TT Interphases', sans-serif;
	background: transparent;
	padding: 0 10px;
}

.user-search-bar input::placeholder {
	color: rgba(17, 24, 39, 0.4);
}

.clear-search-btn {
	width: 24px;
	height: 24px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(12, 75, 52, 0.08);
	border: none;
	border-radius: 6px;
	color: #0C4B34;
	font-size: 16px;
	cursor: pointer;
	transition: background 0.15s ease;
}

.clear-search-btn:hover {
	background: rgba(12, 75, 52, 0.14);
}

/* Search Button - Primary Style */
.search-button {
	background: #0C4B34;
	color: white;
	border: none;
	padding: 10px 18px;
	border-radius: 10px;
	cursor: pointer;
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.2px;
	transition: all 0.22s cubic-bezier(.4, 0, .2, 1);
	display: flex;
	align-items: center;
	gap: 6px;
	font-family: 'TT Interphases', sans-serif;
}

.search-button:hover {
	background: #0a3a28;
	transform: translateY(-1px);
	box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

.search-button:active {
	transform: translateY(0) scale(0.98);
}

/* Faculty Count Badge */
.faculty-count-badge {
	background: rgba(12, 75, 52, 0.04);
	border: 1px solid rgba(12, 75, 52, 0.08);
	color: rgba(17, 24, 39, 0.6);
	padding: 5px 10px;
	border-radius: 8px;
	font-size: 11px;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: 4px;
}

.faculty-count-badge strong {
	color: #0C4B34;
	font-weight: 800;
	font-size: 12px;
}

/* Table Container - DESIGN.md Pattern */
.faculty-table-container {
	background-color: #ffffff;
	padding: 0;
	border-radius: 18px;
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
	border: 1px solid rgba(12, 75, 52, 0.12);
	overflow: hidden;
	transition: all 0.28s cubic-bezier(.4, 0, .2, 1);
}

.faculty-table-container:hover {
	box-shadow: 0 12px 36px rgba(12, 75, 52, 0.1);
}

.faculty-table-container table {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
}

.faculty-table-container table th {
	text-align: left;
	padding: 14px 16px;
	font-size: 11px;
	font-weight: 700;
	color: rgba(17, 24, 39, 0.45);
	text-transform: uppercase;
	letter-spacing: 0.6px;
	border-bottom: 1px solid rgba(12, 75, 52, 0.08);
	background: rgba(12, 75, 52, 0.02);
}

.faculty-table-container table td {
	padding: 14px 16px;
	font-size: 13px;
	color: #333;
	font-weight: 500;
	border-bottom: 1px solid rgba(12, 75, 52, 0.05);
}

.faculty-table-container table td:first-child {
	font-weight: 700;
	color: #111827;
}

.faculty-table-container table tbody tr {
	transition: background-color 0.15s ease;
	cursor: pointer;
}

.faculty-table-container table tbody tr:hover {
	background-color: rgba(12, 75, 52, 0.03);
}

.faculty-table-container table tbody tr:nth-child(even) {
	background-color: rgba(12, 75, 52, 0.015);
}

.faculty-table-container table tbody tr:last-child td {
	border-bottom: none;
}

/* Stat Pills in Table */
.stat-pill {
	background: rgba(12, 75, 52, 0.04);
	border: 1px solid rgba(12, 75, 52, 0.08);
	color: rgba(17, 24, 39, 0.6);
	padding: 5px 10px;
	border-radius: 8px;
	font-size: 11px;
	font-weight: 600;
}

.stat-pill strong {
	color: #0C4B34;
	font-weight: 800;
	font-size: 12px;
}

/* Manage Load Button - Ghost Style */
.assign-course-btn {
	background: transparent;
	color: #0C4B34;
	border: 1px solid rgba(12, 75, 52, 0.2);
	font-size: 12px;
	font-weight: 700;
	cursor: pointer;
	padding: 8px 14px;
	border-radius: 8px;
	display: inline-flex;
	align-items: center;
	gap: 4px;
	transition: all 0.2s ease;
	font-family: 'TT Interphases', sans-serif;
}

.assign-course-btn:hover {
	background: rgba(12, 75, 52, 0.06);
	border-color: rgba(12, 75, 52, 0.35);
	color: #0a3a28;
}

/* Pagination - DESIGN.md Pattern */
.pagination {
	display: flex;
	justify-content: center;
	gap: 6px;
	margin-top: 20px;
}

.pagination button {
	padding: 8px 14px;
	border: 1px solid rgba(12, 75, 52, 0.16);
	background: #ffffff;
	border-radius: 8px;
	cursor: pointer;
	font-size: 13px;
	font-weight: 600;
	color: rgba(17, 24, 39, 0.7);
	transition: all 0.15s ease;
	font-family: 'TT Interphases', sans-serif;
}

.pagination button:hover {
	background: rgba(12, 75, 52, 0.04);
	border-color: rgba(12, 75, 52, 0.25);
}

.pagination button.active {
	background: #0C4B34;
	color: white;
	border-color: #0C4B34;
}

.pagination button:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

/* Empty State */
.faculty-table-container tbody tr.empty-state td {
	padding: 40px 16px;
	text-align: center;
	color: rgba(17, 24, 39, 0.4);
	font-weight: 600;
	font-size: 13px;
}

/* Entrance Animation */
@keyframes fadeSlideUp {
	from {
		opacity: 0;
		transform: translateY(18px) scale(0.985);
	}
	to {
		opacity: 1;
		transform: translateY(0) scale(1);
	}
}

/* Responsive */
@media (max-width: 768px) {
	.faculty-load-container {
		padding: 16px;
	}
	
	.faculty-table-container {
		overflow-x: auto;
	}
	
	.faculty-table-container table th,
	.faculty-table-container table td {
		padding: 10px 12px;
		font-size: 12px;
	}
}

/* Dark Mode */
html[data-theme="dark"] .faculty-load-container {
	background-color: #1e1e1e !important;
	border-color: #333 !important;
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
}

html[data-theme="dark"] .faculty-load-container:hover {
	border-color: #444 !important;
	box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4) !important;
}

html[data-theme="dark"] .departments-header h3 {
	color: #e0e0e0 !important;
}

html[data-theme="dark"] .user-search-bar {
	background-color: #2d2d2d;
	border-color: #404040;
}

html[data-theme="dark"] .user-search-bar input {
	color: #e0e0e0;
}

html[data-theme="dark"] .search-button {
	background: #0F7A53 !important;
}

html[data-theme="dark"] .faculty-table-container {
	background-color: #1e1e1e !important;
	border-color: #333 !important;
}

html[data-theme="dark"] .faculty-table-container table th {
	color: rgba(224, 224, 224, 0.5);
	background: rgba(255, 255, 255, 0.03);
	border-color: #333;
}

html[data-theme="dark"] .faculty-table-container table td {
	color: #b0b0b0;
	border-color: #333;
}

html[data-theme="dark"] .faculty-table-container table tbody tr:hover {
	background-color: rgba(255, 255, 255, 0.04);
}

html[data-theme="dark"] .pagination button {
	background: #2d2d2d;
	border-color: #404040;
	color: #b0b0b0;
}

html[data-theme="dark"] .stat-pill,
html[data-theme="dark"] .faculty-count-badge {
	background: rgba(255, 255, 255, 0.06);
	border-color: rgba(255, 255, 255, 0.1);
	color: rgba(224, 224, 224, 0.7);
}

html[data-theme="dark"] .stat-pill strong,
html[data-theme="dark"] .faculty-count-badge strong {
	color: #81C784;
}

html[data-theme="dark"] .assign-course-btn {
	color: #81C784;
	border-color: rgba(129, 199, 132, 0.3);
}

html[data-theme="dark"] .assign-course-btn:hover {
	background: rgba(129, 199, 132, 0.08);
	border-color: rgba(129, 199, 132, 0.5);
}
</style>

<div class="faculty-load-section">
	<div class="departments-header">
		<div>
			<h3>Faculty Staff</h3>
			<p>List of active faculty and courses they handle</p>
		</div>
	</div>
	<div class="faculty-load-container">
		<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
			<div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
				<div class="user-search-bar" style="width: 380px;">
					<img src="../src/assets/icons/magnifier-icon.png" alt="Search" class="magnifier-icon">
					<input type="text" placeholder="Search faculty by name, employee no., or email..." id="facultySearchInput" autocomplete="off">
					<button type="button" id="clearSearchBtn" class="clear-search-btn" style="display: none;">&times;</button>
				</div>
				<button class="search-button" onclick="searchFaculty()">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
					Search
				</button>
			</div>
			<div class="faculty-count-badge">
				<strong id="facultyCount">0</strong> faculty members
			</div>
		</div>
		
		<div class="faculty-table-container">
			<table>
				<thead>
					<tr>
						<th>Employee No.</th>
						<th>Name</th>
						<th>Institutional Email</th>
						<th>Mobile</th>
						<th style="text-align: center;">Loads</th>
						<th style="text-align: center;">Total Units</th>
						<th style="text-align: center;">Actions</th>
					</tr>
				</thead>
				<tbody id="facultyStaffTbody">
					<tr><td colspan="7" style="padding:12px; color:#666;">Loading faculty list...</td></tr>
				</tbody>
			</table>
		</div>
		
		<!-- Pagination Controls -->
		<div class="pagination" id="facultyPaginationControls">
			<!-- Pagination buttons will be generated here -->
		</div>
	</div>
</div>

<!-- Faculty Details Modal removed - now using dedicated page -->

<script>
(function() {
	const deptCode = "<?php echo htmlspecialchars($deptCode, ENT_QUOTES); ?>";
	const tbody = document.getElementById('facultyStaffTbody');
	
	// Pagination variables
	let allFacultyData = [];
	let filteredFacultyData = [];
	let currentPage = 1;
	const rowsPerPage = 10;

	function formatName(t) {
		const first = t.first_name || '';
		const last = t.last_name || '';
		const title = t.title ? (t.title + ' ') : '';
		return (title + first + ' ' + last).trim();
	}

		async function loadFacultyStaff() {
		if (!deptCode) {
			tbody.innerHTML = '<tr><td colspan="7" style="padding:12px; color:#b00;">Department not identified for this dean.</td></tr>';
			return;
		}
		try {
			const res = await fetch('../super_admin-mis/api/get_department_teachers.php?dept_code=' + encodeURIComponent(deptCode), { cache: 'no-store' });
			const data = await res.json();
			if (!data.success) {
				tbody.innerHTML = '<tr><td colspan="7" style="padding:12px; color:#b00;">Failed to load faculty list.</td></tr>';
				return;
			}
			if (!data.teachers || data.teachers.length === 0) {
				tbody.innerHTML = '<tr><td colspan="7" style="padding:12px; color:#666;">No faculty staff found for this department.</td></tr>';
				return;
			}
			
			// Store faculty data globally for modal access and search
			window.loadedFacultyData = data.teachers;
			allFacultyData = data.teachers; // Store all data for search
			filteredFacultyData = data.teachers; // Initially show all data
			
			// Display faculty with pagination
			displayFacultyList();
		} catch (e) {
			tbody.innerHTML = '<tr><td colspan="7" style="padding:12px; color:#b00;">Error loading faculty list.</td></tr>';
		}
	}

	function displayFacultyList() {
		if (!filteredFacultyData || filteredFacultyData.length === 0) {
			tbody.innerHTML = '<tr><td colspan="7" style="padding:12px; color:#666;">No faculty staff found.</td></tr>';
			document.getElementById('facultyCount').textContent = '0';
			renderPaginationControls();
			return;
		}
		
		// Calculate pagination
		const totalPages = Math.ceil(filteredFacultyData.length / rowsPerPage);
		const startIndex = (currentPage - 1) * rowsPerPage;
		const endIndex = startIndex + rowsPerPage;
		const currentPageData = filteredFacultyData.slice(startIndex, endIndex);
		
		// Note: Courses handling count is 0 for now until course assignments are implemented
		tbody.innerHTML = currentPageData.map((t) => {
			const name = formatName(t);
			const email = t.institutional_email || '';
			const mobile = t.mobile_no || '';
			const courses = 0; // Placeholder; integrate with assignments later
			const totalUnits = t.total_units || 0;
			return `
				<tr onclick="assignCourse(${t.id})">
					<td>${t.employee_no || ''}</td>
					<td>${name}</td>
					<td>${email}</td>
					<td>${mobile}</td>
					<td style="text-align: center;"><span class="stat-pill"><strong>${courses}</strong></span></td>
					<td style="text-align: center;"><span class="stat-pill"><strong>${totalUnits}</strong></span></td>
					<td style="text-align: center;">
						<button class="assign-course-btn" onclick="event.stopPropagation(); assignCourse(${t.id})">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
							Manage Load
						</button>
					</td>
				</tr>
			`;
		}).join('');
		
		// Update faculty count
		document.getElementById('facultyCount').textContent = filteredFacultyData.length;
		
		// Render pagination controls
		renderPaginationControls(totalPages);
	}

	// Make searchFaculty globally accessible
	window.searchFaculty = function() {
		const searchTerm = document.getElementById('facultySearchInput').value.toLowerCase().trim();
		
		if (!searchTerm) {
			// If search is empty, show all faculty
			filteredFacultyData = allFacultyData;
		} else {
			// Filter faculty based on search term
			filteredFacultyData = allFacultyData.filter(faculty => {
				const name = formatName(faculty).toLowerCase();
				const employeeNo = (faculty.employee_no || '').toLowerCase();
				const email = (faculty.institutional_email || '').toLowerCase();
				
				return name.includes(searchTerm) || 
					   employeeNo.includes(searchTerm) || 
					   email.includes(searchTerm);
			});
		}
		
		// Reset to first page when searching
		currentPage = 1;
		displayFacultyList();
		
		// Focus the search button for visual feedback
		const searchButton = document.querySelector('.search-button');
		if (searchButton) {
			searchButton.focus();
		}
	};

	function renderPaginationControls(totalPages = 0) {
		const paginationContainer = document.getElementById('facultyPaginationControls');
		
		if (totalPages <= 1) {
			paginationContainer.innerHTML = '';
			return;
		}
		
		let paginationHTML = '';
		
		// Previous button
		if (currentPage > 1) {
			paginationHTML += `<button onclick="goToPage(${currentPage - 1})">Previous</button>`;
		}
		
		// Page numbers
		const maxVisiblePages = 5;
		let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
		let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
		
		if (endPage - startPage + 1 < maxVisiblePages) {
			startPage = Math.max(1, endPage - maxVisiblePages + 1);
		}
		
		for (let i = startPage; i <= endPage; i++) {
			if (i === currentPage) {
				paginationHTML += `<button class="active">${i}</button>`;
			} else {
				paginationHTML += `<button onclick="goToPage(${i})">${i}</button>`;
			}
		}
		
		// Next button
		if (currentPage < totalPages) {
			paginationHTML += `<button onclick="goToPage(${currentPage + 1})">Next</button>`;
		}
		
		paginationContainer.innerHTML = paginationHTML;
	}
	
	// Make goToPage globally accessible
	window.goToPage = function(page) {
		currentPage = page;
		displayFacultyList();
	};
	
	// Set up event listeners for search functionality
	document.addEventListener('DOMContentLoaded', function() {
		loadFacultyStaff();
		
		// Get search input and clear button
		const searchInput = document.getElementById('facultySearchInput');
		const clearSearchBtn = document.getElementById('clearSearchBtn');
		
		// Add input event to show/hide clear button
		if (searchInput) {
			searchInput.addEventListener('input', function() {
				if (clearSearchBtn) {
					if (this.value.trim() !== '') {
						clearSearchBtn.style.display = 'flex';
					} else {
						clearSearchBtn.style.display = 'none';
					}
				}
			});
			
			// Add Enter key support for search
			searchInput.addEventListener('keypress', function(event) {
				if (event.key === 'Enter') {
					event.preventDefault();
					searchFaculty();
				}
			});
		}
		
		// Add clear button functionality
		if (clearSearchBtn) {
			clearSearchBtn.addEventListener('click', function() {
				searchInput.value = '';
				clearSearchBtn.style.display = 'none';
				// Show all data when cleared
				filteredFacultyData = allFacultyData;
				currentPage = 1;
				displayFacultyList();
			});
		}
	});
})();

// Faculty action functions - old modal functions removed

// All old modal functions removed - now using dedicated faculty details page

function assignCourse(facultyId) {
	
	// Navigate to faculty details page
	window.location.href = `content.php?page=faculty-details&faculty_id=${facultyId}`;
}
</script>