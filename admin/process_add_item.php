<?php
require_once '../connection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize basic data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    $is_category_showcase = isset($_POST['is_category_showcase']) ? 1 : 0;
    $showcase_subtitle = ($is_category_showcase && isset($_POST['showcase_subtitle'])) ? trim($_POST['showcase_subtitle']) : null;
    $showcase_link = ($is_category_showcase && isset($_POST['showcase_link'])) ? trim($_POST['showcase_link']) : null;

    $upload_dir_thumb_relative = "../uploads/thumbnails/";
    $upload_dir_album_relative = "../uploads/album_images/"; // New directory for album images

    if (!is_dir($upload_dir_thumb_relative)) { mkdir($upload_dir_thumb_relative, 0775, true); }
    if (!is_dir($upload_dir_album_relative)) { mkdir($upload_dir_album_relative, 0775, true); }

    $image_thumb_path_for_db = null;
    $upload_errors = [];

    // Re-using your handle_upload function for the thumbnail
    function handle_single_upload($file_input_name, $target_dir_relative_to_script, &$db_path_variable, &$errors_array) {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
            $original_file_name = $_FILES[$file_input_name]['name'];
            $file_size = $_FILES[$file_input_name]['size'];
            $file_ext_parts = explode('.', $original_file_name);
            $file_ext = strtolower(end($file_ext_parts));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $max_file_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file_ext, $allowed_extensions)) {
                $errors_array[] = "Invalid file type for '{$file_input_name}': .{$file_ext}."; return false;
            }
            if ($file_size > $max_file_size) {
                $errors_array[] = "File '{$file_input_name}' is too large."; return false;
            }
            $safe_filename_base = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", pathinfo($original_file_name, PATHINFO_FILENAME));
            $safe_filename_base = substr($safe_filename_base, 0, 100);
            $new_file_name = time() . '_' . uniqid('', true) . '_' . $safe_filename_base . '.' . $file_ext;
            $destination_path_on_server = $target_dir_relative_to_script . $new_file_name;
            if (move_uploaded_file($file_tmp_path, $destination_path_on_server)) {
                $db_path_variable = str_replace('../', '', $target_dir_relative_to_script) . $new_file_name;
                return true;
            } else {
                $errors_array[] = "Failed to move '{$file_input_name}'. Check permissions for {$target_dir_relative_to_script}."; return false;
            }
        } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
            $errors_array[] = "Upload error for '{$file_input_name}'. Code: " . $_FILES[$file_input_name]['error']; return false;
        } elseif (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
            if ($file_input_name === 'image_thumb') { // Thumbnail is required
                $errors_array[] = "No file uploaded for '{$file_input_name}'. This field is required."; return false;
            }
        }
        return true; // True if optional and no file, or successful upload
    }

    if (empty($title)) { $upload_errors[] = "Title is required."; }
    if (empty($category)) { $upload_errors[] = "Category is required."; }

    $thumb_uploaded_successfully = handle_single_upload('image_thumb', $upload_dir_thumb_relative, $image_thumb_path_for_db, $upload_errors);

    if ($is_category_showcase == 1 && empty($showcase_subtitle)) {
        $upload_errors[] = "Showcase Subtitle is required for showcase blocks.";
    }

    // Proceed only if thumbnail is uploaded and no initial validation errors
    if ($thumb_uploaded_successfully && empty($upload_errors)) {
        if (isset($conn) && !$conn->connect_error) {
            $conn->begin_transaction();
            try {
                // Insert into main portfolio_items table (image_full column is now removed from this table)
                $sql_main = "INSERT INTO portfolio_items (title, category, image_thumb, description, is_category_showcase, showcase_subtitle, showcase_link)
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_main = $conn->prepare($sql_main);
                if ($stmt_main === false) throw new Exception("DB Main Prepare Error: " . $conn->error);

                $stmt_main->bind_param("ssssiss",
                    $title, $category, $image_thumb_path_for_db,
                    $description, $is_category_showcase, $showcase_subtitle, $showcase_link
                );

                if (!$stmt_main->execute()) throw new Exception("DB Main Execute Error: " . $stmt_main->error);
                $portfolio_item_id = $stmt_main->insert_id; // Get the ID of the newly created portfolio item
                $stmt_main->close();

                // --- Process Multiple Album Images ---
                $album_images_paths_for_db = [];
                if (isset($_FILES['album_images']) && count($_FILES['album_images']['name']) > 0 && $_FILES['album_images']['name'][0] != "") {
                    $total_album_files = count($_FILES['album_images']['name']);
                    for ($i = 0; $i < $total_album_files; $i++) {
                        if ($_FILES['album_images']['error'][$i] == UPLOAD_ERR_OK) {
                            $album_file_tmp_path = $_FILES['album_images']['tmp_name'][$i];
                            $album_original_file_name = $_FILES['album_images']['name'][$i];
                            $album_file_size = $_FILES['album_images']['size'][$i];
                            // Add validation for each album image (type, size)
                            $album_file_ext_parts = explode('.', $album_original_file_name);
                            $album_file_ext = strtolower(end($album_file_ext_parts));
                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            $max_file_size = 5 * 1024 * 1024; // 5MB

                            if (!in_array($album_file_ext, $allowed_extensions)) {
                                $upload_errors[] = "Invalid file type for album image '{$album_original_file_name}'."; continue;
                            }
                            if ($album_file_size > $max_file_size) {
                                $upload_errors[] = "Album image '{$album_original_file_name}' is too large."; continue;
                            }

                            $album_safe_filename_base = preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", pathinfo($album_original_file_name, PATHINFO_FILENAME));
                            $album_safe_filename_base = substr($album_safe_filename_base, 0, 100);
                            $album_new_file_name = $portfolio_item_id . '_' . time() . '_' . uniqid('',true) . '_' . $album_safe_filename_base . '.' . $album_file_ext;
                            $album_destination_on_server = $upload_dir_album_relative . $album_new_file_name;
                            $album_image_path_db = str_replace('../', '', $upload_dir_album_relative) . $album_new_file_name;

                            if (move_uploaded_file($album_file_tmp_path, $album_destination_on_server)) {
                                $sql_album_img = "INSERT INTO portfolio_item_images (portfolio_item_id, image_path) VALUES (?, ?)";
                                $stmt_album_img = $conn->prepare($sql_album_img);
                                if($stmt_album_img === false) throw new Exception("DB Album Image Prepare Error: " . $conn->error);
                                $stmt_album_img->bind_param("is", $portfolio_item_id, $album_image_path_db);
                                if (!$stmt_album_img->execute()) throw new Exception("DB Album Image Execute Error: " . $stmt_album_img->error);
                                $stmt_album_img->close();
                                $album_images_paths_for_db[] = $album_image_path_db; // Keep track of successfully uploaded album images
                            } else {
                                $upload_errors[] = "Failed to move album image '{$album_original_file_name}'.";
                            }
                        } elseif ($_FILES['album_images']['error'][$i] != UPLOAD_ERR_NO_FILE) {
                            $upload_errors[] = "Upload error for album image '{$_FILES['album_images']['name'][$i]}'. Code: " . $_FILES['album_images']['error'][$i];
                        }
                    }
                }
                // --- End Process Multiple Album Images ---

                if (!empty($upload_errors)) { // If any errors occurred during album image processing or other validations
                    throw new Exception(implode("<br>", $upload_errors));
                }

                $conn->commit();
                header('Location: add_item.php?status=success');
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                error_log("Admin Panel Error: " . $e->getMessage());
                // Clean up: delete thumbnail if it was uploaded
                if ($image_thumb_path_for_db && file_exists('../' . $image_thumb_path_for_db)) {
                    unlink('../' . $image_thumb_path_for_db);
                }
                // Clean up: delete any album images that were successfully moved before the error
                foreach ($album_images_paths_for_db as $path_to_delete) {
                    if (file_exists('../' . $path_to_delete)) {
                        unlink('../' . $path_to_delete);
                    }
                }
                header('Location: add_item.php?status=error&msg=' . urlencode("Operation failed: " . $e->getMessage()));
                exit();
            }
        } else { // Database connection error
            $upload_errors[] = "Database connection not available.";
        }
    }
    // If thumbnail upload failed or initial validation errors
    $error_message = !empty($upload_errors) ? implode("<br>", $upload_errors) : "An unknown error occurred.";
    header('Location: add_item.php?status=upload_error&msg=' . urlencode($error_message));
    exit();
} else {
    header('Location: add_item.php');
    exit();
}
?>