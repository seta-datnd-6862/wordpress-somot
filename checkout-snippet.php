<?php
// ========================================
// CUSTOM CHECKOUT PAGE WITH 2-STEP PROCESS
// ========================================

// 1. Detect custom checkout page và hiển thị nội dung
add_filter('the_content', 'custom_checkout_page_content');
function custom_checkout_page_content($content) {
    if (is_page('checkout')) {
        ob_start();
        render_custom_checkout();
        return ob_get_clean();
    }
    return $content;
}

// 2. Hàm render custom checkout
function render_custom_checkout() {
    // Kiểm tra giỏ hàng
    if (WC()->cart->is_empty()) {
        echo '<div class="woocommerce"><div class="woocommerce-notices-wrapper">';
        echo '<div class="woocommerce-info">Your shopping cart is empty. <a href="' . get_permalink(wc_get_page_id('shop')) . '">Continue shopping</a></div>';
        echo '</div></div>';
        return;
    }
    
    // Lấy thông tin chi nhánh từ settings
    $branches = array(
        array('id' => 'tayuman', 'name' => 'Tayuman Branch, Manila', 'lat' => 14.6175959, 'lng' => 120.9837713, 'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila, Philippines'),
        array('id' => 'pioneer', 'name' => 'Pioneer Branch, Pasig', 'lat' => 14.5731404, 'lng' => 121.0164509, 'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines'),
        array('id' => 'unimart', 'name' => 'Unimart Branch, Capitol Commons, Pasig', 'lat' => 14.574848, 'lng' => 121.0618259, 'address' => 'Ground Floor, Unimart at Capitol Commons, Shaw Blvd, Pasig, Metro Manila, Philippines'),
    );
    
    ?>
    <style>
        .custom-checkout-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .checkout-header h1 {
            font-size: 32px;
            color: #2d5016;
            margin-bottom: 10px;
        }
        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: #f0f0f0;
            border-radius: 30px;
            font-weight: 600;
            color: #666;
        }
        .step-indicator.active {
            background: #2d5016;
            color: white;
        }
        .step-indicator .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            color: #2d5016;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .step-indicator.active .step-number {
            background: #fff;
            color: #2d5016;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        .checkout-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2d5016;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d5016;
        }
        .delivery-type {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .delivery-option {
            position: relative;
        }
        .delivery-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        .delivery-option label {
            display: block;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .delivery-option input[type="radio"]:checked + label {
            border-color: #2d5016;
            background: #f0f9ff;
        }
        .delivery-option label .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .order-item-quantity {
            color: #666;
            font-size: 14px;
        }
        .order-item-price {
            font-weight: bold;
            color: #2d5016;
        }
        .order-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }
        .summary-row.total {
            border-top: 2px solid #ddd;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 20px;
            font-weight: bold;
            color: #2d5016;
        }
        .place-order-btn {
            width: 100%;
            padding: 18px;
            background: #2d5016;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .place-order-btn:hover {
            background: #1f3810;
        }
        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .hidden {
            display: none;
        }
        .delivery-info-box {
            background: #f0fdf4;
            padding: 15px;
            border-left: 4px solid #10b981;
            border-radius: 4px;
            margin-top: 15px;
        }
        .delivery-info-box p {
            margin: 5px 0;
        }
        .error-message {
            color: #dc2626;
            font-size: 14px;
            margin-top: 5px;
        }
        .required {
            color: #dc2626;
        }
        .location-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #2d5016;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .location-btn:hover {
            background: #1f3810;
        }
        .location-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .branch-suggestion {
            background: #e8f5e9;
            padding: 15px;
            border-left: 4px solid #10b981;
            border-radius: 4px;
            margin-top: 15px;
        }
        .branch-list {
            margin-top: 10px;
        }
        .branch-item {
            padding: 10px;
            background: white;
            border-radius: 4px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .branch-distance {
            color: #2d5016;
            font-weight: bold;
        }
        
        /* Step 2 - Payment Section */
        .payment-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .bank-accounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        @media (max-width: 768px) {
            .bank-accounts {
                grid-template-columns: 1fr;
            }
        }
        .bank-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }
        .bank-card h4 {
            margin: 0 0 15px 0;
            color: #2d3748;
        }
        .account-number {
            font-size: 18px;
            font-weight: bold;
            background: #2d5016;
            color: white;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin: 10px 0;
        }
        .qr-code img {
            max-width: 200px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            margin-top: 10px;
        }
        .file-upload-area {
            border: 2px dashed #2d5016;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background: #f8fafb;
            cursor: pointer;
            transition: all 0.3s;
            margin: 20px 0;
        }
        .file-upload-area:hover {
            background: #f0f4f8;
            border-color: #1f3810;
        }
        .file-upload-area.drag-over {
            background: #e8f5e9;
            border-color: #10b981;
        }
        .file-preview {
            margin-top: 15px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }
        .file-remove-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .alert-info {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            color: #075985;
        }
    </style>

    <div class="custom-checkout-wrapper">
        <div class="checkout-header">
            <h1>CHECKOUT</h1>
        </div>

        <!-- Step Indicators -->
        <div class="checkout-steps">
            <div class="step-indicator active" id="step1-indicator">
                <span class="step-number">1</span>
                <span>Order Details</span>
            </div>
            <div class="step-indicator" id="step2-indicator">
                <span class="step-number">2</span>
                <span>Payment</span>
            </div>
        </div>

        <!-- STEP 1: Order Details -->
        <div id="step1-content">
            <form id="checkout-step1-form">
                <div class="checkout-grid">
                    <!-- LEFT COLUMN -->
                    <div>
                        <!-- Delivery Type -->
                        <div class="checkout-section">
                            <div class="section-title">🚗 Delivery</div>
                            <div class="delivery-type">
                                <div class="delivery-option">
                                    <input type="radio" id="pickup" name="delivery_type" value="pickup" checked>
                                    <label for="pickup">
                                        <div class="icon">📦</div>
                                        <div>Pickup</div>
                                    </label>
                                </div>
                                <div class="delivery-option">
                                    <input type="radio" id="delivery" name="delivery_type" value="delivery">
                                    <label for="delivery">
                                        <div class="icon">🚚</div>
                                        <div>Delivery</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Delivery Date & Time -->
                            <div class="form-group">
                                <label id="delivery-date-label">Pick up date <span class="required">*</span></label>
                                <input type="date" name="delivery_date" id="delivery_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label id="delivery-time-label">Pick up time <span class="required">*</span></label>
                                <input type="time" name="delivery_time" id="delivery_time" required min="07:00" max="23:00">
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="checkout-section" style="margin-top: 20px;">
                            <div class="section-title">👤 Contact information</div>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">We'll use this email to send you details and updates about your order.</p>
                            
                            <div class="form-group">
                                <label>Email address <span class="required">*</span></label>
                                <input type="email" name="email" id="email" required placeholder="your@email.com">
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="checkout-section" style="margin-top: 20px;">
                            <div class="section-title">📮 Shipping address</div>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">Enter the address where you want your order delivered.</p>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>First name <span class="required">*</span></label>
                                    <input type="text" name="first_name" id="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Last name <span class="required">*</span></label>
                                    <input type="text" name="last_name" id="last_name" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone <span class="required">*</span></label>
                                <input type="tel" name="phone" id="phone" required>
                            </div>

                            <!-- Address with Location Button -->
                            <div class="form-group">
                                <label>Street address <span class="required">*</span></label>
                                <input type="text" name="address" id="address-autocomplete" required placeholder="Start typing address...">
                                <input type="hidden" name="address_lat" id="address_lat">
                                <input type="hidden" name="address_lng" id="address_lng">
                                <button type="button" class="location-btn" id="get-location-btn">
                                    📍 Use My Location
                                </button>
                                <div id="address-error" class="error-message hidden">Please select an address from suggestions</div>
                            </div>

                            <!-- Branch Suggestions -->
                            <div id="branch-suggestions" class="branch-suggestion hidden">
                                <h4 style="margin: 0 0 10px 0;">🏢 Nearest Branches:</h4>
                                <div class="branch-list" id="branch-list"></div>
                            </div>

                            <div class="form-group">
                                <label>Apartment, suite, unit, etc. (optional)</label>
                                <input type="text" name="apartment" id="apartment">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Town / City <span class="required">*</span></label>
                                    <select name="city" id="city" required>
                                        <option value="">-- Select City --</option>
                                        <option value="PASAY">PASAY</option>
                                        <option value="PARANAQUE">PARANAQUE</option>
                                        <option value="MAKATI">MAKATI</option>
                                        <option value="MANILA">MANILA</option>
                                        <option value="MANDALUYONG">MANDALUYONG</option>
                                        <option value="TAGUIG">TAGUIG</option>
                                        <option value="PASIG">PASIG</option>
                                        <option value="SAN JUAN">SAN JUAN</option>
                                        <option value="MALABON">MALABON</option>
                                        <option value="MARIKINA">MARIKINA</option>
                                        <option value="QUEZON CITY">QUEZON CITY</option>
                                        <option value="LAS PIÑAS">LAS PIÑAS</option>
                                        <option value="VALENZUELA">VALENZUELA</option>
                                        <option value="CALOOCAN">CALOOCAN</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>State / County <span class="required">*</span></label>
                                    <select name="state" id="state" required>
                                        <option value="Metro Manila">Metro Manila</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Postcode / ZIP <span class="required">*</span></label>
                                <input type="text" name="postcode" id="postcode" required>
                            </div>

                            <!-- Delivery Address Fields (Hidden by default for pickup) -->
                            <div id="delivery-address-fields" class="hidden">
                                <div class="form-group">
                                    <label>Country / Region <span class="required">*</span></label>
                                    <select name="country" id="country" class="delivery-required">
                                        <option value="Philippines" selected>Philippines</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Distance Info -->
                            <div class="delivery-info-box hidden" id="distance-info-box">
                                <p><strong>🚚 Distance:</strong> <span id="distance-display">0</span> km</p>
                                <p><strong>💰 Shipping fee:</strong> <span id="shipping-fee-display">₱0</span></p>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" name="need_vat" id="need_vat" value="1" style="width: auto;">
                                    Do you need VAT invoice?
                                </label>
                            </div>

                            <!-- VAT Fields -->
                            <div id="vat-fields" class="hidden">
                                <div class="form-group">
                                    <label>Company name <span class="required">*</span></label>
                                    <input type="text" name="company" id="company" class="vat-required">
                                </div>

                                <div class="form-group">
                                    <label>Company address <span class="required">*</span></label>
                                    <input type="text" name="company_address" id="company_address" class="vat-required">
                                </div>

                                <div class="form-group">
                                    <label>Tax Code <span class="required">*</span></label>
                                    <input type="text" name="tax_code" id="tax_code" class="vat-required" placeholder="e.g., 0123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Branch Selection -->
                        <div class="checkout-section" id="branch-section" style="margin-top: 20px;">
                            <div class="section-title">🏢 Select Branch</div>
                            <div class="form-group">
                                <label>Choose branch <span class="required">*</span></label>
                                <select name="branch" id="branch-select" required>
                                    <option value="">-- Select Branch --</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>" 
                                                data-lat="<?php echo $branch['lat']; ?>" 
                                                data-lng="<?php echo $branch['lng']; ?>"
                                                data-address="<?php echo esc_attr($branch['address']); ?>">
                                            <?php echo esc_html($branch['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="checkout-section" style="margin-top: 20px;">
                            <div class="form-group">
                                <label>Add a note to your order</label>
                                <textarea name="order_notes" id="order_notes" rows="4" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN - Order Summary -->
                    <div>
                        <div class="checkout-section" style="position: sticky; top: 20px;">
                            <div class="section-title">📋 Order summary</div>

                            <div id="order-items">
                                <?php
                                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                                    
                                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                                        ?>
                                        <div class="order-item">
                                            <img src="<?php echo esc_url(wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail')); ?>" alt="<?php echo esc_attr($_product->get_name()); ?>">
                                            <div class="order-item-info">
                                                <div class="order-item-name"><?php echo wp_kses_post($_product->get_name()); ?></div>
                                                <div class="order-item-quantity">Quantity: <?php echo $cart_item['quantity']; ?></div>
                                                <?php
                                                if (!empty($cart_item['variation'])) {
                                                    echo '<div class="order-item-meta" style="font-size: 12px; color: #666;">';
                                                    foreach ($cart_item['variation'] as $key => $value) {
                                                        echo esc_html(ucfirst(str_replace('attribute_', '', $key))) . ': ' . esc_html($value) . '<br>';
                                                    }
                                                    echo '</div>';
                                                }
                                                ?>
                                            </div>
                                            <div class="order-item-price">
                                                ₱<?php echo number_format($cart_item['line_total'], 2); ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span id="subtotal">₱<?php echo number_format(WC()->cart->get_subtotal(), 2); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping Fee</span>
                                    <span id="shipping-fee">₱0.00</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total</span>
                                    <span id="total">₱<?php echo number_format(WC()->cart->get_subtotal(), 2); ?></span>
                                </div>
                            </div>

                            <button type="button" class="place-order-btn" id="proceed-to-payment-btn">
                                Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 2: Payment -->
        <div id="step2-content" class="hidden">
            <div class="checkout-grid">
                <div>
                    <div class="payment-section">
                        <div class="section-title">💳 Complete Your Payment</div>
                        
                        <div class="alert-info">
                            <p><strong>📋 Order Summary</strong></p>
                            <p>Total Amount: <strong style="font-size: 20px; color: #2d5016;">₱<span id="payment-total">0.00</span></strong></p>
                        </div>

                        <h3 style="margin: 20px 0 15px 0; color: #2d3748;">Choose Your Payment Method:</h3>
                        
                        <div class="bank-accounts">
                            <!-- BDO Account -->
                            <div class="bank-card">
                                <h4>🏦 BDO Bank</h4>
                                <p style="margin: 5px 0; color: #666;">Account Name: <strong>Kha V Ngo</strong></p>
                                <div class="account-number">007540182560</div>
                                <div class="qr-code" style="text-align: center;">
                                    <p style="font-size: 12px; margin: 10px 0 5px 0;">Scan QR Code:</p>
                                    <img src="https://so-mot.com/wp-content/uploads/2025/10/BDO-007540182560-Kha-V-Ngo.jpg" alt="BDO QR Code">
                                </div>
                            </div>
                            
                            <!-- GCash Account -->
                            <div class="bank-card">
                                <h4>📱 GCash</h4>
                                <p style="margin: 5px 0; color: #666;">Account Name: <strong>V**BI*H N</strong></p>
                                <div class="account-number">09950979419</div>
                                <div class="qr-code" style="text-align: center;">
                                    <p style="font-size: 12px; margin: 10px 0 5px 0;">Scan QR Code:</p>
                                    <img src="https://so-mot.com/wp-content/uploads/2025/10/Gcash-09950979419-V-BI-H-N.jpg" alt="GCash QR Code">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 30px;">
                            <h4 style="margin: 0 0 15px 0; color: #2d3748;">📸 Upload Payment Proof</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">Please upload your payment screenshot or receipt to confirm your order</p>
                            
                            <!-- File Upload Area -->
                            <div id="file-upload-area" class="file-upload-area">
                                <p style="margin: 0 0 10px 0; font-size: 32px;">📁</p>
                                <p style="margin: 0 0 5px 0; font-weight: 600;">Drag & Drop your file here</p>
                                <p style="margin: 0; color: #666; font-size: 14px;">or click to browse (PNG, JPG, PDF - Max 5MB)</p>
                            </div>
                            
                            <input type="file" id="payment-proof-file" accept=".png,.jpg,.jpeg,.pdf" style="display: none;">
                            
                            <!-- File Preview -->
                            <div id="file-preview" class="file-preview hidden">
                                <p style="margin: 0 0 10px 0;">
                                    <strong>✅ File selected:</strong><br>
                                    <span id="file-name-display" style="color: #2d5016; font-weight: 600;"></span>
                                </p>
                                <button type="button" class="file-remove-btn" onclick="removePaymentFile()">
                                    ❌ Remove File
                                </button>
                            </div>

                            <div id="file-error" class="error-message hidden"></div>
                        </div>

                        <div style="margin-top: 30px; display: flex; gap: 15px;">
                            <button type="button" class="place-order-btn" style="background: #666;" onclick="backToStep1()">
                                ← Back
                            </button>
                            <button type="button" class="place-order-btn" id="complete-order-btn" disabled>
                                Complete Order
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="checkout-section" style="position: sticky; top: 20px;">
                        <div class="section-title">📋 Order Details</div>
                        <div id="order-summary-step2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXShFxiu-eawxmLBhT8NamWJK7giYd6Dc&libraries=places"></script>

    <script>
    jQuery(document).ready(function($) {
        let destinationAutocomplete;
        let selectedPlace = null;
        let branchLocation = {lat: 14.6175959, lng: 120.9837713};
        let calculatedShippingFee = 0;
        let selectedFile = null;
        let orderData = {};
        
        const branches = <?php echo json_encode($branches); ?>;
        
        // Initialize Google Maps Autocomplete
        function initAutocomplete() {
            const destinationInput = document.getElementById('address-autocomplete');
            
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);
                
                destinationAutocomplete.addListener('place_changed', function() {
                    selectedPlace = destinationAutocomplete.getPlace();
                    
                    if (!selectedPlace.geometry) {
                        $('#address-error').removeClass('hidden');
                        $('#address_lat').val('');
                        $('#address_lng').val('');
                        return;
                    }
                    
                    $('#address-error').addClass('hidden');
                    const lat = selectedPlace.geometry.location.lat();
                    const lng = selectedPlace.geometry.location.lng();
                    
                    $('#address_lat').val(lat);
                    $('#address_lng').val(lng);
                    
                    // Show nearest branches
                    showNearestBranches(lat, lng);
                    
                    if ($('input[name="delivery_type"]:checked').val() === 'delivery') {
                        calculateDistance(lat, lng);
                    }
                });
            }
        }
        
        // Get user location
        $('#get-location-btn').click(function() {
            if (navigator.geolocation) {
                $(this).prop('disabled', true).text('Getting location...');
                
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    $('#address_lat').val(lat);
                    $('#address_lng').val(lng);
                    
                    // Reverse geocode
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({location: {lat: lat, lng: lng}}, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            $('#address-autocomplete').val(results[0].formatted_address);
                            showNearestBranches(lat, lng);
                            
                            if ($('input[name="delivery_type"]:checked').val() === 'delivery') {
                                calculateDistance(lat, lng);
                            }
                        }
                        $('#get-location-btn').prop('disabled', false).text('📍 Use My Location');
                    });
                }, function() {
                    alert('Could not get your location. Please enter address manually.');
                    $('#get-location-btn').prop('disabled', false).text('📍 Use My Location');
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });
        
        // Show nearest branches
        function showNearestBranches(lat, lng) {
            const distances = branches.map(branch => {
                const distance = calculateDistanceKm(lat, lng, branch.lat, branch.lng);
                return {
                    ...branch,
                    distance: distance
                };
            }).sort((a, b) => a.distance - b.distance);
            
            let html = '';
            distances.forEach(branch => {
                html += '<div class="branch-item">' +
                    '<div><strong>' + branch.name + '</strong></div>' +
                    '<div class="branch-distance">' + branch.distance.toFixed(2) + ' km</div>' +
                    '</div>';
            });
            
            $('#branch-list').html(html);
            $('#branch-suggestions').removeClass('hidden');
        }
        
        // Calculate distance between two points
        function calculateDistanceKm(lat1, lng1, lat2, lng2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lng2 - lng1) * Math.PI / 180;
            
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                     Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                     Math.sin(dLon/2) * Math.sin(dLon/2);
            
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
        
        // Calculate distance for delivery
        function calculateDistance(destLat, destLng) {
            const distance = calculateDistanceKm(branchLocation.lat, branchLocation.lng, destLat, destLng);
            
            $('#distance-display').text(distance.toFixed(2));
            $('#distance-info-box').removeClass('hidden');
            
            getShippingFee(distance);
        }
        
        // Get shipping fee
        function getShippingFee(distance) {
            const deliveryTime = $('#delivery_time').val();
            const deliveryDate = $('#delivery_date').val();
            const currentHour = new Date(deliveryDate + 'T' + deliveryTime).getHours();
            const nightShift = (currentHour >= 22 || currentHour <= 6) ? 1 : 0;
            
            $.ajax({
                url: 'https://goodriver.online/api/setting/get-delivery-fee',
                type: 'POST',
                data: {
                    distance: distance.toFixed(2),
                    cash_on_delivery: 0,
                    holiday: 0,
                    night_shift: nightShift,
                    raining: 0
                },
                success: function(response) {
                    calculatedShippingFee = response.data.total_delivery_fee || 0;
                    updateOrderTotal();
                },
                error: function() {
                    calculatedShippingFee = 0;
                    updateOrderTotal();
                }
            });
        }
        
        // Update order total
        function updateOrderTotal() {
            const subtotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            const shippingFee = calculatedShippingFee;
            const total = subtotal + shippingFee;
            
            $('#shipping-fee').text('₱' + shippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#shipping-fee-display').text('₱' + shippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#total').text('₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }
        
        // Delivery type change
        $('input[name="delivery_type"]').change(function() {
            if ($(this).val() === 'pickup') {
                $('#delivery-date-label').html('Pick up date <span class="required">*</span>');
                $('#delivery-time-label').html('Pick up time <span class="required">*</span>');
                $('#delivery-address-fields').addClass('hidden');
                $('#distance-info-box').addClass('hidden');
                $('.delivery-required').removeAttr('required');
                calculatedShippingFee = 0;
                updateOrderTotal();
            } else {
                $('#delivery-date-label').html('Delivery date <span class="required">*</span>');
                $('#delivery-time-label').html('Delivery time <span class="required">*</span>');
                $('#delivery-address-fields').removeClass('hidden');
                $('.delivery-required').attr('required', 'required');
                
                if ($('#address_lat').val() && $('#address_lng').val()) {
                    calculateDistance(
                        parseFloat($('#address_lat').val()),
                        parseFloat($('#address_lng').val())
                    );
                }
            }
        });
        
        // Branch selection change
        $('#branch-select').change(function() {
            const selectedOption = $(this).find('option:selected');
            branchLocation.lat = parseFloat(selectedOption.data('lat'));
            branchLocation.lng = parseFloat(selectedOption.data('lng'));
            
            if ($('#address_lat').val() && $('#address_lng').val()) {
                calculateDistance(
                    parseFloat($('#address_lat').val()),
                    parseFloat($('#address_lng').val())
                );
            }
        });
        
        // VAT checkbox
        $('#need_vat').change(function() {
            if ($(this).is(':checked')) {
                $('#vat-fields').removeClass('hidden');
                $('.vat-required').attr('required', 'required');
            } else {
                $('#vat-fields').addClass('hidden');
                $('.vat-required').removeAttr('required');
            }
        });
        
        // Proceed to payment
        $('#proceed-to-payment-btn').click(function() {
            // Validate form
            const form = $('#checkout-step1-form')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const deliveryType = $('input[name="delivery_type"]:checked').val();
            
            if (deliveryType === 'delivery') {
                if (!$('#branch-select').val()) {
                    alert('Please select a branch');
                    return;
                }
                if (!$('#address_lat').val() || !$('#address_lng').val()) {
                    alert('Please select a valid address from suggestions');
                    $('#address-error').removeClass('hidden');
                    return;
                }
            }
            
            // Store order data
            orderData = {
                delivery_type: deliveryType,
                delivery_date: $('#delivery_date').val(),
                delivery_time: $('#delivery_time').val(),
                email: $('#email').val(),
                first_name: $('#first_name').val(),
                last_name: $('#last_name').val(),
                phone: $('#phone').val(),
                address: $('#address-autocomplete').val(),
                address_lat: $('#address_lat').val(),
                address_lng: $('#address_lng').val(),
                apartment: $('#apartment').val(),
                city: $('#city').val(),
                state: $('#state').val(),
                postcode: $('#postcode').val(),
                country: $('#country').val(),
                branch: $('#branch-select').val(),
                need_vat: $('#need_vat').is(':checked') ? 'yes' : 'no',
                company: $('#company').val(),
                company_address: $('#company_address').val(),
                tax_code: $('#tax_code').val(),
                order_notes: $('#order_notes').val(),
                shipping_fee: calculatedShippingFee
            };
            
            // Switch to step 2
            $('#step1-content').addClass('hidden');
            $('#step2-content').removeClass('hidden');
            $('#step1-indicator').removeClass('active');
            $('#step2-indicator').addClass('active');
            
            // Update payment total
            const subtotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            const total = subtotal + calculatedShippingFee;
            $('#payment-total').text(total.toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Copy order summary
            $('#order-summary-step2').html($('#order-items').html() + $('.order-summary').prop('outerHTML'));
            
            // Scroll to top
            window.scrollTo(0, 0);
        });
        
        // File upload handling
        const uploadArea = document.getElementById('file-upload-area');
        const fileInput = document.getElementById('payment-proof-file');
        const filePreview = document.getElementById('file-preview');
        const fileNameDisplay = document.getElementById('file-name-display');
        const fileError = document.getElementById('file-error');
        const completeBtn = document.getElementById('complete-order-btn');
        
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        function handleFileSelect(file) {
            fileError.classList.add('hidden');
            
            const allowedTypes = ['image/png', 'image/jpeg', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                showFileError('Invalid file type. Only PNG, JPG, and PDF are allowed.');
                return;
            }
            
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showFileError('File size must be less than 5MB.');
                return;
            }
            
            selectedFile = file;
            fileNameDisplay.textContent = file.name;
            filePreview.classList.remove('hidden');
            completeBtn.disabled = false;
            completeBtn.style.opacity = '1';
            completeBtn.style.cursor = 'pointer';
        }
        
        window.removePaymentFile = function() {
            selectedFile = null;
            fileInput.value = '';
            filePreview.classList.add('hidden');
            fileError.classList.add('hidden');
            completeBtn.disabled = true;
            completeBtn.style.opacity = '0.5';
            completeBtn.style.cursor = 'not-allowed';
        };
        
        function showFileError(message) {
            fileError.textContent = '❌ ' + message;
            fileError.classList.remove('hidden');
            removePaymentFile();
        }
        
        // Complete order
        $('#complete-order-btn').click(function() {
            if (!selectedFile) {
                alert('Please upload payment proof before completing order.');
                return;
            }
            
            if (!confirm('Have you completed the payment? Please make sure your payment has been sent before confirming.')) {
                return;
            }
            
            $(this).prop('disabled', true).text('Processing...');
            
            const formData = new FormData();
            formData.append('action', 'process_complete_checkout');
            formData.append('payment_file', selectedFile);
            
            // Add order data
            Object.keys(orderData).forEach(key => {
                formData.append(key, orderData[key]);
            });
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert(response.data.message || 'Error processing order');
                        $('#complete-order-btn').prop('disabled', false).text('Complete Order');
                    }
                },
                error: function() {
                    alert('Error processing order. Please try again.');
                    $('#complete-order-btn').prop('disabled', false).text('Complete Order');
                }
            });
        });
        
        // Back to step 1
        window.backToStep1 = function() {
            $('#step2-content').addClass('hidden');
            $('#step1-content').removeClass('hidden');
            $('#step2-indicator').removeClass('active');
            $('#step1-indicator').addClass('active');
            window.scrollTo(0, 0);
        };
        
        // Initialize
        if (typeof google !== 'undefined') {
            initAutocomplete();
        } else {
            setTimeout(initAutocomplete, 1000);
        }
    });
    </script>
    <?php
}

// AJAX handler for processing complete checkout with payment proof
add_action('wp_ajax_process_complete_checkout', 'process_complete_checkout');
add_action('wp_ajax_nopriv_process_complete_checkout', 'process_complete_checkout');
function process_complete_checkout() {
    try {
        // Create order
        $order = wc_create_order();
        
        // Add products
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $order->add_product($cart_item['data'], $cart_item['quantity']);
        }
        
        // Set billing details
        $order->set_billing_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_billing_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_billing_email(sanitize_email($_POST['email']));
        $order->set_billing_phone(sanitize_text_field($_POST['phone']));
        $order->set_billing_address_1(sanitize_text_field($_POST['address']));
        $order->set_billing_address_2(sanitize_text_field($_POST['apartment']));
        $order->set_billing_city(sanitize_text_field($_POST['city']));
        $order->set_billing_state(sanitize_text_field($_POST['state']));
        $order->set_billing_postcode(sanitize_text_field($_POST['postcode']));
        $order->set_billing_country(sanitize_text_field($_POST['country']));
        $order->set_billing_company(sanitize_text_field($_POST['company']));
        
        // Set shipping details
        $order->set_shipping_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_shipping_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_shipping_address_1(sanitize_text_field($_POST['address']));
        $order->set_shipping_address_2(sanitize_text_field($_POST['apartment']));
        $order->set_shipping_city(sanitize_text_field($_POST['city']));
        $order->set_shipping_state(sanitize_text_field($_POST['state']));
        $order->set_shipping_postcode(sanitize_text_field($_POST['postcode']));
        $order->set_shipping_country(sanitize_text_field($_POST['country']));
        
        // Set custom meta
        $order->update_meta_data('_delivery_type', sanitize_text_field($_POST['delivery_type']));
        $order->update_meta_data('_delivery_date', sanitize_text_field($_POST['delivery_date']));
        $order->update_meta_data('_delivery_time', sanitize_text_field($_POST['delivery_time']));
        $order->update_meta_data('_order_notes', sanitize_textarea_field($_POST['order_notes']));
        $order->update_meta_data('_selected_branch', sanitize_text_field($_POST['branch']));
        
        if ($_POST['delivery_type'] === 'delivery') {
            $order->update_meta_data('_delivery_latitude', sanitize_text_field($_POST['address_lat']));
            $order->update_meta_data('_delivery_longitude', sanitize_text_field($_POST['address_lng']));
            
            $shipping_fee = floatval($_POST['shipping_fee']);
            if ($shipping_fee > 0) {
                $item = new WC_Order_Item_Shipping();
                $item->set_method_title('Delivery');
                $item->set_method_id('custom_delivery');
                $item->set_total($shipping_fee);
                $order->add_item($item);
            }
        }
        
        // VAT info
        if ($_POST['need_vat'] === 'yes') {
            $order->update_meta_data('_need_vat_invoice', 'yes');
            $order->update_meta_data('_vat_company_name', sanitize_text_field($_POST['company']));
            $order->update_meta_data('_vat_company_address', sanitize_text_field($_POST['company_address']));
            $order->update_meta_data('_vat_tax_code', sanitize_text_field($_POST['tax_code']));
        }
        
        // Set payment method
        $order->set_payment_method('bacs');
        $order->set_payment_method_title('Online Bank Transfer');
        
        // Handle payment proof upload
        if (isset($_FILES['payment_file'])) {
            $file = $_FILES['payment_file'];
            $upload_dir = wp_upload_dir();
            $payment_dir = $upload_dir['basedir'] . '/payment-proofs';
            
            if (!file_exists($payment_dir)) {
                mkdir($payment_dir, 0755, true);
            }
            
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'order_pending_' . time() . '.' . $file_ext;
            $filepath = $payment_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $file_url = $upload_dir['baseurl'] . '/payment-proofs/' . $filename;
                $order->update_meta_data('_payment_proof_file', $file_url);
                $order->add_order_note('Payment proof uploaded: ' . $filename);
            }
        }
        
        // Calculate totals and save
        $order->calculate_totals();
        $order->save();
        
        // Create customer account
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        
        if (!email_exists($email) && !username_exists($email)) {
            $random_password = wp_generate_password(12, false);
            
            $user_id = wp_create_user($email, $random_password, $email);
            
            if (!is_wp_error($user_id)) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name,
                    'role' => 'customer'
                ));
                
                update_user_meta($user_id, 'billing_phone', $phone);
                
                // Send password email
                wp_mail(
                    $email,
                    'Your Account Has Been Created - Somot',
                    "Hello $first_name,\n\nYour account has been created successfully!\n\nEmail: $email\nPassword: $random_password\n\nYou can login at: " . wp_login_url() . "\n\nThank you for shopping with us!",
                    array('Content-Type: text/plain; charset=UTF-8')
                );
                
                // Associate order with customer
                $order->set_customer_id($user_id);
                $order->save();
            }
        }
        
        // Empty cart
        WC()->cart->empty_cart();
        
        wp_send_json_success(array(
            'redirect' => $order->get_checkout_order_received_url()
        ));
        
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}

// Display custom checkout info in admin
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_checkout_info_in_admin');
function display_custom_checkout_info_in_admin($order) {
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $lat = $order->get_meta('_delivery_latitude');
    $lng = $order->get_meta('_delivery_longitude');
    $payment_proof = $order->get_meta('_payment_proof_file');
    
    echo '<div class="custom-checkout-info" style="padding: 15px; background: #f0f9ff; margin-top: 15px; border-radius: 4px;">';
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
        echo '<p><strong>Branch:</strong> ' . esc_html($branch) . '</p>';
    }
    
    if ($lat && $lng) {
        echo '<p><strong>Location:</strong> <a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" target="_blank">View on Google Maps</a></p>';
    }
    
    if ($payment_proof) {
        echo '<p><strong>Payment Proof:</strong> <a href="' . esc_url($payment_proof) . '" target="_blank">View File</a></p>';
    }
    
    echo '</div>';
    
    $need_vat = $order->get_meta('_need_vat_invoice');
    if ($need_vat === 'yes') {
        $vat_company = $order->get_meta('_vat_company_name');
        $vat_address = $order->get_meta('_vat_company_address');
        $vat_tax_code = $order->get_meta('_vat_tax_code');
        
        echo '<div class="vat-invoice-info" style="padding: 15px; background: #fff7ed; margin-top: 15px; border-radius: 4px; border-left: 4px solid #f59e0b;">';
        echo '<h3 style="margin-top: 0;">🧾 VAT Invoice Required</h3>';
        
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

// Add custom content to order emails
add_action('woocommerce_email_before_order_table', 'add_custom_content_to_order_email', 20, 4);
function add_custom_content_to_order_email($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin) {
        return;
    }
    
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $need_vat = $order->get_meta('_need_vat_invoice');
    
    if ($plain_text) {
        echo "\n========================================\n";
        echo "DELIVERY INFORMATION\n";
        echo "========================================\n\n";
        
        if ($delivery_type) echo "Delivery Type: " . ucfirst($delivery_type) . "\n";
        if ($delivery_date) echo "Delivery Date: " . $delivery_date . "\n";
        if ($delivery_time) echo "Delivery Time: " . $delivery_time . "\n";
        if ($branch) echo "Branch: " . ucfirst($branch) . "\n";
        
        echo "\n";
    } else {
        ?>
        <div style="margin-bottom: 40px; padding: 20px; background-color: #f7fafc; border-radius: 8px;">
            <h2 style="color: #2d3748; margin-top: 0;">🚚 Delivery Information</h2>
            
            <?php if ($delivery_type): ?>
                <p><strong>Type:</strong> <?php echo esc_html(ucfirst($delivery_type)); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_date): ?>
                <p><strong>Date:</strong> <?php echo esc_html($delivery_date); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_time): ?>
                <p><strong>Time:</strong> <?php echo esc_html($delivery_time); ?></p>
            <?php endif; ?>
            
            <?php if ($branch): ?>
                <p><strong>Branch:</strong> <?php echo esc_html(ucfirst($branch)); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
