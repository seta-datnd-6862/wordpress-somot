<?php
/**
 * Somot Restaurant Reservation System - INLINE VERSION
 * 
 * This snippet replaces the Elementor form with a custom reservation system
 * that integrates with Goodriver Laravel backend.
 * All CSS and JavaScript are included inline - no separate files needed.
 * 
 * Installation:
 * 1. Add this code to your theme's functions.php or use Code Snippets plugin
 * 2. Update GOODRIVER_API_URL and GOODRIVER_API_KEY constants below
 * 3. Create a new page and add shortcode: [somot_reservation_form]
 * 4. Done!
 */

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================================================

define('GOODRIVER_API_URL', 'https://goodriver.online/api');
define('GOODRIVER_API_KEY', 'your-secure-api-key-here'); // From .env WORDPRESS_API_KEY

// ============================================================================
// BRANCH CONFIGURATION (matches Goodriver branches)
// ============================================================================

function somot_get_branches() {
    return array(
        array('id' => 2, 'name' => 'Tayuman Branch, Manila', 'lat' => 14.6175959, 'lng' => 120.9837713, 'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila, Philippines'),
        array('id' => 1, 'name' => 'Pioneer Branch, Pasig', 'lat' => 14.5731404, 'lng' => 121.0164509, 'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines'),
    );
}

function somot_get_all_branches_info() {
    return array(
        array(
            'id' => 1,
            'name' => 'So Mot Vietnamese Restaurant Tayuman St. cor. Oroquieta St.',
            'phone' => '+63 929 573 6960',
            'hours' => '08:00 AM – 10:00 PM',
            'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila, Philippines'
        ),
        array(
            'id' => 2,
            'name' => 'So Mot Vietnamese Restaurant Pioneer Center Supermarket',
            'phone' => '+63 928 945 2998',
            'hours' => '07:00 AM – 11:00 PM',
            'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines'
        ),
        array(
            'id' => 3,
            'name' => 'So Mot Vietnamese Kiosk Unimart, Capitol Commons',
            'phone' => '0969 049 8158',
            'hours' => '09:00 AM – 09:30 PM',
            'address' => 'Ground Floor, Unimart at Capitol Commons, Shaw Blvd, Pasig, Metro Manila, Philippines'
        ),
        array(
            'id' => 4,
            'name' => 'So Mot Vietnamese Restaurant 4th Floor, Ayala Malls Cloverleaf',
            'phone' => '+63 969 151 1222',
            'hours' => '09:00 AM – 09:30 PM',
            'address' => 'A. Bonifacio Ave, Quezon City, 1115 Metro Manila, Philippines'
        ),
    );
}

// ============================================================================
// REGISTER CUSTOM POST TYPE
// ============================================================================

function somot_register_reservation_post_type() {
    register_post_type('reservation', array(
        'labels' => array(
            'name' => 'Reservations',
            'singular_name' => 'Reservation',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Reservation',
            'edit_item' => 'Edit Reservation',
            'view_item' => 'View Reservation',
            'search_items' => 'Search Reservations',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title'),
        'has_archive' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap' => true,
    ));
}
add_action('init', 'somot_register_reservation_post_type');

// ============================================================================
// META BOXES
// ============================================================================

function somot_add_reservation_meta_boxes() {
    add_meta_box(
        'reservation_details',
        'Reservation Details',
        'somot_reservation_details_callback',
        'reservation',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'somot_add_reservation_meta_boxes');

function somot_reservation_details_callback($post) {
    $customer_name = get_post_meta($post->ID, '_customer_name', true);
    $customer_email = get_post_meta($post->ID, '_customer_email', true);
    $customer_phone = get_post_meta($post->ID, '_customer_phone', true);
    $number_of_guests = get_post_meta($post->ID, '_number_of_guests', true);
    $reservation_date = get_post_meta($post->ID, '_reservation_date', true);
    $reservation_time = get_post_meta($post->ID, '_reservation_time', true);
    $branch_id = get_post_meta($post->ID, '_branch_id', true);
    $branch_name = get_post_meta($post->ID, '_branch_name', true);
    $additional_notes = get_post_meta($post->ID, '_additional_notes', true);
    $goodriver_id = get_post_meta($post->ID, '_goodriver_id', true);
    $order_key = get_post_meta($post->ID, '_order_key', true);
    $status = get_post_meta($post->ID, '_status', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th>Order Key:</th>
            <td><strong><?php echo esc_html($order_key); ?></strong></td>
        </tr>
        <tr>
            <th>Goodriver ID:</th>
            <td><?php echo esc_html($goodriver_id); ?></td>
        </tr>
        <tr>
            <th>Status:</th>
            <td><?php echo esc_html($status ?: 'Waiting'); ?></td>
        </tr>
        <tr>
            <th>Customer Name:</th>
            <td><?php echo esc_html($customer_name); ?></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?php echo esc_html($customer_email); ?></td>
        </tr>
        <tr>
            <th>Phone:</th>
            <td><?php echo esc_html($customer_phone); ?></td>
        </tr>
        <tr>
            <th>Number of Guests:</th>
            <td><?php echo esc_html($number_of_guests); ?></td>
        </tr>
        <tr>
            <th>Reservation Date:</th>
            <td><?php echo esc_html($reservation_date); ?></td>
        </tr>
        <tr>
            <th>Reservation Time:</th>
            <td><?php echo esc_html($reservation_time); ?></td>
        </tr>
        <tr>
            <th>Branch:</th>
            <td><?php echo esc_html($branch_name); ?></td>
        </tr>
        <?php if ($additional_notes): ?>
        <tr>
            <th>Additional Notes:</th>
            <td><?php echo esc_html($additional_notes); ?></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php
}

// ============================================================================
// SHORTCODE
// ============================================================================

function somot_reservation_form_shortcode($atts) {
    ob_start();
    ?>
    <div class="somot-reservation-wrapper">
        <div id="reservation-messages"></div>
        
        <div class="somot-reservation-card">
            <h3 class="reservation-title">Booking A Table Online Is Easy</h3>
            
            <form id="somot-reservation-form" class="somot-reservation-form">
                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-column">
                        <div class="form-group">
                            <label for="reservation_date">Date <span class="required">*</span></label>
                            <input type="text" id="reservation_date" name="reservation_date" required placeholder="2025-12-18" readonly>
                        </div>

                        <div class="form-group">
                            <label for="reservation_time">From when? <span class="required">*</span></label>
                            <select id="reservation_time" name="reservation_time" required>
                                <option value="">Start time here</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="15:30">3:30 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="16:30">4:30 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="18:30">6:30 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="19:30">7:30 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="20:30">8:30 PM</option>
                                <option value="21:00">9:00 PM</option>
                                <option value="21:30">9:30 PM</option>
                                <option value="22:00">10:00 PM</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="customer_name">Your Name <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required placeholder="Name here">
                        </div>

                        <div class="form-group">
                            <label for="customer_email">Your Email <span class="required">*</span></label>
                            <input type="email" id="customer_email" name="customer_email" required placeholder="Email here">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-column">
                        <div class="form-group">
                            <label for="branch_id">Select Branch <span class="required">*</span></label>
                            <select id="branch_id" name="branch_id" required>
                                <option value="">Choose a branch</option>
                                <?php foreach (somot_get_branches() as $branch): ?>
                                    <option value="<?php echo esc_attr($branch['id']); ?>">
                                        <?php echo esc_html($branch['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Until?</label>
                            <select id="reservation_end_time" name="reservation_end_time">
                                <option value="">End time here</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="15:30">3:30 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="16:30">4:30 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="17:30">5:30 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="18:30">6:30 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="19:30">7:30 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="20:30">8:30 PM</option>
                                <option value="21:00">9:00 PM</option>
                                <option value="21:30">9:30 PM</option>
                                <option value="22:00">10:00 PM</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">How can we contact you? <span class="required">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="Phone Number here">
                        </div>

                        <div class="form-group">
                            <label for="number_of_guests">Total Guests <span class="required">*</span></label>
                            <input type="number" id="number_of_guests" name="number_of_guests" required placeholder="Number of Guests" min="1" max="100">
                        </div>
                    </div>
                </div>

                <!-- Full Width Field -->
                <div class="form-group form-group-full">
                    <label for="additional_notes">Additional Information</label>
                    <textarea id="additional_notes" name="additional_notes" rows="4" placeholder="Enter Your Message here"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="button" id="request-cancellation" class="btn btn-secondary">Request Cancellation</button>
                    <button type="submit" id="submit-reservation" class="btn btn-primary">Book a table</button>
                </div>
            </form>
        </div>

        <!-- Branches Information Section -->
        <div class="somot-branches-section">
            <h3 class="branches-title">Our Locations</h3>
            <div class="branches-grid">
                <?php foreach (somot_get_all_branches_info() as $branch): ?>
                    <div class="branch-card">
                        <h4 class="branch-name"><?php echo esc_html($branch['name']); ?></h4>
                        <div class="branch-info">
                            <p class="branch-detail">
                                <span class="branch-label">Phone:</span> 
                                <a href="tel:<?php echo esc_attr(str_replace(' ', '', $branch['phone'])); ?>"><?php echo esc_html($branch['phone']); ?></a>
                            </p>
                            <p class="branch-detail">
                                <span class="branch-label">Hours Open:</span> 
                                <?php echo esc_html($branch['hours']); ?>
                            </p>
                            <p class="branch-detail">
                                <span class="branch-label">Address:</span> 
                                <?php echo esc_html($branch['address']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('somot_reservation_form', 'somot_reservation_form_shortcode');

// ============================================================================
// FULL PAGE SHORTCODE (with Hero + Form + Video)
// ============================================================================

function somot_reservation_full_page_shortcode($atts) {
    ob_start();
    ?>
    <div class="ast-container" style="
    max-width: 1200px !important;
    width: 100% !important;
    flex-direction: column !important;
">
        <!-- Hero Section -->
        <div class="somot-hero-section">
            <div class="somot-hero-overlay">
                <h1 class="somot-hero-title" style="color: #FFFDF1; font-weight: bold;">Reserve Your Table - Savor Authentic Vietnam</h1>
                <span class="somot-subtitle">For Every Taste, Every Moment</span>
                <nav class="somot-breadcrumb">
                    <a href="<?php echo home_url(); ?>">Home</a>
                    <span class="separator">&nbsp;/&nbsp;</span>
                    <span>Reservation</span>
                </nav>
            </div>
        </div>

        <!-- Form Section (Centered) -->
        <div class="somot-form-section">
            <div class="somot-container">
                <div class="somot-form-center">
                    <?php echo somot_reservation_form_shortcode(array()); ?>
                </div>
            </div>
        </div>

        <!-- Video Section (3 YouTube Videos) -->
        <div class="somot-video-section">
            <div class="somot-container">
                <div class="somot-video-grid">
                    <div class="somot-video-item">
                        <iframe 
                            src="https://www.youtube.com/embed/WSwNxGUeaC8?si=JLuqTp2J7RFJRm4Z" 
                            title="Số Một Video 1" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="somot-video-item">
                        <iframe 
                            src="https://www.youtube.com/embed/Qfb5GDSkauw?si=ND7d0qXuSQzNxI4-" 
                            title="Số Một Video 2" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="somot-video-item">
                        <iframe 
                            src="https://www.youtube.com/embed/cqIjDg7kuwo?si=6XEqSMk7gGqBFTbo" 
                            title="Số Một Video 3" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('somot_reservation_full_page', 'somot_reservation_full_page_shortcode');

// ============================================================================
// ENQUEUE SCRIPTS (INLINE)
// ============================================================================

function somot_reservation_enqueue_scripts() {
    $post_content = '';
    if (is_page() && get_post()) {
        $post_content = get_post()->post_content;
    }
    
    if (has_shortcode($post_content, 'somot_reservation_form') || has_shortcode($post_content, 'somot_reservation_full_page')) {
        // jQuery UI Datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // Localize script data
        wp_localize_script('jquery', 'somot_reservation', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('somot_reservation_nonce'),
            'branches' => somot_get_branches(),
        ));
        
        // Add inline CSS
        add_action('wp_head', 'somot_reservation_inline_css');
        
        // Add inline JavaScript
        add_action('wp_footer', 'somot_reservation_inline_js');
    }
}
add_action('wp_enqueue_scripts', 'somot_reservation_enqueue_scripts');

// ============================================================================
// INLINE CSS
// ============================================================================

function somot_reservation_inline_css() {
    ?>
    <style type="text/css">
        /* ============================================ */
        /* HERO SECTION */
        /* ============================================ */
        .somot-hero-section {
            min-height: 450px;
            background-image: url('https://so-mot.com/wp-content/uploads/2025/10/so-mot-restaurant-web.jpg.webp');
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 60px;
        }

        .somot-hero-overlay {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #FFFDF1;
        }

        .somot-hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #2d5016;
            opacity: 0.7;
            z-index: 1;
        }

        .somot-hero-title {
            font-family: "Prata", Sans-serif;
            font-size: 35px;
            font-weight: 400;
            text-transform: capitalize;
            line-height: 1.2em;
            letter-spacing: 2px;
            margin: 0 0 15px 0;
            color: #FFFDF1 !important;
        }

        .somot-subtitle {
            font-family: "Quicksand", Sans-serif;
            font-size: 25px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #FFFDF1;
        }

        .somot-breadcrumb {
            font-family: "Quicksand", Sans-serif;
            color: #FFFDF1;
            font-size: 14px;
        }

        .somot-breadcrumb a {
            color: #FFFDF1;
            text-decoration: none;
        }

        .somot-breadcrumb a:hover {
            text-decoration: underline;
        }

        .somot-breadcrumb .separator {
            color: #FFFDF1;
        }

        /* ============================================ */
        /* FORM SECTION CONTAINER */
        /* ============================================ */
        .somot-form-section {
            background-color: #FFFDF1;
        }

        .somot-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .somot-form-center {
            max-width: 800px;
            margin: 0 auto;
        }

        /* ============================================ */
        /* BRANCHES INFORMATION SECTION */
        /* ============================================ */
        .somot-branches-section {
            margin-top: 60px;
            padding: 40px 0;
        }

        .branches-title {
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            color: #2d5016;
            margin-bottom: 40px;
            font-family: "Quicksand", Sans-serif;
            text-transform: capitalize;
            letter-spacing: 1px;
        }

        .branches-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .branch-card {
            background-color: #fff4d1;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .branch-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .branch-name {
            color: #3B7D3B;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 20px 0;
            line-height: 1.4;
            font-family: "Quicksand", Sans-serif;
        }

        .branch-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .branch-detail {
            margin: 0;
            line-height: 1.6;
            color: #000000;
            font-size: 15px;
            font-family: "Quicksand", Sans-serif;
        }

        .branch-label {
            font-weight: 700;
            color: #000000;
        }

        .branch-detail a {
            color: #000000;
            text-decoration: none;
            font-weight: 700;
        }

        .branch-detail a:hover {
            color: #3B7D3B;
            text-decoration: underline;
        }

        /* ============================================ */
        /* VIDEO SECTION */
        /* ============================================ */
        .somot-video-section {
            margin: 60px 0;
            background-color: #FFFDF1;
            padding: 40px 0;
        }

        .somot-video-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin: 0 auto;
        }

        .somot-video-item {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 300px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .somot-video-item iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
        }

        /* ============================================ */
        /* FORM STYLES (Original) */
        /* ============================================ */
        .somot-reservation-wrapper {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        .somot-reservation-card {
            background: #ffffff;
            padding: 48px;
            border-radius: 12px;
            border: 1px solid #2d5016;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .reservation-title {
            color: #111827;
            margin: 15px 0 32px 0;
            text-align: center;
            font-size: 33px;
            font-weight: 800;
            line-height: 1.2em;
            letter-spacing: 2px;
            text-transform: capitalize;
            font-family: "Quicksand", Sans-serif;
        }

        .somot-reservation-form {
            width: 100%;
        }

        /* Two Column Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Form Groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-group-full {
            grid-column: 1 / -1;
            margin-top: 0;
        }

        .form-group-hidden {
            opacity: 0.5;
            pointer-events: none;
        }

        .form-group label {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.25rem;
            margin: 0;
            font-family: "Quicksand", Sans-serif;
        }

        .form-group .required {
            color: #dc2626;
            margin-left: 4px;
        }

        /* Form Controls */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            height: 44px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.5;
            color: #2d5016;
            background-color: #FFFDF1;
            transition: all 0.15s ease;
            box-sizing: border-box;
            outline: none;
            font-family: "Quicksand", Sans-serif;
            font-weight: 600;
        }

        .form-group textarea {
            min-height: 96px;
            height: auto;
            resize: vertical;
            padding: 12px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2d5016;
            box-shadow: 0 0 0 3px rgba(45, 80, 22, 0.1);
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
            padding-right: 40px;
        }

        .form-group select:disabled {
            background-color: #f9fafb;
            cursor: not-allowed;
            color: #9ca3af;
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .btn {
            height: 44px;
            padding: 0 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            outline: none;
        }

        .btn-primary {
            background-color: #2d5016;
            color: #FFFDF1;
            flex: 1;
            min-width: 200px;
            padding: 16px 24px;
        }

        .btn-primary:hover {
            background-color: #2d5016;
            opacity: 0.9;
        }

        .btn-primary:active {
            background-color: #1a3009;
        }

        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #2d5016;
            padding: 0 24px;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        /* Alert Messages */
        #reservation-messages {
            margin-bottom: 24px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 16px;
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert strong {
            font-weight: 500;
        }

        .alert-success {
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-info {
            background-color: #dbeafe;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .alert .close {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
            padding: 0;
            width: 24px;
            height: 24px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert .close:hover {
            opacity: 1;
        }

        /* jQuery UI Datepicker Customization */
        .ui-datepicker {
            font-family: inherit !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            padding: 8px !important;
        }

        .ui-datepicker-header {
            background: #2d5016 !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 8px !important;
        }

        .ui-datepicker-title {
            color: #ffffff !important;
        }

        .ui-datepicker-prev,
        .ui-datepicker-next {
            color: #ffffff !important;
        }

        .ui-state-default {
            text-align: center !important;
            border: 1px solid transparent !important;
            background: transparent !important;
        }

        .ui-state-default:hover {
            background: #f3f4f6 !important;
            border-color: #d1d5db !important;
        }

        .ui-state-highlight {
            background: #2d5016 !important;
            color: #ffffff !important;
            border-color: #2d5016 !important;
        }

        .ui-state-active {
            background: #1a3009 !important;
            color: #ffffff !important;
            border-color: #1a3009 !important;
        }

        /* ============================================ */
        /* RESPONSIVE DESIGN */
        /* ============================================ */
        @media (max-width: 1024px) {
            .somot-hero-title {
                font-size: 32px;
            }

            .somot-subtitle {
                font-size: 22px;
            }

            .branches-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .branches-title {
                font-size: 28px;
            }

            .somot-video-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .reservation-title {
                font-size: 26px;
                letter-spacing: 0.5px;
            }
        }

        @media (max-width: 768px) {
            .somot-hero-section {
                min-height: 300px;
                margin-bottom: 40px;
            }

            .somot-hero-title {
                font-size: 28px;
            }

            .somot-subtitle {
                font-size: 20px;
            }

            .somot-breadcrumb {
                font-size: 12px;
            }

            .somot-form-section {
                margin: 40px 0;
            }

            .somot-container {
                padding: 0 15px;
            }

            .somot-form-center {
                max-width: 100%;
            }

            .somot-reservation-card {
                padding: 24px;
                border-radius: 8px;
            }

            .reservation-title {
                font-size: 18px;
                margin-bottom: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-column {
                gap: 20px;
            }

            .form-group label {
                font-size: 15px;
            }

            .form-group input[type="text"],
            .form-group input[type="email"],
            .form-group input[type="tel"],
            .form-group input[type="number"],
            .form-group select,
            .form-group textarea {
                font-size: 13px;
            }

            .form-actions {
                flex-direction: column;
                gap: 12px;
            }

            .btn {
                width: 100%;
                min-width: 0;
            }

            /* Branches Section Mobile */
            .somot-branches-section {
                margin-top: 40px;
                padding: 30px 0;
            }

            .branches-title {
                font-size: 24px;
                margin-bottom: 30px;
            }

            .branches-grid {
                gap: 20px;
            }

            .branch-card {
                padding: 20px;
            }

            .branch-name {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .branch-detail {
                font-size: 14px;
            }

            .somot-video-section {
                padding: 30px 0;
                margin: 40px 0;
            }

            .somot-video-grid {
                gap: 15px;
            }
        }

        /* Loading State */
        .form-group input:disabled,
        .form-group select:disabled,
        .form-group textarea:disabled {
            background-color: #f9fafb;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Focus visible for accessibility */
        *:focus-visible {
            outline: 2px solid #2d5016;
            outline-offset: 2px;
        }

        /* Error state */
        .form-group input.error,
        .form-group select.error {
            border-color: #dc2626;
        }
    </style>
    <?php
}

// ============================================================================
// INLINE JAVASCRIPT
// ============================================================================

function somot_reservation_inline_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            'use strict';

            // Initialize datepicker
            $('#reservation_date').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                maxDate: '+3M',
                beforeShowDay: function(date) {
                    return [date >= new Date().setHours(0,0,0,0)];
                },
                onSelect: function(dateText) {
                    // loadTimeSlots();
                }
            });

            // Load time slots when date or branch changes
            $('#branch_id').on('change', function() {
                if ($('#reservation_date').val()) {
                    // loadTimeSlots();
                }
            });

            // Load available time slots
            function loadTimeSlots() {
                var date = $('#reservation_date').val();
                var branchId = $('#branch_id').val();

                if (!date || !branchId) {
                    return;
                }

                $('#reservation_time').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: somot_reservation.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'get_time_slots',
                        nonce: somot_reservation.nonce,
                        date: date,
                        branch_id: branchId
                    },
                    success: function(response) {
                        if (response.success) {
                            var options = '<option value="">Select time</option>';
                            
                            $.each(response.data.slots, function(index, slot) {
                                if (slot.available) {
                                    options += '<option value="' + slot.value + '">' + slot.label + '</option>';
                                } else {
                                    options += '<option value="' + slot.value + '" disabled>' + slot.label + ' (Booked)</option>';
                                }
                            });
                            
                            $('#reservation_time').html(options).prop('disabled', false);
                        } else {
                            showMessage('error', response.data.message || 'Failed to load time slots.');
                            $('#reservation_time').html('<option value="">Select time</option>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        showMessage('error', 'Failed to load time slots. Please try again.');
                        $('#reservation_time').html('<option value="">Select time</option>').prop('disabled', false);
                    }
                });
            }

            // Form submission
            $('#somot-reservation-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $submitBtn = $('#submit-reservation');
                var formData = $form.serialize();

                $submitBtn.prop('disabled', true).text('Submitting...');
                $('#reservation-messages').empty();

                $.ajax({
                    url: somot_reservation.ajax_url,
                    type: 'POST',
                    data: formData + '&action=submit_reservation&nonce=' + somot_reservation.nonce,
                    success: function(response) {
                        if (response.success) {
                            showMessage('success', response.data.message);
                            $form[0].reset();
                            $('#reservation_time').html('<option value="">Select time</option>');
                            
                            $('html, body').animate({
                                scrollTop: $('#reservation-messages').offset().top - 100
                            }, 500);
                        } else {
                            showMessage('error', response.data.message || 'Failed to submit reservation. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'An error occurred. Please try again later.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMsg = xhr.responseJSON.data.message;
                        }
                        
                        showMessage('error', errorMsg);
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text('Book a table');
                    }
                });
            });

            // Request cancellation
            $('#request-cancellation').on('click', function() {
                alert('To cancel your reservation, please contact us directly via phone or email.');
            });

            // Show message function
            function showMessage(type, message) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                var iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                
                var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                           '<strong>' + message + '</strong>' +
                           '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                           '<span aria-hidden="true">&times;</span>' +
                           '</button>' +
                           '</div>';
                
                $('#reservation-messages').html(html);
                
                // Manual close button handler
                $('.alert .close').on('click', function() {
                    $(this).closest('.alert').fadeOut(function() {
                        $(this).remove();
                    });
                });
                
                // Auto dismiss after 10 seconds
                setTimeout(function() {
                    $('#reservation-messages .alert').fadeOut(function() {
                        $(this).remove();
                    });
                }, 10000);
            }

            // Phone number formatting
            $('#customer_phone').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                if (value.length > 0 && value[0] !== '+') {
                    if (value.length <= 12) {
                        $(this).val('+' + value);
                    }
                }
            });

            // Email validation
            $('#customer_email').on('blur', function() {
                var email = $(this).val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('error');
                    showMessage('error', 'Please enter a valid email address.');
                } else {
                    $(this).removeClass('error');
                }
            });

            // Number of guests validation
            $('#number_of_guests').on('change input', function() {
                var guests = parseInt($(this).val());
                
                if (guests > 20) {
                    showMessage('info', 'For parties larger than 20 guests, please contact us directly to arrange your reservation.');
                }
                
                if (guests < 1) {
                    $(this).val(1);
                }
                
                if (guests > 100) {
                    $(this).val(100);
                }
            });
        });
    </script>
    <?php
}

// ============================================================================
// AJAX HANDLERS
// ============================================================================

// Handle form submission
function somot_handle_reservation_submission() {
    check_ajax_referer('somot_reservation_nonce', 'nonce');
    
    $customer_name = sanitize_text_field($_POST['customer_name']);
    $customer_email = sanitize_email($_POST['customer_email']);
    $customer_phone = sanitize_text_field($_POST['customer_phone']);
    $number_of_guests = intval($_POST['number_of_guests']);
    $branch_id = intval($_POST['branch_id']);
    $reservation_date = sanitize_text_field($_POST['reservation_date']);
    $reservation_time = sanitize_text_field($_POST['reservation_time']);
    $additional_notes = sanitize_textarea_field($_POST['additional_notes']);
    
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || 
        empty($number_of_guests) || empty($branch_id) || empty($reservation_date) || empty($reservation_time)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        return;
    }
    
    if (!is_email($customer_email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        return;
    }
    
    $branches = somot_get_branches();
    $branch = null;
    foreach ($branches as $b) {
        if ($b['id'] == $branch_id) {
            $branch = $b;
            break;
        }
    }
    
    if (!$branch) {
        wp_send_json_error(array('message' => 'Invalid branch selected.'));
        return;
    }
    
    $post_id = wp_insert_post(array(
        'post_type' => 'reservation',
        'post_title' => $customer_name . ' - ' . $reservation_date . ' ' . $reservation_time,
        'post_status' => 'publish',
    ));
    
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to create reservation. Please try again.'));
        return;
    }
    
    $api_data = array(
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'number_of_guests' => $number_of_guests,
        'branch_id' => $branch_id,
        'reservation_date' => $reservation_date,
        'reservation_time' => $reservation_time,
        'additional_notes' => $additional_notes,
        'wordpress_post_id' => $post_id,
    );
    
    $response = wp_remote_post(GOODRIVER_API_URL . '/reservations', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => GOODRIVER_API_KEY,
        ),
        'body' => json_encode($api_data),
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        wp_delete_post($post_id, true);
        wp_send_json_error(array('message' => 'Failed to connect to reservation system. Please try again later.'));
        return;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!$body['success']) {
        wp_delete_post($post_id, true);
        $error_message = isset($body['message']) ? $body['message'] : 'Failed to create reservation.';
        wp_send_json_error(array('message' => $error_message));
        return;
    }
    
    $reservation_data = $body['data'];
    update_post_meta($post_id, '_customer_name', $customer_name);
    update_post_meta($post_id, '_customer_email', $customer_email);
    update_post_meta($post_id, '_customer_phone', $customer_phone);
    update_post_meta($post_id, '_number_of_guests', $number_of_guests);
    update_post_meta($post_id, '_branch_id', $branch_id);
    update_post_meta($post_id, '_branch_name', $branch['name']);
    update_post_meta($post_id, '_reservation_date', $reservation_date);
    update_post_meta($post_id, '_reservation_time', $reservation_time);
    update_post_meta($post_id, '_additional_notes', $additional_notes);
    update_post_meta($post_id, '_goodriver_id', $reservation_data['id']);
    update_post_meta($post_id, '_order_key', $reservation_data['order_key']);
    update_post_meta($post_id, '_status', 'Waiting');
    
    wp_send_json_success(array(
        'message' => 'Your reservation has been submitted successfully! We will contact you shortly to confirm.',
        'reservation' => $reservation_data,
    ));
}
add_action('wp_ajax_submit_reservation', 'somot_handle_reservation_submission');
add_action('wp_ajax_nopriv_submit_reservation', 'somot_handle_reservation_submission');

// Get available time slots
function somot_get_available_time_slots() {
    check_ajax_referer('somot_reservation_nonce', 'nonce');
    
    $date = sanitize_text_field($_POST['date']);
    $branch_id = intval($_POST['branch_id']);
    
    if (empty($date) || empty($branch_id)) {
        wp_send_json_error(array('message' => 'Invalid parameters.'));
        return;
    }
    
    $response = wp_remote_post(GOODRIVER_API_URL . '/reservations/check-availability', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => GOODRIVER_API_KEY,
        ),
        'body' => json_encode(array(
            'date' => $date,
            'branch_id' => $branch_id,
        )),
        'timeout' => 15,
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to check availability.'));
        return;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!$body['success']) {
        wp_send_json_error(array('message' => 'Failed to check availability.'));
        return;
    }
    
    $slots = array();
    $start_time = strtotime('10:00');
    $end_time = strtotime('22:00');
    $interval = 30 * 60;
    
    $booked_slots = isset($body['data']['booked_slots']) ? $body['data']['booked_slots'] : array();
    
    for ($time = $start_time; $time <= $end_time; $time += $interval) {
        $time_string = date('H:i', $time);
        $time_display = date('g:i A', $time);
        
        $slots[] = array(
            'value' => $time_string,
            'label' => $time_display,
            'available' => !in_array($time_string, $booked_slots),
        );
    }
    
    wp_send_json_success(array('slots' => $slots));
}
add_action('wp_ajax_get_time_slots', 'somot_get_available_time_slots');
add_action('wp_ajax_nopriv_get_time_slots', 'somot_get_available_time_slots');
