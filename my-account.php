<?php
/**
 * My Account - OPTION 1: Toggle Inline Login/Register
 * - Single page /my-account với toggle giữa Login và Register
 * - Tích hợp XS Social Login (WSLU plugin)
 * - Layout đẹp với header, sidebar, tabs
 */

// ============================================
// 1. MY ACCOUNT PAGE
// ============================================

add_action('init', 'create_my_account_inline');
function create_my_account_inline() {
    $page_slug = 'my-account';
    $page_check = get_page_by_path($page_slug);
    
    if (!$page_check) {
        wp_insert_post(array(
            'post_title'    => 'My account',
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '[my_account_inline]',
        ));
    } else {
        wp_update_post(array(
            'ID'           => $page_check->ID,
            'post_content' => '[my_account_inline]',
        ));
    }
}

// ============================================
// LOST PASSWORD PAGE
// ============================================

add_action('init', 'create_lost_password_page');
function create_lost_password_page() {
    $page_slug = 'lost-password';
    $page_check = get_page_by_path($page_slug);
    
    if (!$page_check) {
        wp_insert_post(array(
            'post_title'    => 'Lost Password',
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '[lost_password_form]',
        ));
    }
}

// Shortcode Lost Password Form
add_shortcode('lost_password_form', 'lost_password_form_func');
function lost_password_form_func() {
    // Xử lý form submission
    $message = '';
    $error = '';
    
    if (isset($_POST['reset_password'])) {
        if (!isset($_POST['reset_password_nonce']) || !wp_verify_nonce($_POST['reset_password_nonce'], 'reset_password')) {
            $error = 'Invalid request.';
        } else {
            $email = sanitize_email($_POST['user_login']);
            
            if (empty($email)) {
                $error = 'Please enter your email address.';
            } elseif (!is_email($email)) {
                $error = 'Please enter a valid email address.';
            } elseif (!email_exists($email)) {
                $error = 'There is no account with that email address.';
            } else {
                $user = get_user_by('email', $email);
                
                if ($user) {
                    $reset_key = get_password_reset_key($user);
                    
                    if (!is_wp_error($reset_key)) {
                        $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
                        
                        $email_message = "Hi " . $user->display_name . ",\n\n";
                        $email_message .= "You requested to reset your password for your account at " . get_bloginfo('name') . ".\n\n";
                        $email_message .= "To reset your password, please click the link below:\n";
                        $email_message .= $reset_url . "\n\n";
                        $email_message .= "If you did not request this, please ignore this email.\n\n";
                        $email_message .= "This link will expire in 24 hours.\n\n";
                        $email_message .= "Thanks!";
                        
                        $sent = wp_mail(
                            $email,
                            'Password Reset Request - ' . get_bloginfo('name'),
                            $email_message
                        );
                        
                        if ($sent) {
                            $message = 'Check your email for the confirmation link, then visit the <a href="' . home_url('/my-account') . '">login page</a>.';
                        } else {
                            $error = 'Failed to send email. Please try again.';
                        }
                    } else {
                        $error = 'Failed to generate reset key. Please try again.';
                    }
                }
            }
        }
    }
    
    ob_start();
    ?>
    <style>
        <?php echo get_my_account_inline_styles(); ?>
    </style>
    
    <!-- Header -->
    <div class="mat-header">
        <h1>RESET PASSWORD</h1>
    </div>
    
    <div class="mat-wrapper mat-wrapper-auth">
        <div class="mat-auth-container" style="max-width: 600px;">
            <?php if (!empty($message)) : ?>
                <div class="mat-success-message">
                    <span class="mat-success-icon">✓</span>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)) : ?>
                <div class="mat-error-message">
                    <span class="mat-error-icon">⚠️</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="mat-form-column active" style="display: block; max-width: 600px; margin: 0 auto;">
                <h2>Lost your password?</h2>
                <p style="text-align: center; color: #666; margin-bottom: 25px;">
                    Please enter your email address. You will receive a link to create a new password via email.
                </p>
                
                <form method="post" action="" class="mat-form">
                    <div class="mat-form-group">
                        <label>Email address *</label>
                        <input type="email" name="user_login" value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="reset_password" class="mat-btn-submit">Reset password</button>
                    <?php wp_nonce_field('reset_password', 'reset_password_nonce'); ?>
                    
                    <p class="mat-toggle-form" style="margin-top: 20px;">
                        Remember your password? <a href="<?php echo home_url('/my-account'); ?>">Back to login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Shortcode My Account
add_shortcode('my_account_inline', 'my_account_inline_func');
function my_account_inline_func() {
    if (!is_user_logged_in()) {
        return my_account_login_register_forms();
    }
    
    $current_user = wp_get_current_user();
    
    ob_start();
    ?>
    <style>
        <?php echo get_my_account_inline_styles(); ?>
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
                    <span class="mat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </span>
                    <span>Address</span>
                </a>
                <a href="#account-details" class="mat-menu-item" data-tab="account-details">
                    <span class="mat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </span>
                    <span>Account Details</span>
                </a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="mat-menu-item">
                    <span class="mat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </span>
                    <span>Log Out</span>
                </a>
                <a href="<?php echo home_url(); ?>" class="mat-menu-item mat-home">
                    <span class="mat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </span>
                    <span>Home</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Area -->
        <div class="mat-main">
            <!-- Tabs -->
            <div class="mat-tabs">
                <a href="#orders" class="mat-tab active" data-content="orders">
                    <span class="mat-tab-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </span> 
                    Order
                </a>
                <a href="#reservations" class="mat-tab" data-content="reservations">
                    <span class="mat-tab-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span> 
                    Reservations
                </a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="mat-tab mat-tab-link">
                    <span class="mat-tab-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </span> 
                    Cart
                </a>
            </div>
            
            <!-- Content -->
            <div class="mat-content">
                <div class="mat-section" id="dashboard">
                    <?php echo get_dashboard_inline(); ?>
                </div>
                
                <div class="mat-section active" id="orders">
                    <?php echo get_orders_inline(); ?>
                </div>
                
                <div class="mat-section" id="reservations">
                    <?php echo get_reservations_inline(); ?>
                </div>
                
                <div class="mat-section" id="address">
                    <?php echo get_address_inline(); ?>
                </div>
                
                <div class="mat-section" id="account-details">
                    <?php echo get_account_details_inline(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================
// 2. LOGIN & REGISTER FORMS (Toggle Inline)
// ============================================

function my_account_login_register_forms() {
    // Xử lý registration
    $error_message = '';
    $success_message = '';
    $show_register = false;
    $show_lost_password = false;
    
    if (isset($_POST['register_user_inline'])) {
        $error_message = handle_user_registration_inline();
        $show_register = true;
    }
    
    // Xử lý Lost Password
    if (isset($_POST['reset_password_inline'])) {
        $result = handle_lost_password_inline();
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
        $show_lost_password = true;
    }
    
    ob_start();
    ?>
    <style>
        <?php echo get_my_account_inline_styles(); ?>
    </style>
    
    <!-- MY ACCOUNT Header -->
    <div class="mat-header">
        <h1>MY ACCOUNT</h1>
    </div>
    
    <div class="mat-wrapper mat-wrapper-auth">
        <!-- Forms Container -->
        <div class="mat-auth-container">
            <?php if (!empty($success_message)) : ?>
                <div class="mat-success-message">
                    <span class="mat-success-icon">✓</span>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)) : ?>
                <div class="mat-error-message">
                    <span class="mat-error-icon">⚠️</span>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="mat-forms-wrapper">
                <!-- Login Form -->
                <div class="mat-form-column mat-login-column <?php echo (!$show_register && !$show_lost_password) ? 'active' : ''; ?>">
                    <h2>Login</h2>
                    
                    <?php echo get_social_login_buttons(); ?>
                    
                    <form method="post" action="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="mat-form mat-login-form">
                        <div class="mat-form-group">
                            <label>Username or email address *</label>
                            <input type="text" name="log" required>
                        </div>
                        
                        <div class="mat-form-group">
                            <label>Password *</label>
                            <input type="password" name="pwd" required>
                        </div>
                        
                        <div class="mat-checkbox">
                            <input type="checkbox" name="rememberme" value="forever" id="rememberme">
                            <label for="rememberme">Remember me</label>
                        </div>
                        
                        <button type="submit" class="mat-btn-submit">Log in</button>
                        
                        <p class="mat-lost-pw">
                            <a href="#" class="mat-toggle-link" data-target="lost-password">Lost your password?</a>
                        </p>
                        
                        <p class="mat-toggle-form">
                            Not a member? <a href="#" class="mat-toggle-link" data-target="register">Register</a>
                        </p>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div class="mat-form-column mat-register-column <?php echo $show_register ? 'active' : ''; ?>">
                    <h2>Register</h2>
                    
                    <form method="post" action="" class="mat-form mat-register-form">
                        <div class="mat-form-group">
                            <label>Email address *</label>
                            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="mat-form-row">
                            <div class="mat-form-group">
                                <label>First name</label>
                                <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>">
                            </div>
                            <div class="mat-form-group">
                                <label>Last name</label>
                                <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <p class="mat-register-note">
                            A link to set a new password will be sent to your email address.
                        </p>
                        
                        <p class="mat-privacy-note">
                            Your personal data will be used to support your experience throughout this website, 
                            to manage access to your account, and for other purposes described in our 
                            <a href="<?php echo get_privacy_policy_url(); ?>" target="_blank">privacy policy</a>.
                        </p>
                        
                        <button type="submit" name="register_user_inline" class="mat-btn-submit">Register</button>
                        <?php wp_nonce_field('register_user_inline', 'register_user_inline_nonce'); ?>
                        
                        <p class="mat-toggle-form">
                            Already a member? <a href="#" class="mat-toggle-link" data-target="login">Login</a>
                        </p>
                    </form>
                </div>
                
                <!-- Lost Password Form -->
                <div class="mat-form-column mat-lost-password-column <?php echo $show_lost_password ? 'active' : ''; ?>">
                    <h2>Reset Password</h2>
                    
                    <p class="mat-register-note" style="text-align: center;">
                        Lost your password? Please enter your email address. You will receive a link to create a new password via email.
                    </p>
                    
                    <form method="post" action="" class="mat-form mat-lost-password-form">
                        <div class="mat-form-group">
                            <label>Email address *</label>
                            <input type="email" name="user_email" value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>" required>
                        </div>
                        
                        <button type="submit" name="reset_password_inline" class="mat-btn-submit">Reset password</button>
                        <?php wp_nonce_field('reset_password_inline', 'reset_password_inline_nonce'); ?>
                        
                        <p class="mat-toggle-form">
                            Remember your password? <a href="#" class="mat-toggle-link" data-target="login">Back to login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Social Login Buttons
function get_social_login_buttons() {
    ob_start();
    
    // Luôn render button thật (plugin đã có sẵn)
    $current_url = urlencode(get_permalink());
    $google_url = home_url("/wp-json/wslu-social-login/type/google?XScurrentPage={$current_url}");
    ?>
    <div class="mat-social-login">
        <a href="<?php echo esc_url($google_url); ?>" class="mat-btn-social mat-btn-gg">
            <span>G</span> Login with Google
        </a>
    </div>
    <?php
    
    return ob_get_clean();
}

// Xử lý registration
function handle_user_registration_inline() {
    if (!isset($_POST['register_user_inline_nonce']) || !wp_verify_nonce($_POST['register_user_inline_nonce'], 'register_user_inline')) {
        return 'Invalid request.';
    }
    
    $email = sanitize_email($_POST['email']);
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    
    if (empty($email) || !is_email($email)) {
        return 'Please provide a valid email address.';
    }
    
    if (email_exists($email)) {
        return 'An account is already registered with ' . $email . '. Please log in or use a different email address.';
    }
    
    $username = sanitize_user(current(explode('@', $email)), true);
    $append = 1;
    $o_username = $username;
    while (username_exists($username)) {
        $username = $o_username . $append;
        $append++;
    }
    
    $user_id = wp_create_user($username, wp_generate_password(), $email);
    
    if (is_wp_error($user_id)) {
        return $user_id->get_error_message();
    }
    
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => trim($first_name . ' ' . $last_name) ?: $username,
    ));
    
    $user = get_userdata($user_id);
    $reset_key = get_password_reset_key($user);
    
    if (!is_wp_error($reset_key)) {
        $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
        
        $message = "Hi " . ($first_name ?: 'there') . ",\n\n";
        $message .= "Welcome to " . get_bloginfo('name') . "!\n\n";
        $message .= "To set your password, please click the link below:\n";
        $message .= $reset_url . "\n\n";
        $message .= "If you did not request this, please ignore this email.\n\n";
        $message .= "Thanks!";
        
        wp_mail($email, 'Welcome to ' . get_bloginfo('name'), $message);
    }
    
    wp_redirect(add_query_arg('registered', 'true', home_url('/my-account')));
    exit;
}

// Xử lý Lost Password
function handle_lost_password_inline() {
    if (!isset($_POST['reset_password_inline_nonce']) || !wp_verify_nonce($_POST['reset_password_inline_nonce'], 'reset_password_inline')) {
        return array('success' => false, 'message' => 'Invalid request.');
    }
    
    $email = sanitize_email($_POST['user_email']);
    
    if (empty($email)) {
        return array('success' => false, 'message' => 'Please enter your email address.');
    }
    
    if (!is_email($email)) {
        return array('success' => false, 'message' => 'Please enter a valid email address.');
    }
    
    if (!email_exists($email)) {
        return array('success' => false, 'message' => 'There is no account with that email address.');
    }
    
    $user = get_user_by('email', $email);
    
    if (!$user) {
        return array('success' => false, 'message' => 'User not found.');
    }
    
    $reset_key = get_password_reset_key($user);
    
    if (is_wp_error($reset_key)) {
        return array('success' => false, 'message' => 'Failed to generate reset key. Please try again.');
    }
    
    $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
    
    $email_message = "Hi " . $user->display_name . ",\n\n";
    $email_message .= "You requested to reset your password for your account at " . get_bloginfo('name') . ".\n\n";
    $email_message .= "To reset your password, please click the link below:\n";
    $email_message .= $reset_url . "\n\n";
    $email_message .= "If you did not request this, please ignore this email.\n\n";
    $email_message .= "This link will expire in 24 hours.\n\n";
    $email_message .= "Thanks!";
    
    $sent = wp_mail(
        $email,
        'Password Reset Request - ' . get_bloginfo('name'),
        $email_message
    );
    
    if ($sent) {
        return array(
            'success' => true, 
            'message' => 'Check your email for the confirmation link. If you don\'t see it, please check your spam folder.'
        );
    } else {
        return array('success' => false, 'message' => 'Failed to send email. Please try again or contact support.');
    }
}

// ============================================
// 3. STYLES
// ============================================

function get_my_account_inline_styles() {
    return '
        /* Modal Styles */
        .mat-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .mat-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s;
        }

        .mat-modal-content {
            position: relative;
            background: #fff;
            max-width: 900px;
            margin: 50px auto;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s;
            max-height: calc(100vh - 100px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .mat-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .mat-modal-header h2 {
            margin: 0;
            color: #3B7D3B;
            font-size: 24px;
        }

        .mat-modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .mat-modal-close:hover {
            background: #f5f5f5;
            color: #333;
        }

        .mat-modal-body {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }

        .mat-loading {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 18px;
        }

        /* Order Details Styles */
        .mat-order-detail {
            margin-bottom: 30px;
        }

        .mat-order-detail-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .mat-order-detail-header h3 {
            margin: 0 0 15px 0;
            color: #3B7D3B;
            font-size: 20px;
        }

        .mat-order-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .mat-order-meta-item {
            display: flex;
            flex-direction: column;
        }

        .mat-order-meta-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .mat-order-meta-value {
            font-weight: 600;
            color: #333;
        }

        .mat-order-items {
            margin-bottom: 25px;
        }

        .mat-order-items h4 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #333;
        }

        .mat-order-item-detail {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .mat-order-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .mat-order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mat-order-item-info-detail {
            flex: 1;
        }

        .mat-order-item-name-detail {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .mat-order-item-meta-detail {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .mat-order-item-price-detail {
            font-weight: bold;
            color: #3B7D3B;
            font-size: 16px;
        }

        .mat-order-summary-detail {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .mat-order-summary-detail h4 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #333;
        }

        .mat-summary-row-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }

        .mat-summary-row-detail.total {
            border-top: 2px solid #ddd;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #3B7D3B;
        }

        /* Responsive Modal */
        @media (max-width: 992px) {
            .mat-modal-content {
                margin: 20px;
                max-height: calc(100vh - 40px);
            }
            
            .mat-order-meta {
                grid-template-columns: 1fr;
            }
            
            .mat-order-item-detail {
                flex-direction: column;
            }
            
            .mat-order-item-image {
                width: 100%;
                height: 150px;
            }
        }
        * { box-sizing: border-box; }
        
        /* Ẩn page title */
        .entry-title,
        h1.entry-title,
        .page-title {
            display: none !important;
        }
        
        /* Header */
        .mat-header {
            background: #3B7D3B;
            padding: 40px 20px;
            text-align: center;
            margin: -64px -20px 0 -20px;
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
        }
        
        .mat-wrapper-auth {
            justify-content: center;
            padding: 40px 20px;
        }
        
        /* Auth Container */
        .mat-auth-container {
            max-width: 900px;
            width: 100%;
        }
        
        .mat-forms-wrapper {
            display: flex;
            gap: 40px;
            margin-top: 20px;
        }
        
        .mat-form-column {
            flex: 1;
            padding: 30px;
            border-radius: 12px;
            display: none;
            transition: opacity 0.3s ease;
        }
        
        .mat-form-column.active {
            display: block;
            opacity: 1;
        }
        
        .mat-form-column h2 {
            color: #3B7D3B;
            margin: 0 0 25px;
            text-align: center;
            font-size: 28px;
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

        .mat-avatar {
            display: flex;
            justify-content: center;
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
            color: #3B7D3B;
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
            color: #3B7D3B;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .mat-menu-item:hover {
            background: #3B7D3B;
            color: #fff !important;
        }
        
        .mat-icon svg {
            display: block;
            transition: all 0.3s ease;
        }

        .mat-menu-item:hover .mat-icon svg {
            transform: scale(1.1);
            stroke: #FFFFFF; /* Thay bằng màu chủ đạo của bạn */
        }

        .mat-menu-item.active .mat-icon svg {
            stroke: #FFFFFF;
        }
        
        .mat-home {
            margin-top: 10px;
            background: #3B7D3B !important;
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
            color: #3B7D3B;
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
            border-bottom-color: #3B7D3B;
            font-weight: 600;
        }
        
        .mat-tab-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            vertical-align: middle;
        }

        .mat-tab-icon svg {
            display: block;
            transition: all 0.3s ease;
        }

        /* Active tab */
        .mat-tab.active .mat-tab-icon svg {
            stroke: #3B7D3B; /* Màu khi active */
        }

        /* Hover effect */
        .mat-tab:hover .mat-tab-icon svg {
            transform: translateY(-2px);
            stroke: #3B7D3B;
        }

        /* Optional: Thêm animation khi click */
        .mat-tab:active .mat-tab-icon svg {
            transform: scale(0.95);
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
            color: #3B7D3B;
        }
        
        .mat-order-details p {
            margin: 3px 0;
            font-size: 14px;
            color: #666;
        }
        
        .mat-order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        
        .mat-btn-tracking {
            background: #ff9800;
            color: #fff;
        }
        
        .mat-btn-pay {
            background: #3B7D3B;
            color: #fff;
        }
        
        .mat-btn-view {
            background: #fff;
            color: #3B7D3B;
            border: 2px solid #3B7D3B;
        }
        
        .mat-btn-cancel {
            background: #fff;
            color: #d32f2f;
            border: 2px solid #d32f2f;
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
            border-left: 4px solid #3B7D3B;
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
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .mat-res-datetime {
            display: flex;
            gap: 20px;
            font-weight: 600;
            color: #3B7D3B;
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
            color: #3B7D3B;
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
            color: #333;
        }
        
        .mat-form-group input,
        .mat-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
        }
        
        .mat-form-group input:focus,
        .mat-form-group textarea:focus {
            outline: none;
            border-color: #3B7D3B;
        }
        
        .mat-form-group small {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #999;
        }
        
        .mat-btn-submit,
        .mat-btn-save {
            width: 100%;
            padding: 14px;
            background: #3B7D3B;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .mat-btn-submit:hover,
        .mat-btn-save:hover {
            background: #2e6b2e;
        }
        
        /* Social Login */
        .mat-social-login {
            margin-bottom: 25px;
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
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
        }
        
        .mat-btn-social span:first-child {
            font-weight: 700;
            font-size: 18px;
        }
        
        .mat-btn-fb {
            color: #1877f2;
            border-color: #1877f2;
        }
        
        .mat-btn-fb:hover {
            background: #1877f2;
            color: #fff;
        }
        
        .mat-btn-gg {
            color: #db4437;
            border-color: #db4437;
        }
        
        .mat-btn-gg:hover {
            background: #db4437;
            color: #fff;
        }
        
        .mat-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
        }
        
        .mat-checkbox input {
            width: auto;
        }
        
        .mat-lost-pw,
        .mat-toggle-form {
            text-align: center;
            margin-top: 15px;
        }
        
        .mat-lost-pw a,
        .mat-toggle-form a {
            color: #d32f2f;
            text-decoration: none;
            font-weight: 500;
        }
        
        .mat-lost-pw a:hover,
        .mat-toggle-form a:hover {
            text-decoration: underline;
        }
        
        .mat-register-note,
        .mat-privacy-note {
            font-size: 14px;
            color: #d32f2f;
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .mat-privacy-note {
            color: #666;
        }
        
        .mat-privacy-note a {
            color: #d32f2f;
            text-decoration: none;
        }
        
        .mat-error-message {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #c62828;
        }
        
        .mat-error-icon {
            margin-right: 8px;
        }

        .mat-success-message {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #2e7d32;
        }

        .mat-success-icon {
            margin-right: 8px;
            font-weight: bold;
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
            
            .mat-forms-wrapper {
                flex-direction: column;
            }
            
            /* Mobile: Chỉ hiển thị form active */
            .mat-form-column {
                display: none;
            }
            
            .mat-form-column.active {
                display: block;
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
            
            .mat-form-row {
                flex-direction: column;
            }
        }
        
        @media (min-width: 993px) {
            .mat-forms-wrapper {
                display: flex !important;
            }
        }
    ';
}

// ============================================
// 4. CONTENT FUNCTIONS
// ============================================

function get_dashboard_inline() {
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

function get_orders_inline() {
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
                    <?php 
                    $tracking_url = 'https://goodriver.online/guest/share/order/woocommerce/' . $order->get_order_key();
                    ?>
                    <a href="<?php echo esc_url($tracking_url); ?>" class="mat-btn mat-btn-tracking" target="_blank">Tracking</a>
                    
                    <button type="button" class="mat-btn mat-btn-view mat-view-order" data-order-id="<?php echo $order->get_id(); ?>">View</button>
                    <?php if (in_array($order->get_status(), array('pending', 'on-hold'))) : ?>
                        <a href="<?php echo $order->get_cancel_order_url(); ?>" class="mat-btn mat-btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; else : ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

    <!-- Order Details Modal -->
    <div id="mat-order-modal" class="mat-modal" style="display: none;">
        <div class="mat-modal-overlay"></div>
        <div class="mat-modal-content">
            <div class="mat-modal-header">
                <h2>Order Details</h2>
                <button type="button" class="mat-modal-close">&times;</button>
            </div>
            <div class="mat-modal-body">
                <div class="mat-loading">Loading...</div>
                <div id="mat-order-details-content"></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function get_reservations_inline() {
    $user = wp_get_current_user();
    
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
                    <?php if ($order_key) : 
                        $tracking_url = 'https://goodriver.online/guest/reservation/track/' . $order_key;
                    ?>
                        <a href="<?php echo esc_url($tracking_url); ?>" class="mat-btn mat-btn-tracking" target="_blank">Tracking</a>
                    <?php endif; ?>
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

function get_address_inline() {
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

function get_account_details_inline() {
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
        
        <button type="submit" name="save_account_inline" class="mat-btn-save">Save changes</button>
        <?php wp_nonce_field('save_account_inline', 'save_account_inline_nonce'); ?>
    </form>
    <?php
    return ob_get_clean();
}

// ============================================
// 5. SAVE HANDLER
// ============================================

add_action('init', 'handle_save_account_inline');
function handle_save_account_inline() {
    if (!isset($_POST['save_account_inline']) || !isset($_POST['save_account_inline_nonce'])) return;
    if (!wp_verify_nonce($_POST['save_account_inline_nonce'], 'save_account_inline')) return;
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

// ============================================
// 6. JAVASCRIPT
// ============================================

add_action('wp_footer', 'mat_scripts_inline');
function mat_scripts_inline() {
    if (!is_page('my-account')) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Tab switching (for logged-in users)
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
        
        // Toggle between Login, Register and Lost Password forms
        $('.mat-toggle-link').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).data('target');
            
            // Ẩn tất cả forms
            $('.mat-form-column').removeClass('active');
            
            // Hiển thị form được chọn
            if (target === 'register') {
                $('.mat-register-column').addClass('active');
            } else if (target === 'lost-password') {
                $('.mat-lost-password-column').addClass('active');
            } else {
                $('.mat-login-column').addClass('active');
            }
            
            // Scroll to top trên mobile
            if ($(window).width() <= 992) {
                $('html, body').animate({
                    scrollTop: $('.mat-header').offset().top
                }, 400);
            }
        });
        
        // ============================================
        // VIEW ORDER MODAL
        // ============================================
        
        // Open modal when clicking View button
        $(document).on('click', '.mat-view-order', function(e) {
            e.preventDefault();
            
            var orderId = $(this).data('order-id');
            var $modal = $('#mat-order-modal');
            
            // Show modal
            $modal.fadeIn(300);
            $('body').css('overflow', 'hidden');
            
            // Show loading
            $('.mat-loading').show();
            $('#mat-order-details-content').hide();
            
            // Load order details via AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_order_details',
                    order_id: orderId
                },
                success: function(response) {
                    $('.mat-loading').hide();
                    if (response.success) {
                        $('#mat-order-details-content').html(response.data.html).fadeIn(300);
                    } else {
                        $('#mat-order-details-content').html('<p style="color: red; text-align: center;">Error loading order details.</p>').fadeIn(300);
                    }
                },
                error: function() {
                    $('.mat-loading').hide();
                    $('#mat-order-details-content').html('<p style="color: red; text-align: center;">Error loading order details.</p>').fadeIn(300);
                }
            });
        });
        
        // Close modal
        $(document).on('click', '.mat-modal-close, .mat-modal-overlay', function() {
            $('#mat-order-modal').fadeOut(300);
            $('body').css('overflow', '');
        });
        
        // Close modal on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#mat-order-modal').is(':visible')) {
                $('#mat-order-modal').fadeOut(300);
                $('body').css('overflow', '');
            }
        });
    });
    </script>
    <?php
}

// AJAX handler để load order details
add_action('wp_ajax_get_order_details', 'get_order_details_ajax');
function get_order_details_ajax() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    
    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);
    
    // Check if order belongs to current user
    if (!$order || $order->get_customer_id() !== get_current_user_id()) {
        wp_send_json_error('Invalid order');
    }
    
    ob_start();
    ?>
    <div class="mat-order-detail">
        <!-- Order Header -->
        <div class="mat-order-detail-header">
            <h3>Order #<?php echo $order->get_order_number(); ?></h3>
            <div class="mat-order-meta">
                <div class="mat-order-meta-item">
                    <span class="mat-order-meta-label">Date</span>
                    <span class="mat-order-meta-value"><?php echo $order->get_date_created()->format('F j, Y'); ?></span>
                </div>
                <div class="mat-order-meta-item">
                    <span class="mat-order-meta-label">Status</span>
                    <span class="mat-order-meta-value"><?php echo wc_get_order_status_name($order->get_status()); ?></span>
                </div>
                <div class="mat-order-meta-item">
                    <span class="mat-order-meta-label">Payment Method</span>
                    <span class="mat-order-meta-value"><?php echo $order->get_payment_method_title(); ?></span>
                </div>
                <div class="mat-order-meta-item">
                    <span class="mat-order-meta-label">Total</span>
                    <span class="mat-order-meta-value"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="mat-order-items">
            <h4>Order Items</h4>
            <?php foreach ($order->get_items() as $item_id => $item) : 
                $product = $item->get_product();
                if (!$product) continue;
            ?>
                <div class="mat-order-item-detail">
                    <?php if ($product->get_image_id()) : ?>
                        <div class="mat-order-item-image">
                            <?php echo $product->get_image('thumbnail'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mat-order-item-info-detail">
                        <div class="mat-order-item-name-detail"><?php echo $item->get_name(); ?></div>
                        <div class="mat-order-item-meta-detail">
                            Quantity: <?php echo $item->get_quantity(); ?>
                            <?php
                            // Display variations
                            if ($product->is_type('variation')) {
                                $variation_data = $product->get_variation_attributes();
                                if (!empty($variation_data)) {
                                    echo '<br>';
                                    foreach ($variation_data as $key => $value) {
                                        echo '<span>' . ucfirst(str_replace('attribute_', '', $key)) . ': ' . $value . '</span><br>';
                                    }
                                }
                            }
                            
                            // Display add-ons if any
                            $item_meta = $item->get_meta_data();
                            if (!empty($item_meta)) {
                                foreach ($item_meta as $meta) {
                                    if (strpos($meta->key, '_') !== 0) { // Skip hidden meta
                                        echo '<br><span>' . $meta->key . ': ' . $meta->value . '</span>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mat-order-item-price-detail">
                        <?php echo $order->get_formatted_line_subtotal($item); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Order Summary -->
        <div class="mat-order-summary-detail">
            <h4>Order Summary</h4>
            <div class="mat-summary-row-detail">
                <span>Subtotal</span>
                <span><?php echo wc_price($order->get_subtotal()); ?></span>
            </div>
            <?php if ($order->get_shipping_total() > 0) : ?>
                <div class="mat-summary-row-detail">
                    <span>Shipping</span>
                    <span><?php echo wc_price($order->get_shipping_total()); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($order->get_total_tax() > 0) : ?>
                <div class="mat-summary-row-detail">
                    <span>Tax</span>
                    <span><?php echo wc_price($order->get_total_tax()); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($order->get_discount_total() > 0) : ?>
                <div class="mat-summary-row-detail" style="color: #10b981;">
                    <span>Discount</span>
                    <span>-<?php echo wc_price($order->get_discount_total()); ?></span>
                </div>
            <?php endif; ?>
            <div class="mat-summary-row-detail total">
                <span>Total</span>
                <span><?php echo $order->get_formatted_order_total(); ?></span>
            </div>
        </div>
        
        <!-- Billing & Shipping Address -->
        <?php if ($order->get_billing_address_1()) : ?>
            <div class="mat-order-items" style="margin-top: 25px;">
                <h4>Billing Address</h4>
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <?php echo $order->get_formatted_billing_address(); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($order->get_shipping_address_1()) : ?>
            <div class="mat-order-items" style="margin-top: 25px;">
                <h4>Shipping Address</h4>
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <?php echo $order->get_formatted_shipping_address(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    
    $html = ob_get_clean();
    wp_send_json_success(array('html' => $html));
}
