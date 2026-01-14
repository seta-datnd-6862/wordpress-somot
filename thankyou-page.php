<?php
// ========================================
// AUTO-CREATE THANK YOU PAGE WITH SHORTCODE
// Tự động tạo page và redirect như my-account page
// ========================================

// ============================================
// 1. TẠO PAGE TỰ ĐỘNG
// ============================================

add_action('init', 'create_thank_you_page_auto');
function create_thank_you_page_auto() {
    $page_slug = 'thank-you-page';
    $page_check = get_page_by_path($page_slug);
    
    if (!$page_check) {
        wp_insert_post(array(
            'post_title'    => 'Order Confirmation',
            'post_name'     => $page_slug,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '[thank_you_inline]',
        ));
    } else {
        // Update content nếu page đã tồn tại
        wp_update_post(array(
            'ID'           => $page_check->ID,
            'post_content' => '[thank_you_inline]',
        ));
    }
}

// ============================================
// 2. REDIRECT TỪ THANK YOU GỐC SANG PAGE MỚI
// ============================================

add_action('template_redirect', 'redirect_to_thank_you_page');
function redirect_to_thank_you_page() {
    global $wp;
    
    // Kiểm tra nếu đang ở trang order-received
    if (is_checkout() && !empty($wp->query_vars['order-received'])) {
        $order_id = absint($wp->query_vars['order-received']);
        $order_key = isset($_GET['key']) ? wc_clean($_GET['key']) : '';
        
        if ($order_id && $order_key) {
            // Verify order key
            $order = wc_get_order($order_id);
            if ($order && $order->get_order_key() === $order_key) {
                // Redirect sang trang thank-you-page với query string
                $redirect_url = home_url('/thank-you-page/') . '?order_id=' . $order_id . '&key=' . $order_key;
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
}

// ============================================
// 3. SHORTCODE [thank_you_inline]
// ============================================

add_shortcode('thank_you_inline', 'thank_you_inline_func');
function thank_you_inline_func() {
    // Get order info từ URL
    $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
    $order_key = isset($_GET['key']) ? wc_clean($_GET['key']) : '';
    
    // Nếu không có order info, hiển thị error
    if (!$order_id || !$order_key) {
        return get_thank_you_error();
    }
    
    // Get order
    $order = wc_get_order($order_id);
    
    // Verify order key
    if (!$order || $order->get_order_key() !== $order_key) {
        return get_thank_you_error();
    }
    
    // Get order data
    $total = $order->get_total();
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $payment_proof = $order->get_meta('_payment_proof_file');
    $customer_email = $order->get_billing_email();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    
    ob_start();
    ?>
    <style>
        <?php echo get_thank_you_inline_styles(); ?>
    </style>
    
    <div class="tyi-wrapper">
        <!-- Success Header -->
        <div class="tyi-success-header">
            <div class="tyi-success-icon">✅</div>
            <h1>Order Completed Successfully!</h1>
            <p>Thank you for your order, <?php echo esc_html($customer_name); ?>. We have received your payment proof and will verify it shortly.</p>
            <div class="tyi-order-number">Order #<?php echo $order_id; ?></div>
        </div>

        <!-- Order Summary -->
        <div class="tyi-info-card">
            <div class="tyi-card-title">
                📦 Order Summary
            </div>
            
            <div class="tyi-info-row">
                <span class="tyi-info-label">Order Number</span>
                <span class="tyi-info-value">#<?php echo $order_id; ?></span>
            </div>
            
            <div class="tyi-info-row">
                <span class="tyi-info-label">Order Date</span>
                <span class="tyi-info-value"><?php echo $order->get_date_created()->date_i18n('F j, Y - g:i A'); ?></span>
            </div>
            
            <div class="tyi-info-row">
                <span class="tyi-info-label">Payment Method</span>
                <span class="tyi-info-value"><?php echo esc_html($order->get_payment_method_title()); ?></span>
            </div>
            
            <div class="tyi-info-row">
                <span class="tyi-info-label">Total Amount</span>
                <span class="tyi-info-value" style="color: #10b981; font-size: 24px;">₱<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <!-- Order Items -->
        <div class="tyi-info-card">
            <div class="tyi-card-title">
                🛍️ Order Items
            </div>
            
            <div class="tyi-order-items-list">
                <?php foreach ($order->get_items() as $item_id => $item): ?>
                    <?php 
                    $product = $item->get_product();
                    if (!$product) continue;
                    ?>
                    <div class="tyi-order-item">
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($product->get_image_id(), 'thumbnail')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        <div class="tyi-order-item-details">
                            <div class="tyi-order-item-name"><?php echo esc_html($product->get_name()); ?></div>
                            <div class="tyi-order-item-meta">
                                Quantity: <?php echo $item->get_quantity(); ?>
                                <?php if ($item->get_variation_id()): ?>
                                    <br>Variation: <?php echo wc_get_formatted_variation($product, true); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tyi-order-item-price">
                            ₱<?php echo number_format($item->get_total(), 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Delivery Information -->
        <?php if ($delivery_type): ?>
        <div class="tyi-info-card">
            <div class="tyi-card-title">
                <?php echo $delivery_type === 'pickup' ? '📦 Pickup' : '🚚 Delivery'; ?> Information
            </div>
            
            <div class="tyi-info-row">
                <span class="tyi-info-label">Type</span>
                <span class="tyi-info-value"><?php echo ucfirst($delivery_type); ?></span>
            </div>
            
            <?php if ($delivery_date): ?>
            <div class="tyi-info-row">
                <span class="tyi-info-label"><?php echo $delivery_type === 'pickup' ? 'Pickup' : 'Delivery'; ?> Date</span>
                <span class="tyi-info-value"><?php echo date('F j, Y', strtotime($delivery_date)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($delivery_time): ?>
            <div class="tyi-info-row">
                <span class="tyi-info-label"><?php echo $delivery_type === 'pickup' ? 'Pickup' : 'Delivery'; ?> Time</span>
                <span class="tyi-info-value"><?php echo date('g:i A', strtotime($delivery_time)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($branch): ?>
            <div class="tyi-info-row">
                <span class="tyi-info-label">Branch</span>
                <span class="tyi-info-value"><?php echo ucfirst(str_replace('_', ' ', $branch)); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Payment Confirmation -->
        <?php if ($payment_proof): ?>
        <div class="tyi-payment-proof-box">
            <h3 style="margin: 0 0 10px 0; color: #065f46;">✅ Payment Proof Received</h3>
            <p style="margin: 0; color: #047857;">Your payment proof has been uploaded successfully. We will verify and confirm your payment shortly.</p>
            <a href="<?php echo esc_url($payment_proof); ?>" target="_blank" style="display: inline-block; margin-top: 10px; color: #059669; font-weight: 600;">
                View Uploaded File →
            </a>
        </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="tyi-next-steps">
            <h3>📋 What Happens Next?</h3>
            <ul>
                <li><strong>Payment Verification:</strong> Our team will verify your payment within 1-2 hours during business hours.</li>
                <li><strong>Order Processing:</strong> Once verified, we will start preparing your order immediately.</li>
                <li><strong>Order Tracking:</strong> You can track your order status anytime in your account dashboard.</li>
                <li><strong>Email Updates:</strong> We will send you email notifications for any order status changes.</li>
            </ul>
        </div>

        <!-- Important Information -->
        <div class="tyi-alert-info">
            <strong>📧 Check Your Email</strong>
            <p style="margin: 10px 0 0 0;">
                We have sent a confirmation email to <strong><?php echo esc_html($customer_email); ?></strong> with your order details and account information.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="tyi-action-buttons">
            <a href="https://goodriver.online/track-order/<?php echo $order_id; ?>" class="tyi-btn tyi-btn-primary" target="_blank">
                🔍 Track My Order
            </a>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="tyi-btn tyi-btn-secondary">
                🛍️ Continue Shopping
            </a>
        </div>

        <!-- Contact Support -->
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                Need help? Contact us at <a href="mailto:customerservice@so-mot.com" style="color: #10b981; font-weight: 600;">customerservice@so-mot.com</a>
            </p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================
// 4. ERROR PAGE
// ============================================

function get_thank_you_error() {
    ob_start();
    ?>
    <style>
        <?php echo get_thank_you_inline_styles(); ?>
    </style>
    
    <div class="tyi-header">
        <h1>ORDER CONFIRMATION</h1>
    </div>
    
    <div class="tyi-wrapper">
        <div class="tyi-error-message">
            <h2>❌ Invalid Order</h2>
            <p>We couldn't find your order. Please check your email for the order confirmation.</p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="tyi-btn tyi-btn-primary" style="display: inline-block; margin-top: 20px;">
                Go to Shop
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================
// 5. STYLES
// ============================================

function get_thank_you_inline_styles() {
    return '
        /* Reset container */
        * { box-sizing: border-box; }
        
        /* Ẩn page title */
        .entry-title,
        h1.entry-title,
        .page-title {
            display: none !important;
        }
        
        /* Header */
        .tyi-header {
            background: #10b981;
            padding: 40px 20px;
            text-align: center;
            margin: -64px -20px 0 -20px;
        }
        
        .tyi-header h1 {
            color: #fff;
            font-size: 48px;
            margin: 0;
            font-weight: 300;
            letter-spacing: 3px;
            font-family: Georgia, serif;
        }
        
        /* Wrapper */
        .tyi-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .tyi-success-header {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
        }
        
        .tyi-success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.6s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0) rotate(-180deg); }
            50% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        
        .tyi-success-header h1 {
            font-size: 36px;
            margin: 10px 0;
            font-weight: bold;
            color: white;
        }
        
        .tyi-success-header p {
            font-size: 18px;
            opacity: 0.95;
            margin: 15px 0;
            line-height: 1.6;
            color: white;
        }
        
        .tyi-order-number {
            display: inline-block;
            background: rgba(255,255,255,0.25);
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: bold;
            margin-top: 15px;
            font-size: 18px;
            backdrop-filter: blur(10px);
            color: white;
        }
        
        .tyi-info-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .tyi-card-title {
            font-size: 22px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }
        
        .tyi-info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .tyi-info-row:last-child {
            border-bottom: none;
        }
        
        .tyi-info-label {
            color: #718096;
            font-weight: 500;
            font-size: 15px;
        }
        
        .tyi-info-value {
            color: #2d3748;
            font-weight: 600;
            text-align: right;
            font-size: 15px;
        }
        
        .tyi-payment-proof-box {
            background: #f0fdf4;
            padding: 20px;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        
        .tyi-action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .tyi-action-buttons {
                grid-template-columns: 1fr;
            }
        }
        
        .tyi-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px 30px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .tyi-btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .tyi-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .tyi-btn-secondary {
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
        }
        
        .tyi-btn-secondary:hover {
            background: #f0fdf4;
            transform: translateY(-2px);
            color: #10b981;
        }
        
        .tyi-alert-info {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border-left: 4px solid #0284c7;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            color: #075985;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .tyi-alert-info strong {
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }
        
        .tyi-next-steps {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
            margin: 25px 0;
        }
        
        .tyi-next-steps h3 {
            color: #92400e;
            margin-top: 0;
            font-size: 20px;
        }
        
        .tyi-next-steps ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        .tyi-next-steps li {
            color: #78350f;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .tyi-order-items-list {
            margin-top: 20px;
        }
        
        .tyi-order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .tyi-order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .tyi-order-item-details {
            flex: 1;
        }
        
        .tyi-order-item-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .tyi-order-item-meta {
            font-size: 14px;
            color: #718096;
        }
        
        .tyi-order-item-price {
            font-weight: 700;
            color: #10b981;
            font-size: 18px;
        }
        
        .tyi-error-message {
            text-align: center;
            padding: 60px 20px;
            background: #fef2f2;
            border-radius: 12px;
            margin: 40px auto;
            max-width: 600px;
        }
        
        .tyi-error-message h2 {
            color: #991b1b;
            margin-bottom: 15px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tyi-header h1 {
                font-size: 32px;
            }
            
            .tyi-success-header h1 {
                font-size: 28px;
            }
            
            .tyi-order-item {
                flex-direction: column;
            }
            
            .tyi-order-item img {
                width: 100%;
                height: 150px;
            }
        }
    ';
}
