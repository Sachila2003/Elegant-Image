<?php
// admin/manage_testimonials.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once '../connection.php';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete_testimonial' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $testimonial_id_to_delete = intval($_GET['id']);
    $stmt_delete = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $testimonial_id_to_delete);
        if ($stmt_delete->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Testimonial deleted successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting testimonial: ' . $stmt_delete->error];
        }
        $stmt_delete->close();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Database error (prepare delete).'];
    }
    header('Location: manage_testimonials.php');
    exit();
}

$testimonials = [];
$sql = "SELECT id, client_name, LEFT(testimonial_text, 100) as testimonial_excerpt, rating, is_featured, sort_order, created_at FROM testimonials ORDER BY sort_order ASC, created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Testimonials</title>
    <style>
        /* Re-use or adapt styles from manage_items.php */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; color: #333; padding-bottom: 20px;}
        .admin-container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 1000px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #343a40; margin-bottom: 20px; margin-top:0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; color: #495057; }
        .actions a { margin-right: 8px; text-decoration: none; padding: 5px 8px; border-radius: 4px; font-size: 0.85em; color:white !important; display:inline-block; margin-bottom:3px;}
        .edit-btn { background-color: #ffc107; color: #212529 !important; }
        .edit-btn:hover { background-color: #e0a800; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border-width: 1px; border-style: solid; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .add-new-button-container {text-align: right; margin-bottom: 15px;}
        .add-new-button-container a {padding: 8px 12px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;}
        .add-new-button-container a:hover {background-color: #218838;}
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; ?>
    <div class="admin-container">
        <h2>Manage Testimonials</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . htmlspecialchars($_SESSION['message']['type']) . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
            unset($_SESSION['message']);
        }
        ?>
        <div class="add-new-button-container">
            <a href="add_testimonial.php">+ Add New Testimonial</a>
        </div>
        <?php if (!empty($testimonials)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Excerpt</th>
                        <th>Rating</th>
                        <th>Featured</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testimonials as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['testimonial_excerpt']); ?>...</td>
                            <td><?php echo str_repeat('★', $item['rating']) . str_repeat('☆', 5 - $item['rating']); ?></td>
                            <td><?php echo $item['is_featured'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars($item['sort_order']); ?></td>
                            <td class="actions">
                                <a href="edit_testimonial.php?id=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                                <a href="manage_testimonials.php?action=delete_testimonial&id=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this testimonial?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No testimonials found. <a href="add_testimonial.php" style="color: #007bff; text-decoration:underline;">Add one now!</a></p>
        <?php endif; ?>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>