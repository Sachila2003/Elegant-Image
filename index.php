<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegant Image | Professional Wedding & Event Photographer in Sri Lanka</title>
    <meta name="description"
        content="Discover the art of professional photography with Elegant Image, a Sri Lanka based photographer specializing in capturing beautiful moments from weddings, events, and portraits. View our portfolio.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- ==================== HEADER (Final Corrected HTML) ==================== -->
    <header class="header" id="header">
        <a href="#home" class="logo">
            <span>Elegant Image</span>
        </a>

        <!-- Navigation Menu -->
        <nav class="navbar" id="nav-menu">
            <!-- Links ටික list එකකට දැම්මා -->
            <ul class="nav-list">
                <li class="nav-item"><a href="#home" class="nav-link active">Home</a></li>
                <li class="nav-item"><a href="#about-section" class="nav-link">About</a></li>
                <li class="nav-item"><a href="#portfolio-section" class="nav-link">Portfolio</a></li>
                <li class="nav-item"><a href="#testimonials" class="nav-link">Testimonials</a></li>
                <li class="nav-item"><a href="#contact-section" class="nav-link">Contact</a></li>
            </ul>

            <!-- Mobile Menu Close Button -->
            <div class="nav-close" id="nav-close">
                <i class="fas fa-times"></i>
            </div>
        </nav>

        <!-- Header Action Icons -->
        <div class="header-action-group">
            <a href="admin/login.php" class="header-icon-btn admin-nav-icon" title="Admin Panel">
                <i class="fa-solid fa-gear"></i>
            </a>
            <button class="header-icon-btn theme-toggle" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <!-- Hamburger Menu Toggle Button -->
            <button class="header-icon-btn nav-toggle" id="nav-toggle" title="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="hero-text">
                <h1>I'm here to <span class="highlight">capture</span> your moments.</h1>
                <p>More than just photos, I capture the feeling—the joy, the love, and the laughter. Let me help you
                    tell your beautiful story.</p>
                <a href="#portfolio-section" class="cta-button">Explore The Gallery</a>
            </div>
            <div class="hero-gallery">
                <div class="photo-slot" id="slot1"><img src="placeholder1.jpg" alt="Photography example 1"></div>
                <div class="photo-slot" id="slot2"><img src="placeholder2.jpg" alt="Photography example 2"></div>
                <!-- <div class="photo-slot" id="slot3"><img src="placeholder3.jpg" alt="Photography example 3"></div> -->
                <div class="photo-slot" id="slot4"><img src="placeholder4.jpg" alt="Photography example 4"></div>
            </div>
        </section>

        <section class="about" id="about-section">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Us</h2>
                    <h3>Capturing Life's Beautiful Moments</h3>
                    <p>
                        Five years behind the lens has shown us that every photo holds a feeling. Our passion isn't just
                        photography; it's capturing the unspoken joy, the quiet love, and the fleeting moments that
                        define your life. Let us help you preserve the memories you'll want to relive forever.
                    </p>

                    <div class="stats-container">
                        <div class="stat-item">
                            <span class="stat-number">5+</span>
                            <span class="stat-label">Years Experience</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">100+</span>
                            <span class="stat-label">Happy Clients</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">200+</span>
                            <span class="stat-label">Projects Done</span>
                        </div>
                    </div>

                    <div class="connect-with-us-about">
                        <h4>Join Our Journey</h4>
                        <div class="social-icons-about">
                            <a href="https://www.facebook.com/elegantIMAGElk?mibextid=ZbWKwL" target="_blank"
                                class="social-icon fb" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                            <a href="https://www.tiktok.com/@elegantimage00?_t=ZS-8xo1ydAyGl6&_r=1" target="_blank"
                                class="social-icon tk" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                            <a href="https://wa.me/message/JGAZC7SP4K4WE1" target="_blank" class="social-icon wa"
                                title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
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
                    <h1>Moments We've Captured</h1>
                    <p class="portfolio-subtitle">Framing life's timeless chapters.</p>
                </div>

                <?php
                $current_filter = isset($_GET['filter_category']) ? htmlspecialchars($_GET['filter_category']) : 'all';
                ?>

                <div class="portfolio-filters">
                    <a href="?filter_category=all#portfolio-section"
                        class="<?php echo ($current_filter == 'all' ? 'filter-active' : ''); ?>">All</a>
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
                                if ($stmt)
                                    $stmt->close();
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
        <?php

        $testimonials_list_data = [];
        if (isset($conn) && !$conn->connect_error) {
            $sql_testimonials_query = "SELECT client_name, testimonial_text, rating, designation
                               FROM testimonials
                               WHERE is_featured = 1
                               ORDER BY sort_order ASC, created_at DESC
                               LIMIT 5";
            $result_testimonials_query = $conn->query($sql_testimonials_query);
            if ($result_testimonials_query && $result_testimonials_query->num_rows > 0) {
                while ($row_testimonial_data = $result_testimonials_query->fetch_assoc()) {
                    $testimonials_list_data[] = $row_testimonial_data;
                }
            }
        }
        ?>
        <section class="testimonials-section" id="testimonials">
            <div class="container">
                <div class="section-title-testimonials">
                    <h2>What Our Clients Say</h2>
                </div>

                <?php if (!empty($testimonials_list_data)): ?>
                    <div class="testimonial-slider-wrapper">
                        <div class="testimonial-slider-container">
                            <div class="testimonial-slider">
                                <?php foreach ($testimonials_list_data as $index => $testimonial_item): ?>
                                    <div class="testimonial-slide">
                                        <div class="testimonial-card">
                                            <?php if (isset($testimonial_item['rating']) && $testimonial_item['rating'] > 0): ?>
                                                <div class="rating-stars">
                                                    <?php
                                                    $rating_value = intval($testimonial_item['rating']);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo ($i <= $rating_value) ? '<span class="star filled">★</span>' : '<span class="star empty">☆</span>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <p class="testimonial-quote">
                                                "<?php echo nl2br(htmlspecialchars($testimonial_item['testimonial_text'])); ?>"
                                            </p>
                                            <div class="client-info">
                                                <h4 class="client-name">-
                                                    <?php echo htmlspecialchars($testimonial_item['client_name']); ?>
                                                </h4>
                                                <?php if (!empty($testimonial_item['designation'])): ?>
                                                    <p class="client-designation">
                                                        <?php echo htmlspecialchars($testimonial_item['designation']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if (count($testimonials_list_data) > 1): ?>
                            <div class="slider-nav-controls">
                                <div class="slider-nav-arrows">
                                    <button type="button" class="slider-arrow prev-arrow"
                                        aria-label="Previous Testimonial">❮</button>
                                    <button type="button" class="slider-arrow next-arrow"
                                        aria-label="Next Testimonial">❯</button>
                                </div>
                                <div class="slider-nav-dots">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align:center; color:#777; padding: 20px 0;">We are grateful for all our clients! More
                        testimonials coming soon.</p>
                <?php endif; ?>
            </div>
        </section>
        <!-- END TESTIMONIALS SECTION -->
        <section class="contact-section" id="contact-section">
            <div class="container">
                <div class="section-title-contact">
                    <h2>Get in Touch</h2>
                    <p class="contact-subtitle">Let's create something beautiful together</p>
                </div>

                <div class="contact-content-wrapper">
                    <div class="contact-details-column">
                        <h3 class="contact-main-heading">Connect With Us</h3>
                        <div class="contact-info-item">
                            <div class="contact-icon phone-icon">
                                📞
                            </div>
                            <div class="contact-text">
                                <h4>Call Us</h4>
                                <p>+94 74-271-5484</p>
                                <small>Available 9:00 AM - 6:00 PM</small>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-icon email-icon">
                                📧
                            </div>
                            <div class="contact-text">
                                <h4>Email Us</h4>
                                <p>Contact.elegantimage@gmail.com</p>
                                <small>We reply within 24 hours</small>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form-column">
                        <div id="whatsapp-contact-form" class="contact-form">
                            <div class="form-group">
                                <label for="whatsapp_name">Your Name</label>
                                <input type="text" id="whatsapp_name" name="name" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp_subject">Subject</label>
                                <input type="text" id="whatsapp_subject" name="subject"
                                    placeholder="e.g., Wedding Photography Inquiry" required>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp_message">Your Message</label>
                                <textarea id="whatsapp_message" name="message"
                                    placeholder="Hi, I would like to know more about..." required></textarea>
                            </div>
                            <a href="#" id="send-whatsapp-btn" class="btn-send-message">Send Message via WhatsApp</a>
                        </div>
                        <div id="form-status-message" style="margin-top: 15px;"></div>
                    </div>

                </div>
            </div>
        </section>

    </main>
    <footer class="site-footer" id="footer">
        <div class="container">
            <div class="footer-content">

                <!-- තීරුව 1: Brand සහ Motto -->
                <div class="footer-column footer-about">
                    <h4 class="footer-logo">Elegant Image</h4>
                    <p class="footer-motto">
                        Capturing life's beautiful moments, one frame at a time.
                    </p>
                </div>

                <div class="footer-column footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#portfolio-section">Portfolio</a></li>
                        <li><a href="#about-section">About Us</a></li>
                        <li><a href="#contact-section">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-column footer-social">
                    <h4>Follow Us</h4>
                    <div class="footer-social-icons">
                        <a href="https://www.facebook.com/elegantIMAGElk?mibextid=ZbWKwL" target="_blank"
                            class="social-icon fb" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                        <a href="https://www.tiktok.com/@elegantimage00?_t=ZS-8xo1ydAyGl6&_r=1" target="_blank"
                            class="social-icon tk" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="https://wa.me/message/JGAZC7SP4K4WE1" target="_blank" class="social-icon wa"
                            title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                <p>© <span id="copyright-year"></span> Elegant Image. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>