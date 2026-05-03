<?php
// Library Management (Librarian) — inventory-style book management.
// Included by librarian/content.php (session/auth handled upstream).
?>

<div class="library-management lm-page">
  <div class="lm-header card">
    <div class="lm-header-left">
      <div class="lm-title-row">
        <h1 class="lm-title">Library Management</h1>
        <span class="lm-subtitle">Manage your library’s books, departments, categories, and search tags.</span>
      </div>
      <div class="lm-hint">
        Tip: Add lots of tags (e.g., “oop”, “java”, “database”, “research methods”) so users can search faster.
      </div>
    </div>
    <div class="lm-header-actions">
      <button class="btn btn-secondary" type="button" id="lmManageCategoriesBtn">
        <i data-lucide="layers"></i>
        Categories
      </button>
      <button class="btn btn-primary" type="button" id="lmAddBookBtn">
        <i data-lucide="plus"></i>
        Add Book
      </button>
    </div>
  </div>

  <div class="lm-stats">
    <div class="lm-stat card" data-stat="total">
      <div class="lm-stat-label">Total Books</div>
      <div class="lm-stat-value" id="lmStatTotal">—</div>
    </div>
    <div class="lm-stat card" data-stat="available">
      <div class="lm-stat-label">Available</div>
      <div class="lm-stat-value" id="lmStatAvailable">—</div>
    </div>
    <div class="lm-stat card" data-stat="checked_out">
      <div class="lm-stat-label">Checked Out</div>
      <div class="lm-stat-value" id="lmStatCheckedOut">—</div>
    </div>
    <div class="lm-stat card" data-stat="filters">
      <div class="lm-stat-label">Active Filters</div>
      <div class="lm-stat-value" id="lmStatFilters">0</div>
    </div>
  </div>

  <div class="lm-toolbar card">
    <div class="lm-search">
      <i data-lucide="search"></i>
      <input type="text" id="lmSearchInput" placeholder="Search title, author, ISBN, tags, categories…" autocomplete="off" />
      <button type="button" class="lm-icon-btn" id="lmClearSearchBtn" title="Clear search" aria-label="Clear search">
        <i data-lucide="x"></i>
      </button>
    </div>

    <div class="lm-filters">
      <div class="lm-filter">
        <label for="lmDepartmentFilter">Department</label>
        <select id="lmDepartmentFilter">
          <option value="all">All</option>
        </select>
      </div>

      <div class="lm-filter">
        <label for="lmCategoryFilter">Category</label>
        <select id="lmCategoryFilter">
          <option value="all">All</option>
        </select>
      </div>

      <div class="lm-filter">
        <label for="lmStatusFilter">Status</label>
        <select id="lmStatusFilter">
          <option value="all">All</option>
          <option value="available">Available</option>
          <option value="checked_out">Checked Out</option>
          <option value="reserved">Reserved</option>
          <option value="maintenance">Maintenance</option>
          <option value="lost">Lost</option>
        </select>
      </div>

      <div class="lm-filter">
        <label for="lmClassificationFilter">Classification</label>
        <select id="lmClassificationFilter">
          <option value="all">All</option>
        </select>
      </div>
    </div>
  </div>

  <div class="lm-table card">
    <div class="lm-table-head">
      <div class="lm-table-meta" id="lmTableMeta">Loading…</div>
      <div class="lm-table-actions">
        <label class="lm-inline">
          <span>Rows</span>
          <select id="lmLimitSelect">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
        </label>
      </div>
    </div>

    <div class="lm-table-wrap">
      <table class="lm-books-table" aria-label="Books">
        <thead>
          <tr>
            <th class="col-title">Title</th>
            <th class="col-author">Author</th>
            <th class="col-dept">Departments</th>
            <th class="col-cat">Categories</th>
            <th class="col-tags">Tags</th>
            <th class="col-copies">Copies</th>
            <th class="col-location">Location</th>
            <th class="col-status">Status</th>
            <th class="col-actions">Actions</th>
          </tr>
        </thead>
        <tbody id="lmBooksTbody">
          <tr>
            <td colspan="9" class="lm-empty">Loading books…</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="lm-pagination">
      <button class="btn btn-secondary" type="button" id="lmPrevPageBtn">Prev</button>
      <div class="lm-page-info" id="lmPageInfo">—</div>
      <button class="btn btn-secondary" type="button" id="lmNextPageBtn">Next</button>
    </div>
  </div>
</div>

<!-- Book modal -->
<div class="lm-modal-overlay" id="lmBookModal" aria-hidden="true">
  <div class="lm-modal lm-book-modal card" role="dialog" aria-modal="true" aria-labelledby="lmBookModalTitle">
    <div class="lm-modal-header">
      <div>
        <div class="lm-modal-title" id="lmBookModalTitle">Add Book</div>
        <div class="lm-modal-subtitle" id="lmBookModalSubtitle">Complete details help searching and reporting.</div>
      </div>
      <button class="lm-icon-btn" type="button" id="lmCloseBookModalBtn" aria-label="Close">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form id="lmBookForm" class="lm-form" autocomplete="off" novalidate>
      <input type="hidden" id="lmBookId" value="" />

      <div class="lm-form-grid">
        <div class="lm-section span-3">
          <div class="lm-section-title">
            <i data-lucide="book-open"></i>
            Core Details
          </div>
          <div class="lm-section-subtitle">Start with the searchable basics: title, author, ISBN.</div>
        </div>

        <div class="lm-field span-2">
          <label for="lmBookTitle">Book Title<span class="req">*</span></label>
          <input id="lmBookTitle" type="text" required placeholder="e.g., Introduction to Algorithms" />
        </div>
        <div class="lm-field">
          <label for="lmAuthor">Author</label>
          <input id="lmAuthor" type="text" placeholder="e.g., Cormen, Leiserson, Rivest, Stein" />
        </div>
        <div class="lm-field">
          <label for="lmIsbn">ISBN</label>
          <input id="lmIsbn" type="text" placeholder="Optional" />
        </div>

        <div class="lm-section span-3">
          <div class="lm-section-title">
            <i data-lucide="archive"></i>
            Library Metadata
          </div>
          <div class="lm-section-subtitle">These help cataloging, classification, and reporting.</div>
        </div>

        <div class="lm-field">
          <label for="lmPublisher">Publisher</label>
          <input id="lmPublisher" type="text" placeholder="Optional" />
        </div>
        <div class="lm-field">
          <label for="lmPublicationYear">Publication Year</label>
          <input id="lmPublicationYear" type="text" inputmode="numeric" placeholder="e.g., 2023" />
        </div>
        <div class="lm-field">
          <label for="lmEdition">Edition</label>
          <input id="lmEdition" type="text" placeholder="e.g., 3rd" />
        </div>

        <div class="lm-field">
          <label for="lmCallNumber">Call Number</label>
          <input id="lmCallNumber" type="text" placeholder="e.g., 005.1 C811" />
        </div>
        <div class="lm-field">
          <label for="lmClassification">Classification</label>
          <select id="lmClassification">
            <option value="">None</option>
          </select>
        </div>
        <div class="lm-field">
          <label for="lmLocation">Location</label>
          <div class="lm-location-checkboxes" style="display: flex; gap: 16px; margin-top: 8px;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="checkbox" name="lmLocation[]" value="Main Library" id="lmLocationMain" style="width: 18px; height: 18px; cursor: pointer;">
              <span>Main Library</span>
            </label>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="checkbox" name="lmLocation[]" value="Buenavista Library" id="lmLocationBuenavista" style="width: 18px; height: 18px; cursor: pointer;">
              <span>Buenavista Library</span>
            </label>
          </div>
        </div>

        <div class="lm-section span-3">
          <div class="lm-section-title">
            <i data-lucide="layers"></i>
            Organization
          </div>
          <div class="lm-section-subtitle">Departments and categories can be multiple. Tags can be unlimited.</div>
        </div>

        <div class="lm-field">
          <label for="lmNoOfCopies">Total Copies</label>
          <input id="lmNoOfCopies" type="number" min="0" value="1" />
        </div>
        <div class="lm-field">
          <label for="lmAvailableCopies">Available Copies</label>
          <input id="lmAvailableCopies" type="number" min="0" value="1" />
        </div>
        <div class="lm-field">
          <label for="lmStatus">Status</label>
          <select id="lmStatus">
            <option value="available">Available</option>
            <option value="checked_out">Checked Out</option>
            <option value="reserved">Reserved</option>
            <option value="maintenance">Maintenance</option>
            <option value="lost">Lost</option>
          </select>
        </div>

        <div class="lm-field span-2">
          <label for="lmDepartments">Departments (multi)</label>
          <div class="lm-multi" id="lmDepartmentsMulti">
            <div class="lm-chips" id="lmDepartmentChips"></div>
            <input id="lmDepartmentsInput" type="text" placeholder="Type to search departments…" />
            <div class="lm-dropdown" id="lmDepartmentsDropdown" role="listbox"></div>
          </div>
          <div class="lm-help">Pick one or more departments. The first selected becomes the primary department.</div>
        </div>

        <div class="lm-field span-2">
          <label for="lmCategories">Categories (multi)</label>
          <div class="lm-multi" id="lmCategoriesMulti">
            <div class="lm-chips" id="lmCategoryChips"></div>
            <input id="lmCategoriesInput" type="text" placeholder="Type to search categories…" />
            <div class="lm-dropdown" id="lmCategoriesDropdown" role="listbox"></div>
          </div>
          <div class="lm-help">Create new categories from the “Categories” button on the page.</div>
        </div>

        <div class="lm-field span-2">
          <label for="lmTagsInput">Tags (multi)</label>
          <div class="lm-multi" id="lmTagsMulti">
            <div class="lm-chips" id="lmTagChips"></div>
            <input id="lmTagsInput" type="text" placeholder="Type a tag and press Enter…" />
          </div>
          <div class="lm-help">Tags are searchable. Add as many as you want (duplicates are ignored).</div>
        </div>

        <div class="lm-section span-3">
          <div class="lm-section-title">
            <i data-lucide="file-text"></i>
            Notes
          </div>
          <div class="lm-section-subtitle">Optional description for librarians and future reference.</div>
        </div>

        <div class="lm-field span-2">
          <label for="lmDescription">Description</label>
          <textarea id="lmDescription" rows="3" placeholder="Optional notes (summary, edition notes, usage, etc.)"></textarea>
        </div>
      </div>

      <div class="lm-form-actions">
        <div class="lm-form-error" id="lmBookFormError" role="alert"></div>
        <div class="lm-form-actions-right">
          <button class="btn btn-cancel" type="button" id="lmCancelBookBtn" title="Close without saving">
            <i data-lucide="x-circle"></i>
            <span class="lm-cancel-text">Cancel</span>
          </button>
          <button class="btn btn-primary" type="submit" id="lmSaveBookBtn">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Categories modal -->
<div class="lm-modal-overlay" id="lmCategoriesModal" aria-hidden="true">
  <div class="lm-modal card" role="dialog" aria-modal="true" aria-labelledby="lmCategoriesModalTitle">
    <div class="lm-modal-header">
      <div>
        <div class="lm-modal-title" id="lmCategoriesModalTitle">Categories</div>
        <div class="lm-modal-subtitle">Create reusable categories for books.</div>
      </div>
      <button class="lm-icon-btn" type="button" id="lmCloseCategoriesModalBtn" aria-label="Close">
        <i data-lucide="x"></i>
      </button>
    </div>

    <div class="lm-categories-body">
      <div class="lm-categories-create">
        <input id="lmNewCategoryName" type="text" placeholder="New category name…" />
        <button class="btn btn-primary" type="button" id="lmCreateCategoryBtn">Create</button>
      </div>
      <div class="lm-categories-list" id="lmCategoriesList"></div>
      <div class="lm-form-error" id="lmCategoriesError" role="alert"></div>
    </div>
  </div>
</div>

<script>
  // Ensure icons render (lucide is loaded in librarian/content.php)
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }
</script>
