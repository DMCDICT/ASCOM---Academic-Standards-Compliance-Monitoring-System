(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const pageRoot = $(".library-management.lm-page");
  if (!pageRoot) return;

  const state = {
    search: "",
    department: "all",
    category: "all",
    status: "all",
    classification: "all",
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 1,
    initialFormSnapshot: "",
  };

  const cache = {
    departments: [],
    categories: [],
    classifications: [],
  };

  const els = {
    search: $("#lmSearchInput"),
    clearSearch: $("#lmClearSearchBtn"),
    deptFilter: $("#lmDepartmentFilter"),
    catFilter: $("#lmCategoryFilter"),
    statusFilter: $("#lmStatusFilter"),
    classFilter: $("#lmClassificationFilter"),
    limitSelect: $("#lmLimitSelect"),
    tbody: $("#lmBooksTbody"),
    meta: $("#lmTableMeta"),
    prev: $("#lmPrevPageBtn"),
    next: $("#lmNextPageBtn"),
    pageInfo: $("#lmPageInfo"),
    statTotal: $("#lmStatTotal"),
    statAvailable: $("#lmStatAvailable"),
    statCheckedOut: $("#lmStatCheckedOut"),
    statFilters: $("#lmStatFilters"),

    addBookBtn: $("#lmAddBookBtn"),
    manageCategoriesBtn: $("#lmManageCategoriesBtn"),

    bookModal: $("#lmBookModal"),
    closeBookModal: $("#lmCloseBookModalBtn"),
    cancelBook: $("#lmCancelBookBtn"),
    bookForm: $("#lmBookForm"),
    bookError: $("#lmBookFormError"),
    bookModalTitle: $("#lmBookModalTitle"),
    bookModalSubtitle: $("#lmBookModalSubtitle"),

    bookId: $("#lmBookId"),
    bookTitle: $("#lmBookTitle"),
    author: $("#lmAuthor"),
    isbn: $("#lmIsbn"),
    publisher: $("#lmPublisher"),
    publicationYear: $("#lmPublicationYear"),
    edition: $("#lmEdition"),
    callNumber: $("#lmCallNumber"),
    classification: $("#lmClassification"),
    location: $("#lmLocation"),
    noOfCopies: $("#lmNoOfCopies"),
    availableCopies: $("#lmAvailableCopies"),
    status: $("#lmStatus"),
    description: $("#lmDescription"),

    deptMulti: {
      container: $("#lmDepartmentsMulti"),
      chips: $("#lmDepartmentChips"),
      input: $("#lmDepartmentsInput"),
      dropdown: $("#lmDepartmentsDropdown"),
    },
    catMulti: {
      container: $("#lmCategoriesMulti"),
      chips: $("#lmCategoryChips"),
      input: $("#lmCategoriesInput"),
      dropdown: $("#lmCategoriesDropdown"),
    },
    tagsMulti: {
      chips: $("#lmTagChips"),
      input: $("#lmTagsInput"),
    },

    categoriesModal: $("#lmCategoriesModal"),
    closeCategoriesModal: $("#lmCloseCategoriesModalBtn"),
    newCategoryName: $("#lmNewCategoryName"),
    createCategoryBtn: $("#lmCreateCategoryBtn"),
    categoriesList: $("#lmCategoriesList"),
    categoriesError: $("#lmCategoriesError"),
  };

  const fetchJSON = async (url, options) => {
    const res = await fetch(url, options);
    const text = await res.text();
    let json;
    try {
      json = JSON.parse(text);
    } catch {
      throw new Error("Invalid server response");
    }
    if (!res.ok || json?.success === false) {
      throw new Error(json?.message || `Request failed (${res.status})`);
    }
    return json;
  };

  const debounce = (fn, ms) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  };

  const escapeHtml = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (c) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    }[c]));

  const openModal = (overlayEl) => {
    overlayEl.classList.add("open");
    overlayEl.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  const closeModal = (overlayEl) => {
    overlayEl.classList.remove("open");
    overlayEl.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    if (els.bookModal.classList.contains("open")) {
      safeCloseBookModal();
      return;
    }
    if (els.categoriesModal.classList.contains("open")) {
      closeModal(els.categoriesModal);
    }
  });

  const countActiveFilters = () => {
    let n = 0;
    if (state.search.trim() !== "") n += 1;
    if (state.department !== "all") n += 1;
    if (state.category !== "all") n += 1;
    if (state.status !== "all") n += 1;
    if (state.classification !== "all") n += 1;
    return n;
  };

  const statusLabel = (s) => ({
    available: "Available",
    checked_out: "Checked Out",
    reserved: "Reserved",
    maintenance: "Maintenance",
    lost: "Lost",
  }[s] || s);

  const pill = (text, cls = "", attrs = "") =>
    `<span class="lm-pill ${cls}" ${attrs}>${escapeHtml(text)}</span>`;

  const renderBooks = (rows) => {
    if (!rows || rows.length === 0) {
      els.tbody.innerHTML = `<tr><td colspan="9" class="lm-empty">No books found. Try changing filters or add a new book.</td></tr>`;
      return;
    }

    els.tbody.innerHTML = rows.map((b) => {
      const departments = (b.departments || []).slice(0, 3).map((d) => pill(d.code || d.name, "", `title="${escapeHtml(d.name)}"`)).join("");
      const deptMore = (b.departments || []).length > 3 ? pill(`+${(b.departments.length - 3)}`, "", `title="${escapeHtml(b.departments.map(d=>d.name).join(", "))}"`) : "";

      const categories = (b.categories || []).slice(0, 3).map((c) => pill(c.name)).join("");
      const catMore = (b.categories || []).length > 3 ? pill(`+${(b.categories.length - 3)}`, "", `title="${escapeHtml(b.categories.map(c=>c.name).join(", "))}"`) : "";

      const tags = (b.tags || []).slice(0, 3).map((t) => pill(t, "lm-pill-tag")).join("");
      const tagMore = (b.tags || []).length > 3 ? pill(`+${(b.tags.length - 3)}`, "lm-pill-tag", `title="${escapeHtml(b.tags.join(", "))}"`) : "";

      const copies = `${Number(b.available_copies ?? 0)}/${Number(b.no_of_copies ?? 0)}`;
      const title = escapeHtml(b.book_title);
      const author = escapeHtml(b.author || "—");
      const location = escapeHtml(b.location || "—");
      const status = escapeHtml(statusLabel(b.status));

      return `
        <tr data-book-id="${b.id}">
          <td class="col-title" title="${title}">${title}</td>
          <td class="col-author">${author}</td>
          <td class="col-dept">${departments}${deptMore}</td>
          <td class="col-cat">${categories}${catMore}</td>
          <td class="col-tags">${tags}${tagMore}</td>
          <td class="col-copies">${escapeHtml(copies)}</td>
          <td class="col-location">${location}</td>
          <td class="col-status">${pill(status, "lm-pill-status", `data-status="${escapeHtml(b.status)}"`)}
          </td>
          <td class="col-actions">
            <div class="lm-actions">
              <button class="lm-icon-btn" type="button" data-action="edit" title="Edit">
                <i data-lucide="pencil"></i>
              </button>
              <button class="lm-icon-btn" type="button" data-action="delete" title="Delete">
                <i data-lucide="trash-2"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join("");

    if (window.lucide?.createIcons) window.lucide.createIcons();
  };

  const setMeta = () => {
    const from = state.total === 0 ? 0 : (state.page - 1) * state.limit + 1;
    const to = Math.min(state.total, state.page * state.limit);
    els.meta.textContent = `Showing ${from}-${to} of ${state.total}`;
    els.pageInfo.textContent = `Page ${state.page} / ${state.totalPages}`;
    els.prev.disabled = state.page <= 1;
    els.next.disabled = state.page >= state.totalPages;
    els.statFilters.textContent = String(countActiveFilters());
  };

  const loadBooks = async () => {
    els.tbody.innerHTML = `<tr><td colspan="9" class="lm-empty">Loading…</td></tr>`;
    const params = new URLSearchParams({
      search: state.search,
      department: state.department,
      category: state.category,
      status: state.status,
      classification: state.classification,
      page: String(state.page),
      limit: String(state.limit),
    });

    const json = await fetchJSON(`./api/get_books.php?${params.toString()}`);
    const { data, total, totalPages, stats } = json;

    state.total = total ?? 0;
    state.totalPages = totalPages ?? 1;

    els.statTotal.textContent = String(stats?.total ?? "—");
    els.statAvailable.textContent = String(stats?.available ?? "—");
    els.statCheckedOut.textContent = String(stats?.checked_out ?? "—");

    renderBooks(data);
    setMeta();
  };

  const populateSelect = (selectEl, items, getLabel) => {
    const current = selectEl.value;
    const options = [`<option value="all">All</option>`]
      .concat(items.map((it) => `<option value="${escapeHtml(it.id)}">${escapeHtml(getLabel(it))}</option>`));
    selectEl.innerHTML = options.join("");
    if ([...selectEl.options].some((o) => o.value === current)) selectEl.value = current;
  };

  const multiSelect = (cfg) => {
    const selected = new Map(); // id -> item
    let items = [];

    const renderChips = () => {
      cfg.chips.innerHTML = Array.from(selected.values()).map((it) => `
        <span class="lm-chip" data-id="${it.id}">
          ${escapeHtml(cfg.chipLabel(it))}
          <button type="button" aria-label="Remove">
            <i data-lucide="x"></i>
          </button>
        </span>
      `).join("");
      if (window.lucide?.createIcons) window.lucide.createIcons();
    };

    const renderDropdown = (q) => {
      const query = (q || "").toLowerCase().trim();
      const filtered = query
        ? items.filter((it) => cfg.searchText(it).toLowerCase().includes(query))
        : items.slice();

      const max = 25;
      const visible = filtered.slice(0, max).filter((it) => !selected.has(String(it.id)));
      cfg.dropdown.innerHTML = visible.length
        ? visible.map((it) => `<div class="lm-option" role="option" data-id="${it.id}">${escapeHtml(cfg.optionLabel(it))}</div>`).join("")
        : `<div class="lm-option" role="option" data-id="" style="cursor: default; opacity: .7;">No matches</div>`;
    };

    const open = () => cfg.dropdown.classList.add("open");
    const close = () => cfg.dropdown.classList.remove("open");

    cfg.input.addEventListener("focus", () => {
      renderDropdown(cfg.input.value);
      open();
    });
    cfg.input.addEventListener("input", () => {
      renderDropdown(cfg.input.value);
      open();
    });

    cfg.dropdown.addEventListener("click", (e) => {
      const opt = e.target.closest(".lm-option");
      if (!opt) return;
      const id = opt.getAttribute("data-id");
      if (!id) return;
      const it = items.find((x) => String(x.id) === String(id));
      if (!it) return;
      selected.set(String(it.id), it);
      cfg.input.value = "";
      renderChips();
      renderDropdown("");
      cfg.input.focus();
    });

    cfg.chips.addEventListener("click", (e) => {
      const chip = e.target.closest(".lm-chip");
      if (!chip) return;
      const removeBtn = e.target.closest("button");
      if (!removeBtn) return;
      const id = chip.getAttribute("data-id");
      selected.delete(String(id));
      renderChips();
    });

    document.addEventListener("click", (e) => {
      if (!cfg.container.contains(e.target)) close();
    });

    return {
      setItems(newItems) {
        items = newItems || [];
        renderDropdown(cfg.input.value);
      },
      getSelectedIds() {
        return Array.from(selected.keys()).map((x) => Number(x));
      },
      setSelectedByIds(ids) {
        selected.clear();
        (ids || []).forEach((id) => {
          const it = items.find((x) => Number(x.id) === Number(id));
          if (it) selected.set(String(it.id), it);
        });
        renderChips();
      },
      clear() {
        selected.clear();
        renderChips();
      },
    };
  };

  const deptMulti = multiSelect({
    container: els.deptMulti.container,
    chips: els.deptMulti.chips,
    input: els.deptMulti.input,
    dropdown: els.deptMulti.dropdown,
    optionLabel: (d) => `${d.department_code} — ${d.department_name}`,
    chipLabel: (d) => d.department_code || d.department_name,
    searchText: (d) => `${d.department_code} ${d.department_name}`,
  });

  const catMulti = multiSelect({
    container: els.catMulti.container,
    chips: els.catMulti.chips,
    input: els.catMulti.input,
    dropdown: els.catMulti.dropdown,
    optionLabel: (c) => c.name,
    chipLabel: (c) => c.name,
    searchText: (c) => c.name,
  });

  const tagsState = {
    tags: [],
  };

  const renderTagChips = () => {
    els.tagsMulti.chips.innerHTML = tagsState.tags.map((t) => `
      <span class="lm-chip" data-tag="${escapeHtml(t)}">
        ${escapeHtml(t)}
        <button type="button" aria-label="Remove">
          <i data-lucide="x"></i>
        </button>
      </span>
    `).join("");
    if (window.lucide?.createIcons) window.lucide.createIcons();
  };

  const getFormSnapshot = () => JSON.stringify({
    title: els.bookTitle.value.trim(),
    author: els.author.value.trim(),
    isbn: els.isbn.value.trim(),
    publisher: els.publisher.value.trim(),
    year: els.publicationYear.value.trim(),
    edition: els.edition.value.trim(),
    call: els.callNumber.value.trim(),
    class: els.classification.value,
    loc: els.location.value.trim(),
    copies: els.noOfCopies.value,
    avail: els.availableCopies.value,
    status: els.status.value,
    desc: els.description.value.trim(),
    depts: deptMulti.getSelectedIds().sort(),
    cats: catMulti.getSelectedIds().sort(),
    tags: tagsState.tags.slice().sort(),
  });

  const updateCancelButtonUX = () => {
    const textEl = document.querySelector("#lmCancelBookBtn .lm-cancel-text");
    const btn = els.cancelBook;
    if (!btn || !textEl) return;

    const dirty = els.bookModal.classList.contains("open") && getFormSnapshot() !== state.initialFormSnapshot;
    if (dirty) {
      textEl.textContent = "Discard";
      btn.title = "Discard changes and close";
    } else {
      textEl.textContent = "Cancel";
      btn.title = "Close without saving";
    }
  };

  const addTag = (raw) => {
    const tag = String(raw || "").trim().toLowerCase();
    if (!tag) return;
    if (tag.length > 100) return;
    if (!tagsState.tags.includes(tag)) tagsState.tags.push(tag);
    renderTagChips();
    updateCancelButtonUX();
  };

  const resetBookForm = () => {
    els.bookError.textContent = "";
    els.bookId.value = "";
    els.bookTitle.value = "";
    els.author.value = "";
    els.isbn.value = "";
    els.publisher.value = "";
    els.publicationYear.value = "";
    els.edition.value = "";
    els.callNumber.value = "";
    els.classification.value = "";
    els.location.value = "";
    els.noOfCopies.value = "1";
    els.availableCopies.value = "1";
    els.status.value = "available";
    els.description.value = "";
    deptMulti.clear();
    catMulti.clear();
    tagsState.tags = [];
    renderTagChips();
    updateCancelButtonUX();
  };

  const openAddBook = () => {
    resetBookForm();
    els.bookModalTitle.textContent = "Add Book";
    els.bookModalSubtitle.textContent = "Add complete details, departments, categories, and tags for better searching.";
    openModal(els.bookModal);
    setTimeout(() => {
      els.bookTitle.focus();
      state.initialFormSnapshot = getFormSnapshot();
      updateCancelButtonUX();
    }, 50);
  };

  const openEditBook = async (id) => {
    resetBookForm();
    els.bookModalTitle.textContent = "Edit Book";
    els.bookModalSubtitle.textContent = "Update details, departments, categories, and searchable tags.";
    openModal(els.bookModal);
    els.bookError.textContent = "Loading…";
    try {
      const json = await fetchJSON(`./api/get_library_book.php?id=${encodeURIComponent(id)}`);
      const b = json.data;
      els.bookError.textContent = "";
      els.bookId.value = String(b.id);
      els.bookTitle.value = b.book_title || "";
      els.author.value = b.author || "";
      els.isbn.value = b.isbn || "";
      els.publisher.value = b.publisher || "";
      els.publicationYear.value = b.publication_year || "";
      els.edition.value = b.edition || "";
      els.callNumber.value = b.call_number || "";
      els.classification.value = b.classification_id ? String(b.classification_id) : "";
      els.location.value = b.location || "";
      els.noOfCopies.value = String(b.no_of_copies ?? 0);
      els.availableCopies.value = String(b.available_copies ?? 0);
      els.status.value = b.status || "available";
      els.description.value = b.description || "";

      deptMulti.setSelectedByIds((b.departments || []).map((d) => Number(d.id)));
      catMulti.setSelectedByIds((b.categories || []).map((c) => Number(c.id)));
      tagsState.tags = (b.tags || []).slice().map((t) => String(t).toLowerCase());
      renderTagChips();
      state.initialFormSnapshot = getFormSnapshot();
      updateCancelButtonUX();
    } catch (e) {
      els.bookError.textContent = e.message || "Failed to load book";
    }
  };

  const saveBook = async () => {
    els.bookError.textContent = "";
    const title = els.bookTitle.value.trim();
    if (!title) {
      els.bookError.textContent = "Book title is required.";
      els.bookTitle.focus();
      return;
    }

    const noOfCopies = Math.max(0, Number(els.noOfCopies.value || 0));
    const availableCopies = Math.max(0, Number(els.availableCopies.value || 0));
    if (availableCopies > noOfCopies) {
      els.bookError.textContent = "Available copies cannot be greater than total copies.";
      els.availableCopies.focus();
      return;
    }

    const payload = {
      book_title: title,
      author: els.author.value.trim(),
      isbn: els.isbn.value.trim(),
      publisher: els.publisher.value.trim(),
      publication_year: els.publicationYear.value.trim(),
      edition: els.edition.value.trim(),
      call_number: els.callNumber.value.trim(),
      classification_id: els.classification.value ? Number(els.classification.value) : null,
      no_of_copies: noOfCopies,
      available_copies: availableCopies,
      location: els.location.value.trim(),
      description: els.description.value.trim(),
      status: els.status.value,
      department_ids: deptMulti.getSelectedIds(),
      category_ids: catMulti.getSelectedIds(),
      tags: tagsState.tags.slice(),
    };

    const isEdit = Boolean(els.bookId.value);
    if (isEdit) payload.book_id = Number(els.bookId.value);

    const endpoint = isEdit ? "./api/update_library_book.php" : "./api/add_library_book.php";
    const btn = $("#lmSaveBookBtn");
    const oldText = btn.textContent;
    btn.disabled = true;
    btn.textContent = "Saving…";

    try {
      await fetchJSON(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      closeModal(els.bookModal);
      await loadBooks();
    } catch (e) {
      els.bookError.textContent = e.message || "Failed to save";
    } finally {
      btn.disabled = false;
      btn.textContent = oldText;
    }
  };

  const deleteBook = async (id, title) => {
    const ok = window.confirm(`Delete "${title}"? This cannot be undone.`);
    if (!ok) return;
    try {
      await fetchJSON("./api/delete_library_book.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ book_id: Number(id) }),
      });
      await loadBooks();
    } catch (e) {
      window.alert(e.message || "Delete failed");
    }
  };

  const loadLookups = async () => {
    const [depts, cats, classes] = await Promise.all([
      fetchJSON("./api/get_departments.php"),
      fetchJSON("./api/get_categories.php"),
      fetchJSON("./api/get_classifications.php"),
    ]);

    cache.departments = depts.data || [];
    cache.categories = cats.data || [];
    cache.classifications = classes.data || [];

    populateSelect(els.deptFilter, cache.departments, (d) => `${d.department_code} — ${d.department_name}`);
    populateSelect(els.catFilter, cache.categories, (c) => c.name);
    populateSelect(els.classFilter, cache.classifications, (c) => c.name);

    els.classification.innerHTML =
      `<option value="">None</option>` +
      cache.classifications.map((c) => `<option value="${escapeHtml(c.id)}">${escapeHtml(c.name)}${c.call_number_range ? ` (${escapeHtml(c.call_number_range)})` : ""}</option>`).join("");

    deptMulti.setItems(cache.departments);
    catMulti.setItems(cache.categories);
  };

  const renderCategoriesModal = () => {
    els.categoriesList.innerHTML = cache.categories.map((c) => `<span class="lm-pill">${escapeHtml(c.name)}</span>`).join("");
  };

  const openCategories = async () => {
    els.categoriesError.textContent = "";
    openModal(els.categoriesModal);
    renderCategoriesModal();
    setTimeout(() => els.newCategoryName.focus(), 50);
  };

  const createCategory = async () => {
    els.categoriesError.textContent = "";
    const name = els.newCategoryName.value.trim();
    if (!name) {
      els.categoriesError.textContent = "Category name is required.";
      return;
    }
    const btn = els.createCategoryBtn;
    btn.disabled = true;
    try {
      const json = await fetchJSON("./api/add_category.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name }),
      });
      els.newCategoryName.value = "";
      cache.categories = [...cache.categories, json.category].sort((a, b) => String(a.name).localeCompare(String(b.name)));
      populateSelect(els.catFilter, cache.categories, (c) => c.name);
      catMulti.setItems(cache.categories);
      renderCategoriesModal();
    } catch (e) {
      els.categoriesError.textContent = e.message || "Failed to create category";
    } finally {
      btn.disabled = false;
    }
  };

  // Events
  els.addBookBtn.addEventListener("click", openAddBook);
  els.manageCategoriesBtn.addEventListener("click", openCategories);

  const safeCloseBookModal = () => {
    if (els.bookModal.classList.contains("open") && getFormSnapshot() !== state.initialFormSnapshot) {
      if (!window.confirm("Discard changes and close this form?")) return;
    }
    closeModal(els.bookModal);
  };

  els.closeBookModal.addEventListener("click", safeCloseBookModal);
  els.cancelBook.addEventListener("click", safeCloseBookModal);
  els.bookModal.addEventListener("click", (e) => {
    if (e.target === els.bookModal) safeCloseBookModal();
  });

  els.closeCategoriesModal.addEventListener("click", () => closeModal(els.categoriesModal));
  els.categoriesModal.addEventListener("click", (e) => {
    if (e.target === els.categoriesModal) closeModal(els.categoriesModal);
  });

  els.createCategoryBtn.addEventListener("click", createCategory);
  els.newCategoryName.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      createCategory();
    }
  });

  els.bookForm.addEventListener("submit", (e) => {
    e.preventDefault();
    saveBook();
  });

  const syncAvailableToTotal = () => {
    const total = Math.max(0, Number(els.noOfCopies.value || 0));
    const available = Math.max(0, Number(els.availableCopies.value || 0));
    if (available > total) els.availableCopies.value = String(total);
  };

  els.noOfCopies.addEventListener("input", syncAvailableToTotal);
  els.availableCopies.addEventListener("input", syncAvailableToTotal);

  els.tagsMulti.input.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === ",") {
      e.preventDefault();
      addTag(els.tagsMulti.input.value);
      els.tagsMulti.input.value = "";
    } else if (e.key === "Backspace" && els.tagsMulti.input.value === "" && tagsState.tags.length) {
      tagsState.tags.pop();
      renderTagChips();
      updateCancelButtonUX();
    }
  });

  els.tagsMulti.chips.addEventListener("click", (e) => {
    const chip = e.target.closest(".lm-chip");
    const btn = e.target.closest("button");
    if (!chip || !btn) return;
    const t = chip.getAttribute("data-tag");
    tagsState.tags = tagsState.tags.filter((x) => x !== t);
    renderTagChips();
    updateCancelButtonUX();
  });

  // Keep Cancel/Discard label in sync while typing/selecting
  const cancelUXTick = debounce(updateCancelButtonUX, 120);
  [
    els.bookTitle, els.author, els.isbn, els.publisher, els.publicationYear, els.edition,
    els.callNumber, els.classification, els.location, els.noOfCopies, els.availableCopies,
    els.status, els.description,
    els.deptMulti.input, els.catMulti.input, els.tagsMulti.input,
  ].forEach((el) => {
    if (!el) return;
    el.addEventListener("input", cancelUXTick);
    el.addEventListener("change", cancelUXTick);
  });

  els.search.addEventListener("input", debounce(() => {
    state.search = els.search.value;
    state.page = 1;
    loadBooks().catch(() => {});
    setMeta();
  }, 250));

  els.clearSearch.addEventListener("click", () => {
    els.search.value = "";
    state.search = "";
    state.page = 1;
    loadBooks().catch(() => {});
    setMeta();
  });

  const onFilterChange = () => {
    state.department = els.deptFilter.value || "all";
    state.category = els.catFilter.value || "all";
    state.status = els.statusFilter.value || "all";
    state.classification = els.classFilter.value || "all";
    state.page = 1;
    loadBooks().catch(() => {});
    setMeta();
  };

  els.deptFilter.addEventListener("change", onFilterChange);
  els.catFilter.addEventListener("change", onFilterChange);
  els.statusFilter.addEventListener("change", onFilterChange);
  els.classFilter.addEventListener("change", onFilterChange);

  els.limitSelect.addEventListener("change", () => {
    state.limit = Number(els.limitSelect.value || 10);
    state.page = 1;
    loadBooks().catch(() => {});
  });

  els.prev.addEventListener("click", () => {
    state.page = Math.max(1, state.page - 1);
    loadBooks().catch(() => {});
  });
  els.next.addEventListener("click", () => {
    state.page = Math.min(state.totalPages, state.page + 1);
    loadBooks().catch(() => {});
  });

  els.tbody.addEventListener("click", (e) => {
    const row = e.target.closest("tr[data-book-id]");
    if (!row) return;
    const id = row.getAttribute("data-book-id");
    const actionBtn = e.target.closest("button[data-action]");
    const action = actionBtn?.getAttribute("data-action");
    const title = row.querySelector(".col-title")?.textContent?.trim() || "this book";
    if (action === "delete") {
      deleteBook(id, title);
      return;
    }
    if (action === "edit") {
      openEditBook(id);
      return;
    }
    // Row click opens edit for faster management
    openEditBook(id);
  });

  // Init
  (async () => {
    try {
      await loadLookups();
      await loadBooks();
      setMeta();
    } catch (e) {
      els.tbody.innerHTML = `<tr><td colspan="9" class="lm-empty">${escapeHtml(e.message || "Failed to load page data")}</td></tr>`;
    }
  })();
})();
