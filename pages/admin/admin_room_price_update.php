<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to admin login instead of index
    header("Location: admin_login.php");
    exit();
}

require_once '../../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle price update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_prices'])) {
    $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null;
    $percentage = (float)$_POST['percentage'];
    
    try {
        $multiplier = 1 + ($percentage / 100);
        
        if ($hotel_id) {
            // Update specific hotel
            $sql = "UPDATE rooms SET price = ROUND(price * ?, 2), updated_at = NOW() WHERE hotel_id = ? AND is_active = TRUE";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $multiplier, $hotel_id);
            
            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                
                // Get hotel name
                $hotel_query = "SELECT hotel_name FROM hotels WHERE hotel_id = ?";
                $hotel_stmt = $conn->prepare($hotel_query);
                $hotel_stmt->bind_param("i", $hotel_id);
                $hotel_stmt->execute();
                $hotel_result = $hotel_stmt->get_result();
                $hotel_name = $hotel_result->fetch_assoc()['hotel_name'];
                $hotel_stmt->close();
                
                $success_message = "✅ Successfully updated $affected_rows rooms in $hotel_name with " . ($percentage >= 0 ? "+" : "") . "$percentage% price change.";
            } else {
                throw new Exception($conn->error);
            }
        } else {
            // Update all hotels
            $sql = "UPDATE rooms SET price = ROUND(price * ?, 2), updated_at = NOW() WHERE is_active = TRUE";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("d", $multiplier);
            
            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                
                $success_message = "✅ Successfully updated $affected_rows rooms across all hotels with " . ($percentage >= 0 ? "+" : "") . "$percentage% price change.";
            } else {
                throw new Exception($conn->error);
            }
        }
        
    } catch (Exception $e) {
        $error_message = "❌ Error updating prices: " . $e->getMessage();
    }
}

// Get list of hotels for dropdown
$hotels_query = "SELECT hotel_id, hotel_name FROM hotels WHERE is_active = TRUE ORDER BY hotel_name";
$hotels_result = $conn->query($hotels_query);
$hotels = [];
while ($row = $hotels_result->fetch_assoc()) {
    $hotels[] = $row;
}

// Get current room prices for preview
$preview_query = "
    SELECT h.hotel_name, r.room_number, rt.type_name, r.price, r.room_id, r.updated_at
    FROM rooms r 
    JOIN hotels h ON r.hotel_id = h.hotel_id 
    JOIN room_types rt ON r.type_id = rt.type_id
    WHERE r.is_active = TRUE 
    ORDER BY h.hotel_name, r.room_number
";
$preview_result = $conn->query($preview_query);
$current_rooms = [];
while ($row = $preview_result->fetch_assoc()) {
    $current_rooms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Price Management - SmartStay Admin</title>
    <link href="../../css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_home.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_hotels.php">
                                <i class="fas fa-hotel"></i> Hotels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="admin_room_price_update.php">
                                <i class="fas fa-dollar-sign"></i> Price Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admin_logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ml-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-dollar-sign"></i> Room Price Management</h1>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Price Update Form -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-edit"></i> Update Room Prices</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="hotel_id" class="form-label">Select Hotel:</label>
                                        <select class="form-control" id="hotel_id" name="hotel_id">
                                            <option value="">All Hotels</option>
                                            <?php foreach ($hotels as $hotel): ?>
                                                <option value="<?php echo $hotel['hotel_id']; ?>">
                                                    <?php echo htmlspecialchars($hotel['hotel_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">Leave empty to update all hotels</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="percentage" class="form-label">Price Change Percentage:</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="percentage" name="percentage" 
                                                   step="0.01" min="-50" max="100" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="form-text text-muted">
                                            Enter positive number to increase (e.g., 10 for 10% increase) or negative to decrease (e.g., -5 for 5% decrease)
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" name="update_prices" class="btn btn-primary" onclick="return confirmUpdate()">
                                            <i class="fas fa-save"></i> Update Prices
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Room Prices -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Current Room Prices</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Hotel</th>
                                        <th>Room Number</th>
                                        <th>Room Type</th>
                                        <th>Current Price</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($current_rooms as $room): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room['hotel_name']); ?></td>
                                            <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                            <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                            <td><strong>$<?php echo number_format($room['price'], 2); ?></strong></td>
                                            <td>
                                                <?php 
                                                if ($room['updated_at']) {
                                                    $updated = new DateTime($room['updated_at']);
                                                    $now = new DateTime();
                                                    $diff = $now->diff($updated);
                                                    
                                                    if ($diff->days == 0 && $diff->h == 0 && $diff->i < 5) {
                                                        echo '<span class="badge bg-success">Just updated</span>';
                                                    } else {
                                                        echo '<span class="text-muted">' . $updated->format('M d, Y g:i A') . '</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">Never updated</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Debug info -->
                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                    <div class="alert alert-info mt-4">
                        <strong>Debug Info:</strong><br>
                        Session Role: <?php echo $_SESSION['role'] ?? 'Not set'; ?><br>
                        Hotel ID: <?php echo $_POST['hotel_id'] ?? 'Not set'; ?><br>
                        Percentage: <?php echo $_POST['percentage'] ?? 'Not set'; ?><br>
                        Form Submitted: Yes
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmUpdate() {
            const hotelId = document.getElementById('hotel_id').value;
            const percentage = document.getElementById('percentage').value;
            const hotelName = hotelId ? document.getElementById('hotel_id').options[document.getElementById('hotel_id').selectedIndex].text : 'ALL HOTELS';
            
            const action = percentage >= 0 ? 'increase' : 'decrease';
            const message = `Are you sure you want to ${action} room prices by ${Math.abs(percentage)}% for ${hotelName}?`;
            
            return confirm(message);
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Scroll to table after update
            <?php if (isset($success_message) && $success_message): ?>
                setTimeout(function() {
                    document.querySelector('.table-responsive').scrollIntoView({ behavior: 'smooth' });
                }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>