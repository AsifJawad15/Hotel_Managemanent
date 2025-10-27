<?php
session_start();
require_once '../../includes/db_connect.php';

// Initialize variables
$search_query = '';
$filter_city = '';
$success = null;
$error = null;

// Handle search and filters
// Query #1: SELECT - Search and filter customers (with dynamic WHERE clauses)
$sql = "SELECT * FROM customers WHERE 1=1";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR address LIKE ? OR id_number LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $types .= "sssss";
}

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $filter_city = $_GET['city'];
    $sql .= " AND address LIKE ?";
    $params[] = "%$filter_city%";
    $types .= "s";
}

$sql .= " ORDER BY customer_id DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $customers = $stmt->get_result();
} else {
    $customers = $conn->query($sql);
}

// Query #2: SELECT - Get unique cities from customer addresses
$cities_result = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(TRIM(address), ',', -1) as city FROM customers ORDER BY city");

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $full_name = esc($_POST['full_name']);
        $email = esc($_POST['email']);
        $phone = esc($_POST['phone']);
        $address = esc($_POST['address']);
        $id_number = esc($_POST['id_number']);
        
        // Query #3: INSERT - Add new customer
        $sql_insert = "INSERT INTO customers (full_name, email, phone, address, id_number) 
                VALUES ('$full_name', '$email', '$phone', '$address', '$id_number')";
        
        if ($conn->query($sql_insert)) {
            $success = "Customer added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $customer_id = intval($_POST['customer_id']);
        $full_name = esc($_POST['full_name']);
        $email = esc($_POST['email']);
        $phone = esc($_POST['phone']);
        $address = esc($_POST['address']);
        $id_number = esc($_POST['id_number']);
        
        // Query #4: UPDATE - Update customer details
        $sql_update = "UPDATE customers SET full_name='$full_name', email='$email', phone='$phone', 
                address='$address', id_number='$id_number' WHERE customer_id=$customer_id";
        
        if ($conn->query($sql_update)) {
            $success = "Customer updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $customer_id = intval($_POST['customer_id']);
        // Query #5: DELETE - Remove customer
        $sql_delete = "DELETE FROM customers WHERE customer_id=$customer_id";
        
        if ($conn->query($sql_delete)) {
            $success = "Customer deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4E56C0 0%, #9B5DE0 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: rgba(26, 26, 46, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(78, 86, 192, 0.3);
            border: 1px solid rgba(253, 207, 250, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 48px rgba(155, 93, 224, 0.4);
        }
                .btn-primary {
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%);
            border: none;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(155, 93, 224, 0.4);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(215, 143, 238, 0.6);
            color: white;
            font-weight: 600;
        }
        .table { 
            color: #FDCFFA;
            background: rgba(255,255,255,0.03);
        }
        .table th { 
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%); 
            color: white;
            border: none;
            font-weight: 600;
        }
        .table td {
            border-color: rgba(253, 207, 250, 0.2);
            vertical-align: middle;
        }
        .table tbody tr {
            transition: all 0.3s ease;
        }
        .table tbody tr:hover {
            background: rgba(155, 93, 224, 0.2);
            transform: scale(1.01);
        }
        .query-sidebar {
            position: fixed;
            right: -400px;
            top: 0;
            width: 400px;
            height: 100vh;
            background: #0d1117;
            box-shadow: -5px 0 25px rgba(0,0,0,0.5);
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            z-index: 1050;
            overflow-y: auto;
            padding: 20px;
        }
        .query-sidebar.show {
            right: 0;
        }
        .query-toggle-btn {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 20px rgba(155, 93, 224, 0.5);
            z-index: 1040;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        .query-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(215, 143, 238, 0.8);
        }
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(155, 93, 224, 0.5);
            }
            50% {
                box-shadow: 0 4px 30px rgba(215, 143, 238, 0.8);
            }
        }
        .query-badge {
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            color: #1a1a2e;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(215, 143, 238, 0.4);
        }
        .query-title-text {
            color: #D78FEE;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            font-style: italic;
        }
        .query-sql {
            background: #0d0d1f;
            color: #FDCFFA;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            padding: 14px;
            border-radius: 8px;
            overflow-x: auto;
            line-height: 1.6;
            white-space: pre;
            border-left: 4px solid #D78FEE;
            margin-bottom: 20px;
        }
        .search-box, .filter-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
            border-radius: 10px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .search-box:focus, .filter-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: #9B5DE0;
            box-shadow: 0 0 20px rgba(155, 93, 224, 0.5);
            color: #FDCFFA;
        }
        .filter-select option {
            background: #1a1a2e;
            color: #FDCFFA;
        }
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2541 100%);
            color: #FDCFFA;
            border: 1px solid rgba(155, 93, 224, 0.3);
        }
        .modal-header {
            border-bottom: 1px solid rgba(155, 93, 224, 0.3);
        }
        .modal-footer {
            border-top: 1px solid rgba(155, 93, 224, 0.3);
        }
        .form-control, .form-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: #9B5DE0;
            color: #FDCFFA;
            box-shadow: 0 0 0 0.2rem rgba(155, 93, 224, 0.25);
        }
        .form-label {
            color: #D78FEE;
            font-weight: 500;
        }
        .alert {
            border-radius: 15px;
            border: none;
            animation: slideInDown 0.5s ease;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        h2 {
            color: #D78FEE;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(215, 143, 238, 0.5);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people-fill"></i> Manage Customers</h2>
                    <a href="../../index.php" class="btn btn-secondary">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </div>

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search and Filter Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-search text-white"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control search-box border-start-0" 
                                           placeholder="Search by name, email, phone, address, ID number..." 
                                           value="<?= htmlspecialchars($search_query) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="city" class="form-select filter-select">
                                    <option value="">All Cities</option>
                                    <?php while ($city_row = $cities_result->fetch_assoc()): ?>
                                        <?php if (!empty($city_row['city'])): ?>
                                        <option value="<?= trim($city_row['city']) ?>" <?= $filter_city == trim($city_row['city']) ? 'selected' : '' ?>>
                                            <?= trim($city_row['city']) ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel-fill"></i> Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Customer Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New Customer
                </button>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>ID Number</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $customers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['customer_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td><?= htmlspecialchars($row['address']) ?></td>
                                        <td><?= htmlspecialchars($row['id_number']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['registration_date'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editCustomer(<?= json_encode($row) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?= $row['customer_id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Query Sidebar -->
    <div class="query-sidebar" id="querySidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color: #D78FEE;"><i class="bi bi-code-square"></i> SQL Queries</h5>
            <button class="btn btn-sm btn-danger" onclick="toggleQuerySidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="mb-3">
            <div class="query-badge">#1 SELECT (All Customers)</div>
            <div class="query-title-text">Purpose: Display all customers</div>
            <div class="query-sql">SELECT * FROM customers
ORDER BY customer_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#2 SEARCH Query</div>
            <div class="query-title-text">Purpose: Search customers by multiple fields</div>
            <div class="query-sql">SELECT * FROM customers
WHERE full_name LIKE '%search%'
   OR email LIKE '%search%'
   OR phone LIKE '%search%'
   OR address LIKE '%search%'
   OR id_number LIKE '%search%'
ORDER BY customer_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#3 FILTER by City</div>
            <div class="query-title-text">Purpose: Filter customers by city (from address)</div>
            <div class="query-sql">SELECT * FROM customers
WHERE address LIKE '%city%'
ORDER BY customer_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#4 Get Unique Cities</div>
            <div class="query-title-text">Purpose: Extract unique cities from address</div>
            <div class="query-sql">SELECT DISTINCT 
  SUBSTRING_INDEX(TRIM(address), ',', -1) as city
FROM customers
ORDER BY city</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#5 INSERT Customer</div>
            <div class="query-title-text">Purpose: Add new customer</div>
            <div class="query-sql">INSERT INTO customers 
(full_name, email, phone, address, id_number) 
VALUES (?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#6 UPDATE Customer</div>
            <div class="query-title-text">Purpose: Update customer information</div>
            <div class="query-sql">UPDATE customers 
SET full_name=?, email=?, phone=?,
    address=?, id_number=?
WHERE customer_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#7 DELETE Customer</div>
            <div class="query-title-text">Purpose: Delete customer</div>
            <div class="query-sql">DELETE FROM customers
WHERE customer_id=?</div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="query-toggle-btn" onclick="toggleQuerySidebar()" title="Show/Hide SQL Queries">
        <i class="bi bi-code-slash fs-4 text-dark"></i>
    </button>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID Number</label>
                            <input type="text" name="id_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="customer_id" id="edit_customer_id">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID Number</label>
                            <input type="text" name="id_number" id="edit_id_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form method="POST" id="deleteForm" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="customer_id" id="delete_customer_id">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleQuerySidebar() {
            document.getElementById('querySidebar').classList.toggle('show');
        }

        function editCustomer(customer) {
            document.getElementById('edit_customer_id').value = customer.customer_id;
            document.getElementById('edit_full_name').value = customer.full_name;
            document.getElementById('edit_email').value = customer.email;
            document.getElementById('edit_phone').value = customer.phone;
            document.getElementById('edit_address').value = customer.address;
            document.getElementById('edit_id_number').value = customer.id_number;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteCustomer(id) {
            if (confirm('Are you sure you want to delete this customer?')) {
                document.getElementById('delete_customer_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
