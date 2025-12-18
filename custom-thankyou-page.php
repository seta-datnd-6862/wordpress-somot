// ========================================
// CUSTOM THANK YOU PAGE
// ========================================

// 1. Completely override thank you page
add_action('woocommerce_thankyou', 'custom_complete_thank_you_page', 1);
function custom_complete_thank_you_page($order_id) {
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    
    // Remove all default WooCommerce thank you hooks
    remove_all_actions('woocommerce_thankyou');
    
    // Render our custom page
    render_custom_thank_you_page($order);
}

// 2. Hide default order received text
add_filter('woocommerce_thankyou_order_received_text', '__return_empty_string', 999);

// 3. Remove default order details table
remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);

// 4. Hide all default WooCommerce sections on thank you page
add_action('wp_head', 'hide_default_woocommerce_thankyou_sections');
function hide_default_woocommerce_thankyou_sections() {
    if (!is_order_received_page()) {
        return;
    }
    ?>
    <style>
        .woocommerce-order-received .woocommerce-order-overview,
        .woocommerce-order-received .woocommerce-order-details,
        .woocommerce-order-received .woocommerce-customer-details,
        .woocommerce-order-received .woocommerce-bacs-bank-details,
        .woocommerce-order-received .woocommerce-thankyou-order-details,
        .woocommerce-order-received .woocommerce-notice,
        .woocommerce-order-received .woocommerce-order,
        ul.order_details,
        .woocommerce-table,
        .woocommerce-column,
        .woocommerce-columns, 
        .woocommerce-bacs-bank-details,
        .woocommerce-thankyou-order-details {
            display: none !important;
        }
    </style>
    <?php
}

// 5. Render custom thank you page
function render_custom_thank_you_page($order) {
    $order_id = $order->get_id();
    $order_status = $order->get_status();
    $total = $order->get_total();
    
    // Get custom fields
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $payment_proof = $order->get_meta('_payment_proof_file');
    
    ?>
    <style>
        .custom-thank-you-wrapper {
            max-width: 900px;
            margin: 0 auto;
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
        }
        
        .success-header p {
            font-size: 18px;
            opacity: 0.95;
            margin: 15px 0;
            line-height: 1.6;
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
        }
        
        .btn-secondary {
            background: white;
            color: #10b981 !important;
            border: 2px solid #10b981;
        }
        
        .btn-secondary:hover {
            background: #f0fdf4;
            transform: translateY(-2px);
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

        .color-white {
            color: white !important;
        }
    </style>

    <div class="custom-thank-you-wrapper">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">✅</div>
            <h1 class="color-white">Order Completed Successfully!</h1>
            <p class="color-white">Thank you for your order. We have received your payment proof and will verify it shortly.</p>
            <div class="order-number">Order #<?php echo $order_id; ?></div>
        </div>

        <!-- Order Summary -->
        <div class="info-card">
            <div class="card-title">
                📦 Order Details
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Number</span>
                <span class="info-value">#<?php echo $order_id; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Date</span>
                <span class="info-value"><?php echo $order->get_date_created()->format('F j, Y - g:i A'); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value" style="color: #10b981; font-size: 24px;">₱<?php echo number_format($total, 2); ?></span>
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
                <li><strong>Order Processing:</strong> Once verified, we'll start preparing your order immediately.</li>
                <li><strong>Order Tracking:</strong> You can track your order status anytime in your account dashboard.</li>
                <li><strong>Email Updates:</strong> We'll send you email notifications for any order status changes.</li>
            </ul>
        </div>

        <!-- Important Information -->
        <div class="alert-info">
            <strong>📧 Account Created!</strong>
            <p style="margin: 10px 0 0 0;">
                We have created an account for you using your email address. Check your inbox for your login credentials. 
                You can use this account to track your orders and manage your profile.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="https://goodriver.online/guest/share/order/woocommerce/<?php echo $order_id; ?>" class="btn btn-primary">
                🔍 Track My Order
            </a>
            <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn btn-secondary">
                🛍️ Continue Shopping
            </a>
        </div>

        <!-- Contact Support -->
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                Need help? Contact us at <a href="mailto:support@somot.com" style="color: #10b981; font-weight: 600;">support@somot.com</a>
            </p>
        </div>
    </div>
    <?php
}

// 6. Add custom CSS to thank you page
add_action('wp_head', 'add_custom_thank_you_styles');
function add_custom_thank_you_styles() {
    if (!is_order_received_page()) {
        return;
    }
    ?>
    <style>
        .woocommerce-order-details,
        .woocommerce-customer-details,
        .woocommerce-order-overview,
        .woocommerce-thankyou-order-details,
        .woocommerce-order,
        .woocommerce-notice,
        .woocommerce-bacs-bank-details,
        ul.order_details,
        .woocommerce-table,
        .woocommerce-columns,
        .woocommerce-column,
        section.woocommerce-order-details,
        section.woocommerce-customer-details {
            display: none !important;
            visibility: hidden !important;
        }
        
        .woocommerce-order-received {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .custom-thank-you-wrapper {
            display: block !important;
            visibility: visible !important;
        }
    </style>
    <?php
}

// 7. Add custom fields to order emails
add_filter('woocommerce_email_order_meta_fields', 'add_custom_fields_to_order_email', 10, 3);
function add_custom_fields_to_order_email($fields, $sent_to_admin, $order) {
    $custom_fields = array();
    
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    
    if ($delivery_type) {
        $custom_fields[] = array('label' => 'Delivery Type', 'value' => ucfirst($delivery_type));
    }
    
    if ($delivery_date) {
        $custom_fields[] = array('label' => ($delivery_type === 'pickup' ? 'Pickup' : 'Delivery') . ' Date', 'value' => $delivery_date);
    }
    
    if ($delivery_time) {
        $custom_fields[] = array('label' => ($delivery_type === 'pickup' ? 'Pickup' : 'Delivery') . ' Time', 'value' => $delivery_time);
    }
    
    if ($branch) {
        $custom_fields[] = array('label' => 'Selected Branch', 'value' => ucfirst(str_replace('_', ' ', $branch)));
    }
    
    $need_vat = $order->get_meta('_need_vat_invoice');
    if ($need_vat === 'yes') {
        $custom_fields[] = array('label' => 'VAT Invoice', 'value' => 'Required');
        
        $vat_company = $order->get_meta('_vat_company_name');
        if ($vat_company) {
            $custom_fields[] = array('label' => 'Company Name', 'value' => $vat_company);
        }
    }
    
    return array_merge($fields, $custom_fields);
}

// 8. Display custom info in admin order page
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_order_info_admin');
function display_custom_order_info_admin($order) {
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $lat = $order->get_meta('_delivery_latitude');
    $lng = $order->get_meta('_delivery_longitude');
    $payment_proof = $order->get_meta('_payment_proof_file');
    
    echo '<div class="custom-order-info" style="padding: 15px; background: #f0f9ff; margin-top: 15px; border-radius: 4px;">';
    echo '<h3 style="margin-top: 0;">🚚 Delivery Information</h3>';
    
    if ($delivery_type) {
        echo '<p><strong>Type:</strong> ' . esc_html(ucfirst($delivery_type)) . '</p>';
    }
    
    if ($delivery_date) {
        echo '<p><strong>Date:</strong> ' . esc_html($delivery_date) . '</p>';
    }
    
    if ($delivery_time) {
        echo '<p><strong>Time:</strong> ' . esc_html($delivery_time) . '</p>';
    }
    
    if ($branch) {
        echo '<p><strong>Branch:</strong> ' . esc_html(ucfirst(str_replace('_', ' ', $branch))) . '</p>';
    }
    
    if ($lat && $lng) {
        echo '<p><strong>Location:</strong> <a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" target="_blank">View on Google Maps</a></p>';
    }
    
    echo '</div>';
    
    if ($payment_proof) {
        echo '<div class="payment-proof-admin" style="padding: 15px; background: #f0fdf4; margin-top: 15px; border-radius: 4px; border-left: 4px solid #10b981;">';
        echo '<h3 style="margin-top: 0;">💳 Payment Proof</h3>';
        echo '<p><a href="' . esc_url($payment_proof) . '" target="_blank" class="button">View Payment Proof</a></p>';
        
        // Show image preview if it's an image
        $file_ext = pathinfo($payment_proof, PATHINFO_EXTENSION);
        if (in_array(strtolower($file_ext), array('jpg', 'jpeg', 'png'))) {
            echo '<img src="' . esc_url($payment_proof) . '" style="max-width: 300px; margin-top: 10px; border: 2px solid #10b981; border-radius: 8px;">';
        }
        echo '</div>';
    }
    
    $need_vat = $order->get_meta('_need_vat_invoice');
    if ($need_vat === 'yes') {
        $vat_company = $order->get_meta('_vat_company_name');
        $vat_address = $order->get_meta('_vat_company_address');
        $vat_tax_code = $order->get_meta('_vat_tax_code');
        
        echo '<div class="vat-info-admin" style="padding: 15px; background: #fff7ed; margin-top: 15px; border-radius: 4px; border-left: 4px solid #f59e0b;">';
        echo '<h3 style="margin-top: 0;">🧾 VAT Invoice Information</h3>';
        
        if ($vat_company) {
            echo '<p><strong>Company Name:</strong> ' . esc_html($vat_company) . '</p>';
        }
        
        if ($vat_address) {
            echo '<p><strong>Company Address:</strong> ' . esc_html($vat_address) . '</p>';
        }
        
        if ($vat_tax_code) {
            echo '<p><strong>Tax Code:</strong> ' . esc_html($vat_tax_code) . '</p>';
        }
        
        echo '</div>';
    }
}
