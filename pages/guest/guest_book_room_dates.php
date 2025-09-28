<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$guest_id = (int)$_SESSION['guest_id'];
$hotel_id = (int)($_GET['hotel_id'] ?? $_POST['hotel_id'] ?? 0);
$room_id  = (int)($_GET['room_id']  ?? $_POST['room_id']  ?? 0);

$room = $conn->query("SELECT r.*, h.hotel_name, rt.type_name FROM rooms r JOIN hotels h ON r.hotel_id=h.hotel_id JOIN room_types rt ON r.type_id = rt.type_id WHERE r.room_id=$room_id AND r.hotel_id=$hotel_id")->fetch_assoc();
if (!$room) { header("Location: guest_search.php"); exit(); }

// Get guest membership for discount calculation
$guest = $conn->query("SELECT membership_level FROM guests WHERE guest_id=$guest_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['check_in'], $_POST['check_out'])) {
  $in  = trim($_POST['check_in']);
  $out = trim($_POST['check_out']);
  $adults = (int)($_POST['adults'] ?? 1);
  $children = (int)($_POST['children'] ?? 0);
  $special_requests = trim($_POST['special_requests'] ?? '');

  // Basic validation
  $dIn  = DateTime::createFromFormat('Y-m-d', $in);
  $dOut = DateTime::createFromFormat('Y-m-d', $out);
  if (!$dIn || !$dOut) {
    $error = "Please select valid dates.";
  } else {
    $nights = (int)$dOut->diff($dIn)->days;
    if ($nights < 1 || $dOut <= $dIn) {
      $error = "Check-out must be after check-in.";
    } elseif ($adults + $children > $room['max_occupancy']) {
      $error = "Total guests exceed room capacity of " . $room['max_occupancy'] . ".";
    } else {
      // Check room availability for the selected dates
      $availability_check = $conn->query("
        SELECT COUNT(*) as conflicts 
        FROM bookings 
        WHERE room_id = $room_id 
        AND booking_status IN ('Confirmed', 'Completed')
        AND NOT ('$out' <= check_in OR '$in' >= check_out)
      ")->fetch_assoc();
      
      if ($availability_check['conflicts'] > 0) {
        $error = "Room is not available for the selected dates.";
      } else {
        // Calculate pricing using dynamic pricing function (if available)
        try {
          $dynamic_price_query = $conn->query("SELECT CalculateDynamicPrice($room_id, '$in', $nights) as dynamic_price");
          if ($dynamic_price_query && $dynamic_price_row = $dynamic_price_query->fetch_assoc()) {
            $daily_rate = $dynamic_price_row['dynamic_price'];
          } else {
            $daily_rate = (float)$room['price'];
          }
        } catch (Exception $e) {
          $daily_rate = (float)$room['price']; // Fallback to base price
        }
        
        $subtotal = $nights * $daily_rate;
        
        // Apply membership discount
        $discount_percentage = 0;
        switch ($guest['membership_level']) {
          case 'Platinum': $discount_percentage = 15; break;
          case 'Gold': $discount_percentage = 10; break;
          case 'Silver': $discount_percentage = 5; break;
          default: $discount_percentage = 0;
        }
        
        $discount_amount = $subtotal * ($discount_percentage / 100);
        $tax_rate = 0.10; // 10% tax
        $tax_amount = ($subtotal - $discount_amount) * $tax_rate;
        $final_amount = $subtotal - $discount_amount + $tax_amount;

        // Prevent double booking
        $exists = $conn->query("SELECT 1 FROM bookings WHERE room_id=$room_id AND guest_id=$guest_id AND check_in='$in' AND check_out='$out'");
        if ($exists && $exists->num_rows) {
          $error = "You already have a booking for this room on those dates.";
        } else {
          $booking_query = "INSERT INTO bookings (
            guest_id, room_id, check_in, check_out, adults, children, 
            total_amount, discount_amount, tax_amount, final_amount,
            booking_status, payment_status, special_requests
          ) VALUES (
            $guest_id, $room_id, '$in', '$out', $adults, $children,
            $subtotal, $discount_amount, $tax_amount, $final_amount,
            'Confirmed', 'Pending', '" . esc($special_requests) . "'
          )";
          
          if ($conn->query($booking_query)) {
            $booking_id = $conn->insert_id;
            
            // Update loyalty points
            try {
              $conn->query("CALL CalculateLoyaltyPoints($guest_id, $final_amount)");
            } catch (Exception $e) {
              // Continue if loyalty calculation fails
            }
            
            $success = [
              'booking_id' => $booking_id,
              'hotel_name' => $room['hotel_name'],
              'room_number'=> $room['room_number'],
              'room_type' => $room['type_name'],
              'nights' => $nights,
              'daily_rate' => $daily_rate,
              'subtotal' => $subtotal,
              'discount_amount' => $discount_amount,
              'tax_amount' => $tax_amount,
              'final_amount' => $final_amount,
              'discount_percentage' => $discount_percentage,
              'in' => $in,
              'out' => $out,
              'adults' => $adults,
              'children' => $children
            ];
          } else {
            $error = "Could not create booking. Please try again.";
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book Room – Smart Stay</title>
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .booking-summary {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 20px;
      margin: 20px 0;
    }
    .price-breakdown {
      border-top: 1px solid #e2e8f0;
      padding-top: 15px;
      margin-top: 15px;
    }
    .price-row {
      display: flex;
      justify-content: space-between;
      margin: 5px 0;
    }
    .price-total {
      font-weight: bold;
      font-size: 1.2em;
      border-top: 2px solid #3b82f6;
      padding-top: 10px;
      margin-top: 10px;
    }
    .membership-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8em;
      font-weight: 600;
      margin-left: 10px;
    }
    .badge-platinum { background: #1e293b; color: white; }
    .badge-gold { background: #f59e0b; color: white; }
    .badge-silver { background: #6b7280; color: white; }
    .badge-bronze { background: #92400e; color: white; }
  </style>
</head>
<body>
<div class="header">
  <div>Book Room – <?= htmlspecialchars($room['hotel_name']) ?></div>
  <div class="nav">
    <a href="guest_hotel_view.php?hotel_id=<?= $hotel_id ?>">Back</a>
    <a href="guest_my_bookings.php">My Bookings</a>
    <a href="guest_logout.php">Logout</a>
  </div>
</div>

<div class="main">
  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="booking-summary">
      <h3>✅ Booking Confirmed!</h3>
      <p><strong>Booking ID:</strong> #<?= $success['booking_id'] ?></p>
      <p><strong>Hotel:</strong> <?= htmlspecialchars($success['hotel_name']) ?></p>
      <p><strong>Room:</strong> <?= htmlspecialchars($success['room_number']) ?> (<?= htmlspecialchars($success['room_type']) ?>)</p>
      <p><strong>Dates:</strong> <?= htmlspecialchars($success['in']) ?> → <?= htmlspecialchars($success['out']) ?> (<?= $success['nights'] ?> nights)</p>
      <p><strong>Guests:</strong> <?= $success['adults'] ?> adults<?= $success['children'] > 0 ? ', ' . $success['children'] . ' children' : '' ?></p>
      
      <div class="price-breakdown">
        <div class="price-row">
          <span>Room rate (per night):</span>
          <span>$<?= number_format($success['daily_rate'], 2) ?></span>
        </div>
        <div class="price-row">
          <span>Subtotal (<?= $success['nights'] ?> nights):</span>
          <span>$<?= number_format($success['subtotal'], 2) ?></span>
        </div>
        <?php if ($success['discount_amount'] > 0): ?>
        <div class="price-row" style="color: #16a34a;">
          <span>Membership discount (<?= $success['discount_percentage'] ?>%):</span>
          <span>-$<?= number_format($success['discount_amount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="price-row">
          <span>Taxes & fees:</span>
          <span>$<?= number_format($success['tax_amount'], 2) ?></span>
        </div>
        <div class="price-row price-total">
          <span>Total Amount:</span>
          <span>$<?= number_format($success['final_amount'], 2) ?></span>
        </div>
      </div>
      
      <p style="margin-top: 20px;"><strong>Payment Status:</strong> Pending</p>
      <p><em>Please arrive at check-in time and complete payment at the hotel.</em></p>
      
      <div style="margin-top: 20px;">
        <a class="btn btn-primary" href="guest_my_bookings.php">View All Bookings</a>
        <a class="btn btn-secondary" href="guest_hotel_view.php?hotel_id=<?= $hotel_id ?>">Back to Hotel</a>
      </div>
    </div>
  <?php else: ?>
    <div class="booking-summary">
      <h3>Room Details</h3>
      <p><strong>Hotel:</strong> <?= htmlspecialchars($room['hotel_name']) ?></p>
      <p><strong>Room:</strong> <?= htmlspecialchars($room['room_number']) ?> - <?= htmlspecialchars($room['type_name']) ?></p>
      <p><strong>Maximum Occupancy:</strong> <?= $room['max_occupancy'] ?> guests</p>
      <p><strong>Base Rate:</strong> $<?= number_format($room['price'], 2) ?> per night</p>
      <p><strong>Your Membership:</strong> 
        <span class="membership-badge badge-<?= strtolower($guest['membership_level']) ?>">
          <?= htmlspecialchars($guest['membership_level']) ?>
        </span>
        <?php
        $discount_text = '';
        switch ($guest['membership_level']) {
          case 'Platinum': $discount_text = '15% discount'; break;
          case 'Gold': $discount_text = '10% discount'; break;
          case 'Silver': $discount_text = '5% discount'; break;
          default: $discount_text = 'No discount';
        }
        ?>
        (<?= $discount_text ?>)
      </p>
    </div>

    <form method="post" class="form-container">
      <input type="hidden" name="hotel_id" value="<?= $hotel_id ?>">
      <input type="hidden" name="room_id" value="<?= $room_id ?>">
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div class="form-group">
          <label>Check-in Date *</label>
          <input type="date" name="check_in" min="<?= date('Y-m-d') ?>" required value="<?= $_POST['check_in'] ?? '' ?>">
        </div>
        <div class="form-group">
          <label>Check-out Date *</label>
          <input type="date" name="check_out" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required value="<?= $_POST['check_out'] ?? '' ?>">
        </div>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div class="form-group">
          <label>Adults *</label>
          <select name="adults" required>
            <?php for ($i = 1; $i <= min(6, $room['max_occupancy']); $i++): ?>
              <option value="<?= $i ?>" <?= (($_POST['adults'] ?? 1) == $i) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Children</label>
          <select name="children">
            <?php for ($i = 0; $i <= min(4, $room['max_occupancy'] - 1); $i++): ?>
              <option value="<?= $i ?>" <?= (($_POST['children'] ?? 0) == $i) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label>Special Requests</label>
        <textarea name="special_requests" rows="3" placeholder="Any special requirements or preferences..."><?= htmlspecialchars($_POST['special_requests'] ?? '') ?></textarea>
      </div>
      
      <p style="color: #64748b; font-size: 0.9em;">
        * Final price will be calculated based on dynamic pricing, your membership discount, and applicable taxes.
      </p>
      
      <button class="btn btn-primary" type="submit">Calculate Price & Book</button>
    </form>
  <?php endif; ?>
</div>

<script>
// Auto-update checkout date when checkin date changes
document.querySelector('input[name="check_in"]').addEventListener('change', function() {
  const checkInDate = new Date(this.value);
  const checkOutDate = new Date(checkInDate);
  checkOutDate.setDate(checkOutDate.getDate() + 1);
  
  const checkOutInput = document.querySelector('input[name="check_out"]');
  if (!checkOutInput.value || new Date(checkOutInput.value) <= checkInDate) {
    checkOutInput.value = checkOutDate.toISOString().split('T')[0];
  }
  checkOutInput.min = checkOutDate.toISOString().split('T')[0];
});
</script>
</body>
</html>
