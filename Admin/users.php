<?php
// Show all errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Database connection
include '../db.php';

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'], $_POST['user_id'], $_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];

    $table = '';
    if ($role === 'user') {
        $table = 'users';
    } elseif ($role === 'hotel') {
        $table = 'hotel';
    } elseif ($role === 'driver') {
        $table = 'cab_drivers';
    }

    if ($table) {
        try {
            $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error preparing delete statement: " . $conn->error);
            }
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = ucfirst($role) . " deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Invalid role.";
    }

    header("Location: users.php");
    exit();
}

// Search, Filters, Sorting
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = strtoupper($_GET['order'] ?? 'DESC');
$order = $order === 'ASC' ? 'ASC' : 'DESC';

// Prepare user list
$users = [];

// Fetch users function
function fetchUsers($conn, $table, $role, $search, $role_filter, $sort, $order) {
    // Custom column mapping for hotels
    if ($table === 'hotels') {
        $columns = "id, hotel_name AS name, email, created_at";
    } else {
        $columns = "id, name, email, created_at";
    }
    $query = "SELECT $columns FROM $table";
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(name LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }

    if (!empty($role_filter) && $role_filter !== $role) {
        return []; // Skip if not matching role
    }

    if ($conditions) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    $allowed_sort = ['name', 'email', 'created_at'];
    if (!in_array($sort, $allowed_sort)) {
        $sort = 'created_at';
    }
    $query .= " ORDER BY $sort $order";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $row['role'] = $role;
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }
    return [];
}

// Fetch users from each table
$users = array_merge($users, fetchUsers($conn, 'users', 'user', $search, $role_filter, $sort, $order));
$users = array_merge($users, fetchUsers($conn, 'hotels', 'hotel', $search, $role_filter, $sort, $order));
$users = array_merge($users, fetchUsers($conn, 'cab_drivers', 'driver', $search, $role_filter, $sort, $order));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #2c3e50;
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

        .filters {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .dashboard-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            cursor: pointer;
        }

        .dashboard-table th:hover {
            background-color: #e9ecef;
        }

        .dashboard-table tr:hover {
            background-color: #f8f9fa;
        }

        .profile-photo-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-photo-small.default {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-badge.admin {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .role-badge.user {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #0d6efd;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .sort-icon {
            margin-left: 5px;
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
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="destinations.php"><i class="fas fa-map-marker-alt"></i> Destinations</a></li>
                <li><a href="../Logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>User Management</h1>
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

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>        
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="filters">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Search by name or email" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="">All Users</option>
                        <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Normal Users</option>
                        <option value="hotel" <?php echo $role_filter === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                        <option value="driver" <?php echo $role_filter === 'driver' ? 'selected' : ''; ?>>Car Drivers</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
              
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th onclick="sortTable('name')">Name <i class="fas fa-sort sort-icon"></i></th>
                            <th onclick="sortTable('email')">Email <i class="fas fa-sort sort-icon"></i></th>
                            <th onclick="sortTable('role')">Role <i class="fas fa-sort sort-icon"></i></th>
                            <th onclick="sortTable('created_at')">Joined Date <i class="fas fa-sort sort-icon"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php 
                                        switch($user['role']) {
                                            case 'user':
                                                echo 'Normal User';
                                                break;
                                            case 'hotel':
                                                echo 'Hotel';
                                                break;
                                            case 'driver':
                                                echo 'Car Driver';
                                                break;
                                            default:
                                                echo ucfirst($user['role']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this user?')">
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
    </div>

    <script>
        // Function to handle sorting
        function sortTable(column) {
            const currentUrl = new URL(window.location.href);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentOrder = currentUrl.searchParams.get('order');
            
            let newOrder = 'ASC';
            if (currentSort === column) {
                newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            }
            
            currentUrl.searchParams.set('sort', column);
            currentUrl.searchParams.set('order', newOrder);
            window.location.href = currentUrl.toString();
        }

        // Debounce function to limit how often a function can be called
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Function to handle search and filter changes
        const searchInput = document.getElementById('search');
        const roleSelect = document.getElementById('role');

        // Update filters with debounce for search input
        const debouncedUpdateFilters = debounce(function() {
            const search = searchInput.value;
            const role = roleSelect.value;
            
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', search);
            currentUrl.searchParams.set('role', role);
            
            window.location.href = currentUrl.toString();
        }, 500); // 500ms delay

        // Add event listeners
        searchInput.addEventListener('input', debouncedUpdateFilters);
        roleSelect.addEventListener('change', function() {
            const search = searchInput.value;
            const role = roleSelect.value;
            
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', search);
            currentUrl.searchParams.set('role', role);
            
            window.location.href = currentUrl.toString();
        });
    </script>
</body>
</html> 