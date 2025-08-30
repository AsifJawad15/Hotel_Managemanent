<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$gid = (int)$_SESSION['guest_id'];

// Room bookings (join hotels & rooms)
$roomBookings = $conn->query("
  SELECT b.booking_id, b.check_in, b.check_out, b.total_amount,
         r.room_id, r.room_number, r.type, r.price,
         h.hotel_id, h.hotel_name
  FROM bookings b
  JOIN rooms r  ON b.room_id = r.room_id
  JOIN hotels h ON r.hotel_id = h.hotel_id
  WHERE b.guest_id = $gid
  ORDER BY b.booking_id DESC
");

// Event bookings: scan events and check the per-event tables
$eventRows = [];
$events = $conn->query("SELECT e.*, h.hotel_name FROM events e JOIN hotels h ON e.hotel_id=h.hotel_id ORDER BY e.event_date DESC");
if ($events) {
  while ($e = $events->fetch_assoc()) {
    $hid = (int)$e['hotel_id']; $eid = (int)$e['event_id'];
    $t = "hotel{$hid}_event{$eid}";
    // try a cheap existence + membership check
    $chk = $conn->query("SELECT 1 FROM `$t` WHERE guest_id=$gid LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
      $eventRows[] = $e;
    }
  }
}
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
          <th>Action</th>
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
      ?>
        <tr>
          <td><?= (int)$b['booking_id'] ?></td>
          <td><?= htmlspecialchars($b['hotel_name']) ?></td>
          <td><?= htmlspecialchars($b['room_number']) ?> (<?= htmlspecialchars($b['type']) ?>)</td>
          <td><?= htmlspecialchars($b['check_in']) ?> → <?= htmlspecialchars($b['check_out']) ?> (<?= (int)$nights ?> nights)</td>
          <td>$<?= htmlspecialchars(number_format((float)$b['total_amount'],2)) ?></td>
          <td>
            <a class="btn btn-danger" onclick="return confirmDelete();"
               href="guest_cancel_room.php?booking_id=<?= (int)$b['booking_id'] ?>">Cancel</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No room bookings yet.</p>
  <?php endif; ?>

  <h2 style="margin-top:28px;">Event Bookings</h2>
  <?php if (count($eventRows)): ?>
    <table class="table">
      <thead>
        <tr>
          <th>Hotel</th>
          <th>Event</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($eventRows as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['hotel_name']) ?></td>
          <td><?= htmlspecialchars($e['event_name']) ?></td>
          <td><?= htmlspecialchars($e['event_date']) ?></td>
          <td>
            <a class="btn btn-danger" onclick="return confirmDelete();"
               href="guest_cancel_event.php?event_id=<?= (int)$e['event_id'] ?>&hotel_id=<?= (int)$e['hotel_id'] ?>">Cancel</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No event bookings yet.</p>
  <?php endif; ?>
</div>

<script src='../../js/script.js'></script>
</body>
</html>
