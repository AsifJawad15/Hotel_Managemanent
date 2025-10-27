<?php
session_start();
require_once '../../includes/db_connect.php';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hotel_id = intval($_POST['hotel_id']);
        $service_name = esc($_POST['service_name']);
        $service_type = esc($_POST['service_type']);
        $price = floatval($_POST['price']);
        $status = esc($_POST['status']);
        
        // Query #3: INSERT - Add new service
        $sql = "INSERT INTO services (hotel_id, service_name, service_type, price, status) 
                VALUES ($hotel_id, '$service_name', '$service_type', $price, '$status')";
        
        if ($conn->query($sql)) {
            $success = "Service added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $service_id = intval($_POST['service_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $service_name = esc($_POST['service_name']);
        $service_type = esc($_POST['service_type']);
        $price = floatval($_POST['price']);
        $status = esc($_POST['status']);
        
        // Query #4: UPDATE - Update service details
        $sql = "UPDATE services SET hotel_id=$hotel_id, service_name='$service_name', 
                service_type='$service_type', price=$price, status='$status' WHERE service_id=$service_id";
        
        if ($conn->query($sql)) {
            $success = "Service updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $service_id = intval($_POST['service_id']);
        // Query #5: DELETE - Remove service
        $sql = "DELETE FROM services WHERE service_id=$service_id";
        
        if ($conn->query($sql)) {
            $success = "Service deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Query #1: INNER JOIN - Display services with hotel details
$services = $conn->query("
    SELECT s.*, h.hotel_name 
    FROM services s
    INNER JOIN hotels h ON s.hotel_id = h.hotel_id
    ORDER BY s.service_id DESC
");

// Query #2: SELECT - Get active hotels for dropdown
$hotels = $conn->query("SELECT hotel_id, hotel_name FROM hotels WHERE status='Active' ORDER BY hotel_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../css/admin_theme.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-concierge" style="color: #93BFC7;"></i> Manage Services</h2>
                    <a href="../../index.php" class="btn btn-secondary"><i class="bi bi-house-fill"></i> Dashboard</a>
                </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add New Service
        </button>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hotel</th>
                                <th>Service Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['service_id'] ?></td>
                                <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                <td><strong><?= htmlspecialchars($row['service_name']) ?></strong></td>
                                <td><span class="badge bg-info"><?= $row['service_type'] ?></span></td>
                                <td>$<?= number_format($row['price'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editService(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteService(<?= $row['service_id'] ?>)">
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

    <!-- Query Sidebar -->
    <div class="query-sidebar" id="querySidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color: var(--secondary-light);"><i class="bi bi-code-square"></i> SQL Queries</h5>
            <button class="btn btn-sm btn-danger" onclick="toggleQuerySidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #1</div>
            <div class="query-title-text">INNER JOIN - Display services with hotel details</div>
            <div class="query-sql">SELECT s.*, h.hotel_name 
FROM services s
INNER JOIN hotels h 
  ON s.hotel_id = h.hotel_id
ORDER BY s.service_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #2</div>
            <div class="query-title-text">SELECT - Get active hotels for dropdown</div>
            <div class="query-sql">SELECT hotel_id, hotel_name 
FROM hotels 
WHERE status='Active' 
ORDER BY hotel_name</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #3</div>
            <div class="query-title-text">INSERT - Add new service</div>
            <div class="query-sql">INSERT INTO services 
  (hotel_id, service_name, 
   service_type, price, status) 
VALUES (?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #4</div>
            <div class="query-title-text">UPDATE - Update service information</div>
            <div class="query-sql">UPDATE services 
SET hotel_id=?, service_name=?, 
    service_type=?, price=?, 
    status=? 
WHERE service_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #5</div>
            <div class="query-title-text">DELETE - Remove service</div>
            <div class="query-sql">DELETE FROM services 
WHERE service_id=?</div>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button class="query-toggle-btn" onclick="toggleQuerySidebar()" title="Toggle SQL Queries (Press Q)">
        <i class="bi bi-code-slash" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Hotel *</label>
                            <select name="hotel_id" class="form-control" required>
                                <option value="">Select Hotel</option>
                                <?php 
                                $hotels->data_seek(0);
                                while ($h = $hotels->fetch_assoc()): 
                                ?>
                                <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['hotel_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Name *</label>
                            <input type="text" name="service_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type *</label>
                            <select name="service_type" class="form-control" required>
                                <option value="Spa">Spa</option>
                                <option value="Restaurant">Restaurant</option>
                                <option value="Room Service">Room Service</option>
                                <option value="Transport">Transport</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="service_id" id="edit_service_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Hotel *</label>
                            <select name="hotel_id" id="edit_hotel_id" class="form-control" required>
                                <?php 
                                $hotels->data_seek(0);
                                while ($h = $hotels->fetch_assoc()): 
                                ?>
                                <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['hotel_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Name *</label>
                            <input type="text" name="service_name" id="edit_service_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type *</label>
                            <select name="service_type" id="edit_service_type" class="form-control" required>
                                <option value="Spa">Spa</option>
                                <option value="Restaurant">Restaurant</option>
                                <option value="Room Service">Room Service</option>
                                <option value="Transport">Transport</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editService(service) {
            document.getElementById('edit_service_id').value = service.service_id;
            document.getElementById('edit_hotel_id').value = service.hotel_id;
            document.getElementById('edit_service_name').value = service.service_name;
            document.getElementById('edit_service_type').value = service.service_type;
            document.getElementById('edit_price').value = service.price;
            document.getElementById('edit_status').value = service.status;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteService(id) {
            if (confirm('Are you sure you want to delete this service?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="service_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="../../js/query_sidebar.js"></script>
</body>
</html>
