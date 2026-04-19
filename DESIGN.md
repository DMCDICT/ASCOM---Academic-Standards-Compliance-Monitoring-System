# DESIGN.md — ASCOM System Design Reference

This document is the **single source of truth** for all styling, animation, and UX patterns across the entire ASCOM system. Every portal (Super Admin, Dean, Teacher, Librarian, QA) must follow these tokens and patterns to maintain a cohesive, premium experience.

---

## 1. Theme Tokens

### 1.1 Colors

| Token | Value | Usage |
|-------|-------|-------|
| **Primary** | `#0C4B34` | Sidebar, buttons, section headings, badges, accent bars |
| **Primary Hover** | `#0a3a28` | Button hover, link hover |
| **Primary Light** | `#0F7A53` | Gradient endpoints, active nav, create/confirm buttons |
| **Primary Tint** | `rgba(12, 75, 52, 0.08)` | Icon backgrounds, stat pill fills, subtle card tints |
| **Primary Border** | `rgba(12, 75, 52, 0.14)` | Card borders, container borders |
| **Primary Shadow** | `rgba(12, 75, 52, 0.12)` | Hover box-shadows |
| **Text Primary** | `#111827` | Headings, bold values, card titles |
| **Text Muted** | `rgba(17, 24, 39, 0.5)` | Labels, subtitles, secondary text |
| **Text Faint** | `rgba(17, 24, 39, 0.4)` | Sub-labels, helper text |
| **Surface** | `#ffffff` | Card backgrounds, modal backgrounds |
| **Page Background** | `#EFEFEF` | Body background |
| **Accent Blue** | `#1565C0` / `#42A5F5` | Secondary metric accent (e.g., "Total Users") |
| **Accent Green** | `#2E7D32` / `#66BB6A` | Tertiary metric accent (e.g., "System Status") |
| **Danger** | `#b91c1c` | Error states, destructive actions |
| **Danger Light** | `rgba(185, 28, 28, 0.06)` | Error state backgrounds |
| **Success** | `#4CAF50` | Success modals, confirm states |
| **Cancel Gray** | `#C9C9C9` | Cancel button background |

### 1.2 Dark Mode Tokens

Applied via `html[data-theme="dark"]`:

| Token | Value | Usage |
|-------|-------|-------|
| `--bg-primary` | `#1a1a1a` | Body/page background |
| `--bg-secondary` | `#2d2d2d` | Input backgrounds |
| `--text-primary` | `#e0e0e0` | Primary text |
| `--text-secondary` | `#b0b0b0` | Secondary text |
| `--border-color` | `#404040` | Borders |
| `--card-bg` | `#252525` | Card backgrounds |
| `--hover-bg` | `#353535` | Hover states |
| Card BG (direct) | `#1e1e1e` | Most card surfaces |
| Card Border | `#333` | Card borders |
| Card Border Hover | `#444` | Card borders on hover |
| Primary accent | `#81C784` | Green accents in dark mode |
| Blue accent | `#64B5F6` | Blue accents in dark mode |
| Text bright | `#f0f0f0` | Emphasized text |

### 1.3 Typography

| Property | Value |
|----------|-------|
| **Font Family** | `'TT Interphases', sans-serif` |
| **Bold Font** | `'TT Interphases Bold', sans-serif` (via `@font-face` weight: bold) |
| **Heading weight** | `800` |
| **Body weight** | `500` – `600` |
| **Label weight** | `700` |
| **Stat value weight** | `800` – `900` |
| **Letter spacing (labels)** | `0.6px` |
| **Letter spacing (buttons)** | `0.2px` |
| **Letter spacing (badges)** | `0.8px` |

---

## 2. Animations

### 2.1 Entrance — `fadeSlideUp`

**Use for:** Cards, sections, and containers appearing on page load. Apply with staggered `animation-delay` for cascading effect.

```css
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

/* Usage */
.element {
  animation: fadeSlideUp 0.45s ease-out both;
}

/* Stagger pattern — 80ms increments */
.element:nth-child(1) { animation-delay: 0.08s; }
.element:nth-child(2) { animation-delay: 0.16s; }
.element:nth-child(3) { animation-delay: 0.24s; }
/* ...etc */
```

### 2.2 Modal Entrance — `modalPop`

**Use for:** All modal dialog boxes.

```css
@keyframes modalPop {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.985);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Usage */
.modal-box {
  animation: modalPop 0.18s ease-out;
}
```

### 2.3 Status Pulse — `statusPulse`

**Use for:** Live status indicators (dots, badges).

```css
@keyframes statusPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.55; }
}

/* Usage */
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #2E7D32;
  animation: statusPulse 2s ease-in-out infinite;
}
```

### 2.4 Glow Pulse — `pulseGlow`

**Use for:** Drawing attention to an element temporarily.

```css
@keyframes pulseGlow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(12, 75, 52, 0); }
  50% { box-shadow: 0 0 0 6px rgba(12, 75, 52, 0.07); }
}
```

### 2.5 Transition Timing

| Context | Duration | Easing |
|---------|----------|--------|
| **Hover lift** | `0.28s` | `cubic-bezier(.4,0,.2,1)` |
| **Button press** | `0.22s` | `cubic-bezier(.4,0,.2,1)` |
| **Background change** | `0.15s` | `ease` |
| **Sidebar expand** | `0.4s` | `ease` |
| **Modal entrance** | `0.18s` | `ease-out` |
| **Page entrance** | `0.4s – 0.45s` | `ease-out` |

---

## 3. Component Patterns

### 3.1 Cards

All cards follow this base pattern:

```css
.card {
  background: #ffffff;
  border-radius: 16px – 18px;
  border: 1px solid rgba(12, 75, 52, 0.14);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
  padding: 22px 24px;
  transition: all 0.28s cubic-bezier(.4,0,.2,1);
  animation: fadeSlideUp 0.45s ease-out both;
  position: relative;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
  border-color: rgba(12, 75, 52, 0.25);
}

/* Dark mode */
html[data-theme="dark"] .card {
  background-color: #1e1e1e !important;
  border-color: #333 !important;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
}

html[data-theme="dark"] .card:hover {
  border-color: #444 !important;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4) !important;
}
```

#### Top Accent Stripe

Cards can have a gradient top stripe for visual identity:

```css
.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
  border-radius: 18px 18px 0 0;
}
```

### 3.2 Icon Containers

Used inside cards for visual identity:

```css
.icon-container {
  width: 44px – 46px;
  height: 44px – 46px;
  border-radius: 12px – 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: rgba(12, 75, 52, 0.08);
  color: #0C4B34;
}

.icon-container svg {
  width: 22px;
  height: 22px;
}

/* Dark mode */
html[data-theme="dark"] .icon-container {
  background: rgba(255, 255, 255, 0.06) !important;
  color: #81C784 !important;
}
```

### 3.3 Section Headers

Every content section uses a consistent label pattern:

```html
<div class="section-header">
    <div class="label-bar"></div>
    <div>
        <h3>Section Title</h3>
        <p>Short description of this section</p>
    </div>
</div>
```

```css
.section-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 16px;
}

.label-bar {
  width: 4px;
  height: 20px;
  border-radius: 2px;
  background: linear-gradient(180deg, #0C4B34 0%, #0F7A53 100%);
  flex-shrink: 0;
  margin-top: 2px;
}

.section-header h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0C4B34;
  margin: 0 0 4px 0;
  font-family: 'TT Interphases', sans-serif;
}

.section-header p {
  font-size: 12px;
  color: rgba(17, 24, 39, 0.5);
  margin: 0;
  font-weight: 600;
}

/* Dark mode */
html[data-theme="dark"] .section-header h3 {
  color: #e0e0e0 !important;
}
```

### 3.4 Stat Pills / Badges

Small inline metadata indicators:

```css
.stat-pill {
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

.stat-pill strong {
  color: #0C4B34;
  font-weight: 800;
  font-size: 12px;
}
```

### 3.5 Code Badges

For department codes, category tags, etc.:

```css
.code-badge {
  display: inline-block;
  padding: 5px 9px;
  border-radius: 8px;
  color: #ffffff;
  font-weight: 800;
  font-size: 11px;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  /* background-color set dynamically via inline style */
}
```

---

## 4. Buttons

### 4.1 Primary Button

```css
.btn-primary {
  background: #0C4B34;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.2px;
  transition: all 0.22s cubic-bezier(.4,0,.2,1);
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-primary:hover {
  background: #0a3a28;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

.btn-primary:active {
  transform: translateY(0) scale(0.98);
}

/* Dark mode */
html[data-theme="dark"] .btn-primary {
  background: #0F7A53 !important;
}
```

### 4.2 Cancel / Secondary Button

```css
.btn-cancel {
  background-color: #C9C9C9;
  color: black;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  height: 50px;
  transition: background-color 0.3s;
}

.btn-cancel:hover {
  background-color: #B9B9B9;
}
```

### 4.3 Create / Confirm Button (Modals)

```css
.btn-create {
  background-color: #0F7A53;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  height: 50px;
  transition: background-color 0.3s;
}

.btn-create:hover:enabled {
  background-color: #0a5f42;
}

.btn-create:disabled {
  background-color: #A5A5A5;
  color: black;
  cursor: not-allowed;
}
```

### 4.4 Ghost / Link Button

```css
.btn-ghost {
  background: transparent;
  color: #0C4B34;
  border: none;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
}

.btn-ghost:hover {
  color: #0a3a28;
  transform: translateX(4px);
}
```

---

## 5. Modals

### 5.1 Overlay

```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
```

### 5.2 Modal Box

```css
.modal-box {
  background-color: #EFEFEF;
  padding: 25px – 30px;
  border-radius: 15px;
  width: 620px; /* adjust per modal */
  max-height: 80vh;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  animation: modalPop 0.18s ease-out;
  display: flex;
  flex-direction: column;
}
```

### 5.3 Premium Modal (Department Details style)

For larger, detail-heavy modals:

```css
.premium-modal {
  width: min(980px, calc(100vw - 28px));
  max-height: min(84vh, 780px);
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.18);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.22);
  display: flex;
  flex-direction: column;
  animation: modalPop 0.18s ease-out;
}
```

### 5.4 Modal Header

```css
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 16px 18px;
  background: linear-gradient(0deg, rgba(12, 75, 52, 0.08), rgba(12, 75, 52, 0.08)), #ffffff;
  border-bottom: 1px solid rgba(12, 75, 52, 0.14);
}

.modal-title {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  margin: 0;
}
```

### 5.5 Modal Close Button

```css
.modal-close {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(12, 75, 52, 0.16);
  background: rgba(12, 75, 52, 0.06);
  color: #0C4B34;
  font-size: 26px;
  line-height: 1;
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: background 0.15s ease, transform 0.08s ease;
  flex: none;
}

.modal-close:hover {
  background: rgba(12, 75, 52, 0.1);
}

.modal-close:active {
  transform: scale(0.98);
  background: rgba(12, 75, 52, 0.14);
}

.modal-close:focus-visible {
  outline: 2px solid rgba(12, 75, 52, 0.45);
  outline-offset: 2px;
}
```

### 5.6 Modal Content Sections

```css
.modal-section {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 14px;
}

.modal-section-title {
  margin: 0 0 10px 0;
  font-size: 14px;
  font-weight: 800;
  color: #0C4B34;
}
```

---

## 6. Tables

### 6.1 Standard Data Table

```css
.data-table-container {
  background-color: #ffffff;
  padding: 22px 24px;
  border-radius: 18px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(12, 75, 52, 0.12);
  overflow-x: auto;
  transition: all 0.28s cubic-bezier(.4,0,.2,1);
  animation: fadeSlideUp 0.45s ease-out both;
}

.data-table-container:hover {
  box-shadow: 0 12px 36px rgba(12, 75, 52, 0.1);
}

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

table th {
  text-align: left;
  padding: 10px 14px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(17, 24, 39, 0.45);
  text-transform: uppercase;
  letter-spacing: 0.6px;
  border-bottom: 1px solid rgba(12, 75, 52, 0.08);
}

table td {
  padding: 13px 14px;
  font-size: 13px;
  color: #333;
  font-weight: 500;
  border-bottom: 1px solid rgba(12, 75, 52, 0.05);
}

table td:first-child {
  font-weight: 700;
  color: #111827;
}

table tbody tr {
  transition: background-color 0.15s ease;
}

table tbody tr:hover {
  background-color: rgba(12, 75, 52, 0.03);
}

table tbody tr:nth-child(even) {
  background-color: rgba(12, 75, 52, 0.015);
}

table tbody tr:last-child td {
  border-bottom: none;
}
```

---

## 7. Form Inputs

### 7.1 Text Inputs

```css
input[type="text"],
input[type="email"],
input[type="password"],
textarea,
select {
  background-color: #FFFFFF;
  border: 1px solid #ccc;
  border-radius: 12px;
  height: 50px;
  padding: 0 12px;
  font-size: 14px;
  font-family: 'TT Interphases', sans-serif;
  box-sizing: border-box;
  width: 100%;
  transition: border-color 0.2s ease;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #0C4B34;
  outline: none;
}

input:disabled,
select:disabled {
  background-color: #f5f5f5;
  color: #999;
  cursor: not-allowed;
  opacity: 0.6;
}
```

### 7.2 Search Bar

```css
.search-bar {
  display: flex;
  align-items: center;
  background-color: #FFFFFF;
  height: 38px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid #e0e0e0;
}

.search-bar input {
  border: none;
  outline: none;
  flex: 1;
  font-size: 14px;
  background: transparent;
}
```

### 7.3 Toggle Switch

```css
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 22px;
}

.switch input { opacity: 0; width: 0; height: 0; }

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #ccc;
  transition: background 0.3s;
  border-radius: 32px;
}

.slider::before {
  content: "";
  position: absolute;
  height: 16px;
  width: 16px;
  left: 3px;
  top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.3s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}

.switch input:checked + .slider {
  background-color: #0F7A53;
}

.switch input:checked + .slider::before {
  transform: translateX(18px);
}
```

---

## 8. Empty States

When a list, table, or section has no data:

```css
.empty-state {
  padding: 32px 16px;
  text-align: center;
  color: rgba(17, 24, 39, 0.4);
  font-weight: 600;
  font-size: 13px;
}

.empty-state svg {
  display: block;
  margin: 0 auto 12px;
  opacity: 0.3;
}
```

For bordered empty containers:

```css
.empty-dashed {
  padding: 10px;
  border-radius: 12px;
  background: rgba(12, 75, 52, 0.06);
  border: 1px dashed rgba(12, 75, 52, 0.2);
  color: rgba(17, 24, 39, 0.7);
  font-style: italic;
}
```

---

## 9. Error & Success States

### 9.1 Error Card

```css
.error-card {
  padding: 20px 14px;
  text-align: center;
  color: #b91c1c;
  background: rgba(185, 28, 28, 0.06);
  border: 1px solid rgba(185, 28, 28, 0.14);
  border-radius: 12px;
  font-weight: 700;
}
```

### 9.2 Key-Value Grid

For displaying label-value pairs inside modals or detail views:

```css
.kv-grid {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 8px 12px;
  align-items: center;
}

.kv-label {
  color: rgba(17, 24, 39, 0.62);
  font-weight: 700;
  font-size: 12px;
}

.kv-value {
  color: #111827;
  font-weight: 700;
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

---

## 10. Responsive Breakpoints

| Breakpoint | Behavior |
|------------|----------|
| `≤ 1100px` | Grid columns reduce (4 → 2 for cards, 3 → 2 for overview) |
| `≤ 768px` | Single column everything. Stack headers. Reduce padding. |
| `≤ 640px` | Modal stat grids go single column. Lists go single column. |

---

## 11. Spacing & Layout Rules

| Spacing | Value | Usage |
|---------|-------|-------|
| **Section gap** | `28px` | Between major dashboard sections |
| **Card gap** | `16px – 18px` | Between cards in a grid |
| **Card padding** | `22px 24px` | Standard card internal padding |
| **Section header margin-bottom** | `16px` | Below every section header |
| **Border radius (cards)** | `16px – 18px` | Cards and containers |
| **Border radius (buttons)** | `10px` | All buttons |
| **Border radius (inputs)** | `12px` | Text inputs, selects |
| **Border radius (modals)** | `15px – 18px` | Modal dialogs |
| **Border radius (badges)** | `8px – 10px` | Code badges, pills |
| **Border radius (icon containers)** | `12px – 14px` | Icon wrappers |

---

## 12. Hover & Interaction Rules

1. **Card hover:** `translateY(-3px)` + elevated shadow. Never scale up (except `scale(0.98)` for press feedback).
2. **Button hover:** `translateY(-1px)` + shadow glow. Use `:active { transform: translateY(0) scale(0.98); }` for press feedback.
3. **Ghost button hover:** `translateX(4px)` for directional links (e.g., "View All →").
4. **Table row hover:** `background-color: rgba(12, 75, 52, 0.03)` — never bold, just tint.
5. **Modal close hover:** Background opacity increase only.
6. **Do not** use `transform: scale(1.01)` on hover — removed in favor of `translateY(-3px)` only.

---

## 13. Accessibility

1. All interactive cards must have `tabindex="0"` and `aria-label`.
2. `:focus-visible` uses `outline: 2px solid #0C4B34; outline-offset: 2px;` (not box-shadow).
3. All buttons must have `cursor: pointer`.
4. Color contrast: ensure all text meets WCAG AA (4.5:1 for body, 3:1 for large text).
5. Dark mode: always provide `html[data-theme="dark"]` overrides for new components.
