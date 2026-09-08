<?php
// API Controller for Laboratory Equipment Inventory System
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $search = trim($_GET['search'] ?? '');
            $category = trim($_GET['category'] ?? '');
            $condition = trim($_GET['condition'] ?? '');
            $sortBy = $_GET['sort_by'] ?? 'equipment_id';
            $sortOrder = strtoupper($_GET['sort_order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

            // Allowed sort columns
            $allowedSorts = ['equipment_id', 'equipment_name', 'category', 'quantity', 'condition', 'laboratory', 'date_acquired'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'equipment_id';
            }

            // Build Supabase query
            $endpoint = '/rest/v1/equipment?select=*';
            
            // Add filters
            if ($search !== '') {
                $endpoint .= '&or=(equipment_id.ilike.' . urlencode('%' . $search . '%') . ',equipment_name.ilike.' . urlencode('%' . $search . '%') . ',category.ilike.' . urlencode('%' . $search . '%') . ',laboratory.ilike.' . urlencode('%' . $search . '%') . ',condition.ilike.' . urlencode('%' . $search . '%') . ')';
            }
            if ($category !== '') {
                $endpoint .= '&category=eq.' . urlencode($category);
            }
            if ($condition !== '') {
                $endpoint .= '&condition=eq.' . urlencode($condition);
            }
            $endpoint .= '&order=' . $sortBy . '.' . strtolower($sortOrder);

            $result = supabase_request($endpoint, 'GET');
            $records = $result['data'] ?? [];

            // Calculate Dashboard Statistics
            $total = count($records);
            $total_units = array_sum(array_column($records, 'quantity'));
            $good = count(array_filter($records, fn($r) => $r['condition'] === 'Good'));
            $repair = count(array_filter($records, fn($r) => $r['condition'] === 'For Repair'));
            $damaged = count(array_filter($records, fn($r) => $r['condition'] === 'Damaged'));
            $unserviceable = count(array_filter($records, fn($r) => $r['condition'] === 'Unserviceable'));

            // Fetch distinct categories for filter dropdown
            $categories = array_unique(array_column($records, 'category'));
            sort($categories);

            echo json_encode([
                'status' => 'success',
                'data' => $records,
                'stats' => [
                    'total' => $total,
                    'total_units' => $total_units,
                    'good' => $good,
                    'repair' => $repair,
                    'damaged' => $damaged,
                    'unserviceable' => $unserviceable
                ],
                'categories' => array_values($categories),
                'db_type' => $db_type
            ]);
            break;

        case 'get':
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'Equipment ID is required.']);
                exit;
            }
            $endpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($id) . '&limit=1';
            $result = supabase_request($endpoint, 'GET');
            $item = $result['data'][0] ?? null;
            if ($item) {
                echo json_encode(['status' => 'success', 'data' => $item]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Equipment record not found.']);
            }
            break;

        case 'create':
            $equipment_id   = trim($_POST['equipment_id'] ?? '');
            $equipment_name = trim($_POST['equipment_name'] ?? '');
            $category       = trim($_POST['category'] ?? '');
            $quantity       = $_POST['quantity'] ?? '';
            $condition      = trim($_POST['condition'] ?? '');
            $laboratory     = trim($_POST['laboratory'] ?? '');
            $date_acquired  = trim($_POST['date_acquired'] ?? '');

            // Server-side Validations
            $errors = [];

            if (empty($equipment_id)) {
                $errors[] = "Equipment ID cannot be empty.";
            } else {
                // Check uniqueness via Supabase
                $checkEndpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($equipment_id) . '&select=equipment_id';
                $checkResult = supabase_request($checkEndpoint, 'GET');
                if (!empty($checkResult['data'])) {
                    $errors[] = "Equipment ID '{$equipment_id}' already exists. Must be unique.";
                }
            }

            if (empty($equipment_name)) {
                $errors[] = "Equipment Name cannot be empty.";
            }

            if (empty($category)) {
                $errors[] = "Category cannot be empty.";
            }

            if ($quantity === '' || !is_numeric($quantity)) {
                $errors[] = "Quantity must be a valid number.";
            } else if ((int)$quantity < 0) {
                $errors[] = "Quantity cannot be negative.";
            }

            $validConditions = ['Good', 'For Repair', 'Damaged', 'Unserviceable'];
            if (empty($condition) || !in_array($condition, $validConditions)) {
                $errors[] = "Please select a valid Condition (Good, For Repair, Damaged, Unserviceable).";
            }

            if (empty($laboratory)) {
                $errors[] = "Laboratory location cannot be empty.";
            }

            if (empty($date_acquired)) {
                $errors[] = "Date Acquired cannot be empty.";
            } else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_acquired)) {
                $errors[] = "Date Acquired must be a valid date format (YYYY-MM-DD).";
            }

            if (!empty($errors)) {
                echo json_encode(['status' => 'error', 'message' => implode(' ', $errors), 'errors' => $errors]);
                exit;
            }

            // Insert via Supabase
            $data = [
                'equipment_id' => $equipment_id,
                'equipment_name' => $equipment_name,
                'category' => $category,
                'quantity' => (int)$quantity,
                'condition' => $condition,
                'laboratory' => $laboratory,
                'date_acquired' => $date_acquired
            ];
            $result = supabase_request('/rest/v1/equipment', 'POST', $data);

            if ($result['status'] >= 200 && $result['status'] < 300) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Equipment record '{$equipment_name}' (ID: {$equipment_id}) added successfully!"
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create record: ' . json_encode($result['data'])]);
            }
            break;

        case 'update':
            $equipment_id   = trim($_POST['equipment_id'] ?? '');
            $equipment_name = trim($_POST['equipment_name'] ?? '');
            $category       = trim($_POST['category'] ?? '');
            $quantity       = $_POST['quantity'] ?? '';
            $condition      = trim($_POST['condition'] ?? '');
            $laboratory     = trim($_POST['laboratory'] ?? '');
            $date_acquired  = trim($_POST['date_acquired'] ?? '');

            // Server-side Validations
            $errors = [];

            if (empty($equipment_id)) {
                $errors[] = "Equipment ID is required for update.";
            } else {
                // Check if record exists via Supabase
                $checkEndpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($equipment_id) . '&select=equipment_id';
                $checkResult = supabase_request($checkEndpoint, 'GET');
                if (empty($checkResult['data'])) {
                    $errors[] = "Equipment record with ID '{$equipment_id}' does not exist.";
                }
            }

            if (empty($equipment_name)) {
                $errors[] = "Equipment Name cannot be empty.";
            }

            if (empty($category)) {
                $errors[] = "Category cannot be empty.";
            }

            if ($quantity === '' || !is_numeric($quantity)) {
                $errors[] = "Quantity must be a valid number.";
            } else if ((int)$quantity < 0) {
                $errors[] = "Quantity cannot be negative.";
            }

            $validConditions = ['Good', 'For Repair', 'Damaged', 'Unserviceable'];
            if (empty($condition) || !in_array($condition, $validConditions)) {
                $errors[] = "Please select a valid Condition.";
            }

            if (empty($laboratory)) {
                $errors[] = "Laboratory location cannot be empty.";
            }

            if (empty($date_acquired)) {
                $errors[] = "Date Acquired cannot be empty.";
            } else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_acquired)) {
                $errors[] = "Date Acquired must be a valid date format (YYYY-MM-DD).";
            }

            if (!empty($errors)) {
                echo json_encode(['status' => 'error', 'message' => implode(' ', $errors), 'errors' => $errors]);
                exit;
            }

            // Update via Supabase
            $data = [
                'equipment_name' => $equipment_name,
                'category' => $category,
                'quantity' => (int)$quantity,
                'condition' => $condition,
                'laboratory' => $laboratory,
                'date_acquired' => $date_acquired
            ];
            $endpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($equipment_id);
            $result = supabase_request($endpoint, 'PATCH', $data);

            if ($result['status'] >= 200 && $result['status'] < 300) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Equipment record '{$equipment_id}' updated successfully!"
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update record: ' . json_encode($result['data'])]);
            }
            break;

        case 'delete':
            $equipment_id = trim($_POST['equipment_id'] ?? '');
            if (empty($equipment_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Equipment ID is required for deletion.']);
                exit;
            }

            // Get item name first via Supabase
            $checkEndpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($equipment_id) . '&select=equipment_name';
            $checkResult = supabase_request($checkEndpoint, 'GET');
            $item = $checkResult['data'][0] ?? null;

            if (!$item) {
                echo json_encode(['status' => 'error', 'message' => 'Record not found or already deleted.']);
                exit;
            }

            // Delete via Supabase
            $endpoint = '/rest/v1/equipment?equipment_id=eq.' . urlencode($equipment_id);
            $result = supabase_request($endpoint, 'DELETE');

            if ($result['status'] >= 200 && $result['status'] < 300) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Equipment record '{$item['equipment_name']}' (ID: {$equipment_id}) deleted successfully!"
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete record: ' . json_encode($result['data'])]);
            }
            break;

        case 'generate_id':
            // Generate next available ID (e.g. EQ-107)
            $endpoint = '/rest/v1/equipment?equipment_id=like.EQ-&select=equipment_id';
            $result = supabase_request($endpoint, 'GET');
            $ids = array_column($result['data'] ?? [], 'equipment_id');
            $maxNum = 100;
            foreach ($ids as $idStr) {
                if (preg_match('/EQ-(\d+)/', $idStr, $m)) {
                    $num = (int)$m[1];
                    if ($num > $maxNum) {
                        $maxNum = $num;
                    }
                }
            }
            $nextId = 'EQ-' . ($maxNum + 1);
            echo json_encode(['status' => 'success', 'next_id' => $nextId]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
