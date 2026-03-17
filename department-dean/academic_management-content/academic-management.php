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

<div class="faculty-load-section" style="margin-top: 20px;">
	<div class="departments-header">
		<div>
			<h3 style="margin-top: 0px;">Faculty Staff</h3>
			<p>List of active faculty and courses they handle</p>
		</div>
	</div>
	<div class="faculty-load-container">
		<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
			<div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
				<div class="user-search-bar" style="width: 450px;">
					<img src="../src/assets/icons/magnifier-icon.png" alt="Search" class="magnifier-icon">
					<input type="text" placeholder="Search faculty by name, employee no., or email..." id="facultySearchInput" autocomplete="off">
					<button type="button" id="clearSearchBtn" class="clear-search-btn" style="display: none;">&times;</button>
				</div>
				<button class="search-button" onclick="searchFaculty()">Search</button>
			</div>
			<div style="color: #666; font-size: 14px; white-space: nowrap;">
				<span id="facultyCount">0</span> faculty members found
			</div>
		</div>
		<table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
			<thead>
				<tr style="background:#f5f5f5;">
					<th style="padding:12px; text-align:left;">Employee No.</th>
					<th style="padding:12px; text-align:left;">Name</th>
					<th style="padding:12px; text-align:left;">Institutional Email</th>
					<th style="padding:12px; text-align:left;">Mobile</th>
					<th style="padding:12px; text-align:center;">Loads</th>
					<th style="padding:12px; text-align:center;">Total Units</th>
					<th style="padding:12px; text-align:center;">Actions</th>
				</tr>
			</thead>
			<tbody id="facultyStaffTbody">
				<tr><td colspan="7" style="padding:12px; color:#666;">Loading faculty list...</td></tr>
			</tbody>
		</table>
		
		<!-- Pagination Controls -->
		<div class="pagination" id="facultyPaginationControls" style="display: flex; justify-content: center; gap: 6px; margin-top: 10px;">
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
				<tr onclick="assignCourse(${t.id})" style="cursor:pointer; transition:background-color 0.2s;" onmouseover="this.style.backgroundColor='#e3f2fd'" onmouseout="this.style.backgroundColor=''">
					<td style="padding:10px;">${t.employee_no || ''}</td>
					<td style="padding:10px;">${name}</td>
					<td style="padding:10px;">${email}</td>
					<td style="padding:10px;">${mobile}</td>
					<td style="padding:10px; text-align:center; font-weight:600;">${courses}</td>
					<td style="padding:10px; text-align:center; font-weight:600;">${totalUnits}</td>
					<td style="padding:10px; text-align:center;">
						<button class="assign-course-btn" onclick="event.stopPropagation(); assignCourse(${t.id})" style="padding:6px 12px; background:#ff6b35; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">Manage Load</button>
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
			paginationHTML += `<button onclick="goToPage(${currentPage - 1})" style="padding: 6px 12px; border: 1px solid #ccc; background-color: white; border-radius: 4px; cursor: pointer;">Previous</button>`;
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
				paginationHTML += `<button class="active" style="padding: 6px 12px; border: 1px solid #ccc; background-color: #0077FF; color: white; border-radius: 4px; cursor: pointer;">${i}</button>`;
			} else {
				paginationHTML += `<button onclick="goToPage(${i})" style="padding: 6px 12px; border: 1px solid #ccc; background-color: white; border-radius: 4px; cursor: pointer;">${i}</button>`;
			}
		}
		
		// Next button
		if (currentPage < totalPages) {
			paginationHTML += `<button onclick="goToPage(${currentPage + 1})" style="padding: 6px 12px; border: 1px solid #ccc; background-color: white; border-radius: 4px; cursor: pointer;">Next</button>`;
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
	console.log('Navigate to faculty details page for ID:', facultyId);
	
	// Navigate to faculty details page
	window.location.href = `content.php?page=faculty-details&faculty_id=${facultyId}`;
}
</script>