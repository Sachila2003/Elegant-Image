<?php
// admin/logout.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the MAIN SITE'S HOME PAGE instead of the admin login page.
// Assuming index.php is in the parent directory of the 'admin' folder.
header("Location: ../index.php"); // CHANGED THIS LINE
exit();
?>