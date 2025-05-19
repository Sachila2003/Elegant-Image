<?php
// admin/manage_items.php
if (session_status() === PHP_SESSION_NONE) { // Start session if not already started for messages
    session_start();
}
require_once '../connection.php';

// Handle Delete Action (this part remains the same)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
}

$portfolio_items = [];
$sql = "SELECT id, title, category, image_thumb, uploaded_at FROM portfolio_items ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $portfolio_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Portfolio Items</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; color: #333; padding-bottom: 20px;}
        .admin-container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 900px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #343a40; margin-bottom: 20px; margin-top:0;}
        /* Removed .admin-nav-links as it's replaced by header_admin.php */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; vertical-align: middle;}
        th { background-color: #e9ecef; color: #495057; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .actions a { margin-right: 8px; text-decoration: none; padding: 5px 8px; border-radius: 4px; font-size: 0.85em; color:white; display:inline-block; margin-bottom:3px;}
        .edit-btn { background-color: #ffc107; color: #212529; }
        .edit-btn:hover { background-color: #e0a800; }
        .delete-btn { background-color: #dc3545; } /* color: white; is inherited or set in a:link */
        .delete-btn:hover { background-color: #c82333; }
        .item-thumbnail { width: 70px; height: 50px; object-fit: cover; border-radius: 3px; display:block; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border-width: 1px; border-style: solid; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; // INCLUDE THE ADMIN NAVIGATION BAR ?>

    <div class="admin-container"> <!-- Changed class to admin-container -->
        <h2>Manage Portfolio Items</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . htmlspecialchars($_SESSION['message']['type']) . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <?php if (!empty($portfolio_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portfolio_items as $item): ?>
                        <tr>
                            <td><img src="../<?php echo htmlspecialchars($item['image_thumb']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="item-thumbnail"></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($item['category'])); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($item['uploaded_at'])); ?></td>
                            <td class="actions">
                                <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                                <a href="manage_items.php?action=delete&id=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item and all its images? This action cannot be undone.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No portfolio items found. <a href="add_item.php" style="color: #007bff; text-decoration: underline;">Add some now!</a></p>
        <?php endif; ?>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>