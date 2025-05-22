<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegant Image Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="logo">
            <a href="#">Elegant Image</a>
        </div>
        <nav>
            <ul>
                <li><a href="#" class="active">Home</a></li>
                <li><a href="#about-section">About</a></li>
                <li><a href="#portfolio-section">Portfolio</a></li>
                <li><a href="#">Testimonials</a></li>
                <li><a href="#contact-section">Contact</a></li>
                <li>
                    <a href="admin/add_item.php" title="Admin Panel" class="admin-nav-icon">
                        👤
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-text">
                <h1>I'm here to <span class="highlight">capture</span> your moments.</h1>
                <p>I would like to give you a unique photography experience & capture your special moments for you.</p>
                <a href="#portfolio-section" class="cta-button">See My Works</a>
            </div>
            <div class="hero-gallery">
                <div class="photo-slot" id="slot1"><img src="placeholder1.jpg" alt="Photography example 1"></div>
                <div class="photo-slot" id="slot2"><img src="placeholder2.jpg" alt="Photography example 2"></div>
                <div class="photo-slot" id="slot3"><img src="placeholder3.jpg" alt="Photography example 3"></div>
                <div class="photo-slot" id="slot4"><img src="placeholder4.jpg" alt="Photography example 4"></div>
            </div>
        </section>

        <section class="about" id="about-section">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Us</h2>
                    <h3>Capturing Life's Beautiful Moments</h3>
                    <p>
                        With over a decade of experience in photography, we specialize in
                        turning ordinary moments into extraordinary memories. Our passion
                        lies in storytelling through images, creating timeless pieces that
                        you'll cherish forever.
                    </p>
                    <div class="stats-container">
                        <div class="stat-item">
                            <span class="stat-number">5+</span>
                            <span class="stat-label">Years Experience</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">200+</span>
                            <span class="stat-label">Happy Clients</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Projects Done</span>
                        </div>
                    </div>
                </div>
                <div class="about-image-slideshow">
                    <img src="Images/about1.jpg" alt="About Us Image" id="about-slideshow-image">
                </div>
            </div>
        </section>

        <?php
        include_once 'connection.php';
        ?>
        <section class="portfolio-main-section" id="portfolio-section">
            <div class="container">
                <div class="section-title-portfolio">
                    <h1>Showcase of my Expertise</h1>
                    <p class="portfolio-subtitle">Capturing moments, creating memories</p>
                </div>

                <?php
                $current_filter = isset($_GET['filter_category']) ? htmlspecialchars($_GET['filter_category']) : 'all';
                ?>

                <div class="portfolio-filters">
                    <a href="?filter_category=all#portfolio-section" class="<?php echo ($current_filter == 'all' ? 'filter-active' : ''); ?>">All</a>
                    <?php
                    $design_categories = ['wedding', 'portrait', 'pre-shoot', 'baby'];
                    foreach ($design_categories as $cat_slug) {
                        $display_name = ucfirst(str_replace('-', ' ', $cat_slug));
                        echo '<a href="?filter_category=' . urlencode($cat_slug) . '#portfolio-section" class="' . ($current_filter == $cat_slug ? 'filter-active' : '') . '">' . htmlspecialchars($display_name) . '</a>';
                    }
                    ?>
                </div>

                <div class="portfolio-grid">
                    <?php
                    if (!isset($conn) || (isset($conn->connect_error) && $conn->connect_error)) {
                        $db_error_msg = "Database connection failed. Please check configuration.";
                        if (isset($conn->connect_error) && $conn->connect_error) {
                            $db_error_msg .= " (Error: " . htmlspecialchars($conn->connect_error) . ")";
                        }
                        echo "<p class='no-items-message'>" . $db_error_msg . "</p>";
                    } else {
                        $sql = "SELECT id, title, category, image_thumb, description, is_category_showcase, showcase_subtitle, showcase_link FROM portfolio_items ";
                        if ($current_filter != 'all') {
                            $sql .= " WHERE category = ? ";
                        }
                        $sql .= " ORDER BY is_category_showcase DESC, uploaded_at DESC";
                        $result = null;
                        if ($current_filter != 'all') {
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                            } else {
                                $stmt->bind_param("s", $current_filter);
                                if (!$stmt->execute()) {
                                } else {
                                    $result = $stmt->get_result();
                                }
                                if ($stmt) $stmt->close();
                            }
                        } else {
                            $result_query = $conn->query($sql);
                            if ($result_query === false) {
                            } else {
                                $result = $result_query;
                            }
                        }

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                if ($row['is_category_showcase'] == 1) {
                                    if ($current_filter == 'all' || $current_filter == $row['category']) {
                                        echo '<div class="portfolio-item portfolio-showcase-item" data-category="' . htmlspecialchars($row['category']) . '">';
                                        echo '  <div class="showcase-bg-img" style="background-image: url(\'' . htmlspecialchars($row['image_thumb']) . '\');"></div>';
                                        echo '  <div class="showcase-content">';
                                        echo '      <h3>' . htmlspecialchars($row['title']) . '</h3>';
                                        echo '      <p>' . htmlspecialchars($row['showcase_subtitle']) . '</p>';
                                        echo '      <a href="' . htmlspecialchars($row['showcase_link']) . '" class="btn-see-more">See More</a>';
                                        echo '  </div>';
                                        echo '</div>';
                                    }
                                } else {
                                    if ($current_filter == 'all' || $current_filter == $row['category']) {
                                        echo '<div class="portfolio-item normal-item" data-id="' . htmlspecialchars($row['id']) . '" data-category="' . htmlspecialchars($row['category']) . '">';
                                        echo '  <img src="' . htmlspecialchars($row['image_thumb']) . '" alt="' . htmlspecialchars($row['title']) . '" data-fullsrc="' . htmlspecialchars($row['image_thumb']) . '" data-description="' . htmlspecialchars($row['description'] ?? $row['title']) . '">';
                                        echo '  <div class="item-overlay">';
                                        echo '      <h4 class="overlay-title">' . htmlspecialchars($row['title']) . '</h4>';
                                        $short_description = strip_tags($row['description'] ?? '');
                                        if (strlen($short_description) > 80) {
                                            $short_description = mb_substr($short_description, 0, 77, 'UTF-8') . '...';
                                        }
                                        echo '      <p class="overlay-description">' . nl2br(htmlspecialchars($short_description)) . '</p>';
                                        echo '      <a href="view_album.php?item_id=' . htmlspecialchars($row['id']) . '" class="btn-see-album">See Album</a>';
                                        echo '  </div>';
                                        echo '</div>';
                                    }
                                }
                            }
                        } else if ($result) {
                            echo "<p class='no-items-message'>No portfolio items found for this category.</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </section>

        <section class="contact-section" id="contact-section">
            <div class="container">
                <div class="section-title-contact">
                    <h2>Get in Touch</h2>
                    <p class="contact-subtitle">Let's create something beautiful together</p>
                </div>

                <div class="contact-content-wrapper">
                    <div class="contact-details-column">
                        <div class="contact-info-item">
                            <div class="contact-icon phone-icon">
                                📞
                            </div>
                            <div class="contact-text">
                                <h4>Call Us</h4>
                                <p>+62 11-9518-9545</p>
                                <small>Available 9:00 AM - 6:00 PM</small>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-icon email-icon">
                                📧 
                            </div>
                            <div class="contact-text">
                                <h4>Email Us</h4>
                                <p>brotherphotography@gmail.com</p>
                                <small>We reply within 24 hours</small>
                            </div>
                        </div>

                        <div class="connect-with-us">
                            <h4>Connect With Us</h4>
                            <div class="social-icons">
                                <a href="#" target="_blank" class="social-icon fb" title="Facebook"><i class="fa-brands fa-facebook"></i></a> <!-- Placeholder, use actual icons -->
                                <a href="#" target="_blank" class="social-icon ig" title="Instagram"><i class="fa-brands fa-square-instagram"></i></a>
                                <a href="#" target="_blank" class="social-icon tk" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                                <a href="#" target="_blank" class="social-icon wa" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form-column">
                        <form action="process_contact_form.php" method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="your-name">Your Name</label>
                                <input type="text" id="your-name" name="your_name" required>
                            </div>
                            <div class="form-group">
                                <label for="your-email">Your Email</label>
                                <input type="email" id="your-email" name="your_email" required>
                            </div>
                            <div class="form-group">
                                <label for="your-message">Your Message</label>
                                <textarea id="your-message" name="your_message" rows="6" required></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn-send-message">Send Message ➔</button>
                            </div>
                        </form>
                        <div id="form-status-message" style="margin-top: 15px;"></div>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <footer>
        <p>© <?php echo date("Y"); ?> Elegant Image</p>
    </footer>

    <script src="script.js"></script>
</body>

</html>