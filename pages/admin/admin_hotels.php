<?php
session_start();
require_once '../../includes/db_connect.php';

// Initialize variables
$search_query = '';
$filter_city = '';
$filter_status = '';
$success = null;
$error = null;

// Handle search and filters
// Query #1: SELECT - Search and filter hotels (with dynamic WHERE clauses)
$sql = "SELECT * FROM hotels WHERE 1=1";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql .= " AND (hotel_name LIKE ? OR address LIKE ? OR city LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $types .= "sssss";
}

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $filter_city = $_GET['city'];
    $sql .= " AND city = ?";
    $params[] = $filter_city;
    $types .= "s";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filter_status = $_GET['status'];
    $sql .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$sql .= " ORDER BY hotel_id DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $hotels = $stmt->get_result();
} else {
    $hotels = $conn->query($sql);
}

// Query #2: SELECT - Get unique cities for filter dropdown
$cities = $conn->query("SELECT DISTINCT city FROM hotels ORDER BY city");

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hotel_name = $conn->real_escape_string($_POST['hotel_name']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $rating = floatval($_POST['rating']);
        $total_rooms = intval($_POST['total_rooms']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Query #3: INSERT - Add new hotel
        $sql_insert = "INSERT INTO hotels (hotel_name, address, city, phone, email, rating, total_rooms, status) 
                VALUES ('$hotel_name', '$address', '$city', '$phone', '$email', $rating, $total_rooms, '$status')";
        
        if ($conn->query($sql_insert)) {
            $success = "Hotel added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $hotel_id = intval($_POST['hotel_id']);
        $hotel_name = $conn->real_escape_string($_POST['hotel_name']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $rating = floatval($_POST['rating']);
        $total_rooms = intval($_POST['total_rooms']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Query #4: UPDATE - Update hotel details
        $sql_update = "UPDATE hotels SET hotel_name='$hotel_name', address='$address', city='$city', 
                phone='$phone', email='$email', rating=$rating, total_rooms=$total_rooms, status='$status'
                WHERE hotel_id=$hotel_id";
        
        if ($conn->query($sql_update)) {
            $success = "Hotel updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $hotel_id = intval($_POST['hotel_id']);
        // Query #5: DELETE - Remove hotel
        $sql_delete = "DELETE FROM hotels WHERE hotel_id=$hotel_id";
        
        if ($conn->query($sql_delete)) {
            $success = "Hotel deleted successfully!";
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
    <title>Manage Hotels - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #4E56C0 0%, #9B5DE0 100%); 
            min-height: 100vh; 
            color: #FDCFFA;
        }
        .card { 
            border: none; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.3); 
            border-radius: 20px; 
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(253, 207, 250, 0.2);
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .btn-primary { 
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%); 
            border: none;
            box-shadow: 0 4px 15px rgba(155, 93, 224, 0.4);
            transition: all 0.3s ease;
            color: white;
            font-weight: 600;
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(155, 93, 224, 0.6);
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
        }
        .table { 
            color: #FDCFFA;
            background: rgba(255,255,255,0.05);
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
        .badge-active { 
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            color: #4E56C0;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-inactive { 
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .query-sidebar {
            position: fixed;
            right: -400px;
            top: 0;
            width: 400px;
            height: 100vh;
            background: #1a1a2e;
            box-shadow: -5px 0 25px rgba(0,0,0,0.5);
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            z-index: 1050;
            overflow-y: auto;
            padding: 20px;
            border-left: 3px solid #9B5DE0;
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
            box-shadow: 0 6px 30px rgba(155, 93, 224, 0.8);
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
        .search-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
            border-radius: 10px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .search-box:focus {
            background: rgba(255,255,255,0.08);
            border-color: #9B5DE0;
            box-shadow: 0 0 20px rgba(155, 93, 224, 0.5);
            color: #FDCFFA;
        }
        .filter-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
            border-radius: 10px;
        }
        .filter-select:focus {
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
            <!-- Main Content -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-building-fill"></i> Manage Hotels</h2>
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
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-search text-white"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control search-box border-start-0" 
                                           placeholder="Search by name, address, city, email, phone..." 
                                           value="<?= htmlspecialchars($search_query) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="city" class="form-select filter-select">
                                    <option value="">All Cities</option>
                                    <?php while ($city_row = $cities->fetch_assoc()): ?>
                                        <option value="<?= $city_row['city'] ?>" <?= $filter_city == $city_row['city'] ? 'selected' : '' ?>>
                                            <?= $city_row['city'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select filter-select">
                                    <option value="">All Status</option>
                                    <option value="Active" <?= $filter_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $filter_status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
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

                <!-- Add Hotel Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New Hotel
                </button>

                <!-- Hotels Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hotel Name</th>
                                        <th>City</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Rating</th>
                                        <th>Total Rooms</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $hotels->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['hotel_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($row['hotel_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['city']) ?></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <?php for($i=0; $i<floor($row['rating']); $i++): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted">(<?= $row['rating'] ?>)</span>
                                        </td>
                                        <td><?= $row['total_rooms'] ?></td>
                                        <td>
                                            <span class="badge-<?= strtolower($row['status']) ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editHotel(<?= json_encode($row) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteHotel(<?= $row['hotel_id'] ?>)">
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
            <div class="query-badge">#1 SELECT (All Hotels)</div>
            <div class="query-title-text">Purpose: Display all hotels</div>
            <div class="query-sql">SELECT * FROM hotels
ORDER BY hotel_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#2 SEARCH Query</div>
            <div class="query-title-text">Purpose: Search hotels by multiple fields</div>
            <div class="query-sql">SELECT * FROM hotels
WHERE hotel_name LIKE '%search%'
   OR address LIKE '%search%'
   OR city LIKE '%search%'
   OR email LIKE '%search%'
   OR phone LIKE '%search%'
ORDER BY hotel_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#3 FILTER by City</div>
            <div class="query-title-text">Purpose: Filter hotels by city</div>
            <div class="query-sql">SELECT * FROM hotels
WHERE city = 'New York'
ORDER BY hotel_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#4 FILTER by Status</div>
            <div class="query-title-text">Purpose: Filter hotels by status</div>
            <div class="query-sql">SELECT * FROM hotels
WHERE status = 'Active'
ORDER BY hotel_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#5 Get Unique Cities</div>
            <div class="query-title-text">Purpose: Get all unique cities for filter dropdown</div>
            <div class="query-sql">SELECT DISTINCT city
FROM hotels
ORDER BY city</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#6 INSERT Hotel</div>
            <div class="query-title-text">Purpose: Add new hotel</div>
            <div class="query-sql">INSERT INTO hotels 
(hotel_name, address, city, phone, email, 
 rating, total_rooms, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#7 UPDATE Hotel</div>
            <div class="query-title-text">Purpose: Update hotel information</div>
            <div class="query-sql">UPDATE hotels 
SET hotel_name=?, address=?, city=?,
    phone=?, email=?, rating=?,
    total_rooms=?, status=?
WHERE hotel_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#8 DELETE Hotel</div>
            <div class="query-title-text">Purpose: Delete hotel</div>
            <div class="query-sql">DELETE FROM hotels
WHERE hotel_id=?</div>
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
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" name="hotel_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rating</label>
                                <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Rooms</label>
                                <input type="number" name="total_rooms" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
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
                    <h5 class="modal-title">Edit Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="hotel_id" id="edit_hotel_id">
                        <div class="mb-3">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" name="hotel_name" id="edit_hotel_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="edit_city" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rating</label>
                                <input type="number" name="rating" id="edit_rating" class="form-control" min="0" max="5" step="0.1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Rooms</label>
                                <input type="number" name="total_rooms" id="edit_total_rooms" class="form-control" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form method="POST" id="deleteForm" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="hotel_id" id="delete_hotel_id">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleQuerySidebar() {
            document.getElementById('querySidebar').classList.toggle('show');
        }

        function editHotel(hotel) {
            document.getElementById('edit_hotel_id').value = hotel.hotel_id;
            document.getElementById('edit_hotel_name').value = hotel.hotel_name;
            document.getElementById('edit_address').value = hotel.address;
            document.getElementById('edit_city').value = hotel.city;
            document.getElementById('edit_phone').value = hotel.phone;
            document.getElementById('edit_email').value = hotel.email;
            document.getElementById('edit_rating').value = hotel.rating;
            document.getElementById('edit_total_rooms').value = hotel.total_rooms;
            document.getElementById('edit_status').value = hotel.status;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteHotel(id) {
            if (confirm('Are you sure you want to delete this hotel?')) {
                document.getElementById('delete_hotel_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
