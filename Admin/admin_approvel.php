<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    die("Access denied");
}
require '../db.php';

$user_tables = ['hotels', 'cab_drivers'];

// Approve payment
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    // Get payment info
    $res = $conn->query("SELECT * FROM pending_payments WHERE id=$id");
    $row = $res->fetch_assoc();
    if ($row) {
        // Calculate plan duration
        $start = date('Y-m-d');
        switch ($row['plan']) {
            case 'monthly': $end = date('Y-m-d', strtotime('+1 month')); break;
            case '3months': $end = date('Y-m-d', strtotime('+3 months')); break;
            case 'halfyearly': $end = date('Y-m-d', strtotime('+6 months')); break;
            case 'yearly': $end = date('Y-m-d', strtotime('+1 year')); break;
        }
        // Insert into active_plans
        $stmt = $conn->prepare("INSERT INTO active_plans (user_id, role, plan, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $row['user_id'], $row['role'], $row['plan'], $start, $end);
        $stmt->execute();
        // Mark as approved
        $conn->query("UPDATE pending_payments SET approved=1 WHERE id=$id");
        echo "Approved!";
    }
}

// List pending payments
$res = $conn->query("SELECT * FROM pending_payments WHERE approved=0");
if (!$res) {
    die("SQL Error: " . $conn->error);
}
echo "<h2>Pending Payments</h2><table border=1><tr><th>User ID</th><th>Role</th><th>Plan</th><th>UTR</th><th>Action</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr>
        <td>{$row['user_id']}</td>
        <td>{$row['role']}</td>
        <td>{$row['plan']}</td>
        <td>{$row['utr']}</td>
        <td><a href='admin_approve.php?approve={$row['id']}'>Approve</a></td>
    </tr>";
}
echo "</table>";

// Get total users pending approval from all user tables
$total_pending_approval = 0;
foreach ($user_tables as $ut) {
    $check = $conn->query("SHOW TABLES LIKE '$ut'");
    if ($check && $check->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) FROM $ut WHERE is_approved = 0");
        if ($result) {
            $total_pending_approval += $result->fetch_row()[0];
        }
    }
}
?>

<div class="stat-card">
    <i class="fas fa-user-clock"></i>
    <h3>Users Pending Approval</h3>
    <div class="number"><?php echo $total_pending_approval; ?></div>
</div>
