USE `smart_stay`;

CREATE TABLE room_types (
  type_id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  max_occupancy INT DEFAULT 2,
  amenities LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin CHECK (json_valid(amenities)),
  created_at TIMESTAMP DEFAULT current_timestamp(),
  FULLTEXT KEY ft_room_type_search (type_name, description)
) ENGINE=InnoDB;

CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  full_name VARCHAR(100),
  role ENUM('Super Admin','Admin','Manager') DEFAULT 'Admin',
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  is_active TINYINT DEFAULT 1,
  KEY idx_admin_email (email)
) ENGINE=InnoDB;

CREATE TABLE guests (
  guest_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  date_of_birth DATE,
  gender ENUM('Male','Female','Other'),
  nationality VARCHAR(50),
  address TEXT,
  loyalty_points INT DEFAULT 0,
  membership_level ENUM('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze',
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_active TINYINT DEFAULT 1,
  KEY idx_guest_email (email),
  KEY idx_membership_level (membership_level),
  KEY idx_guest_membership_active (membership_level,is_active)
) ENGINE=InnoDB;

CREATE TABLE hotels (
  hotel_id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  description TEXT,
  address VARCHAR(255),
  city VARCHAR(50),
  state VARCHAR(50),
  country VARCHAR(50),
  postal_code VARCHAR(20),
  phone VARCHAR(20),
  star_rating DECIMAL(2,1),
  total_rooms INT DEFAULT 0,
  amenities LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin CHECK (json_valid(amenities)),
  check_in_time TIME DEFAULT '14:00:00',
  check_out_time TIME DEFAULT '11:00:00',
  established_year INT,
  license_number VARCHAR(50),
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  is_active TINYINT DEFAULT 1,
  KEY idx_hotel_city (city),
  KEY idx_star_rating (star_rating),
  FULLTEXT KEY ft_hotel_search (hotel_name,description,city)
) ENGINE=InnoDB;

CREATE TABLE rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  room_number VARCHAR(20) NOT NULL,
  type_id INT NOT NULL,
  floor_number INT,
  price DECIMAL(10,2) NOT NULL,
  area_sqft INT,
  max_occupancy INT DEFAULT 2,
  amenities LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin CHECK (json_valid(amenities)),
  maintenance_status ENUM('Available','Under Maintenance','Out of Service') DEFAULT 'Available',
  is_active TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY unique_room (hotel_id,room_number),
  KEY idx_room_hotel (hotel_id),
  KEY idx_room_type (type_id),
  KEY idx_room_price (price),
  KEY idx_room_hotel_status (hotel_id,maintenance_status,is_active),
  CONSTRAINT fk_room_hotel FOREIGN KEY (hotel_id) REFERENCES hotels (hotel_id) ON DELETE CASCADE,
  CONSTRAINT fk_room_type FOREIGN KEY (type_id) REFERENCES room_types (type_id)
) ENGINE=InnoDB;

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
  booking_status ENUM('Confirmed','Cancelled','Completed','No-Show') DEFAULT 'Confirmed',
  payment_status ENUM('Pending','Paid','Partial','Refunded') DEFAULT 'Pending',
  booking_source ENUM('Website','Phone','Walk-in','Third-party') DEFAULT 'Website',
  special_requests TEXT,
  cancellation_reason TEXT,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY idx_booking_guest (guest_id),
  KEY idx_booking_room (room_id),
  KEY idx_booking_dates (check_in,check_out),
  KEY idx_booking_status (booking_status),
  KEY idx_booking_date_status (check_in,check_out,booking_status),
  CONSTRAINT fk_booking_guest FOREIGN KEY (guest_id) REFERENCES guests (guest_id) ON DELETE CASCADE,
  CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms (room_id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
  event_type ENUM('Conference','Wedding','Meeting','Party','Workshop','Other') DEFAULT 'Other',
  event_status ENUM('Upcoming','Active','Completed','Cancelled') DEFAULT 'Upcoming',
  organizer_name VARCHAR(100),
  organizer_contact VARCHAR(100),
  requirements TEXT,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY idx_event_hotel (hotel_id),
  KEY idx_event_date (event_date),
  KEY idx_event_status (event_status),
  KEY idx_event_date_status (event_date,event_status,hotel_id),
  FULLTEXT KEY ft_event_search (event_name,description,venue),
  CONSTRAINT fk_event_hotel FOREIGN KEY (hotel_id) REFERENCES hotels (hotel_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE event_bookings (
  event_booking_id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  guest_id INT NOT NULL,
  participants INT DEFAULT 1,
  amount_paid DECIMAL(10,2) NOT NULL,
  booking_status ENUM('Confirmed','Cancelled','Attended','No-Show') DEFAULT 'Confirmed',
  special_requirements TEXT,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY unique_event_guest (event_id,guest_id),
  KEY idx_event_booking_guest (guest_id),
  CONSTRAINT fk_event_booking_event FOREIGN KEY (event_id) REFERENCES events (event_id) ON DELETE CASCADE,
  CONSTRAINT fk_event_booking_guest FOREIGN KEY (guest_id) REFERENCES guests (guest_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  guest_id INT NOT NULL,
  booking_id INT,
  rating DECIMAL(2,1) NOT NULL,
  title VARCHAR(200),
  comment TEXT,
  service_rating DECIMAL(2,1),
  cleanliness_rating DECIMAL(2,1),
  location_rating DECIMAL(2,1),
  amenities_rating DECIMAL(2,1),
  is_approved TINYINT DEFAULT 0,
  admin_response TEXT,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY idx_review_hotel (hotel_id),
  KEY idx_review_guest (guest_id),
  KEY idx_review_booking (booking_id),
  KEY idx_review_hotel_approved (hotel_id,is_approved,created_at),
  CONSTRAINT fk_review_hotel FOREIGN KEY (hotel_id) REFERENCES hotels (hotel_id) ON DELETE CASCADE,
  CONSTRAINT fk_review_guest FOREIGN KEY (guest_id) REFERENCES guests (guest_id) ON DELETE CASCADE,
  CONSTRAINT fk_review_booking FOREIGN KEY (booking_id) REFERENCES bookings (booking_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  payment_method ENUM('Cash','Card','Online','Bank Transfer') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  transaction_id VARCHAR(100),
  payment_status ENUM('Success','Failed','Pending','Refunded') DEFAULT 'Pending',
  payment_date TIMESTAMP DEFAULT current_timestamp(),
  gateway_response TEXT,
  KEY idx_payment_booking (booking_id),
  KEY idx_payment_status (payment_status),
  KEY idx_payment_date_status (payment_date,payment_status),
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id) REFERENCES bookings (booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE services (
  service_id INT AUTO_INCREMENT PRIMARY KEY,
  hotel_id INT NOT NULL,
  service_name VARCHAR(100) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  service_type ENUM('Spa','Restaurant','Room Service','Transport','Laundry','Other') DEFAULT 'Other',
  is_active TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  updated_at TIMESTAMP DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY idx_service_hotel (hotel_id),
  FULLTEXT KEY ft_service_search (service_name,description),
  CONSTRAINT fk_service_hotel FOREIGN KEY (hotel_id) REFERENCES hotels (hotel_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE system_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  user_type ENUM('Guest','Hotel','Admin','System') DEFAULT 'System',
  user_id INT,
  action VARCHAR(50) NOT NULL,
  table_name VARCHAR(50) NOT NULL,
  record_id INT,
  old_values JSON,
  new_values JSON,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  KEY idx_action (action),
  KEY idx_table (table_name),
  KEY idx_created (created_at)
) ENGINE=InnoDB;
