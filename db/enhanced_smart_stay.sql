-- Enhanced Smart Stay Database Schema
-- This file contains all DDL, DML, Constraints, Views, Procedures, Functions, and Triggers
-- Run this file in phpMyAdmin SQL tab

-- =====================================================
-- DATABASE AND TABLE CREATION (DDL)
-- =====================================================

DROP DATABASE IF EXISTS smart_stay;
CREATE DATABASE smart_stay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_stay;

-- Core Tables with Enhanced Structure
CREATE TABLE guests (
    guest_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Other',
    nationality VARCHAR(50),
    address TEXT,
    loyalty_points INT DEFAULT 0,
    membership_level ENUM('Bronze', 'Silver', 'Gold', 'Platinum') DEFAULT 'Bronze',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_membership (membership_level),
    INDEX idx_loyalty (loyalty_points)
);

CREATE TABLE hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    description TEXT,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    phone VARCHAR(20),
    website VARCHAR(255),
    star_rating DECIMAL(2,1) CHECK (star_rating >= 1.0 AND star_rating <= 5.0),
    total_rooms INT DEFAULT 0,
    amenities JSON,
    check_in_time TIME DEFAULT '15:00:00',
    check_out_time TIME DEFAULT '11:00:00',
    established_year YEAR,
    license_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_city (city),
    INDEX idx_rating (star_rating),
    INDEX idx_name (hotel_name)
);

CREATE TABLE room_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    max_occupancy INT NOT NULL DEFAULT 2,
    base_price DECIMAL(10,2) NOT NULL,
    amenities JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    type_id INT NOT NULL,
    floor_number INT,
    price DECIMAL(10,2) NOT NULL,
    area_sqft DECIMAL(8,2),
    max_occupancy INT DEFAULT 2,
    amenities JSON,
    is_active BOOLEAN DEFAULT TRUE,
    maintenance_status ENUM('Available', 'Maintenance', 'Out of Order') DEFAULT 'Available',
    last_cleaned TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES room_types(type_id),
    UNIQUE KEY unique_hotel_room (hotel_id, room_number),
    INDEX idx_hotel_type (hotel_id, type_id),
    INDEX idx_price (price),
    INDEX idx_floor (floor_number)
);

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    adults INT DEFAULT 1,
    children INT DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('Confirmed', 'Cancelled', 'Completed', 'No-Show') DEFAULT 'Confirmed',
    payment_status ENUM('Pending', 'Paid', 'Partial', 'Refunded') DEFAULT 'Pending',
    booking_source ENUM('Website', 'Phone', 'Walk-in', 'Third-party') DEFAULT 'Website',
    special_requests TEXT,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_guest_booking (guest_id, booking_status),
    INDEX idx_room_dates (room_id, check_in, check_out),
    INDEX idx_check_in (check_in),
    INDEX idx_booking_status (booking_status),
    CONSTRAINT check_dates CHECK (check_out > check_in),
    CONSTRAINT check_occupancy CHECK (adults >= 1 AND children >= 0)
);

CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    venue VARCHAR(100),
    max_participants INT,
    current_participants INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    event_type ENUM('Conference', 'Wedding', 'Meeting', 'Party', 'Workshop', 'Other') DEFAULT 'Other',
    event_status ENUM('Upcoming', 'Active', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
    organizer_name VARCHAR(100),
    organizer_contact VARCHAR(100),
    requirements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_hotel_date (hotel_id, event_date),
    INDEX idx_event_status (event_status),
    INDEX idx_event_type (event_type),
    CONSTRAINT check_participants CHECK (current_participants <= max_participants),
    CONSTRAINT check_times CHECK (end_time > start_time OR end_time IS NULL)
);

CREATE TABLE event_bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    guest_id INT NOT NULL,
    participants INT DEFAULT 1,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    booking_status ENUM('Confirmed', 'Cancelled', 'Attended', 'No-Show') DEFAULT 'Confirmed',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    special_requirements TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_guest (event_id, guest_id),
    INDEX idx_event_status (event_id, booking_status)
);

CREATE TABLE hotel_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_id INT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_type ENUM('Hotel', 'Room', 'Amenity', 'Event') DEFAULT 'Hotel',
    caption TEXT,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_hotel_type (hotel_id, image_type),
    INDEX idx_primary (hotel_id, is_primary)
);

CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    role ENUM('Super Admin', 'Admin', 'Manager') DEFAULT 'Admin',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Financial Tables
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Online', 'Bank Transfer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    payment_status ENUM('Success', 'Failed', 'Pending', 'Refunded') DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    gateway_response TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking_payment (booking_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_transaction (transaction_id)
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    guest_id INT NOT NULL,
    booking_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL CHECK (rating >= 1.0 AND rating <= 5.0),
    title VARCHAR(200),
    comment TEXT,
    service_rating DECIMAL(2,1) CHECK (service_rating >= 1.0 AND service_rating <= 5.0),
    cleanliness_rating DECIMAL(2,1) CHECK (cleanliness_rating >= 1.0 AND cleanliness_rating <= 5.0),
    location_rating DECIMAL(2,1) CHECK (location_rating >= 1.0 AND location_rating <= 5.0),
    amenities_rating DECIMAL(2,1) CHECK (amenities_rating >= 1.0 AND amenities_rating <= 5.0),
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved BOOLEAN DEFAULT FALSE,
    admin_response TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_review (booking_id),
    INDEX idx_hotel_rating (hotel_id, rating),
    INDEX idx_guest_review (guest_id)
);

-- Inventory and Services
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    service_type ENUM('Room Service', 'Laundry', 'Transport', 'Spa', 'Restaurant', 'Other') DEFAULT 'Other',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_hotel_service (hotel_id, service_type)
);

CREATE TABLE service_bookings (
    service_booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    service_date DATE,
    service_time TIME,
    status ENUM('Requested', 'Confirmed', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Requested',
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    INDEX idx_booking_service (booking_id, service_date)
);

-- Staff Management
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    position VARCHAR(100),
    department ENUM('Front Desk', 'Housekeeping', 'Maintenance', 'Restaurant', 'Security', 'Management', 'Other') DEFAULT 'Other',
    salary DECIMAL(10,2),
    hire_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_hotel_dept (hotel_id, department),
    INDEX idx_active_staff (hotel_id, is_active)
);

-- System Logs
CREATE TABLE system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('Guest', 'Hotel', 'Admin', 'System') NOT NULL,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_type, user_id, created_at),
    INDEX idx_table_record (table_name, record_id)
);

-- =====================================================
-- CONSTRAINTS AND INDEXES
-- =====================================================

-- Additional Foreign Key Constraints
ALTER TABLE rooms 
ADD CONSTRAINT fk_rooms_hotel 
FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE rooms 
ADD CONSTRAINT fk_rooms_type 
FOREIGN KEY (type_id) REFERENCES room_types(type_id) ON UPDATE CASCADE;

-- Check Constraints
ALTER TABLE hotels 
ADD CONSTRAINT chk_star_rating 
CHECK (star_rating IS NULL OR (star_rating >= 1.0 AND star_rating <= 5.0));

ALTER TABLE rooms 
ADD CONSTRAINT chk_price_positive 
CHECK (price > 0);

ALTER TABLE bookings 
ADD CONSTRAINT chk_amounts_positive 
CHECK (total_amount >= 0 AND discount_amount >= 0 AND tax_amount >= 0 AND final_amount >= 0);

-- Composite Indexes for Performance
CREATE INDEX idx_booking_performance ON bookings(hotel_id, check_in, check_out, booking_status);
CREATE INDEX idx_events_performance ON events(hotel_id, event_date, event_status);
CREATE INDEX idx_guest_loyalty ON guests(loyalty_points DESC, membership_level);

-- =====================================================
-- SAMPLE DATA (DML)
-- =====================================================

-- Insert Room Types
INSERT INTO room_types (type_name, description, max_occupancy, base_price, amenities) VALUES
('Standard Single', 'Basic single occupancy room with essential amenities', 1, 80.00, '["TV", "WiFi", "Air Conditioning"]'),
('Standard Double', 'Double occupancy room with twin beds or one double bed', 2, 120.00, '["TV", "WiFi", "Air Conditioning", "Mini Fridge"]'),
('Deluxe Room', 'Spacious room with premium amenities and city view', 2, 180.00, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony"]'),
('Executive Suite', 'Luxury suite with separate living area', 4, 300.00, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen", "Living Room"]'),
('Presidential Suite', 'Premium suite with top-tier amenities', 6, 500.00, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen", "Living Room", "Jacuzzi", "Butler Service"]');

-- Insert Sample Admin
INSERT INTO admins (username, password, email, full_name, role) VALUES 
('admin', '12345678', 'admin@smartstay.com', 'System Administrator', 'Super Admin'),
('manager', 'manager123', 'manager@smartstay.com', 'Hotel Manager', 'Manager');

-- Insert Sample Hotels
INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities) VALUES
('Grand Plaza Hotel', 'plaza@hotels.com', 'plaza123', 'Luxurious hotel in the heart of the city with world-class amenities', '123 Main Street', 'New York', 'NY', 'USA', '10001', '+1-555-0101', 4.5, 150, '["WiFi", "Pool", "Gym", "Restaurant", "Spa", "Conference Rooms"]'),
('Ocean View Resort', 'ocean@hotels.com', 'ocean123', 'Beachfront resort with stunning ocean views and premium facilities', '456 Beach Road', 'Miami', 'FL', 'USA', '33101', '+1-555-0102', 4.8, 200, '["WiFi", "Pool", "Beach Access", "Restaurant", "Bar", "Water Sports"]'),
('Mountain Lodge', 'mountain@hotels.com', 'mountain123', 'Cozy mountain retreat perfect for nature lovers and adventure seekers', '789 Mountain Path', 'Denver', 'CO', 'USA', '80201', '+1-555-0103', 4.2, 80, '["WiFi", "Fireplace", "Hiking Trails", "Restaurant", "Ski Access"]');

-- Insert Sample Guests
INSERT INTO guests (name, email, password, phone, date_of_birth, gender, nationality, loyalty_points, membership_level) VALUES
('John Smith', 'john@email.com', 'john123', '+1-555-1001', '1990-05-15', 'Male', 'American', 1200, 'Gold'),
('Emma Johnson', 'emma@email.com', 'emma123', '+1-555-1002', '1985-08-22', 'Female', 'American', 800, 'Silver'),
('Michael Brown', 'michael@email.com', 'mike123', '+1-555-1003', '1992-03-10', 'Male', 'Canadian', 2500, 'Platinum'),
('Sarah Davis', 'sarah@email.com', 'sarah123', '+1-555-1004', '1988-12-05', 'Female', 'British', 400, 'Bronze');

-- Insert Sample Rooms
INSERT INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
-- Grand Plaza Hotel (hotel_id: 1)
(1, '101', 1, 1, 85.00, 250, 1, '["TV", "WiFi", "Air Conditioning"]'),
(1, '102', 2, 1, 125.00, 300, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge"]'),
(1, '201', 3, 2, 185.00, 450, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View"]'),
(1, '301', 4, 3, 310.00, 800, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen"]'),
(1, '401', 5, 4, 520.00, 1200, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen", "Jacuzzi"]'),
-- Ocean View Resort (hotel_id: 2)
(2, 'A101', 2, 1, 140.00, 350, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Ocean View"]'),
(2, 'A201', 3, 2, 200.00, 500, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Balcony"]'),
(2, 'B301', 4, 3, 350.00, 900, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Kitchen"]');

-- Insert Sample Events
INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, price, event_type, organizer_name, organizer_contact) VALUES
(1, 'Business Conference 2024', 'Annual business networking conference', '2024-12-15', '09:00:00', '17:00:00', 'Main Conference Hall', 200, 150.00, 'Conference', 'Business Association', 'conference@business.com'),
(1, 'Wedding Reception', 'Elegant wedding celebration', '2024-11-20', '18:00:00', '23:00:00', 'Grand Ballroom', 150, 250.00, 'Wedding', 'John & Jane', 'wedding@email.com'),
(2, 'Beach Yoga Retreat', 'Relaxing yoga sessions by the ocean', '2024-10-10', '07:00:00', '18:00:00', 'Beach Area', 50, 80.00, 'Workshop', 'Yoga Studio', 'yoga@studio.com'),
(3, 'Mountain Adventure Workshop', 'Learn outdoor survival skills', '2024-11-05', '08:00:00', '16:00:00', 'Training Center', 30, 120.00, 'Workshop', 'Adventure Group', 'adventure@group.com');

-- Insert Sample Services
INSERT INTO services (hotel_id, service_name, description, price, service_type) VALUES
(1, 'Room Service', '24/7 in-room dining service', 25.00, 'Room Service'),
(1, 'Laundry Service', 'Professional cleaning and pressing', 15.00, 'Laundry'),
(1, 'Airport Transfer', 'Luxury car service to/from airport', 50.00, 'Transport'),
(2, 'Spa Treatment', 'Relaxing massage and wellness treatments', 100.00, 'Spa'),
(2, 'Restaurant Dining', 'Fine dining experience', 75.00, 'Restaurant'),
(3, 'Guided Hiking', 'Professional mountain guide service', 60.00, 'Other');

-- =====================================================
-- VIEWS
-- =====================================================

-- Hotel Performance Summary View
CREATE VIEW hotel_performance AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    h.city,
    h.star_rating,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    AVG(rev.rating) as average_rating,
    COUNT(DISTINCT rev.review_id) as total_reviews,
    SUM(b.final_amount) as total_revenue,
    COUNT(DISTINCT CASE WHEN b.booking_status = 'Confirmed' THEN b.booking_id END) as active_bookings
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id
WHERE h.is_active = TRUE
GROUP BY h.hotel_id, h.hotel_name, h.city, h.star_rating;

-- Guest Booking History View
CREATE VIEW guest_booking_history AS
SELECT 
    g.guest_id,
    g.name as guest_name,
    g.email,
    g.membership_level,
    b.booking_id,
    h.hotel_name,
    r.room_number,
    rt.type_name,
    b.check_in,
    b.check_out,
    DATEDIFF(b.check_out, b.check_in) as nights,
    b.final_amount,
    b.booking_status,
    b.payment_status
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
ORDER BY b.created_at DESC;

-- Room Occupancy View
CREATE VIEW room_occupancy AS
SELECT 
    h.hotel_name,
    r.room_id,
    r.room_number,
    rt.type_name,
    r.price,
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM bookings b 
            WHERE b.room_id = r.room_id 
            AND b.booking_status = 'Confirmed' 
            AND CURDATE() BETWEEN b.check_in AND b.check_out
        ) THEN 'Occupied'
        WHEN r.maintenance_status != 'Available' THEN r.maintenance_status
        ELSE 'Available'
    END as current_status,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_revenue
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
LEFT JOIN bookings b ON r.room_id = b.room_id
GROUP BY h.hotel_name, r.room_id, r.room_number, rt.type_name, r.price, current_status;

-- Monthly Revenue Report View
CREATE VIEW monthly_revenue_report AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    YEAR(b.check_in) as revenue_year,
    MONTH(b.check_in) as revenue_month,
    MONTHNAME(b.check_in) as month_name,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.total_amount) as gross_revenue,
    SUM(b.discount_amount) as total_discounts,
    SUM(b.tax_amount) as total_taxes,
    SUM(b.final_amount) as net_revenue,
    AVG(b.final_amount) as average_booking_value
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN bookings b ON r.room_id = b.room_id
WHERE b.booking_status IN ('Confirmed', 'Completed')
GROUP BY h.hotel_id, h.hotel_name, revenue_year, revenue_month
ORDER BY revenue_year DESC, revenue_month DESC;

-- Event Participation View
CREATE VIEW event_participation AS
SELECT 
    e.event_id,
    e.event_name,
    h.hotel_name,
    e.event_date,
    e.event_type,
    e.max_participants,
    COUNT(eb.booking_id) as registered_participants,
    (e.max_participants - COUNT(eb.booking_id)) as available_spots,
    SUM(eb.amount_paid) as total_revenue,
    AVG(eb.amount_paid) as average_ticket_price,
    CASE 
        WHEN COUNT(eb.booking_id) >= e.max_participants THEN 'Full'
        WHEN COUNT(eb.booking_id) > e.max_participants * 0.8 THEN 'Almost Full'
        ELSE 'Available'
    END as booking_status
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
LEFT JOIN event_bookings eb ON e.event_id = eb.event_id AND eb.booking_status = 'Confirmed'
GROUP BY e.event_id, e.event_name, h.hotel_name, e.event_date, e.event_type, e.max_participants
ORDER BY e.event_date DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to calculate loyalty points and update membership level
CREATE PROCEDURE CalculateLoyaltyPoints(
    IN guest_id_param INT,
    IN booking_amount DECIMAL(10,2)
)
BEGIN
    DECLARE current_points INT DEFAULT 0;
    DECLARE new_points INT;
    DECLARE new_level VARCHAR(20);
    
    -- Get current points
    SELECT loyalty_points INTO current_points 
    FROM guests WHERE guest_id = guest_id_param;
    
    -- Calculate new points (1 point per dollar spent)
    SET new_points = current_points + FLOOR(booking_amount);
    
    -- Determine membership level based on points
    CASE 
        WHEN new_points >= 5000 THEN SET new_level = 'Platinum';
        WHEN new_points >= 2000 THEN SET new_level = 'Gold';
        WHEN new_points >= 500 THEN SET new_level = 'Silver';
        ELSE SET new_level = 'Bronze';
    END CASE;
    
    -- Update guest record
    UPDATE guests 
    SET loyalty_points = new_points, membership_level = new_level 
    WHERE guest_id = guest_id_param;
END//

-- Procedure to get available rooms for a date range
CREATE PROCEDURE GetAvailableRooms(
    IN hotel_id_param INT,
    IN check_in_param DATE,
    IN check_out_param DATE,
    IN room_type_param INT
)
BEGIN
    SELECT 
        r.room_id,
        r.room_number,
        rt.type_name,
        r.price,
        r.max_occupancy,
        r.amenities,
        h.hotel_name
    FROM rooms r
    JOIN room_types rt ON r.type_id = rt.type_id
    JOIN hotels h ON r.hotel_id = h.hotel_id
    WHERE r.hotel_id = hotel_id_param
    AND r.is_active = TRUE
    AND r.maintenance_status = 'Available'
    AND (room_type_param IS NULL OR r.type_id = room_type_param)
    AND r.room_id NOT IN (
        SELECT b.room_id 
        FROM bookings b 
        WHERE b.booking_status = 'Confirmed'
        AND NOT (check_out_param <= b.check_in OR check_in_param >= b.check_out)
    )
    ORDER BY r.price ASC;
END//

-- Procedure to generate hotel occupancy report
CREATE PROCEDURE GenerateOccupancyReport(
    IN hotel_id_param INT,
    IN start_date DATE,
    IN end_date DATE
)
BEGIN
    SELECT 
        DATE(b.check_in) as occupancy_date,
        COUNT(DISTINCT b.room_id) as occupied_rooms,
        (SELECT COUNT(*) FROM rooms WHERE hotel_id = hotel_id_param AND is_active = TRUE) as total_rooms,
        ROUND(
            (COUNT(DISTINCT b.room_id) / 
            (SELECT COUNT(*) FROM rooms WHERE hotel_id = hotel_id_param AND is_active = TRUE)) * 100, 2
        ) as occupancy_percentage,
        SUM(b.final_amount) as daily_revenue
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE r.hotel_id = hotel_id_param
    AND b.booking_status IN ('Confirmed', 'Completed')
    AND DATE(b.check_in) BETWEEN start_date AND end_date
    GROUP BY DATE(b.check_in)
    ORDER BY occupancy_date;
END//

-- Procedure to calculate room revenue statistics
CREATE PROCEDURE CalculateRoomRevenue(
    IN hotel_id_param INT,
    IN year_param INT
)
BEGIN
    SELECT 
        rt.type_name,
        COUNT(b.booking_id) as total_bookings,
        AVG(b.final_amount) as average_rate,
        MIN(b.final_amount) as min_rate,
        MAX(b.final_amount) as max_rate,
        SUM(b.final_amount) as total_revenue,
        AVG(DATEDIFF(b.check_out, b.check_in)) as average_nights
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    JOIN room_types rt ON r.type_id = rt.type_id
    WHERE r.hotel_id = hotel_id_param
    AND YEAR(b.check_in) = year_param
    AND b.booking_status IN ('Confirmed', 'Completed')
    GROUP BY rt.type_id, rt.type_name
    ORDER BY total_revenue DESC;
END//

DELIMITER ;

-- =====================================================
-- FUNCTIONS
-- =====================================================

DELIMITER //

-- Function to calculate age from date of birth
CREATE FUNCTION CalculateAge(birth_date DATE) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE age INT;
    SET age = TIMESTAMPDIFF(YEAR, birth_date, CURDATE());
    RETURN age;
END//

-- Function to calculate booking duration in nights
CREATE FUNCTION CalculateNights(check_in DATE, check_out DATE) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    RETURN DATEDIFF(check_out, check_in);
END//

-- Function to get season based on month
CREATE FUNCTION GetSeason(booking_date DATE) 
RETURNS VARCHAR(20)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE month_num INT;
    DECLARE season VARCHAR(20);
    
    SET month_num = MONTH(booking_date);
    
    CASE 
        WHEN month_num IN (12, 1, 2) THEN SET season = 'Winter';
        WHEN month_num IN (3, 4, 5) THEN SET season = 'Spring';
        WHEN month_num IN (6, 7, 8) THEN SET season = 'Summer';
        WHEN month_num IN (9, 10, 11) THEN SET season = 'Fall';
        ELSE SET season = 'Unknown';
    END CASE;
    
    RETURN season;
END//

-- Function to calculate membership discount percentage
CREATE FUNCTION GetMembershipDiscount(membership_level VARCHAR(20)) 
RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    CASE membership_level
        WHEN 'Platinum' THEN RETURN 15.00;
        WHEN 'Gold' THEN RETURN 10.00;
        WHEN 'Silver' THEN RETURN 5.00;
        WHEN 'Bronze' THEN RETURN 0.00;
        ELSE RETURN 0.00;
    END CASE;
END//

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger to update hotel total_rooms when room is added/removed
CREATE TRIGGER update_hotel_room_count 
AFTER INSERT ON rooms 
FOR EACH ROW
BEGIN
    UPDATE hotels 
    SET total_rooms = (
        SELECT COUNT(*) 
        FROM rooms 
        WHERE hotel_id = NEW.hotel_id AND is_active = TRUE
    ) 
    WHERE hotel_id = NEW.hotel_id;
END//

CREATE TRIGGER update_hotel_room_count_delete 
AFTER DELETE ON rooms 
FOR EACH ROW
BEGIN
    UPDATE hotels 
    SET total_rooms = (
        SELECT COUNT(*) 
        FROM rooms 
        WHERE hotel_id = OLD.hotel_id AND is_active = TRUE
    ) 
    WHERE hotel_id = OLD.hotel_id;
END//

-- Trigger to log booking changes
CREATE TRIGGER log_booking_changes 
AFTER UPDATE ON bookings 
FOR EACH ROW
BEGIN
    INSERT INTO system_logs (user_type, action, table_name, record_id, old_values, new_values)
    VALUES (
        'System', 
        'UPDATE', 
        'bookings', 
        NEW.booking_id,
        JSON_OBJECT(
            'booking_status', OLD.booking_status,
            'payment_status', OLD.payment_status,
            'final_amount', OLD.final_amount
        ),
        JSON_OBJECT(
            'booking_status', NEW.booking_status,
            'payment_status', NEW.payment_status,
            'final_amount', NEW.final_amount
        )
    );
END//

-- Trigger to update event participant count
CREATE TRIGGER update_event_participants_insert
AFTER INSERT ON event_bookings 
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM event_bookings 
        WHERE event_id = NEW.event_id AND booking_status = 'Confirmed'
    )
    WHERE event_id = NEW.event_id;
END//

CREATE TRIGGER update_event_participants_update
AFTER UPDATE ON event_bookings 
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM event_bookings 
        WHERE event_id = NEW.event_id AND booking_status = 'Confirmed'
    )
    WHERE event_id = NEW.event_id;
END//

CREATE TRIGGER update_event_participants_delete
AFTER DELETE ON event_bookings 
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) 
        FROM event_bookings 
        WHERE event_id = OLD.event_id AND booking_status = 'Confirmed'
    )
    WHERE event_id = OLD.event_id;
END//

-- Trigger to automatically calculate loyalty points on booking completion
CREATE TRIGGER calculate_loyalty_on_completion
AFTER UPDATE ON bookings 
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'Completed' AND OLD.booking_status != 'Completed' THEN
        CALL CalculateLoyaltyPoints(NEW.guest_id, NEW.final_amount);
    END IF;
END//

DELIMITER ;

-- =====================================================
-- SAMPLE COMPLEX QUERIES WITH AGGREGATE FUNCTIONS
-- =====================================================

-- Complex query with multiple JOINs and aggregates
-- Top performing hotels by revenue and rating
SELECT 
    h.hotel_name,
    h.city,
    h.star_rating,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_revenue,
    AVG(b.final_amount) as avg_booking_value,
    AVG(rev.rating) as avg_customer_rating,
    COUNT(DISTINCT rev.review_id) as total_reviews,
    DENSE_RANK() OVER (ORDER BY SUM(b.final_amount) DESC) as revenue_rank
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id
WHERE h.is_active = TRUE
GROUP BY h.hotel_id, h.hotel_name, h.city, h.star_rating
HAVING total_revenue > 1000 OR avg_customer_rating >= 4.0
ORDER BY total_revenue DESC, avg_customer_rating DESC
LIMIT 10;

-- Guest spending analysis with window functions
SELECT 
    g.name,
    g.membership_level,
    g.loyalty_points,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_spent,
    AVG(b.final_amount) as avg_booking_value,
    MIN(b.final_amount) as min_booking,
    MAX(b.final_amount) as max_booking,
    STDDEV(b.final_amount) as spending_variance,
    ROW_NUMBER() OVER (PARTITION BY g.membership_level ORDER BY SUM(b.final_amount) DESC) as level_rank,
    LAG(SUM(b.final_amount), 1) OVER (ORDER BY SUM(b.final_amount) DESC) as prev_customer_total
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
WHERE b.booking_status IN ('Completed', 'Confirmed')
GROUP BY g.guest_id, g.name, g.membership_level, g.loyalty_points
HAVING COUNT(b.booking_id) >= 2
ORDER BY total_spent DESC;

-- Seasonal booking analysis
SELECT 
    GetSeason(b.check_in) as season,
    YEAR(b.check_in) as booking_year,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as season_revenue,
    AVG(b.final_amount) as avg_booking_amount,
    AVG(DATEDIFF(b.check_out, b.check_in)) as avg_stay_duration,
    COUNT(DISTINCT b.guest_id) as unique_guests,
    MAX(b.final_amount) as highest_booking,
    MIN(b.final_amount) as lowest_booking
FROM bookings b
WHERE b.booking_status IN ('Confirmed', 'Completed')
AND YEAR(b.check_in) >= 2023
GROUP BY GetSeason(b.check_in), YEAR(b.check_in)
ORDER BY booking_year DESC, 
    CASE GetSeason(b.check_in) 
        WHEN 'Spring' THEN 1
        WHEN 'Summer' THEN 2
        WHEN 'Fall' THEN 3
        WHEN 'Winter' THEN 4
    END;