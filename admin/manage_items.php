<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../connection.php';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
$item_id_to_delete = $_GET['id'];
$conn->begin_transaction(); // Start transaction for data consistency

try {
    // Step 1: Get the path of the main thumbnail image
    $stmt_thumb = $conn->prepare("SELECT image_thumb FROM portfolio_items WHERE id = ?");
    $stmt_thumb->bind_param("i", $item_id_to_delete);
    $stmt_thumb->execute();
    $result_thumb = $stmt_thumb->get_result();
    $thumb_data = ($result_thumb->num_rows > 0) ? $result_thumb->fetch_assoc() : null;
    $stmt_thumb->close();

    // Step 2: Get the paths of all associated album images
    $stmt_album_imgs = $conn->prepare("SELECT image_path FROM portfolio_item_images WHERE portfolio_item_id = ?");
    $stmt_album_imgs->bind_param("i", $item_id_to_delete);
    $stmt_album_imgs->execute();
    $result_album_imgs = $stmt_album_imgs->get_result();
    $album_image_paths = [];
    if ($result_album_imgs->num_rows > 0) {
        while ($album_img_data = $result_album_imgs->fetch_assoc()) {
            $album_image_paths[] = $album_img_data['image_path'];
        }
    }
    $stmt_album_imgs->close();

    // Delete from child table 'portfolio_item_images'
    $stmt_delete_album_refs = $conn->prepare("DELETE FROM portfolio_item_images WHERE portfolio_item_id = ?");
    $stmt_delete_album_refs->bind_param("i", $item_id_to_delete);
    $stmt_delete_album_refs->execute();
    $stmt_delete_album_refs->close();

    // Delete from parent table 'portfolio_items'
    $stmt_delete_main = $conn->prepare("DELETE FROM portfolio_items WHERE id = ?");
    $stmt_delete_main->bind_param("i", $item_id_to_delete);
    if ($stmt_delete_main->execute()) {
        $deleted_rows = $stmt_delete_main->affected_rows;
        $stmt_delete_main->close();

        if ($deleted_rows > 0) {
            // Delete the thumbnail file
            if (!empty($thumb_data['image_thumb']) && file_exists('../' . $thumb_data['image_thumb'])) {
                unlink('../' . $thumb_data['image_thumb']);
            }
            // Delete each album image file
            foreach ($album_image_paths as $album_path) {
                if (!empty($album_path) && file_exists('../' . $album_path)) {
                    unlink('../' . $album_path);
                }
            }

            $conn->commit(); // All operations successful, commit the transaction
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Item (ID: ' . $item_id_to_delete . ') and all associated files have been deleted.'];
        } else {
            // If no rows were deleted, it means the item didn't exist. Rollback.
            throw new Exception("Item not found in the database. No records deleted.");
        }
    } else {
        throw new Exception("Failed to execute delete statement for the main item.");
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("DELETE_ITEM_ERROR (ID: $item_id_to_delete): " . $e->getMessage());
    $_SESSION['message'] = ['type' => 'error', 'text' => 'An error occurred while trying to delete the item. Please try again.'];
}

// Redirect back to the manage_items page to show the result
header('Location: manage_items.php');
exit();
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