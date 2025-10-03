<?php
require_once("includes/db_connect.php");

// Simple test page to demonstrate SQL functions
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test SQL Functions - SmartStay</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .test-section h3 { color: #2563eb; margin-top: 0; }
        .result { background: #f0f9ff; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .success { color: #059669; font-weight: bold; }
        .info { color: #0284c7; }
    </style>
</head>
<body>
<div class="header">
    <div>SQL Functions Test Page</div>
    <div class="nav">
        <a href="index.php">Home</a>
    </div>
</div>

<div class="main">
    <h1>SmartStay SQL Functions Demo</h1>
    <p>This page demonstrates the custom SQL functions in action.</p>

    <!-- Test 1: CalculateAge -->
    <div class="test-section">
        <h3>1. CalculateAge Function</h3>
        <p>Calculate guest ages from their date of birth:</p>
        <?php
        $query = "SELECT guest_id, name, date_of_birth, CalculateAge(date_of_birth) as age 
                  FROM guests 
                  WHERE date_of_birth IS NOT NULL 
                  LIMIT 5";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            echo '<table class="table">';
            echo '<thead><tr><th>ID</th><th>Name</th><th>Date of Birth</th><th>Age</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['guest_id']}</td>";
                echo "<td>{$row['name']}</td>";
                echo "<td>{$row['date_of_birth']}</td>";
                echo "<td class='success'>{$row['age']} years</td>";
                echo "</tr>";
            }
            echo '</tbody></table>';
        }
        ?>
    </div>

    <!-- Test 2: GetSeason -->
    <div class="test-section">
        <h3>2. GetSeason Function</h3>
        <p>Determine season for different dates:</p>
        <?php
        $test_dates = [
            '2025-12-25' => 'Christmas',
            '2025-07-04' => 'Summer',
            '2025-03-15' => 'Spring',
            '2025-10-31' => 'Fall'
        ];
        
        echo '<table class="table">';
        echo '<thead><tr><th>Date</th><th>Description</th><th>Season</th></tr></thead><tbody>';
        foreach ($test_dates as $date => $desc) {
            $query = "SELECT GetSeason('$date') as season";
            $result = $conn->query($query);
            if ($result && $row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>$date</td>";
                echo "<td>$desc</td>";
                echo "<td class='success'>{$row['season']}</td>";
                echo "</tr>";
            }
        }
        echo '</tbody></table>';
        ?>
    </div>

    <!-- Test 3: CalculateDynamicPrice -->
    <div class="test-section">
        <h3>3. CalculateDynamicPrice Function</h3>
        <p>Calculate dynamic pricing for rooms based on date and room type:</p>
        <?php
        $query = "SELECT 
                    r.room_id,
                    h.hotel_name,
                    r.room_number,
                    rt.type_name,
                    r.price as base_price,
                    CalculateDynamicPrice(r.price, '2025-12-25', rt.type_name) as christmas_price,
                    CalculateDynamicPrice(r.price, '2025-07-15', rt.type_name) as summer_price,
                    CalculateDynamicPrice(r.price, '2025-03-10', rt.type_name) as spring_price
                  FROM rooms r
                  JOIN hotels h ON r.hotel_id = h.hotel_id
                  JOIN room_types rt ON r.type_id = rt.type_id
                  LIMIT 5";
        
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            echo '<table class="table">';
            echo '<thead><tr><th>Hotel</th><th>Room</th><th>Type</th><th>Base Price</th><th>Christmas</th><th>Summer</th><th>Spring</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['hotel_name']}</td>";
                echo "<td>{$row['room_number']}</td>";
                echo "<td>{$row['type_name']}</td>";
                echo "<td>\${$row['base_price']}</td>";
                echo "<td class='success'>\${$row['christmas_price']}</td>";
                echo "<td class='success'>\${$row['summer_price']}</td>";
                echo "<td class='success'>\${$row['spring_price']}</td>";
                echo "</tr>";
            }
            echo '</tbody></table>';
            echo '<div class="result"><span class="info">Note:</span> Prices vary by season (Peak/High/Low) and room type (Suite/Deluxe get multipliers)</div>';
        }
        ?>
    </div>

    <!-- Test 4: CalculateGuestSatisfactionScore -->
    <div class="test-section">
        <h3>4. CalculateGuestSatisfactionScore Function</h3>
        <p>Calculate satisfaction scores for hotels based on reviews:</p>
        <?php
        $query = "SELECT 
                    h.hotel_id,
                    h.hotel_name,
                    COUNT(r.review_id) as total_reviews,
                    ROUND(AVG(r.rating), 2) as avg_rating,
                    CalculateGuestSatisfactionScore(h.hotel_id) as satisfaction_score
                  FROM hotels h
                  LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.is_approved = 1
                  GROUP BY h.hotel_id, h.hotel_name
                  HAVING total_reviews > 0
                  ORDER BY satisfaction_score DESC
                  LIMIT 5";
        
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            echo '<table class="table">';
            echo '<thead><tr><th>Rank</th><th>Hotel</th><th>Reviews</th><th>Avg Rating</th><th>Satisfaction Score</th></tr></thead><tbody>';
            $rank = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>$rank</td>";
                echo "<td>{$row['hotel_name']}</td>";
                echo "<td>{$row['total_reviews']}</td>";
                echo "<td>{$row['avg_rating']}/5.0</td>";
                echo "<td class='success'>{$row['satisfaction_score']}%</td>";
                echo "</tr>";
                $rank++;
            }
            echo '</tbody></table>';
            echo '<div class="result"><span class="info">Formula:</span> (Average Rating × 70%) + (Review Count × 20%) + (Response Rate × 10%)</div>';
        }
        ?>
    </div>

    <!-- Real Implementation Example -->
    <div class="test-section">
        <h3>5. Real Implementation in Booking System</h3>
        <p><strong>Where these functions are used in your application:</strong></p>
        <ul>
            <li><strong>CalculateDynamicPrice:</strong> Used in <code>pages/guest/guest_book_room_dates.php</code> (line 48) to calculate room prices based on check-in date and room type.</li>
            <li><strong>CalculateAge:</strong> Can be used for guest demographics and age-restricted services.</li>
            <li><strong>GetSeason:</strong> Helper function for CalculateDynamicPrice to determine peak/off-peak seasons.</li>
            <li><strong>CalculateGuestSatisfactionScore:</strong> Can be used in admin dashboard to rank hotels by guest satisfaction.</li>
        </ul>
        
        <div class="result">
            <p><strong>Test it yourself:</strong></p>
            <ol>
                <li>Go to <a href="pages/guest/guest_login.php">Guest Login</a> (use email: john.smith@email.com, password: guest123)</li>
                <li>Search for a hotel and select a room</li>
                <li>Try booking for different dates (Christmas, Summer, etc.) and see prices change!</li>
                <li>The <code>CalculateDynamicPrice</code> function automatically adjusts prices based on:
                    <ul>
                        <li>Season (Peak/High/Low)</li>
                        <li>Days in advance (Last minute = higher, Early booking = discount)</li>
                        <li>Room type (Suite/Deluxe get premium pricing)</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #f0fdf4; border-radius: 8px;">
        <h3>🎯 Quick Test Credentials</h3>
        <p><strong>Guest Login:</strong> john.smith@email.com / guest123</p>
        <p><strong>Hotel Login:</strong> contact@grandplaza.com / hotel123</p>
        <p><strong>Admin Login:</strong> admin@smartstay.com / admin123</p>
    </div>

</div>
</body>
</html>
