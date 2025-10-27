<?php
session_start();
require_once '../../includes/db_connect.php';

// Initialize variables
$search_query = '';
$filter_event_type = '';
$filter_status = '';
$filter_hotel = '';
$success = null;
$error = null;

// Handle search and filters
// Query #1: LEFT JOIN - Search and filter events with hotel details
$sql = "SELECT e.*, h.hotel_name FROM events e 
        LEFT JOIN hotels h ON e.hotel_id = h.hotel_id 
        WHERE 1=1";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql .= " AND (e.event_name LIKE ? OR e.description LIKE ? OR h.hotel_name LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

if (isset($_GET['event_type']) && !empty($_GET['event_type'])) {
    $filter_event_type = $_GET['event_type'];
    $sql .= " AND e.event_type = ?";
    $params[] = $filter_event_type;
    $types .= "s";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filter_status = $_GET['status'];
    $sql .= " AND e.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (isset($_GET['hotel']) && !empty($_GET['hotel'])) {
    $filter_hotel = intval($_GET['hotel']);
    $sql .= " AND e.hotel_id = ?";
    $params[] = $filter_hotel;
    $types .= "i";
}

$sql .= " ORDER BY e.event_date DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $events = $stmt->get_result();
} else {
    $events = $conn->query($sql);
}

// Query #2: SELECT - Get hotels for dropdown filter and modal
$hotels = $conn->query("SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name");

// Query #3: SELECT - Get unique event types for filter
$event_types = $conn->query("SELECT DISTINCT event_type FROM events ORDER BY event_type");

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hotel_id = intval($_POST['hotel_id']);
        $event_name = esc($_POST['event_name']);
        $event_date = esc($_POST['event_date']);
        $event_type = esc($_POST['event_type']);
        $max_participants = intval($_POST['max_participants']);
        $price = floatval($_POST['price']);
        $status = esc($_POST['status']);
        $description = esc($_POST['description']);
        
        // Query #4: INSERT - Add new event
        $sql_insert = "INSERT INTO events (hotel_id, event_name, event_date, event_type, max_participants, price, status, description) 
                VALUES ($hotel_id, '$event_name', '$event_date', '$event_type', $max_participants, $price, '$status', '$description')";
        
        if ($conn->query($sql_insert)) {
            $success = "Event added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'update') {
        $event_id = intval($_POST['event_id']);
        $hotel_id = intval($_POST['hotel_id']);
        $event_name = esc($_POST['event_name']);
        $event_date = esc($_POST['event_date']);
        $event_type = esc($_POST['event_type']);
        $max_participants = intval($_POST['max_participants']);
        $price = floatval($_POST['price']);
        $status = esc($_POST['status']);
        $description = esc($_POST['description']);
        
        // Query #5: UPDATE - Update event details
        $sql_update = "UPDATE events SET hotel_id=$hotel_id, event_name='$event_name', event_date='$event_date', 
                event_type='$event_type', max_participants=$max_participants, price=$price, 
                status='$status', description='$description' WHERE event_id=$event_id";
        
        if ($conn->query($sql_update)) {
            $success = "Event updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if ($action === 'delete') {
        $event_id = intval($_POST['event_id']);
        // Query #6: DELETE - Remove event
        $sql_delete = "DELETE FROM events WHERE event_id=$event_id";
        
        if ($conn->query($sql_delete)) {
            $success = "Event deleted successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Reset result set for hotels dropdown in modal
$hotels_for_modal = $conn->query("SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4E56C0 0%, #9B5DE0 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: rgba(26, 26, 46, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(78, 86, 192, 0.3);
            border: 1px solid rgba(253, 207, 250, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 48px rgba(155, 93, 224, 0.4);
        }
        .btn-primary { 
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%); 
            border: none;
            box-shadow: 0 4px 15px rgba(155, 93, 224, 0.4);
            transition: all 0.3s ease;
        }
        .btn-primary:hover { 
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(215, 143, 238, 0.6);
            color: white;
            font-weight: 600;
        }
        .table { 
            color: #FDCFFA;
            background: rgba(255,255,255,0.03);
        }
        .table th { 
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%); 
            color: white;
            border: none;
            font-weight: 600;
        }
        .table td {
            border-color: rgba(253, 207, 250, 0.2);
            vertical-align: middle;
        }
        .table tbody tr {
            transition: all 0.3s ease;
        }
        .table tbody tr:hover {
            background: rgba(155, 93, 224, 0.2);
            transform: scale(1.01);
        }
        .badge-scheduled, .badge-open { 
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            color: #4E56C0;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-completed, .badge-closed { 
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-cancelled {
            background: linear-gradient(135deg, #78716c 0%, #44403c 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .query-sidebar {
            position: fixed;
            right: -450px;
            top: 0;
            width: 450px;
            height: 100vh;
            background: #0d1117;
            box-shadow: -5px 0 25px rgba(0,0,0,0.5);
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            z-index: 1050;
            overflow-y: auto;
            padding: 20px;
        }
        .query-sidebar.show {
            right: 0;
        }
        .query-toggle-btn {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: linear-gradient(135deg, #9B5DE0 0%, #D78FEE 100%);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 20px rgba(155, 93, 224, 0.5);
            z-index: 1040;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        .query-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(215, 143, 238, 0.8);
        }
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(155, 93, 224, 0.5);
            }
            50% {
                box-shadow: 0 4px 30px rgba(215, 143, 238, 0.8);
            }
        }
        .query-badge {
            background: linear-gradient(135deg, #D78FEE 0%, #FDCFFA 100%);
            color: #1a1a2e;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(215, 143, 238, 0.4);
        }
        .query-title-text {
            color: #D78FEE;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            font-style: italic;
        }
        .query-sql {
            background: #0d0d1f;
            color: #FDCFFA;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            padding: 14px;
            border-radius: 8px;
            overflow-x: auto;
            line-height: 1.6;
            white-space: pre;
            border-left: 4px solid #D78FEE;
            margin-bottom: 20px;
        }
        .search-box, .filter-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
            border-radius: 10px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .search-box:focus, .filter-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: #9B5DE0;
            box-shadow: 0 0 20px rgba(155, 93, 224, 0.5);
            color: #FDCFFA;
        }
        .filter-select option {
            background: #1a1a2e;
            color: #FDCFFA;
        }
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2541 100%);
            color: #FDCFFA;
            border: 1px solid rgba(155, 93, 224, 0.3);
        }
        .modal-header {
            border-bottom: 1px solid rgba(155, 93, 224, 0.3);
        }
        .modal-footer {
            border-top: 1px solid rgba(155, 93, 224, 0.3);
        }
        .form-control, .form-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(155, 93, 224, 0.3);
            color: #FDCFFA;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: #9B5DE0;
            color: #FDCFFA;
            box-shadow: 0 0 0 0.2rem rgba(155, 93, 224, 0.25);
        }
        .form-label {
            color: #D78FEE;
            font-weight: 500;
        }
        .alert {
            border-radius: 15px;
            border: none;
            animation: slideInDown 0.5s ease;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        h2 {
            color: #D78FEE;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(215, 143, 238, 0.5);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calendar-event-fill"></i> Manage Events</h2>
                    <a href="../../index.php" class="btn btn-secondary">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </div>

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search and Filter Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-search text-white"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control search-box border-start-0" 
                                           placeholder="Search by event name, description, hotel..." 
                                           value="<?= htmlspecialchars($search_query) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="event_type" class="form-select filter-select">
                                    <option value="">All Types</option>
                                    <?php while ($type_row = $event_types->fetch_assoc()): ?>
                                        <option value="<?= $type_row['event_type'] ?>" <?= $filter_event_type == $type_row['event_type'] ? 'selected' : '' ?>>
                                            <?= $type_row['event_type'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select filter-select">
                                    <option value="">All Status</option>
                                    <option value="Scheduled" <?= $filter_status == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="Completed" <?= $filter_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $filter_status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="hotel" class="form-select filter-select">
                                    <option value="">All Hotels</option>
                                    <?php while ($hotel_row = $hotels->fetch_assoc()): ?>
                                        <option value="<?= $hotel_row['hotel_id'] ?>" <?= $filter_hotel == $hotel_row['hotel_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($hotel_row['hotel_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel-fill"></i> Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Event Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New Event
                </button>

                <!-- Events Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Event Name</th>
                                        <th>Hotel</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Max Participants</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $events->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['event_id'] ?></td>
                                        <td><strong><?= htmlspecialchars($row['event_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['event_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['event_type']) ?></td>
                                        <td><?= $row['max_participants'] ?></td>
                                        <td>$<?= number_format($row['price'], 2) ?></td>
                                        <td>
                                            <span class="badge-<?= strtolower($row['status']) ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editEvent(<?= json_encode($row) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?= $row['event_id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Query Sidebar -->
    <div class="query-sidebar" id="querySidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color: #D78FEE;"><i class="bi bi-code-square"></i> SQL Queries</h5>
            <button class="btn btn-sm btn-danger" onclick="toggleQuerySidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="mb-3">
            <div class="query-badge">#1 SELECT (All Events with Hotel)</div>
            <div class="query-title-text">Purpose: Display all events with hotel name</div>
            <div class="query-sql">SELECT e.*, h.hotel_name 
FROM events e 
LEFT JOIN hotels h ON e.hotel_id = h.hotel_id
ORDER BY e.event_date DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#2 SEARCH Query</div>
            <div class="query-title-text">Purpose: Search events by multiple fields</div>
            <div class="query-sql">SELECT e.*, h.hotel_name 
FROM events e 
LEFT JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.event_name LIKE '%search%'
   OR e.description LIKE '%search%'
   OR h.hotel_name LIKE '%search%'
ORDER BY e.event_date DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#3 FILTER by Event Type</div>
            <div class="query-title-text">Purpose: Filter by event type</div>
            <div class="query-sql">SELECT e.*, h.hotel_name 
FROM events e 
LEFT JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.event_type = 'Conference'
ORDER BY e.event_date DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#4 FILTER by Status</div>
            <div class="query-title-text">Purpose: Filter by event status</div>
            <div class="query-sql">SELECT e.*, h.hotel_name 
FROM events e 
LEFT JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.status = 'Scheduled'
ORDER BY e.event_date DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#5 FILTER by Hotel</div>
            <div class="query-title-text">Purpose: Filter by hotel</div>
            <div class="query-sql">SELECT e.*, h.hotel_name 
FROM events e 
LEFT JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.hotel_id = 1
ORDER BY e.event_date DESC</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#6 Get Hotels for Dropdown</div>
            <div class="query-title-text">Purpose: Get all hotels for filter</div>
            <div class="query-sql">SELECT hotel_id, hotel_name 
FROM hotels 
ORDER BY hotel_name</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#7 Get Unique Event Types</div>
            <div class="query-title-text">Purpose: Get all event types for filter</div>
            <div class="query-sql">SELECT DISTINCT event_type 
FROM events 
ORDER BY event_type</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#8 INSERT Event</div>
            <div class="query-title-text">Purpose: Add new event</div>
            <div class="query-sql">INSERT INTO events 
(hotel_id, event_name, event_date, event_type, 
 max_participants, price, status, description) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#9 UPDATE Event</div>
            <div class="query-title-text">Purpose: Update event information</div>
            <div class="query-sql">UPDATE events 
SET hotel_id=?, event_name=?, event_date=?,
    event_type=?, max_participants=?, price=?,
    status=?, description=?
WHERE event_id=?</div>
        </div>

        <div class="mb-3">
            <div class="query-badge">#10 DELETE Event</div>
            <div class="query-title-text">Purpose: Delete event</div>
            <div class="query-sql">DELETE FROM events
WHERE event_id=?</div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="query-toggle-btn" onclick="toggleQuerySidebar()" title="Show/Hide SQL Queries">
        <i class="bi bi-code-slash fs-4 text-dark"></i>
    </button>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hotel</label>
                                <select name="hotel_id" class="form-select" required>
                                    <option value="">Select Hotel</option>
                                    <?php while ($h = $hotels_for_modal->fetch_assoc()): ?>
                                        <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['hotel_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Name</label>
                                <input type="text" name="event_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Type</label>
                                <input type="text" name="event_type" class="form-control" placeholder="Conference, Wedding, etc." required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Participants</label>
                                <input type="number" name="max_participants" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="event_id" id="edit_event_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hotel</label>
                                <select name="hotel_id" id="edit_hotel_id" class="form-select" required>
                                    <?php 
                                    $hotels_edit = $conn->query("SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name");
                                    while ($h = $hotels_edit->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['hotel_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Name</label>
                                <input type="text" name="event_name" id="edit_event_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" id="edit_event_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Type</label>
                                <input type="text" name="event_type" id="edit_event_type" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Participants</label>
                                <input type="number" name="max_participants" id="edit_max_participants" class="form-control" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" id="edit_price" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form method="POST" id="deleteForm" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="event_id" id="delete_event_id">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleQuerySidebar() {
            document.getElementById('querySidebar').classList.toggle('show');
        }

        function editEvent(event) {
            document.getElementById('edit_event_id').value = event.event_id;
            document.getElementById('edit_hotel_id').value = event.hotel_id;
            document.getElementById('edit_event_name').value = event.event_name;
            document.getElementById('edit_event_date').value = event.event_date;
            document.getElementById('edit_event_type').value = event.event_type;
            document.getElementById('edit_max_participants').value = event.max_participants;
            document.getElementById('edit_price').value = event.price;
            document.getElementById('edit_status').value = event.status;
            document.getElementById('edit_description').value = event.description;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteEvent(id) {
            if (confirm('Are you sure you want to delete this event?')) {
                document.getElementById('delete_event_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
