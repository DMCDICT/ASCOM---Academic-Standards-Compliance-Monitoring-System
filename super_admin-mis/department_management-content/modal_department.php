<?php
// modal_add_department.php
// Add Department Modal

if (!isset($conn)) {
    require_once __DIR__ . '/../includes/db_connection.php';
}
?>

<!-- Add Department Modal -->
<div id="addDepartmentModal" class="modal-overlay" style="display: none;">
  <div class="dept-details-modal">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <h2 class="dept-details-modal__title">Add New Department</h2>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="closeAddDepartmentModal()">&times;</button>
    </div>
    
    <div class="dept-details-modal__content">
      <form id="addDepartmentForm" class="dept-form">
        <div class="dept-form__group">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Department Name <span style="color: #dc3545;">*</span></label>
          <input type="text" name="department_name" id="department_name" required placeholder="e.g., College of Computing Studies" style="width: 100%; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none; transition: all 0.2s;">
        </div>
        
        <div class="dept-form__group" style="margin-top: 12px;">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Department Code <span style="color: #dc3545;">*</span></label>
          <input type="text" name="department_code" id="department_code" required placeholder="e.g., CCS" maxlength="10" style="width: 100%; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none; text-transform: uppercase; transition: all 0.2s;">
        </div>
        
        <div class="dept-form__group" style="margin-top: 12px;">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Color Code</label>
          <div class="dept-form__row">
            <input type="color" id="colorPicker" value="#1976d2" style="width: 46px; height: 46px; padding: 0; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; cursor: pointer; flex-shrink: 0;">
            <input type="text" id="colorHex" value="#1976d2" maxlength="7" style="flex: 1; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none;">
          </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(12, 75, 52, 0.08);">
          <button type="button" onclick="closeAddDepartmentModal()" style="padding: 0 20px; height: 42px; background: rgba(12, 75, 52, 0.08); color: #0c4b34; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s;">CANCEL</button>
          <button type="submit" class="create-btn" style="padding: 0 24px; height: 42px; background: #0c4b34; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s; box-shadow: 0 4px 12px rgba(12, 75, 52, 0.2);">CREATE</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Department Modal -->
<div id="editDepartmentModal" class="modal-overlay" style="display: none;">
  <div class="dept-details-modal">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <h2 class="dept-details-modal__title">Edit Department</h2>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="closeEditDepartmentModal()">&times;</button>
    </div>
    
    <div class="dept-details-modal__content">
      <form id="editDepartmentForm" class="dept-form">
        <input type="hidden" name="department_id" id="edit_dept_id">
        
        <div class="dept-form__group">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Department Name <span style="color: #dc3545;">*</span></label>
          <input type="text" name="department_name" id="edit_department_name" required style="width: 100%; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none; transition: all 0.2s;">
        </div>
        
        <div class="dept-form__group" style="margin-top: 12px;">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Department Code <span style="color: #dc3545;">*</span></label>
          <input type="text" name="department_code" id="edit_department_code" required maxlength="10" style="width: 100%; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none; text-transform: uppercase; transition: all 0.2s;">
        </div>
        
        <div class="dept-form__group" style="margin-top: 12px;">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Color Code</label>
          <div class="dept-form__row">
            <input type="color" id="edit_color_picker" value="#1976d2" style="width: 46px; height: 46px; padding: 0; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; cursor: pointer; flex-shrink: 0;">
            <input type="text" id="edit_color_hex" value="#1976d2" maxlength="7" style="flex: 1; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none;">
          </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(12, 75, 52, 0.08);">
          <button type="button" onclick="closeEditDepartmentModal()" style="padding: 0 20px; height: 42px; background: rgba(12, 75, 52, 0.08); color: #0c4b34; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s;">CANCEL</button>
          <button type="submit" class="create-btn" style="padding: 0 24px; height: 42px; background: #0c4b34; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s; box-shadow: 0 4px 12px rgba(12, 75, 52, 0.2);">UPDATE</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div id="deptSuccessModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
  <div style="max-width: 400px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="margin-bottom: 20px;">
      <span style="font-size: 60px;">✓</span>
    </div>
    <h2 style="color: #28a745; margin-bottom: 15px;">Success!</h2>
    <p id="deptSuccessMessage" style="font-size: 16px; margin-bottom: 25px;">Department created successfully!</p>
    <button onclick="closeDeptSuccessModal()" style="min-width: 120px; height: 45px; background-color: #28a745; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px;">OK</button>
  </div>
</div>

<!-- Error Modal -->
<div id="deptErrorModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
  <div style="max-width: 400px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="margin-bottom: 20px;">
      <span style="font-size: 60px; color: #dc3545;">✕</span>
    </div>
    <h2 style="color: #dc3545; margin-bottom: 15px;">Error!</h2>
    <p id="deptErrorMessage" style="font-size: 16px; margin-bottom: 25px;">An error occurred.</p>
    <button onclick="closeDeptErrorModal()" style="min-width: 120px; height: 45px; background-color: #dc3545; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px;">OK</button>
  </div>
</div>

<script>
function closeAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function closeEditDepartmentModal() {
    const modal = document.getElementById('editDepartmentModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function closeDeptSuccessModal() {
    const modal = document.getElementById('deptSuccessModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    location.reload();
}

function closeDeptErrorModal() {
    const modal = document.getElementById('deptErrorModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Initialize color pickers
document.addEventListener('DOMContentLoaded', function() {
    // Add form color picker
    const addColorPicker = document.getElementById('colorPicker');
    const addColorHex = document.getElementById('colorHex');
    if (addColorPicker && addColorHex) {
        addColorPicker.addEventListener('input', function() {
            addColorHex.value = this.value.toUpperCase();
        });
        addColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                addColorPicker.value = this.value;
            }
        });
    }
    
    // Edit form color picker
    const editColorPicker = document.getElementById('edit_color_picker');
    const editColorHex = document.getElementById('edit_color_hex');
    if (editColorPicker && editColorHex) {
        editColorPicker.addEventListener('input', function() {
            editColorHex.value = this.value.toUpperCase();
        });
        editColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                editColorPicker.value = this.value;
            }
        });
    }
    
    // Add form submission
    const addForm = document.getElementById('addDepartmentForm');
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(addForm);
            formData.append('color_code', document.getElementById('colorHex').value);
            
            try {
                const response = await fetch('./api/add_department.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('deptSuccessMessage').textContent = result.message;
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    closeAddDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = result.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            } catch (error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            }
        });
    }
    
    // Edit form submission
    const editForm = document.getElementById('editDepartmentForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(editForm);
            formData.append('color_code', document.getElementById('edit_color_hex').value);
            
            try {
                const response = await fetch('./api/edit_department.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('deptSuccessMessage').textContent = result.message;
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    closeEditDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = result.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            } catch (error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            }
        });
    }
});
</script>

<!-- Assign Dean Modal -->
<div id="assignDeanModal" class="modal-overlay" style="display: none;">
  <div class="dept-details-modal">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <h2 class="dept-details-modal__title">Assign Dean</h2>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="closeAssignDeanModal()">&times;</button>
    </div>
    
    <div class="dept-details-modal__content">
      <div style="margin-bottom: 12px; padding: 12px; background: rgba(12, 75, 52, 0.04); border-radius: 10px; border: 1px solid rgba(12, 75, 52, 0.1);">
        <span id="assignDeanDeptName" style="font-size: 14px; font-weight: 800; color: #0C4B34;"></span>
      </div>
      
      <form id="assignDeanForm" class="dept-form">
        <input type="hidden" name="department_id" id="assign_dept_id">
        
        <div class="dept-form__group">
          <label style="font-size: 13px; font-weight: 800; color: #0C4B34; margin-bottom: 4px;">Select Teacher/Faculty <span style="color: #dc3545;">*</span></label>
          <select name="user_id" id="assign_user_id" required style="width: 100%; height: 46px; padding: 0 14px; border: 1px solid rgba(12, 75, 52, 0.2); border-radius: 12px; background: rgba(12, 75, 52, 0.03); color: #111827; font-size: 14px; font-weight: 600; outline: none; transition: all 0.2s; cursor: pointer;">
            <option value="">-- Select Teacher --</option>
          </select>
        </div>
        
        <div id="currentDeanSection" style="display: none; background: #fff3cd; padding: 14px; border-radius: 12px; border: 1px solid #ffeeba; margin-top: 12px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <strong style="color: #856404; font-size: 13px;">Current Dean:</strong>
              <div id="currentDeanName" style="color: #856404; font-weight: 700; font-size: 14px; margin-top: 2px;"></div>
            </div>
            <button type="button" onclick="removeDean()" style="padding: 6px 14px; color: #dc3545; background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; transition: all 0.2s;">Remove</button>
          </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(12, 75, 52, 0.08);">
          <button type="button" onclick="closeAssignDeanModal()" style="padding: 0 20px; height: 42px; background: rgba(12, 75, 52, 0.08); color: #0c4b34; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s;">CANCEL</button>
          <button type="submit" class="create-btn" style="padding: 0 24px; height: 42px; background: #0c4b34; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: all 0.2s; box-shadow: 0 4px 12px rgba(12, 75, 52, 0.2);">ASSIGN</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAssignDeanModal(deptId, deptCode) {
    document.getElementById('assign_dept_id').value = deptId;
    document.getElementById('assignDeanDeptName').textContent = 'Department: ' + deptCode;
    
    // Show current dean info if exists
    const currentDeanSection = document.getElementById('currentDeanSection');
    const deptCard = document.querySelector(`.department-card[data-dept-id="${deptId}"]`);
    if (deptCard) {
        const deanName = deptCard.querySelector('.dean-name');
        if (deanName && deanName.textContent.trim()) {
            document.getElementById('currentDeanName').textContent = deanName.textContent;
            currentDeanSection.style.display = 'block';
        } else {
            currentDeanSection.style.display = 'none';
        }
    }
    
    // Fetch teachers for this department
    fetch('./api/get_department_teachers.php?department_id=' + deptId)
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('assign_user_id');
        select.innerHTML = '<option value="">-- Select Teacher --</option>';
        
        if (data.success && data.teachers) {
            data.teachers.forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = teacher.first_name + ' ' + teacher.last_name + ' (' + teacher.employee_no + ')';
                select.appendChild(option);
            });
        }
        
        if (select.options.length <= 1) {
            select.innerHTML = '<option value="">No teachers available</option>';
        }
    });
    
    document.getElementById('assignDeanModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAssignDeanModal() {
    document.getElementById('assignDeanModal').style.display = 'none';
    document.body.style.overflow = '';
}

function removeDean() {
    const deptId = document.getElementById('assign_dept_id').value;
    if (confirm('Are you sure you want to remove this dean?')) {
        fetch('./api/remove_dean.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'department_id=' + deptId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('deptSuccessMessage').textContent = 'Dean removed successfully!';
                document.getElementById('deptSuccessModal').style.display = 'flex';
                closeAssignDeanModal();
            } else {
                document.getElementById('deptErrorMessage').textContent = data.message;
                document.getElementById('deptErrorModal').style.display = 'flex';
            }
        });
    }
}

// Handle assign dean form submission
document.addEventListener('DOMContentLoaded', function() {
    const assignDeanForm = document.getElementById('assignDeanForm');
    if (assignDeanForm) {
        assignDeanForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(assignDeanForm);
            
            try {
                const response = await fetch('./api/assign_dean.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('deptSuccessMessage').textContent = result.message;
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    closeAssignDeanModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = result.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            } catch (error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            }
        });
    }
});
</script>