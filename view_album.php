<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'connection.php';

if (!isset($_GET['item_id']) || !filter_var($_GET['item_id'], FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit;
}
$item_id = intval($_GET['item_id']);

$main_item_details = null;
$stmt_main = $conn->prepare("SELECT title, description FROM portfolio_items WHERE id = ? AND is_category_showcase = 0");
if ($stmt_main) {
    $stmt_main->bind_param("i", $item_id);
    $stmt_main->execute();
    $result_main = $stmt_main->get_result();
    if ($result_main->num_rows > 0) {
        $main_item_details = $result_main->fetch_assoc();
    }
    $stmt_main->close();
}

if (!$main_item_details) {
    echo "<p style='text-align:center; padding:50px; font-size:1.2em;'>Album not found or is invalid.</p>";
    exit;
}

$album_images_list = [];
$stmt_album_images = $conn->prepare("SELECT image_path, caption FROM portfolio_item_images WHERE portfolio_item_id = ? ORDER BY sort_order ASC, id ASC");
if ($stmt_album_images) {
    $stmt_album_images->bind_param("i", $item_id);
    $stmt_album_images->execute();
    $result_album_images = $stmt_album_images->get_result();
    if ($result_album_images->num_rows > 0) {
        while ($image_row = $result_album_images->fetch_assoc()) {
            $album_images_list[] = $image_row;
        }
    }
    $stmt_album_images->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album: <?php echo htmlspecialchars($main_item_details['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f4f7f6;
            color: #333;
        }

        .album-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .album-page-header {
            padding: 15px 0;
            text-align: center;
            margin-bottom: 20px;
            
        }

        .album-page-header .logo a {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
        }

        .album-title-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-bottom: 2px solid #776ffa;
        }

        .album-title-section h1 {
            font-size: 2.2em;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .album-title-section .album-description {
            font-size: 1em;
            color: #555;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link-container {
            margin-bottom: 25px;
            text-align: center;
        }

        .back-to-portfolio {
            display: inline-block;
            padding: 10px 20px;
            background-color: #776ffa;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.95em;
            transition: background-color 0.3s;
        }

        .back-to-portfolio:hover {
            background-color: #5b54d1;
        }

        .album-image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .album-image-wrapper {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
            aspect-ratio: 4 / 3;
        }

        .album-image-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .album-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            cursor: pointer;
        }

        .album-image-caption {
            padding: 12px;
            font-size: 0.9em;
            color: #444;
            text-align: center;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="album-container">
        <div class="album-title-section">
            <h1><?php echo htmlspecialchars($main_item_details['title']); ?></h1>
            <?php if (!empty($main_item_details['description'])): ?>
                <p class="album-description"><?php echo nl2br(htmlspecialchars($main_item_details['description'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="back-link-container">
            <a href="index.php#portfolio-section" class="back-to-portfolio">« Back to Main Portfolio</a>
        </div>

        <?php if (!empty($album_images_list)): ?>
            <div class="album-image-grid">
                <?php foreach ($album_images_list as $image_data): ?>
                    <div class="album-image-wrapper">
                        <img src="<?php echo htmlspecialchars($image_data['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($image_data['caption'] ?? $main_item_details['title']); ?>"
                            data-fullsrc="<?php echo htmlspecialchars($image_data['image_path']); ?>"
                            data-description="<?php echo htmlspecialchars($image_data['caption'] ?? ''); ?>">
                        <?php if (!empty($image_data['caption'])): ?>
                            <div class="album-image-caption"><?php echo htmlspecialchars($image_data['caption']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-images-message">This album currently has no additional images.</p>
        <?php endif; ?>
    </div>>
    <div id="lightbox" class="lightbox-hidden">
        <span class="lightbox-close-btn">×</span>
        <img class="lightbox-content" id="lightbox-img" src="">
        <div id="lightbox-caption"></div>
        <a class="lightbox-prev">❮</a>
        <a class="lightbox-next">❯</a>
    </div>
    <script src="script.js"></script>

</body>

</html>