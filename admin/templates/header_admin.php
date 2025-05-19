<?php
// admin/templates/header_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Determine the correct path to login.php relative to header_admin.php
    // If header_admin.php is in admin/templates/, login.php is likely in admin/
    header('Location: ../login.php'); // Go up one level to admin/ then to login.php
    exit();
}
$current_page_admin = basename($_SERVER['PHP_SELF']);
?>
<style>
    .admin-main-navigation {
        background-color: #343a40; /* Dark background for admin nav */
        padding: 12px 0;      /* Padding top/bottom */
        margin-bottom: 25px;
        width: 100%; /* Make it full width of its container */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 0 0 6px 6px; /* Optional: rounded bottom corners if it's at very top */
    }
    .admin-main-navigation ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: center; /* Center the navigation links */
        align-items: center;
    }
    .admin-main-navigation ul li {
        margin: 0 12px; /* Space between links */
    }
    .admin-main-navigation ul li a {
        color: #e9ecef; /* Light text color */
        text-decoration: none;
        font-weight: 500;
        padding: 8px 15px;
        border-radius: 4px;
        transition: background-color 0.2s ease, color 0.2s ease;
        font-size: 0.95em;
    }
    .admin-main-navigation ul li a:hover,
    .admin-main-navigation ul li a.active-admin-page { /* Class for the current active page */
        background-color: #007bff; /* Primary blue for active/hover */
        color: #ffffff;
    }
    .admin-main-navigation ul li a.view-site-link { /* Special style for view site link */
        background-color: #28a745; /* Green for view site */
    }
    .admin-main-navigation ul li a.view-site-link:hover {
        background-color: #218838; /* Darker green */
    }
    .admin-main-navigation ul li a.logout-link {
         background-color: #dc3545;
    }
    .admin-main-navigation ul li a.logout-link:hover {
         background-color: #c82333;
    }
</style>
<div class="admin-main-navigation">
    <ul>
        <!-- If header_admin.php is in templates, links need to reflect that or be absolute -->
        <li><a href="add_item.php" class="<?php echo ($current_page_admin == 'add_item.php' ? 'active-admin-page' : ''); ?>">➕ Add Item</a></li>
        <li><a href="manage_items.php" class="<?php echo ($current_page_admin == 'manage_items.php' ? 'active-admin-page' : ''); ?>">📋 Manage Items</a></li>
        <li><a href="../index.php#portfolio-section" target="_blank" class="view-site-link">🌐 View Portfolio</a></li>
         <li><a href="manage_users.php" class="<?php echo ($current_page_admin == 'manage_users.php' ? 'active-admin-page' : ''); ?>">👥 Manage Users</a></li>
          <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <li><a href="logout.php" class="logout-link">🚪 Logout (<?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>)</a></li>
        <?php else: ?>
            <!-- Show Login link if not logged in and not on login/register page -->
            <?php if ($current_page_admin != 'login.php' && $current_page_admin != 'register.php'): ?>
                <li><a href="login.php" class="<?php echo ($current_page_admin == 'login.php' ? 'active-admin-page' : ''); ?>">🔑 Login</a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>