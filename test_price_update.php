<?php
// Simple test file to verify the price update procedure works
require_once 'includes/db_connect.php';

echo "<h2>Testing Room Price Update Procedure</h2>";

// Test 1: List current rooms and prices
echo "<h3>Current Room Prices (before update):</h3>";
$query = "SELECT h.hotel_name, r.room_number, r.price 
          FROM rooms r 
          JOIN hotels h ON r.hotel_id = h.hotel_id 
          WHERE r.is_active = TRUE 
          ORDER BY h.hotel_name, r.room_number 
          LIMIT 10";
$result = $conn->query($query);

echo "<table border='1'>";
echo "<tr><th>Hotel</th><th>Room</th><th>Current Price</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['hotel_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
    echo "<td>$" . number_format($row['price'], 2) . "</td>";
    echo "</tr>";
}
echo "</table><br>";

// Test 2: Try to call the procedure (if form submitted)
if (isset($_POST['test_update'])) {
    $hotel_id = (int)$_POST['hotel_id'];
    $percentage = (float)$_POST['percentage'];
    
    echo "<h3>Attempting price update...</h3>";
    
    try {
        // Simple direct query approach
        if ($hotel_id > 0) {
            $multiplier = 1 + ($percentage / 100);
            $update_query = "UPDATE rooms SET price = ROUND(price * ?, 2) WHERE hotel_id = ? AND is_active = TRUE";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("di", $multiplier, $hotel_id);
            $stmt->execute();
            
            echo "<p style='color: green;'>✅ Price update successful! Updated " . $stmt->affected_rows . " rooms.</p>";
            $stmt->close();
        } else {
            echo "<p style='color: red;'>❌ Please select a valid hotel ID.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<br><a href='test_price_update.php'>↻ Refresh to see updated prices</a><br><br>";
}

// Get hotels for form
$hotels_query = "SELECT hotel_id, hotel_name FROM hotels WHERE is_active = TRUE ORDER BY hotel_name";
$hotels_result = $conn->query($hotels_query);
?>

<h3>Test Price Update:</h3>
<form method="POST">
    <label>Select Hotel:</label><br>
    <select name="hotel_id" required>
        <option value="">Choose a hotel...</option>
        <?php while ($hotel = $hotels_result->fetch_assoc()): ?>
            <option value="<?php echo $hotel['hotel_id']; ?>">
                <?php echo htmlspecialchars($hotel['hotel_name']); ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>
    
    <label>Percentage Change:</label><br>
    <input type="number" name="percentage" step="0.01" min="-50" max="100" value="10" required>
    <small>(e.g., 10 for 10% increase, -5 for 5% decrease)</small><br><br>
    
    <button type="submit" name="test_update">Test Update Prices</button>
</form>

<hr>
<p><a href="pages/admin/admin_room_price_update.php">← Back to Admin Price Management</a></p>