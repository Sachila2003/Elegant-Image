<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

require_once '../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_GET['action'] ?? 'add'; // Determine if adding or editing

    // Retrieve and sanitize data
    $client_name = trim($_POST['client_name']);
    $testimonial_text = trim($_POST['testimonial_text']);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
    $designation = trim($_POST['designation']) ?? null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?? 0;
    if ($sort_order === false) $sort_order = 0; // Default to 0 if not a valid int

    $errors = [];

    // Validations
    if (empty($client_name)) $errors[] = "Client name is required.";
    if (empty($testimonial_text)) $errors[] = "Testimonial text is required.";
    if ($rating === false) $errors[] = "Rating must be a number between 1 and 5.";


    if (empty($errors)) {
        if ($action == 'add') {
            $sql = "INSERT INTO testimonials (client_name, testimonial_text, rating, designation, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // Types: s=string, s=string, i=integer, s=string, i=integer (for boolean), i=integer
                $stmt->bind_param("ssisii", $client_name, $testimonial_text, $rating, $designation, $is_featured, $sort_order);
                if ($stmt->execute()) {
                    $_SESSION['testimonial_form_message'] = ['type' => 'success', 'text' => 'Testimonial added successfully!'];
                    header('Location: manage_testimonials.php'); // Redirect to manage page
                    exit();
                } else {
                    $errors[] = "Error adding testimonial: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error (prepare add): " . $conn->error;
            }
        } elseif ($action == 'edit' && isset($_POST['testimonial_id']) && is_numeric($_POST['testimonial_id'])) {
            $testimonial_id = intval($_POST['testimonial_id']);
            $sql = "UPDATE testimonials SET client_name=?, testimonial_text=?, rating=?, designation=?, is_featured=?, sort_order=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssisiii", $client_name, $testimonial_text, $rating, $designation, $is_featured, $sort_order, $testimonial_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Testimonial updated successfully!']; // General message for manage page
                    header('Location: manage_testimonials.php');
                    exit();
                } else {
                    $errors[] = "Error updating testimonial: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error (prepare edit): " . $conn->error;
            }
        } else {
            $errors[] = "Invalid action or missing testimonial ID for edit.";
        }
    }

    // If errors, store them and redirect back to the appropriate form
    if (!empty($errors)) {
        $error_string = "<ul>";
        foreach ($errors as $err) { $error_string .= "<li>" . htmlspecialchars($err) . "</li>"; }
        $error_string .= "</ul>";

        if ($action == 'edit' && isset($testimonial_id)) {
            $_SESSION['testimonial_edit_form_error'] = $error_string;
            header('Location: edit_testimonial.php?id=' . $testimonial_id);
        } else { // Default to add form for other errors or add action
            $_SESSION['testimonial_form_message'] = ['type' => 'error', 'text' => $error_string];
            header('Location: add_testimonial.php');
        }
        exit();
    }
    if(isset($conn)) $conn->close();

} else {
    // Not a POST request
    header('Location: add_testimonial.php'); // Or manage_testimonials.php
    exit();
}
?>