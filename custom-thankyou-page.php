<?php
// ========================================
// CUSTOM THANK YOU PAGE
// ========================================

// 1. Override thank you page content
add_filter('woocommerce_thankyou_order_received_text', 'custom_thank_you_page_content', 10, 2);
function custom_thank_you_page_content($text, $order) {
    if (!$order) {
        return $text;
    }
    
    ob_start();
    render_custom_thank_you_page($order);
    return ob_get_clean();
}

// 2. Remove default WooCommerce order details
add_action('wp', 'remove_default_order_details');
function remove_default_order_details() {
    if (is_order_received_page()) {
        // Remove default order details
        remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
    }
}

// 3. Render custom thank you page
function render_custom_thank_you_page($order) {
    $order_id = $order->get_id();
    $order_status = $order->get_status();
    $payment_method = $order->get_payment_method();
    $total = $order->get_total();
    
    // Get custom fields
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $need_vat = $order->get_meta('_need_vat_invoice');
    
    ?>
    <style>
        .custom-thank-you-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .success-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-header h1 {
            font-size: 32px;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .success-header p {
            font-size: 16px;
            opacity: 0.9;
            margin: 10px 0;
        }
        
        .order-number {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #718096;
            font-weight: 500;
        }
        
        .info-value {
            color: #2d3748;
            font-weight: 600;
            text-align: right;
        }
        
        .bank-transfer-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 20px;
        }
        
        .bank-accounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .bank-accounts {
                grid-template-columns: 1fr;
            }
        }
        
        .bank-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .bank-card h4 {
            margin: 0 0 15px 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .bank-info p {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .account-number {
            font-size: 20px;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        
        .qr-code-container {
            text-align: center;
            margin-top: 15px;
        }
        
        .qr-code-container img {
            max-width: 200px;
            border-radius: 8px;
            border: 3px solid rgba(255,255,255,0.3);
            background: white;
            padding: 10px;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea !important;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white !important;
            transform: translateY(-2px);
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .order-items-table th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .order-items-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .total-row {
            background: #f7fafc;
            font-weight: bold;
            font-size: 18px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .alert-warning p {
            margin: 5px 0;
            color: #856404;
        }
        
        .vat-info-box {
            background: #fff7ed;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .vat-info-box h3 {
            color: #92400e;
            margin-top: 0;
        }
    </style>

    <div class="custom-thank-you-wrapper">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">✅</div>
            <h1>Thank you for your order!</h1>
            <p>We've received your order and will process it soon.</p>
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
                <span class="info-value"><?php echo $order->get_date_created()->format('F j, Y'); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Status</span>
                <span class="info-value" style="color: #f59e0b;"><?php echo ucfirst($order_status); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="info-value"><?php echo $order->get_payment_method_title(); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value" style="color: #667eea; font-size: 20px;">₱<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <!-- Delivery Information -->
        <?php if ($delivery_type): ?>
        <div class="info-card">
            <div class="card-title">
                🚚 Delivery Information
            </div>
            
            <div class="info-row">
                <span class="info-label">Delivery Type</span>
                <span class="info-value"><?php echo ucfirst($delivery_type); ?></span>
            </div>
            
            <?php if ($delivery_date): ?>
            <div class="info-row">
                <span class="info-label">Delivery Date</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($delivery_date)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($delivery_time): ?>
            <div class="info-row">
                <span class="info-label">Delivery Time</span>
                <span class="info-value"><?php echo date('g:i A', strtotime($delivery_time)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($branch && $delivery_type === 'delivery'): ?>
            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value"><?php echo ucfirst($branch); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">Shipping Address</span>
                <span class="info-value" style="text-align: right; max-width: 60%;">
                    <?php echo $order->get_shipping_address_1(); ?>
                    <?php if ($order->get_shipping_address_2()): ?>
                        , <?php echo $order->get_shipping_address_2(); ?>
                    <?php endif; ?>
                    <br>
                    <?php echo $order->get_shipping_city(); ?>, <?php echo $order->get_shipping_state(); ?> <?php echo $order->get_shipping_postcode(); ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bank Transfer Info (if payment method is bank transfer) -->
        <div class="bank-transfer-section">
            <h2 style="margin: 0 0 10px 0;">💳 Payment Instructions</h2>
            <p style="margin: 0 0 20px 0; opacity: 0.9;">Please complete your payment using one of the following bank accounts:</p>
            
            <div class="bank-accounts">
                <!-- BDO Account -->
                <div class="bank-card">
                    <h4>🏦 BDO Bank</h4>
                    <div class="bank-info">
                        <p><strong>Account Name:</strong><br>Kha V Ngo</p>
                        <div class="account-number">007540182560</div>
                        <div class="qr-code-container">
                            <p style="margin: 0 0 10px 0; font-size: 12px;">Scan QR Code</p>
                            <img src="https://so-mot.com/wp-content/uploads/2025/10/BDO-007540182560-Kha-V-Ngo.jpg" alt="BDO QR Code">
                        </div>
                    </div>
                </div>
                
                <!-- GCash Account -->
                <div class="bank-card">
                    <h4>📱 GCash</h4>
                    <div class="bank-info">
                        <p><strong>Account Name:</strong><br>V**BI*H N</p>
                        <div class="account-number">09950979419</div>
                        <div class="qr-code-container">
                            <p style="margin: 0 0 10px 0; font-size: 12px;">Scan QR Code</p>
                            <img src="https://so-mot.com/wp-content/uploads/2025/10/Gcash-09950979419-V-BI-H-N.jpg" alt="GCash QR Code">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert-warning">
                <p><strong>⚠️ Important:</strong></p>
                <p>• Please send your payment proof/screenshot to confirm your order</p>
                <p>• Include Order #<?php echo $order_id; ?> in your message</p>
                <p>• Your order will be processed after payment confirmation</p>
            </div>
        </div>

        <!-- VAT Invoice Information -->
        <?php if ($need_vat === 'yes'): ?>
        <div class="vat-info-box">
            <h3>🧾 VAT Invoice Required</h3>
            <?php
            $vat_company = $order->get_meta('_vat_company_name');
            $vat_address = $order->get_meta('_vat_company_address');
            $vat_tax_code = $order->get_meta('_vat_tax_code');
            ?>
            
            <?php if ($vat_company): ?>
                <p><strong>Company Name:</strong> <?php echo esc_html($vat_company); ?></p>
            <?php endif; ?>
            
            <?php if ($vat_address): ?>
                <p><strong>Company Address:</strong> <?php echo esc_html($vat_address); ?></p>
            <?php endif; ?>
            
            <?php if ($vat_tax_code): ?>
                <p><strong>Tax Code:</strong> <?php echo esc_html($vat_tax_code); ?></p>
            <?php endif; ?>
            
            <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #fed7aa;">
                <em>Your VAT invoice will be prepared and sent to your email address.</em>
            </p>
        </div>
        <?php endif; ?>

        <!-- Order Items -->
        <div class="info-card">
            <div class="card-title">
                🛒 Order Items
            </div>
            
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->get_items() as $item_id => $item): ?>
                        <?php 
                        $product = $item->get_product();
                        $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($image_url): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($item->get_name()); ?>" class="product-image">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo $item->get_name(); ?></strong>
                                        <?php
                                        // Display variations
                                        $metadata = $item->get_formatted_meta_data();
                                        if (!empty($metadata)) {
                                            echo '<br><small style="color: #718096;">';
                                            foreach ($metadata as $meta) {
                                                echo esc_html($meta->display_key) . ': ' . esc_html($meta->display_value) . '<br>';
                                            }
                                            echo '</small>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $item->get_quantity(); ?></td>
                            <td style="text-align: right;">₱<?php echo number_format($item->get_subtotal() / $item->get_quantity(), 2); ?></td>
                            <td style="text-align: right;"><strong>₱<?php echo number_format($item->get_total(), 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding-top: 15px;"><strong>Subtotal:</strong></td>
                        <td style="text-align: right; padding-top: 15px;"><strong>₱<?php echo number_format($order->get_subtotal(), 2); ?></strong></td>
                    </tr>
                    <?php if ($order->get_shipping_total() > 0): ?>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Shipping:</strong></td>
                        <td style="text-align: right;"><strong>₱<?php echo number_format($order->get_shipping_total(), 2); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>TOTAL:</strong></td>
                        <td style="text-align: right; color: #667eea;"><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="btn btn-primary">
                🛍️ Continue Shopping
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="btn btn-secondary">
                📋 View All Orders
            </a>
        </div>
    </div>
    <?php
}

// 4. Add custom CSS to thank you page
add_action('wp_head', 'add_custom_thank_you_styles');
function add_custom_thank_you_styles() {
    if (!is_order_received_page()) {
        return;
    }
    ?>
    <style>
        /* Hide default WooCommerce elements on thank you page */
        .woocommerce-order-details,
        .woocommerce-customer-details,
        .woocommerce-order-overview {
            display: none !important;
        }
        
        /* Adjust page layout */
        .woocommerce-order-received {
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
    <?php
}
