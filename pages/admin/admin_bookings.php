<?php
session_start();
require_once '../../includes/db_connect.php';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $customer_id = intval($_POST['customer_id']);
        $room_id = intval($_POST['room_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $check_in = esc($_POST['check_in']);
        $check_out = esc($_POST['check_out']);
        $total_amount = floatval($_POST['total_amount']);
        $payment_status = esc($_POST['payment_status']);
        $booking_status = esc($_POST['booking_status']);
        $notes = esc($_POST['notes']);
        
        // Query #4: INSERT - Create new booking
        $sql = "INSERT INTO bookings (customer_id, room_id, hotel_id, check_in, check_out, total_amount, payment_status, booking_status, notes) 
                VALUES ($customer_id, $room_id, $hotel_id, '$check_in', '$check_out', $total_amount, '$payment_status', '$booking_status', '$notes')";
        
        if ($conn->query($sql)) {
            $success = "Booking created successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $booking_id = intval($_POST['booking_id']);
        $customer_id = intval($_POST['customer_id']);
        $room_id = intval($_POST['room_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $check_in = esc($_POST['check_in']);
        $check_out = esc($_POST['check_out']);
        $total_amount = floatval($_POST['total_amount']);
        $payment_status = esc($_POST['payment_status']);
        $booking_status = esc($_POST['booking_status']);
        $notes = esc($_POST['notes']);
        
        // Query #5: UPDATE - Update booking details
        $sql = "UPDATE bookings SET customer_id=$customer_id, room_id=$room_id, hotel_id=$hotel_id, 
                check_in='$check_in', check_out='$check_out', total_amount=$total_amount, 
                payment_status='$payment_status', booking_status='$booking_status', notes='$notes'
                WHERE booking_id=$booking_id";
        
        if ($conn->query($sql)) {
            $success = "Booking updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    if ($action === 'delete') {
        $booking_id = intval($_POST['booking_id']);
        // Query #6: DELETE - Remove booking
        $sql = "DELETE FROM bookings WHERE booking_id=$booking_id";
        $sql = "DELETE FROM bookings WHERE booking_id=$booking_id";
        
        if ($conn->query($sql)) {
            $success = "Booking deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Query #1: INNER JOIN - Display bookings with customer, hotel, and room details
$bookings = $conn->query("
    SELECT b.*, c.full_name as customer_name, c.phone as customer_phone,
           h.hotel_name, r.room_number, rt.type_name
    FROM bookings b
    INNER JOIN customers c ON b.customer_id = c.customer_id
    INNER JOIN hotels h ON b.hotel_id = h.hotel_id
    INNER JOIN rooms r ON b.room_id = r.room_id
    INNER JOIN room_types rt ON r.type_id = rt.type_id
    ORDER BY b.booking_id DESC
");

// Query #2: SELECT - Get customers for dropdown
$customers = $conn->query("SELECT customer_id, full_name FROM customers ORDER BY full_name");

// Query #3: SELECT - Get active hotels and available rooms for dropdown
$hotels = $conn->query("SELECT hotel_id, hotel_name FROM hotels WHERE status='Active' ORDER BY hotel_name");
$rooms = $conn->query("SELECT r.room_id, r.room_number, h.hotel_name FROM rooms r INNER JOIN hotels h ON r.hotel_id = h.hotel_id WHERE r.status='Available' ORDER BY h.hotel_name, r.room_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - SmartStay</title>
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
                    <h2><i class="bi bi-calendar-check-fill" style="color: #93BFC7;"></i> Manage Bookings</h2>
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
            <i class="bi bi-plus-circle"></i> Add New Booking
        </button>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Hotel</th>
                                <th>Room</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['booking_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($row['customer_phone']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                <td><?= htmlspecialchars($row['room_number']) ?> (<?= htmlspecialchars($row['type_name']) ?>)</td>
                                <td><?= date('M d, Y', strtotime($row['check_in'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['check_out'])) ?></td>
                                <td><strong>$<?= number_format($row['total_amount'], 2) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= $row['payment_status'] === 'Paid' ? 'success' : ($row['payment_status'] === 'Pending' ? 'warning' : 'danger') ?>">
                                        <?= $row['payment_status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $row['booking_status'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editBooking(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteBooking(<?= $row['booking_id'] ?>)">
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
            <div class="query-title-text">MULTIPLE JOINS - Display bookings with all details</div>
            <div class="query-sql">SELECT b.*, c.full_name as customer_name, 
       c.phone as customer_phone,
       h.hotel_name, r.room_number, 
       rt.type_name
FROM bookings b
INNER JOIN customers c 
  ON b.customer_id = c.customer_id
INNER JOIN hotels h 
  ON b.hotel_id = h.hotel_id
INNER JOIN rooms r 
  ON b.room_id = r.room_id
INNER JOIN room_types rt 
  ON r.type_id = rt.type_id
ORDER BY b.booking_id DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #2</div>
            <div class="query-title-text">SELECT (Customers) - Get customers for dropdown</div>
            <div class="query-sql">SELECT customer_id, full_name 
FROM customers 
ORDER BY full_name</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #3</div>
            <div class="query-title-text">SELECT (Available Rooms) - Get available rooms with hotel names</div>
            <div class="query-sql">SELECT r.room_id, r.room_number, 
       h.hotel_name 
FROM rooms r
INNER JOIN hotels h 
  ON r.hotel_id = h.hotel_id
WHERE r.status='Available'
ORDER BY h.hotel_name, r.room_number</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #4</div>
            <div class="query-title-text">INSERT - Create new booking</div>
            <div class="query-sql">INSERT INTO bookings 
  (customer_id, room_id, hotel_id, 
   check_in, check_out, total_amount, 
   payment_status, booking_status, notes) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #5</div>
            <div class="query-title-text">UPDATE - Update booking information</div>
            <div class="query-sql">UPDATE bookings 
SET customer_id=?, room_id=?, 
    hotel_id=?, check_in=?, 
    check_out=?, total_amount=?, 
    payment_status=?, 
    booking_status=?, notes=?
WHERE booking_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">Query #6</div>
            <div class="query-title-text">DELETE - Remove booking</div>
            <div class="query-sql">DELETE FROM bookings 
WHERE booking_id=?</div>
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
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer *</label>
                                <select name="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    <?php 
                                    $customers->data_seek(0);
                                    while ($c = $customers->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
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
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Room *</label>
                                <select name="room_id" class="form-control" required>
                                    <option value="">Select Room</option>
                                    <?php 
                                    $rooms->data_seek(0);
                                    while ($r = $rooms->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $r['room_id'] ?>"><?= htmlspecialchars($r['hotel_name']) ?> - Room <?= htmlspecialchars($r['room_number']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-In Date *</label>
                                <input type="date" name="check_in" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-Out Date *</label>
                                <input type="date" name="check_out" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount *</label>
                                <input type="number" name="total_amount" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Status</label>
                                <select name="booking_status" class="form-control">
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Checked-In">Checked-In</option>
                                    <option value="Checked-Out">Checked-Out</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Booking</button>
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
                    <input type="hidden" name="booking_id" id="edit_booking_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer *</label>
                                <select name="customer_id" id="edit_customer_id" class="form-control" required>
                                    <?php 
                                    $customers->data_seek(0);
                                    while ($c = $customers->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
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
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Room *</label>
                                <select name="room_id" id="edit_room_id" class="form-control" required>
                                    <?php 
                                    $rooms->data_seek(0);
                                    while ($r = $rooms->fetch_assoc()): 
                                    ?>
                                    <option value="<?= $r['room_id'] ?>"><?= htmlspecialchars($r['hotel_name']) ?> - Room <?= htmlspecialchars($r['room_number']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-In Date *</label>
                                <input type="date" name="check_in" id="edit_check_in" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-Out Date *</label>
                                <input type="date" name="check_out" id="edit_check_out" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount *</label>
                                <input type="number" name="total_amount" id="edit_total_amount" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" id="edit_payment_status" class="form-control">
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Status</label>
                                <select name="booking_status" id="edit_booking_status" class="form-control">
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Checked-In">Checked-In</option>
                                    <option value="Checked-Out">Checked-Out</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBooking(booking) {
            document.getElementById('edit_booking_id').value = booking.booking_id;
            document.getElementById('edit_customer_id').value = booking.customer_id;
            document.getElementById('edit_hotel_id').value = booking.hotel_id;
            document.getElementById('edit_room_id').value = booking.room_id;
            document.getElementById('edit_check_in').value = booking.check_in;
            document.getElementById('edit_check_out').value = booking.check_out;
            document.getElementById('edit_total_amount').value = booking.total_amount;
            document.getElementById('edit_payment_status').value = booking.payment_status;
            document.getElementById('edit_booking_status').value = booking.booking_status;
            document.getElementById('edit_notes').value = booking.notes;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteBooking(id) {
            if (confirm('Are you sure you want to delete this booking?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="booking_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="../../js/query_sidebar.js"></script>
</body>
</html>
