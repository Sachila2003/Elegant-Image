<?php
// admin/edit_user.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
// if ($_SESSION['admin_role'] !== 'superadmin' && $_SESSION['admin_user_id'] != $_GET['user_id']) { echo "Access Denied."; exit(); } // Allow editing self or if superadmin

require_once '../connection.php';
$user_id_to_edit = null;
$user_data = null;
$form_message_user = null;

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id_to_edit = intval($_GET['user_id']);
    $stmt_user = $conn->prepare("SELECT id, full_name, username, email, role FROM users WHERE id = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $user_id_to_edit);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'User not found.'];
            header('Location: manage_users.php'); exit();
        }
        $stmt_user->close();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error fetching user details.'];
        header('Location: manage_users.php'); exit();
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid user ID for editing.'];
    header('Location: manage_users.php'); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id_hidden']) && $_POST['user_id_hidden'] == $user_id_to_edit) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']); // Assuming you have a role dropdown/input
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    $errors_user = [];

    if (empty($full_name)) $errors_user[] = "Full name is required.";
    if (empty($username)) $errors_user[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors_user[] = "Valid email is required.";
    // Add more validation for role if needed

    // Password update validation (only if new password is provided)
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) $errors_user[] = "New password must be at least 6 characters.";
        if ($new_password !== $confirm_new_password) $errors_user[] = "New passwords do not match.";
    }

    // Check for username/email conflicts (excluding the current user)
    if (empty($errors_user)) {
        $stmt_conflict = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        if ($stmt_conflict) {
            $stmt_conflict->bind_param("ssi", $username, $email, $user_id_to_edit);
            $stmt_conflict->execute();
            $result_conflict = $stmt_conflict->get_result();
            if ($result_conflict->num_rows > 0) {
                $errors_user[] = "Username or Email already taken by another user.";
            }
            $stmt_conflict->close();
        } else { $errors_user[] = "DB error (conflict check)."; }
    }

    if (empty($errors_user)) {
        $conn->begin_transaction();
        try {
            $sql_update_user = "UPDATE users SET full_name = ?, username = ?, email = ?, role = ?";
            $types_update = "ssss";
            $params_update = [$full_name, $username, $email, $role];

            if (!empty($new_password)) {
                $password_hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_user .= ", password_hash = ?";
                $types_update .= "s";
                $params_update[] = $password_hashed_new;
            }
            $sql_update_user .= " WHERE id = ?";
            $types_update .= "i";
            $params_update[] = $user_id_to_edit;

            $stmt_update = $conn->prepare($sql_update_user);
            if ($stmt_update === false) throw new Exception("DB User Update Prepare Error: " . $conn->error);

            $stmt_update->bind_param($types_update, ...$params_update);
            if (!$stmt_update->execute()) throw new Exception("DB User Update Execute Error: " . $stmt_update->error);
            $stmt_update->close();

            $conn->commit();
            $_SESSION['message'] = ['type' => 'success', 'text' => 'User details updated successfully.'];
            header('Location: manage_users.php');
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['edit_user_form_error'] = 'Error updating user: ' . $e->getMessage();
            error_log("Update User Error (ID: $user_id_to_edit): " . $e->getMessage());
            header('Location: edit_user.php?user_id=' . $user_id_to_edit);
            exit();
        }
    } else {
        $form_message_user = ['type' => 'error', 'text' => implode("<br>", $errors_user)];
    }
}

if (isset($_SESSION['edit_user_form_error'])) {
    $form_message_user = ['type' => 'error', 'text' => $_SESSION['edit_user_form_error']];
    unset($_SESSION['edit_user_form_error']);
}
$available_roles = ['admin', 'editor']; // Define available roles
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit User</title>
    <style>
        /* Copy styles from manage_items.php or add_item.php and adapt */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; padding-top: 20px; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 550px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border: 1px solid transparent;}
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .password-note { font-size: 0.85em; color: #777; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; ?>

    <div class="admin-container">
        <h2>Edit User: <?php echo htmlspecialchars($user_data['username'] ?? 'N/A'); ?></h2>

        <?php
        if ($form_message_user) {
            echo '<div class="message ' . htmlspecialchars($form_message_user['type']) . '">' . htmlspecialchars($form_message_user['text']) . '</div>';
        }
        ?>

        <?php if ($user_data): ?>
        <form action="edit_user.php?user_id=<?php echo $user_id_to_edit; ?>" method="post">
            <input type="hidden" name="user_id_hidden" value="<?php echo $user_id_to_edit; ?>">

            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <?php foreach($available_roles as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo ($user_data['role'] == $r) ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                <?php endforeach; ?>
            </select>

            <hr style="margin: 20px 0;">
            <p class="password-note">Leave password fields blank if you don't want to change the password.</p>
            <label for="new_password">New Password (min 6 chars):</label>
            <input type="password" id="new_password" name="new_password">

            <label for="confirm_new_password">Confirm New Password:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password">

            <input type="submit" value="Update User">
        </form>
        <div style="margin-top:15px; text-align:center;">
             <a href="manage_users.php" style="text-decoration:none; color:#555;">« Back to Manage Users</a>
        </div>
        <?php else: ?>
            <p style="text-align:center; color:red;">Could not load user data for editing.</p>
             <div style="margin-top:15px; text-align:center;">
                 <a href="manage_users.php" style="text-decoration:none; color:#555;">« Back to Manage Users</a>
            </div>
        <?php endif; ?>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>