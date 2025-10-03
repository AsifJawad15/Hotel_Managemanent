<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$gid = (int)$_SESSION['guest_id'];

// Room bookings (join hotels & rooms & room_types)
$roomBookings = $conn->query("
  SELECT b.booking_id, b.check_in, b.check_out, b.total_amount, b.booking_status,
         r.room_id, r.room_number, r.price,
         rt.type_name as type,
         h.hotel_id, h.hotel_name,
         (SELECT COUNT(*) FROM reviews WHERE booking_id = b.booking_id) as has_review
  FROM bookings b
  JOIN rooms r  ON b.room_id = r.room_id
  JOIN hotels h ON r.hotel_id = h.hotel_id
  JOIN room_types rt ON r.type_id = rt.type_id
  WHERE b.guest_id = $gid
  ORDER BY b.booking_id DESC
");

// Event bookings: use the event_bookings table
$eventBookings = $conn->query("
  SELECT e.event_id, e.event_name, e.event_date, e.start_time, e.price,
         h.hotel_id, h.hotel_name,
         eb.event_booking_id, eb.participants, eb.amount_paid, eb.booking_status
  FROM event_bookings eb
  JOIN events e ON eb.event_id = e.event_id
  JOIN hotels h ON e.hotel_id = h.hotel_id
  WHERE eb.guest_id = $gid
  ORDER BY e.event_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Bookings</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="header">
  <div>My Bookings</div>
  <div class="nav">
    <a href="guest_home.php">Home</a>
    <a href="guest_search.php">Search Hotels</a>
    <a href="guest_my_reviews.php">My Reviews</a>
    <a href="guest_profile.php">Profile</a>
    <a href="guest_logout.php">Logout</a>
  </div>
</div>

<div class="main">
  <h2>Room Bookings</h2>
  <?php if ($roomBookings && $roomBookings->num_rows): ?>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Hotel</th>
          <th>Room</th>
          <th>Stay</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($b = $roomBookings->fetch_assoc()):
        $nights = 0;
        if (!empty($b['check_in']) && !empty($b['check_out'])) {
          $di = new DateTime($b['check_in']);
          $do = new DateTime($b['check_out']);
          $nights = $do->diff($di)->days;
        }
        $is_completed = ($b['booking_status'] == 'Completed');
        $is_past = (strtotime($b['check_out']) < time());
      ?>
        <tr>
          <td><?= (int)$b['booking_id'] ?></td>
          <td><?= htmlspecialchars($b['hotel_name']) ?></td>
          <td><?= htmlspecialchars($b['room_number']) ?> (<?= htmlspecialchars($b['type']) ?>)</td>
          <td><?= htmlspecialchars($b['check_in']) ?> → <?= htmlspecialchars($b['check_out']) ?> (<?= (int)$nights ?> nights)</td>
          <td>$<?= htmlspecialchars(number_format((float)$b['total_amount'],2)) ?></td>
          <td>
            <span class="badge badge-<?= $is_completed ? 'success' : 'info' ?>">
              <?= htmlspecialchars($b['booking_status'] ?? 'Confirmed') ?>
            </span>
          </td>
          <td>
            <?php if (($is_completed || $is_past) && $b['has_review'] == 0): ?>
              <!-- Show Write Review for completed OR past bookings -->
              <a class="btn btn-primary" href="guest_write_review.php?booking_id=<?= (int)$b['booking_id'] ?>">
                ⭐ Write Review
              </a>
            <?php elseif ($b['has_review'] > 0): ?>
              <!-- Already reviewed -->
              <a class="btn" href="guest_my_reviews.php" style="background: #10b981; color: white;">
                ✓ Reviewed
              </a>
            <?php elseif (!$is_past): ?>
              <!-- Future/current booking - can cancel -->
              <a class="btn btn-danger" onclick="return confirmDelete();"
                 href="guest_cancel_room.php?booking_id=<?= (int)$b['booking_id'] ?>">Cancel</a>
            <?php else: ?>
              <span style="color: #94a3b8;">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No room bookings yet.</p>
  <?php endif; ?>

  <h2 style="margin-top:28px;">Event Bookings</h2>
  <?php if ($eventBookings && $eventBookings->num_rows > 0): ?>
    <table class="table">
      <thead>
        <tr>
          <th>Hotel</th>
          <th>Event</th>
          <th>Date</th>
          <th>Time</th>
          <th>Participants</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($eb = $eventBookings->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($eb['hotel_name']) ?></td>
          <td><?= htmlspecialchars($eb['event_name']) ?></td>
          <td><?= htmlspecialchars($eb['event_date']) ?></td>
          <td><?= htmlspecialchars($eb['start_time']) ?></td>
          <td><?= (int)$eb['participants'] ?></td>
          <td>$<?= number_format((float)$eb['amount_paid'], 2) ?></td>
          <td><span class="badge badge-info"><?= htmlspecialchars($eb['booking_status']) ?></span></td>
          <td>
            <?php if ($eb['booking_status'] === 'Confirmed'): ?>
              <a class="btn btn-danger" onclick="return confirmDelete();"
                 href="guest_cancel_event.php?event_booking_id=<?= (int)$eb['event_booking_id'] ?>">Cancel</a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No event bookings yet.</p>
  <?php endif; ?>
</div>

<script src='../../js/script.js'></script>
</body>
</html>
