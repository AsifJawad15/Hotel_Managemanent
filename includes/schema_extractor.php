<?php
/**
 * Database Schema Extractor
 * Extracts database schema information for AI context
 */

class SchemaExtractor {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Get complete database schema with descriptions
     */
    public function getDatabaseSchema() {
        return [
            'hotels' => [
                'description' => 'Hotel information and credentials',
                'columns' => [
                    ['name' => 'hotel_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'hotel_name', 'type' => 'VARCHAR(100)', 'description' => 'Hotel name'],
                    ['name' => 'email', 'type' => 'VARCHAR(100)', 'description' => 'Login email'],
                    ['name' => 'description', 'type' => 'TEXT', 'description' => 'Hotel description'],
                    ['name' => 'address', 'type' => 'VARCHAR(255)', 'description' => 'Street address'],
                    ['name' => 'city', 'type' => 'VARCHAR(50)', 'description' => 'City name'],
                    ['name' => 'state', 'type' => 'VARCHAR(50)', 'description' => 'State/Province'],
                    ['name' => 'country', 'type' => 'VARCHAR(50)', 'description' => 'Country name'],
                    ['name' => 'postal_code', 'type' => 'VARCHAR(20)', 'description' => 'Postal/ZIP code'],
                    ['name' => 'phone', 'type' => 'VARCHAR(20)', 'description' => 'Contact phone'],
                    ['name' => 'star_rating', 'type' => 'DECIMAL(2,1)', 'description' => 'Hotel star rating (1-5)'],
                    ['name' => 'total_rooms', 'type' => 'INT', 'description' => 'Total number of rooms'],
                    ['name' => 'check_in_time', 'type' => 'TIME', 'description' => 'Default check-in time'],
                    ['name' => 'check_out_time', 'type' => 'TIME', 'description' => 'Default check-out time'],
                    ['name' => 'is_active', 'type' => 'TINYINT', 'description' => '1=active, 0=inactive'],
                    ['name' => 'created_at', 'type' => 'TIMESTAMP', 'description' => 'Creation timestamp'],
                ],
                'relationships' => [
                    'Has many rooms (rooms.hotel_id)',
                    'Has many events (events.hotel_id)',
                    'Has many services (services.hotel_id)',
                    'Has many reviews (reviews.hotel_id)'
                ]
            ],
            'rooms' => [
                'description' => 'Hotel room inventory with pricing',
                'columns' => [
                    ['name' => 'room_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'hotel_id', 'type' => 'INT', 'description' => 'Foreign key to hotels'],
                    ['name' => 'room_number', 'type' => 'VARCHAR(20)', 'description' => 'Room number/identifier'],
                    ['name' => 'type_id', 'type' => 'INT', 'description' => 'Foreign key to room_types'],
                    ['name' => 'floor_number', 'type' => 'INT', 'description' => 'Floor number'],
                    ['name' => 'price', 'type' => 'DECIMAL(10,2)', 'description' => 'Base price per night'],
                    ['name' => 'area_sqft', 'type' => 'INT', 'description' => 'Room area in square feet'],
                    ['name' => 'max_occupancy', 'type' => 'INT', 'description' => 'Maximum guests allowed'],
                    ['name' => 'maintenance_status', 'type' => 'ENUM', 'description' => 'Available/Under Maintenance/Out of Service'],
                    ['name' => 'is_active', 'type' => 'TINYINT', 'description' => '1=active, 0=inactive'],
                ],
                'relationships' => [
                    'Belongs to hotel (hotel_id)',
                    'Belongs to room_type (type_id)',
                    'Has many bookings (bookings.room_id)'
                ]
            ],
            'room_types' => [
                'description' => 'Room type definitions (Standard, Deluxe, Suite, etc.)',
                'columns' => [
                    ['name' => 'type_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'type_name', 'type' => 'VARCHAR(50)', 'description' => 'Type name (Standard, Deluxe, Suite)'],
                    ['name' => 'description', 'type' => 'TEXT', 'description' => 'Type description'],
                    ['name' => 'max_occupancy', 'type' => 'INT', 'description' => 'Default max occupancy'],
                ],
                'relationships' => [
                    'Has many rooms (rooms.type_id)'
                ]
            ],
            'guests' => [
                'description' => 'Guest profiles with loyalty program',
                'columns' => [
                    ['name' => 'guest_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'name', 'type' => 'VARCHAR(100)', 'description' => 'Guest full name'],
                    ['name' => 'email', 'type' => 'VARCHAR(100)', 'description' => 'Login email'],
                    ['name' => 'phone', 'type' => 'VARCHAR(20)', 'description' => 'Contact phone'],
                    ['name' => 'date_of_birth', 'type' => 'DATE', 'description' => 'Date of birth'],
                    ['name' => 'gender', 'type' => 'ENUM', 'description' => 'Male/Female/Other'],
                    ['name' => 'nationality', 'type' => 'VARCHAR(50)', 'description' => 'Nationality'],
                    ['name' => 'loyalty_points', 'type' => 'INT', 'description' => 'Total loyalty points earned'],
                    ['name' => 'membership_level', 'type' => 'ENUM', 'description' => 'Bronze/Silver/Gold/Platinum'],
                    ['name' => 'is_active', 'type' => 'TINYINT', 'description' => '1=active, 0=inactive'],
                    ['name' => 'created_at', 'type' => 'TIMESTAMP', 'description' => 'Registration date'],
                ],
                'relationships' => [
                    'Has many bookings (bookings.guest_id)',
                    'Has many event_bookings (event_bookings.guest_id)',
                    'Has many reviews (reviews.guest_id)'
                ]
            ],
            'bookings' => [
                'description' => 'Room reservations with payment tracking',
                'columns' => [
                    ['name' => 'booking_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'guest_id', 'type' => 'INT', 'description' => 'Foreign key to guests'],
                    ['name' => 'room_id', 'type' => 'INT', 'description' => 'Foreign key to rooms'],
                    ['name' => 'check_in', 'type' => 'DATE', 'description' => 'Check-in date'],
                    ['name' => 'check_out', 'type' => 'DATE', 'description' => 'Check-out date'],
                    ['name' => 'adults', 'type' => 'INT', 'description' => 'Number of adults'],
                    ['name' => 'children', 'type' => 'INT', 'description' => 'Number of children'],
                    ['name' => 'total_amount', 'type' => 'DECIMAL(10,2)', 'description' => 'Amount before tax/discount'],
                    ['name' => 'discount_amount', 'type' => 'DECIMAL(10,2)', 'description' => 'Discount applied'],
                    ['name' => 'tax_amount', 'type' => 'DECIMAL(10,2)', 'description' => 'Tax amount'],
                    ['name' => 'final_amount', 'type' => 'DECIMAL(10,2)', 'description' => 'Final payable amount'],
                    ['name' => 'booking_status', 'type' => 'ENUM', 'description' => 'Confirmed/Cancelled/Completed/No-Show'],
                    ['name' => 'payment_status', 'type' => 'ENUM', 'description' => 'Pending/Paid/Partial/Refunded'],
                    ['name' => 'booking_source', 'type' => 'ENUM', 'description' => 'Website/Phone/Walk-in/Third-party'],
                    ['name' => 'created_at', 'type' => 'TIMESTAMP', 'description' => 'Booking creation time'],
                ],
                'relationships' => [
                    'Belongs to guest (guest_id)',
                    'Belongs to room (room_id)',
                    'Has many payments (payments.booking_id)'
                ]
            ],
            'events' => [
                'description' => 'Hotel events and functions',
                'columns' => [
                    ['name' => 'event_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'hotel_id', 'type' => 'INT', 'description' => 'Foreign key to hotels'],
                    ['name' => 'event_name', 'type' => 'VARCHAR(100)', 'description' => 'Event name'],
                    ['name' => 'description', 'type' => 'TEXT', 'description' => 'Event description'],
                    ['name' => 'event_date', 'type' => 'DATE', 'description' => 'Event date'],
                    ['name' => 'start_time', 'type' => 'TIME', 'description' => 'Start time'],
                    ['name' => 'end_time', 'type' => 'TIME', 'description' => 'End time'],
                    ['name' => 'venue', 'type' => 'VARCHAR(100)', 'description' => 'Event venue'],
                    ['name' => 'max_participants', 'type' => 'INT', 'description' => 'Maximum participants'],
                    ['name' => 'current_participants', 'type' => 'INT', 'description' => 'Current registered count'],
                    ['name' => 'price', 'type' => 'DECIMAL(10,2)', 'description' => 'Ticket price per person'],
                    ['name' => 'event_type', 'type' => 'ENUM', 'description' => 'Conference/Wedding/Meeting/Party/Workshop/Other'],
                    ['name' => 'event_status', 'type' => 'ENUM', 'description' => 'Upcoming/Active/Completed/Cancelled'],
                ],
                'relationships' => [
                    'Belongs to hotel (hotel_id)',
                    'Has many event_bookings (event_bookings.event_id)'
                ]
            ],
            'event_bookings' => [
                'description' => 'Guest event registrations',
                'columns' => [
                    ['name' => 'event_booking_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'event_id', 'type' => 'INT', 'description' => 'Foreign key to events'],
                    ['name' => 'guest_id', 'type' => 'INT', 'description' => 'Foreign key to guests'],
                    ['name' => 'participants', 'type' => 'INT', 'description' => 'Number of participants'],
                    ['name' => 'amount_paid', 'type' => 'DECIMAL(10,2)', 'description' => 'Amount paid'],
                    ['name' => 'booking_status', 'type' => 'ENUM', 'description' => 'Confirmed/Cancelled/Attended/No-Show'],
                ],
                'relationships' => [
                    'Belongs to event (event_id)',
                    'Belongs to guest (guest_id)'
                ]
            ],
            'reviews' => [
                'description' => 'Guest reviews and ratings',
                'columns' => [
                    ['name' => 'review_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'hotel_id', 'type' => 'INT', 'description' => 'Foreign key to hotels'],
                    ['name' => 'guest_id', 'type' => 'INT', 'description' => 'Foreign key to guests'],
                    ['name' => 'booking_id', 'type' => 'INT', 'description' => 'Foreign key to bookings (optional)'],
                    ['name' => 'rating', 'type' => 'DECIMAL(2,1)', 'description' => 'Overall rating (1-5)'],
                    ['name' => 'title', 'type' => 'VARCHAR(200)', 'description' => 'Review title'],
                    ['name' => 'comment', 'type' => 'TEXT', 'description' => 'Review comment'],
                    ['name' => 'service_rating', 'type' => 'DECIMAL(2,1)', 'description' => 'Service rating'],
                    ['name' => 'cleanliness_rating', 'type' => 'DECIMAL(2,1)', 'description' => 'Cleanliness rating'],
                    ['name' => 'location_rating', 'type' => 'DECIMAL(2,1)', 'description' => 'Location rating'],
                    ['name' => 'is_approved', 'type' => 'TINYINT', 'description' => '1=approved, 0=pending'],
                    ['name' => 'created_at', 'type' => 'TIMESTAMP', 'description' => 'Review date'],
                ],
                'relationships' => [
                    'Belongs to hotel (hotel_id)',
                    'Belongs to guest (guest_id)',
                    'Belongs to booking (booking_id)'
                ]
            ],
            'payments' => [
                'description' => 'Payment transactions',
                'columns' => [
                    ['name' => 'payment_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'booking_id', 'type' => 'INT', 'description' => 'Foreign key to bookings'],
                    ['name' => 'payment_method', 'type' => 'ENUM', 'description' => 'Cash/Card/Online/Bank Transfer'],
                    ['name' => 'amount', 'type' => 'DECIMAL(10,2)', 'description' => 'Payment amount'],
                    ['name' => 'transaction_id', 'type' => 'VARCHAR(100)', 'description' => 'Transaction reference'],
                    ['name' => 'payment_status', 'type' => 'ENUM', 'description' => 'Success/Failed/Pending/Refunded'],
                    ['name' => 'payment_date', 'type' => 'TIMESTAMP', 'description' => 'Payment timestamp'],
                ],
                'relationships' => [
                    'Belongs to booking (booking_id)'
                ]
            ],
            'services' => [
                'description' => 'Hotel additional services',
                'columns' => [
                    ['name' => 'service_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'hotel_id', 'type' => 'INT', 'description' => 'Foreign key to hotels'],
                    ['name' => 'service_name', 'type' => 'VARCHAR(100)', 'description' => 'Service name'],
                    ['name' => 'description', 'type' => 'TEXT', 'description' => 'Service description'],
                    ['name' => 'price', 'type' => 'DECIMAL(10,2)', 'description' => 'Service price'],
                    ['name' => 'service_type', 'type' => 'ENUM', 'description' => 'Spa/Restaurant/Room Service/Transport/Laundry/Other'],
                    ['name' => 'is_active', 'type' => 'TINYINT', 'description' => '1=active, 0=inactive'],
                ],
                'relationships' => [
                    'Belongs to hotel (hotel_id)'
                ]
            ],
            'admins' => [
                'description' => 'System administrators',
                'columns' => [
                    ['name' => 'admin_id', 'type' => 'INT', 'description' => 'Primary key'],
                    ['name' => 'username', 'type' => 'VARCHAR(50)', 'description' => 'Admin username'],
                    ['name' => 'email', 'type' => 'VARCHAR(100)', 'description' => 'Admin email'],
                    ['name' => 'full_name', 'type' => 'VARCHAR(100)', 'description' => 'Full name'],
                    ['name' => 'role', 'type' => 'ENUM', 'description' => 'Super Admin/Admin/Manager'],
                    ['name' => 'last_login', 'type' => 'TIMESTAMP', 'description' => 'Last login time'],
                    ['name' => 'is_active', 'type' => 'TINYINT', 'description' => '1=active, 0=inactive'],
                ],
                'relationships' => []
            ]
        ];
    }
    
    /**
     * Get sample queries for AI training
     */
    public function getSampleQueries() {
        return [
            'Show all hotels' => 'SELECT * FROM hotels WHERE is_active = 1',
            'Top 10 guests by loyalty points' => 'SELECT guest_id, name, loyalty_points, membership_level FROM guests ORDER BY loyalty_points DESC LIMIT 10',
            'Total revenue this month' => 'SELECT SUM(final_amount) as revenue FROM bookings WHERE booking_status = "Completed" AND MONTH(check_in) = MONTH(CURDATE())',
            'Available rooms in hotel 1' => 'SELECT r.* FROM rooms r WHERE r.hotel_id = 1 AND r.is_active = 1 AND r.maintenance_status = "Available"',
            'Upcoming events' => 'SELECT * FROM events WHERE event_status = "Upcoming" AND event_date >= CURDATE() ORDER BY event_date',
        ];
    }
}
?>
