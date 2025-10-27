<?php
session_start();
require_once '../../includes/db_connect.php';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hotel_id = intval($_POST['hotel_id']);
        $full_name = esc($_POST['full_name']);
        $position = esc($_POST['position']);
        $phone = esc($_POST['phone']);
        $email = esc($_POST['email']);
        $salary = floatval($_POST['salary']);
        $hire_date = esc($_POST['hire_date']);
        $status = esc($_POST['status']);
        
        // Query #3: INSERT - Add new staff member
        $sql = "INSERT INTO staff (hotel_id, full_name, position, phone, email, salary, hire_date, status) 
                VALUES ($hotel_id, '$full_name', '$position', '$phone', '$email', $salary, '$hire_date', '$status')";
        
        if ($conn->query($sql)) {
            $success = "Staff member added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $staff_id = intval($_POST['staff_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $full_name = esc($_POST['full_name']);
        $position = esc($_POST['position']);
        $phone = esc($_POST['phone']);
        $email = esc($_POST['email']);
        $salary = floatval($_POST['salary']);
        $hire_date = esc($_POST['hire_date']);
        $status = esc($_POST['status']);
        
        // Query #4: UPDATE - Update staff information
        $sql = "UPDATE staff SET hotel_id=$hotel_id, full_name='$full_name', position='$position', 
                phone='$phone', email='$email', salary=$salary, hire_date='$hire_date', status='$status' 
                WHERE staff_id=$staff_id";
        
        if ($conn->query($sql)) {
            $success = "Staff member updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $staff_id = intval($_POST['staff_id']);
        // Query #5: DELETE - Remove staff member
        $sql = "DELETE FROM staff WHERE staff_id=$staff_id";
        
        if ($conn->query($sql)) {
            $success = "Staff member deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Query #1: INNER JOIN - Display staff with hotel details
$staff = $conn->query("
    SELECT s.*, h.hotel_name 
    FROM staff s
    INNER JOIN hotels h ON s.hotel_id = h.hotel_id
    ORDER BY s.staff_id DESC
");

// Query #2: SELECT - Get active hotels for dropdown
$hotels = $conn->query("SELECT hotel_id, hotel_name FROM hotels WHERE status='Active' ORDER BY hotel_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - SmartStay</title>
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
                    <h2><i class="bi bi-person-badge-fill" style="color: #93BFC7;"></i> Manage Staff</h2>
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
            <i class="bi bi-plus-circle"></i> Add New Staff Member
        </button>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Hotel</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Salary</th>
                                <th>Hire Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $staff->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['staff_id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['position']) ?></span></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><strong>$<?= number_format($row['salary'], 2) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($row['hire_date'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : ($row['status'] === 'On Leave' ? 'warning' : 'secondary') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editStaff(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteStaff(<?= $row['staff_id'] ?>)">
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
            <div class="query-title-text">INNER JOIN - Display staff with hotel details</div>
            <div class="query-sql">SELECT s.*, h.hotel_name 
FROM staff s
INNER JOIN hotels h 
  ON s.hotel_id = h.hotel_id
ORDER BY s.staff_id DESC</div>
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
            <div class="query-title-text">INSERT - Add new staff member</div>
            <div class="query-sql">INSERT INTO staff 
  (hotel_id, full_name, position, 
   phone, email, salary, 
   hire_date, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #4</div>
            <div class="query-title-text">UPDATE - Update staff information</div>
            <div class="query-sql">UPDATE staff 
SET hotel_id=?, full_name=?, 
    position=?, phone=?, email=?, 
    salary=?, hire_date=?, status=? 
WHERE staff_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #5</div>
            <div class="query-title-text">DELETE - Remove staff member</div>
            <div class="query-sql">DELETE FROM staff 
WHERE staff_id=?</div>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button class="query-toggle-btn" onclick="toggleQuerySidebar()" title="Toggle SQL Queries (Press Q)">
        <i class="bi bi-code-slash" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Staff Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" name="position" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary</label>
                                <input type="number" name="salary" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hire Date *</label>
                                <input type="date" name="hire_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="On Leave">On Leave</option>
                                    <option value="Terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Staff Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="staff_id" id="edit_staff_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Staff Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <input type="text" name="position" id="edit_position" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary</label>
                                <input type="number" name="salary" id="edit_salary" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hire Date *</label>
                                <input type="date" name="hire_date" id="edit_hire_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="On Leave">On Leave</option>
                                    <option value="Terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Staff Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStaff(staff) {
            document.getElementById('edit_staff_id').value = staff.staff_id;
            document.getElementById('edit_full_name').value = staff.full_name;
            document.getElementById('edit_hotel_id').value = staff.hotel_id;
            document.getElementById('edit_position').value = staff.position;
            document.getElementById('edit_phone').value = staff.phone;
            document.getElementById('edit_email').value = staff.email;
            document.getElementById('edit_salary').value = staff.salary;
            document.getElementById('edit_hire_date').value = staff.hire_date;
            document.getElementById('edit_status').value = staff.status;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteStaff(id) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="staff_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="../../js/query_sidebar.js"></script>
</body>
</html>
