<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once '../connection.php';
$form_message_testimonial = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Testimonial</title>
    <!-- Re-use styles from other admin pages or a common admin.css -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; padding-top: 20px; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 600px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 120px; resize: vertical; }
        input[type="checkbox"] { margin-right: 8px; vertical-align: middle; width: auto; }
        .checkbox-label { font-weight: normal; display: inline; color: #555; }
        input[type="submit"] { background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px; }
        input[type="submit"]:hover { background-color: #218838; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border: 1px solid transparent; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .error ul { list-style-position: inside; padding-left: 0; margin:0;}
        .form-field-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; ?>
    <div class="admin-container">
        <h2>Add New Testimonial</h2>
        <?php
        if (isset($_SESSION['testimonial_form_message'])) {
            echo '<div class="message ' . htmlspecialchars($_SESSION['testimonial_form_message']['type']) . '">' . $_SESSION['testimonial_form_message']['text'] . '</div>';
            unset($_SESSION['testimonial_form_message']);
        }
        ?>
        <form action="process_testimonial.php?action=add" method="post">
            <div class="form-field-group">
                <label for="client_name">Client Name:</label>
                <input type="text" id="client_name" name="client_name" required>
            </div>
            <div class="form-field-group">
                <label for="testimonial_text">Testimonial Text:</label>
                <textarea id="testimonial_text" name="testimonial_text" rows="5" required></textarea>
            </div>
            <div class="form-field-group">
                <label for="rating">Rating (1-5 Stars):</label>
                <input type="number" id="rating" name="rating" min="1" max="5" value="5" required>
            </div>
            <div class="form-field-group">
                <label for="designation">Client's Designation/Title (Optional):</label>
                <input type="text" id="designation" name="designation">
            </div>
            <div class="form-field-group">
                <input type="checkbox" id="is_featured" name="is_featured" value="1">
                <label for="is_featured" class="checkbox-label">Mark as Featured Testimonial</label>
            </div>
            <div class="form-field-group">
                <label for="sort_order">Sort Order (Optional, e.g., 0, 1, 2... lower numbers appear first):</label>
                <input type="number" id="sort_order" name="sort_order" value="0">
            </div>
            <input type="submit" value="Add Testimonial">
        </form>
    </div>
</body>
</html>