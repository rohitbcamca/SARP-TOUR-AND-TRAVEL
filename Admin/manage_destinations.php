<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_destination':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $region = mysqli_real_escape_string($conn, $_POST['region']);
                $price = (float)$_POST['price'];
                $duration = mysqli_real_escape_string($conn, $_POST['duration']);

                // Handle image upload
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
                    $targetDir = "../uploads/destinations/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    $filename = uniqid() . "_" . basename($_FILES["image_file"]["name"]);
                    $targetFile = $targetDir . $filename;
                    move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetFile);
                    $image_url = $targetFile;
                } else {
                    $_SESSION['error'] = "Image upload failed.";
                    header('Location: manage_destinations.php');
                    exit();
                }

                $sql = "INSERT INTO destinations (name, description, region, image_url, price, duration) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssds", $name, $description, $region, $image_url, $price, $duration);
                $stmt->execute();
                $_SESSION['success'] = 'Destination added successfully.';
                header('Location: manage_destinations.php');
                exit();

            case 'update_destination':
                $id = (int)$_POST['id'];
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $region = mysqli_real_escape_string($conn, $_POST['region']);
                $price = (float)$_POST['price'];
                $duration = mysqli_real_escape_string($conn, $_POST['duration']);
                $image_url = $_POST['current_image_url'];

                // If a new image is uploaded
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
                    $targetDir = "../uploads/destinations/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    $filename = uniqid() . "_" . basename($_FILES["image_file"]["name"]);
                    $targetFile = $targetDir . $filename;
                    move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetFile);
                    $image_url = $targetFile;
                }

                $sql = "UPDATE destinations SET name=?, description=?, region=?, image_url=?, price=?, duration=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssdsi", $name, $description, $region, $image_url, $price, $duration, $id);
                $stmt->execute();
                $_SESSION['success'] = 'Destination updated successfully.';
                header('Location: manage_destinations.php');
                exit();

            case 'delete_destination':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM destinations WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['success'] = 'Destination deleted successfully.';
                header('Location: manage_destinations.php');
                exit();
        }
    }
}

// Fetch all destinations
$destinations = $conn->query("SELECT * FROM destinations ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destination Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f6fa; color: #2c3e50; }
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; padding: 20px 0; }
        .sidebar-header { padding: 0 20px; margin-bottom: 30px; }
        .sidebar-header h2 { color: white; font-size: 1.5rem; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { display: block; padding: 12px 20px; color: #ecf0f1; text-decoration: none; transition: background-color 0.3s; }
        .sidebar-menu a:hover { background-color: #34495e; }
        .sidebar-menu a.active { background-color: #3498db; }
        .sidebar-menu i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { flex: 1; padding: 20px; }
        .header { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .profile-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .profile-photo.default { width: 40px; height: 40px; border-radius: 50%; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .section { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input[type="text"], input[type="number"], input[type="url"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; resize: vertical; }
        button, .btn { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; margin-right: 5px; transition: all 0.3s ease; }
        button:hover, .btn:hover { background: #0056b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #b52a37; }
        .btn-edit { background-color: #28a745; }
        .btn-edit:hover { background-color: #218838; }
        .table-container { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow-x: auto; }
        .dashboard-table { width: 100%; border-collapse: collapse; }
        .dashboard-table th, .dashboard-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .dashboard-table th { background-color: #f8f9fa; font-weight: 600; }
        .dashboard-table tr:hover { background-color: #f8f9fa; }
        .destination-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .modal-bg { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-content { background:#fff; padding:30px; border-radius:8px; max-width:500px; width:90%; position:relative; }
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
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="manage_destinations.php" class="active"><i class="fas fa-map-marker-alt"></i> Destinations</a></li>
                <li><a href="../Logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Destination Management</h1>
                <div class="user-info">
                    <?php if (isset($_SESSION['profile_photo']) && $_SESSION['profile_photo'] !== 'default.jpg'): ?>
                        <img src="../uploads/profile_photos/<?php echo htmlspecialchars($_SESSION['profile_photo']); ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo default">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
            </div>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <div class="section">
                <h2>Add New Destination</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_destination">
                    <div class="form-group">
                        <label for="name">Destination Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="region">Region</label>
                        <input type="text" name="region" required>
                    </div>
                    <div class="form-group">
                        <label for="image_file">Image</label>
                        <input type="file" name="image_file" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration</label>
                        <input type="text" name="duration" required>
                    </div>
                    <button type="submit">Add Destination</button>
                </form>
            </div>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Region</th>
                            <th>Image</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($destination = $destinations->fetch_assoc()): ?>
                        <tr data-id="<?php echo $destination['id']; ?>">
                            <td class="dest-name"><?php echo htmlspecialchars($destination['name']); ?></td>
                            <td class="dest-desc"><?php echo htmlspecialchars($destination['description']); ?></td>
                            <td class="dest-region"><?php echo htmlspecialchars($destination['region']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($destination['image_url']); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" class="destination-image" alt="Image">
                                </a>
                            </td>
                            <td class="dest-price">₹<?php echo number_format($destination['price']); ?></td>
                            <td class="dest-duration"><?php echo htmlspecialchars($destination['duration']); ?></td>
                            <td>
                                <button class="btn btn-edit" type="button" onclick="editDestination(<?php echo $destination['id']; ?>)">Edit</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_destination">
                                    <input type="hidden" name="id" value="<?php echo $destination['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this destination?')">Delete</button>
                                </form>
                                <span class="dest-image-url" style="display:none;"><?php echo htmlspecialchars($destination['image_url']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- Edit Modal -->
<div id="editModal" class="modal-bg">
    <div class="modal-content">
        <h2>Edit Destination</h2>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_destination">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="current_image_url" id="existing_image_url">

            <div class="form-group">
                <label for="edit_name">Destination Name</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea name="description" id="edit_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="edit_region">Region</label>
                <input type="text" name="region" id="edit_region" required>
            </div>
            <div class="form-group">
                <label for="edit_image_file">New Image (optional)</label>
                <input type="file" name="image_file" id="edit_image_file" accept="image/*">
                <div id="currentImagePreview" style="margin-top:8px;"></div>
            </div>
            <div class="form-group">
                <label for="edit_price">Price (₹)</label>
                <input type="number" name="price" id="edit_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="edit_duration">Duration</label>
                <input type="text" name="duration" id="edit_duration" required>
            </div>
            <button type="submit">Update Destination</button>
            <button type="button" onclick="closeEditModal()" style="background:#ccc; color:#333; margin-left:10px;">Cancel</button>
        </form>
    </div>
</div>

<script>
    function editDestination(id) {
        var row = document.querySelector('tr[data-id="' + id + '"]');
        if (!row) return;

        const name = row.querySelector('.dest-name').textContent.trim();
        const description = row.querySelector('.dest-desc').textContent.trim();
        const region = row.querySelector('.dest-region').textContent.trim();
        const price = row.querySelector('.dest-price').textContent.replace(/[₹,]/g, '').trim();
        const duration = row.querySelector('.dest-duration').textContent.trim();
        const imageUrl = row.querySelector('.dest-image-url').textContent.trim();

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_region').value = region;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_duration').value = duration;
        document.getElementById('existing_image_url').value = imageUrl;

        const previewHTML = `<img src="${imageUrl}" alt="Current Image" style="width:120px; height:80px; object-fit:cover; border-radius:4px;">`;
        document.getElementById('currentImagePreview').innerHTML = previewHTML;

        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editForm').reset();
        document.getElementById('currentImagePreview').innerHTML = '';
    }
</script>

</body>
</html> 