// ========================================
// CUSTOM CHECKOUT PAGE WITH 2-STEP PROCESS (WITH ADD-ONS SUPPORT)
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
        array(
            'id' => 'pioneer', 
            'name' => 'So Mot Pioneer Center, Pasig city', 
            'lat' => 14.5731404, 
            'lng' => 121.0164509, 
            'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines',
            'start_time' => '07:00',
            'end_time' => '23:00'
        ),
        array(
            'id' => 'ayala', 
            'name' => 'So Mot Ayala Malls Cloverleaf', 
            'lat' => 14.6550542, 
            'lng' => 120.9630123, 
            'address' => 'A. Bonifacio Ave, La Loma, Quezon City, 1115 Metro Manila, Philippines',
            'start_time' => '10:00',
            'end_time' => '22:00'
        ),
        array(
            'id' => 'tayuman', 
            'name' => 'So Mot Tayuman, Santa Cruz, Manila', 
            'lat' => 14.617797968904622, 
            'lng' => 120.98393022997824, 
            'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila',
            'start_time' => '08:00',
            'end_time' => '22:00'
        ),
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
            padding: 10px;
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
        .order-item-meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .order-item-addons {
            font-size: 13px;
            color: #2d5016;
            margin-top: 8px;
            padding: 8px;
            background: #f0f9ff;
            border-radius: 4px;
            border-left: 3px solid #2d5016;
        }
        .addon-item {
            margin-bottom: 4px;
        }
        .addon-item:last-child {
            margin-bottom: 0;
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

        /* Coupon Section */
        .coupon-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .coupon-input-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .coupon-input-wrapper input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .coupon-input-wrapper input:focus {
            outline: none;
            border-color: #2d5016;
        }

        .coupon-apply-btn {
            padding: 12px 24px;
            background: #2d5016;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .coupon-apply-btn:hover {
            background: #1f3810;
        }

        .coupon-apply-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .coupon-message {
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 10px;
        }

        .coupon-message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .coupon-message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .applied-coupons {
            margin-top: 15px;
        }

        .coupon-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #e8f5e9;
            border-radius: 20px;
            font-size: 13px;
            color: #2d5016;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 1px solid #4caf50;
        }

        .coupon-tag-code {
            font-weight: 700;
            text-transform: uppercase;
        }

        .coupon-tag-discount {
            color: #1b5e20;
        }

        .coupon-remove-btn {
            background: none;
            border: none;
            color: #f44336;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .coupon-remove-btn:hover {
            color: #d32f2f;
        }

        /* Available Coupons Section */
        .available-coupons {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .available-coupons-title {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .available-coupons-title:hover {
            color: #2d5016;
        }

        .coupon-toggle-icon {
            transition: transform 0.3s;
        }

        .coupon-toggle-icon.open {
            transform: rotate(180deg);
        }

        .available-coupons-list {
            display: grid;
            gap: 12px;
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .available-coupons-list::-webkit-scrollbar {
            width: 6px;
        }

        .available-coupons-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .available-coupons-list::-webkit-scrollbar-thumb {
            background: #2d5016;
            border-radius: 3px;
        }

        .coupon-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #61c3a2 100%);
            border: 2px solid #3B7D3B;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .coupon-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #3B7D3B;
        }

        .coupon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 125, 59, 0.2);
            border-color: #3B7D3B;
        }

        .coupon-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
            border-color: #ddd;
        }

        .coupon-card.disabled::before {
            background: #999;
        }

        .coupon-card.disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .coupon-card.applied {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-color: #4caf50;
        }

        .coupon-card.applied::before {
            background: #4caf50;
        }

        .coupon-info {
            flex: 1;
            padding-left: 8px;
        }

        .coupon-code-display {
            font-size: 16px;
            font-weight: 700;
            color: #3B7D3B;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .coupon-card.applied .coupon-code-display {
            color: #2e7d32;
        }

        .coupon-description {
            font-size: 13px;
            color: #1e293b;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .coupon-details {
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        .coupon-discount {
            font-size: 18px;
            font-weight: 700;
            color: #3B7D3B;
            white-space: nowrap;
            margin-right: 12px;
        }

        .coupon-card.applied .coupon-discount {
            color: #2e7d32;
        }

        .coupon-apply-small-btn {
            padding: 8px 16px;
            background: #3B7D3B;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .coupon-apply-small-btn:hover {
            background: #3B7D3B;
            transform: scale(1.05);
        }

        .coupon-apply-small-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .coupon-card.applied .coupon-apply-small-btn {
            background: #4caf50;
        }

        .no-coupons-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }

        .coupon-conditions {
            margin-top: 4px;
            font-size: 11px;
            color: #ef4444;
        }

        .loading-coupons {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .discount-row {
            color: #10b981;
        }
        /* Delivery Area Validation */
        .delivery-area-error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            display: none;
        }

        .delivery-area-error.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delivery-area-error h4 {
            margin: 0 0 10px 0;
            color: #c62828;
            font-size: 16px;
            font-weight: 700;
        }

        .delivery-area-error p {
            margin: 5px 0;
            color: #c62828;
            font-size: 14px;
            line-height: 1.6;
        }

        .delivery-area-error ul {
            margin: 10px 0;
            padding-left: 25px;
            color: #c62828;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
        }

        .delivery-area-error li {
            margin: 3px 0;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .delivery-area-error ul {
                grid-template-columns: 1fr;
            }
        }
										
		@media (min-width: 922px) {
			.ast-container {
				max-width: 1690px !important;
			}
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
                        </div>

                        <!-- Contact Information -->
                        <div class="checkout-section" style="margin-top: 20px;">
                            <div class="section-title">👤 Contact information</div>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">We will use this email to send you details and updates about your order.</p>
                            
                            <div class="form-group">
                                <label>Email address <span class="required">*</span></label>
                                <input type="email" name="email" id="email" required placeholder="your@email.com" value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->user_email) : ''; ?>">
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="checkout-section" style="margin-top: 20px;">
                            <div class="section-title">📮 Shipping address</div>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">Enter the address where you want your order delivered.</p>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>First name <span class="required">*</span></label>
                                    <input type="text" name="first_name" id="first_name" required value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->first_name) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Last name <span class="required">*</span></label>
                                    <input type="text" name="last_name" id="last_name" required value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->last_name) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone <span class="required">*</span></label>
                                <input type="tel" name="phone" id="phone" required value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_phone', true)) : ''; ?>">
                            </div>

                            <!-- Address with Location Button -->
                            <div class="form-group">
                                <label>Street address <span class="required">*</span></label>
                                <input type="text" name="address" id="address-autocomplete" required placeholder="Start typing address..." value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_address_1', true)) : ''; ?>">
                                <input type="hidden" name="address_lat" id="address_lat">
                                <input type="hidden" name="address_lng" id="address_lng">
                                <button type="button" class="location-btn" id="get-location-btn">
                                    📍 Use My Location
                                </button>
                                <div id="address-error" class="error-message hidden">Please select an address from suggestions</div>

                                <!-- Delivery Area Warning -->
                                <div id="delivery-area-warning" class="delivery-area-error">
                                    <h4>⚠️ Out of Delivery Area</h4>
                                    <p>Sorry, we currently only deliver to the following cities in Metro Manila:</p>
                                    <ul>
                                        <li>PASAY</li>
                                        <li>PARANAQUE</li>
                                        <li>MAKATI</li>
                                        <li>MANILA</li>
                                        <li>MANDALUYONG</li>
                                        <li>TAGUIG</li>
                                        <li>PASIG</li>
                                        <li>SAN JUAN</li>
                                        <li>MALABON</li>
                                        <li>MARIKINA</li>
                                        <li>QUEZON CITY</li>
                                        <li>LAS PIÑAS</li>
                                        <li>VALENZUELA</li>
                                        <li>CALOOCAN</li>
                                    </ul>
                                    <p><strong>Please select an address within these areas or choose "Pickup" instead.</strong></p>
                                </div>
                            </div>

                            <!-- Branch Suggestions -->
                            <div id="branch-suggestions" class="branch-suggestion hidden">
                                <h4 style="margin: 0 0 10px 0;">🏢 Nearest Branches:</h4>
                                <div class="branch-list" id="branch-list"></div>
                            </div>

                            <!-- Branch Selection -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Choose branch <span class="required">*</span></label>
                                    <select name="branch" id="branch-select" required>
                                        <option value="">-- Select Branch --</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['id']; ?>" 
                                                    data-lat="<?php echo $branch['lat']; ?>" 
                                                    data-lng="<?php echo $branch['lng']; ?>"
                                                    data-address="<?php echo esc_attr($branch['address']); ?>"
                                                    data-start-time="<?php echo $branch['start_time']; ?>"
                                                    data-end-time="<?php echo $branch['end_time']; ?>">
                                                <?php echo esc_html($branch['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Delivery Date & Time -->
                            <div class="form-group">
                                <label id="delivery-date-label">Pick up date <span class="required">*</span></label>
                                <input type="date" name="delivery_date" id="delivery_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label id="delivery-time-label" for="delivery_time">Pick up time <span class="required">*</span></label>
                                <select name="delivery_time" id="delivery_time_select" class="test" required>
                                    <!-- select time each 15 mins from 07:00 to 23:00 -->
                                    <?php
                                    for ($hour = 7; $hour < 23; $hour++) {
                                        for ($min = 0; $min < 60; $min += 15) {
                                            $time = sprintf('%02d:%02d', $hour, $min);
                                            echo '<option value="' . $time . '">' . $time . '</option>';
                                        }
                                    }
                                    ?>
                                    <option value="23:00">23:00</option>
                                </select>
                            </div>

                            <!-- Distance Info -->
                            <div class="delivery-info-box hidden" id="distance-info-box">
                                <p><strong>🚚 Distance:</strong> <span id="distance-display">0</span> km</p>
                                <p><strong>💰 Shipping fee:</strong> <span id="shipping-fee-display">₱0</span></p>
                            </div>

                            <div class="form-group">
                                <label>Apartment, suite, unit, etc. (optional)</label>
                                <input type="text" name="apartment" id="apartment" value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_address_2', true)) : ''; ?>">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Town / City <span class="required">*</span></label>
                                    <select name="city" id="city" required>
                                        <?php $user_city = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'billing_city', true) : ''; ?>
                                        <option value="">-- Select City --</option>
                                        <option value="PASAY" <?php echo ($user_city === 'PASAY') ? 'selected' : ''; ?>>PASAY</option>
                                        <option value="PARANAQUE" <?php echo ($user_city === 'PARANAQUE') ? 'selected' : ''; ?>>PARANAQUE</option>
                                        <option value="MAKATI" <?php echo ($user_city === 'MAKATI') ? 'selected' : ''; ?>>MAKATI</option>
                                        <option value="MANILA" <?php echo ($user_city === 'MANILA') ? 'selected' : ''; ?>>MANILA</option>
                                        <option value="MANDALUYONG" <?php echo ($user_city === 'MANDALUYONG') ? 'selected' : ''; ?>>MANDALUYONG</option>
                                        <option value="TAGUIG" <?php echo ($user_city === 'TAGUIG') ? 'selected' : ''; ?>>TAGUIG</option>
                                        <option value="PASIG" <?php echo ($user_city === 'PASIG') ? 'selected' : ''; ?>>PASIG</option>
                                        <option value="SAN JUAN" <?php echo ($user_city === 'SAN JUAN') ? 'selected' : ''; ?>>SAN JUAN</option>
                                        <option value="MALABON" <?php echo ($user_city === 'MALABON') ? 'selected' : ''; ?>>MALABON</option>
                                        <option value="MARIKINA" <?php echo ($user_city === 'MARIKINA') ? 'selected' : ''; ?>>MARIKINA</option>
                                        <option value="QUEZON CITY" <?php echo ($user_city === 'QUEZON CITY') ? 'selected' : ''; ?>>QUEZON CITY</option>
                                        <option value="LAS PIÑAS" <?php echo ($user_city === 'LAS PIÑAS') ? 'selected' : ''; ?>>LAS PIÑAS</option>
                                        <option value="VALENZUELA" <?php echo ($user_city === 'VALENZUELA') ? 'selected' : ''; ?>>VALENZUELA</option>
                                        <option value="CALOOCAN" <?php echo ($user_city === 'CALOOCAN') ? 'selected' : ''; ?>>CALOOCAN</option>
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
                                <input type="text" name="postcode" id="postcode" required value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_postcode', true)) : ''; ?>">
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

                            <!-- COUPON SECTION -->
                            <div class="coupon-section">
                                <div class="coupon-input-wrapper">
                                    <input type="text" id="coupon-code-input" placeholder="Enter coupon code" maxlength="50">
                                    <button type="button" id="apply-coupon-btn" class="coupon-apply-btn">Apply</button>
                                </div>
                                <div id="coupon-message" class="coupon-message hidden"></div>
                                <div id="applied-coupons" class="applied-coupons hidden"></div>
                                
                                <!-- Available Coupons List -->
                                <div class="available-coupons">
                                    <div class="available-coupons-title">
                                        <span>🎟️ Available Coupons</span>
                                    </div>
                                    <div id="available-coupons-list" class="available-coupons-list hidden">
                                        <div class="loading-coupons">Loading coupons...</div>
                                    </div>
                                </div>
                            </div>

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
                                                // Display Variations
                                                if (!empty($cart_item['variation'])) {
                                                    echo '<div class="order-item-meta">';
                                                    foreach ($cart_item['variation'] as $key => $value) {
                                                        echo esc_html(ucfirst(str_replace('attribute_', '', $key))) . ': ' . esc_html($value) . '<br>';
                                                    }
                                                    echo '</div>';
                                                }
                                                
                                                // Display Add-ons (Custom Add-ons Plugin)
                                                if (!empty($cart_item['custom_addons'])) {
                                                    echo '<div class="order-item-addons">';
                                                    echo '<strong>Add-on:</strong><br>';
                                                    foreach ($cart_item['custom_addons'] as $addon) {
                                                        $addon_label = isset($addon['optionLabel']) ? $addon['optionLabel'] : (isset($addon['label']) ? $addon['label'] : '');
                                                        $addon_price = isset($addon['price']) ? $addon['price'] : 0;
                                                        $addon_qty = isset($addon['qty']) ? $addon['qty'] : 1;
                                                        
                                                        echo '<div class="addon-item">';
                                                        echo esc_html($addon_label);
                                                        if ($addon_qty > 1) {
                                                            echo ' × ' . esc_html($addon_qty);
                                                        }
                                                        if ($addon_price > 0) {
                                                            echo ' — ₱' . number_format($addon_price, 2);
                                                        }
                                                        echo '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                                ?>
                                            </div>
                                            <div class="order-item-price">
                                                ₱<?php echo number_format($cart_item['line_total'] + $cart_item['line_tax'], 2); ?>
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
                            <!-- GCash Account -->
                            <div class="bank-card">
                                <h4>📱 G-Cash</h4>
                                <p style="margin: 5px 0; color: #666;">Account Name: <strong>ANATALIO JR FRANCISCO</strong></p>
                                <div class="account-number">09277224868</div>
                                <div class="qr-code" style="text-align: center;">
                                    <p style="font-size: 12px; margin: 10px 0 5px 0;">Scan QR Code:</p>
                                    <img src="https://so-mot.com/wp-content/uploads/2025/12/z7330745065666_747ea90e7659f5a825197205af4829e7.jpg" alt="GCash QR Code">
                                </div>
                            </div>
                            <!-- BDO Account -->
                            <div class="bank-card">
                                <h4>🏦 BPI</h4>
                                <p style="margin: 5px 0; color: #666;">Account Name: <strong>KEYSTONE VENTURE NETWORK CORPORATION</strong></p>
                                <div class="account-number">0251000611</div>
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
        let appliedCoupons = [];
        let totalDiscount = 0;

        let availableCouponsData = [];

        // Delivery Area Validation
        const ALLOWED_CITIES = [
            'PASAY', 'PARANAQUE', 'PARAÑAQUE', 'MAKATI', 'MANILA',
            'MANDALUYONG', 'TAGUIG', 'PASIG', 'SAN JUAN', 'MALABON',
            'MARIKINA', 'QUEZON CITY', 'LAS PIÑAS', 'LAS PINAS',
            'VALENZUELA', 'CALOOCAN'
        ];

        // Function to validate delivery area
        function validateDeliveryArea(placeResult, deliveryType) {
            if (deliveryType !== 'delivery') {
                $('#delivery-area-warning').removeClass('show');
                enableProceedButton();
                return true;
            }
            
            let selectedCity = '';
            
            if (placeResult && placeResult.address_components) {
                for (let component of placeResult.address_components) {
                    if (component.types.includes('locality') || 
                        component.types.includes('administrative_area_level_2')) {
                        selectedCity = component.long_name.toUpperCase();
                        break;
                    }
                }
            }
            
            const isInMetroManila = ALLOWED_CITIES.some(city => 
                selectedCity.includes(city) || city.includes(selectedCity)
            );
            
            if (!isInMetroManila) {
                $('#delivery-area-warning').addClass('show');
                disableProceedButton();
                $('#distance-info-box').addClass('hidden');
                calculatedShippingFee = 0;
                updateOrderTotal();
                return false;
            } else {
                $('#delivery-area-warning').removeClass('show');
                enableProceedButton();
                return true;
            }
        }

        function disableProceedButton() {
            $('#proceed-to-payment-btn').prop('disabled', true).css({
                'opacity': '0.5',
                'cursor': 'not-allowed',
                'background': '#ccc'
            });
        }

        function enableProceedButton() {
            $('#proceed-to-payment-btn').prop('disabled', false).css({
                'opacity': '1',
                'cursor': 'pointer',
                'background': '#2d5016'
            });
        }

        loadAvailableCoupons();

        // Load available coupons
        function loadAvailableCoupons() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_available_coupons',
                    cart_total: parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''))
                },
                success: function(response) {
                    if (response.success && response.data.coupons) {
                        availableCouponsData = response.data.coupons;
                        renderAvailableCoupons();
                    } else {
                        $('#available-coupons-list').html('<div class="no-coupons-message">No coupons available at the moment</div>');
                    }
                },
                error: function() {
                    $('#available-coupons-list').html('<div class="no-coupons-message">Error loading coupons</div>');
                }
            });
        }

        // Apply Coupon - COMPLETE FIXED VERSION
        $('#apply-coupon-btn').click(function() {
            const couponCode = $('#coupon-code-input').val().trim().toUpperCase();
            
            if (!couponCode) {
                showCouponMessage('Please enter a coupon code', 'error');
                return;
            }
            
            // Check if coupon already applied
            if (appliedCoupons.some(c => c.code === couponCode)) {
                showCouponMessage('This coupon has already been applied', 'error');
                return;
            }
            
            $(this).prop('disabled', true).text('Checking...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'validate_and_apply_coupon',
                    coupon_code: couponCode,
                    cart_total: parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''))
                },
                success: function(response) {
                    if (response.success) {
                        // ✅ Safely parse discount amount
                        let discountAmount = 0;
                        if (response.data && response.data.discount_amount !== undefined) {
                            discountAmount = parseFloat(response.data.discount_amount);
                            if (isNaN(discountAmount)) {
                                discountAmount = 0;
                            }
                        }
                        
                        // Check individual use restriction
                        const hasIndividualUseCoupon = appliedCoupons.some(c => c.individual_use === true);
                        
                        if (hasIndividualUseCoupon) {
                            showCouponMessage('You cannot use this coupon with other coupons', 'error');
                            $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                            return;
                        }
                        
                        const isIndividualUse = response.data && response.data.individual_use === true;
                        if (isIndividualUse && appliedCoupons.length > 0) {
                            showCouponMessage('This coupon cannot be used with other coupons', 'error');
                            $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                            return;
                        }
                        
                        // Add coupon to applied list
                        appliedCoupons.push({
                            code: couponCode,
                            discount: discountAmount,
                            type: response.data.discount_type || 'percent',
                            description: response.data.description || '',
                            individual_use: isIndividualUse
                        });
                        
                        // Update UI
                        updateAppliedCouponsUI();
                        updateOrderTotalWithCoupons();
                        
                        $('#coupon-code-input').val('');
                        showCouponMessage('Coupon applied successfully! You saved ₱' + discountAmount.toFixed(2), 'success');
                    } else {
                        const errorMessage = response.data && response.data.message ? response.data.message : 'Invalid coupon code';
                        showCouponMessage(errorMessage, 'error');
                    }
                    
                    $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    showCouponMessage('Error validating coupon. Please try again.', 'error');
                    $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                }
            });
        });

        
        
        // Enter key to apply coupon
        $('#coupon-code-input').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#apply-coupon-btn').click();
            }
        });

        // Show coupon message
        function showCouponMessage(message, type) {
            const $message = $('#coupon-message');
            $message.removeClass('success error').addClass(type);
            $message.text(message).removeClass('hidden');
            
            setTimeout(function() {
                $message.addClass('hidden');
            }, 5000);
        }

        // Render available coupons
        function renderAvailableCoupons() {
            const $list = $('#available-coupons-list');
            
            if (availableCouponsData.length === 0) {
                $list.html('<div class="no-coupons-message">No coupons available at the moment</div>');
                return;
            }
            
            const cartTotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            let html = '';

            console.log('availableCouponsData', availableCouponsData);
            
            availableCouponsData.forEach(function(coupon) {
                const isApplied = appliedCoupons.some(c => c.code === coupon.code);
                const isDisabled = coupon.minimum_amount > cartTotal;
                
                let cardClass = 'coupon-card';
                if (isApplied) cardClass += ' applied';
                if (isDisabled) cardClass += ' disabled';
                
                let buttonText = 'Apply';
                if (isApplied) buttonText = 'Applied ✓';
                
                let conditionsHtml = '';
                if (isDisabled) {
                    conditionsHtml = '<div class="coupon-conditions">Minimum order: ₱' + coupon.minimum_amount.toFixed(2) + '</div>';
                }
                
                html += '<div class="' + cardClass + '" onclick="applyCouponFromList(\'' + coupon.code + '\')">' +
                    '<div class="coupon-info">' +
                        '<div class="coupon-code-display">' + coupon.code + '</div>' +
                        '<div class="coupon-description">' + coupon.description + '</div>' +
                        '<div class="coupon-details">' + coupon.details + '</div>' +
                        conditionsHtml +
                    '</div>' +
                    '<div class="coupon-discount">' + coupon.discount_text + '</div>' +
                    '<button type="button" class="coupon-apply-small-btn" ' + 
                        (isDisabled || isApplied ? 'disabled' : '') + 
                        ' onclick="event.stopPropagation(); applyCouponFromList(\'' + coupon.code + '\')">' +
                        buttonText +
                    '</button>' +
                '</div>';
            });
            
            $list.html(html);
        }

        // Apply coupon from list
        window.applyCouponFromList = function(code) {
            // Check if already applied
            if (appliedCoupons.some(c => c.code === code)) {
                showCouponMessage('This coupon is already applied', 'error');
                return;
            }
            
            // Check if disabled
            const cartTotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            const couponData = availableCouponsData.find(c => c.code === code);
            
            if (couponData && couponData.minimum_amount > cartTotal) {
                showCouponMessage('Minimum order amount of ₱' + couponData.minimum_amount.toFixed(2) + ' required', 'error');
                return;
            }
            
            // Set coupon code and apply
            $('#coupon-code-input').val(code);
            $('#apply-coupon-btn').click();
        };

        // Update renderAvailableCoupons when coupons change
        // Thêm vào function updateAppliedCouponsUI
        function updateAppliedCouponsUI() {
            const $container = $('#applied-coupons');
            
            if (appliedCoupons.length === 0) {
                $container.addClass('hidden');
                renderAvailableCoupons(); // Update available coupons list
                return;
            }
            
            let html = '<div style="margin-bottom: 10px; font-weight: 600; font-size: 14px;">Applied Coupons:</div>';
            
            appliedCoupons.forEach(function(coupon) {
                console.log('coupon', coupon);
                html += '<div class="coupon-tag">' +
                    '<span class="coupon-tag-code">' + coupon.code + '</span>' +
                    '<span class="coupon-tag-discount">-₱' + parseFloat(coupon.discount).toFixed(2) + '</span>' +
                    '<button type="button" class="coupon-remove-btn" onclick="removeCoupon(\'' + coupon.code + '\')">✕</button>' +
                    '</div>';
            });
            
            $container.html(html).removeClass('hidden');
            renderAvailableCoupons(); // Update available coupons list
        }

        // Update removeCoupon function
        window.removeCoupon = function(code) {
            appliedCoupons = appliedCoupons.filter(c => c.code !== code);
            updateAppliedCouponsUI();
            updateOrderTotalWithCoupons();
            showCouponMessage('Coupon removed', 'success');
        };

        // Update order total with coupons
        function updateOrderTotalWithCoupons() {
            const subtotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            const shippingFee = calculatedShippingFee;
            
            // Calculate total discount
            totalDiscount = appliedCoupons.reduce((sum, coupon) => sum + parseFloat(coupon.discount), 0);
            
            // Calculate final total
            const total = Math.max(0, subtotal - totalDiscount + shippingFee);
            
            // Update discount row in summary
            const $summaryContainer = $('.order-summary');
            $summaryContainer.find('.discount-row').remove();
            
            if (totalDiscount > 0) {
                const discountRowHtml = '<div class="summary-row discount-row">' +
                    '<span>Discount</span>' +
                    '<span>-₱' + totalDiscount.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span>' +
                    '</div>';
                
                $summaryContainer.find('.summary-row:last').before(discountRowHtml);
            }
            
            // Update total
            $('#total').text('₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }
        
        const branches = <?php echo json_encode($branches); ?>;

        // Function to generate time options based on branch hours
        function updateTimeOptions(startTime, endTime) {
            const $timeSelect = $('#delivery_time_select');
            const currentValue = $timeSelect.val(); // Save current selection
            
            if (!startTime || !endTime) {
                // Default: 7:00 - 23:00
                startTime = '07:00';
                endTime = '23:00';
            }
            
            // Parse start and end times
            const [startHour, startMin] = startTime.split(':').map(Number);
            const [endHour, endMin] = endTime.split(':').map(Number);
            
            // Clear current options
            $timeSelect.empty();
            
            // Generate time options
            let currentHour = startHour;
            let currentMin = startMin;
            
            while (currentHour < endHour || (currentHour === endHour && currentMin <= endMin)) {
                const time = sprintf('%02d:%02d', currentHour, currentMin);
                $timeSelect.append('<option value="' + time + '">' + time + '</option>');
                
                // Increment by 15 minutes
                currentMin += 15;
                if (currentMin >= 60) {
                    currentMin = 0;
                    currentHour++;
                }
            }
            
            // Restore previous selection if still valid
            if (currentValue && $timeSelect.find('option[value="' + currentValue + '"]').length > 0) {
                $timeSelect.val(currentValue);
            } else {
                // Select first option as default
                $timeSelect.val($timeSelect.find('option:first').val());
            }
        }

        // Helper function for sprintf
        function sprintf(format, ...args) {
            let i = 0;
            return format.replace(/%(\d*)d/g, (match, width) => {
                const num = args[i++];
                return width ? String(num).padStart(parseInt(width), '0') : String(num);
            });
        }
        
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
                        $('#delivery-area-warning').removeClass('show');
                        return;
                    }
                    
                    $('#address-error').addClass('hidden');
                    const lat = selectedPlace.geometry.location.lat();
                    const lng = selectedPlace.geometry.location.lng();
                    
                    $('#address_lat').val(lat);
                    $('#address_lng').val(lng);
                    
                    // Validate delivery area
                    const deliveryType = $('input[name="delivery_type"]:checked').val();
                    const isValidArea = validateDeliveryArea(selectedPlace, deliveryType);
                    
                    if (isValidArea) {
                        showNearestBranches(lat, lng);
                        
                        if (deliveryType === 'delivery') {
                            calculateDistance(lat, lng);
                        }
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
                            selectedPlace = results[0];
                            
                            const deliveryType = $('input[name="delivery_type"]:checked').val();
                            const isValidArea = validateDeliveryArea(results[0], deliveryType);
                            
                            if (isValidArea) {
                                showNearestBranches(lat, lng);
                                
                                if (deliveryType === 'delivery') {
                                    calculateDistance(lat, lng);
                                }
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
            const deliveryTime = $('#delivery_time_select').val();
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
            calculatedShippingFee = calculatedShippingFee || 0;
            
            $('#shipping-fee').text('₱' + calculatedShippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#shipping-fee-display').text('₱' + calculatedShippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Use the function that includes coupons
            updateOrderTotalWithCoupons();
        }
        
        // Delivery type change
        $('input[name="delivery_type"]').change(function() {
            const deliveryType = $(this).val();
            
            if (deliveryType === 'pickup') {
                $('#delivery-date-label').html('Pick up date <span class="required">*</span>');
                $('#delivery-time-label').html('Pick up time <span class="required">*</span>');
                $('#delivery-address-fields').addClass('hidden');
                $('#distance-info-box').addClass('hidden');
                $('#delivery-area-warning').removeClass('show');
                $('.delivery-required').removeAttr('required');
                calculatedShippingFee = 0;
                updateOrderTotal();
                enableProceedButton();
            } else {
                $('#delivery-date-label').html('Delivery date <span class="required">*</span>');
                $('#delivery-time-label').html('Delivery time <span class="required">*</span>');
                $('#delivery-address-fields').removeClass('hidden');
                $('.delivery-required').attr('required', 'required');
                
                if ($('#address_lat').val() && $('#address_lng').val() && selectedPlace) {
                    const isValidArea = validateDeliveryArea(selectedPlace, 'delivery');
                    
                    if (isValidArea) {
                        calculateDistance(
                            parseFloat($('#address_lat').val()),
                            parseFloat($('#address_lng').val())
                        );
                    }
                }
            }
        });
        
        // Branch selection change
        $('#branch-select').change(function() {
            const selectedOption = $(this).find('option:selected');
            branchLocation.lat = parseFloat(selectedOption.data('lat'));
            branchLocation.lng = parseFloat(selectedOption.data('lng'));
            
            // Update time options based on branch hours
            const startTime = selectedOption.data('start-time');
            const endTime = selectedOption.data('end-time');
            updateTimeOptions(startTime, endTime);
            
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

                // Validate delivery area
                if (selectedPlace) {
                    const isValidArea = validateDeliveryArea(selectedPlace, 'delivery');
                    if (!isValidArea) {
                        alert('Sorry, we only deliver to Metro Manila cities listed. Please select a valid delivery address or choose Pickup option.');
                        window.scrollTo({
                            top: $('#delivery-area-warning').offset().top - 100,
                            behavior: 'smooth'
                        });
                        return;
                    }
                } else {
                    // Fallback validation
                    const address = $('#address-autocomplete').val().toUpperCase();
                    const isInMetroManila = ALLOWED_CITIES.some(city => address.includes(city));
                    
                    if (!isInMetroManila) {
                        alert('Sorry, we only deliver to Metro Manila cities listed. Please select a valid delivery address or choose Pickup option.');
                        $('#delivery-area-warning').addClass('show');
                        window.scrollTo({
                            top: $('#delivery-area-warning').offset().top - 100,
                            behavior: 'smooth'
                        });
                        return;
                    }
                }
            }
            
            // Store order data
            orderData = {
                delivery_type: deliveryType,
                delivery_date: $('#delivery_date').val(),
                delivery_time: $('#delivery_time_select').val(),
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
            const total = subtotal - totalDiscount + calculatedShippingFee;
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

            // Add coupon data
            if (appliedCoupons.length > 0) {
                formData.append('applied_coupons', JSON.stringify(appliedCoupons));
            }
            
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

        // Initialize time options with default (first branch or no selection)
        const firstBranch = $('#branch-select option:selected');
        if (firstBranch.val()) {
            updateTimeOptions(
                firstBranch.data('start-time'),
                firstBranch.data('end-time')
            );
        } else {
            // Default time range if no branch selected
            updateTimeOptions('07:00', '23:00');
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
            $product_id = $cart_item['data']->get_id();
            $quantity = $cart_item['quantity'];
            
            // Add product to order
            $item_id = $order->add_product($cart_item['data'], $quantity);
            
            // Get the order item object
            $order_item = $order->get_item($item_id);
            
            // Calculate total addon price
            $total_addon_price = 0;
            
            // Add add-ons meta to order item if exists
            if (!empty($cart_item['custom_addons'])) {
                foreach ($cart_item['custom_addons'] as $addon) {
                    $addon_label = isset($addon['optionLabel']) ? $addon['optionLabel'] : (isset($addon['label']) ? $addon['label'] : '');
                    $addon_group = isset($addon['group']) ? $addon['group'] : 'Add-on';
                    $addon_price = isset($addon['price']) ? floatval($addon['price']) : 0;
                    $addon_qty = isset($addon['qty']) ? intval($addon['qty']) : 1;
                    
                    // Store addon as order item meta
                    if ($addon_label) {
                        wc_add_order_item_meta($item_id, $addon_group, $addon_label);
                        
                        // If addon has price, add it
                        if ($addon_price > 0) {
                            wc_add_order_item_meta($item_id, '_addon_' . sanitize_title($addon_label) . '_price', $addon_price);
                            
                            // Add to total addon price
                            $total_addon_price += ($addon_price * $addon_qty);
                        }
                        
                        // Store quantity if more than 1
                        if ($addon_qty > 1) {
                            wc_add_order_item_meta($item_id, '_addon_' . sanitize_title($addon_label) . '_qty', $addon_qty);
                        }
                    }
                }
                
                // Update line item total to include addon prices
                if ($total_addon_price > 0 && $order_item) {
                    $original_subtotal = $order_item->get_subtotal();
                    $original_total = $order_item->get_total();
                    
                    // Add addon price to subtotal and total
                    $order_item->set_subtotal($original_subtotal + $total_addon_price);
                    $order_item->set_total($original_total + $total_addon_price);
                    
                    // Save the item
                    $order_item->save();
                }
            }
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
        
        // Add shipping fee if delivery
        if ($_POST['delivery_type'] === 'delivery') {
            $shipping_fee = floatval($_POST['shipping_fee']);
            if ($shipping_fee > 0) {
                $item = new WC_Order_Item_Shipping();
                $item->set_method_title('Delivery Fee');
                $item->set_method_id('custom_delivery');
                $item->set_total($shipping_fee);
                $order->add_item($item);
            }
        }
        
        // IMPORTANT: Calculate totals BEFORE applying coupons
        $order->calculate_totals();
        
        // Apply coupons if any
        if (!empty($_POST['applied_coupons'])) {
            $coupons = json_decode(stripslashes($_POST['applied_coupons']), true);
            
            foreach ($coupons as $coupon_data) {
                $coupon_code = sanitize_text_field($coupon_data['code']);
                $coupon = new WC_Coupon($coupon_code);
                
                if ($coupon->is_valid()) {
                    // Create coupon line item
                    $coupon_item = new WC_Order_Item_Coupon();
                    $coupon_item->set_props(array(
                        'code' => $coupon_code,
                        'discount' => $coupon_data['discount'],
                        'discount_tax' => 0,
                    ));
                    
                    // Add coupon to order
                    $order->add_item($coupon_item);
                    
                    // Update coupon usage count
                    $coupon->increase_usage_count();
                    
                    // Add order note
                    $order->add_order_note(sprintf('Coupon "%s" applied. Discount: ₱%s', $coupon_code, number_format($coupon_data['discount'], 2)));
                }
            }
        }
        
        // Set custom meta
        $order->update_meta_data('_delivery_type', sanitize_text_field($_POST['delivery_type']));
        $order->update_meta_data('_delivery_date', sanitize_text_field($_POST['delivery_date']));
        $order->update_meta_data('_delivery_time', sanitize_text_field($_POST['delivery_time']));
        $order->update_meta_data('_order_notes', sanitize_textarea_field($_POST['order_notes']));
        $order->update_meta_data('_selected_branch', sanitize_text_field($_POST['branch']));
        
        if ($_POST['delivery_type'] === 'delivery') {
            $order->update_meta_data('_delivery_latitude', sanitize_text_field($_POST['address_lat']));
            $order->update_meta_data('_delivery_longitude', sanitize_text_field($_POST['address_lng']));
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
        
        // IMPORTANT: Recalculate totals after applying coupons
        $order->calculate_totals();
        $order->save();
        
        // Create customer account or associate with existing user
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        
        if (is_user_logged_in()) {
            // User is already logged in, associate order with current user
            $user_id = get_current_user_id();
            $order->set_customer_id($user_id);
            $order->save();
        } elseif (!email_exists($email) && !username_exists($email)) {
            // Create new account for guest user
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
                update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['address']));
                update_user_meta($user_id, 'billing_address_2', sanitize_text_field($_POST['apartment']));
                update_user_meta($user_id, 'billing_city', sanitize_text_field($_POST['city']));
                update_user_meta($user_id, 'billing_state', sanitize_text_field($_POST['state']));
                update_user_meta($user_id, 'billing_postcode', sanitize_text_field($_POST['postcode']));
                update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['country']));
                
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
        } else {
            // Email already exists, associate order with existing user
            $existing_user = get_user_by('email', $email);
            if ($existing_user) {
                $order->set_customer_id($existing_user->ID);
                $order->save();
            }
        }
        
        // Empty cart
        WC()->cart->empty_cart();
        
        wp_send_json_success(array(
            'redirect' => home_url('/thank-you-page/?order_id=' . $order->get_id() . '&key=' . $order->get_order_key())
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

// AJAX: Validate and Apply Coupon - COMPLETE WITH GLOBAL & INDIVIDUAL MIN/MAX
add_action('wp_ajax_validate_and_apply_coupon', 'validate_and_apply_coupon');
add_action('wp_ajax_nopriv_validate_and_apply_coupon', 'validate_and_apply_coupon');
function validate_and_apply_coupon() {
    $coupon_code = strtoupper(sanitize_text_field($_POST['coupon_code']));
    $cart_total = floatval($_POST['cart_total']);
    
    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => 'Please enter a coupon code'));
    }
    
    // Get coupon object
    $coupon = new WC_Coupon($coupon_code);
    
    // Check if coupon exists
    if (!$coupon->get_id()) {
        wp_send_json_error(array('message' => 'Invalid coupon code'));
    }
    
    // Check if coupon is valid (general validation)
    if (!$coupon->is_valid()) {
        wp_send_json_error(array('message' => 'This coupon is not valid'));
    }
    
    // ========================================
    // 1. CHECK EXPIRY DATE
    // ========================================
    $expiry_date = $coupon->get_date_expires();
    if ($expiry_date && $expiry_date->getTimestamp() < time()) {
        wp_send_json_error(array('message' => 'This coupon has expired'));
    }
    
    // ========================================
    // 2. CHECK USAGE LIMIT (TOTAL)
    // ========================================
    $usage_limit = $coupon->get_usage_limit();
    $usage_count = $coupon->get_usage_count();
    
    if ($usage_limit > 0 && $usage_count >= $usage_limit) {
        wp_send_json_error(array('message' => 'This coupon has reached its usage limit'));
    }
    
    // ========================================
    // 3. CHECK USAGE LIMIT PER USER
    // ========================================
    $usage_limit_per_user = $coupon->get_usage_limit_per_user();
    
    if ($usage_limit_per_user > 0) {
        $user_id = get_current_user_id();
        $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
        
        $used_by = $coupon->get_used_by();
        $user_usage_count = 0;
        
        if ($user_id > 0) {
            $user_usage_count = count(array_filter($used_by, function($customer_id) use ($user_id) {
                return intval($customer_id) === $user_id;
            }));
        }
        
        if (!empty($user_email)) {
            global $wpdb;
            $email_usage = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta pm
                LEFT JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_billing_email'
                AND pm.meta_value = %s
                AND p.ID IN (
                    SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items
                    WHERE order_item_name = %s
                    AND order_item_type = 'coupon'
                )",
                $user_email,
                $coupon_code
            ));
            
            $user_usage_count = max($user_usage_count, intval($email_usage));
        }
        
        if ($user_usage_count >= $usage_limit_per_user) {
            wp_send_json_error(array('message' => 'You have reached the usage limit for this coupon'));
        }
    }
    
    // ========================================
    // 4. CHECK EMAIL RESTRICTIONS
    // ========================================
    $email_restrictions = $coupon->get_email_restrictions();
    if (!empty($email_restrictions)) {
        $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
        
        if (empty($user_email)) {
            wp_send_json_error(array('message' => 'Please login to use this coupon'));
        }
        
        $is_email_valid = false;
        foreach ($email_restrictions as $restriction) {
            if (fnmatch($restriction, $user_email, FNM_CASEFOLD)) {
                $is_email_valid = true;
                break;
            }
        }
        
        if (!$is_email_valid) {
            wp_send_json_error(array('message' => 'This coupon is not valid for your email address'));
        }
    }
    
    // ========================================
    // 5. GET COUPON RESTRICTIONS
    // ========================================
    $product_ids = $coupon->get_product_ids();
    $excluded_product_ids = $coupon->get_excluded_product_ids();
    $product_categories = $coupon->get_product_categories();
    $excluded_product_categories = $coupon->get_excluded_product_categories();
    $exclude_sale_items = $coupon->get_exclude_sale_items();
    
    // ========================================
    // 6. SMART COUPONS PRO - GET QUANTITY SETTINGS
    // ========================================
    $wt_sc_coupon_categories = get_post_meta($coupon->get_id(), '_wt_sc_coupon_categories', true);
    $wt_enable_category_restriction = get_post_meta($coupon->get_id(), '_wt_enable_product_category_restriction', true);
    $wt_use_individual_min_max = get_post_meta($coupon->get_id(), '_wt_use_individual_min_max', true);
    $wt_min_matching_product_qty = get_post_meta($coupon->get_id(), '_wt_min_matching_product_qty', true);
    $wt_max_matching_product_qty = get_post_meta($coupon->get_id(), '_wt_max_matching_product_qty', true);
    $wt_category_condition = get_post_meta($coupon->get_id(), '_wt_category_condition', true);
    
    // ========================================
    // 7. FILTER ELIGIBLE CART ITEMS & COUNT QUANTITIES
    // ========================================
    $eligible_items = array();
    $eligible_total = 0;
    $eligible_quantity = 0;
    $category_quantities = array();
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        $item_total = $cart_item['line_total'];
        $quantity = $cart_item['quantity'];
        
        $is_eligible = true;
        
        // Check if product is excluded
        if (!empty($excluded_product_ids)) {
            if (in_array($product_id, $excluded_product_ids) || 
                ($variation_id && in_array($variation_id, $excluded_product_ids))) {
                $is_eligible = false;
            }
        }
        
        // Check if product is in required products list
        if ($is_eligible && !empty($product_ids)) {
            if (!in_array($product_id, $product_ids) && 
                !($variation_id && in_array($variation_id, $product_ids))) {
                $is_eligible = false;
            }
        }
        
        // Check product categories
        if ($is_eligible) {
            $product_cats = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
            
            // Check excluded categories
            if (!empty($excluded_product_categories)) {
                foreach ($excluded_product_categories as $excluded_cat) {
                    if (in_array($excluded_cat, $product_cats)) {
                        $is_eligible = false;
                        break;
                    }
                }
            }
            
            // Check required categories
            if ($is_eligible && !empty($product_categories)) {
                $has_required_category = false;
                foreach ($product_categories as $required_cat) {
                    if (in_array($required_cat, $product_cats)) {
                        $has_required_category = true;
                        
                        // Count quantity per category
                        if (!isset($category_quantities[$required_cat])) {
                            $category_quantities[$required_cat] = 0;
                        }
                        $category_quantities[$required_cat] += $quantity;
                        
                        break;
                    }
                }
                if (!$has_required_category) {
                    $is_eligible = false;
                }
            }
        }
        
        // Check if product is on sale (if coupon excludes sale items)
        if ($is_eligible && $exclude_sale_items && $product->is_on_sale()) {
            $is_eligible = false;
        }
        
        // Add to eligible items
        if ($is_eligible) {
            $eligible_items[] = array(
                'cart_item_key' => $cart_item_key,
                'product_id' => $product_id,
                'product_name' => $product->get_name(),
                'quantity' => $quantity,
                'item_total' => $item_total
            );
            $eligible_total += $item_total;
            $eligible_quantity += $quantity;
        }
    }
    
    // ========================================
    // 8. CHECK IF ANY ELIGIBLE ITEMS EXIST
    // ========================================
    if (empty($eligible_items)) {
        // Build helpful error message
        $error_message = 'This coupon is not valid for any products in your cart.';
        
        if (!empty($product_categories)) {
            $cat_names = array();
            foreach ($product_categories as $cat_id) {
                $category = get_term($cat_id, 'product_cat');
                if ($category) {
                    $cat_names[] = $category->name;
                }
            }
            if (!empty($cat_names)) {
                $error_message = 'This coupon only applies to: ' . implode(', ', $cat_names);
            }
        }
        
        wp_send_json_error(array('message' => $error_message));
    }
    
    // ========================================
    // 9. CHECK QUANTITY RESTRICTIONS
    // ========================================
    
    // 9A. INDIVIDUAL MIN/MAX PER CATEGORY (như 25OFFPHO)
    if ($wt_enable_category_restriction === 'yes' && 
        $wt_use_individual_min_max === 'yes' && 
        !empty($wt_sc_coupon_categories) && 
        is_array($wt_sc_coupon_categories)) {
        
        // Validate minimum và maximum quantity cho từng category
        foreach ($wt_sc_coupon_categories as $cat_id => $qty_rules) {
            $min_qty = !empty($qty_rules['min']) ? intval($qty_rules['min']) : 0;
            $max_qty = !empty($qty_rules['max']) ? intval($qty_rules['max']) : 0;
            $current_qty = isset($category_quantities[$cat_id]) ? $category_quantities[$cat_id] : 0;
            
            // Get category name for error message
            $category = get_term($cat_id, 'product_cat');
            $category_name = $category ? $category->name : 'required category';
            
            // Check minimum quantity
            if ($min_qty > 0 && $current_qty < $min_qty) {
                wp_send_json_error(array(
                    'message' => sprintf(
                        'You need at least %d item(s) from "%s" to use this coupon. Currently: %d item(s)',
                        $min_qty,
                        $category_name,
                        $current_qty
                    )
                ));
            }
            
            // Check maximum quantity
            if ($max_qty > 0 && $current_qty > $max_qty) {
                wp_send_json_error(array(
                    'message' => sprintf(
                        'Maximum %d item(s) from "%s" allowed for this coupon. Currently: %d item(s)',
                        $max_qty,
                        $category_name,
                        $current_qty
                    )
                ));
            }
        }
    }
    // 9B. GLOBAL MIN/MAX (như LUNCH100)
    else if ($wt_enable_category_restriction === 'yes' && 
             $wt_use_individual_min_max === 'no') {
        
        $global_min_qty = !empty($wt_min_matching_product_qty) ? intval($wt_min_matching_product_qty) : 0;
        $global_max_qty = !empty($wt_max_matching_product_qty) ? intval($wt_max_matching_product_qty) : 0;
        
        // Check minimum quantity (tổng số sản phẩm eligible)
        if ($global_min_qty > 0 && $eligible_quantity < $global_min_qty) {
            wp_send_json_error(array(
                'message' => sprintf(
                    'You need at least %d item(s) from eligible categories to use this coupon. Currently: %d item(s)',
                    $global_min_qty,
                    $eligible_quantity
                )
            ));
        }
        
        // Check maximum quantity
        if ($global_max_qty > 0 && $eligible_quantity > $global_max_qty) {
            wp_send_json_error(array(
                'message' => sprintf(
                    'Maximum %d item(s) from eligible categories allowed for this coupon. Currently: %d item(s)',
                    $global_max_qty,
                    $eligible_quantity
                )
            ));
        }
    }
    
    // ========================================
    // 10. CHECK MINIMUM AMOUNT (on eligible items only)
    // ========================================
    $minimum_amount = $coupon->get_minimum_amount();
    if ($minimum_amount > 0 && $eligible_total < $minimum_amount) {
        wp_send_json_error(array(
            'message' => sprintf(
                'Minimum order amount of ₱%s required for eligible products (Current: ₱%s)',
                number_format($minimum_amount, 2),
                number_format($eligible_total, 2)
            )
        ));
    }
    
    // ========================================
    // 11. CHECK MAXIMUM AMOUNT
    // ========================================
    $maximum_amount = $coupon->get_maximum_amount();
    if ($maximum_amount > 0 && $eligible_total > $maximum_amount) {
        wp_send_json_error(array(
            'message' => 'Maximum order amount of ₱' . number_format($maximum_amount, 2) . ' exceeded for this coupon'
        ));
    }
    
    // ========================================
    // 12. CALCULATE DISCOUNT (only on eligible items)
    // ========================================
    $discount_type = $coupon->get_discount_type();
    $coupon_amount = $coupon->get_amount();
    $discount_amount = 0;
    
    if ($discount_type === 'fixed_cart') {
        // Fixed cart discount applies to entire eligible total
        $discount_amount = min($coupon_amount, $eligible_total);
    } elseif ($discount_type === 'fixed_product') {
        // Fixed product discount applies per eligible item
        foreach ($eligible_items as $item) {
            $item_discount = min($coupon_amount * $item['quantity'], $item['item_total']);
            $discount_amount += $item_discount;
        }
    } elseif ($discount_type === 'percent') {
        // Percentage discount on eligible items
        $discount_amount = ($eligible_total * $coupon_amount) / 100;
        
        // Apply maximum discount amount if set
        if ($maximum_amount > 0 && $discount_amount > $maximum_amount) {
            $discount_amount = $maximum_amount;
        }
    }
    
    // Ensure discount doesn't exceed eligible total
    $discount_amount = min($discount_amount, $eligible_total);
    
    // Format description
    $description = '';
    if ($discount_type === 'percent') {
        $description = $coupon_amount . '% off';
    } else if ($discount_type === 'fixed_product') {
        $description = '₱' . number_format($coupon_amount, 2) . ' off per item';
    } else {
        $description = '₱' . number_format($coupon_amount, 2) . ' off';
    }
    
    // Add info about eligible items
    $eligible_count = count($eligible_items);
    $total_items = count(WC()->cart->get_cart());
    
    if ($eligible_count < $total_items) {
        $description .= ' (' . $eligible_quantity . ' eligible items)';
    }
    
    // Build detailed eligible products list
    $eligible_products_info = array();
    foreach ($eligible_items as $item) {
        $eligible_products_info[] = array(
            'name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'subtotal' => floatval($item['item_total'])
        );
    }
    
    wp_send_json_success(array(
        'discount_amount' => floatval($discount_amount),
        'discount_type' => $discount_type,
        'description' => $description,
        'code' => $coupon_code,
        'individual_use' => $coupon->get_individual_use(),
        'eligible_items' => intval($eligible_count),
        'eligible_quantity' => intval($eligible_quantity),
        'total_items' => intval($total_items),
        'eligible_total' => floatval($eligible_total),
        'eligible_products' => $eligible_products_info
    ));
}

// AJAX: Get Available Coupons
add_action('wp_ajax_get_available_coupons', 'get_available_coupons_ajax');
add_action('wp_ajax_nopriv_get_available_coupons', 'get_available_coupons_ajax');
function get_available_coupons_ajax() {
    $cart_total = isset($_POST['cart_total']) ? floatval($_POST['cart_total']) : 0;
    
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $coupons_query = new WP_Query($args);
    $available_coupons = array();
    
    if ($coupons_query->have_posts()) {
        while ($coupons_query->have_posts()) {
            $coupons_query->the_post();
            $coupon_id = get_the_ID();
            $coupon = new WC_Coupon($coupon_id);
            
            // Check if coupon is valid
            if (!$coupon->is_valid()) {
                continue;
            }
            
            //Check usage limit
            if ($coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit()) {
                continue;
            }
            
            // Check expiry date
            $expiry_date = $coupon->get_date_expires();
            if ($expiry_date && $expiry_date->getTimestamp() < time()) {
                continue;
            }
            
            // Get coupon details
            $code = $coupon->get_code();
            $discount_type = $coupon->get_discount_type();
            $amount = $coupon->get_amount();
            $minimum_amount = $coupon->get_minimum_amount();
            $maximum_amount = $coupon->get_maximum_amount();
            $description = $coupon->get_description();
            
            // Format discount text
            $discount_text = '';
            if ($discount_type === 'percent') {
                $discount_text = $amount . '% OFF';
            } else if ($discount_type === 'fixed_cart' || $discount_type === 'fixed_product') {
                $discount_text = '₱' . number_format($amount, 0) . ' OFF';
            }
            
            // Build details text
            $details_parts = array();
            
            if ($minimum_amount > 0) {
                $details_parts[] = 'Min: ₱' . number_format($minimum_amount, 0);
            }
            
            if ($maximum_amount > 0) {
                $details_parts[] = 'Max discount: ₱' . number_format($maximum_amount, 0);
            }
            
            if ($expiry_date) {
                $details_parts[] = 'Expires: ' . $expiry_date->format('M d, Y');
            }
            
            $usage_limit = $coupon->get_usage_limit();
            if ($usage_limit > 0) {
                $remaining = $usage_limit - $coupon->get_usage_count();
                $details_parts[] = $remaining . ' uses left';
            }
            
            $details = !empty($details_parts) ? implode(' • ', $details_parts) : 'No restrictions';
            
            // Use description or create a default one
            if (empty($description)) {
                if ($discount_type === 'percent') {
                    $description = 'Get ' . $amount . '% discount on your order';
                } else {
                    $description = 'Get ₱' . number_format($amount, 2) . ' off your order';
                }
            }
            
            $available_coupons[] = array(
                'code' => $code,
                'discount_type' => $discount_type,
                'amount' => $amount,
                'discount_text' => $discount_text,
                'description' => $description,
                'details' => $details,
                'minimum_amount' => $minimum_amount,
                'maximum_amount' => $maximum_amount,
                'expiry_date' => $expiry_date ? $expiry_date->format('Y-m-d') : null
            );
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success(array(
        'coupons' => $available_coupons
    ));
}

// Display coupon information in admin order details
add_action('woocommerce_admin_order_data_after_order_details', 'display_coupon_info_in_admin');
function display_coupon_info_in_admin($order) {
    // Get applied coupons
    $coupons = $order->get_coupon_codes();
    
    if (!empty($coupons)) {
        echo '<div class="coupon-info-admin" style="padding: 15px; background: #e8f5e9; margin-top: 15px; border-radius: 4px; border-left: 4px solid #4caf50;">';
        echo '<h3 style="margin-top: 0;">🎟️ Applied Coupons</h3>';
        
        foreach ($order->get_items('coupon') as $item_id => $item) {
            $coupon_code = $item->get_code();
            $discount_amount = $item->get_discount();
            
            echo '<div style="padding: 8px 12px; background: white; border-radius: 4px; margin-bottom: 8px;">';
            echo '<strong style="color: #2d5016; font-size: 14px; text-transform: uppercase;">' . esc_html($coupon_code) . '</strong>';
            echo '<span style="float: right; color: #10b981; font-weight: 600;">-₱' . number_format($discount_amount, 2) . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}

// Add order summary box in admin
add_action('woocommerce_admin_order_totals_after_total', 'display_order_summary_breakdown');
function display_order_summary_breakdown($order_id) {
    $order = wc_get_order($order_id);
    
    // Get shipping total
    $shipping_total = $order->get_shipping_total();
    
    // Get discount total
    $discount_total = $order->get_discount_total();
    
    if ($shipping_total > 0 || $discount_total > 0) {
        ?>
        <tr>
            <td colspan="2" style="padding-top: 15px; border-top: 2px solid #ddd;">
                <div style="background: #f7f7f7; padding: 12px; border-radius: 4px;">
                    <?php if ($discount_total > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <strong>💰 Total Discount:</strong>
                        <span style="color: #10b981; font-weight: 600;">-₱<?php echo number_format($discount_total, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($shipping_total > 0): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <strong>🚚 Shipping Fee:</strong>
                        <span style="font-weight: 600;">₱<?php echo number_format($shipping_total, 2); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
    }
}
