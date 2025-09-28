<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_hotel.php");

// Fetch room types for dropdown
$room_types = $conn->query("SELECT * FROM room_types ORDER BY type_name");

if (isset($_POST['save'])) {
    $room_number = esc($_POST['room_number'] ?? '');
    $type_id = (int)($_POST['type_id'] ?? 0);
    $floor_number = (int)($_POST['floor_number'] ?? 1);
    $price = (float)($_POST['price'] ?? 0);
    $area_sqft = (float)($_POST['area_sqft'] ?? 0);
    $max_occupancy = (int)($_POST['max_occupancy'] ?? 2);
    $amenities = esc($_POST['amenities'] ?? '');
    $hid = (int)$_SESSION['hotel_id'];
    
    if ($room_number && $type_id && $price > 0) {
        // Convert amenities to JSON format
        $amenities_array = array_map('trim', explode(',', $amenities));
        $amenities_json = json_encode($amenities_array);
        
        $query = "INSERT INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities, is_active, maintenance_status) 
                  VALUES ($hid, '$room_number', $type_id, $floor_number, $price, " . 
                  ($area_sqft > 0 ? $area_sqft : "NULL") . ", $max_occupancy, '$amenities_json', TRUE, 'Available')";
        
        if ($conn->query($query)) {
            header("Location: hotel_rooms.php"); 
            exit();
        } else { 
            $error = "Failed to add room: " . $conn->error; 
        }
    } else { 
        $error = "Room number, type, and price are required."; 
    }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Add Room</title></head>
<body>
<div class="header"><div>Add Room</div><div class="nav"><a href="hotel_rooms.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group">
        <label>Room Number*</label>
        <input type="text" name="room_number" placeholder="e.g. 101, A-201" required>
    </div>
    
    <div class="form-group">
        <label>Room Type*</label>
        <select name="type_id" required>
            <option value="">Select Room Type</option>
            <?php while($type = $room_types->fetch_assoc()): ?>
                <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['type_name']) ?> - $<?= number_format($type['base_price'], 2) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Floor Number</label>
        <input type="number" name="floor_number" value="1" min="1" max="50">
    </div>
    
    <div class="form-group">
        <label>Price per Night*</label>
        <input type="number" step="0.01" name="price" min="0" placeholder="0.00" required>
    </div>
    
    <div class="form-group">
        <label>Area (sq ft)</label>
        <input type="number" step="0.01" name="area_sqft" placeholder="e.g. 300.50">
    </div>
    
    <div class="form-group">
        <label>Max Occupancy</label>
        <input type="number" name="max_occupancy" value="2" min="1" max="10">
    </div>
    
    <div class="form-group">
        <label>Amenities (comma-separated)</label>
        <textarea name="amenities" rows="3" placeholder="e.g. TV, WiFi, Air Conditioning, Mini Fridge"></textarea>
        <small style="color: #6b7280; font-size: 12px;">Enter amenities separated by commas</small>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary" name="save">Add Room</button>
        <a href="hotel_rooms.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body></html>
