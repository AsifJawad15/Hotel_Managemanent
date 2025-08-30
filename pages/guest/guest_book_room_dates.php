<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$guest_id = (int)$_SESSION['guest_id'];
$hotel_id = (int)($_GET['hotel_id'] ?? $_POST['hotel_id'] ?? 0);
$room_id  = (int)($_GET['room_id']  ?? $_POST['room_id']  ?? 0);

$room = $conn->query("SELECT r.*, h.hotel_name FROM rooms r JOIN hotels h ON r.hotel_id=h.hotel_id WHERE r.room_id=$room_id AND r.hotel_id=$hotel_id")->fetch_assoc();
if (!$room) { header("Location: guest_search.php"); exit(); }

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['check_in'], $_POST['check_out'])) {
  $in  = trim($_POST['check_in']);
  $out = trim($_POST['check_out']);

  // basic validation
  $dIn  = DateTime::createFromFormat('Y-m-d', $in);
  $dOut = DateTime::createFromFormat('Y-m-d', $out);
  if (!$dIn || !$dOut) {
    $error = "Please select valid dates.";
  } else {
    $nights = (int)$dOut->diff($dIn)->days;
    if ($nights < 1 || $dOut <= $dIn) {
      $error = "Check-out must be after check-in.";
    } else {
      // compute bill using current price (per requirement)
      $price = (float)$room['price'];
      $total = $nights * $price;

      // prevent double-book if already booked (simple approach)
      $exists = $conn->query("SELECT 1 FROM bookings WHERE room_id=$room_id AND guest_id=$guest_id AND check_in='$in' AND check_out='$out'");
      if ($exists && $exists->num_rows) {
        $error = "You already booked this room for those dates.";
      } else {
        $ok1 = $conn->query("INSERT INTO bookings (guest_id, room_id, check_in, check_out, total_amount) VALUES ($guest_id, $room_id, '$in', '$out', $total)");
        $ok2 = $conn->query("UPDATE rooms SET is_booked=1 WHERE room_id=$room_id");
        if ($ok1) {
          $booking_id = $conn->insert_id;
          $success = [
            'booking_id' => $booking_id,
            'hotel_name' => $room['hotel_name'],
            'room_number'=> $room['room_number'],
            'nights'     => $nights,
            'price'      => number_format($price,2),
            'total'      => number_format($total,2),
            'in'         => $in,
            'out'        => $out
          ];
        } else {
          $error = "Could not create booking. Please try again.";
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
</head>
<body>
<div class="header">
  <div>Book Room – <?= htmlspecialchars($room['hotel_name']) ?> (Room <?= htmlspecialchars($room['room_number']) ?>)</div>
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
    <h3>Booking Confirmed</h3>
    <p><strong>Hotel:</strong> <?= htmlspecialchars($success['hotel_name']) ?></p>
    <p><strong>Room:</strong> <?= htmlspecialchars($success['room_number']) ?></p>
    <p><strong>Stay:</strong> <?= htmlspecialchars($success['in']) ?> → <?= htmlspecialchars($success['out']) ?> (<?= (int)$success['nights'] ?> nights)</p>
    <p><strong>Rate (per night):</strong> $<?= $success['price'] ?></p>
    <p><strong>Total:</strong> $<?= $success['total'] ?></p>
    <p><a class="btn btn-primary" href="guest_my_bookings.php">Go to My Bookings</a></p>
  <?php else: ?>
    <form method="post" class="form-container">
      <input type="hidden" name="hotel_id" value="<?= $hotel_id ?>">
      <input type="hidden" name="room_id"  value="<?= $room_id ?>">
      <div class="form-group">
        <label>Check-in</label>
        <input type="date" name="check_in" required>
      </div>
      <div class="form-group">
        <label>Check-out</label>
        <input type="date" name="check_out" required>
      </div>
      <p>Current rate (per night): <strong>$<?= htmlspecialchars(number_format($room['price'],2)) ?></strong></p>
      <button class="btn btn-primary">Confirm Booking</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
