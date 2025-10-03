<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$success_message = '';
$error_message = '';
$booking = null;

// Get booking details if booking_id provided
if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $guest_id = $_SESSION['guest_id'];
    
    // Verify this booking belongs to the guest and is completed
    $booking_query = "SELECT b.*, h.hotel_name, h.hotel_id, r.room_number
                      FROM bookings b
                      JOIN rooms r ON b.room_id = r.room_id
                      JOIN hotels h ON r.hotel_id = h.hotel_id
                      WHERE b.booking_id = ? AND b.guest_id = ? AND b.booking_status = 'Completed'";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param("ii", $booking_id, $guest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        $error_message = "Booking not found or not eligible for review.";
    } else {
        // Check if already reviewed
        $check_review = "SELECT review_id FROM reviews WHERE booking_id = ? AND guest_id = ?";
        $stmt2 = $conn->prepare($check_review);
        $stmt2->bind_param("ii", $booking_id, $guest_id);
        $stmt2->execute();
        if ($stmt2->get_result()->num_rows > 0) {
            $error_message = "You have already reviewed this booking.";
            $booking = null;
        }
    }
}

// Handle review submission
if (isset($_POST['submit_review']) && $booking) {
    $hotel_id = intval($_POST['hotel_id']);
    $booking_id = intval($_POST['booking_id']);
    $guest_id = $_SESSION['guest_id'];
    $rating = floatval($_POST['rating']);
    $title = trim($_POST['title']);
    $comment = trim($_POST['comment']);
    $service_rating = !empty($_POST['service_rating']) ? floatval($_POST['service_rating']) : null;
    $cleanliness_rating = !empty($_POST['cleanliness_rating']) ? floatval($_POST['cleanliness_rating']) : null;
    $location_rating = !empty($_POST['location_rating']) ? floatval($_POST['location_rating']) : null;
    $amenities_rating = !empty($_POST['amenities_rating']) ? floatval($_POST['amenities_rating']) : null;
    
    if ($rating < 1 || $rating > 5) {
        $error_message = "Please provide a valid rating between 1 and 5.";
    } else {
        $insert_query = "INSERT INTO reviews (hotel_id, guest_id, booking_id, rating, title, comment, 
                         service_rating, cleanliness_rating, location_rating, amenities_rating, is_approved) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiidssdddd", $hotel_id, $guest_id, $booking_id, $rating, $title, $comment,
                         $service_rating, $cleanliness_rating, $location_rating, $amenities_rating);
        
        if ($stmt->execute()) {
            $success_message = "Thank you! Your review has been submitted and is pending approval.";
            header("refresh:2;url=guest_my_bookings.php");
        } else {
            $error_message = "Error submitting review. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Write Review - SmartStay</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .review-form {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-info {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        .rating-group {
            margin: 20px 0;
        }
        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 30px;
            margin: 10px 0;
        }
        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        .star.active,
        .star:hover {
            color: #fbbf24;
        }
        .optional-ratings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .rating-input {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        .rating-input label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .rating-input select {
            width: 100%;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>Write Hotel Review</div>
    <div class="nav">
        <a href="guest_home.php">Home</a>
        <a href="guest_my_bookings.php">My Bookings</a>
        <a href="guest_profile.php">Profile</a>
        <a href="guest_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <div class="review-form">
        <h2>Share Your Experience</h2>
        
        <?php if ($success_message): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <?php if ($booking): ?>
            <div class="booking-info">
                <h3>📍 <?= htmlspecialchars($booking['hotel_name']) ?></h3>
                <p><strong>Room:</strong> <?= htmlspecialchars($booking['room_number']) ?> | 
                   <strong>Check-in:</strong> <?= date('M d, Y', strtotime($booking['check_in'])) ?> | 
                   <strong>Check-out:</strong> <?= date('M d, Y', strtotime($booking['check_out'])) ?></p>
            </div>
            
            <form method="post">
                <input type="hidden" name="hotel_id" value="<?= $booking['hotel_id'] ?>">
                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                <input type="hidden" name="rating" id="rating-value" value="5">
                
                <!-- Overall Rating -->
                <div class="rating-group">
                    <label><strong>Overall Rating *</strong></label>
                    <div class="star-rating" id="star-rating">
                        <span class="star active" data-rating="1">★</span>
                        <span class="star active" data-rating="2">★</span>
                        <span class="star active" data-rating="3">★</span>
                        <span class="star active" data-rating="4">★</span>
                        <span class="star active" data-rating="5">★</span>
                    </div>
                    <small>Click to rate from 1 to 5 stars</small>
                </div>
                
                <!-- Review Title -->
                <div class="form-group">
                    <label>Review Title *</label>
                    <input type="text" name="title" required maxlength="200" 
                           placeholder="Summarize your experience in one line">
                </div>
                
                <!-- Review Comment -->
                <div class="form-group">
                    <label>Your Review *</label>
                    <textarea name="comment" rows="6" required 
                              placeholder="Share your detailed experience..."></textarea>
                </div>
                
                <!-- Optional Detailed Ratings -->
                <h3>Detailed Ratings (Optional)</h3>
                <div class="optional-ratings">
                    <div class="rating-input">
                        <label>Service Quality</label>
                        <select name="service_rating">
                            <option value="">Not Rated</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    
                    <div class="rating-input">
                        <label>Cleanliness</label>
                        <select name="cleanliness_rating">
                            <option value="">Not Rated</option>
                            <option value="5">5 - Spotless</option>
                            <option value="4">4 - Very Clean</option>
                            <option value="3">3 - Clean</option>
                            <option value="2">2 - Needs Improvement</option>
                            <option value="1">1 - Unacceptable</option>
                        </select>
                    </div>
                    
                    <div class="rating-input">
                        <label>Location</label>
                        <select name="location_rating">
                            <option value="">Not Rated</option>
                            <option value="5">5 - Perfect</option>
                            <option value="4">4 - Great</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Average</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    
                    <div class="rating-input">
                        <label>Amenities</label>
                        <select name="amenities_rating">
                            <option value="">Not Rated</option>
                            <option value="5">5 - Outstanding</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Adequate</option>
                            <option value="2">2 - Limited</option>
                            <option value="1">1 - Insufficient</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    <a href="guest_my_bookings.php" class="btn">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p>No eligible booking found for review. Please select a completed booking from your <a href="guest_my_bookings.php">bookings page</a>.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Star rating functionality
const stars = document.querySelectorAll('.star');
const ratingValue = document.getElementById('rating-value');

stars.forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        ratingValue.value = rating;
        
        // Update star display
        stars.forEach(s => {
            if (s.getAttribute('data-rating') <= rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});
</script>
</body>
</html>
