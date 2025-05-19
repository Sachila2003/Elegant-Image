<?php
require_once '../connection.php'; // Database connection
$registration_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Plain password from form
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    // Basic Validations
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $errors[] = "Username or Email already exists.";
            }
            $stmt_check->close();
        } else {
            $errors[] = "Database error (check existing user): " . $conn->error;
        }
    }

    if (empty($errors)) {
        // Hash the password
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $default_role = 'admin'; // Set a default role for new registrations
        $current_datetime = date('Y-m-d H:i:s');
        $stmt_insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt_insert) {
            // Types: s=string. We have 7 placeholders, so 7 types.
            // full_name (s), username (s), email (s), password_hash (s), role (s), created_at (s), updated_at (s)
            $stmt_insert->bind_param("sssssss",
                $full_name,
                $username,
                $email,
                $password_hashed,
                $default_role,
                $current_datetime,
                $current_datetime
            );

            if ($stmt_insert->execute()) {
                $registration_message = "<p class='message success'>Registration successful! You can now <a href='login.php'>login</a>.</p>";
                // Clear form fields after successful registration by not re-populating them
                $_POST = array(); // Clear POST data to empty the form
            } else {
                $registration_message = "<p class='message error'>Registration failed: " . htmlspecialchars($stmt_insert->error) . "</p>";
            }
            $stmt_insert->close();
        } else {
            $registration_message = "<p class='message error'>Database error (prepare insert): " . htmlspecialchars($conn->error) . "</p>";
        }
    }

    // Display errors if any
    if (!empty($errors)) {
        $registration_message = "<div class='message error'><strong>Please correct the following errors:</strong><ul>";
        foreach ($errors as $err) {
            $registration_message .= "<li>" . htmlspecialchars($err) . "</li>";
        }
        $registration_message .= "</ul></div>";
    }
    if(isset($conn)) $conn->close(); // Close connection if it was opened
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .auth-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
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
        <h2>Admin Registration</h2>
        <?php if(!empty($registration_message)) echo $registration_message; ?>
        <form action="register.php" method="post" novalidate> <!-- Added novalidate to test server-side validation -->
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

            <label for="password">Password (min 6 chars):</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <input type="submit" value="Register">
        </form>
        <p class="form-link">Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>