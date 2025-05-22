<?php
// admin/edit_testimonial.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once '../connection.php';
$testimonial_id = null;
$testimonial_data = null;
$form_message_testimonial_edit = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $testimonial_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $testimonial_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $testimonial_data = $result->fetch_assoc();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Testimonial not found.'];
            header('Location: manage_testimonials.php'); exit();
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error fetching testimonial.'];
        header('Location: manage_testimonials.php'); exit();
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid testimonial ID.'];
    header('Location: manage_testimonials.php'); exit();
}

// Process form submission is handled by process_testimonial.php?action=edit
// This page is only for displaying the form with existing data.

if (isset($_SESSION['testimonial_edit_form_error'])) { // Check for errors from process_testimonial.php
    $form_message_testimonial_edit = ['type' => 'error', 'text' => $_SESSION['testimonial_edit_form_error']];
    unset($_SESSION['testimonial_edit_form_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Testimonial</title>
    <!-- Re-use styles from add_testimonial.php -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; padding-top: 20px; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 120px; resize: vertical; }
        input[type="checkbox"] { margin-right: 8px; vertical-align: middle; width: auto; }
        .checkbox-label { font-weight: normal; display: inline; color: #555; }
        input[type="submit"] { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border: 1px solid transparent;}
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .error ul { list-style-position: inside; padding-left: 0; margin:0;}
        .form-field-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; ?>
    <div class="admin-container">
        <h2>Edit Testimonial: <?php echo htmlspecialchars($testimonial_data['client_name'] ?? 'N/A'); ?></h2>
        <?php
        if ($form_message_testimonial_edit) {
            echo '<div class="message ' . htmlspecialchars($form_message_testimonial_edit['type']) . '">' . $form_message_testimonial_edit['text'] . '</div>';
        }
        ?>
        <?php if ($testimonial_data): ?>
        <form action="process_testimonial.php?action=edit" method="post">
            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial_data['id']; ?>">

            <div class="form-field-group">
                <label for="client_name">Client Name:</label>
                <input type="text" id="client_name" name="client_name" value="<?php echo htmlspecialchars($testimonial_data['client_name']); ?>" required>
            </div>
            <div class="form-field-group">
                <label for="testimonial_text">Testimonial Text:</label>
                <textarea id="testimonial_text" name="testimonial_text" rows="5" required><?php echo htmlspecialchars($testimonial_data['testimonial_text']); ?></textarea>
            </div>
            <div class="form-field-group">
                <label for="rating">Rating (1-5 Stars):</label>
                <input type="number" id="rating" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($testimonial_data['rating']); ?>" required>
            </div>
            <div class="form-field-group">
                <label for="designation">Client's Designation/Title (Optional):</label>
                <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($testimonial_data['designation']); ?>">
            </div>
            <div class="form-field-group">
                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo ($testimonial_data['is_featured'] == 1) ? 'checked' : ''; ?>>
                <label for="is_featured" class="checkbox-label">Mark as Featured Testimonial</label>
            </div>
            <div class="form-field-group">
                <label for="sort_order">Sort Order (Optional):</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($testimonial_data['sort_order']); ?>">
            </div>
            <input type="submit" value="Update Testimonial">
        </form>
         <div style="margin-top:15px; text-align:center;">
             <a href="manage_testimonials.php" style="text-decoration:none; color:#555;">« Back to Manage Testimonials</a>
        </div>
        <?php else: ?>
            <p style="text-align:center; color:red;">Could not load testimonial data.</p>
        <?php endif; ?>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>