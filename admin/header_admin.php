<?php
// admin/templates/header_admin.php
// session_start(); // If using sessions for login
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php'); // Redirect to login if not logged in
//     exit();
// }
$current_page = basename($_SERVER['PHP_SELF']); // Get current page filename
?>
<style>
    /* Styles for this admin navigation bar */
    .admin-main-nav {
        background-color: #343a40; /* Dark background */
        padding: 10px 20px;
        margin-bottom: 25px;
        border-radius: 0 0 5px 5px; /* Rounded bottom corners */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .admin-main-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: center; /* Center the links */
    }
    .admin-main-nav ul li {
        margin: 0 15px;
    }
    .admin-main-nav ul li a {
        color: #f8f9fa; /* Light text */
        text-decoration: none;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .admin-main-nav ul li a:hover,
    .admin-main-nav ul li a.active-admin-link {
        background-color: #007bff; /* Highlight color */
        color: #fff;
    }
    .admin-main-nav ul li a.view-site-link {
        background-color: #6c757d; /* Different color for view site */
    }
    .admin-main-nav ul li a.view-site-link:hover {
        background-color: #5a6268;
    }
</style>
<div class="admin-main-nav">
    <ul>
        <li><a href="add_item.php" class="<?php echo ($current_page == 'add_item.php' ? 'active-admin-link' : ''); ?>">➕ Add Item</a></li>
        <li><a href="manage_items.php" class="<?php echo ($current_page == 'manage_items.php' ? 'active-admin-link' : ''); ?>">📋 Manage Items</a></li>
        <!-- Add more admin links here as your panel grows -->
        <li><a href="../index.php" target="_blank" class="view-site-link">🌐 View Site</a></li>
        <?php // if(isset($_SESSION['admin_logged_in'])): // For logout later ?>
            <!-- <li><a href="logout.php">Logout</a></li> -->
        <?php // endif; ?>
    </ul>
</div>