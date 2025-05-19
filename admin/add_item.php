<?php
// Example: At the top of manage_items.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); // Redirect to login page
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Portfolio Item</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; /* display: flex; justify-content: center; align-items: center; min-height: 100vh; */ padding-top: 20px; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 700px; margin: 20px auto; /* Centering the container */ }
        .admin-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="file"], select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 100px; resize: vertical; }
        input[type="checkbox"] { margin-right: 8px; vertical-align: middle; width: auto; }
        .checkbox-label { font-weight: normal; display: inline; color: #555; }
        input[type="submit"] { background-color: #5cb85c; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px; }
        input[type="submit"]:hover { background-color: #4cae4c; }
        .showcase-options { border: 1px dashed #ccc; padding: 15px; margin-top: 15px; border-radius: 4px; background-color: #f9f9f9; display: none; }
        .showcase-options small { display: block; margin-top: 8px; color: #777; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; }
        .success { background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e6c6; }
        .error { background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; }
        hr { border: 0; border-top: 1px solid #eee; margin: 25px 0; }
        .navigation-buttons { margin-top: 20px; text-align: center; }
        .back-to-home-btn { display: inline-block; padding: 10px 18px; background-color: #007bff; color: white !important; text-decoration: none; border-radius: 4px; font-size: 0.9em; transition: background-color 0.3s ease; }
        .back-to-home-btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; // INCLUDE THE ADMIN NAVIGATION BAR ?>

    <div class="admin-container"> <!-- Changed class to admin-container for clarity -->
        <h2>Add New Portfolio Item</h2>

        <?php
        if (isset($_GET['status'])) {
            $message = '';
            $msg_text = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
            // $show_back_button_with_message = false; // This button is now part of header_admin.php

            if ($_GET['status'] == 'success') {
                $message = '<p class="message success">Item added successfully!</p>';
            } elseif ($_GET['status'] == 'error') {
                $message = '<p class="message error">Error adding item: ' . $msg_text . '</p>';
            } elseif ($_GET['status'] == 'upload_error') {
                $message = '<p class="message error">File upload error: ' . $msg_text . '</p>';
            }
            echo $message;

            // The "Back to Home/Portfolio" button is now part of the admin navigation in header_admin.php
            // So, we don't need to echo it here based on status.
        }
        ?>

        <form action="process_add_item.php" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories_available as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo ucfirst(str_replace('-', ' ', htmlspecialchars($cat))); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description (Optional, for lightbox):</label>
            <textarea id="description" name="description" placeholder="A short description about the photo..."></textarea>

            <label for="image_thumb">Thumbnail Image (e.g., for portfolio grid):</label>
            <input type="file" id="image_thumb" name="image_thumb" accept="image/jpeg, image/png, image/gif, image/webp" required>

            <label for="album_images">Album Images (Select multiple files for the album view):</label>
            <input type="file" id="album_images" name="album_images[]" accept="image/jpeg, image/png, image/gif, image/webp" multiple>
            <small style="display:block; margin-top:-5px; margin-bottom:10px; color:#777;">
                These images will be shown when "See Album" is clicked.
            </small>
            <hr>

            <div>
                <input type="checkbox" id="is_category_showcase" name="is_category_showcase" value="1" onchange="toggleShowcaseOptions()">
                <label for="is_category_showcase" class="checkbox-label">Is this a Special Category Showcase Block?</label>
                <small style="display: block; color: #777; margin-left: 25px;">(e.g., The "Wedding Photography" block)</small>
            </div>

            <div class="showcase-options" id="showcase_options_div">
                <label for="showcase_subtitle">Showcase Subtitle:</label>
                <input type="text" id="showcase_subtitle" name="showcase_subtitle" placeholder="Displayed below the showcase title">
                <label for="showcase_link">Showcase "See More" Link:</label>
                <input type="text" id="showcase_link" name="showcase_link" placeholder="e.g., portfolio_category.php?category=wedding">
                <small>For showcase blocks, the 'Thumbnail Image' will be its background.</small>
            </div>

            <input type="submit" value="Add Portfolio Item">
        </form>
    </div>

    <script>
        function toggleShowcaseOptions() {
            var checkbox = document.getElementById('is_category_showcase');
            var optionsDiv = document.getElementById('showcase_options_div');
            var subtitleInput = document.getElementById('showcase_subtitle');
            if (checkbox.checked) {
                optionsDiv.style.display = 'block';
                subtitleInput.required = true;
            } else {
                optionsDiv.style.display = 'none';
                subtitleInput.required = false;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleShowcaseOptions();
        });
    </script>
</body>
</html>