<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laboratory Equipment Inventory System Prototype</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">

  <!-- Header Banner -->
  <header>
    <div class="header-title">
      <h1>🔬 Laboratory Equipment Inventory System</h1>
      <p>Systems Analysis and Design Laboratory Activity | Equipment Management Module Prototype</p>
    </div>
    <div class="header-badges">
      <span class="badge-info">SAD Lab Prototype</span>
      <span id="dbBadge" class="badge-db"><span class="dot-online"></span> Database: <?php echo strtoupper($db_type); ?></span>
    </div>
  </header>

  <!-- Dashboard Summary Cards (Bonus Feature) -->
  <section class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon total">📦</div>
      <div class="stat-info">
        <h3 id="statTotal">0</h3>
        <p>Total Equipment</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon good">✅</div>
      <div class="stat-info">
        <h3 id="statGood">0</h3>
        <p>Condition: Good</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon repair">🛠️</div>
      <div class="stat-info">
        <h3 id="statRepair">0</h3>
        <p>For Repair</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon damaged">⚠️</div>
      <div class="stat-info">
        <h3 id="statDamaged">0</h3>
        <p>Damaged</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon unserviceable">❌</div>
      <div class="stat-info">
        <h3 id="statUnserviceable">0</h3>
        <p>Unserviceable</p>
      </div>
    </div>
  </section>

  <!-- Main App Split Grid -->
  <main class="app-grid">

    <!-- PART B: Equipment Form Card -->
    <section class="card" id="formCard">
      <div class="card-header">
        <h2>📝 Equipment Entry Form</h2>
      </div>

      <form id="equipmentForm" autocomplete="off" onsubmit="return false;">
        
        <!-- Equipment ID -->
        <div class="form-group">
          <label class="form-label" for="equipment_id">Equipment ID <span class="required">*</span></label>
          <div class="input-wrapper">
            <input type="text" id="equipment_id" name="equipment_id" class="form-control" placeholder="e.g. EQ-101" required>
            <button type="button" id="btnAutoId" class="btn-auto-id" title="Generate Next Equipment ID">✨ Auto ID</button>
          </div>
        </div>

        <!-- Equipment Name -->
        <div class="form-group">
          <label class="form-label" for="equipment_name">Equipment Name <span class="required">*</span></label>
          <input type="text" id="equipment_name" name="equipment_name" class="form-control" placeholder="e.g. Digital Storage Oscilloscope" required>
        </div>

        <!-- Category -->
        <div class="form-group">
          <label class="form-label" for="category">Category <span class="required">*</span></label>
          <input type="text" id="category" name="category" class="form-control" placeholder="e.g. Electronics, Biology, Chemistry" list="categoryList" required>
          <datalist id="categoryList">
            <option value="Electronics">
            <option value="Biology">
            <option value="Chemistry">
            <option value="Physics">
            <option value="IT & Computing">
          </datalist>
        </div>

        <!-- Quantity -->
        <div class="form-group">
          <label class="form-label" for="quantity">Quantity <span class="required">*</span></label>
          <input type="number" id="quantity" name="quantity" class="form-control" placeholder="e.g. 10" min="0" required>
        </div>

        <!-- Condition Dropdown -->
        <div class="form-group">
          <label class="form-label" for="condition">Condition <span class="required">*</span></label>
          <select id="condition" name="condition" class="form-control" required>
            <option value="">-- Select Condition --</option>
            <option value="Good">Good</option>
            <option value="For Repair">For Repair</option>
            <option value="Damaged">Damaged</option>
            <option value="Unserviceable">Unserviceable</option>
          </select>
        </div>

        <!-- Laboratory -->
        <div class="form-group">
          <label class="form-label" for="laboratory">Laboratory Location <span class="required">*</span></label>
          <input type="text" id="laboratory" name="laboratory" class="form-control" placeholder="e.g. Electronics Lab 101" required>
        </div>

        <!-- Date Acquired -->
        <div class="form-group">
          <label class="form-label" for="date_acquired">Date Acquired <span class="required">*</span></label>
          <input type="date" id="date_acquired" name="date_acquired" class="form-control" required>
        </div>

        <!-- Required Action Buttons (PART B & C) -->
        <div class="btn-group">
          <button type="submit" id="btnSave" class="btn btn-primary">💾 SAVE</button>
          <button type="button" id="btnUpdate" class="btn btn-warning" disabled>✏️ UPDATE</button>
          <button type="button" id="btnDelete" class="btn btn-danger" disabled>🗑️ DELETE</button>
          <button type="button" id="btnClear" class="btn btn-secondary">🧹 CLEAR</button>
        </div>

      </form>
    </section>

    <!-- PART C & D: Equipment Records Table & Search/Filter Toolbar -->
    <section class="card">
      <div class="card-header">
        <h2>📋 Equipment Records List</h2>
        <button id="btnExportPrint" class="btn btn-outline btn-sm">🖨️ Print / Export</button>
      </div>

      <!-- Search & Filter Toolbar (PART D & BONUS) -->
      <div class="toolbar">
        <div class="search-box">
          <input type="text" id="inputSearch" class="form-control" placeholder="Search by ID, Name, Category, or Lab...">
          <button id="btnSearch" class="btn btn-primary btn-sm">🔍 Search</button>
        </div>

        <div class="filter-box">
          <select id="selectFilterCategory" class="form-control" style="width: auto;">
            <option value="">All Categories</option>
          </select>

          <select id="selectFilterCondition" class="form-control" style="width: auto;">
            <option value="">All Conditions</option>
            <option value="Good">Good</option>
            <option value="For Repair">For Repair</option>
            <option value="Damaged">Damaged</option>
            <option value="Unserviceable">Unserviceable</option>
          </select>

          <select id="selectSortBy" class="form-control" style="width: auto;">
            <option value="equipment_id">Sort by ID</option>
            <option value="equipment_name">Sort by Name</option>
            <option value="quantity">Sort by Quantity</option>
            <option value="date_acquired">Sort by Date</option>
          </select>
        </div>
      </div>

      <!-- PART C Read Table -->
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Equipment</th>
              <th>Category</th>
              <th>Qty.</th>
              <th>Condition</th>
              <th>Laboratory</th>
              <th>Date Acquired</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="equipmentTableBody">
            <!-- Dynamic rows rendered by JavaScript -->
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<!-- Modal Confirmation Prompt for DELETE Operation (PART C Requirement) -->
<div id="deleteModal" class="modal-overlay">
  <div class="modal-card">
    <div class="modal-header">
      <span>⚠️</span> Confirm Deletion
    </div>
    <div class="modal-body">
      Are you sure you want to delete this equipment record?<br><br>
      <strong id="modalTargetName" style="color: #ef4444;"></strong>
      <p style="font-size: 0.8rem; color: #64748b; margin-top: 8px;">This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button type="button" id="btnCancelDelete" class="btn btn-outline">Cancel</button>
      <button type="button" id="btnConfirmDelete" class="btn btn-danger">Yes, Delete Record</button>
    </div>
  </div>
</div>

<!-- Toast Container for Notifications -->
<div id="toastContainer" class="toast-container"></div>

<script src="app.js"></script>
</body>
</html>
<?php ?>
