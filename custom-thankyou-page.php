// ========================================
// CUSTOM THANK YOU PAGE - FIXED VERSION
// ========================================

// 1. Remove default order received text
add_filter('woocommerce_thankyou_order_received_text', '__return_empty_string', 20);

// 2. Hide default sections with CSS
add_action('wp_head', 'hide_default_woocommerce_thankyou_sections');
function hide_default_woocommerce_thankyou_sections() {
    if (!is_order_received_page()) {
        return;
    }
    ?>
    <style>
        /* Hide all default WooCommerce sections */
        .woocommerce-order-overview,
        .woocommerce-order-details,
        .woocommerce-customer-details,
        .woocommerce-bacs-bank-details,
        .woocommerce-thankyou-order-details,
        ul.order_details,
        .woocommerce-table--order-details,
        .woocommerce-columns,
        .woocommerce-column,
        section.woocommerce-order-details,
        section.woocommerce-customer-details {
            display: none !important;
        }
    </style>
    <?php
}

// 3. Render custom thank you page
add_action('woocommerce_thankyou', 'render_custom_thank_you_page', 10);
function render_custom_thank_you_page($order_id) {
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    
    $order_status = $order->get_status();
    $total = $order->get_total();
    
    // Get custom fields
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $payment_proof = $order->get_meta('_payment_proof_file');
    $order_notes = $order->get_meta('_order_notes');
    
    // Get customer info
    $customer_email = $order->get_billing_email();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    
    ?>
    <style>
        .custom-thank-you-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .success-header {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.6s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0) rotate(-180deg); }
            50% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        
        .success-header h1 {
            font-size: 36px;
            margin: 10px 0;
            font-weight: bold;
            color: white;
        }
        
        .success-header p {
            font-size: 18px;
            opacity: 0.95;
            margin: 15px 0;
            line-height: 1.6;
            color: white;
        }
        
        .order-number {
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
        
        .info-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-title {
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
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #718096;
            font-weight: 500;
            font-size: 15px;
        }
        
        .info-value {
            color: #2d3748;
            font-weight: 600;
            text-align: right;
            font-size: 15px;
        }
        
        .payment-proof-box {
            background: #f0fdf4;
            padding: 20px;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .payment-proof-box img {
            max-width: 300px;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #10b981;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
        
        .btn {
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
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .btn-secondary {
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
        }
        
        .btn-secondary:hover {
            background: #f0fdf4;
            transform: translateY(-2px);
            color: #10b981;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border-left: 4px solid #0284c7;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            color: #075985;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .alert-info strong {
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }
        
        .next-steps {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
            margin: 25px 0;
        }
        
        .next-steps h3 {
            color: #92400e;
            margin-top: 0;
            font-size: 20px;
        }
        
        .next-steps ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        .next-steps li {
            color: #78350f;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .order-items-list {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .order-item-meta {
            font-size: 14px;
            color: #718096;
        }
        
        .order-item-price {
            font-weight: 700;
            color: #10b981;
            font-size: 18px;
        }
    </style>

    <div class="custom-thank-you-wrapper">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">✅</div>
            <h1>Order Completed Successfully!</h1>
            <p>Thank you for your order, <?php echo esc_html($customer_name); ?>. We have received your payment proof and will verify it shortly.</p>
            <div class="order-number">Order #<?php echo $order_id; ?></div>
        </div>

        <!-- Order Summary -->
        <div class="info-card">
            <div class="card-title">
                📦 Order Summary
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Number</span>
                <span class="info-value">#<?php echo $order_id; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Date</span>
                <span class="info-value"><?php echo $order->get_date_created()->date_i18n('F j, Y - g:i A'); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="info-value"><?php echo esc_html($order->get_payment_method_title()); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value" style="color: #10b981; font-size: 24px;">₱<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <!-- Order Items -->
        <div class="info-card">
            <div class="card-title">
                🛍️ Order Items
            </div>
            
            <div class="order-items-list">
                <?php foreach ($order->get_items() as $item_id => $item): ?>
                    <?php 
                    $product = $item->get_product();
                    if (!$product) continue;
                    ?>
                    <div class="order-item">
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($product->get_image_id(), 'thumbnail')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        <div class="order-item-details">
                            <div class="order-item-name"><?php echo esc_html($product->get_name()); ?></div>
                            <div class="order-item-meta">
                                Quantity: <?php echo $item->get_quantity(); ?>
                                <?php if ($item->get_variation_id()): ?>
                                    <br>Variation: <?php echo wc_get_formatted_variation($product, true); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="order-item-price">
                            ₱<?php echo number_format($item->get_total(), 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Delivery Information -->
        <?php if ($delivery_type): ?>
        <div class="info-card">
            <div class="card-title">
                <?php echo $delivery_type === 'pickup' ? '📦 Pickup' : '🚚 Delivery'; ?> Information
            </div>
            
            <div class="info-row">
                <span class="info-label">Type</span>
                <span class="info-value"><?php echo ucfirst($delivery_type); ?></span>
            </div>
            
            <?php if ($delivery_date): ?>
            <div class="info-row">
                <span class="info-label"><?php echo $delivery_type === 'pickup' ? 'Pickup' : 'Delivery'; ?> Date</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($delivery_date)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($delivery_time): ?>
            <div class="info-row">
                <span class="info-label"><?php echo $delivery_type === 'pickup' ? 'Pickup' : 'Delivery'; ?> Time</span>
                <span class="info-value"><?php echo date('g:i A', strtotime($delivery_time)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($branch): ?>
            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $branch)); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Payment Confirmation -->
        <?php if ($payment_proof): ?>
        <div class="payment-proof-box">
            <h3 style="margin: 0 0 10px 0; color: #065f46;">✅ Payment Proof Received</h3>
            <p style="margin: 0; color: #047857;">Your payment proof has been uploaded successfully. We will verify and confirm your payment shortly.</p>
            <a href="<?php echo esc_url($payment_proof); ?>" target="_blank" style="display: inline-block; margin-top: 10px; color: #059669; font-weight: 600;">
                View Uploaded File →
            </a>
        </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="next-steps">
            <h3>📋 What Happens Next?</h3>
            <ul>
                <li><strong>Payment Verification:</strong> Our team will verify your payment within 1-2 hours during business hours.</li>
                <li><strong>Order Processing:</strong> Once verified, we will start preparing your order immediately.</li>
                <li><strong>Order Tracking:</strong> You can track your order status anytime in your account dashboard.</li>
                <li><strong>Email Updates:</strong> We will send you email notifications for any order status changes.</li>
            </ul>
        </div>

        <!-- Important Information -->
        <div class="alert-info">
            <strong>📧 Check Your Email</strong>
            <p style="margin: 10px 0 0 0;">
                We have sent a confirmation email to <strong><?php echo esc_html($customer_email); ?></strong> with your order details and account information.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="https://goodriver.online/guest/share/order/woocommerce/<?php echo $order_id; ?>" class="btn btn-primary" target="_blank">
                🔍 Track My Order
            </a>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-secondary">
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
}
