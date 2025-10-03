-- =====================================================
-- SMARTSTAY DATABASE SAMPLE DATA
-- Test data for development and demonstration
-- =====================================================

USE `smart_stay`;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- SAMPLE DATA: Room Types
-- =====================================================
INSERT IGNORE INTO `room_types` (`type_id`, `type_name`, `description`, `max_occupancy`, `amenities`) VALUES
(1, 'Standard', 'Comfortable room with basic amenities', 2, '["TV", "WiFi", "Air Conditioning"]'),
(2, 'Deluxe', 'Spacious room with premium amenities', 3, '["TV", "WiFi", "Air Conditioning", "Mini Bar", "City View"]'),
(3, 'Suite', 'Luxurious suite with separate living area', 4, '["TV", "WiFi", "Air Conditioning", "Mini Bar", "Ocean View", "Jacuzzi"]'),
(4, 'Family Room', 'Large room suitable for families', 5, '["TV", "WiFi", "Air Conditioning", "Kitchenette", "Extra Beds"]'),
(5, 'Executive Suite', 'Premium suite for business travelers', 2, '["TV", "WiFi", "Air Conditioning", "Workstation", "Meeting Room Access", "Lounge Access"]');

-- =====================================================
-- SAMPLE DATA: Admins
-- Password: 1234
-- =====================================================
INSERT IGNORE INTO `admins` (`admin_id`, `username`, `password`, `email`, `full_name`, `role`, `is_active`) VALUES
(1, 'admin', '1234', 'admin@smartstay.com', 'System Administrator', 'Super Admin', 1),
(2, 'manager', '1234', 'manager@smartstay.com', 'Hotel Manager', 'Manager', 1),
(3, 'support', '1234', 'support@smartstay.com', 'Support Admin', 'Admin', 1);

-- =====================================================
-- SAMPLE DATA: Hotels (9 hotels)
-- Password: 1234
-- =====================================================
INSERT IGNORE INTO `hotels` (`hotel_id`, `hotel_name`, `email`, `password`, `description`, `address`, `city`, `state`, `country`, `postal_code`, `phone`, `star_rating`, `amenities`, `established_year`) VALUES
(1, 'Grand Plaza Hotel', 'contact@grandplaza.com', '1234', 'Luxury hotel in downtown', '123 Main St', 'New York', 'NY', 'USA', '10001', '+1-212-555-0100', 5.0, '["Pool", "Spa", "Gym", "Restaurant", "Bar", "Conference Rooms"]', 1990),
(2, 'Seaside Resort', 'info@seasideresort.com', '1234', 'Beautiful beachfront resort', '456 Ocean Blvd', 'Miami', 'FL', 'USA', '33139', '+1-305-555-0200', 4.5, '["Beach Access", "Pool", "Water Sports", "Restaurant", "Spa"]', 1995),
(3, 'Mountain View Lodge', 'reservations@mountainview.com', '1234', 'Cozy mountain retreat', '789 Summit Rd', 'Denver', 'CO', 'USA', '80202', '+1-303-555-0300', 4.0, '["Skiing", "Hiking", "Restaurant", "Fireplace", "Spa"]', 2000),
(4, 'Urban Boutique Hotel', 'hello@urbanboutique.com', '1234', 'Modern boutique hotel', '321 Fashion Ave', 'Los Angeles', 'CA', 'USA', '90028', '+1-213-555-0400', 4.5, '["Rooftop Bar", "Pool", "Gym", "Restaurant", "Art Gallery"]', 2010),
(5, 'Historic Inn', 'bookings@historicinn.com', '1234', 'Charming historic property', '567 Heritage Ln', 'Boston', 'MA', 'USA', '02108', '+1-617-555-0500', 4.0, '["Library", "Garden", "Restaurant", "Tea Room"]', 1885),
(6, 'City Center Express', 'info@citycenterexpress.com', '1234', 'Convenient city center location', '100 Business Plaza', 'Chicago', 'IL', 'USA', '60601', '+1-312-555-0600', 3.5, '["WiFi", "Business Center", "Coffee Shop", "Parking"]', 2015),
(7, 'Harborfront Luxury Suites', 'stay@harborfront.com', '1234', 'Luxury suites with harbor views', '200 Marina Way', 'Seattle', 'WA', 'USA', '98101', '+1-206-555-0700', 5.0, '["Harbor View", "Spa", "Fine Dining", "Concierge", "Valet"]', 2018),
(8, 'Forest Retreat Villas', 'welcome@forestretreat.com', '1234', 'Peaceful forest getaway', '300 Woodland Path', 'Portland', 'OR', 'USA', '97201', '+1-503-555-0800', 4.5, '["Nature Trails", "Yoga", "Organic Restaurant", "Spa", "Wildlife Tours"]', 2020),
(9, 'Metropolitan Art Hotel', 'contact@metroarthotel.com', '1234', 'Contemporary art-themed hotel', '400 Gallery Street', 'San Francisco', 'CA', 'USA', '94102', '+1-415-555-0900', 4.5, '["Art Exhibitions", "Rooftop Bar", "Restaurant", "Gym", "Library"]', 2017);

-- =====================================================
-- SAMPLE DATA: Guests
-- Password: 1234
-- =====================================================
INSERT IGNORE INTO `guests` (`guest_id`, `name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `nationality`, `loyalty_points`, `membership_level`) VALUES
(1, 'John Smith', 'john.smith@email.com', '1234', '+1-555-0101', '1985-03-15', 'Male', 'USA', 2500, 'Gold'),
(2, 'Emma Johnson', 'emma.j@email.com', '1234', '+1-555-0102', '1990-07-22', 'Female', 'USA', 5200, 'Platinum'),
(3, 'Michael Chen', 'mchen@email.com', '1234', '+1-555-0103', '1988-11-10', 'Male', 'China', 850, 'Silver'),
(4, 'Sarah Williams', 'swilliams@email.com', '1234', '+1-555-0104', '1992-05-18', 'Female', 'UK', 350, 'Bronze'),
(5, 'David Martinez', 'dmartinez@email.com', '1234', '+1-555-0105', '1987-09-30', 'Male', 'Spain', 1200, 'Silver'),
(6, 'Lisa Anderson', 'landerson@email.com', '1234', '+1-555-0106', '1995-01-25', 'Female', 'Canada', 180, 'Bronze'),
(7, 'Robert Taylor', 'rtaylor@email.com', '1234', '+1-555-0107', '1983-12-05', 'Male', 'USA', 6500, 'Platinum'),
(8, 'Jennifer Lee', 'jlee@email.com', '1234', '+1-555-0108', '1991-08-14', 'Female', 'South Korea', 420, 'Bronze'),
(9, 'James Brown', 'jbrown@email.com', '1234', '+1-555-0109', '1989-04-20', 'Male', 'Australia', 3100, 'Gold'),
(10, 'Maria Garcia', 'mgarcia@email.com', '1234', '+1-555-0110', '1994-06-08', 'Female', 'Mexico', 750, 'Silver');

-- =====================================================
-- SAMPLE DATA: Rooms (10 per hotel)
-- =====================================================

-- Hotel 1: Grand Plaza Hotel
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(1, 1, '101', 1, 1, 150.00, 300, 2),
(2, 1, '102', 2, 1, 220.00, 450, 3),
(3, 1, '201', 3, 2, 350.00, 650, 4),
(4, 1, '202', 1, 2, 150.00, 300, 2),
(5, 1, '301', 2, 3, 220.00, 450, 3),
(6, 1, '302', 3, 3, 350.00, 650, 4),
(7, 1, '401', 4, 4, 280.00, 550, 5),
(8, 1, '402', 5, 4, 420.00, 750, 2),
(9, 1, '501', 3, 5, 380.00, 700, 4),
(10, 1, '502', 5, 5, 450.00, 800, 2);

-- =====================================================
-- SAMPLE DATA: Bookings (Mix of past, current, future)
-- =====================================================
INSERT IGNORE INTO `bookings` (`booking_id`, `guest_id`, `room_id`, `check_in`, `check_out`, `total_amount`, `booking_status`, `special_requests`) VALUES
(1, 1, 1, '2025-10-15', '2025-10-18', 450.00, 'Confirmed', 'Late check-in requested'),
(2, 2, 3, '2025-10-20', '2025-10-25', 1750.00, 'Confirmed', 'Extra pillows please'),
(3, 3, 11, '2025-09-01', '2025-09-05', 720.00, 'Completed', NULL),
(4, 4, 21, '2025-10-10', '2025-10-12', 280.00, 'Confirmed', 'Early check-in if possible'),
(5, 5, 2, '2025-11-01', '2025-11-05', 880.00, 'Confirmed', 'Non-smoking room'),
(6, 7, 19, '2025-08-15', '2025-08-20', 2250.00, 'Completed', 'Anniversary celebration'),
(7, 9, 13, '2025-10-25', '2025-10-30', 2000.00, 'Confirmed', 'Ocean view preferred'),
(8, 1, 23, '2025-12-20', '2025-12-27', 2240.00, 'Confirmed', 'Holiday booking'),
(9, 2, 8, '2025-10-05', '2025-10-08', 1260.00, 'Confirmed', NULL),
(10, 10, 12, '2025-09-10', '2025-09-15', 1250.00, 'Completed', 'Honeymoon package');

-- Hotel 2: Seaside Resort
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(11, 2, '101', 1, 1, 180.00, 320, 2),
(12, 2, '102', 2, 1, 250.00, 480, 3),
(13, 2, '201', 3, 2, 400.00, 680, 4),
(14, 2, '202', 1, 2, 180.00, 320, 2),
(15, 2, '301', 2, 3, 250.00, 480, 3),
(16, 2, '302', 3, 3, 400.00, 680, 4),
(17, 2, '401', 4, 4, 320.00, 580, 5),
(18, 2, '402', 3, 4, 420.00, 700, 4),
(19, 2, '501', 3, 5, 450.00, 750, 4),
(20, 2, '502', 5, 5, 520.00, 850, 2);

-- Hotel 3: Mountain View Lodge  
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(21, 3, '101', 1, 1, 140.00, 280, 2),
(22, 3, '102', 2, 1, 210.00, 420, 3),
(23, 3, '201', 3, 2, 320.00, 620, 4),
(24, 3, '202', 1, 2, 140.00, 280, 2),
(25, 3, '301', 2, 3, 210.00, 420, 3),
(26, 3, '302', 3, 3, 320.00, 620, 4),
(27, 3, '401', 4, 4, 270.00, 540, 5),
(28, 3, '402', 2, 4, 220.00, 440, 3),
(29, 3, '501', 3, 5, 350.00, 680, 4),
(30, 3, '502', 5, 5, 410.00, 780, 2);

-- Hotel 4: Urban Boutique Hotel
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(31, 4, '101', 1, 1, 190.00, 310, 2),
(32, 4, '102', 2, 1, 270.00, 470, 3),
(33, 4, '201', 3, 2, 410.00, 670, 4),
(34, 4, '202', 1, 2, 190.00, 310, 2),
(35, 4, '301', 2, 3, 270.00, 470, 3),
(36, 4, '302', 3, 3, 410.00, 670, 4),
(37, 4, '401', 4, 4, 340.00, 590, 5),
(38, 4, '402', 5, 4, 480.00, 790, 2),
(39, 4, '501', 3, 5, 440.00, 720, 4),
(40, 4, '502', 5, 5, 510.00, 830, 2);

-- Hotel 5: Historic Inn
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(41, 5, '101', 1, 1, 130.00, 290, 2),
(42, 5, '102', 2, 1, 200.00, 430, 3),
(43, 5, '201', 3, 2, 310.00, 630, 4),
(44, 5, '202', 1, 2, 130.00, 290, 2),
(45, 5, '301', 2, 3, 200.00, 430, 3),
(46, 5, '302', 3, 3, 310.00, 630, 4),
(47, 5, '401', 4, 4, 260.00, 530, 5),
(48, 5, '402', 2, 4, 210.00, 450, 3),
(49, 5, '501', 3, 5, 340.00, 670, 4),
(50, 5, '502', 5, 5, 400.00, 770, 2);

-- Hotel 6: City Center Express
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(51, 6, '101', 1, 1, 120.00, 280, 2),
(52, 6, '102', 1, 1, 120.00, 280, 2),
(53, 6, '201', 2, 2, 180.00, 400, 3),
(54, 6, '202', 2, 2, 180.00, 400, 3),
(55, 6, '301', 3, 3, 280.00, 600, 4),
(56, 6, '302', 1, 3, 120.00, 280, 2),
(57, 6, '401', 2, 4, 180.00, 400, 3),
(58, 6, '402', 3, 4, 280.00, 600, 4),
(59, 6, '501', 4, 5, 240.00, 520, 5),
(60, 6, '502', 2, 5, 180.00, 400, 3);

-- Hotel 7: Harborfront Luxury Suites
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(61, 7, '101', 2, 1, 280.00, 490, 3),
(62, 7, '102', 3, 1, 420.00, 690, 4),
(63, 7, '201', 3, 2, 450.00, 720, 4),
(64, 7, '202', 5, 2, 530.00, 840, 2),
(65, 7, '301', 3, 3, 450.00, 720, 4),
(66, 7, '302', 5, 3, 530.00, 840, 2),
(67, 7, '401', 3, 4, 480.00, 750, 4),
(68, 7, '402', 5, 4, 560.00, 870, 2),
(69, 7, '501', 3, 5, 520.00, 800, 4),
(70, 7, '502', 5, 5, 600.00, 900, 2);

-- Hotel 8: Forest Retreat Villas
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(71, 8, 'V1', 4, 1, 340.00, 650, 5),
(72, 8, 'V2', 4, 1, 340.00, 650, 5),
(73, 8, 'V3', 3, 1, 390.00, 700, 4),
(74, 8, 'V4', 3, 1, 390.00, 700, 4),
(75, 8, 'V5', 4, 2, 360.00, 680, 5),
(76, 8, 'V6', 4, 2, 360.00, 680, 5),
(77, 8, 'V7', 3, 2, 410.00, 730, 4),
(78, 8, 'V8', 3, 2, 410.00, 730, 4),
(79, 8, 'V9', 5, 3, 490.00, 820, 2),
(80, 8, 'V10', 5, 3, 490.00, 820, 2);

-- Hotel 9: Metropolitan Art Hotel
INSERT IGNORE INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`) VALUES
(81, 9, '101', 1, 1, 170.00, 310, 2),
(82, 9, '102', 2, 1, 240.00, 460, 3),
(83, 9, '201', 3, 2, 380.00, 660, 4),
(84, 9, '202', 1, 2, 170.00, 310, 2),
(85, 9, '301', 2, 3, 240.00, 460, 3),
(86, 9, '302', 3, 3, 380.00, 660, 4),
(87, 9, '401', 4, 4, 310.00, 570, 5),
(88, 9, '402', 5, 4, 460.00, 780, 2),
(89, 9, '501', 3, 5, 420.00, 710, 4),
(90, 9, '502', 5, 5, 500.00, 820, 2);

-- =====================================================
-- SAMPLE DATA: Events (5 per hotel)
-- =====================================================

INSERT IGNORE INTO `events` (`event_id`, `hotel_id`, `event_name`, `description`, `event_date`, `start_time`, `end_time`, `venue`, `max_participants`, `current_participants`, `price`, `event_type`, `event_status`) VALUES
-- Hotel 1 Events
(1, 1, 'Business Leadership Summit', 'Annual conference for business leaders', '2025-03-15', '09:00:00', '17:00:00', 'Grand Ballroom', 200, 0, 450.00, 'Conference', 'Upcoming'),
(2, 1, 'Summer Jazz Night', 'Live jazz performance with dinner', '2025-06-20', '19:00:00', '23:00:00', 'Rooftop Terrace', 150, 0, 120.00, 'Party', 'Upcoming'),
(3, 1, 'Wedding Reception', 'Elegant wedding celebration', '2025-04-10', '18:00:00', '23:30:00', 'Crystal Ballroom', 250, 0, 200.00, 'Wedding', 'Upcoming'),
(4, 1, 'Tech Innovation Workshop', 'Latest trends in technology', '2025-05-05', '10:00:00', '16:00:00', 'Conference Room A', 80, 0, 350.00, 'Workshop', 'Upcoming'),
(5, 1, 'New Year Gala', 'Celebrate new year in style', '2025-12-31', '20:00:00', '02:00:00', 'Grand Ballroom', 300, 0, 180.00, 'Party', 'Upcoming'),

-- Hotel 2 Events
(6, 2, 'Beach Yoga Retreat', 'Weekend wellness and yoga', '2025-03-22', '07:00:00', '18:00:00', 'Beach Pavilion', 50, 0, 280.00, 'Workshop', 'Upcoming'),
(7, 2, 'Seafood Festival', 'Culinary experience by the ocean', '2025-07-15', '12:00:00', '22:00:00', 'Ocean View Restaurant', 120, 0, 95.00, 'Other', 'Upcoming'),
(8, 2, 'Corporate Team Building', 'Beach activities for teams', '2025-04-18', '09:00:00', '17:00:00', 'Beach Activities Center', 60, 0, 420.00, 'Conference', 'Upcoming'),
(9, 2, 'Sunset Wedding Ceremony', 'Romantic beachside wedding', '2025-05-30', '17:00:00', '22:00:00', 'Beach Garden', 180, 0, 250.00, 'Wedding', 'Upcoming'),
(10, 2, 'Summer Pool Party', 'DJ and poolside entertainment', '2025-08-12', '14:00:00', '20:00:00', 'Resort Pool', 200, 0, 75.00, 'Party', 'Upcoming'),

-- Hotel 3 Events
(11, 3, 'Mountain Photography Workshop', 'Capture stunning landscapes', '2025-04-08', '06:00:00', '18:00:00', 'Mountain Trail', 30, 0, 320.00, 'Workshop', 'Upcoming'),
(12, 3, 'Ski Competition', 'Annual skiing championship', '2025-02-20', '08:00:00', '16:00:00', 'Ski Slopes', 100, 0, 180.00, 'Other', 'Upcoming'),
(13, 3, 'Winter Wonderland Gala', 'Festive celebration', '2025-12-20', '18:00:00', '23:00:00', 'Mountain View Hall', 150, 0, 160.00, 'Party', 'Upcoming'),
(14, 3, 'Hiking Adventure Weekend', 'Guided mountain hikes', '2025-06-01', '07:00:00', '19:00:00', 'Trail Center', 40, 0, 250.00, 'Other', 'Upcoming'),
(15, 3, 'Mountain Lodge Wedding', 'Rustic mountain wedding', '2025-09-15', '15:00:00', '22:00:00', 'Lodge Hall', 120, 0, 280.00, 'Wedding', 'Upcoming'),

-- Hotel 4 Events
(16, 4, 'Art Gallery Opening', 'Contemporary art exhibition', '2025-03-10', '18:00:00', '22:00:00', 'Gallery Space', 100, 0, 65.00, 'Other', 'Upcoming'),
(17, 4, 'Fashion Show Extravaganza', 'Latest fashion trends', '2025-05-25', '19:00:00', '22:30:00', 'Runway Hall', 200, 0, 150.00, 'Party', 'Upcoming'),
(18, 4, 'Marketing Strategy Conference', 'Digital marketing insights', '2025-04-12', '09:00:00', '17:00:00', 'Conference Hall', 150, 0, 380.00, 'Conference', 'Upcoming'),
(19, 4, 'Rooftop Cocktail Mixer', 'Networking event', '2025-06-08', '18:00:00', '21:00:00', 'Rooftop Bar', 80, 0, 95.00, 'Party', 'Upcoming'),
(20, 4, 'Boutique Wedding Reception', 'Intimate wedding celebration', '2025-07-22', '17:00:00', '23:00:00', 'Boutique Ballroom', 100, 0, 220.00, 'Wedding', 'Upcoming'),

-- Hotel 5 Events
(21, 5, 'Historical Tea Party', 'Victorian-era themed tea', '2025-04-20', '14:00:00', '17:00:00', 'Tea Room', 60, 0, 85.00, 'Other', 'Upcoming'),
(22, 5, 'Book Club Literary Evening', 'Author meet and greet', '2025-05-15', '18:00:00', '21:00:00', 'Library', 40, 0, 55.00, 'Other', 'Upcoming'),
(23, 5, 'Classic Music Recital', 'Piano and violin performance', '2025-06-10', '19:00:00', '21:30:00', 'Music Hall', 90, 0, 75.00, 'Party', 'Upcoming'),
(24, 5, 'Heritage Wedding', 'Traditional wedding ceremony', '2025-08-05', '16:00:00', '22:00:00', 'Heritage Garden', 150, 0, 240.00, 'Wedding', 'Upcoming'),
(25, 5, 'Historical Architecture Tour', 'Guided building tour', '2025-03-28', '10:00:00', '14:00:00', 'Main Building', 25, 0, 45.00, 'Workshop', 'Upcoming'),

-- Hotel 6 Events
(26, 6, 'Business Breakfast Meeting', 'Networking breakfast', '2025-03-18', '07:30:00', '09:30:00', 'Conference Room', 40, 0, 65.00, 'Meeting', 'Upcoming'),
(27, 6, 'Express Training Workshop', 'Productivity and time management', '2025-04-25', '09:00:00', '13:00:00', 'Training Room', 30, 0, 180.00, 'Workshop', 'Upcoming'),
(28, 6, 'City Business Mixer', 'Professional networking', '2025-05-20', '17:00:00', '20:00:00', 'Coffee Lounge', 50, 0, 45.00, 'Meeting', 'Upcoming'),
(29, 6, 'Small Wedding Ceremony', 'Intimate wedding event', '2025-07-15', '15:00:00', '20:00:00', 'Event Space', 60, 0, 150.00, 'Wedding', 'Upcoming'),
(30, 6, 'Holiday Party', 'End of year celebration', '2025-12-15', '18:00:00', '22:00:00', 'Main Hall', 80, 0, 85.00, 'Party', 'Upcoming'),

-- Hotel 7 Events
(31, 7, 'Luxury Wine Tasting', 'Premium wines with harbor views', '2025-04-05', '18:00:00', '21:00:00', 'Penthouse Lounge', 40, 0, 180.00, 'Party', 'Upcoming'),
(32, 7, 'Executive Leadership Retreat', 'C-suite strategy session', '2025-05-12', '08:00:00', '18:00:00', 'Executive Suite', 25, 0, 850.00, 'Conference', 'Upcoming'),
(33, 7, 'Harbor View Wedding', 'Luxury waterfront wedding', '2025-06-28', '16:00:00', '23:00:00', 'Harbor Ballroom', 200, 0, 380.00, 'Wedding', 'Upcoming'),
(34, 7, 'Gourmet Culinary Workshop', 'Chef-led cooking class', '2025-07-10', '15:00:00', '19:00:00', 'Gourmet Kitchen', 20, 0, 280.00, 'Workshop', 'Upcoming'),
(35, 7, 'New Year Harbor Celebration', 'Fireworks and champagne', '2025-12-31', '21:00:00', '01:00:00', 'Harbor Deck', 150, 0, 250.00, 'Party', 'Upcoming'),

-- Hotel 8 Events
(36, 8, 'Forest Meditation Retreat', 'Mindfulness in nature', '2025-03-25', '08:00:00', '17:00:00', 'Forest Pavilion', 30, 0, 220.00, 'Workshop', 'Upcoming'),
(37, 8, 'Wildlife Photography Tour', 'Capture forest wildlife', '2025-05-18', '06:00:00', '18:00:00', 'Nature Trail', 15, 0, 340.00, 'Other', 'Upcoming'),
(38, 8, 'Organic Farm-to-Table Dinner', 'Sustainable dining experience', '2025-06-22', '18:00:00', '22:00:00', 'Forest Restaurant', 50, 0, 160.00, 'Other', 'Upcoming'),
(39, 8, 'Nature Wedding Ceremony', 'Eco-friendly forest wedding', '2025-08-20', '15:00:00', '21:00:00', 'Forest Garden', 80, 0, 290.00, 'Wedding', 'Upcoming'),
(40, 8, 'Autumn Harvest Festival', 'Seasonal celebration', '2025-10-15', '12:00:00', '20:00:00', 'Main Lodge', 100, 0, 95.00, 'Party', 'Upcoming'),

-- Hotel 9 Events
(41, 9, 'Contemporary Art Exhibition', 'Modern art showcase', '2025-03-30', '17:00:00', '21:00:00', 'Art Gallery', 120, 0, 75.00, 'Other', 'Upcoming'),
(42, 9, 'Creative Industries Summit', 'Design and innovation conference', '2025-04-28', '09:00:00', '18:00:00', 'Conference Center', 180, 0, 420.00, 'Conference', 'Upcoming'),
(43, 9, 'Artistic Wedding Celebration', 'Creative themed wedding', '2025-06-15', '17:00:00', '23:00:00', 'Gallery Ballroom', 150, 0, 320.00, 'Wedding', 'Upcoming'),
(44, 9, 'Jazz and Art Evening', 'Live music and art viewing', '2025-07-25', '19:00:00', '23:00:00', 'Rooftop Gallery', 100, 0, 110.00, 'Party', 'Upcoming'),
(45, 9, 'Photography Workshop', 'Urban photography techniques', '2025-05-08', '10:00:00', '16:00:00', 'Studio Space', 25, 0, 260.00, 'Workshop', 'Upcoming');

-- =====================================================
-- SAMPLE DATA: Staff
-- =====================================================
INSERT IGNORE INTO `staff` (`staff_id`, `hotel_id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `salary`, `hire_date`) VALUES
(1, 1, 'Alice', 'Johnson', 'alice.j@grandplaza.com', '+1-212-555-1001', 'Front Desk Manager', 'Front Desk', 45000.00, '2020-01-15'),
(2, 1, 'Bob', 'Smith', 'bob.s@grandplaza.com', '+1-212-555-1002', 'Housekeeping Supervisor', 'Housekeeping', 38000.00, '2019-03-20'),
(3, 2, 'Carlos', 'Rodriguez', 'carlos.r@seasideresort.com', '+1-305-555-2001', 'Concierge', 'Front Desk', 42000.00, '2021-05-10'),
(4, 2, 'Diana', 'Martinez', 'diana.m@seasideresort.com', '+1-305-555-2002', 'Head Chef', 'Restaurant', 65000.00, '2018-07-01'),
(5, 3, 'Edward', 'Wilson', 'edward.w@mountainview.com', '+1-303-555-3001', 'Maintenance Manager', 'Maintenance', 50000.00, '2019-11-15'),
(6, 4, 'Fiona', 'Chen', 'fiona.c@urbanboutique.com', '+1-213-555-4001', 'General Manager', 'Management', 85000.00, '2017-02-28'),
(7, 5, 'George', 'Taylor', 'george.t@historicinn.com', '+1-617-555-5001', 'Guest Relations', 'Front Desk', 40000.00, '2020-09-05'),
(8, 6, 'Hannah', 'Lee', 'hannah.l@citycenterexpress.com', '+1-312-555-6001', 'Receptionist', 'Front Desk', 35000.00, '2021-01-10'),
(9, 7, 'Ian', 'Brown', 'ian.b@harborfront.com', '+1-206-555-7001', 'Hotel Manager', 'Management', 75000.00, '2018-06-15'),
(10, 8, 'Julia', 'Anderson', 'julia.a@forestretreat.com', '+1-503-555-8001', 'Spa Director', 'Other', 55000.00, '2020-03-22'),
(11, 9, 'Kevin', 'White', 'kevin.w@metroarthotel.com', '+1-415-555-9001', 'Event Coordinator', 'Other', 48000.00, '2019-08-18');

-- =====================================================
-- SAMPLE DATA: Services
-- =====================================================
INSERT IGNORE INTO `services` (`service_id`, `hotel_id`, `service_name`, `description`, `price`, `service_type`) VALUES
(1, 1, 'Spa Treatment', 'Relaxing massage and treatments', 120.00, 'Spa'),
(2, 1, 'Room Service', '24/7 in-room dining', 35.00, 'Room Service'),
(3, 1, 'Airport Transfer', 'Luxury car service', 75.00, 'Transport'),
(4, 2, 'Scuba Diving Lesson', 'Beginner diving course', 150.00, 'Other'),
(5, 2, 'Beach Cabana Rental', 'Private beach cabana', 80.00, 'Other'),
(6, 3, 'Ski Equipment Rental', 'Full ski gear', 65.00, 'Other'),
(7, 4, 'Personal Shopping', 'Fashion district tour', 200.00, 'Other'),
(8, 5, 'Tea Service', 'Afternoon tea experience', 45.00, 'Restaurant'),
(9, 7, 'Yacht Charter', 'Private harbor cruise', 800.00, 'Transport'),
(10, 8, 'Nature Guide Tour', 'Forest exploration', 95.00, 'Other');

-- =====================================================
-- SAMPLE DATA: Reviews (for testing satisfaction score)
-- =====================================================
INSERT IGNORE INTO `reviews` (`review_id`, `hotel_id`, `guest_id`, `rating`, `title`, `comment`, `service_rating`, `cleanliness_rating`, `location_rating`, `amenities_rating`, `is_approved`, `admin_response`) VALUES
(1, 1, 1, 4.5, 'Excellent Stay', 'Great hotel with wonderful service', 4.5, 5.0, 5.0, 4.0, 1, 'Thank you for your wonderful feedback!'),
(2, 1, 2, 5.0, 'Perfect!', 'Everything was absolutely perfect', 5.0, 5.0, 5.0, 5.0, 1, 'We are thrilled you enjoyed your stay!'),
(3, 2, 3, 4.0, 'Beautiful Beach Location', 'Amazing views but service could be better', 3.5, 4.0, 5.0, 4.5, 1, NULL),
(4, 3, 4, 4.5, 'Mountain Paradise', 'Loved the hiking trails and cozy atmosphere', 4.5, 4.5, 4.0, 4.5, 1, 'We appreciate your kind words!'),
(5, 1, 5, 4.8, 'Outstanding Service', 'The staff went above and beyond', 5.0, 4.5, 5.0, 4.5, 1, 'Your satisfaction means everything to us!'),
(6, 2, 6, 3.5, 'Good but not great', 'Room was nice but pool was crowded', 3.0, 4.0, 4.5, 3.5, 1, NULL),
(7, 1, 7, 5.0, 'Best Hotel Ever', 'Will definitely come back!', 5.0, 5.0, 5.0, 5.0, 1, 'Looking forward to welcoming you again!'),
(8, 3, 8, 4.2, 'Great Mountain Getaway', 'Perfect for winter vacation', 4.0, 4.5, 4.0, 4.0, 1, NULL),
(9, 4, 5, 5.0, 'Trendy and Modern', 'The design is stunning, great location', 5.0, 5.0, 5.0, 5.0, 1, 'Thank you for the 5-star review!'),
(10, 5, 6, 4.0, 'Charming Historic Property', 'Full of character and history', 4.0, 4.0, 4.5, 3.5, 1, NULL),
(11, 6, 7, 3.5, 'Good Value', 'Simple but clean and convenient', 3.5, 4.0, 5.0, 3.0, 1, NULL),
(12, 7, 8, 5.0, 'Luxury at its Best', 'Worth every penny, incredible service', 5.0, 5.0, 5.0, 5.0, 1, 'We are honored by your review!'),
(13, 8, 9, 4.5, 'Nature Lover\'s Dream', 'So peaceful and relaxing', 4.5, 4.5, 4.0, 4.5, 1, NULL),
(14, 9, 10, 4.5, 'Art and Comfort Combined', 'Unique experience with excellent amenities', 4.5, 4.5, 5.0, 5.0, 1, 'We appreciate your artistic eye!');

-- =====================================================
-- SAMPLE DATA: Events
-- =====================================================
INSERT IGNORE INTO `events` (`event_id`, `hotel_id`, `event_name`, `description`, `event_date`, `start_time`, `end_time`, `venue`, `max_participants`, `current_participants`, `price`, `event_type`, `event_status`) VALUES
(1, 1, 'New Year Gala', 'Celebrate New Year in style', '2025-12-31', '20:00:00', '02:00:00', 'Grand Ballroom', 200, 0, 150.00, 'Party', 'Upcoming'),
(2, 2, 'Beach Yoga Retreat', 'Morning yoga by the ocean', '2025-10-15', '06:00:00', '08:00:00', 'Beach Area', 30, 0, 25.00, 'Workshop', 'Upcoming'),
(3, 3, 'Ski Competition', 'Amateur skiing contest', '2025-12-20', '09:00:00', '17:00:00', 'Mountain Slope', 50, 0, 75.00, 'Other', 'Upcoming'),
(4, 1, 'Business Conference', 'Tech industry networking event', '2025-11-10', '09:00:00', '18:00:00', 'Conference Hall', 150, 0, 200.00, 'Conference', 'Upcoming'),
(5, 7, 'Wine Tasting Evening', 'Premium wine selection', '2025-10-20', '18:00:00', '21:00:00', 'Rooftop Lounge', 40, 0, 85.00, 'Other', 'Upcoming');

-- =====================================================
-- SAMPLE DATA: Event Bookings
-- =====================================================
INSERT IGNORE INTO `event_bookings` (`event_booking_id`, `event_id`, `guest_id`, `participants`, `amount_paid`, `booking_status`) VALUES
(1, 1, 1, 2, 300.00, 'Confirmed'),
(2, 2, 2, 1, 25.00, 'Confirmed'),
(3, 4, 7, 1, 200.00, 'Confirmed'),
(4, 5, 9, 2, 170.00, 'Confirmed');

SET FOREIGN_KEY_CHECKS = 1;
