<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Database connection
include '../db.php';

// Initialize stats array with default values
$stats = [
    'users' => 0,
    'messages' => 0,
    'destinations' => 0
];

// Function to safely get count
function getCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) FROM $table");
    if ($result) {
        return $result->fetch_row()[0];
    }
    return 0;
}

// Get total users from all user tables
$total_users = 0;
$user_tables = ['users', 'hotels', 'cab_drivers'];
foreach ($user_tables as $ut) {
    $check = $conn->query("SHOW TABLES LIKE '$ut'");
    if ($check && $check->num_rows > 0) {
        $total_users += getCount($conn, $ut);
    }
}

// Get statistics with error handling
try {
    // Check if tables exist
    $tables = ['users', 'contact_messages', 'destinations'];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check && $check->num_rows > 0) {
            $stats[$table] = getCount($conn, $table);
        }
    }
} catch (Exception $e) {
    // Log error but continue execution
    error_log("Error in dashboard statistics: " . $e->getMessage());
}

// Get recent messages with error handling
$recentMessages = [];
try {
    $messagesQuery = $conn->query("
        SELECT m.*, u.name as user_name 
        FROM contact_messages m 
        JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at DESC 
        LIMIT 5
   ");
    if ($messagesQuery) {
        while ($row = $messagesQuery->fetch_assoc()) {
            $recentMessages[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching recent messages: " . $e->getMessage());
}

// Get recent bookings with error handling
$recentBookings = [];
try {
    $bookingsQuery = $conn->query("
        SELECT b.*, u.name as user_name, d.name as destination_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN destinations d ON b.destination_id = d.id 
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    if ($bookingsQuery) {
        while ($row = $bookingsQuery->fetch_assoc()) {
            $recentBookings[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching recent bookings: " . $e->getMessage());
}

$total_messages = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SARP Tour and Travels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px;
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #34495e;
        }

        .sidebar-menu a.active {
            background-color: #3498db;
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-card i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }

        .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .recent-activity h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .activity-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .user-info .profile-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
        }

        .user-info .profile-photo.default {
            background-color: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .user-info i {
            font-size: 1.5rem;
            color: #3498db;
        }

        .user-email {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="manage_destinations.php"><i class="fas fa-map-marker-alt"></i> Destinations</a></li>
                <li><a href="../Logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <?php if (isset($_SESSION['profile_photo']) && $_SESSION['profile_photo'] !== 'default.jpg'): ?>
                        <img src="../uploads/profile_photos/<?php echo htmlspecialchars($_SESSION['profile_photo']); ?>" 
                             alt="Profile Photo" 
                             class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo default">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <?php if (isset($_SESSION['email'])): ?>
                        <span class="user-email">(<?php echo htmlspecialchars($_SESSION['email']); ?>)</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Total Messages</h3>
                    <div class="number"><?php echo $total_messages; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Total Destinations</h3>
                    <div class="number"><?php echo $stats['destinations']; ?></div>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Messages</h2>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMessages as $message): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($message['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . '...'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo isset($message['status']) && $message['status'] ? 'status-completed' : 'status-pending'; ?>">
                                    <?php echo isset($message['status']) && $message['status'] ? 'Completed' : 'Pending'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 