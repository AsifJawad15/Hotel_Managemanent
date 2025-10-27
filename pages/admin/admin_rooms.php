<?php
session_start();
require_once '../../includes/db_connect.php';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hotel_id = intval($_POST['hotel_id']);
        $room_number = esc($_POST['room_number']);
        $type_id = intval($_POST['type_id']);
        $price = floatval($_POST['price']);
        $max_occupancy = intval($_POST['max_occupancy']);
        $status = esc($_POST['status']);
        
        // Query #4: INSERT - Add new room
        $sql = "INSERT INTO rooms (hotel_id, room_number, type_id, price, max_occupancy, status) 
                VALUES ($hotel_id, '$room_number', $type_id, $price, $max_occupancy, '$status')";
        
        if ($conn->query($sql)) {
            $success = "Room added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $room_id = intval($_POST['room_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $room_number = esc($_POST['room_number']);
        $type_id = intval($_POST['type_id']);
        $price = floatval($_POST['price']);
        $max_occupancy = intval($_POST['max_occupancy']);
        $status = esc($_POST['status']);
        
        // Query #5: UPDATE - Update room details
        $sql = "UPDATE rooms SET hotel_id=$hotel_id, room_number='$room_number', type_id=$type_id, 
                price=$price, max_occupancy=$max_occupancy, status='$status' WHERE room_id=$room_id";
        
        if ($conn->query($sql)) {
            $success = "Room updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $room_id = intval($_POST['room_id']);
        // Query #6: DELETE - Remove room
        $sql = "DELETE FROM rooms WHERE room_id=$room_id";
        
        if ($conn->query($sql)) {
            $success = "Room deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Query #1: INNER JOIN - Display rooms with hotel and room type details
$rooms = $conn->query("
    SELECT r.*, h.hotel_name, rt.type_name 
    FROM rooms r
    INNER JOIN hotels h ON r.hotel_id = h.hotel_id
    INNER JOIN room_types rt ON r.type_id = rt.type_id
    ORDER BY r.room_id DESC
");

// Query #2: SELECT - Get active hotels for dropdown
$hotels = $conn->query("SELECT hotel_id, hotel_name FROM hotels WHERE status='Active' ORDER BY hotel_name");

// Query #3: SELECT - Get room types for dropdown
$room_types = $conn->query("SELECT type_id, type_name FROM room_types ORDER BY type_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../css/admin_theme.css" rel="stylesheet">
    <style>
        .badge-available { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; }
        .badge-occupied { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; }
        .badge-maintenance { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-door-open-fill" style="color: #93BFC7;"></i> Manage Rooms</h2>
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
            <i class="bi bi-plus-circle"></i> Add New Room
        </button>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hotel</th>
                                <th>Room Number</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Max Occupancy</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $rooms->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['room_id'] ?></td>
                                <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                <td><strong><?= htmlspecialchars($row['room_number']) ?></strong></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['type_name']) ?></span></td>
                                <td>$<?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['max_occupancy'] ?> persons</td>
                                <td>
                                    <span class="badge badge-<?= strtolower($row['status']) ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editRoom(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRoom(<?= $row['room_id'] ?>)">
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
            <div class="query-title-text">INNER JOIN - Display rooms with hotel and type details</div>
            <div class="query-sql">SELECT r.*, h.hotel_name, rt.type_name 
FROM rooms r
INNER JOIN hotels h 
  ON r.hotel_id = h.hotel_id
INNER JOIN room_types rt 
  ON r.type_id = rt.type_id
ORDER BY r.room_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #2</div>
            <div class="query-title-text">SELECT (Hotels) - Get active hotels for dropdown</div>
            <div class="query-sql">SELECT hotel_id, hotel_name 
FROM hotels 
WHERE status='Active' 
ORDER BY hotel_name</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #3</div>
            <div class="query-title-text">SELECT (Room Types) - Get room types for dropdown</div>
            <div class="query-sql">SELECT type_id, type_name 
FROM room_types 
ORDER BY type_name</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #4</div>
            <div class="query-title-text">INSERT - Add new room</div>
            <div class="query-sql">INSERT INTO rooms 
  (hotel_id, room_number, type_id, 
   price, max_occupancy, status) 
VALUES (?, ?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #5</div>
            <div class="query-title-text">UPDATE - Update room information</div>
            <div class="query-sql">UPDATE rooms 
SET hotel_id=?, room_number=?, 
    type_id=?, price=?, 
    max_occupancy=?, status=? 
WHERE room_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #6</div>
            <div class="query-title-text">DELETE - Remove room</div>
            <div class="query-sql">DELETE FROM rooms 
WHERE room_id=?</div>
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
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Room</h5>
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
                            <label class="form-label">Room Number *</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type *</label>
                            <select name="type_id" class="form-control" required>
                                <option value="">Select Type</option>
                                <?php 
                                $room_types->data_seek(0);
                                while ($rt = $room_types->fetch_assoc()): 
                                ?>
                                <option value="<?= $rt['type_id'] ?>"><?= htmlspecialchars($rt['type_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Occupancy</label>
                            <input type="number" name="max_occupancy" class="form-control" value="2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Room</button>
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
                    <input type="hidden" name="room_id" id="edit_room_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Room</h5>
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
                            <label class="form-label">Room Number *</label>
                            <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type *</label>
                            <select name="type_id" id="edit_type_id" class="form-control" required>
                                <?php 
                                $room_types->data_seek(0);
                                while ($rt = $room_types->fetch_assoc()): 
                                ?>
                                <option value="<?= $rt['type_id'] ?>"><?= htmlspecialchars($rt['type_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Occupancy</label>
                            <input type="number" name="max_occupancy" id="edit_max_occupancy" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(room) {
            document.getElementById('edit_room_id').value = room.room_id;
            document.getElementById('edit_hotel_id').value = room.hotel_id;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_type_id').value = room.type_id;
            document.getElementById('edit_price').value = room.price;
            document.getElementById('edit_max_occupancy').value = room.max_occupancy;
            document.getElementById('edit_status').value = room.status;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteRoom(id) {
            if (confirm('Are you sure you want to delete this room?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="room_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="../../js/query_sidebar.js"></script>
</body>
</html>
