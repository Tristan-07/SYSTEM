// Laboratory Equipment Inventory System - Frontend Controller

document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const form = document.getElementById('equipmentForm');
  const inputEquipmentId = document.getElementById('equipment_id');
  const inputEquipmentName = document.getElementById('equipment_name');
  const selectCategory = document.getElementById('category');
  const inputQuantity = document.getElementById('quantity');
  const selectCondition = document.getElementById('condition');
  const inputLaboratory = document.getElementById('laboratory');
  const inputDateAcquired = document.getElementById('date_acquired');

  // Action Buttons
  const btnSave = document.getElementById('btnSave');
  const btnUpdate = document.getElementById('btnUpdate');
  const btnDelete = document.getElementById('btnDelete');
  const btnClear = document.getElementById('btnClear');
  const btnAutoId = document.getElementById('btnAutoId');

  // Toolbar Controls
  const inputSearch = document.getElementById('inputSearch');
  const btnSearch = document.getElementById('btnSearch');
  const selectFilterCategory = document.getElementById('selectFilterCategory');
  const selectFilterCondition = document.getElementById('selectFilterCondition');
  const selectSortBy = document.getElementById('selectSortBy');
  const btnExportPrint = document.getElementById('btnExportPrint');

  // Table Body & Stats
  const tableBody = document.getElementById('equipmentTableBody');
  const statTotal = document.getElementById('statTotal');
  const statGood = document.getElementById('statGood');
  const statRepair = document.getElementById('statRepair');
  const statDamaged = document.getElementById('statDamaged');
  const statUnserviceable = document.getElementById('statUnserviceable');
  const dbBadge = document.getElementById('dbBadge');

  // Modal Confirmation Elements
  const deleteModal = document.getElementById('deleteModal');
  const modalTargetName = document.getElementById('modalTargetName');
  const btnConfirmDelete = document.getElementById('btnConfirmDelete');
  const btnCancelDelete = document.getElementById('btnCancelDelete');

  let currentEditingId = null;
  let pendingDeleteId = null;

  // Initialize
  loadEquipmentList();
  setDefaultDate();

  // Helper: Set default date acquired to today
  function setDefaultDate() {
    const today = new Date().toISOString().split('T')[0];
    if (!inputDateAcquired.value) {
      inputDateAcquired.value = today;
    }
  }

  // Toast Notification System
  function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'ℹ️';
    if (type === 'success') icon = '✅';
    if (type === 'error') icon = '❌';
    if (type === 'warning') icon = '⚠️';

    toast.innerHTML = `<span>${icon}</span><span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // HTML Escaper for XSS prevention
  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Load & Render Equipment Records
  async function loadEquipmentList() {
    const search = inputSearch.value.trim();
    const category = selectFilterCategory.value;
    const condition = selectFilterCondition.value;
    const sortBy = selectSortBy.value;

    const queryParams = new URLSearchParams({
      action: 'list',
      search: search,
      category: category,
      condition: condition,
      sort_by: sortBy
    });

    try {
      const response = await fetch(`api.php?${queryParams.toString()}`);
      const result = await response.json();

      if (result.status === 'success') {
        renderTable(result.data);
        updateDashboardStats(result.stats);
        updateCategoryOptions(result.categories);
        if (dbBadge) {
          dbBadge.innerHTML = `<span class="dot-online"></span> ${result.db_type.toUpperCase()} Connected`;
        }
      } else {
        showToast(result.message || 'Failed to fetch equipment records.', 'error');
      }
    } catch (err) {
      console.error('Fetch error:', err);
      showToast('Error connecting to backend API.', 'error');
    }
  }

  // Render Table Rows
  function renderTable(items) {
    if (!items || items.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="8" class="empty-state">
            <div>🔍 No equipment records found matching your search.</div>
          </td>
        </tr>`;
      return;
    }

    tableBody.innerHTML = items.map(item => {
      const isSelected = currentEditingId === item.equipment_id;
      return `
        <tr class="${isSelected ? 'selected-row' : ''}" data-id="${escapeHtml(item.equipment_id)}">
          <td><strong>${escapeHtml(item.equipment_id)}</strong></td>
          <td>${escapeHtml(item.equipment_name)}</td>
          <td>${escapeHtml(item.category)}</td>
          <td><strong>${item.quantity}</strong></td>
          <td>${getConditionBadge(item.condition)}</td>
          <td>${escapeHtml(item.laboratory)}</td>
          <td>${escapeHtml(item.date_acquired)}</td>
          <td class="action-cell">
            <button class="btn btn-warning btn-sm btn-edit" onclick="editEquipment('${escapeHtml(item.equipment_id)}')">
              ✏️ Edit
            </button>
            <button class="btn btn-danger btn-sm btn-delete" onclick="promptDelete('${escapeHtml(item.equipment_id)}', '${escapeHtml(item.equipment_name)}')">
              🗑️ Delete
            </button>
          </td>
        </tr>`;
    }).join('');
  }

  // Format Condition Badge
  function getConditionBadge(condition) {
    let cssClass = 'badge-unserviceable';
    if (condition === 'Good') cssClass = 'badge-good';
    if (condition === 'For Repair') cssClass = 'badge-repair';
    if (condition === 'Damaged') cssClass = 'badge-damaged';
    if (condition === 'Unserviceable') cssClass = 'badge-unserviceable';

    return `<span class="badge-condition ${cssClass}">${escapeHtml(condition)}</span>`;
  }

  // Update Stats Cards
  function updateDashboardStats(stats) {
    if (!stats) return;
    statTotal.textContent = stats.total || 0;
    statGood.textContent = stats.good || 0;
    statRepair.textContent = stats.repair || 0;
    statDamaged.textContent = stats.damaged || 0;
    if (statUnserviceable) statUnserviceable.textContent = stats.unserviceable || 0;
  }

  // Populate Filter Categories
  function updateCategoryOptions(categories) {
    if (!categories) return;
    const currentVal = selectFilterCategory.value;
    selectFilterCategory.innerHTML = '<option value="">All Categories</option>' +
      categories.map(cat => `<option value="${escapeHtml(cat)}" ${cat === currentVal ? 'selected' : ''}>${escapeHtml(cat)}</option>`).join('');
  }

  // Input Validation Rules (PART D Requirement)
  function validateForm(isUpdate = false) {
    const equipmentId = inputEquipmentId.value.trim();
    const equipmentName = inputEquipmentName.value.trim();
    const category = selectCategory.value.trim();
    const quantityStr = inputQuantity.value.trim();
    const condition = selectCondition.value.trim();
    const laboratory = inputLaboratory.value.trim();
    const dateAcquired = inputDateAcquired.value.trim();

    if (!equipmentId) {
      showToast('Validation Error: Equipment ID cannot be empty.', 'error');
      inputEquipmentId.focus();
      return false;
    }

    if (!equipmentName) {
      showToast('Validation Error: Equipment Name cannot be empty.', 'error');
      inputEquipmentName.focus();
      return false;
    }

    if (!category) {
      showToast('Validation Error: Category cannot be empty.', 'error');
      selectCategory.focus();
      return false;
    }

    if (quantityStr === '' || isNaN(quantityStr)) {
      showToast('Validation Error: Quantity must be a valid number.', 'error');
      inputQuantity.focus();
      return false;
    }

    const quantityNum = parseInt(quantityStr, 10);
    if (quantityNum < 0) {
      showToast('Validation Error: Quantity cannot be negative.', 'error');
      inputQuantity.focus();
      return false;
    }

    if (!condition) {
      showToast('Validation Error: Condition must be selected.', 'error');
      selectCondition.focus();
      return false;
    }

    if (!laboratory) {
      showToast('Validation Error: Laboratory location cannot be empty.', 'error');
      inputLaboratory.focus();
      return false;
    }

    if (!dateAcquired) {
      showToast('Validation Error: Date Acquired cannot be empty.', 'error');
      inputDateAcquired.focus();
      return false;
    }

    return true;
  }

  // CREATE Operation
  btnSave.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!validateForm(false)) return;

    const formData = new FormData(form);
    formData.append('action', 'create');

    try {
      btnSave.disabled = true;
      btnSave.textContent = 'Saving...';
      const response = await fetch('api.php', { method: 'POST', body: formData });
      const result = await response.json();

      if (result.status === 'success') {
        showToast(result.message, 'success');
        clearForm();
        loadEquipmentList();
      } else {
        showToast(result.message || 'Error creating equipment.', 'error');
      }
    } catch (err) {
      showToast('Failed to save record.', 'error');
    } finally {
      btnSave.disabled = false;
      btnSave.innerHTML = '💾 SAVE';
    }
  });

  // UPDATE Operation
  btnUpdate.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!currentEditingId) {
      showToast('Please select a record from the table to edit first.', 'warning');
      return;
    }

    if (!validateForm(true)) return;

    const formData = new FormData(form);
    formData.append('action', 'update');

    try {
      btnUpdate.disabled = true;
      btnUpdate.textContent = 'Updating...';
      const response = await fetch('api.php', { method: 'POST', body: formData });
      const result = await response.json();

      if (result.status === 'success') {
        showToast(result.message, 'success');
        clearForm();
        loadEquipmentList();
      } else {
        showToast(result.message || 'Error updating record.', 'error');
      }
    } catch (err) {
      showToast('Failed to update record.', 'error');
    } finally {
      btnUpdate.disabled = false;
      btnUpdate.innerHTML = '✏️ UPDATE';
    }
  });

  // DELETE Operation Trigger from Form Button
  btnDelete.addEventListener('click', (e) => {
    e.preventDefault();
    const id = inputEquipmentId.value.trim();
    const name = inputEquipmentName.value.trim();
    if (!id) {
      showToast('Select an equipment record to delete.', 'warning');
      return;
    }
    window.promptDelete(id, name || id);
  });

  // Global Edit Function Called from Table Row Button
  window.editEquipment = async (id) => {
    try {
      const response = await fetch(`api.php?action=get&id=${encodeURIComponent(id)}`);
      const result = await response.json();

      if (result.status === 'success' && result.data) {
        const d = result.data;
        currentEditingId = d.equipment_id;
        inputEquipmentId.value = d.equipment_id;
        inputEquipmentId.readOnly = true; // Lock ID during update
        inputEquipmentName.value = d.equipment_name;
        selectCategory.value = d.category;
        inputQuantity.value = d.quantity;
        selectCondition.value = d.condition;
        inputLaboratory.value = d.laboratory;
        inputDateAcquired.value = d.date_acquired;

        // Button States
        btnSave.disabled = true;
        btnUpdate.disabled = false;
        btnDelete.disabled = false;

        showToast(`Editing Equipment Record: ${d.equipment_id}`, 'info');

        // Scroll to form if on mobile
        document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
        loadEquipmentList(); // Highlight row
      }
    } catch (err) {
      showToast('Error loading record details.', 'error');
    }
  };

  // Global Prompt Delete Modal (PART C Requirement)
  window.promptDelete = (id, name) => {
    pendingDeleteId = id;
    modalTargetName.textContent = `'${name}' (ID: ${id})`;
    deleteModal.classList.add('active');
  };

  // Modal Action: Confirm Delete
  btnConfirmDelete.addEventListener('click', async () => {
    if (!pendingDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('equipment_id', pendingDeleteId);

    try {
      const response = await fetch('api.php', { method: 'POST', body: formData });
      const result = await response.json();

      if (result.status === 'success') {
        showToast(result.message, 'success');
        if (currentEditingId === pendingDeleteId) {
          clearForm();
        }
        loadEquipmentList();
      } else {
        showToast(result.message || 'Error deleting record.', 'error');
      }
    } catch (err) {
      showToast('Failed to delete record.', 'error');
    } finally {
      deleteModal.classList.remove('active');
      pendingDeleteId = null;
    }
  });

  // Modal Action: Cancel Delete
  btnCancelDelete.addEventListener('click', () => {
    deleteModal.classList.remove('active');
    pendingDeleteId = null;
  });

  // CLEAR Form
  btnClear.addEventListener('click', (e) => {
    e.preventDefault();
    clearForm();
    showToast('Form cleared.', 'info');
  });

  function clearForm() {
    form.reset();
    inputEquipmentId.readOnly = false;
    currentEditingId = null;
    btnSave.disabled = false;
    btnUpdate.disabled = true;
    btnDelete.disabled = true;
    setDefaultDate();
    loadEquipmentList();
  }

  // AUTO-GEN ID (Bonus Requirement)
  btnAutoId.addEventListener('click', async () => {
    try {
      const response = await fetch('api.php?action=generate_id');
      const result = await response.json();
      if (result.status === 'success') {
        inputEquipmentId.value = result.next_id;
        showToast(`Auto-generated ID: ${result.next_id}`, 'info');
      }
    } catch (err) {
      showToast('Failed to generate ID.', 'error');
    }
  });

  // SEARCH & FILTER Handlers (PART D Requirement)
  btnSearch.addEventListener('click', loadEquipmentList);
  inputSearch.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') loadEquipmentList();
  });
  selectFilterCategory.addEventListener('change', loadEquipmentList);
  selectFilterCondition.addEventListener('change', loadEquipmentList);
  selectSortBy.addEventListener('change', loadEquipmentList);

  // EXPORT / PRINT (Bonus Requirement)
  btnExportPrint.addEventListener('click', () => {
    window.print();
  });
});
