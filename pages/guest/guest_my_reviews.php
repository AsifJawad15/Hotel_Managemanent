<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$guest_id = $_SESSION['guest_id'];

// Get all reviews by this guest
$reviews_query = "SELECT r.*, h.hotel_name, h.city, b.check_in, b.check_out
                  FROM reviews r
                  JOIN hotels h ON r.hotel_id = h.hotel_id
                  LEFT JOIN bookings b ON r.booking_id = b.booking_id
                  WHERE r.guest_id = ?
                  ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Reviews - SmartStay</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .review-card {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        .hotel-info h3 {
            margin: 0 0 5px 0;
            color: #1e293b;
        }
        .rating-display {
            font-size: 24px;
            color: #fbbf24;
        }
        .review-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .status-approved {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .detailed-ratings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
        }
        .rating-item {
            font-size: 14px;
        }
        .admin-response {
            background: #eff6ff;
            padding: 15px;
            border-left: 4px solid #3b82f6;
            margin-top: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>My Reviews</div>
    <div class="nav">
        <a href="guest_home.php">Home</a>
        <a href="guest_my_bookings.php">My Bookings</a>
        <a href="guest_profile.php">Profile</a>
        <a href="guest_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <h2>My Hotel Reviews</h2>
    <p>Your reviews help other travelers make informed decisions!</p>
    
    <?php if ($reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="hotel-info">
                        <h3>🏨 <?= htmlspecialchars($review['hotel_name']) ?></h3>
                        <p style="color: #64748b; margin: 0;">
                            📍 <?= htmlspecialchars($review['city']) ?> | 
                            📅 Stayed: <?= date('M d, Y', strtotime($review['check_in'])) ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <div class="rating-display">
                            <?php
                            $rating = $review['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '★' : '☆';
                            }
                            echo " " . number_format($rating, 1);
                            ?>
                        </div>
                        <span class="review-status <?= $review['is_approved'] ? 'status-approved' : 'status-pending' ?>">
                            <?= $review['is_approved'] ? '✓ Approved' : '⏳ Pending Review' ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($review['title']): ?>
                    <h4 style="margin: 10px 0;"><?= htmlspecialchars($review['title']) ?></h4>
                <?php endif; ?>
                
                <p style="color: #475569; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                </p>
                
                <?php if ($review['service_rating'] || $review['cleanliness_rating'] || 
                         $review['location_rating'] || $review['amenities_rating']): ?>
                    <div class="detailed-ratings">
                        <?php if ($review['service_rating']): ?>
                            <div class="rating-item">
                                <strong>Service:</strong> <?= $review['service_rating'] ?>/5 ⭐
                            </div>
                        <?php endif; ?>
                        <?php if ($review['cleanliness_rating']): ?>
                            <div class="rating-item">
                                <strong>Cleanliness:</strong> <?= $review['cleanliness_rating'] ?>/5 ⭐
                            </div>
                        <?php endif; ?>
                        <?php if ($review['location_rating']): ?>
                            <div class="rating-item">
                                <strong>Location:</strong> <?= $review['location_rating'] ?>/5 ⭐
                            </div>
                        <?php endif; ?>
                        <?php if ($review['amenities_rating']): ?>
                            <div class="rating-item">
                                <strong>Amenities:</strong> <?= $review['amenities_rating'] ?>/5 ⭐
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($review['admin_response']): ?>
                    <div class="admin-response">
                        <strong>🏨 Hotel Response:</strong><br>
                        <?= nl2br(htmlspecialchars($review['admin_response'])) ?>
                    </div>
                <?php endif; ?>
                
                <p style="color: #94a3b8; font-size: 13px; margin-top: 15px;">
                    Posted on <?= date('F j, Y \a\t g:i A', strtotime($review['created_at'])) ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
            <h3>📝 No Reviews Yet</h3>
            <p>You haven't written any reviews yet. Complete a booking and share your experience!</p>
            <a href="guest_my_bookings.php" class="btn btn-primary">View My Bookings</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
