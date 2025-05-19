<?php
// admin/edit_item.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); } // For login system

require_once '../connection.php';

$item_id_to_edit = null;
$item_data = null; // To store existing data of the item being edited
$album_images_existing = []; // To store existing album images for this item
$message = null; // For success/error messages

// Check if ID is provided and is valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $item_id_to_edit = intval($_GET['id']);

    // Fetch existing item data from portfolio_items table
    $stmt_item = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    if ($stmt_item) {
        $stmt_item->bind_param("i", $item_id_to_edit);
        $stmt_item->execute();
        $result_item = $stmt_item->get_result();
        if ($result_item->num_rows > 0) {
            $item_data = $result_item->fetch_assoc();
        }
        $stmt_item->close();
    }

    // Fetch existing album images from portfolio_item_images table
    if ($item_data) { // Only fetch if main item exists
        $stmt_album = $conn->prepare("SELECT id, image_path, caption FROM portfolio_item_images WHERE portfolio_item_id = ? ORDER BY sort_order ASC, id ASC");
        if ($stmt_album) {
            $stmt_album->bind_param("i", $item_id_to_edit);
            $stmt_album->execute();
            $result_album = $stmt_album->get_result();
            while ($img_row = $result_album->fetch_assoc()) {
                $album_images_existing[] = $img_row;
            }
            $stmt_album->close();
        }
    }

    if (!$item_data) { // If item with given ID not found
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Portfolio item not found.'];
        header('Location: manage_items.php');
        exit();
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid item ID for editing.'];
    header('Location: manage_items.php');
    exit();
}


// --- HANDLE FORM SUBMISSION FOR UPDATE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id_hidden']) && $_POST['item_id_hidden'] == $item_id_to_edit) {
    // Retrieve form data
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']) ?? null;
    $is_category_showcase = isset($_POST['is_category_showcase']) ? 1 : 0;
    $showcase_subtitle = ($is_category_showcase && isset($_POST['showcase_subtitle'])) ? trim($_POST['showcase_subtitle']) : null;
    $showcase_link = ($is_category_showcase && isset($_POST['showcase_link'])) ? trim($_POST['showcase_link']) : null;

    $existing_image_thumb = $_POST['existing_image_thumb'];
    $new_thumb_path_for_db = $existing_image_thumb; // Assume old thumb initially
    $upload_errors = [];

    // Simplified handle_single_upload function (same as in process_add_item.php)
    function handle_single_upload_edit($file_input_name, $target_dir_relative, &$db_path_variable, &$errors_array, $is_optional = false) {
        // ... (Copy the full handle_single_upload function from process_add_item.php here)
        // ... (Make sure it correctly handles optional uploads: if $is_optional is true and no file, it should return true without error)
         if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            // ... (file validation logic: type, size) ...
            // ... (filename generation logic) ...
            // ... (move_uploaded_file logic) ...
            // If successful: $db_path_variable = ... ; return true;
            // Else: $errors_array[] = ... ; return false;
            // For brevity, the full function is omitted. Refer to process_add_item.php's function
            // For this edit page, the thumbnail upload is optional.
            $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
            $original_file_name = $_FILES[$file_input_name]['name'];
            // ... (rest of your validation and move logic)
            // Example path (ensure this logic is robust as in process_add_item.php)
            $safe_filename_base = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", pathinfo($original_file_name, PATHINFO_FILENAME));
            $safe_filename_base = substr($safe_filename_base, 0, 100);
            $new_file_name = time() . '_' . uniqid('', true) . '_' . $safe_filename_base . '.' .strtolower(end(explode('.', $original_file_name)));
            $destination_path_on_server = $target_dir_relative . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $destination_path_on_server)) {
                $db_path_variable = str_replace('../', '', $target_dir_relative) . $new_file_name; // Store path relative to root
                return true;
            } else { $errors_array[] = "Failed to move new thumbnail."; return false; }

        } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return true; // No new file uploaded, which is fine for an optional field
        } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
            $errors_array[] = "Error uploading new thumbnail: " . $_FILES[$file_input_name]['error']; return false;
        }
        return true; // Default if no file input was even sent for this name (e.g. form error)
    }


    $conn->begin_transaction();
    try {
        // Handle Thumbnail Update
        if (isset($_FILES['image_thumb_new']) && $_FILES['image_thumb_new']['error'] == UPLOAD_ERR_OK) {
            $temp_new_thumb_path = null;
            if (handle_single_upload_edit('image_thumb_new', '../uploads/thumbnails/', $temp_new_thumb_path, $upload_errors, true)) {
                if ($temp_new_thumb_path) { // If a new thumb was actually uploaded and moved
                    // Delete old thumbnail file from server
                    if ($existing_image_thumb && file_exists('../' . $existing_image_thumb)) {
                        unlink('../' . $existing_image_thumb);
                    }
                    $new_thumb_path_for_db = $temp_new_thumb_path; // Set to the new path
                }
            } else { // handle_single_upload_edit returned false, meaning an error with the new file
                 throw new Exception(implode("<br>", $upload_errors));
            }
        }
        // If $new_thumb_path_for_db is still the $existing_image_thumb, it means no new thumb was uploaded or there was an error handled by exception.

        // Update portfolio_items table
        $sql_update_main = "UPDATE portfolio_items SET title=?, category=?, description=?, image_thumb=?, 
                            is_category_showcase=?, showcase_subtitle=?, showcase_link=? WHERE id=?";
        $stmt_update_main = $conn->prepare($sql_update_main);
        if ($stmt_update_main === false) throw new Exception("DB Main Update Prepare Error: " . $conn->error);

        $stmt_update_main->bind_param("ssssissi",
            $title, $category, $description, $new_thumb_path_for_db,
            $is_category_showcase, $showcase_subtitle, $showcase_link,
            $item_id_to_edit
        );
        if (!$stmt_update_main->execute()) throw new Exception("DB Main Update Execute Error: " . $stmt_update_main->error);
        $stmt_update_main->close();

        // Handle Deletion of selected existing album images
        if (isset($_POST['delete_album_images']) && is_array($_POST['delete_album_images'])) {
            $delete_ids_placeholders = implode(',', array_fill(0, count($_POST['delete_album_images']), '?'));
            $types = str_repeat('i', count($_POST['delete_album_images']));

            // First, get paths of images to be deleted from server
            $stmt_get_paths = $conn->prepare("SELECT image_path FROM portfolio_item_images WHERE id IN ($delete_ids_placeholders)");
            if ($stmt_get_paths) {
                $stmt_get_paths->bind_param($types, ...$_POST['delete_album_images']);
                $stmt_get_paths->execute();
                $result_paths = $stmt_get_paths->get_result();
                while ($img_to_del = $result_paths->fetch_assoc()) {
                    if (file_exists('../' . $img_to_del['image_path'])) {
                        unlink('../' . $img_to_del['image_path']);
                    }
                }
                $stmt_get_paths->close();
            }

            // Then, delete from DB
            $stmt_delete_selected_album = $conn->prepare("DELETE FROM portfolio_item_images WHERE id IN ($delete_ids_placeholders)");
            if ($stmt_delete_selected_album) {
                $stmt_delete_selected_album->bind_param($types, ...$_POST['delete_album_images']);
                if (!$stmt_delete_selected_album->execute()) throw new Exception("Error deleting selected album images: " . $stmt_delete_selected_album->error);
                $stmt_delete_selected_album->close();
            } else { throw new Exception("Prepare failed for deleting selected album images: " . $conn->error); }
        }

        // Handle Upload of new album images (similar to process_add_item.php's album image loop)
        if (isset($_FILES['album_images_new']) && count($_FILES['album_images_new']['name']) > 0 && $_FILES['album_images_new']['name'][0] != "") {
            $upload_dir_album_relative = "../uploads/album_images/";
            // ... (Loop through $_FILES['album_images_new'], validate, move, and INSERT into portfolio_item_images) ...
            // ... (This logic will be similar to the one in process_add_item.php for album_images) ...
            // Remember to use $item_id_to_edit as portfolio_item_id
        }

        $conn->commit();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Portfolio item updated successfully!'];
        header('Location: manage_items.php'); // Redirect to manage page
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        // Store error message in session to display on edit page after redirect
        $_SESSION['edit_form_error'] = 'Error updating item: ' . $e->getMessage();
        error_log("Update Item Error: " . $e->getMessage());
        header('Location: edit_item.php?id=' . $item_id_to_edit); // Redirect back to edit page
        exit();
    }
}
// If there was an error message from POST processing, display it
if (isset($_SESSION['edit_form_error'])) {
    $message = ['type' => 'error', 'text' => $_SESSION['edit_form_error']];
    unset($_SESSION['edit_form_error']);
}

$categories_available = ['wedding', 'portrait', 'pre-shoot', 'baby', 'event', 'landscape', 'fashion', 'product'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Portfolio Item</title>
    <!-- Re-using styles from add_item.php or a common admin CSS file -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; padding-top: 20px; padding-bottom: 20px; }
        .admin-container { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 700px; margin: 20px auto; }
        .admin-container h2 { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="file"], select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 100px; resize: vertical; }
        input[type="checkbox"] { margin-right: 8px; vertical-align: middle; width: auto; }
        .checkbox-label { font-weight: normal; display: inline; color: #555; }
        input[type="submit"] { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 20px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .showcase-options { border: 1px dashed #ccc; padding: 15px; margin-top: 15px; border-radius: 4px; background-color: #f9f9f9; display: none; }
        .showcase-options small { display: block; margin-top: 8px; color: #777; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95em; border: 1px solid transparent; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        hr { border: 0; border-top: 1px solid #eee; margin: 25px 0; }

        .current-image-preview { margin-bottom: 10px; }
        .current-image-preview img { max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px; }
        .existing-album-images-container { margin-bottom: 15px; }
        .existing-album-image { display: inline-block; margin-right: 10px; margin-bottom: 10px; text-align: center; vertical-align: top;}
        .existing-album-image img { width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; display: block; margin-bottom: 5px; }
        .delete-checkbox-label { font-size: 0.85em; color: #dc3545; }
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; // Admin Navigation Bar ?>

    <div class="admin-container">
        <h2>Edit Portfolio Item: <?php echo htmlspecialchars($item_data['title'] ?? 'Item Not Found'); ?></h2>

        <?php
        if (isset($message)) { // Display error from POST processing if redirected back
            echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
        }
        ?>

        <?php if ($item_data): // Only show form if item data was successfully fetched ?>
        <form action="edit_item.php?id=<?php echo $item_id_to_edit; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="item_id_hidden" value="<?php echo $item_id_to_edit; ?>">

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($item_data['title']); ?>" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <?php foreach ($categories_available as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($item_data['category'] == $cat) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('-', ' ', htmlspecialchars($cat))); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description (Optional):</label>
            <textarea id="description" name="description" placeholder="A short description..."><?php echo htmlspecialchars($item_data['description']); ?></textarea>

            <label>Current Thumbnail:</label>
            <div class="current-image-preview">
                <?php if (!empty($item_data['image_thumb'])): ?>
                    <img src="../<?php echo htmlspecialchars($item_data['image_thumb']); ?>" alt="Current Thumbnail">
                <?php else: ?>
                    <p>No thumbnail uploaded.</p>
                <?php endif; ?>
            </div>
            <label for="image_thumb_new">Upload New Thumbnail (optional, replaces current):</label>
            <input type="file" id="image_thumb_new" name="image_thumb_new" accept="image/jpeg, image/png, image/gif, image/webp">
            <input type="hidden" name="existing_image_thumb" value="<?php echo htmlspecialchars($item_data['image_thumb']); ?>">

            <hr>

            <h4>Album Images:</h4>
            <div class="existing-album-images-container">
                <?php if (!empty($album_images_existing)): ?>
                    <p>Current Album Images (select checkbox to delete an image):</p>
                    <?php foreach ($album_images_existing as $img): ?>
                        <div class="existing-album-image">
                            <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" alt="Album image <?php echo htmlspecialchars($img['id']); ?>">
                            <input type="checkbox" name="delete_album_images[]" value="<?php echo $img['id']; ?>" id="delete_img_<?php echo $img['id']; ?>">
                            <label for="delete_img_<?php echo $img['id']; ?>" class="delete-checkbox-label">Delete</label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No existing album images for this item.</p>
                <?php endif; ?>
            </div>
            <label for="album_images_new">Add New Album Images (optional):</label>
            <input type="file" id="album_images_new" name="album_images_new[]" multiple accept="image/jpeg, image/png, image/gif, image/webp">

            <hr>

            <div>
                <input type="checkbox" id="is_category_showcase" name="is_category_showcase" value="1" <?php echo ($item_data['is_category_showcase'] == 1) ? 'checked' : ''; ?> onchange="toggleShowcaseOptions()">
                <label for="is_category_showcase" class="checkbox-label">Is this a Special Category Showcase Block?</label>
            </div>
            <div class="showcase-options" id="showcase_options_div">
                <label for="showcase_subtitle">Showcase Subtitle:</label>
                <input type="text" id="showcase_subtitle" name="showcase_subtitle" value="<?php echo htmlspecialchars($item_data['showcase_subtitle'] ?? ''); ?>" placeholder="Displayed below the showcase title">
                <label for="showcase_link">Showcase "See More" Link:</label>
                <input type="text" id="showcase_link" name="showcase_link" value="<?php echo htmlspecialchars($item_data['showcase_link'] ?? ''); ?>" placeholder="e.g., category_page.php?category=wedding">
            </div>

            <input type="submit" value="Update Portfolio Item">
        </form>

        <div class="navigation-buttons" style="margin-top: 20px; text-align:center;">
            <a href="manage_items.php" class="back-to-home-btn" style="background-color: #6c757d;">« Back to Manage Items</a>
        </div>
        <?php else: ?>
            <p style="text-align:center; color:red;">Could not load item data for editing.</p>
            <div class="navigation-buttons" style="margin-top: 20px; text-align:center;">
                 <a href="manage_items.php" class="back-to-home-btn" style="background-color: #6c757d;">« Back to Manage Items</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleShowcaseOptions() {
            var checkbox = document.getElementById('is_category_showcase');
            var optionsDiv = document.getElementById('showcase_options_div');
            var subtitleInput = document.getElementById('showcase_subtitle');
            if (checkbox && optionsDiv && subtitleInput) { // Ensure elements exist
                if (checkbox.checked) {
                    optionsDiv.style.display = 'block';
                    subtitleInput.required = true; // Consider if showcase link is also required
                } else {
                    optionsDiv.style.display = 'none';
                    subtitleInput.required = false;
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleShowcaseOptions();
        });
    </script>
</body>
</html>