<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$hid = (int)($_GET['hotel_id'] ?? 0);
$hotel = $conn->query("SELECT * FROM hotels WHERE hotel_id=$hid")->fetch_assoc();
if (!$hotel) { header("Location: guest_search.php"); exit(); }

// Rooms
$roomsRes = $conn->query("SELECT r.*, rt.type_name, rt.description AS type_description, rt.amenities AS type_amenities
                          FROM rooms r
                          JOIN room_types rt ON r.type_id = rt.type_id
                          WHERE r.hotel_id=$hid
                          ORDER BY r.room_number");
$rooms = [];
while ($roomsRes && $r = $roomsRes->fetch_assoc()) $rooms[] = $r;

// Bookings map (room_id => guest_id who booked)
// Note: with the new date-based system, we still treat any existing booking row as "Booked".
$ids = array_map(fn($x) => $x['room_id'], $rooms);
$bookings = [];
if ($ids) {
  $in = implode(',', array_map('intval', $ids));
  $sql = "SELECT room_id, guest_id
          FROM bookings
          WHERE room_id IN ($in)
            AND booking_status IN ('Confirmed','Completed')
            AND check_out >= CURDATE()";
  $b = $conn->query($sql);
  while ($b && $row = $b->fetch_assoc()) $bookings[(int)$row['room_id']] = (int)$row['guest_id'];
}

// Events
$guest_id = (int)$_SESSION['guest_id'];
$eventsRes = $conn->query("SELECT * FROM events WHERE hotel_id=$hid ORDER BY event_date");
$eventBookings = [];
$ebRes = $conn->query("SELECT event_id FROM event_bookings WHERE guest_id=$guest_id");
while ($ebRes && $row = $ebRes->fetch_assoc()) {
  $eventBookings[(int)$row['event_id']] = true;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../../css/style.css">
  <title>Hotel Details</title>
</head>
<body>
<div class="header">
  <div><?= htmlspecialchars($hotel['hotel_name']) ?></div>
  <div class="nav">
    <a href="guest_search.php">Back</a>
    <a href="guest_my_bookings.php">My Bookings</a>
    <a href="guest_logout.php">Logout</a>
  </div>
</div>

<div class="main">
  <h3>Description</h3>
  <p><?= nl2br(htmlspecialchars($hotel['description'])) ?></p>

  <h3 style="margin-top:24px;">Rooms</h3>
  <table class="table">
    <thead><tr><th>#</th><th>Type</th><th>Description</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($rooms as $r):
        $rid = (int)$r['room_id'];
        $bookedBy = $bookings[$rid] ?? null;
        $typeName = $r['type_name'] ?? 'N/A';
        $typeDesc = $r['type_description'] ? substr($r['type_description'], 0, 90) . (strlen($r['type_description']) > 90 ? '…' : '') : '—';
      ?>
      <tr>
        <td><?= htmlspecialchars($r['room_number']) ?></td>
        <td><?= htmlspecialchars($typeName) ?></td>
        <td><?= htmlspecialchars($typeDesc) ?></td>
        <td>$<?= number_format((float)$r['price'], 2) ?></td>
        <td><?= $bookedBy ? 'Booked' : 'Available' ?></td>
        <td>
          <?php if (!$bookedBy): ?>
            <!-- UPDATED: go to date-selection page before booking -->
            <a class="btn btn-primary" href="guest_book_room_dates.php?room_id=<?= $rid ?>&hotel_id=<?= $hid ?>">Book</a>
          <?php elseif ($bookedBy == $guest_id): ?>
            <!-- With date-based bookings, cancellation happens from My Bookings by booking_id -->
            <a class="btn btn-secondary" href="guest_my_bookings.php">Booked by you</a>
          <?php else: ?>
            <span class="btn btn-secondary" style="opacity:.6;cursor:not-allowed;">N/A</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h3 style="margin-top:24px;">Events</h3>
  <table class="table">
    <thead><tr><th>Name</th><th>Type</th><th>Date</th><th>Status</th><th>Price</th><th>Action</th></tr></thead>
    <tbody>
      <?php if ($eventsRes && $eventsRes->num_rows):
        while ($e = $eventsRes->fetch_assoc()):
          $eid = (int)$e['event_id'];
          $mine = $eventBookings[$eid] ?? false;
      ?>
      <tr>
        <td><?= htmlspecialchars($e['event_name']) ?></td>
        <td><?= htmlspecialchars($e['event_type']) ?></td>
        <td><?= htmlspecialchars($e['event_date']) ?></td>
        <td><?= htmlspecialchars($e['event_status']) ?></td>
        <td><?= $e['price'] ? '$' . number_format((float)$e['price'], 2) : 'Free' ?></td>
        <td>
          <?php if (!$mine): ?>
            <a class="btn btn-primary" href="guest_book_event.php?event_id=<?= $eid ?>&hotel_id=<?= $hid ?>">Book Event</a>
          <?php else: ?>
            <a class="btn btn-danger" href="guest_cancel_event.php?event_id=<?= $eid ?>&hotel_id=<?= $hid ?>">Cancel</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="6">No events scheduled yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
