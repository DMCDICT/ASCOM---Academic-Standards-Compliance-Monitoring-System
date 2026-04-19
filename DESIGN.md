# DESIGN.md

This document captures the styling implemented for the **Super Admin → College Department Management → Department Details** modal (`#departmentDetailsModal`), aligned to the app’s theme color `#0C4B34`.

## Theme Tokens (Modal)

- Primary: `#0C4B34`
- Default dept accent (fallback): `--dept-color: #0C4B34`

## Animations

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
```

## Department Details Modal Styles

```css
/* Department Details Modal (scoped to department-management) */
#departmentDetailsModal .dept-details-modal {
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
  --dept-color: #0c4b34;
}

#departmentDetailsModal .dept-details-modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 16px 18px;
  background: linear-gradient(0deg, rgba(12, 75, 52, 0.08), rgba(12, 75, 52, 0.08)), #ffffff;
  border-bottom: 1px solid rgba(12, 75, 52, 0.14);
}

#departmentDetailsModal .dept-details-modal__titlewrap {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

#departmentDetailsModal .dept-details-modal__badge {
  background: var(--dept-color, #0c4b34);
  color: #ffffff;
  font-weight: 800;
  font-size: 12px;
  letter-spacing: 0.8px;
  padding: 7px 10px;
  border-radius: 10px;
  box-shadow: 0 10px 26px rgba(12, 75, 52, 0.22);
  flex: none;
  text-transform: uppercase;
}

#departmentDetailsModal .dept-details-modal__titles {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

#departmentDetailsModal .dept-details-modal__title {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

#departmentDetailsModal .dept-details-modal__subtitle {
  font-size: 12px;
  color: rgba(17, 24, 39, 0.72);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

#departmentDetailsModal .dept-details-modal__close {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(12, 75, 52, 0.16);
  background: rgba(12, 75, 52, 0.06);
  color: #0c4b34;
  font-size: 26px;
  line-height: 1;
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: background 0.15s ease, transform 0.08s ease;
  flex: none;
}

#departmentDetailsModal .dept-details-modal__close:hover {
  background: rgba(12, 75, 52, 0.1);
}

#departmentDetailsModal .dept-details-modal__close:active {
  transform: scale(0.98);
  background: rgba(12, 75, 52, 0.14);
}

#departmentDetailsModal .dept-details-modal__close:focus-visible {
  outline: 2px solid rgba(12, 75, 52, 0.45);
  outline-offset: 2px;
}

#departmentDetailsModal .dept-details-modal__content {
  padding: 16px 18px 18px 18px;
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #ffffff;
}

#departmentDetailsModal .dept-details-modal__loading {
  padding: 28px 8px;
  text-align: center;
  color: rgba(17, 24, 39, 0.72);
  font-weight: 600;
}

#departmentDetailsModal .dept-details-modal__error {
  padding: 20px 14px;
  text-align: center;
  color: #b91c1c;
  background: rgba(185, 28, 28, 0.06);
  border: 1px solid rgba(185, 28, 28, 0.14);
  border-radius: 12px;
  font-weight: 700;
}

#departmentDetailsModal .dept-details-modal__section {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 14px 14px;
}

#departmentDetailsModal .dept-details-modal__section-title {
  margin: 0 0 10px 0;
  font-size: 14px;
  font-weight: 800;
  color: #0c4b34;
  display: flex;
  align-items: baseline;
  gap: 8px;
}

#departmentDetailsModal .dept-details-modal__count {
  font-size: 12px;
  color: rgba(17, 24, 39, 0.6);
  font-weight: 700;
}

#departmentDetailsModal .dept-details-modal__empty {
  padding: 10px 10px;
  border-radius: 12px;
  background: rgba(12, 75, 52, 0.06);
  border: 1px dashed rgba(12, 75, 52, 0.2);
  color: rgba(17, 24, 39, 0.7);
  font-style: italic;
}

#departmentDetailsModal .dept-details-modal__kv {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 8px 12px;
  align-items: center;
}

#departmentDetailsModal .dept-details-modal__k {
  color: rgba(17, 24, 39, 0.62);
  font-weight: 700;
  font-size: 12px;
}

#departmentDetailsModal .dept-details-modal__v {
  color: #111827;
  font-weight: 700;
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

#departmentDetailsModal .dept-details-modal__stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

#departmentDetailsModal .dept-details-modal__stat {
  background: rgba(12, 75, 52, 0.06);
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 14px 10px;
  text-align: center;
}

#departmentDetailsModal .dept-details-modal__stat-value {
  font-size: 22px;
  font-weight: 900;
  color: #0c4b34;
  line-height: 1.1;
}

#departmentDetailsModal .dept-details-modal__stat-label {
  margin-top: 4px;
  font-size: 12px;
  color: rgba(17, 24, 39, 0.7);
  font-weight: 700;
}

#departmentDetailsModal .dept-details-modal__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

#departmentDetailsModal .dept-details-modal__list-item {
  display: grid;
  grid-template-columns: 96px 1fr auto;
  gap: 10px;
  align-items: center;
  padding: 10px 10px;
  border-radius: 12px;
  border: 1px solid rgba(12, 75, 52, 0.1);
  background: #ffffff;
}

#departmentDetailsModal .dept-details-modal__list-code {
  font-weight: 900;
  color: rgba(17, 24, 39, 0.86);
  font-size: 12px;
  background: rgba(12, 75, 52, 0.08);
  border: 1px solid rgba(12, 75, 52, 0.14);
  padding: 6px 8px;
  border-radius: 10px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

#departmentDetailsModal .dept-details-modal__list-text {
  color: #111827;
  font-weight: 700;
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

#departmentDetailsModal .dept-details-modal__list-meta {
  color: rgba(17, 24, 39, 0.55);
  font-weight: 800;
  font-size: 12px;
  white-space: nowrap;
}

#departmentDetailsModal .dept-details-modal__list-more {
  padding: 8px 10px;
  color: rgba(17, 24, 39, 0.62);
  font-size: 12px;
  font-weight: 700;
}

@media (max-width: 640px) {
  #departmentDetailsModal .dept-details-modal__stats {
    grid-template-columns: 1fr;
  }
  #departmentDetailsModal .dept-details-modal__kv {
    grid-template-columns: 1fr;
  }
  #departmentDetailsModal .dept-details-modal__list-item {
    grid-template-columns: 1fr;
    gap: 6px;
  }
  #departmentDetailsModal .dept-details-modal__list-meta {
    justify-self: start;
  }
}
```

