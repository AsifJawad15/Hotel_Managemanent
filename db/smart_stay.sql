-- Smart Stay schema with plain-text admin password
CREATE DATABASE IF NOT EXISTS smart_stay;
USE smart_stay;

CREATE TABLE IF NOT EXISTS guests (
    guest_id   INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    phone      VARCHAR(20)  NOT NULL
);

CREATE TABLE IF NOT EXISTS hotels (
    hotel_id    INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name  VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS rooms (
    room_id     INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id    INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    type        VARCHAR(50) NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    is_booked   TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS bookings (
    booking_id  INT AUTO_INCREMENT PRIMARY KEY,
    guest_id    INT NOT NULL,
    room_id     INT NOT NULL
);

CREATE TABLE IF NOT EXISTS events (
    event_id    INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id    INT NOT NULL,
    event_name  VARCHAR(100) NOT NULL,
    description TEXT,
    event_date  DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS hotel_images (
    image_id   INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id   INT NOT NULL,
    image_path VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS admins (
    admin_id  INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50) NOT NULL,
    password  VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, password) VALUES ('admin', '12345678')
ON DUPLICATE KEY UPDATE password='12345678';
