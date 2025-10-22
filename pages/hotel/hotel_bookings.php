<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$success_message = '';
$error_message = '';

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['payment_status'];
    $hotel_id = $_SESSION['hotel_id'];
    
    // Verify the booking belongs to this hotel
    $verify_sql = "SELECT b.booking_id, b.payment_status, g.name as guest_name, r.room_number 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.room_id 
                   JOIN guests g ON b.guest_id = g.guest_id
                   WHERE b.booking_id = ? AND r.hotel_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $booking_id, $hotel_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $old_status = $booking['payment_status'];
        
        // Update payment status
        $update_sql = "UPDATE bookings SET payment_status = ?, updated_at = NOW() WHERE booking_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_status, $booking_id);
        
        if ($update_stmt->execute()) {
            // If payment is confirmed as Paid, update booking status to Confirmed
            if ($new_status === 'Paid') {
                $conn->query("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = $booking_id AND booking_status = 'Confirmed'");
            }
            
            $success_message = "✅ Payment status updated from '$old_status' to '$new_status' for {$booking['guest_name']} (Room {$booking['room_number']})";
        } else {
            $error_message = "❌ Failed to update payment status: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        $error_message = "❌ Booking not found or doesn't belong to your hotel.";
    }
    $verify_stmt->close();
}

// Get all bookings for this hotel with payment details
$hotel_id = $_SESSION['hotel_id'];
$bookings_sql = "
    SELECT 
        b.booking_id,
        b.check_in,
        b.check_out,
        b.total_amount,
        b.final_amount,
        b.booking_status,
        b.payment_status,
        b.booking_source,
        b.created_at,
        g.name as guest_name,
        g.email as guest_email,
        g.phone as guest_phone,
        r.room_number,
        rt.type_name as room_type,
        DATEDIFF(b.check_out, b.check_in) as nights,
        p.payment_method,
        p.transaction_id,
        p.payment_date
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    JOIN guests g ON b.guest_id = g.guest_id
    JOIN room_types rt ON r.type_id = rt.type_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.payment_status = 'Success'
    WHERE r.hotel_id = ?
    ORDER BY 
        CASE b.payment_status
            WHEN 'Pending' THEN 1
            WHEN 'Partial' THEN 2
            WHEN 'Paid' THEN 3
            WHEN 'Refunded' THEN 4
        END,
        b.check_in DESC
";
$bookings_stmt = $conn->prepare($bookings_sql);
$bookings_stmt->bind_param("i", $hotel_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking & Payment Management - <?= htmlspecialchars($_SESSION['hotel_name']) ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .bookings-container { max-width: 1400px; margin: 20px auto; padding: 0 20px; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .payment-pending { background: #fef3c7; color: #92400e; }
        .payment-paid { background: #d1fae5; color: #065f46; }
        .payment-partial { background: #fecaca; color: #991b1b; }
        .payment-refunded { background: #e5e7eb; color: #374151; }
        
        .booking-confirmed { background: #d1fae5; color: #065f46; }
        .booking-cancelled { background: #fee2e2; color: #991b1b; }
        .booking-completed { background: #dbeafe; color: #1e40af; }
        
        .booking-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        .booking-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #6b7280; font-size: 0.85em; display: block; margin-bottom: 4px; }
        .info-value { color: #1f2937; font-size: 1em; }
        .payment-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-update {
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-update:hover { background: #2563eb; }
        select.payment-select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95em;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-label { font-size: 0.85em; color: #6b7280; margin-bottom: 8px; }
        .stat-value { font-size: 1.8em; font-weight: bold; color: #1f2937; }
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
<div class="header">
    <div>Hotel: <?= htmlspecialchars($_SESSION['hotel_name']) ?> - Booking Management</div>
    <div class="nav">
        <a href="hotel_home.php">Dashboard</a>
        <a href="hotel_rooms.php">Rooms</a>
        <a href="hotel_services.php">Services</a>
        <a href="hotel_events.php">Events</a>
        <a href="hotel_bookings.php" style="font-weight: bold;">Bookings</a>
        <a href="hotel_logout.php">Logout</a>
    </div>
</div>

<div class="bookings-container">
    <h2>📋 Booking & Payment Management</h2>
    
    <?php if ($success_message): ?>
        <div class="success" style="margin: 20px 0; padding: 15px; background: #d1fae5; border-radius: 6px;">
            <?= $success_message ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error" style="margin: 20px 0; padding: 15px; background: #fee2e2; border-radius: 6px;">
            <?= $error_message ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <?php
    // Calculate statistics
    $total_bookings = 0;
    $pending_payments = 0;
    $total_revenue = 0;
    $confirmed_bookings = 0;
    
    $bookings_result->data_seek(0); // Reset pointer
    while ($row = $bookings_result->fetch_assoc()) {
        $total_bookings++;
        if ($row['payment_status'] === 'Pending') $pending_payments++;
        if ($row['payment_status'] === 'Paid') $total_revenue += $row['final_amount'];
        if ($row['booking_status'] === 'Confirmed') $confirmed_bookings++;
    }
    $bookings_result->data_seek(0); // Reset again for display
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= $total_bookings ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Payments</div>
            <div class="stat-value" style="color: #dc2626;"><?= $pending_payments ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Confirmed Bookings</div>
            <div class="stat-value" style="color: #16a34a;"><?= $confirmed_bookings ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue (Paid)</div>
            <div class="stat-value" style="color: #2563eb;">$<?= number_format($total_revenue, 2) ?></div>
        </div>
    </div>
    
    <!-- Bookings List -->
    <h3 style="margin-top: 30px; margin-bottom: 15px;">All Bookings</h3>
    
    <?php if ($bookings_result->num_rows === 0): ?>
        <div class="booking-card" style="text-align: center; color: #6b7280;">
            <p>No bookings found for your hotel yet.</p>
        </div>
    <?php else: ?>
        <?php while ($booking = $bookings_result->fetch_assoc()): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <div>
                        <h3 style="margin: 0 0 8px 0;">Booking #<?= $booking['booking_id'] ?></h3>
                        <div style="display: flex; gap: 10px;">
                            <span class="status-badge booking-<?= strtolower($booking['booking_status']) ?>">
                                <?= $booking['booking_status'] ?>
                            </span>
                            <span class="status-badge payment-<?= strtolower($booking['payment_status']) ?>">
                                Payment: <?= $booking['payment_status'] ?>
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.5em; font-weight: bold; color: #1f2937;">
                            $<?= number_format($booking['final_amount'], 2) ?>
                        </div>
                        <div style="font-size: 0.85em; color: #6b7280;">
                            <?= $booking['nights'] ?> night<?= $booking['nights'] > 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>
                
                <div class="booking-info">
                    <div class="info-item">
                        <span class="info-label">Guest Name</span>
                        <span class="info-value"><?= htmlspecialchars($booking['guest_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($booking['guest_email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= htmlspecialchars($booking['guest_phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Room</span>
                        <span class="info-value"><?= htmlspecialchars($booking['room_number']) ?> (<?= htmlspecialchars($booking['room_type']) ?>)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Check-in</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($booking['check_in'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Check-out</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($booking['check_out'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Booking Source</span>
                        <span class="info-value"><?= $booking['booking_source'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Booked On</span>
                        <span class="info-value"><?= date('M d, Y g:i A', strtotime($booking['created_at'])) ?></span>
                    </div>
                    
                    <?php if ($booking['payment_method']): ?>
                    <div class="info-item">
                        <span class="info-label">Payment Method</span>
                        <span class="info-value"><?= $booking['payment_method'] ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking['transaction_id']): ?>
                    <div class="info-item">
                        <span class="info-label">Transaction ID</span>
                        <span class="info-value"><?= htmlspecialchars($booking['transaction_id']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Status Update Form -->
                <?php if ($booking['booking_status'] !== 'Cancelled'): ?>
                <form method="POST" class="payment-actions">
                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                    <label style="font-weight: 600; color: #374151;">Update Payment Status:</label>
                    <select name="payment_status" class="payment-select" required>
                        <option value="Pending" <?= $booking['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Partial" <?= $booking['payment_status'] === 'Partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="Paid" <?= $booking['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid (Confirmed)</option>
                        <option value="Refunded" <?= $booking['payment_status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                    <button type="submit" name="update_payment" class="btn-update" 
                            onclick="return confirm('Are you sure you want to update the payment status for this booking?')">
                        Update Status
                    </button>
                </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.success, .error');
    messages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
});
</script>
</body>
</html>
<?php
$bookings_stmt->close();
$conn->close();
?>
