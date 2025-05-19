<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Uncomment and adapt for your login system
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: login.php');
//     exit();
// }

require_once '../connection.php'; // Ensure this path is correct

$item_id_to_edit = null;
$item_data = null;
$album_images_existing = [];
$form_message = null; // For displaying messages on the form after POST or for errors

// --- Reusable File Upload Function (Adapted for Edit Page - Thumbnail is optional new upload) ---
function handle_single_upload_edit(string $file_input_name, string $target_dir_relative_to_script, &$db_path_variable, array &$errors_array, bool $is_new_upload_truly_optional = true): bool {
    $db_path_variable = null; // Initialize: no new path unless successful upload

    if (!isset($_FILES[$file_input_name])) {
        if ($is_new_upload_truly_optional) return true; // No file input element sent, fine if optional
        $errors_array[] = "File input '{$file_input_name}' not found in form submission."; return false;
    }

    if ($_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
        return $is_new_upload_truly_optional; // No file selected, fine if optional
    }

    if ($_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
        $original_file_name = basename($_FILES[$file_input_name]['name']); // Use basename for security
        $file_size = $_FILES[$file_input_name]['size'];

        if (empty($original_file_name)) {
            $errors_array[] = "Uploaded file for '{$file_input_name}' has an empty name."; return false;
        }
        $file_ext = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_extensions)) {
            $errors_array[] = "Invalid file type for '{$file_input_name}': .{$file_ext}."; return false;
        }
        if ($file_size == 0) { // 0 byte file is an issue
             $errors_array[] = "File '{$file_input_name}' ('{$original_file_name}') is empty."; return false;
        }
        if ($file_size > $max_file_size) {
            $errors_array[] = "File '{$file_input_name}' ('{$original_file_name}') is too large (Max: " . ($max_file_size / 1024 / 1024) . "MB)."; return false;
        }

        $safe_filename_base = preg_replace("/[^a-zA-Z0-9_\-]/", "_", pathinfo($original_file_name, PATHINFO_FILENAME)); // Allow dots in base for versioning etc if needed, but usually not.
        $safe_filename_base = substr($safe_filename_base, 0, 100);
        $new_file_name = time() . '_' . uniqid('', true) . '_' . $safe_filename_base . '.' . $file_ext;
        $destination_path_on_server = rtrim($target_dir_relative_to_script, '/') . '/' . $new_file_name;

        if (!is_dir(dirname($destination_path_on_server))) {
            if (!mkdir(dirname($destination_path_on_server), 0775, true)) {
                 $errors_array[] = "Failed to create upload directory: " . dirname($destination_path_on_server); return false;
            }
        }

        if (move_uploaded_file($file_tmp_path, $destination_path_on_server)) {
            $db_path_variable = str_replace('../', '', $target_dir_relative_to_script) . $new_file_name;
            return true;
        } else {
            $errors_array[] = "Failed to move uploaded file '{$original_file_name}'. Check permissions. Error: " . $_FILES[$file_input_name]['error'];
            return false;
        }
    } else { // Other upload errors
        $errors_array[] = "Upload error for '{$file_input_name}'. Code: " . $_FILES[$file_input_name]['error'];
        return false;
    }
}
// --- End File Upload Function ---


// --- Handle GET Request: Fetch item data for editing ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $item_id_to_edit = intval($_GET['id']);
    $stmt_item = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    if ($stmt_item) {
        $stmt_item->bind_param("i", $item_id_to_edit);
        $stmt_item->execute();
        $result_item = $stmt_item->get_result();
        if ($result_item->num_rows > 0) {
            $item_data = $result_item->fetch_assoc();
            // Fetch existing album images for this item
            $stmt_album = $conn->prepare("SELECT id, image_path, caption FROM portfolio_item_images WHERE portfolio_item_id = ? ORDER BY sort_order ASC, id ASC");
            if ($stmt_album) {
                $stmt_album->bind_param("i", $item_id_to_edit);
                $stmt_album->execute();
                $result_album = $stmt_album->get_result();
                while ($img_row = $result_album->fetch_assoc()) { $album_images_existing[] = $img_row; }
                $stmt_album->close();
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Portfolio item not found.'];
            header('Location: manage_items.php'); exit();
        }
        $stmt_item->close();
    } else {
        // Error preparing statement to fetch item
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error fetching item details.'];
        header('Location: manage_items.php'); exit();
    }
}
// --- End GET Request Handling ---


// --- Handle POST Request: Process form submission for update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id_hidden']) && is_numeric($_POST['item_id_hidden'])) {
    $item_id_to_edit = intval($_POST['item_id_hidden']); // Get ID from hidden field
    // Re-fetch item data to ensure we're working with the correct item if ID was manipulated,
    // or rely on the $item_data fetched via GET if POST ID matches GET ID. For simplicity:
    if (!$item_data || $item_data['id'] != $item_id_to_edit) {
         // If item_data wasn't fetched or ID mismatch, fetch again or error out
        // This is a safety check, normally $item_data would be set from GET
        $stmt_check = $conn->prepare("SELECT image_thumb FROM portfolio_items WHERE id = ?");
        $stmt_check->bind_param("i", $item_id_to_edit); $stmt_check->execute(); $res_check = $stmt_check->get_result();
        if($res_check->num_rows == 0) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Item to update not found.'];
            header('Location: manage_items.php'); exit();
        }
        $item_data_for_post = $res_check->fetch_assoc(); // We need at least existing_image_thumb
        $existing_image_thumb_from_form = $item_data_for_post['image_thumb']; // Or pass this in hidden field
        $stmt_check->close();
    } else {
        $existing_image_thumb_from_form = $item_data['image_thumb'];
    }


    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']) ?? null;
    $is_category_showcase = isset($_POST['is_category_showcase']) ? 1 : 0;
    $showcase_subtitle = ($is_category_showcase && isset($_POST['showcase_subtitle'])) ? trim($_POST['showcase_subtitle']) : null;
    $showcase_link = ($is_category_showcase && isset($_POST['showcase_link'])) ? trim($_POST['showcase_link']) : null;

    $new_thumb_path_for_db = $existing_image_thumb_from_form;
    $upload_errors = [];
    $newly_uploaded_thumb_server_path = null; // Track path of newly uploaded thumb for cleanup on error
    $newly_uploaded_album_server_paths = []; // Track paths of newly uploaded album images

    $conn->begin_transaction();
    try {
        // 1. Handle Thumbnail Update (if a new one is uploaded)
        if (isset($_FILES['image_thumb_new']) && $_FILES['image_thumb_new']['error'] == UPLOAD_ERR_OK) {
            if (handle_single_upload_edit('image_thumb_new', '../uploads/thumbnails/', $newly_uploaded_thumb_server_path, $upload_errors, true)) {
                if ($newly_uploaded_thumb_server_path) { // A new file was successfully uploaded and moved
                    // Delete old thumbnail file from server if it's different and exists
                    if ($existing_image_thumb_from_form && $existing_image_thumb_from_form != $newly_uploaded_thumb_server_path && file_exists('../' . $existing_image_thumb_from_form)) {
                        if(!unlink('../' . $existing_image_thumb_from_form)) {
                            // Log error, but don't necessarily stop the whole process
                            error_log("Could not delete old thumbnail: ../" . $existing_image_thumb_from_form);
                        }
                    }
                    $new_thumb_path_for_db = $newly_uploaded_thumb_server_path;
                }
            } else { // Error with new thumbnail upload
                throw new Exception("Error processing new thumbnail: " . implode("; ", $upload_errors));
            }
        }

        // 2. Update main portfolio_items table
        $sql_update_main = "UPDATE portfolio_items SET title=?, category=?, description=?, image_thumb=?,
                            is_category_showcase=?, showcase_subtitle=?, showcase_link=?
                            WHERE id=?";
        $stmt_update_main = $conn->prepare($sql_update_main);
        if ($stmt_update_main === false) throw new Exception("DB Main Update Prepare Error: " . $conn->error);
        $stmt_update_main->bind_param("ssssissi",
            $title, $category, $description, $new_thumb_path_for_db,
            $is_category_showcase, $showcase_subtitle, $showcase_link,
            $item_id_to_edit
        );
        if (!$stmt_update_main->execute()) throw new Exception("DB Main Update Execute Error: " . $stmt_update_main->error);
        $stmt_update_main->close();

        // 3. Handle Deletion of selected existing album images
        if (isset($_POST['delete_album_images']) && is_array($_POST['delete_album_images'])) {
            $album_ids_to_delete = $_POST['delete_album_images'];
            if (!empty($album_ids_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($album_ids_to_delete), '?'));
                $types = str_repeat('i', count($album_ids_to_delete));

                $sql_get_paths = "SELECT image_path FROM portfolio_item_images WHERE portfolio_item_id = ? AND id IN ($placeholders)";
                $stmt_get_paths = $conn->prepare($sql_get_paths);
                if ($stmt_get_paths) {
                    $bind_params_get_paths = array_merge([$item_id_to_edit], $album_ids_to_delete);
                    $stmt_get_paths->bind_param("i" . $types, ...$bind_params_get_paths);
                    $stmt_get_paths->execute();
                    $result_paths = $stmt_get_paths->get_result();
                    while ($img_to_del = $result_paths->fetch_assoc()) {
                        if (!empty($img_to_del['image_path']) && file_exists('../' . $img_to_del['image_path'])) {
                            unlink('../' . $img_to_del['image_path']);
                        }
                    }
                    $stmt_get_paths->close();
                } else { throw new Exception("Prepare error (get paths for delete): " . $conn->error); }


                $sql_delete_selected = "DELETE FROM portfolio_item_images WHERE portfolio_item_id = ? AND id IN ($placeholders)";
                $stmt_delete_selected = $conn->prepare($sql_delete_selected);
                if ($stmt_delete_selected) {
                    $bind_params_delete = array_merge([$item_id_to_edit], $album_ids_to_delete);
                    $stmt_delete_selected->bind_param("i" . $types, ...$bind_params_delete);
                    if (!$stmt_delete_selected->execute()) throw new Exception("Error deleting selected album images from DB: " . $stmt_delete_selected->error);
                    $stmt_delete_selected->close();
                } else { throw new Exception("Prepare error (delete from DB): " . $conn->error); }
            }
        }

        // 4. Handle Upload of NEW album images
        if (isset($_FILES['album_images_new']) && count($_FILES['album_images_new']['name']) > 0 && $_FILES['album_images_new']['name'][0] != "") {
            $upload_dir_album_relative = "../uploads/album_images/";
            if (!is_dir($upload_dir_album_relative)) { mkdir($upload_dir_album_relative, 0775, true); }

            for ($i = 0; $i < count($_FILES['album_images_new']['name']); $i++) {
                // Construct a temporary $_FILES-like structure for single file to pass to handle_single_upload_edit
                $current_album_file = [
                    'name' => $_FILES['album_images_new']['name'][$i],
                    'type' => $_FILES['album_images_new']['type'][$i],
                    'tmp_name' => $_FILES['album_images_new']['tmp_name'][$i],
                    'error' => $_FILES['album_images_new']['error'][$i],
                    'size' => $_FILES['album_images_new']['size'][$i]
                ];
                // Need to pass this structure to a modified handle_single_upload_edit or a new function
                // For simplicity, re-implementing the core logic here:
                if ($current_album_file['error'] == UPLOAD_ERR_OK) {
                    $temp_album_path_server = null; // Will hold path like 'uploads/album_images/file.jpg'

                    // Inline upload handling for this album image
                    $album_original_file_name = basename($current_album_file['name']);
                    $album_file_ext = strtolower(pathinfo($album_original_file_name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Ensure this list is defined
                    $max_file_size = 5 * 1024 * 1024; // Ensure this is defined

                    if (!in_array($album_file_ext, $allowed_extensions)) { $upload_errors[] = "Invalid type for album: {$album_original_file_name}"; continue; }
                    if ($current_album_file['size'] > $max_file_size) { $upload_errors[] = "Album file too large: {$album_original_file_name}"; continue; }
                    if ($current_album_file['size'] == 0) { $upload_errors[] = "Album file is empty: {$album_original_file_name}"; continue; }


                    $album_safe_filename_base = preg_replace("/[^a-zA-Z0-9_\-]/", "_", pathinfo($album_original_file_name, PATHINFO_FILENAME));
                    $album_safe_filename_base = substr($album_safe_filename_base, 0, 100);
                    $album_new_file_name = $item_id_to_edit . '_album_' . time() . '_' . uniqid('',true) . '_' . $album_safe_filename_base . '.' . $album_file_ext;
                    $album_destination_on_server = rtrim($upload_dir_album_relative, '/') . '/' . $album_new_file_name;

                    if (move_uploaded_file($current_album_file['tmp_name'], $album_destination_on_server)) {
                        $temp_album_path_server = str_replace('../', '', $upload_dir_album_relative) . $album_new_file_name;
                        $sql_add_album_img = "INSERT INTO portfolio_item_images (portfolio_item_id, image_path) VALUES (?, ?)";
                        $stmt_add_album_img = $conn->prepare($sql_add_album_img);
                        if($stmt_add_album_img === false) throw new Exception("DB New Album Img Prepare Error: " . $conn->error);
                        $stmt_add_album_img->bind_param("is", $item_id_to_edit, $temp_album_path_server);
                        if (!$stmt_add_album_img->execute()) throw new Exception("DB New Album Img Execute Error: " . $stmt_add_album_img->error);
                        $stmt_add_album_img->close();
                        $newly_uploaded_album_server_paths[] = $temp_album_path_server;
                    } else {
                        $upload_errors[] = "Failed to move new album image '{$album_original_file_name}'.";
                    }
                } elseif ($current_album_file['error'] != UPLOAD_ERR_NO_FILE) {
                    $upload_errors[] = "Upload error for new album image '{$current_album_file['name']}'. Code: " . $current_album_file['error'];
                }
            }
            if (!empty($upload_errors)) throw new Exception(implode("<br>", $upload_errors));
        }

        $conn->commit();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Portfolio item updated successfully!'];
        header('Location: manage_items.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        if (isset($newly_uploaded_thumb_server_path) && $newly_uploaded_thumb_server_path && file_exists('../' . $newly_uploaded_thumb_server_path)) {
            unlink('../' . $newly_uploaded_thumb_server_path);
        }
        foreach ($newly_uploaded_album_server_paths as $path_to_del) {
            if (file_exists('../' . $path_to_del)) { unlink('../' . $path_to_del); }
        }
        $_SESSION['edit_form_error'] = 'Error updating item: ' . $e->getMessage(); // Use a specific session var for this page
        error_log("Update Item Error (ID: $item_id_to_edit): " . $e->getMessage());
        header('Location: edit_item.php?id=' . $item_id_to_edit); // Redirect back to edit page
        exit();
    }
} // End POST request handling


// If there was an error message from POST processing redirect, display it now
if (isset($_SESSION['edit_form_error'])) {
    $form_message = ['type' => 'error', 'text' => $_SESSION['edit_form_error']];
    unset($_SESSION['edit_form_error']);
} elseif (isset($_SESSION['message']) && basename($_SERVER['PHP_SELF']) == 'edit_item.php' && isset($_GET['id'])) {
    // This handles general messages if user is redirected back to edit page for some other reason with a general message
    // However, typically update success/failure leads to manage_items.php
    // This might be redundant if all POST errors redirect with 'edit_form_error'
    // $form_message = $_SESSION['message'];
    // unset($_SESSION['message']);
}


$categories_available = ['wedding', 'portrait', 'pre-shoot', 'baby', 'event', 'landscape', 'fashion', 'product']; // Ensure this is defined for form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Portfolio Item</title>
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
        .current-image-preview img { max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px; object-fit: cover; }
        .existing-album-images-container { margin-bottom: 15px; border: 1px solid #eee; padding: 10px; border-radius: 4px; }
        .existing-album-images-container p {margin-bottom: 10px; font-weight:500;}
        .existing-album-image { display: inline-block; margin-right: 10px; margin-bottom: 10px; text-align: center; vertical-align: top; background: #f9f9f9; padding: 5px; border-radius: 4px;}
        .existing-album-image img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; display: block; margin-bottom: 5px; }
        .delete-checkbox-label { font-size: 0.85em; color: #dc3545; cursor:pointer; }
        .delete-checkbox-label input {vertical-align: middle; margin-right: 3px;}
        .form-field-group { margin-bottom: 15px; } /* Grouping related fields */
    </style>
</head>
<body>
    <?php include_once 'templates/header_admin.php'; // Admin Navigation Bar ?>

    <div class="admin-container">
        <h2>Edit Portfolio Item: <?php echo htmlspecialchars($item_data['title'] ?? 'Not Found'); ?></h2>

        <?php
        if ($form_message) { // Display error from POST processing if redirected back
            echo '<div class="message ' . htmlspecialchars($form_message['type']) . '">' . htmlspecialchars($form_message['text']) . '</div>';
        }
        ?>

        <?php if ($item_data): ?>
        <form action="edit_item.php?id=<?php echo $item_id_to_edit; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="item_id_hidden" value="<?php echo $item_id_to_edit; ?>">

            <div class="form-field-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($item_data['title']); ?>" required>
            </div>

            <div class="form-field-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories_available as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($item_data['category'] == $cat) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('-', ' ', htmlspecialchars($cat))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field-group">
                <label for="description">Description (Optional):</label>
                <textarea id="description" name="description" placeholder="A short description..."><?php echo htmlspecialchars($item_data['description']); ?></textarea>
            </div>

            <div class="form-field-group">
                <label>Current Thumbnail:</label>
                <div class="current-image-preview">
                    <?php if (!empty($item_data['image_thumb'])): ?>
                        <img src="../<?php echo htmlspecialchars($item_data['image_thumb']); ?>" alt="Current Thumbnail">
                    <?php else: ?>
                        <p>No thumbnail currently uploaded.</p>
                    <?php endif; ?>
                </div>
                <label for="image_thumb_new">Upload New Thumbnail (optional, replaces current):</label>
                <input type="file" id="image_thumb_new" name="image_thumb_new" accept="image/jpeg, image/png, image/gif, image/webp">
                <input type="hidden" name="existing_image_thumb" value="<?php echo htmlspecialchars($item_data['image_thumb'] ?? ''); ?>">
            </div>

            <hr>

            <h4>Album Images:</h4>
            <div class="existing-album-images-container">
                <?php if (!empty($album_images_existing)): ?>
                    <p>Current Album Images (select to delete):</p>
                    <?php foreach ($album_images_existing as $img): ?>
                        <div class="existing-album-image">
                            <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" alt="Album image for <?php echo htmlspecialchars($item_data['title']); ?>">
                             <label class="delete-checkbox-label">
                                <input type="checkbox" name="delete_album_images[]" value="<?php echo $img['id']; ?>"> Delete
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No existing album images for this item.</p>
                <?php endif; ?>
            </div>
            <div class="form-field-group">
                <label for="album_images_new">Add New Album Images (optional):</label>
                <input type="file" id="album_images_new" name="album_images_new[]" multiple accept="image/jpeg, image/png, image/gif, image/webp">
            </div>
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
            <a href="manage_items.php" class="back-to-home-btn" style="background-color: #6c757d; color:white;">« Back to Manage Items</a>
        </div>
        <?php else: ?>
            <p style="text-align:center; color:red;">Could not load item data for editing. It might have been deleted.</p>
             <div class="navigation-buttons" style="margin-top: 20px; text-align:center;">
                 <a href="manage_items.php" class="back-to-home-btn" style="background-color: #6c757d; color:white;">« Back to Manage Items</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleShowcaseOptions() {
            var checkbox = document.getElementById('is_category_showcase');
            var optionsDiv = document.getElementById('showcase_options_div');
            var subtitleInput = document.getElementById('showcase_subtitle');
            if (checkbox && optionsDiv && subtitleInput) {
                if (checkbox.checked) {
                    optionsDiv.style.display = 'block';
                    subtitleInput.required = (optionsDiv.style.display === 'block'); // Make required only if visible
                } else {
                    optionsDiv.style.display = 'none';
                    subtitleInput.required = false;
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleShowcaseOptions(); // Call on page load
        });
    </script>
</body>
</html>