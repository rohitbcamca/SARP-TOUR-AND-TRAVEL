<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Database connection
include '../db.php';

// Handle message actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['message_id'])) {
        $message_id = $_POST['message_id'];
        
        try {
            switch ($_POST['action']) {
                case 'toggle_status':
                    // Check if status column exists
                    $columnCheck = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'status'");
                    if ($columnCheck && $columnCheck->num_rows > 0) {
                        // Toggle message status
                        $stmt = $conn->prepare("UPDATE contact_messages SET status = NOT status WHERE message_id = ?");
                        if ($stmt === false) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                        $stmt->bind_param("i", $message_id);
                        if ($stmt->execute()) {
                            echo "<script>alert('Message status updated successfully.'); window.location.href='messages.php';</script>";
                            exit();
                        } else {
                            throw new Exception("Error executing statement: " . $stmt->error);
                        }
                    } else {
                        // If status column doesn't exist, add it
                        $conn->query("ALTER TABLE contact_messages ADD COLUMN status TINYINT(1) DEFAULT 0");
                        echo "<script>alert('Status column added. Please try the action again.'); window.location.href='messages.php';</script>";
                        exit();
                    }
                    break;
                    
                case 'delete':
                    // Delete message
                    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE message_id = ?");
                    if ($stmt === false) {
                        throw new Exception("Error preparing statement: " . $conn->error);
                    }
                    $stmt->bind_param("i", $message_id);
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Message deleted successfully.";
                    } else {
                        throw new Exception("Error executing statement: " . $stmt->error);
                    }
                    break;
                    
                case 'reply':
                    if (isset($_POST['reply'])) {
                        $reply = $_POST['reply'];
                        // Check if reply column exists
                        $columnCheck = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'reply'");
                        if ($columnCheck && $columnCheck->num_rows > 0) {
                            // Check if status column exists
                            $statusCheck = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'status'");
                            if ($statusCheck && $statusCheck->num_rows > 0) {
                                $stmt = $conn->prepare("UPDATE contact_messages SET reply = ?, status = 1 WHERE message_id = ?");
                                $stmt->bind_param("si", $reply, $message_id);
                            } else {
                                $stmt = $conn->prepare("UPDATE contact_messages SET reply = ? WHERE message_id = ?");
                                $stmt->bind_param("si", $reply, $message_id);
                            }
                        } else {
                            // If reply column doesn't exist, add it
                            $conn->query("ALTER TABLE contact_messages ADD COLUMN reply TEXT");
                            echo "<script>
                                alert('Reply column added. Please try the action again.');
                                window.location.href = 'messages.php';
                            </script>";
                            exit();
                        }
                        
                        if ($stmt === false) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                        
                        if ($stmt->execute()) {
                            // Get user information for notification
                            $userStmt = $conn->prepare("SELECT user_id FROM contact_messages WHERE message_id = ?");
                            $userStmt->bind_param("i", $message_id);
                            $userStmt->execute();
                            $userResult = $userStmt->get_result();
                            $userRow = $userResult->fetch_assoc();
                            $user_id = $userRow['user_id'];
                            
                            // Check if notifications table exists, create if not
                            $notifTableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
                            if (!$notifTableCheck || $notifTableCheck->num_rows == 0) {
                                $createNotifTable = $conn->query("
                                    CREATE TABLE notifications (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        user_id INT NOT NULL,
                                        message TEXT NOT NULL,
                                        type VARCHAR(50) NOT NULL,
                                        created_at DATETIME NOT NULL,
                                        FOREIGN KEY (user_id) REFERENCES users(id)
                                    )
                                ");
                                if (!$createNotifTable) {
                                    throw new Exception("Error creating notifications table: " . $conn->error);
                                }
                            } else {
                                // Check if type column exists, add if not
                                $columnCheck = $conn->query("SHOW COLUMNS FROM notifications LIKE 'type'");
                                if (!$columnCheck || $columnCheck->num_rows == 0) {
                                    $addColumn = $conn->query("ALTER TABLE notifications ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'message'");
                                    if (!$addColumn) {
                                        throw new Exception("Error adding type column: " . $conn->error);
                                    }
                                }
                            }
                            
                            // Insert notification
                            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, 'Your message has been replied to', 'message', NOW())");
                            if ($notifStmt === false) {
                                throw new Exception("Error preparing notification statement: " . $conn->error);
                            }
                            $notifStmt->bind_param("i", $user_id);
                            if (!$notifStmt->execute()) {
                                throw new Exception("Error executing notification statement: " . $notifStmt->error);
                            }
                            
                            echo "<script>
                                alert('Reply sent successfully!');
                                window.location.href = 'messages.php';
                            </script>";
                            exit();
                        } else {
                            throw new Exception("Error executing statement: " . $stmt->error);
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            error_log("Error in message action: " . $e->getMessage());
        }
        
        header("Location: messages.php");
        exit();
    }
}

// Get all messages with error handling
$messages = [];
try {
    // First check if the table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Check if status column exists
        $statusCheck = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'status'");
        $hasStatus = $statusCheck && $statusCheck->num_rows > 0;
        
        // Check if reply column exists
        $replyCheck = $conn->query("SHOW COLUMNS FROM contact_messages LIKE 'reply'");
        $hasReply = $replyCheck && $replyCheck->num_rows > 0;
        
        $messagesQuery = $conn->query("
            SELECT m.message_id, m.*, u.name as user_name, u.email as user_email 
            FROM contact_messages m 
            LEFT JOIN users u ON m.user_id = u.id 
            ORDER BY m.created_at DESC
        ");
        
        if ($messagesQuery) {
            while ($row = $messagesQuery->fetch_assoc()) {
                // Ensure all required fields exist
                $row['message_id'] = $row['message_id'] ?? '';
                $row['user_name'] = $row['user_name'] ?? 'Unknown User';
                $row['user_email'] = $row['user_email'] ?? 'No Email';
                $row['subject'] = $row['subject'] ?? 'No Subject';
                $row['message'] = $row['message'] ?? 'No Message';
                $row['created_at'] = $row['created_at'] ?? date('Y-m-d H:i:s');
                $row['status'] = $hasStatus ? ($row['status'] ?? 0) : 0;
                $row['reply'] = $hasReply ? ($row['reply'] ?? '') : '';
                $messages[] = $row;
            }
        } else {
            error_log("Error in messages query: " . $conn->error);
        }
    } else {
        error_log("Contact messages table does not exist");
    }
} catch (Exception $e) {
    error_log("Error fetching messages: " . $e->getMessage());
}

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Management - SARP Tour and Travels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Include the same styles as dashboard.php */
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

        .messages-table {
            width: 100%;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }

        .messages-table th,
        .messages-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .messages-table th {
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

        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 600px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close {
            font-size: 24px;
            cursor: pointer;
        }

        .reply-form {
            margin-top: 20px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            min-height: 100px;
        }

        .message-content {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .reply-content {
            margin-top: 20px;
            padding: 15px;
            background-color: #e3f2fd;
            border-radius: 5px;
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="manage_destinations.php"><i class="fas fa-map-marker-alt"></i> Destinations</a></li>
                <li><a href="../Logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Messages Management</h1>
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
                </div>
            </div>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search messages...">
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label>Status:</label>
                    <select id="statusFilter">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date Range:</label>
                    <select id="dateFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>

            <table class="messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo isset($message['message_id']) ? htmlspecialchars($message['message_id']) : ''; ?></td>
                        <td>
                            <?php echo isset($message['user_name']) ? htmlspecialchars($message['user_name']) : ''; ?><br>
                            <small><?php echo isset($message['user_email']) ? htmlspecialchars($message['user_email']) : ''; ?></small>
                        </td>
                        <td><?php echo isset($message['subject']) ? htmlspecialchars($message['subject']) : ''; ?></td>
                        <td><?php echo isset($message['message']) ? htmlspecialchars(substr($message['message'], 0, 50)) . '...' : ''; ?></td>
                        <td><?php echo isset($message['created_at']) ? date('M d, Y', strtotime($message['created_at'])) : ''; ?></td>
                        <td>
                            <span class="status-badge <?php echo isset($message['status']) && $message['status'] ? 'status-completed' : 'status-pending'; ?>">
                                <?php echo isset($message['status']) && $message['status'] ? 'Completed' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="message_id" value="<?php echo isset($message['message_id']) ? htmlspecialchars($message['message_id']) : ''; ?>">
                                <button type="button" class="btn btn-primary view-message" data-message='<?php echo isset($message) ? htmlspecialchars(json_encode($message)) : ''; ?>'>
                                    View
                                </button>
                                <button type="submit" name="action" value="toggle_status" class="btn btn-success">
                                    <?php echo isset($message['status']) && $message['status'] ? 'Mark Pending' : 'Mark Completed'; ?>
                                </button>
                                <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Message View Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Message Details</h2>
                <span class="close">&times;</span>
            </div>
            <div id="messageDetails">
                <!-- Message content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.messages-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('.messages-table tbody tr');
            
            rows.forEach(row => {
                const rowStatus = row.querySelector('.status-badge').textContent.toLowerCase();
                if (status === '' || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Date filter
        document.getElementById('dateFilter').addEventListener('change', function() {
            const range = this.value;
            const rows = document.querySelectorAll('.messages-table tbody tr');
            const today = new Date();
            
            rows.forEach(row => {
                const dateStr = row.querySelector('td:nth-child(5)').textContent;
                const date = new Date(dateStr);
                
                let show = true;
                switch (range) {
                    case 'today':
                        show = date.toDateString() === today.toDateString();
                        break;
                    case 'week':
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        show = date >= weekStart;
                        break;
                    case 'month':
                        show = date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
                        break;
                }
                
                row.style.display = show ? '' : 'none';
            });
        });

        // Modal functionality
        const modal = document.getElementById('messageModal');
        const closeBtn = document.querySelector('.close');
        const messageDetails = document.getElementById('messageDetails');

        document.querySelectorAll('.view-message').forEach(button => {
            button.addEventListener('click', function() {
                const message = JSON.parse(this.dataset.message);
                const status = message.status ? 'Completed' : 'Pending';
                const statusClass = message.status ? 'status-completed' : 'status-pending';
                
                messageDetails.innerHTML = `
                    <div class="message-content">
                        <p><strong>From:</strong> ${message.user_name} (${message.user_email})</p>
                        <p><strong>Subject:</strong> ${message.subject}</p>
                        <p><strong>Date:</strong> ${new Date(message.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${status}</span></p>
                        <p><strong>Message:</strong></p>
                        <p>${message.message}</p>
                    </div>
                    ${message.reply ? `
                        <div class="reply-content">
                            <p><strong>Reply:</strong></p>
                            <p>${message.reply}</p>
                        </div>
                    ` : `
                        <form method="POST" class="reply-form">
                            <input type="hidden" name="message_id" value="${message.message_id}">
                            <textarea name="reply" placeholder="Type your reply here..." required></textarea>
                            <button type="submit" name="action" value="reply" class="btn btn-primary">Send Reply</button>
                        </form>
                    `}
                `;
                modal.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html> 