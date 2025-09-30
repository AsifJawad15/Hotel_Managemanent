-- COMPREHENSIVE SAMPLE DATA INSERTS
-- Run this file after enhanced_smart_stay.sql to populate the database with extensive test data
-- This will provide enough data to test all advanced SQL features

-- ⚠️  IMPORTANT: READ BEFORE RUNNING ⚠️  
-- 
-- IF YOU GET DUPLICATE ERRORS:
-- 1. First run: cleanup_sample_data.sql 
-- 2. Then run: this file (sample_data_inserts.sql)
--
-- OR alternatively, drop and recreate the entire database:
-- 1. DROP DATABASE smart_stay;
-- 2. CREATE DATABASE smart_stay;  
-- 3. Run: enhanced_smart_stay.sql
-- 4. Run: sample_data_inserts.sql

USE smart_stay;

-- =====================================================
-- ADDITIONAL HOTELS (hotel_id will start from 4)
-- =====================================================

-- Note: The main schema already includes 3 hotels:
-- hotel_id 1: Grand Plaza Hotel (plaza@hotels.com)  
-- hotel_id 2: Ocean View Resort (ocean@hotels.com)
-- hotel_id 3: Mountain Lodge (mountain@hotels.com)

-- Idempotent inserts so this file can be executed multiple times safely
INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Skyline Business Hotel', 'skyline@businesshotels.com', 'skyline123', 'Modern business hotel with state-of-the-art conference facilities', '321 Business District', 'Chicago', 'IL', 'USA', '60601', '+1-555-0104', 4.3, 120, '["WiFi", "Business Center", "Conference Rooms", "Gym", "Restaurant", "Airport Shuttle"]', '14:00:00', '12:00:00', 2010, 'CHI-2010-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'skyline@businesshotels.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Riverside Resort & Spa', 'riverside@sparetreat.com', 'river123', 'Luxury spa resort along the beautiful riverside with premium wellness facilities', '789 River Valley Road', 'Portland', 'OR', 'USA', '97201', '+1-555-0105', 4.7, 180, '["WiFi", "Spa", "Pool", "Restaurant", "Bar", "Wellness Center", "Yoga Studio"]', '16:00:00', '11:00:00', 2015, 'POR-2015-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'riverside@sparetreat.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Historic Downtown Inn', 'downtown@heritage.com', 'historic123', 'Charming boutique hotel in the heart of historic downtown', '456 Heritage Street', 'Boston', 'MA', 'USA', '02101', '+1-555-0106', 4.1, 60, '["WiFi", "Historic Ambiance", "Restaurant", "Bar", "Concierge"]', '15:00:00', '11:00:00', 1995, 'BOS-1995-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'downtown@heritage.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Desert Oasis Resort', 'desert@luxuryresorts.com', 'desert123', 'Luxury desert resort with stunning landscape views and outdoor activities', '999 Desert View Drive', 'Phoenix', 'AZ', 'USA', '85001', '+1-555-0107', 4.6, 250, '["WiFi", "Pool", "Spa", "Golf Course", "Restaurant", "Bar", "Desert Tours"]', '16:00:00', '12:00:00', 2018, 'PHX-2018-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'desert@luxuryresorts.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'City Center Express', 'express@cityhotels.com', 'express123', 'Budget-friendly hotel with essential amenities in prime location', '123 Central Avenue', 'Seattle', 'WA', 'USA', '98101', '+1-555-0108', 3.8, 90, '["WiFi", "24/7 Front Desk", "Continental Breakfast", "Business Center"]', '15:00:00', '11:00:00', 2020, 'SEA-2020-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'express@cityhotels.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Harborfront Luxury Suites', 'harbor@luxurysuites.com', 'harbor123', 'Upscale waterfront hotel with stunning harbor views and premium amenities', '555 Harbor Boulevard', 'San Francisco', 'CA', 'USA', '94111', '+1-555-0109', 4.5, 150, '["WiFi", "Spa", "Pool", "Restaurant", "Bar", "Harbor View", "Valet Parking"]', '15:00:00', '12:00:00', 2016, 'SF-2016-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'harbor@luxurysuites.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Forest Retreat Villas', 'forest@natureretreat.com', 'forest123', 'Eco-friendly mountain retreat with sustainable accommodations and nature activities', '888 Forest Trail', 'Asheville', 'NC', 'USA', '28801', '+1-555-0110', 4.2, 75, '["WiFi", "Fireplace", "Hiking Trails", "Eco Tours", "Organic Restaurant", "Wellness Center"]', '16:00:00', '11:00:00', 2019, 'ASH-2019-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'forest@natureretreat.com');

INSERT INTO hotels (hotel_name, email, password, description, address, city, state, country, postal_code, phone, star_rating, total_rooms, amenities, check_in_time, check_out_time, established_year, license_number)
SELECT 'Metropolitan Art Hotel', 'metro@arthotel.com', 'metro123', 'Boutique art hotel featuring contemporary design and cultural experiences', '777 Arts District', 'Austin', 'TX', 'USA', '78701', '+1-555-0111', 4.4, 100, '["WiFi", "Art Gallery", "Restaurant", "Bar", "Cultural Events", "Artist Studios"]', '15:00:00', '12:00:00', 2021, 'AUS-2021-001'
WHERE NOT EXISTS (SELECT 1 FROM hotels WHERE email = 'metro@arthotel.com');

-- =====================================================
-- ADDITIONAL GUESTS WITH DIVERSE PROFILES (guest_id will start from 5)
-- =====================================================

-- Note: The main schema already includes 4 guests:
-- guest_id 1: John Smith (john@email.com) - Gold member
-- guest_id 2: Emma Johnson (emma@email.com) - Silver member  
-- guest_id 3: Michael Brown (michael@email.com) - Platinum member
-- guest_id 4: Sarah Davis (sarah@email.com) - Bronze member

INSERT IGNORE INTO guests (name, email, password, phone, date_of_birth, gender, nationality, address, loyalty_points, membership_level) VALUES
('David Wilson', 'david.wilson@email.com', 'david123', '+1-555-2001', '1987-07-18', 'Male', 'American', '123 Oak Street, Los Angeles, CA', 3200, 'Platinum'),
('Lisa Anderson', 'lisa.anderson@email.com', 'lisa123', '+1-555-2002', '1993-11-30', 'Female', 'Canadian', '456 Maple Ave, Toronto, ON', 650, 'Silver'),
('Robert Garcia', 'robert.garcia@email.com', 'robert123', '+1-555-2003', '1975-02-14', 'Male', 'Mexican', '789 Pine Road, Mexico City', 150, 'Bronze'),
('Jennifer Lee', 'jennifer.lee@email.com', 'jen123', '+1-555-2004', '1990-09-08', 'Female', 'American', '321 Elm Street, San Francisco, CA', 1800, 'Gold'),
('Carlos Rodriguez', 'carlos.rodriguez@email.com', 'carlos123', '+1-555-2005', '1982-04-22', 'Male', 'Spanish', '654 Cedar Lane, Madrid, Spain', 2800, 'Platinum'),
('Amanda Thompson', 'amanda.thompson@email.com', 'amanda123', '+1-555-2006', '1991-12-03', 'Female', 'Australian', '987 Birch Boulevard, Sydney, NSW', 920, 'Silver'),
('James Martinez', 'james.martinez@email.com', 'james123', '+1-555-2007', '1986-06-16', 'Male', 'American', '147 Willow Way, Houston, TX', 380, 'Bronze'),
('Maria Gonzalez', 'maria.gonzalez@email.com', 'maria123', '+1-555-2008', '1994-01-25', 'Female', 'Argentinian', '258 Spruce Street, Buenos Aires', 1250, 'Gold'),
('Kevin O\'Connor', 'kevin.oconnor@email.com', 'kevin123', '+1-555-2009', '1989-10-12', 'Male', 'Irish', '369 Ash Avenue, Dublin, Ireland', 750, 'Silver'),
('Sophie Martin', 'sophie.martin@email.com', 'sophie123', '+1-555-2010', '1985-08-07', 'Female', 'French', '741 Poplar Place, Paris, France', 2100, 'Gold'),
('Zhang Wei', 'zhang.wei@email.com', 'zhang123', '+1-555-2011', '1992-03-19', 'Male', 'Chinese', '852 Bamboo Road, Beijing, China', 450, 'Bronze'),
('Isabella Rossi', 'isabella.rossi@email.com', 'bella123', '+1-555-2012', '1988-07-28', 'Female', 'Italian', '963 Olive Street, Rome, Italy', 1650, 'Gold');

-- =====================================================
-- ADDITIONAL ROOMS FOR ALL HOTELS
-- =====================================================

-- Grand Plaza Hotel (hotel_id: 1) - Additional rooms
-- Note: Base schema has rooms 101, 102, 201, 301, 401 - using different room numbers to avoid conflicts
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(1, '106', 1, 1, 88.00, 260, 1, '["TV", "WiFi", "Air Conditioning", "Work Desk"]'),
(1, '107', 2, 1, 128.00, 310, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Coffee Maker"]'),
(1, '108', 2, 1, 130.00, 320, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Coffee Maker"]'),
(1, '204', 3, 2, 188.00, 460, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View", "Balcony"]'),
(1, '205', 3, 2, 190.00, 470, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View", "Balcony"]'),
(1, '303', 4, 3, 315.00, 820, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen", "Living Room"]'),
(1, '403', 5, 4, 530.00, 1250, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Balcony", "Kitchen", "Jacuzzi", "Butler Service"]');

-- Ocean View Resort (hotel_id: 2) - Additional rooms
-- Note: Base schema has rooms A101, A201, B301
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(2, 'A102', 2, 1, 145.00, 360, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Ocean View", "Balcony"]'),
(2, 'A103', 2, 1, 142.00, 355, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Ocean View"]'),
(2, 'A202', 3, 2, 205.00, 510, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Balcony", "Jacuzzi"]'),
(2, 'A203', 3, 2, 198.00, 495, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Balcony"]'),
(2, 'B302', 4, 3, 360.00, 920, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Kitchen", "Living Room"]'),
(2, 'S401', 5, 4, 580.00, 1400, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Kitchen", "Jacuzzi", "Private Beach Access"]');

-- Mountain Lodge (hotel_id: 3) - Additional rooms
-- Note: Base schema has NO rooms for Mountain Lodge - all rooms in this section are new
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(3, '101', 1, 1, 75.00, 240, 1, '["TV", "WiFi", "Air Conditioning", "Mountain View"]'),
(3, '102', 2, 1, 115.00, 290, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Mountain View"]'),
(3, '201', 2, 2, 118.00, 295, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Mountain View", "Fireplace"]'),
(3, '202', 3, 2, 175.00, 440, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Mountain View", "Fireplace"]'),
(3, '301', 4, 3, 280.00, 750, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Mountain View", "Kitchen", "Fireplace"]');

-- Skyline Business Hotel (hotel_id: 4) - Rooms
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(4, '501', 1, 5, 95.00, 280, 1, '["TV", "WiFi", "Air Conditioning", "Work Desk", "Business Phone"]'),
(4, '502', 2, 5, 135.00, 330, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Work Desk", "Business Phone"]'),
(4, '601', 3, 6, 195.00, 480, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View", "Executive Lounge Access"]'),
(4, '701', 4, 7, 320.00, 850, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Kitchen", "Meeting Area"]'),
(4, '801', 5, 8, 550.00, 1300, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Kitchen", "Conference Room", "Butler Service"]');

-- Riverside Resort & Spa (hotel_id: 5) - Rooms
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(5, 'R101', 2, 1, 150.00, 370, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "River View", "Spa Access"]'),
(5, 'R201', 3, 2, 210.00, 520, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "River View", "Balcony", "Spa Access"]'),
(5, 'R301', 4, 3, 370.00, 950, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "River View", "Kitchen", "Spa Access"]'),
(5, 'RS401', 5, 4, 600.00, 1450, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "River View", "Kitchen", "Private Spa", "Butler Service"]');

-- City Center Express (hotel_id: 6) - Rooms
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(6, 'CC101', 1, 1, 75.00, 240, 1, '["TV", "WiFi", "Air Conditioning", "Work Desk"]'),
(6, 'CC201', 2, 2, 110.00, 300, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "City View"]'),
(6, 'CC301', 3, 3, 165.00, 420, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View"]'),
(6, 'CC401', 4, 4, 280.00, 720, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Kitchen", "Living Area"]');

-- Harborfront Luxury Suites (hotel_id: 7) - Rooms
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(7, 'H201', 2, 2, 165.00, 340, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Harbor View"]'),
(7, 'H305', 3, 3, 235.00, 520, 3, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Harbor View", "Balcony"]'),
(7, 'H407', 4, 4, 365.00, 880, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Kitchen", "Living Room", "Private Terrace"]'),
(7, 'HPH1', 5, 5, 620.00, 1420, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Kitchen", "Jacuzzi", "Butler Service"]');

-- Forest Retreat Villas (hotel_id: 8) - Rooms  
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(8, 'FV101', 1, 1, 95.00, 280, 2, '["TV", "WiFi", "Fireplace", "Forest View"]'),
(8, 'FV202', 2, 2, 155.00, 360, 3, '["TV", "WiFi", "Mini Fridge", "Fireplace", "Forest View"]'),
(8, 'FV303', 3, 3, 215.00, 480, 4, '["TV", "WiFi", "Mini Fridge", "Safe", "Balcony", "Forest View"]'),
(8, 'FV Chalet', 4, 1, 335.00, 960, 6, '["TV", "WiFi", "Kitchenette", "Fireplace", "Outdoor Hot Tub", "Private Deck"]');

-- Metropolitan Art Hotel (hotel_id: 9) - Rooms
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(9, 'MA208', 2, 2, 178.00, 350, 2, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Art Gallery Access"]'),
(9, 'MA312', 3, 3, 248.00, 540, 3, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View", "Curated Art Pieces"]'),
(9, 'MA418', 4, 4, 375.00, 900, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Kitchen", "Living Room", "Private Exhibition Space"]'),
(9, 'MA Penthouse', 5, 6, 690.00, 1580, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Chef\'s Kitchen", "Private Cinema", "Rooftop Terrace"]');

-- Signature suites and family rooms across hotels
INSERT IGNORE INTO rooms (hotel_id, room_number, type_id, floor_number, price, area_sqft, max_occupancy, amenities) VALUES
(1, 'PH501', 5, 5, 645.00, 1500, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "City View", "Kitchen", "Jacuzzi", "Butler Service"]'),
(1, 'F210', 3, 2, 205.00, 520, 3, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Sofa Bed", "City View"]'),
(2, 'V502', 4, 5, 395.00, 980, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Ocean View", "Kitchen", "Private Terrace"]'),
(3, 'Cottage A', 4, 1, 285.00, 780, 4, '["TV", "WiFi", "Fireplace", "Kitchenette", "Mountain View", "Private Deck"]'),
(4, 'Enterp-1201', 4, 12, 340.00, 870, 4, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Conference Table", "Executive Lounge"]'),
(4, 'Board-1502', 5, 15, 575.00, 1320, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Panoramic View", "Private Meeting Space"]'),
(5, 'Villa-02', 5, 1, 680.00, 1600, 6, '["TV", "WiFi", "Air Conditioning", "Mini Fridge", "Safe", "Private Garden", "Spa Access", "Outdoor Jacuzzi"]');

-- =====================================================
-- EXTENSIVE BOOKINGS DATA
-- =====================================================

-- Historical bookings (completed)
INSERT INTO bookings (guest_id, room_id, check_in, check_out, adults, children, total_amount, discount_amount, tax_amount, final_amount, booking_status, payment_status, booking_source, special_requests) VALUES
-- Guest 1 (John Smith) - Multiple stays
(1, 1, '2024-01-15', '2024-01-18', 1, 0, 255.00, 25.50, 18.87, 248.37, 'Completed', 'Paid', 'Website', 'Late check-in requested'),
(1, 7, '2024-03-20', '2024-03-25', 2, 1, 940.00, 94.00, 69.42, 915.42, 'Completed', 'Paid', 'Website', 'Extra towels needed'),
(1, 13, '2024-06-10', '2024-06-15', 1, 0, 525.00, 52.50, 34.91, 507.41, 'Completed', 'Paid', 'Phone', 'Quiet room please'),

-- Guest 2 (Emma Johnson) - Silver member stays
(2, 3, '2024-02-08', '2024-02-12', 2, 0, 740.00, 37.00, 54.67, 757.67, 'Completed', 'Paid', 'Website', 'High floor preferred'),
(2, 18, '2024-04-15', '2024-04-18', 2, 0, 630.00, 31.50, 46.49, 644.99, 'Completed', 'Paid', 'Website', NULL),

-- Guest 3 (Michael Brown) - Platinum member stays
(3, 5, '2024-01-20', '2024-01-25', 2, 2, 2600.00, 390.00, 163.40, 2373.40, 'Completed', 'Paid', 'Phone', 'Connecting rooms if possible'),
(3, 22, '2024-05-01', '2024-05-06', 2, 1, 1850.00, 277.50, 116.18, 1688.68, 'Completed', 'Paid', 'Website', 'Airport transfer needed'),

-- Guest 4 (Sarah Davis) - Bronze member
(4, 2, '2024-03-01', '2024-03-04', 2, 0, 375.00, 0.00, 27.69, 402.69, 'Completed', 'Paid', 'Walk-in', 'Early check-in'),

-- New guests with various booking patterns
(5, 8, '2024-02-14', '2024-02-17', 2, 0, 420.00, 0.00, 31.02, 451.02, 'Completed', 'Paid', 'Website', 'Valentine\'s package'),
(6, 14, '2024-04-20', '2024-04-23', 1, 0, 285.00, 14.25, 20.11, 290.86, 'Completed', 'Paid', 'Third-party', NULL),
(7, 4, '2024-01-10', '2024-01-13', 2, 1, 564.00, 0.00, 41.68, 605.68, 'Completed', 'Paid', 'Website', 'Crib needed'),
(8, 19, '2024-03-15', '2024-03-20', 2, 0, 1050.00, 105.00, 69.83, 1014.83, 'Completed', 'Paid', 'Phone', 'Spa appointments'),
(9, 10, '2024-02-25', '2024-02-28', 1, 0, 225.00, 11.25, 15.83, 229.58, 'Completed', 'Paid', 'Website', NULL),
(10, 20, '2024-04-01', '2024-04-05', 2, 1, 1480.00, 148.00, 98.37, 1430.37, 'Completed', 'Paid', 'Website', 'River view room');

-- Future bookings (confirmed)
INSERT INTO bookings (guest_id, room_id, check_in, check_out, adults, children, total_amount, discount_amount, tax_amount, final_amount, booking_status, payment_status, booking_source, special_requests) VALUES
(1, 6, '2024-11-15', '2024-11-20', 2, 0, 1575.00, 157.50, 104.71, 1522.21, 'Confirmed', 'Paid', 'Website', 'Anniversary celebration'),
(2, 9, '2024-10-20', '2024-10-23', 2, 1, 636.00, 31.80, 44.70, 648.90, 'Confirmed', 'Pending', 'Phone', 'Baby cot required'),
(3, 21, '2024-12-01', '2024-12-07', 2, 2, 2220.00, 333.00, 139.37, 2026.37, 'Confirmed', 'Partial', 'Website', 'Holiday package'),
(11, 11, '2024-10-10', '2024-10-14', 1, 0, 380.00, 0.00, 28.08, 408.08, 'Confirmed', 'Pending', 'Website', NULL),
(12, 23, '2024-11-25', '2024-11-30', 2, 0, 1050.00, 105.00, 69.83, 1014.83, 'Confirmed', 'Paid', 'Third-party', 'Thanksgiving stay');

-- Some cancelled bookings for analysis
INSERT INTO bookings (guest_id, room_id, check_in, check_out, adults, children, total_amount, discount_amount, tax_amount, final_amount, booking_status, payment_status, booking_source, special_requests) VALUES
(5, 12, '2024-09-15', '2024-09-18', 2, 0, 570.00, 0.00, 42.11, 612.11, 'Cancelled', 'Refunded', 'Website', 'Trip cancelled due to emergency'),
(7, 15, '2024-08-20', '2024-08-25', 1, 1, 375.00, 0.00, 27.69, 402.69, 'Cancelled', 'Refunded', 'Phone', 'Flight cancelled');

-- =====================================================
-- EVENTS DATA
-- =====================================================

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements) VALUES
-- Grand Plaza Hotel events
(1, 'Tech Innovation Summit', 'Annual technology and innovation conference', '2024-10-15', '08:00:00', '18:00:00', 'Grand Conference Hall', 300, 0, 200.00, 'Conference', 'Upcoming', 'Tech Association', 'tech@association.com', 'AV equipment, WiFi, catering'),
(1, 'Smith Wedding', 'Beautiful wedding ceremony and reception', '2024-11-10', '16:00:00', '23:00:00', 'Grand Ballroom', 200, 0, 300.00, 'Wedding', 'Upcoming', 'Jennifer Smith', 'jennifer.smith@email.com', 'Floral arrangements, music system, special lighting'),

-- Ocean View Resort events
(2, 'Sunset Yoga Festival', 'Weekend yoga and wellness retreat', '2024-10-05', '06:00:00', '20:00:00', 'Beach Pavilion', 80, 0, 120.00, 'Workshop', 'Upcoming', 'Wellness Studio', 'info@wellnessstudio.com', 'Yoga mats, sound system, refreshments'),
(2, 'Corporate Team Building', 'Executive team building activities', '2024-12-08', '09:00:00', '17:00:00', 'Conference Center', 50, 0, 180.00, 'Meeting', 'Upcoming', 'ABC Corporation', 'hr@abccorp.com', 'Team building equipment, catering'),

-- Skyline Business Hotel events
(4, 'Quarterly Business Review', 'Q4 business performance review meeting', '2024-11-20', '09:00:00', '16:00:00', 'Executive Board Room', 25, 0, 100.00, 'Meeting', 'Upcoming', 'Business Solutions Inc', 'meetings@bizolutions.com', 'Presentation equipment, refreshments'),
(4, 'Holiday Gala', 'Annual company holiday celebration', '2024-12-15', '18:00:00', '23:00:00', 'Grand Ballroom', 150, 0, 250.00, 'Party', 'Upcoming', 'Skyline Events', 'events@skyline.com', 'Entertainment, catering, decorations'),

-- Riverside Resort events
(5, 'Spa & Wellness Expo', 'Health and wellness exhibition', '2024-10-25', '10:00:00', '18:00:00', 'Exhibition Hall', 120, 0, 80.00, 'Workshop', 'Upcoming', 'Health Expo Inc', 'contact@healthexpo.com', 'Exhibition booths, wellness activities'),

-- Completed events
(1, 'Summer Music Festival', 'Outdoor music and entertainment festival', '2024-08-15', '15:00:00', '23:00:00', 'Outdoor Amphitheater', 500, 450, 75.00, 'Party', 'Completed', 'Music Events LLC', 'music@events.com', 'Stage setup, sound system, security'),
(2, 'Marine Biology Conference', 'Research and conservation conference', '2024-09-10', '08:00:00', '17:00:00', 'Conference Center', 100, 85, 150.00, 'Conference', 'Completed', 'Marine Institute', 'research@marine.org', 'Scientific equipment, presentation facilities'),
(3, 'Mountain Photography Workshop', 'Professional landscape photography training', '2024-09-20', '07:00:00', '19:00:00', 'Mountain Base', 20, 18, 200.00, 'Workshop', 'Completed', 'Photo Academy', 'workshops@photoacademy.com', 'Photography equipment, transportation');

-- Seasonal and specialty events per property (idempotent)
INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 1, 'New Year Skyline Celebration', 'Black-tie rooftop gala with fireworks viewing and live orchestra', '2024-12-31', '20:00:00', '23:59:00', 'Skyline Rooftop', 250, 0, 325.00, 'Party', 'Upcoming', 'Grand Plaza Events', 'events@grandplaza.com', 'Formal attire, live band, fireworks access'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 1 AND event_name = 'New Year Skyline Celebration');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 3, 'Winter Ski Festival', 'Week-long celebration featuring night skiing, workshops, and apres-ski parties', '2024-12-05', '09:00:00', '21:00:00', 'Mountain Lodge Base Camp', 180, 0, 145.00, 'Festival', 'Upcoming', 'Mountain Lodge Adventures', 'activities@mountainlodge.com', 'Ski passes, safety gear, guided tours'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 3 AND event_name = 'Winter Ski Festival');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 4, 'Executive Leadership Summit', 'Invitation-only leadership summit with keynote speakers and breakout strategy sessions', '2024-11-05', '08:00:00', '19:00:00', 'Executive Conference Pavilion', 120, 0, 275.00, 'Conference', 'Upcoming', 'Skyline Corporate Services', 'summit@skyline.com', 'VIP seating, translation services, gourmet catering'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 4 AND event_name = 'Executive Leadership Summit');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 5, 'River Lights Festival', 'Evening celebration with illuminated boat parade, live jazz, and gourmet tastings', '2024-12-12', '17:00:00', '23:00:00', 'Riverside Promenade', 220, 0, 185.00, 'Festival', 'Upcoming', 'Riverside Experiences', 'celebrate@riverside.com', 'Boat access, lighting rigs, catering stations'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 5 AND event_name = 'River Lights Festival');

-- Events for hotels 6-9
INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 6, 'Seattle Coffee Culture Festival', 'Weekend celebration of local coffee culture with tastings and barista competitions', '2024-11-16', '09:00:00', '18:00:00', 'City Center Atrium', 200, 0, 85.00, 'Festival', 'Upcoming', 'Seattle Coffee Guild', 'events@coffeeguild.com', 'Coffee stations, competition area, seating'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 6 AND event_name = 'Seattle Coffee Culture Festival');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 7, 'Harborfront Jazz Night', 'Sunset jazz experience with gourmet tastings on the pier', '2024-11-22', '18:30:00', '22:30:00', 'Harbor Promenade', 180, 0, 145.00, 'Party', 'Upcoming', 'Harborfront Events', 'events@harborfront.com', 'Outdoor heaters, live band stage, tasting stations'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 7 AND event_name = 'Harborfront Jazz Night');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 8, 'Forest Wellness Retreat', 'Weekend of guided meditation, forest bathing, and holistic workshops', '2024-11-08', '07:00:00', '19:00:00', 'Evergreen Pavilion', 120, 0, 210.00, 'Retreat', 'Upcoming', 'Nature Balance Collective', 'wellness@forestretreat.com', 'Yoga mats, sound bath equipment, organic catering'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 8 AND event_name = 'Forest Wellness Retreat');

INSERT INTO events (hotel_id, event_name, description, event_date, start_time, end_time, venue, max_participants, current_participants, price, event_type, event_status, organizer_name, organizer_contact, requirements)
SELECT 9, 'Metropolitan Art Fair', 'Curated art fair featuring emerging artists, galleries, and collectors', '2024-12-03', '10:00:00', '21:00:00', 'Atrium Exhibition Hall', 350, 0, 185.00, 'Exhibition', 'Upcoming', 'Metropolitan Arts Council', 'curator@metart.com', 'Gallery lighting, ticketing booth, security detail'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE hotel_id = 9 AND event_name = 'Metropolitan Art Fair');

-- =====================================================
-- EVENT BOOKINGS
-- =====================================================

-- Bookings for completed events
INSERT IGNORE INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status, special_requirements) VALUES
-- Summer Music Festival bookings
(8, 1, 2, 150.00, 'Attended', 'VIP seating requested'),
(8, 2, 1, 75.00, 'Attended', NULL),
(8, 5, 3, 225.00, 'Attended', 'Group seating'),
(8, 8, 2, 150.00, 'Attended', NULL),

-- Marine Biology Conference bookings
(9, 3, 1, 150.00, 'Attended', 'Dietary restrictions: vegetarian'),
(9, 10, 1, 150.00, 'Attended', NULL),
(9, 12, 1, 150.00, 'No-Show', NULL),

-- Mountain Photography Workshop bookings
(10, 6, 1, 200.00, 'Attended', 'Camera equipment rental needed'),
(10, 9, 1, 200.00, 'Attended', NULL),

-- Future event bookings
(1, 4, 1, 200.00, 'Confirmed', 'Presentation slot requested'),
(1, 7, 2, 400.00, 'Confirmed', NULL),
(2, 1, 2, 600.00, 'Confirmed', 'Special menu preferences'),
(3, 11, 1, 120.00, 'Confirmed', 'Beginner level'),
(4, 3, 3, 540.00, 'Confirmed', 'Team building focus'),
(5, 5, 1, 100.00, 'Confirmed', NULL);

SET @event_new_year := (SELECT event_id FROM events WHERE hotel_id = 1 AND event_name = 'New Year Skyline Celebration' LIMIT 1);
INSERT INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status, special_requirements)
SELECT @event_new_year, 1, 2, 650.00, 'Confirmed', 'Front-row table'
FROM dual
WHERE @event_new_year IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM event_bookings WHERE event_id = @event_new_year AND guest_id = 1
  );

SET @event_winter_ski := (SELECT event_id FROM events WHERE hotel_id = 3 AND event_name = 'Winter Ski Festival' LIMIT 1);
INSERT INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status, special_requirements)
SELECT @event_winter_ski, 6, 4, 420.00, 'Confirmed', 'Family package'
FROM dual
WHERE @event_winter_ski IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM event_bookings WHERE event_id = @event_winter_ski AND guest_id = 6
  );

SET @event_exec_summit := (SELECT event_id FROM events WHERE hotel_id = 4 AND event_name = 'Executive Leadership Summit' LIMIT 1);
INSERT INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status, special_requirements)
SELECT @event_exec_summit, 3, 1, 275.00, 'Confirmed', 'Speaker access'
FROM dual
WHERE @event_exec_summit IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM event_bookings WHERE event_id = @event_exec_summit AND guest_id = 3
  );

SET @event_river_lights := (SELECT event_id FROM events WHERE hotel_id = 5 AND event_name = 'River Lights Festival' LIMIT 1);
INSERT INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status, special_requirements)
SELECT @event_river_lights, 8, 2, 370.00, 'Confirmed', 'Riverfront seating'
FROM dual
WHERE @event_river_lights IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM event_bookings WHERE event_id = @event_river_lights AND guest_id = 8
  );

-- =====================================================
-- SERVICES DATA
-- =====================================================

INSERT INTO services (hotel_id, service_name, description, price, service_type, is_active) VALUES
-- Additional services for existing hotels
(1, 'Concierge Service', 'Personal concierge assistance', 30.00, 'Other', TRUE),
(1, 'Business Center Access', '24/7 business center with printing and meeting facilities', 20.00, 'Other', TRUE),
(1, 'Valet Parking', 'Premium valet parking service', 35.00, 'Other', TRUE),

(2, 'Beach Equipment Rental', 'Umbrellas, chairs, and water sports equipment', 40.00, 'Other', TRUE),
(2, 'Massage Therapy', 'Professional therapeutic massage services', 120.00, 'Spa', TRUE),
(2, 'Sunset Cruise', 'Luxury yacht sunset cruise experience', 80.00, 'Other', TRUE),

(3, 'Equipment Rental', 'Hiking and camping equipment rental', 45.00, 'Other', TRUE),
(3, 'Trail Guide Service', 'Professional mountain trail guide', 75.00, 'Other', TRUE),

-- Services for new hotels
(4, 'Executive Lounge Access', 'Premium lounge with refreshments and business facilities', 50.00, 'Other', TRUE),
(4, 'Meeting Room Rental', 'Private meeting rooms with AV equipment', 100.00, 'Other', TRUE),
(4, 'Document Services', 'Printing, copying, and document preparation', 25.00, 'Other', TRUE),

(5, 'Couples Spa Package', 'Romantic spa treatment for couples', 250.00, 'Spa', TRUE),
(5, 'Yoga Classes', 'Professional yoga instruction by the river', 35.00, 'Other', TRUE),
(5, 'Wine Tasting', 'Premium wine tasting experience', 60.00, 'Restaurant', TRUE);

-- =====================================================
-- SERVICE BOOKINGS
-- =====================================================

INSERT INTO service_bookings (booking_id, service_id, quantity, unit_price, total_price, service_date, service_time, status, special_instructions) VALUES
-- Services for completed bookings
(1, 1, 1, 25.00, 25.00, '2024-01-16', '19:30:00', 'Completed', 'Room 101'),
(2, 2, 1, 15.00, 15.00, '2024-03-21', '10:00:00', 'Completed', 'Express service'),
(3, 4, 2, 100.00, 200.00, '2024-06-12', '14:00:00', 'Completed', 'Couples treatment'),
(4, 5, 1, 75.00, 75.00, '2024-02-10', '20:00:00', 'Completed', 'Seafood preference'),
(7, 6, 1, 60.00, 60.00, '2024-01-22', '18:00:00', 'Completed', 'Mountain trail guide'),

-- Services for future bookings
(11, 7, 1, 30.00, 30.00, '2024-11-16', '12:00:00', 'Confirmed', 'Theater tickets'),
(12, 10, 1, 40.00, 40.00, '2024-10-21', '10:00:00', 'Requested', 'Beach chairs and umbrella'),
(13, 15, 1, 250.00, 250.00, '2024-12-03', '15:00:00', 'Confirmed', 'Anniversary celebration');

-- =====================================================
-- REVIEWS AND RATINGS
-- =====================================================

INSERT IGNORE INTO reviews (hotel_id, guest_id, booking_id, rating, title, comment, service_rating, cleanliness_rating, location_rating, amenities_rating, is_approved, admin_response) VALUES
(1, 1, 1, 4.5, 'Great downtown location', 'Excellent hotel with professional staff and great amenities. The room was clean and comfortable.', 4.5, 4.8, 4.9, 4.3, TRUE, 'Thank you for your positive feedback!'),
(2, 2, 4, 4.8, 'Amazing ocean views', 'Absolutely loved our stay! The ocean view was breathtaking and the spa services were top-notch.', 4.9, 4.7, 5.0, 4.8, TRUE, 'We\'re delighted you enjoyed the ocean views!'),
(1, 3, 5, 4.2, 'Good business hotel', 'Perfect for business travel. Conference facilities were excellent and staff was very helpful.', 4.3, 4.1, 4.5, 4.0, TRUE, NULL),
(1, 4, 8, 3.8, 'Decent stay', 'Good hotel but room was a bit small. Service was friendly though.', 4.0, 3.5, 4.2, 3.5, TRUE, 'Thank you for your feedback. We\'ll consider room size in future renovations.'),
(2, 5, 9, 4.9, 'Perfect romantic getaway', 'Incredible resort! Perfect for our Valentine\'s celebration. Will definitely return.', 4.8, 5.0, 4.9, 4.9, TRUE, 'So happy we could make your Valentine\'s special!'),
(3, 6, 10, 4.4, 'Great mountain retreat', 'Beautiful location and cozy atmosphere. Loved the fireplace in the room.', 4.2, 4.3, 4.8, 4.4, TRUE, NULL),
(1, 7, 11, 4.1, 'Family-friendly', 'Good hotel for families. Kids loved the pool area. Staff was accommodating with extra amenities.', 4.2, 4.0, 4.3, 3.9, TRUE, 'Glad your family enjoyed the stay!'),
(5, 8, 13, 4.7, 'Relaxing spa experience', 'The spa treatments were amazing! Very peaceful and rejuvenating environment.', 4.9, 4.6, 4.5, 4.8, TRUE, 'Thank you for choosing our spa services!'),
(3, 9, 14, 4.3, 'Good value for money', 'Nice mountain lodge with reasonable prices. Beautiful scenery and clean rooms.', 4.1, 4.4, 4.6, 4.0, TRUE, NULL),
(5, 10, 15, 4.6, 'Excellent riverside location', 'Loved waking up to river views every morning. Very peaceful and well-maintained property.', 4.5, 4.7, 4.9, 4.4, TRUE, 'The river views are indeed special!');

-- =====================================================
-- PAYMENTS DATA
-- =====================================================

INSERT INTO payments (booking_id, payment_method, amount, transaction_id, payment_status, payment_date, gateway_response) VALUES
(1, 'Card', 248.37, 'TXN_2024_001', 'Success', '2024-01-14 15:30:00', 'Payment processed successfully'),
(2, 'Online', 757.67, 'TXN_2024_002', 'Success', '2024-02-07 09:15:00', 'Online payment confirmed'),
(3, 'Card', 915.42, 'TXN_2024_003', 'Success', '2024-03-19 14:22:00', 'Card payment authorized'),
(4, 'Online', 644.99, 'TXN_2024_004', 'Success', '2024-04-14 11:45:00', 'PayPal payment completed'),
(5, 'Card', 507.41, 'TXN_2024_005', 'Success', '2024-06-09 16:30:00', 'Visa payment processed'),
(6, 'Online', 2373.40, 'TXN_2024_006', 'Success', '2024-01-19 13:20:00', 'Bank transfer confirmed'),
(7, 'Card', 1688.68, 'TXN_2024_007', 'Success', '2024-04-30 10:15:00', 'Mastercard payment approved'),
(8, 'Cash', 402.69, NULL, 'Success', '2024-03-01 15:00:00', 'Cash payment received'),
(9, 'Card', 451.02, 'TXN_2024_009', 'Success', '2024-02-13 12:30:00', 'Valentine special payment'),
(10, 'Online', 290.86, 'TXN_2024_010', 'Success', '2024-04-19 14:45:00', 'Third-party booking payment'),
(11, 'Card', 1522.21, 'TXN_2024_011', 'Success', '2024-11-10 09:30:00', 'Anniversary booking payment'),
(12, 'Online', 325.45, 'TXN_2024_012', 'Pending', '2024-10-15 16:20:00', 'Payment processing'),
(13, 'Card', 1350.00, 'TXN_2024_013', 'Success', '2024-11-25 11:15:00', 'Partial payment received'),
(14, 'Bank Transfer', 612.11, 'TXN_2024_014', 'Refunded', '2024-09-10 10:30:00', 'Refund processed due to cancellation');

-- =====================================================
-- STAFF DATA
-- =====================================================

INSERT IGNORE INTO staff (hotel_id, first_name, last_name, email, phone, position, department, salary, hire_date, is_active) VALUES
-- Grand Plaza Hotel staff
(1, 'Sarah', 'Johnson', 'sarah.j@grandplaza.com', '+1-555-3001', 'Front Desk Manager', 'Front Desk', 45000.00, '2020-03-15', TRUE),
(1, 'Michael', 'Davis', 'michael.d@grandplaza.com', '+1-555-3002', 'Housekeeping Supervisor', 'Housekeeping', 38000.00, '2021-01-20', TRUE),
(1, 'Jennifer', 'Wilson', 'jennifer.w@grandplaza.com', '+1-555-3003', 'Concierge', 'Front Desk', 42000.00, '2019-08-10', TRUE),
(1, 'Robert', 'Brown', 'robert.b@grandplaza.com', '+1-555-3004', 'Maintenance Supervisor', 'Maintenance', 48000.00, '2018-11-05', TRUE),

-- Ocean View Resort staff
(2, 'Emma', 'Martinez', 'emma.m@oceanview.com', '+1-555-3005', 'Spa Manager', 'Other', 55000.00, '2020-06-01', TRUE),
(2, 'David', 'Garcia', 'david.g@oceanview.com', '+1-555-3006', 'Beach Services Coordinator', 'Other', 35000.00, '2021-04-15', TRUE),
(2, 'Lisa', 'Rodriguez', 'lisa.r@oceanview.com', '+1-555-3007', 'Restaurant Manager', 'Restaurant', 50000.00, '2019-02-20', TRUE),

-- Mountain Lodge staff
(3, 'James', 'Taylor', 'james.t@mountainlodge.com', '+1-555-3008', 'Lodge Manager', 'Management', 60000.00, '2018-05-10', TRUE),
(3, 'Amanda', 'Anderson', 'amanda.a@mountainlodge.com', '+1-555-3009', 'Activity Coordinator', 'Other', 38000.00, '2020-09-15', TRUE),

-- Skyline Business Hotel staff
(4, 'Kevin', 'Thompson', 'kevin.t@skyline.com', '+1-555-3010', 'Business Center Manager', 'Other', 52000.00, '2021-01-10', TRUE),
(4, 'Maria', 'Lopez', 'maria.l@skyline.com', '+1-555-3011', 'Event Coordinator', 'Other', 46000.00, '2020-07-20', TRUE),

-- Riverside Resort staff
(5, 'Sophie', 'White', 'sophie.w@riverside.com', '+1-555-3012', 'Wellness Director', 'Other', 58000.00, '2019-12-01', TRUE),
(5, 'Carlos', 'Gonzalez', 'carlos.g@riverside.com', '+1-555-3013', 'Guest Relations Manager', 'Front Desk', 47000.00, '2020-03-25', TRUE);

-- =====================================================
-- HOTEL IMAGES DATA
-- =====================================================

INSERT INTO hotel_images (hotel_id, room_id, image_path, image_type, caption, is_primary, display_order) VALUES
-- Grand Plaza Hotel images
(1, NULL, 'images/hotels/1_grand_plaza_exterior.jpg', 'Hotel', 'Grand Plaza Hotel - Main Entrance', TRUE, 1),
(1, NULL, 'images/hotels/1_grand_plaza_lobby.jpg', 'Hotel', 'Elegant Lobby Area', FALSE, 2),
(1, NULL, 'images/hotels/1_grand_plaza_pool.jpg', 'Amenity', 'Rooftop Pool with City View', FALSE, 3),
(1, 1, 'images/hotels/1_room_101.jpg', 'Room', 'Standard Single Room', FALSE, 1),
(1, 2, 'images/hotels/1_room_102.jpg', 'Room', 'Standard Double Room', FALSE, 1),

-- Ocean View Resort images
(2, NULL, 'images/hotels/2_ocean_view_exterior.jpg', 'Hotel', 'Ocean View Resort - Beachfront', TRUE, 1),
(2, NULL, 'images/hotels/2_ocean_view_beach.jpg', 'Amenity', 'Private Beach Access', FALSE, 2),
(2, NULL, 'images/hotels/2_ocean_view_spa.jpg', 'Amenity', 'Luxury Spa Facilities', FALSE, 3),
(2, 6, 'images/hotels/2_room_A101.jpg', 'Room', 'Ocean View Double Room', FALSE, 1),

-- Mountain Lodge images
(3, NULL, 'images/hotels/3_mountain_lodge_exterior.jpg', 'Hotel', 'Mountain Lodge - Scenic Location', TRUE, 1),
(3, NULL, 'images/hotels/3_mountain_lodge_fireplace.jpg', 'Amenity', 'Cozy Fireplace Lounge', FALSE, 2);

-- =====================================================
-- UPDATE TRIGGERS TO REFLECT SAMPLE DATA
-- =====================================================

-- Update hotel room counts based on inserted rooms
UPDATE hotels SET total_rooms = (SELECT COUNT(*) FROM rooms WHERE hotel_id = hotels.hotel_id AND is_active = TRUE);

-- Update event participant counts based on event bookings
UPDATE events SET current_participants = (
    SELECT COUNT(*) 
    FROM event_bookings 
    WHERE event_id = events.event_id AND booking_status IN ('Confirmed', 'Attended')
);

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Verify data insertion
SELECT 
    'Hotels' as table_name, COUNT(*) as record_count FROM hotels
UNION ALL
SELECT 'Guests' as table_name, COUNT(*) as record_count FROM guests
UNION ALL
SELECT 'Rooms' as table_name, COUNT(*) as record_count FROM rooms
UNION ALL
SELECT 'Bookings' as table_name, COUNT(*) as record_count FROM bookings
UNION ALL
SELECT 'Events' as table_name, COUNT(*) as record_count FROM events
UNION ALL
SELECT 'Event Bookings' as table_name, COUNT(*) as record_count FROM event_bookings
UNION ALL
SELECT 'Reviews' as table_name, COUNT(*) as record_count FROM reviews
UNION ALL
SELECT 'Services' as table_name, COUNT(*) as record_count FROM services
UNION ALL
SELECT 'Staff' as table_name, COUNT(*) as record_count FROM staff;

-- Display summary statistics
SELECT 
    'Total Revenue from Completed Bookings' as metric,
    CONCAT('$', FORMAT(SUM(final_amount), 2)) as value
FROM bookings 
WHERE booking_status = 'Completed'

UNION ALL

SELECT 
    'Average Hotel Rating' as metric,
    CONCAT(ROUND(AVG(rating), 2), '/5.0') as value
FROM reviews 
WHERE is_approved = TRUE

UNION ALL

SELECT 
    'Total Event Participants' as metric,
    CAST(SUM(current_participants) AS CHAR) as value
FROM events 
WHERE event_status = 'Completed';

COMMIT;