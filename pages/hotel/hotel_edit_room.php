<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id']; 
$id = (int)($_GET['id'] ?? 0);

// Fetch room details with room type information
$room_query = "SELECT r.*, rt.type_name 
               FROM rooms r 
               JOIN room_types rt ON r.type_id = rt.type_id 
               WHERE r.room_id = $id AND r.hotel_id = $hid";
$r = $conn->query($room_query)->fetch_assoc();

if (!$r) { 
    header("Location: hotel_rooms.php"); 
    exit(); 
}

// Fetch all room types for dropdown
$room_types = $conn->query("SELECT * FROM room_types ORDER BY type_name");

if (isset($_POST['save'])) {
    $room_number = esc($_POST['room_number'] ?? '');
    $type_id = (int)($_POST['type_id'] ?? 0);
    $floor_number = (int)($_POST['floor_number'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $area_sqft = (float)($_POST['area_sqft'] ?? 0);
    $max_occupancy = (int)($_POST['max_occupancy'] ?? 2);
    $amenities = esc($_POST['amenities'] ?? '');
    $maintenance_status = esc($_POST['maintenance_status'] ?? 'Available');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($room_number && $type_id && $price > 0) {
        // Convert amenities to JSON format
        $amenities_array = array_map('trim', explode(',', $amenities));
        $amenities_json = json_encode($amenities_array);
        
        $query = "UPDATE rooms SET 
                    room_number = '$room_number',
                    type_id = $type_id,
                    floor_number = $floor_number,
                    price = $price,
                    area_sqft = " . ($area_sqft > 0 ? $area_sqft : "NULL") . ",
                    max_occupancy = $max_occupancy,
                    amenities = '$amenities_json',
                    maintenance_status = '$maintenance_status',
                    is_active = $is_active,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE room_id = $id AND hotel_id = $hid";
        
        if ($conn->query($query)) {
            header("Location: hotel_rooms.php"); 
            exit();
        } else { 
            $error = "Failed to update room: " . $conn->error; 
        }
    } else { 
        $error = "Room number, type, and price are required."; 
    }
}

// Convert JSON amenities back to comma-separated string for display
$amenities_display = '';
if ($r['amenities']) {
    $amenities_array = json_decode($r['amenities'], true);
    if (is_array($amenities_array)) {
        $amenities_display = implode(', ', $amenities_array);
    }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Edit Room</title></head>
<body>
<div class="header"><div>Edit Room</div><div class="nav"><a href="hotel_rooms.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group">
        <label>Room Number*</label>
        <input type="text" name="room_number" value="<?= htmlspecialchars($r['room_number']) ?>" required>
    </div>
    
    <div class="form-group">
        <label>Room Type*</label>
        <select name="type_id" required>
            <option value="">Select Room Type</option>
            <?php while($type = $room_types->fetch_assoc()): ?>
                <option value="<?= $type['type_id'] ?>" <?= $type['type_id'] == $r['type_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type['type_name']) ?> - $<?= number_format($type['base_price'], 2) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Floor Number</label>
        <input type="number" name="floor_number" value="<?= $r['floor_number'] ?>" min="1" max="50">
    </div>
    
    <div class="form-group">
        <label>Price per Night*</label>
        <input type="number" step="0.01" name="price" value="<?= $r['price'] ?>" min="0" required>
    </div>
    
    <div class="form-group">
        <label>Area (sq ft)</label>
        <input type="number" step="0.01" name="area_sqft" value="<?= $r['area_sqft'] ?>">
    </div>
    
    <div class="form-group">
        <label>Max Occupancy</label>
        <input type="number" name="max_occupancy" value="<?= $r['max_occupancy'] ?>" min="1" max="10">
    </div>
    
    <div class="form-group">
        <label>Amenities (comma-separated)</label>
        <textarea name="amenities" rows="3"><?= htmlspecialchars($amenities_display) ?></textarea>
    </div>
    
    <div class="form-group">
        <label>Maintenance Status</label>
        <select name="maintenance_status">
            <option value="Available" <?= $r['maintenance_status'] == 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="Maintenance" <?= $r['maintenance_status'] == 'Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
            <option value="Out of Order" <?= $r['maintenance_status'] == 'Out of Order' ? 'selected' : '' ?>>Out of Order</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_active" <?= $r['is_active'] ? 'checked' : '' ?>>
            Room is Active
        </label>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary" name="save">Update Room</button>
        <a href="hotel_rooms.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body></html>
