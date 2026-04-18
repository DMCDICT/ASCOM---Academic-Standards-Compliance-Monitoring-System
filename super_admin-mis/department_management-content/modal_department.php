<?php
// modal_add_department.php
// Add Department Modal

if (!isset($conn)) {
    require_once __DIR__ . '/../includes/db_connection.php';
}
?>

<!-- Add Department Modal -->
<div id="addDepartmentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; max-height: 98vh; overflow-y: auto; margin: 20px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Add New Department</h2>
      <span onclick="closeAddDepartmentModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    
    <form id="addDepartmentForm" style="display: flex; flex-direction: column; gap: 15px;">
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Name <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_name" id="department_name" required placeholder="e.g., College of Computing Studies" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
      </div>
      
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Code <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_code" id="department_code" required placeholder="e.g., CCS" maxlength="10" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF; text-transform: uppercase;">
      </div>
      
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Color Code</label>
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="color" id="colorPicker" value="#1976d2" style="width: 50px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
          <input type="text" id="colorHex" value="#1976d2" maxlength="7" style="flex: 1; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="closeAddDepartmentModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" class="create-btn" style="width: 125px; height: 50px;">CREATE</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Department Modal -->
<div id="editDepartmentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; max-height: 98vh; overflow-y: auto; margin: 20px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Edit Department</h2>
      <span onclick="closeEditDepartmentModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    
    <form id="editDepartmentForm" style="display: flex; flex-direction: column; gap: 15px;">
      <input type="hidden" name="department_id" id="edit_dept_id">
      
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Name <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_name" id="edit_department_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
      </div>
      
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Code <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_code" id="edit_department_code" required maxlength="10" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF; text-transform: uppercase;">
      </div>
      
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Color Code</label>
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="color" id="edit_color_picker" value="#1976d2" style="width: 50px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
          <input type="text" id="edit_color_hex" value="#1976d2" maxlength="7" style="flex: 1; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="closeEditDepartmentModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" class="create-btn" style="width: 125px; height: 50px;">UPDATE</button>
      </div>
    </form>
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