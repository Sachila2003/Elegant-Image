<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: login.php'); exit(); }
// Optional: Add role check, e.g., only 'superadmin' can manage users
// if ($_SESSION['admin_role'] !== 'superadmin') { echo "Access Denied."; exit(); }

require_once '../connection.php';

// Handle Delete User Action
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id_to_delete = intval($_GET['user_id']);
    // Prevent deleting the currently logged-in user or a primary admin account (add more checks if needed)
    if (isset($_SESSION['admin_user_id']) && $_SESSION['admin_user_id'] == $user_id_to_delete) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'You cannot delete your own account.'];
    } else if ($user_id_to_delete == 1 && strtolower($_SESSION['admin_username']) !== 'superadmin_username_here') { // Example: protect user ID 1 if it's a superadmin
         $_SESSION['message'] = ['type' => 'error', 'text' => 'This user account cannot be deleted.'];
    }
    else {
        $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt_delete_user) {
            $stmt_delete_user->bind_param("i", $user_id_to_delete);
            if ($stmt_delete_user->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'User deleted successfully.'];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting user: ' . $stmt_delete_user->error];
            }
            $stmt_delete_user->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Database error (prepare delete user).'];
        }
    }
    header('Location: manage_users.php');
    exit();
}

// Fetch all users to display
$users_list = [];
$sql_users = "SELECT id, full_name, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result_users = $conn->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    while ($user_row = $result_users->fetch_assoc()) {
        $users_list[] = $user_row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <!-- Re-use styles from manage_items.php or a common admin.css -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; color: #333; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 900px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #343a40; margin-bottom: 20px; margin-top:0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; color: #495057; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .actions a { margin-right: 8px; text-decoration: none; padding: 5px 8px; border-radius: 4px; font-size: 0.85em; color:white; display:inline-block; margin-bottom:3px;}
        .edit-btn { background-color: #ffc107; color: #212529 !important; } /* Important to override generic a color */
        .edit-btn:hover { background-color: #e0a800; }
        .delete-btn { background-color: #dc3545; color: white !important; }
        .delete-btn:hover { background-color: #c82333; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border-width: 1px; border-style: solid; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; // Common Admin Navigation ?>

    <div class="admin-container">
        <h2>Manage Users</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . htmlspecialchars($_SESSION['message']['type']) . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <div style="margin-bottom: 15px; text-align: right;">
             <a href="register.php" style="padding: 8px 12px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">+ Add New User</a>
        </div>

        <?php if (!empty($users_list)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_list as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($user['created_at'])); ?></td>
                            <td class="actions">
                                <a href="edit_user.php?user_id=<?php echo $user['id']; ?>" class="edit-btn">Edit</a>
                                <?php if (!isset($_SESSION['admin_user_id']) || $_SESSION['admin_user_id'] != $user['id']): // Prevent self-delete button ?>
                                    <?php if ($user['id'] != 1): // Example: Prevent deleting user ID 1 (superadmin) directly ?>
                                    <a href="manage_users.php?action=delete_user&user_id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                                    <?php else: ?>
                                    <span style="font-size:0.8em; color:#999;">(Cannot Delete Primary Admin)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                     <span style="font-size:0.8em; color:#999;">(Your Account)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No users found. <a href="register.php" style="color: #007bff; text-decoration: underline;">Register the first admin user.</a></p>
        <?php endif; ?>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>