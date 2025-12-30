/**
 * My Account Test - Đúng Layout Gốc
 * Layout: MY ACCOUNT header + Sidebar bên trái + Tabs + Content
 */

// 1. Tạo page (hoặc update page có sẵn)
add_action('init', 'create_my_account_test_page_v2');
function create_my_account_test_page_v2() {
    $page_slug = 'my-account-test';
    $page_check = get_page_by_path($page_slug);
    
    if (!$page_check) {
        wp_insert_post(array(
            'post_title'    => 'My Account Test',
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '[my_account_test_content]',
        ));
    }
}

// 2. Shortcode - PHẢI DÙNG TÊN NÀY để khớp với page có sẵn
add_shortcode('my_account_test_content', 'my_account_test_v2_func');
function my_account_test_v2_func() {
    if (!is_user_logged_in()) {
        return my_account_login_form_v2();
    }
    
    $current_user = wp_get_current_user();
    
    ob_start();
    ?>
    <style>
        * { box-sizing: border-box; }
        
        /* Header */
        .mat-header {
            background: #2d6a4f;
            padding: 40px 20px;
            text-align: center;
            margin: -20px -20px 0 -20px;
        }
        
        .mat-header h1 {
            color: #fff;
            font-size: 48px;
            margin: 0;
            font-weight: 300;
            letter-spacing: 3px;
            font-family: Georgia, serif;
        }
        
        /* Wrapper */
        .mat-wrapper {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
        }
        
        /* Sidebar */
        .mat-sidebar {
            width: 280px;
            background: #f5f5f5;
            padding: 30px 20px;
        }
        
        .mat-user-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .mat-avatar img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .mat-greeting {
            display: flex;
            flex-direction: column;
            color: #2d6a4f;
            margin-top: 15px;
        }
        
        .mat-hi {
            font-size: 16px;
        }
        
        .mat-username {
            font-size: 14px;
            font-weight: 600;
            word-break: break-word;
        }
        
        .mat-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mat-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border-radius: 8px;
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .mat-menu-item:hover {
            background: #2d6a4f;
            color: #fff;
        }
        
        .mat-icon {
            font-size: 18px;
        }
        
        .mat-home {
            margin-top: 10px;
            background: #2d6a4f !important;
            color: #fff !important;
        }
        
        /* Main */
        .mat-main {
            flex: 1;
        }
        
        /* Tabs */
        .mat-tabs {
            display: flex;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .mat-tab {
            flex: 1;
            padding: 18px 15px;
            text-align: center;
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        
        .mat-tab:hover {
            background: #f8f9fa;
        }
        
        .mat-tab.active {
            border-bottom-color: #2d6a4f;
            font-weight: 600;
        }
        
        .mat-tab-icon {
            font-size: 16px;
        }
        
        /* Content */
        .mat-content {
            padding: 30px;
        }
        
        .mat-section {
            display: none;
        }
        
        .mat-section.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Orders */
        .mat-orders {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .mat-order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .mat-order-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .mat-order-thumb {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .mat-order-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .mat-order-details h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #2d6a4f;
        }
        
        .mat-order-details p {
            margin: 3px 0;
            font-size: 14px;
            color: #666;
        }
        
        .mat-order-actions {
            display: flex;
            gap: 10px;
        }
        
        .mat-btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .mat-btn-pay {
            background: #2d6a4f;
            color: #fff;
        }
        
        .mat-btn-view {
            background: #fff;
            color: #2d6a4f;
            border: 2px solid #2d6a4f;
        }
        
        .mat-btn-cancel {
            background: #fff;
            color: #d32f2f;
            border: 2px solid #d32f2f;
        }
        
        /* Downloads */
        .mat-downloads-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .mat-downloads-table th,
        .mat-downloads-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .mat-downloads-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2d6a4f;
        }
        
        .mat-btn-download {
            padding: 6px 12px;
            background: #2d6a4f;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        
        /* Reservations */
        .mat-reservations {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        
        .mat-res-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2d6a4f;
        }
        
        .mat-res-waiting,
        .mat-res-pending {
            border-left-color: #ff9800;
        }
        
        .mat-res-confirmed {
            border-left-color: #4caf50;
        }
        
        .mat-res-cancelled {
            border-left-color: #f44336;
            opacity: 0.7;
        }
        
        .mat-res-past {
            border-left-color: #9e9e9e;
            opacity: 0.8;
        }
        
        .mat-res-info {
            flex: 1;
        }
        
        .mat-res-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .mat-res-datetime {
            display: flex;
            gap: 20px;
            font-weight: 600;
            color: #2d6a4f;
        }
        
        .mat-res-details p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .mat-res-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .mat-res-badge.mat-res-waiting,
        .mat-res-badge.mat-res-pending {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .mat-res-badge.mat-res-confirmed {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .mat-res-badge.mat-res-cancelled {
            background: #ffebee;
            color: #f44336;
        }
        
        .mat-res-badge.mat-res-completed {
            background: #f5f5f5;
            color: #9e9e9e;
        }
        
        .mat-res-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .mat-no-data {
            padding: 40px;
            text-align: center;
            color: #999;
        }
        
        /* Addresses */
        .mat-addresses {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 20px;
        }
        
        .mat-address-block {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .mat-address-block h3 {
            color: #2d6a4f;
            margin: 0 0 15px;
        }
        
        .mat-address-block address {
            font-style: normal;
            line-height: 1.8;
            color: #666;
        }
        
        /* Form */
        .mat-form-row {
            display: flex;
            gap: 20px;
        }
        
        .mat-form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .mat-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .mat-form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
        }
        
        .mat-form-group input:focus {
            outline: none;
            border-color: #2d6a4f;
        }
        
        .mat-form-group small {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #999;
        }
        
        .mat-btn-save {
            padding: 12px 30px;
            background: #40b9d4;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Login */
        .mat-login {
            max-width: 500px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .mat-login h2 {
            text-align: center;
            color: #2d6a4f;
            margin-bottom: 30px;
        }
        
        .mat-social-login {
            margin-bottom: 20px;
        }
        
        .mat-btn-social {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 15px;
        }
        
        .mat-btn-fb {
            color: #1877f2;
            border-color: #1877f2;
        }
        
        .mat-btn-gg {
            color: #db4437;
            border-color: #db4437;
        }
        
        .mat-btn-login {
            width: 100%;
            padding: 12px;
            background: #40b9d4;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .mat-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
        }
        
        .mat-lost-pw,
        .mat-register {
            text-align: center;
            margin-top: 15px;
        }
        
        .mat-lost-pw a,
        .mat-register a {
            color: #d32f2f;
            text-decoration: none;
        }
        
        .mat-payment-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .mat-payment-list li {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .mat-wrapper {
                flex-direction: column;
            }
            
            .mat-sidebar {
                width: 100%;
            }
            
            .mat-tabs {
                flex-wrap: wrap;
            }
            
            .mat-tab {
                flex: 1 1 50%;
            }
            
            .mat-addresses {
                grid-template-columns: 1fr;
            }
            
            .mat-order-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .mat-order-actions {
                width: 100%;
            }
            
            .mat-res-item {
                flex-direction: column;
            }
            
            .mat-res-actions {
                width: 100%;
                flex-direction: row;
                margin-top: 15px;
            }
        }
    </style>
    
    <!-- MY ACCOUNT Header -->
    <div class="mat-header">
        <h1>MY ACCOUNT</h1>
    </div>
    
    <div class="mat-wrapper">
        <!-- Sidebar -->
        <div class="mat-sidebar">
            <div class="mat-user-info">
                <div class="mat-avatar">
                    <?php echo get_avatar(get_current_user_id(), 100); ?>
                </div>
                <div class="mat-greeting">
                    <span class="mat-hi">Hi</span>
                    <span class="mat-username"><?php echo esc_html($current_user->display_name); ?></span>
                </div>
            </div>
            
            <nav class="mat-menu">
                <a href="#address" class="mat-menu-item" data-tab="address">
                    <span class="mat-icon">📍</span>
                    <span>Address</span>
                </a>
                <a href="#account-details" class="mat-menu-item" data-tab="account-details">
                    <span class="mat-icon">📋</span>
                    <span>Account Details</span>
                </a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="mat-menu-item">
                    <span class="mat-icon">🔓</span>
                    <span>Log Out</span>
                </a>
                <a href="<?php echo home_url(); ?>" class="mat-menu-item mat-home">
                    <span class="mat-icon">🏠</span>
                    <span>Home</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Area -->
        <div class="mat-main">
            <!-- Tabs -->
            <div class="mat-tabs">
                <a href="#dashboard" class="mat-tab active" data-content="dashboard">
                    <span class="mat-tab-icon">👤</span> Dashboard
                </a>
                <a href="#orders" class="mat-tab" data-content="orders">
                    <span class="mat-tab-icon">🛒</span> Order
                </a>
                <a href="#downloads" class="mat-tab" data-content="downloads">
                    <span class="mat-tab-icon">⬇️</span> Download
                </a>
                <a href="#payment" class="mat-tab" data-content="payment">
                    <span class="mat-tab-icon">💳</span> Payment
                </a>
                <a href="#reservations" class="mat-tab" data-content="reservations">
                    <span class="mat-tab-icon">📅</span> Reservations
                </a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="mat-tab mat-tab-link">
                    <span class="mat-tab-icon">🛒</span> Cart
                </a>
            </div>
            
            <!-- Content -->
            <div class="mat-content">
                <!-- Dashboard -->
                <div class="mat-section active" id="dashboard">
                    <?php echo get_dashboard_v2(); ?>
                </div>
                
                <!-- Orders -->
                <div class="mat-section" id="orders">
                    <?php echo get_orders_v2(); ?>
                </div>
                
                <!-- Downloads -->
                <div class="mat-section" id="downloads">
                    <?php echo get_downloads_v2(); ?>
                </div>
                
                <!-- Payment -->
                <div class="mat-section" id="payment">
                    <?php echo get_payment_v2(); ?>
                </div>
                
                <!-- Reservations -->
                <div class="mat-section" id="reservations">
                    <?php echo get_reservations_v2(); ?>
                </div>
                
                <!-- Address -->
                <div class="mat-section" id="address">
                    <?php echo get_address_v2(); ?>
                </div>
                
                <!-- Account Details -->
                <div class="mat-section" id="account-details">
                    <?php echo get_account_details_v2(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Content functions
function get_dashboard_v2() {
    $user = wp_get_current_user();
    ob_start();
    ?>
    <p>Hello <strong><?php echo esc_html($user->display_name); ?></strong> 
    (not <strong><?php echo esc_html($user->display_name); ?></strong>? 
    <a href="<?php echo wp_logout_url(); ?>">Log out</a>)</p>
    
    <p>From your account dashboard you can view your 
    <a href="#" class="mat-link-orders">recent orders</a>, 
    manage your <a href="#" class="mat-link-address">shipping and billing addresses</a>, 
    and <a href="#" class="mat-link-account">edit your password and account details</a>.</p>
    <?php
    return ob_get_clean();
}

function get_orders_v2() {
    if (!class_exists('WooCommerce')) return '<p>WooCommerce not active.</p>';
    
    $orders = wc_get_orders(array(
        'customer' => get_current_user_id(),
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
    ));
    
    ob_start();
    ?>
    <div class="mat-orders">
        <?php if ($orders) : foreach ($orders as $order) : ?>
            <div class="mat-order-item">
                <div class="mat-order-info">
                    <?php
                    $items = $order->get_items();
                    $first_item = reset($items);
                    if ($first_item) {
                        $product = $first_item->get_product();
                        if ($product && $product->get_image_id()) {
                            echo '<div class="mat-order-thumb">' . $product->get_image('thumbnail') . '</div>';
                        }
                    }
                    ?>
                    <div class="mat-order-details">
                        <h3><?php echo $order->get_date_created()->format('Y-m-d'); ?></h3>
                        <p>Status - <?php echo wc_get_order_status_name($order->get_status()); ?></p>
                        <p><?php echo $order->get_formatted_order_total(); ?> for <?php echo $order->get_item_count(); ?> items</p>
                    </div>
                </div>
                <div class="mat-order-actions">
                    <?php if ($order->needs_payment()) : ?>
                        <a href="<?php echo $order->get_checkout_payment_url(); ?>" class="mat-btn mat-btn-pay">Tracking</a>
                    <?php endif; ?>
                    <?php if ($order->needs_payment()) : ?>
                        <a href="<?php echo $order->get_checkout_payment_url(); ?>" class="mat-btn mat-btn-pay">Pay</a>
                    <?php endif; ?>
                    <a href="<?php echo $order->get_view_order_url(); ?>" class="mat-btn mat-btn-view">View</a>
                    <?php if (in_array($order->get_status(), array('pending', 'on-hold'))) : ?>
                        <a href="<?php echo $order->get_cancel_order_url(); ?>" class="mat-btn mat-btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; else : ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function get_downloads_v2() {
    if (!class_exists('WooCommerce')) return '<p>WooCommerce not active.</p>';
    
    $downloads = WC()->customer->get_downloadable_products();
    
    ob_start();
    ?>
    <?php if ($downloads) : ?>
        <table class="mat-downloads-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Downloads remaining</th>
                    <th>Expires</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($downloads as $download) : ?>
                    <tr>
                        <td><?php echo esc_html($download['product_name']); ?></td>
                        <td><?php echo is_numeric($download['downloads_remaining']) ? $download['downloads_remaining'] : '∞'; ?></td>
                        <td><?php echo $download['access_expires'] ? date('Y-m-d', strtotime($download['access_expires'])) : 'Never'; ?></td>
                        <td><a href="<?php echo esc_url($download['download_url']); ?>" class="mat-btn-download">Download</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No downloads available.</p>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function get_payment_v2() {
    if (!class_exists('WooCommerce')) return '<p>WooCommerce not active.</p>';
    
    $saved_methods = wc_get_customer_saved_methods_list(get_current_user_id());
    
    ob_start();
    ?>
    <h2>Payment Methods</h2>
    <?php if (!empty($saved_methods)) : ?>
        <ul class="mat-payment-list">
            <?php foreach ($saved_methods as $type => $methods) : ?>
                <?php foreach ($methods as $method) : ?>
                    <li><?php echo esc_html($method['method']['last4'] ?? $type); ?> - Expires <?php echo esc_html($method['expires'] ?? 'N/A'); ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No saved payment methods.</p>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function get_reservations_v2() {
    $user = wp_get_current_user();
    
    // Tìm post type
    $possible_types = array('reservation', 'reservations', 'booking', 'bookings', 'rtb-booking');
    $post_type = '';
    
    foreach ($possible_types as $pt) {
        if (post_type_exists($pt)) {
            $post_type = $pt;
            break;
        }
    }
    
    if (empty($post_type)) {
        return '<p>Reservation system not configured.</p>';
    }
    
    // Lấy reservations
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array('key' => 'email', 'value' => $user->user_email, 'compare' => '='),
            array('key' => 'customer_email', 'value' => $user->user_email, 'compare' => '='),
        ),
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $reservations = get_posts($args);
    
    if (empty($reservations)) {
        $args = array(
            'post_type' => $post_type,
            'author' => get_current_user_id(),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        $reservations = get_posts($args);
    }
    
    // Tách upcoming/past
    $upcoming = array();
    $past = array();
    $today = current_time('Y-m-d');
    
    foreach ($reservations as $res) {
        $date = get_post_meta($res->ID, 'reservation_date', true) ?: get_post_meta($res->ID, 'date', true);
        if (strtotime($date)) {
            $date = date('Y-m-d', strtotime($date));
        }
        
        if ($date >= $today) {
            $upcoming[] = $res;
        } else {
            $past[] = $res;
        }
    }
    
    ob_start();
    ?>
    <h2>My Reservations</h2>
    
    <h3>Upcoming Reservations</h3>
    <?php if (!empty($upcoming)) : ?>
        <div class="mat-reservations">
            <?php foreach ($upcoming as $res) : 
                $order_key = get_post_meta($res->ID, 'order_key', true);
                $status = get_post_meta($res->ID, 'status', true);
                $guests = get_post_meta($res->ID, 'number_of_guests', true) ?: get_post_meta($res->ID, 'party', true);
                $date = get_post_meta($res->ID, 'reservation_date', true) ?: get_post_meta($res->ID, 'date', true);
                $time = get_post_meta($res->ID, 'reservation_time', true) ?: get_post_meta($res->ID, 'time', true);
                $branch = get_post_meta($res->ID, 'branch', true);
                $phone = get_post_meta($res->ID, 'phone', true);
                
                if (strtotime($date)) $date = date('Y-m-d', strtotime($date));
            ?>
            <div class="mat-res-item mat-res-<?php echo esc_attr(strtolower($status)); ?>">
                <div class="mat-res-info">
                    <div class="mat-res-header">
                        <div class="mat-res-datetime">
                            <span>📅 <?php echo esc_html($date); ?></span>
                            <span>🕐 <?php echo esc_html($time); ?></span>
                        </div>
                        <span class="mat-res-badge mat-res-<?php echo esc_attr(strtolower($status)); ?>">
                            <?php echo esc_html(ucfirst($status)); ?>
                        </span>
                    </div>
                    <div class="mat-res-details">
                        <?php if ($order_key) : ?><p><strong>Order:</strong> <?php echo esc_html($order_key); ?></p><?php endif; ?>
                        <p><strong>Guests:</strong> <?php echo esc_html($guests); ?> people</p>
                        <?php if ($branch) : ?><p><strong>Branch:</strong> <?php echo esc_html($branch); ?></p><?php endif; ?>
                        <?php if ($phone) : ?><p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p><?php endif; ?>
                    </div>
                </div>
                <div class="mat-res-actions">
                    <a href="<?php echo get_edit_post_link($res->ID); ?>" class="mat-btn mat-btn-view" target="_blank">View</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="mat-no-data">No upcoming reservations.</p>
    <?php endif; ?>
    
    <?php if (!empty($past)) : ?>
        <h3>Past Reservations</h3>
        <div class="mat-reservations">
            <?php foreach ($past as $res) : 
                $date = get_post_meta($res->ID, 'reservation_date', true) ?: get_post_meta($res->ID, 'date', true);
                $time = get_post_meta($res->ID, 'reservation_time', true) ?: get_post_meta($res->ID, 'time', true);
                $guests = get_post_meta($res->ID, 'number_of_guests', true) ?: get_post_meta($res->ID, 'party', true);
                $status = get_post_meta($res->ID, 'status', true);
                
                if (strtotime($date)) $date = date('Y-m-d', strtotime($date));
            ?>
            <div class="mat-res-item mat-res-past">
                <div class="mat-res-info">
                    <span>📅 <?php echo esc_html($date); ?> at <?php echo esc_html($time); ?></span>
                    <span><?php echo esc_html($guests); ?> Guests</span>
                </div>
                <span class="mat-res-badge mat-res-<?php echo esc_attr(strtolower($status)); ?>">
                    <?php echo esc_html(ucfirst($status)); ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function get_address_v2() {
    if (!class_exists('WooCommerce')) return '<p>WooCommerce not active.</p>';
    
    $customer = new WC_Customer(get_current_user_id());
    
    ob_start();
    ?>
    <h2>Addresses</h2>
    <div class="mat-addresses">
        <div class="mat-address-block">
            <h3>Billing Address</h3>
            <address>
                <?php
                $billing_first = $customer->get_billing_first_name();
                $billing_address = $customer->get_billing_address_1();
                
                if ($billing_first || $billing_address) {
                    echo esc_html($billing_first . ' ' . $customer->get_billing_last_name()) . '<br>';
                    if ($customer->get_billing_company()) echo esc_html($customer->get_billing_company()) . '<br>';
                    echo esc_html($billing_address) . '<br>';
                    if ($customer->get_billing_address_2()) echo esc_html($customer->get_billing_address_2()) . '<br>';
                    echo esc_html($customer->get_billing_city() . ', ' . $customer->get_billing_state() . ' ' . $customer->get_billing_postcode());
                } else {
                    echo 'You have not set up this type of address yet.';
                }
                ?>
            </address>
        </div>
        
        <div class="mat-address-block">
            <h3>Shipping Address</h3>
            <address>
                <?php
                $shipping_first = $customer->get_shipping_first_name();
                $shipping_address = $customer->get_shipping_address_1();
                
                if ($shipping_first || $shipping_address) {
                    echo esc_html($shipping_first . ' ' . $customer->get_shipping_last_name()) . '<br>';
                    if ($customer->get_shipping_company()) echo esc_html($customer->get_shipping_company()) . '<br>';
                    echo esc_html($shipping_address) . '<br>';
                    if ($customer->get_shipping_address_2()) echo esc_html($customer->get_shipping_address_2()) . '<br>';
                    echo esc_html($customer->get_shipping_city() . ', ' . $customer->get_shipping_state() . ' ' . $customer->get_shipping_postcode());
                } else {
                    echo 'You have not set up this type of address yet.';
                }
                ?>
            </address>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function get_account_details_v2() {
    $user = wp_get_current_user();
    
    ob_start();
    ?>
    <form method="post" action="" class="mat-form">
        <div class="mat-form-row">
            <div class="mat-form-group">
                <label>First name *</label>
                <input type="text" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" required>
            </div>
            <div class="mat-form-group">
                <label>Last name *</label>
                <input type="text" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" required>
            </div>
        </div>
        
        <div class="mat-form-group">
            <label>Display name *</label>
            <input type="text" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" required>
            <small>This will be how your name will be displayed in the account section and in reviews</small>
        </div>
        
        <div class="mat-form-group">
            <label>Email address *</label>
            <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
        </div>
        
        <h3>Password change</h3>
        
        <div class="mat-form-group">
            <label>Current password (leave blank to leave unchanged)</label>
            <input type="password" name="current_password">
        </div>
        
        <div class="mat-form-group">
            <label>New password (leave blank to leave unchanged)</label>
            <input type="password" name="new_password">
        </div>
        
        <div class="mat-form-group">
            <label>Confirm new password</label>
            <input type="password" name="confirm_password">
        </div>
        
        <button type="submit" name="save_account_v2" class="mat-btn-save">Save changes</button>
        <?php wp_nonce_field('save_account_v2', 'save_account_v2_nonce'); ?>
    </form>
    <?php
    return ob_get_clean();
}

function my_account_login_form_v2() {
    ob_start();
    ?>
    <div class="mat-login">
        <h2>Login</h2>
        
        <div class="mat-social-login">
            <button class="mat-btn-social mat-btn-fb">
                <span>f</span> Login with Facebook
            </button>
            <button class="mat-btn-social mat-btn-gg">
                <span>G</span> Login with Google
            </button>
        </div>
        
        <form method="post" action="<?php echo esc_url(wp_login_url()); ?>">
            <div class="mat-form-group">
                <label>Username or email address *</label>
                <input type="text" name="log" required>
            </div>
            
            <div class="mat-form-group">
                <label>Password *</label>
                <input type="password" name="pwd" required>
            </div>
            
            <div class="mat-checkbox">
                <input type="checkbox" name="rememberme" value="forever" id="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="mat-btn-login">Log in</button>
            
            <p class="mat-lost-pw"><a href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a></p>
            <p class="mat-register">Not a member? <a href="<?php echo wp_registration_url(); ?>">Register</a></p>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

// Save account details
add_action('init', 'handle_save_account_v2');
function handle_save_account_v2() {
    if (!isset($_POST['save_account_v2']) || !isset($_POST['save_account_v2_nonce'])) return;
    if (!wp_verify_nonce($_POST['save_account_v2_nonce'], 'save_account_v2')) return;
    if (!is_user_logged_in()) return;
    
    $user_id = get_current_user_id();
    
    $data = array(
        'ID' => $user_id,
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'display_name' => sanitize_text_field($_POST['display_name']),
        'user_email' => sanitize_email($_POST['email']),
    );
    
    if (!empty($_POST['new_password']) && $_POST['new_password'] === $_POST['confirm_password']) {
        $current_user = wp_get_current_user();
        if (!empty($_POST['current_password']) && wp_check_password($_POST['current_password'], $current_user->user_pass, $user_id)) {
            $data['user_pass'] = $_POST['new_password'];
        }
    }
    
    wp_update_user($data);
    wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
    exit;
}

// JS
add_action('wp_footer', 'mat_scripts');
function mat_scripts() {
    if (!is_page('my-account-test')) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Tab switching
        $('.mat-tab').on('click', function(e) {
            if ($(this).hasClass('mat-tab-link')) return true;
            
            e.preventDefault();
            var id = $(this).attr('href').substring(1);
            
            $('.mat-tab').removeClass('active');
            $(this).addClass('active');
            
            $('.mat-section').removeClass('active');
            $('#' + id).addClass('active');
        });
        
        // Sidebar menu
        $('.mat-menu-item[data-tab]').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            
            $('.mat-section').removeClass('active');
            $('#' + tab).addClass('active');
            
            $('.mat-tab').removeClass('active');
        });
        
        // Quick links
        $('.mat-link-orders').on('click', function(e) {
            e.preventDefault();
            $('.mat-tab[href="#orders"]').click();
        });
        
        $('.mat-link-address').on('click', function(e) {
            e.preventDefault();
            $('.mat-menu-item[data-tab="address"]').click();
        });
        
        $('.mat-link-account').on('click', function(e) {
            e.preventDefault();
            $('.mat-menu-item[data-tab="account-details"]').click();
        });
    });
    </script>
    <?php
}
