-- ============================================
-- SMART STAY - Hotel Management System
-- Simple Database Project (No Login Required)
-- ============================================

DROP DATABASE IF EXISTS smart_stay;
CREATE DATABASE smart_stay;
USE smart_stay;

-- ============================================
-- 1. HOTELS TABLE
-- ============================================
CREATE TABLE hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    rating DECIMAL(2,1) DEFAULT 0.0,
    total_rooms INT DEFAULT 0,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample hotels
INSERT INTO hotels (hotel_name, address, city, phone, email, rating, total_rooms) VALUES
('Grand Plaza Hotel', '123 Main Street', 'New York', '555-0101', 'info@grandplaza.com', 4.5, 50),
('Ocean View Resort', '456 Beach Road', 'Miami', '555-0102', 'contact@oceanview.com', 4.8, 75),
('Mountain Lodge', '789 Hill Avenue', 'Denver', '555-0103', 'booking@mountainlodge.com', 4.2, 30);

-- ============================================
-- 2. ROOM TYPES TABLE
-- ============================================
CREATE TABLE room_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Standard room types
INSERT INTO room_types (type_name, description) VALUES
('Single', 'Single bed room for one person'),
('Double', 'Double bed room for two people'),
('Suite', 'Luxury suite with multiple rooms'),
('Deluxe', 'Premium room with extra amenities'),
('Family', 'Large room suitable for families');

-- ============================================
-- 3. ROOMS TABLE
-- ============================================
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    type_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_occupancy INT DEFAULT 2,
    status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES room_types(type_id),
    UNIQUE KEY unique_room (hotel_id, room_number)
);

-- Sample rooms
INSERT INTO rooms (hotel_id, room_number, type_id, price, max_occupancy, status) VALUES
(1, '101', 1, 100.00, 1, 'Available'),
(1, '102', 2, 150.00, 2, 'Available'),
(1, '201', 3, 300.00, 4, 'Available'),
(2, '101', 2, 180.00, 2, 'Occupied'),
(2, '102', 3, 350.00, 4, 'Available'),
(3, '101', 1, 90.00, 1, 'Available');

-- ============================================
-- 4. CUSTOMERS TABLE
-- ============================================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    id_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample customers
INSERT INTO customers (full_name, email, phone, id_number) VALUES
('John Smith', 'john.smith@email.com', '555-1001', 'ID-12345'),
('Sarah Johnson', 'sarah.j@email.com', '555-1002', 'ID-12346'),
('Michael Brown', 'michael.b@email.com', '555-1003', 'ID-12347');

-- ============================================
-- 5. BOOKINGS TABLE
-- ============================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    room_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    booking_status ENUM('Confirmed', 'Checked-In', 'Checked-Out', 'Cancelled') DEFAULT 'Confirmed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE
);

-- Sample bookings
INSERT INTO bookings (customer_id, room_id, hotel_id, check_in, check_out, total_amount, payment_status, booking_status) VALUES
(1, 1, 1, '2025-11-01', '2025-11-05', 400.00, 'Paid', 'Confirmed'),
(2, 4, 2, '2025-10-28', '2025-10-30', 360.00, 'Paid', 'Checked-In'),
(3, 6, 3, '2025-11-10', '2025-11-12', 180.00, 'Pending', 'Confirmed');

-- ============================================
-- 6. SERVICES TABLE
-- ============================================
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    service_type ENUM('Spa', 'Restaurant', 'Room Service', 'Transport', 'Laundry', 'Other') DEFAULT 'Other',
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE
);

-- Sample services
INSERT INTO services (hotel_id, service_name, service_type, price) VALUES
(1, 'Spa Treatment', 'Spa', 120.00),
(1, 'Airport Shuttle', 'Transport', 50.00),
(2, 'Beach Restaurant', 'Restaurant', 0.00),
(2, 'Laundry Service', 'Laundry', 25.00),
(3, 'Room Service', 'Room Service', 35.00);

-- ============================================
-- 7. EVENTS TABLE
-- ============================================
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    event_type ENUM('Conference', 'Wedding', 'Party', 'Meeting', 'Other') DEFAULT 'Other',
    max_participants INT DEFAULT 50,
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Scheduled', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE
);

-- Sample events
INSERT INTO events (hotel_id, event_name, event_date, event_type, max_participants, price, description) VALUES
(1, 'Business Conference 2025', '2025-12-15', 'Conference', 100, 500.00, 'Annual business conference'),
(2, 'Beach Wedding', '2025-11-20', 'Wedding', 150, 2000.00, 'Beachside wedding ceremony'),
(3, 'Mountain Retreat', '2025-11-25', 'Meeting', 30, 300.00, 'Corporate team building');

-- ============================================
-- 8. STAFF TABLE
-- ============================================
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    salary DECIMAL(10,2),
    hire_date DATE NOT NULL,
    status ENUM('Active', 'On Leave', 'Terminated') DEFAULT 'Active',
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE
);

-- Sample staff
INSERT INTO staff (hotel_id, full_name, position, phone, salary, hire_date) VALUES
(1, 'Alice Manager', 'Hotel Manager', '555-2001', 5000.00, '2024-01-15'),
(1, 'Bob Receptionist', 'Front Desk', '555-2002', 2500.00, '2024-03-20'),
(2, 'Carol Chef', 'Head Chef', '555-2003', 4000.00, '2024-02-10'),
(3, 'David Cleaner', 'Housekeeping', '555-2004', 2000.00, '2024-04-01');

-- ============================================
-- USEFUL VIEWS (Using JOIN, GROUP BY, Aggregate Functions)
-- ============================================

-- View: Hotel Overview with Room Statistics
-- Uses: LEFT JOIN, COUNT, SUM, CASE, GROUP BY
CREATE VIEW hotel_overview AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    h.city,
    h.rating,
    h.status,
    COUNT(DISTINCT r.room_id) as total_rooms,
    SUM(CASE WHEN r.status = 'Available' THEN 1 ELSE 0 END) as available_rooms,
    SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) as occupied_rooms,
    COUNT(DISTINCT s.service_id) as total_services,
    COUNT(DISTINCT e.event_id) as total_events
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN services s ON h.hotel_id = s.hotel_id
LEFT JOIN events e ON h.hotel_id = e.hotel_id
GROUP BY h.hotel_id, h.hotel_name, h.city, h.rating, h.status;

-- View: Active Bookings with Customer and Room Details
-- Uses: INNER JOIN (multiple tables)
CREATE VIEW active_bookings AS
SELECT 
    b.booking_id,
    b.check_in,
    b.check_out,
    b.total_amount,
    b.payment_status,
    b.booking_status,
    c.full_name as customer_name,
    c.phone as customer_phone,
    h.hotel_name,
    r.room_number,
    rt.type_name as room_type
FROM bookings b
INNER JOIN customers c ON b.customer_id = c.customer_id
INNER JOIN rooms r ON b.room_id = r.room_id
INNER JOIN hotels h ON b.hotel_id = h.hotel_id
INNER JOIN room_types rt ON r.type_id = rt.type_id
WHERE b.booking_status != 'Cancelled'
ORDER BY b.check_in DESC;

-- View: Revenue Summary by Hotel
-- Uses: LEFT JOIN, COUNT, SUM, AVG, CASE, GROUP BY
CREATE VIEW revenue_summary AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    COUNT(b.booking_id) as total_bookings,
    SUM(CASE WHEN b.payment_status = 'Paid' THEN b.total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN b.payment_status = 'Pending' THEN b.total_amount ELSE 0 END) as pending_revenue,
    AVG(b.total_amount) as avg_booking_amount
FROM hotels h
LEFT JOIN bookings b ON h.hotel_id = b.hotel_id
GROUP BY h.hotel_id, h.hotel_name;

-- View: Room Occupancy Rate by Hotel
-- Uses: LEFT JOIN, COUNT, GROUP BY, Aggregate Functions
CREATE VIEW room_occupancy_rate AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    COUNT(r.room_id) as total_rooms,
    COUNT(CASE WHEN r.status = 'Occupied' THEN 1 END) as occupied_rooms,
    COUNT(CASE WHEN r.status = 'Available' THEN 1 END) as available_rooms,
    ROUND((COUNT(CASE WHEN r.status = 'Occupied' THEN 1 END) / COUNT(r.room_id)) * 100, 2) as occupancy_percentage
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
GROUP BY h.hotel_id, h.hotel_name
HAVING COUNT(r.room_id) > 0;

-- View: Customer Booking Statistics
-- Uses: INNER JOIN, COUNT, SUM, MAX, MIN, GROUP BY, HAVING
CREATE VIEW customer_stats AS
SELECT 
    c.customer_id,
    c.full_name,
    c.phone,
    c.email,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.total_amount) as total_spent,
    AVG(b.total_amount) as avg_booking_amount,
    MAX(b.check_in) as last_booking_date,
    MIN(b.check_in) as first_booking_date
FROM customers c
INNER JOIN bookings b ON c.customer_id = b.customer_id
WHERE b.booking_status != 'Cancelled'
GROUP BY c.customer_id, c.full_name, c.phone, c.email
HAVING COUNT(b.booking_id) > 0;

-- View: Monthly Revenue Report
-- Uses: INNER JOIN, DATE functions, GROUP BY, SUM, COUNT
CREATE VIEW monthly_revenue AS
SELECT 
    h.hotel_name,
    YEAR(b.check_in) as booking_year,
    MONTH(b.check_in) as booking_month,
    COUNT(b.booking_id) as total_bookings,
    SUM(CASE WHEN b.payment_status = 'Paid' THEN b.total_amount ELSE 0 END) as paid_revenue,
    SUM(CASE WHEN b.payment_status = 'Pending' THEN b.total_amount ELSE 0 END) as pending_revenue,
    SUM(b.total_amount) as total_revenue
FROM hotels h
INNER JOIN bookings b ON h.hotel_id = b.hotel_id
WHERE b.booking_status != 'Cancelled'
GROUP BY h.hotel_name, YEAR(b.check_in), MONTH(b.check_in)
ORDER BY booking_year DESC, booking_month DESC;

-- View: Room Type Performance
-- Uses: INNER JOIN, COUNT, SUM, AVG, GROUP BY
CREATE VIEW room_type_performance AS
SELECT 
    rt.type_name,
    COUNT(r.room_id) as total_rooms,
    COUNT(b.booking_id) as total_bookings,
    AVG(r.price) as avg_price,
    SUM(b.total_amount) as total_revenue
FROM room_types rt
INNER JOIN rooms r ON rt.type_id = r.type_id
LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status != 'Cancelled'
GROUP BY rt.type_name
ORDER BY total_revenue DESC;

-- View: Hotels with High Revenue (HAVING clause example)
-- Uses: INNER JOIN, SUM, GROUP BY, HAVING
CREATE VIEW high_revenue_hotels AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    h.city,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.total_amount) as total_revenue
FROM hotels h
INNER JOIN bookings b ON h.hotel_id = b.hotel_id
WHERE b.payment_status = 'Paid'
GROUP BY h.hotel_id, h.hotel_name, h.city
HAVING SUM(b.total_amount) > 500
ORDER BY total_revenue DESC;

-- ============================================
-- SAMPLE COMPLEX QUERIES (For Demonstration)
-- ============================================

-- Query 1: Hotels with most bookings (JOIN + GROUP BY + ORDER BY)
-- SELECT h.hotel_name, COUNT(b.booking_id) as booking_count
-- FROM hotels h
-- INNER JOIN bookings b ON h.hotel_id = b.hotel_id
-- GROUP BY h.hotel_name
-- ORDER BY booking_count DESC;

-- Query 2: Customers who spent more than $500 (JOIN + GROUP BY + HAVING)
-- SELECT c.full_name, SUM(b.total_amount) as total_spent
-- FROM customers c
-- INNER JOIN bookings b ON c.customer_id = b.customer_id
-- WHERE b.payment_status = 'Paid'
-- GROUP BY c.full_name
-- HAVING SUM(b.total_amount) > 500;

-- Query 3: Room availability by hotel and type (Multiple JOINs + GROUP BY)
-- SELECT h.hotel_name, rt.type_name, COUNT(r.room_id) as available_rooms
-- FROM hotels h
-- INNER JOIN rooms r ON h.hotel_id = r.hotel_id
-- INNER JOIN room_types rt ON r.type_id = rt.type_id
-- WHERE r.status = 'Available'
-- GROUP BY h.hotel_name, rt.type_name;

-- Query 4: Average booking amount by city (JOIN + GROUP BY + AVG)
-- SELECT h.city, AVG(b.total_amount) as avg_booking_amount
-- FROM hotels h
-- INNER JOIN bookings b ON h.hotel_id = b.hotel_id
-- GROUP BY h.city;

-- Query 5: Top 5 revenue generating services (JOIN + SUM + GROUP BY + LIMIT)
-- SELECT h.hotel_name, s.service_name, s.price, COUNT(*) as usage_count
-- FROM hotels h
-- INNER JOIN services s ON h.hotel_id = s.hotel_id
-- GROUP BY h.hotel_name, s.service_name, s.price
-- ORDER BY s.price DESC
-- LIMIT 5;

-- ============================================
-- INDEXES for Better Performance
-- ============================================
CREATE INDEX idx_hotel_status ON hotels(status);
CREATE INDEX idx_room_status ON rooms(status);
CREATE INDEX idx_booking_dates ON bookings(check_in, check_out);
CREATE INDEX idx_booking_status ON bookings(booking_status, payment_status);
CREATE INDEX idx_customer_phone ON customers(phone);
CREATE INDEX idx_event_date ON events(event_date);

-- ============================================
-- SUMMARY
-- ============================================
-- Total Tables: 8
-- 1. hotels - Hotel properties
-- 2. room_types - Room categories
-- 3. rooms - Individual rooms
-- 4. customers - Booking customers
-- 5. bookings - Room reservations
-- 6. services - Hotel services
-- 7. events - Hotel events
-- 8. staff - Hotel employees
--
-- Views: 8 (using JOIN, GROUP BY, HAVING, Aggregate Functions)
-- 1. hotel_overview - Hotel statistics with room count
-- 2. active_bookings - Current bookings with customer details
-- 3. revenue_summary - Revenue by hotel
-- 4. room_occupancy_rate - Occupancy percentage by hotel
-- 5. customer_stats - Customer booking statistics
-- 6. monthly_revenue - Monthly revenue breakdown
-- 7. room_type_performance - Revenue by room type
-- 8. high_revenue_hotels - Hotels earning > $500 (HAVING example)
--
-- SQL FEATURES DEMONSTRATED:
-- ✅ DDL: CREATE TABLE, CREATE VIEW, CREATE INDEX
-- ✅ DML: INSERT, UPDATE, DELETE
-- ✅ Joins: INNER JOIN, LEFT JOIN (Multiple tables)
-- ✅ Aggregate Functions: COUNT, SUM, AVG, MAX, MIN
-- ✅ GROUP BY: Grouping data
-- ✅ HAVING: Filter grouped data
-- ✅ CASE: Conditional logic
-- ✅ WHERE: Filter rows
-- ✅ ORDER BY: Sort results
-- ✅ DISTINCT: Unique values
-- ✅ Date Functions: YEAR(), MONTH()
--
-- NO LOGIN REQUIRED - Direct access to dashboard
-- Pure SQL features only - No PL/SQL procedures
-- ============================================


