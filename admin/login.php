<?php
// admin/login.php
session_start(); // Start the session at the very beginning of the script
require_once '../connection.php'; // Database connection
$login_message = '';

// If the user is already logged in, redirect them away from the login page
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: manage_items.php'); // Or your admin dashboard page
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password']; // Plain password from form
    $errors = [];

    // Basic Validations
    if (empty($username_or_email)) {
        $errors[] = "Username or Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Determine if the input is an email or a username
        $field_type = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Prepare SQL statement to fetch user by username or email
        $sql = "SELECT id, username, password_hash, full_name, role FROM users WHERE $field_type = ?";
        $stmt_login = $conn->prepare($sql);

        if ($stmt_login) {
            $stmt_login->bind_param("s", $username_or_email);
            $stmt_login->execute();
            $result_login = $stmt_login->get_result();

            if ($result_login->num_rows == 1) {
                $user = $result_login->fetch_assoc();

                // Verify the password against the stored hash
                if (password_verify($password, $user['password_hash'])) {
                    // Password is correct, authentication successful

                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Store user information in session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name']; // Store full name too
                    $_SESSION['admin_role'] = $user['role'];          // Store role if you use it

                    // Redirect to the main admin page (e.g., manage_items.php or a dashboard)
                    header('Location: manage_items.php');
                    exit();
                } else {
                    // Password is not correct
                    $login_message = "<p class='message error'>Invalid username/email or password.</p>";
                }
            } else {
                // No user found with that username/email
                $login_message = "<p class='message error'>Invalid username/email or password.</p>";
            }
            $stmt_login->close();
        } else {
            // Database query preparation error
            $login_message = "<p class='message error'>Database error. Please try again later.</p>";
            error_log("Login prepare error: " . $conn->error); // Log the actual error for admin
        }
    } else {
        // Form validation errors
        $login_message = "<div class='message error'><ul>";
        foreach ($errors as $err) {
            $login_message .= "<li>" . htmlspecialchars($err) . "</li>";
        }
        $login_message .= "</ul></div>";
    }
    if(isset($conn)) $conn->close(); // Close connection if it was opened
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Re-use styles from register.php or a common admin_auth.css -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .auth-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; /* Slightly smaller than register */ }
        .auth-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 25px; }
        label { display: block; margin-top: 12px; margin-bottom: 5px; color: #555; font-weight: bold; font-size: 0.9em; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; width: 100%; margin-top: 10px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 0.9em; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .error ul { list-style-position: inside; padding-left: 0; margin:0;}
        .form-link { text-align: center; margin-top: 15px; font-size: 0.9em; }
        .form-link a { color: #007bff; text-decoration: none; }
        .form-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Admin Login</h2>
        <?php echo $login_message; // Display login errors or messages here ?>
        <form action="login.php" method="post">
            <label for="username_or_email">Username or Email:</label>
            <input type="text" id="username_or_email" name="username_or_email" value="<?php echo isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : ''; ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
        <p class="form-link">Don't have an account? <a href="register.php">Register here</a></p>
        <!-- Optional: Add a "Forgot Password?" link later -->
    </div>
</body>
</html> 