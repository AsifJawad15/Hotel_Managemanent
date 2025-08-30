<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$hid = (int)($_GET['hotel_id'] ?? 0);
$hotel = $conn->query("SELECT * FROM hotels WHERE hotel_id=$hid")->fetch_assoc();
if (!$hotel) { header("Location: guest_search.php"); exit(); }

// Rooms
$roomsRes = $conn->query("SELECT * FROM rooms WHERE hotel_id=$hid ORDER BY room_id");
$rooms = [];
while ($roomsRes && $r = $roomsRes->fetch_assoc()) $rooms[] = $r;

// Bookings map (room_id => guest_id who booked)
// Note: with the new date-based system, we still treat any existing booking row as "Booked".
$ids = array_map(fn($x) => $x['room_id'], $rooms);
$bookings = [];
if ($ids) {
  $in = implode(',', array_map('intval', $ids));
  $b = $conn->query("SELECT room_id, guest_id FROM bookings WHERE room_id IN ($in)");
  while ($b && $row = $b->fetch_assoc()) $bookings[(int)$row['room_id']] = (int)$row['guest_id'];
}

// Events
$events = $conn->query("SELECT * FROM events WHERE hotel_id=$hid ORDER BY event_date");

$guest_id = (int)$_SESSION['guest_id'];
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

  <h3>Gallery</h3>
  <div class="gallery">
    <?php
      $imgs = $conn->query("SELECT * FROM hotel_images WHERE hotel_id=$hid ORDER BY image_id DESC");
      if ($imgs && $imgs->num_rows) {
        while ($im = $imgs->fetch_assoc()):
    ?>
      <img src="../../images/hotels/<?= htmlspecialchars($im['image_path']) ?>" alt="Hotel image">
    <?php endwhile; } else { echo "<p>No images yet.</p>"; } ?>
  </div>

  <h3 style="margin-top:24px;">Rooms</h3>
  <table class="table">
    <thead><tr><th>#</th><th>Type</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($rooms as $r):
        $rid = (int)$r['room_id'];
        $bookedBy = $bookings[$rid] ?? null;
      ?>
      <tr>
        <td><?= htmlspecialchars($r['room_number']) ?></td>
        <td><?= htmlspecialchars($r['type']) ?></td>
        <td><?= htmlspecialchars($r['price']) ?></td>
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
    <thead><tr><th>Name</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
      <?php while ($e = $events->fetch_assoc()):
        $eid = (int)$e['event_id'];
        $table = "hotel{$hid}_event{$eid}";
        $ck = $conn->query("SELECT * FROM $table WHERE guest_id=$guest_id");
        $mine = ($ck && $ck->num_rows > 0);
      ?>
      <tr>
        <td><?= htmlspecialchars($e['event_name']) ?></td>
        <td><?= htmlspecialchars($e['event_date']) ?></td>
        <td>
          <?php if (!$mine): ?>
            <a class="btn btn-primary" href="guest_book_event.php?event_id=<?= $eid ?>&hotel_id=<?= $hid ?>">Book Event</a>
          <?php else: ?>
            <a class="btn btn-danger" href="guest_cancel_event.php?event_id=<?= $eid ?>&hotel_id=<?= $hid ?>">Cancel</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
