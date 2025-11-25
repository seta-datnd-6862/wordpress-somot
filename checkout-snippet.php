<?php

// ========================================
// CUSTOM CHECKOUT PAGE WITH COUPON
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
        array('id' => 'tayuman', 'name' => 'Tayuman Branch', 'lat' => 14.6175959, 'lng' => 120.9837713, 'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila, Philippines'),
        array('id' => 'pioneer', 'name' => 'Pioneer Branch', 'lat' => 14.5731404, 'lng' => 121.0164509, 'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines'),
        array('id' => 'unimart', 'name' => 'Unimart Branch', 'lat' => 14.574848, 'lng' => 121.0618259, 'address' => 'Ground Floor, Unimart at Capitol Commons, Shaw Blvd, Pasig, Metro Manila, Philippines'),
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
        
        /* Coupon Styles */
        .coupon-section {
            background: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px dashed #3b82f6;
        }
        .coupon-input-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .coupon-input-group input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .coupon-input-group button {
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
        .coupon-input-group button:hover {
            background: #1f3810;
        }
        .coupon-input-group button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .applied-coupon {
            background: #10b981;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .applied-coupon .remove-coupon {
            background: transparent;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            padding: 0 5px;
        }
        .coupon-message {
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 14px;
        }
        .coupon-message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .coupon-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }
        .summary-row.discount {
            color: #10b981;
        }
    </style>

    <div class="custom-checkout-wrapper">
        <div class="checkout-header">
            <h1>CHECKOUT</h1>
        </div>

        <form id="custom-checkout-form" method="post">
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
                            <label>Delivery date <span class="required">*</span></label>
                            <input type="date" name="delivery_date" id="delivery_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Delivery time <span class="required">*</span></label>
                            <input type="time" name="delivery_time" id="delivery_time" required min="07:00" max="23:00">
                        </div>
                    </div>

                    <!-- Branch Selection -->
                    <div class="checkout-section" id="branch-section" style="margin-top: 20px;">
                        <div class="section-title">🏢 Select Branch</div>
                        <div class="form-group">
                            <label>Choose branch <span class="required">*</span></label>
                            <select name="branch" id="branch-select">
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

                        <!-- Delivery Address Fields (Hidden by default) -->
                        <div id="delivery-address-fields" class="hidden">
                            <div class="form-group">
                                <label>Country / Region <span class="required">*</span></label>
                                <select name="country" id="country" class="delivery-required">
                                    <option value="Philippines" selected>Philippines</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Street address <span class="required">*</span></label>
                                <input type="text" name="address" id="address-autocomplete" class="delivery-required" placeholder="Start typing address...">
                                <input type="hidden" name="address_lat" id="address_lat">
                                <input type="hidden" name="address_lng" id="address_lng">
                                <div id="address-error" class="error-message hidden">Please select an address from suggestions</div>
                            </div>

                            <div class="form-group">
                                <label>Apartment, suite, unit, etc. (optional)</label>
                                <input type="text" name="apartment" id="apartment">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label>Town / City <span class="required">*</span></label>
                                    <select name="city" id="city" class="delivery-required">
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
                                    <select name="state" id="state" class="delivery-required">
                                        <option value="Metro Manila">Metro Manila</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Postcode / ZIP <span class="required">*</span></label>
                                <input type="text" name="postcode" id="postcode" class="delivery-required">
                            </div>

                            <!-- Distance Info -->
                            <div class="delivery-info-box hidden" id="distance-info-box">
                                <p><strong>🚚 Distance:</strong> <span id="distance-display">0</span> km</p>
                                <p><strong>💰 Shipping fee:</strong> <span id="shipping-fee-display">0</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Options -->
                    <div class="checkout-section" style="margin-top: 20px;">
                        <div class="section-title">💳 Payment options</div>
                        
                        <div class="form-group">
                            <label>
                                <input type="radio" name="payment_method" value="bank_transfer" checked>
                                Direct bank transfer
                            </label>
                            <div class="bank-transfer-info" style="color: #666; font-size: 14px; margin-left: 25px; margin-top: 5px;">
                                <p>Online Payment (GCash, BDO, BPI):</p>
                                <p>Pay ahead via bank transfer or GCash</p>
                                <p>Shipping fee is usually lower and fixed</p>
                                <p>Faster and smoother delivery since no cash needed with rider</p>
                                <p>Confirm payment by sending transfer slip po</p>
                                <p>If you want faster delivery and less hassle, online payment is best po. But if you prefer paying cash, COD is available too.</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>
                                <input type="radio" name="payment_method" value="cod">
                                Cash on delivery
                            </label>
                            <div class="cod-info hidden" style="color: #666; font-size: 14px; margin-left: 25px; margin-top: 5px;">
                                <p>𝗖𝗢𝗗 (Cash on Delivery):</p>
                                <p>Pay the rider in cash upon delivery</p>
                                <p>Shipping fee may be higher (+₱50 or more)</p>
                                <p>Finding riders with cash can take longer, especially for orders ₱2,000+</p>
                                <p>Slightly slower delivery sometimes po</p>
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
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                <span style="font-size: 20px;">🎟️</span>
                                <strong style="font-size: 16px;">Have a coupon code?</strong>
                            </div>
                            <p style="color: #666; font-size: 13px; margin: 5px 0 10px 0;">Enter your code below to get discount</p>
                            
                            <div class="coupon-input-group">
                                <input type="text" id="coupon-code" placeholder="Enter coupon code" />
                                <button type="button" id="apply-coupon-btn">Apply</button>
                            </div>
                            
                            <div id="coupon-message"></div>
                            <div id="applied-coupon-display"></div>
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
                            <div class="summary-row discount hidden" id="discount-row">
                                <span>Discount (<span id="coupon-code-display"></span>)</span>
                                <span id="discount-amount">-₱0.00</span>
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

                        <input type="hidden" name="applied_coupon" id="applied-coupon-input" value="">

                        <button type="submit" class="place-order-btn" id="place-order-btn">
                            Place Order
                        </button>

                        <p style="text-align: center; margin-top: 15px; font-size: 12px; color: #666;">
                            By proceeding with your purchase you agree to our Terms and Conditions and Privacy Policy
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXShFxiu-eawxmLBhT8NamWJK7giYd6Dc&libraries=places"></script>

    <script>
    jQuery(document).ready(function($) {
        let destinationAutocomplete;
        let selectedPlace = null;
        let branchLocation = {lat: 14.6175959, lng: 120.9837713};
        let calculatedShippingFee = 0;
        let appliedCouponCode = '';
        let discountAmount = 0;
        
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
                    
                    if ($('input[name="delivery_type"]:checked').val() === 'delivery') {
                        calculateDistance(lat, lng);
                    }
                });
            }
        }
        
        // Calculate distance
        function calculateDistance(destLat, destLng) {
            const R = 6371;
            const dLat = (destLat - branchLocation.lat) * Math.PI / 180;
            const dLon = (destLng - branchLocation.lng) * Math.PI / 180;
            
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                     Math.cos(branchLocation.lat * Math.PI / 180) * Math.cos(destLat * Math.PI / 180) *
                     Math.sin(dLon/2) * Math.sin(dLon/2);
            
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            $('#distance-display').text(distance.toFixed(2));
            $('#distance-info-box').removeClass('hidden');
            
            getShippingFee(distance);
        }
        
        // Get shipping fee
        function getShippingFee(distance) {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            const cashOnDelivery = (paymentMethod === 'cod') ? 1 : 0;
            const deliveryTime = $('#delivery_time').val();
            const deliveryDate = $('#delivery_date').val();
            const currentHour = new Date(deliveryDate + 'T' + deliveryTime).getHours();
            const nightShift = (currentHour >= 22 || currentHour <= 6) ? 1 : 0;
            
            $.ajax({
                url: 'https://goodriver.online/api/setting/get-delivery-fee',
                type: 'POST',
                data: {
                    distance: distance.toFixed(2),
                    cash_on_delivery: cashOnDelivery,
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
        
        // Apply Coupon
        $('#apply-coupon-btn').click(function() {
            const couponCode = $('#coupon-code').val().trim();
            
            if (!couponCode) {
                showCouponMessage('Please enter a coupon code', 'error');
                return;
            }
            
            $(this).prop('disabled', true).text('Applying...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'apply_coupon_custom',
                    coupon_code: couponCode
                },
                success: function(response) {
                    if (response.success) {
                        appliedCouponCode = couponCode;
                        discountAmount = response.data.discount;
                        
                        $('#applied-coupon-input').val(couponCode);
                        $('#coupon-code').val('').prop('disabled', true);
                        
                        showAppliedCoupon(couponCode);
                        showCouponMessage(response.data.message, 'success');
                        updateOrderTotal();
                    } else {
                        showCouponMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    showCouponMessage('Error applying coupon. Please try again.', 'error');
                },
                complete: function() {
                    $('#apply-coupon-btn').prop('disabled', false).text('Apply');
                }
            });
        });
        
        // Remove Coupon
        $(document).on('click', '.remove-coupon', function() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'remove_coupon_custom',
                    coupon_code: appliedCouponCode
                },
                success: function(response) {
                    appliedCouponCode = '';
                    discountAmount = 0;
                    
                    $('#applied-coupon-input').val('');
                    $('#coupon-code').prop('disabled', false);
                    $('#applied-coupon-display').empty();
                    $('#coupon-message').empty();
                    
                    updateOrderTotal();
                }
            });
        });
        
        // Show Coupon Message
        function showCouponMessage(message, type) {
            $('#coupon-message').html('<div class="coupon-message ' + type + '">' + message + '</div>');
            
            setTimeout(function() {
                $('#coupon-message').fadeOut(function() {
                    $(this).empty().show();
                });
            }, 5000);
        }
        
        // Show Applied Coupon
        function showAppliedCoupon(code) {
            const html = '<div class="applied-coupon">' +
                '<span>✓ Coupon applied: <strong>' + code + '</strong></span>' +
                '<button type="button" class="remove-coupon">✕</button>' +
                '</div>';
            $('#applied-coupon-display').html(html);
        }
        
        // Update order total
        function updateOrderTotal() {
            const subtotal = parseFloat($('#subtotal').text().replace('₱', '').replace(',', ''));
            const shippingFee = calculatedShippingFee;
            const total = subtotal - discountAmount + shippingFee;
            
            $('#shipping-fee').text('₱' + shippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#shipping-fee-display').text('₱' + shippingFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            if (discountAmount > 0) {
                $('#discount-row').removeClass('hidden');
                $('#coupon-code-display').text(appliedCouponCode);
                $('#discount-amount').text('-₱' + discountAmount.toLocaleString('en-US', {minimumFractionDigits: 2}));
            } else {
                $('#discount-row').addClass('hidden');
            }
            
            $('#total').text('₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }
        
        // Delivery type change
        $('input[name="delivery_type"]').change(function() {
            if ($(this).val() === 'delivery') {
                $('#branch-section').removeClass('hidden');
                $('#delivery-address-fields').removeClass('hidden');
                
                // Make delivery fields required
                $('.delivery-required').attr('required', 'required');
                
                calculatedShippingFee = 0;
                
                if ($('#address_lat').val() && $('#address_lng').val()) {
                    calculateDistance(
                        parseFloat($('#address_lat').val()),
                        parseFloat($('#address_lng').val())
                    );
                }
            } else {
                $('#branch-section').addClass('hidden');
                $('#delivery-address-fields').addClass('hidden');
                $('#distance-info-box').addClass('hidden');
                
                // Remove required from delivery fields
                $('.delivery-required').removeAttr('required');
                
                calculatedShippingFee = 0;
                updateOrderTotal();
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
        
        // Payment method change
        $('input[name="payment_method"]').change(function() {
            if ($('input[name="delivery_type"]:checked').val() === 'delivery' && 
                $('#address_lat').val() && $('#address_lng').val()) {
                calculateDistance(
                    parseFloat($('#address_lat').val()),
                    parseFloat($('#address_lng').val())
                );
            }
        });
        
        // Form submission
        $('#custom-checkout-form').submit(function(e) {
            e.preventDefault();
            
            const deliveryType = $('input[name="delivery_type"]:checked').val();
            
            if (deliveryType === 'delivery') {
                if (!$('#branch-select').val()) {
                    alert('Please select a branch');
                    return false;
                }
                if (!$('#address_lat').val() || !$('#address_lng').val()) {
                    alert('Please select a valid address from suggestions');
                    $('#address-error').removeClass('hidden');
                    return false;
                }
            }
            
            $('#place-order-btn').prop('disabled', true).text('Processing...');
            
            let formData = $(this).serialize();
            formData += '&action=process_custom_checkout';
            formData += '&shipping_fee=' + calculatedShippingFee;
            formData += '&discount_amount=' + discountAmount;
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert(response.data.message || 'Error processing order');
                        $('#place-order-btn').prop('disabled', false).text('Place Order');
                    }
                },
                error: function() {
                    alert('Error processing order. Please try again.');
                    $('#place-order-btn').prop('disabled', false).text('Place Order');
                }
            });
        });
        
        // Initialize autocomplete
        if (typeof google !== 'undefined') {
            initAutocomplete();
        } else {
            setTimeout(initAutocomplete, 1000);
        }

        $(document).on('click', 'input[name="payment_method"]', function() {
            if ($(this).val() === 'cod') {
                $('.cod-info').removeClass('hidden');
                $('.bank-transfer-info').addClass('hidden');
            } else if ($(this).val() === 'bank_transfer') {
                $('.cod-info').addClass('hidden');
                $('.bank-transfer-info').removeClass('hidden');
            }

            if ($('input[name="delivery_type"]:checked').val() === 'delivery' && 
                $('#address_lat').val() && $('#address_lng').val()) {
                calculateDistance(
                    parseFloat($('#address_lat').val()),
                    parseFloat($('#address_lng').val())
                );
            }
        });

        $(document).on('change', '#need_vat', function() {
            if ($(this).is(':checked')) {
                $('#vat-fields').removeClass('hidden');
                $('.vat-required').attr('required', 'required');
            } else {
                $('#vat-fields').addClass('hidden');
                $('.vat-required').removeAttr('required');
            }
        });
    });
    </script>
    <?php
}

// AJAX handler for applying coupon
add_action('wp_ajax_apply_coupon_custom', 'apply_coupon_custom');
add_action('wp_ajax_nopriv_apply_coupon_custom', 'apply_coupon_custom');
function apply_coupon_custom() {
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    
    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => 'Please enter a coupon code'));
    }
    
    $coupon = new WC_Coupon($coupon_code);
    
    if (!$coupon->is_valid()) {
        wp_send_json_error(array('message' => 'Invalid coupon code'));
    }
    
    // Apply coupon to cart
    WC()->cart->add_discount($coupon_code);
    
    // Calculate discount
    $discount = WC()->cart->get_discount_total();
    
    wp_send_json_success(array(
        'message' => 'Coupon applied successfully!',
        'discount' => $discount
    ));
}

// AJAX handler for removing coupon
add_action('wp_ajax_remove_coupon_custom', 'remove_coupon_custom');
add_action('wp_ajax_nopriv_remove_coupon_custom', 'remove_coupon_custom');
function remove_coupon_custom() {
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    
    if (!empty($coupon_code)) {
        WC()->cart->remove_coupon($coupon_code);
    }
    
    wp_send_json_success();
}

// 3. AJAX handler for processing custom checkout
add_action('wp_ajax_process_custom_checkout', 'process_custom_checkout');
add_action('wp_ajax_nopriv_process_custom_checkout', 'process_custom_checkout');
function process_custom_checkout() {
    try {
        $order = wc_create_order();
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $order->add_product($cart_item['data'], $cart_item['quantity']);
        }
        
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
        
        $order->set_shipping_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_shipping_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_shipping_address_1(sanitize_text_field($_POST['address']));
        $order->set_shipping_address_2(sanitize_text_field($_POST['apartment']));
        $order->set_shipping_city(sanitize_text_field($_POST['city']));
        $order->set_shipping_state(sanitize_text_field($_POST['state']));
        $order->set_shipping_postcode(sanitize_text_field($_POST['postcode']));
        $order->set_shipping_country(sanitize_text_field($_POST['country']));
        
        $order->update_meta_data('_delivery_type', sanitize_text_field($_POST['delivery_type']));
        $order->update_meta_data('_delivery_date', sanitize_text_field($_POST['delivery_date']));
        $order->update_meta_data('_delivery_time', sanitize_text_field($_POST['delivery_time']));
        $order->update_meta_data('_order_notes', sanitize_textarea_field($_POST['order_notes']));
        
        if ($_POST['delivery_type'] === 'delivery') {
            $order->update_meta_data('_selected_branch', sanitize_text_field($_POST['branch']));
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
        
        // Apply coupon if exists
        if (!empty($_POST['applied_coupon'])) {
            $coupon_code = sanitize_text_field($_POST['applied_coupon']);
            $discount_amount = floatval($_POST['discount_amount']);
            
            $order->apply_coupon($coupon_code);
            $order->update_meta_data('_coupon_code', $coupon_code);
            $order->update_meta_data('_discount_amount', $discount_amount);
        }
        
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $order->set_payment_method($payment_method);
        $order->set_payment_method_title($payment_method === 'cod' ? 'Cash on Delivery' : 'Direct Bank Transfer');
        
        $order->calculate_totals();
        $order->save();
        
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

// 4. Display custom checkout info in admin
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_checkout_info_in_admin');
function display_custom_checkout_info_in_admin($order) {
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $lat = $order->get_meta('_delivery_latitude');
    $lng = $order->get_meta('_delivery_longitude');
    $payment_method = $order->get_payment_method();
    $coupon_code = $order->get_meta('_coupon_code');
    $discount_amount = $order->get_meta('_discount_amount');
    
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
    
    if ($coupon_code) {
        echo '<p><strong>Coupon:</strong> ' . esc_html($coupon_code) . ' (-₱' . number_format($discount_amount, 2) . ')</p>';
    }
    
    echo '</div>';
    
    if ($payment_method === 'bank_transfer' || $payment_method === 'bacs') {
        echo '<div class="bank-transfer-info" style="padding: 15px; background: #f0fdf4; margin-top: 15px; border-radius: 4px; border-left: 4px solid #10b981;">';
        echo '<h3 style="margin-top: 0;">💳 Bank Transfer Details</h3>';
        
        echo '<div style="margin-bottom: 20px; padding: 10px; background: white; border-radius: 4px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #2563eb;">BDO Account</h4>';
        echo '<p style="margin: 5px 0;"><strong>Account Name:</strong> Kha V Ngo</p>';
        echo '<p style="margin: 5px 0;"><strong>Bank:</strong> BDO</p>';
        echo '<p style="margin: 5px 0;"><strong>Account Number:</strong> <span style="font-size: 16px; font-weight: bold; color: #2563eb;">007540182560</span></p>';
        echo '<p style="margin: 10px 0 5px 0;"><strong>QR Code:</strong></p>';
        echo '<img src="https://so-mot.com/wp-content/uploads/2025/10/BDO-007540182560-Kha-V-Ngo.jpg" alt="BDO QR Code" style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '</div>';
        
        echo '<div style="padding: 10px; background: white; border-radius: 4px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #10b981;">GCash Account</h4>';
        echo '<p style="margin: 5px 0;"><strong>Account Name:</strong> V**BI*H N</p>';
        echo '<p style="margin: 5px 0;"><strong>Payment Method:</strong> GCash</p>';
        echo '<p style="margin: 5px 0;"><strong>Phone Number:</strong> <span style="font-size: 16px; font-weight: bold; color: #10b981;">09950979419</span></p>';
        echo '<p style="margin: 10px 0 5px 0;"><strong>QR Code:</strong></p>';
        echo '<img src="https://so-mot.com/wp-content/uploads/2025/10/Gcash-09950979419-V-BI-H-N.jpg" alt="GCash QR Code" style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '</div>';
        
        echo '<div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px;">';
        echo '<p style="margin: 0; font-size: 13px;"><strong>⚠️ Note:</strong> Please confirm payment by sending transfer slip to complete the order.</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
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

// 5. Customize order confirmation email
add_filter('woocommerce_email_order_meta_fields', 'add_custom_fields_to_order_email', 10, 3);
function add_custom_fields_to_order_email($fields, $sent_to_admin, $order) {
    $custom_fields = array();
    
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $coupon_code = $order->get_meta('_coupon_code');
    $discount_amount = $order->get_meta('_discount_amount');
    
    if ($delivery_type) {
        $custom_fields[] = array('label' => 'Delivery Type', 'value' => ucfirst($delivery_type));
    }
    
    if ($delivery_date) {
        $custom_fields[] = array('label' => 'Delivery Date', 'value' => $delivery_date);
    }
    
    if ($delivery_time) {
        $custom_fields[] = array('label' => 'Delivery Time', 'value' => $delivery_time);
    }
    
    if ($branch) {
        $custom_fields[] = array('label' => 'Selected Branch', 'value' => ucfirst($branch));
    }
    
    if ($coupon_code) {
        $custom_fields[] = array('label' => 'Coupon Applied', 'value' => $coupon_code . ' (-₱' . number_format($discount_amount, 2) . ')');
    }
    
    $need_vat = $order->get_meta('_need_vat_invoice');
    if ($need_vat === 'yes') {
        $custom_fields[] = array('label' => 'VAT Invoice', 'value' => 'Required');
        
        $vat_company = $order->get_meta('_vat_company_name');
        if ($vat_company) {
            $custom_fields[] = array('label' => 'Company Name', 'value' => $vat_company);
        }
        
        $vat_address = $order->get_meta('_vat_company_address');
        if ($vat_address) {
            $custom_fields[] = array('label' => 'Company Address', 'value' => $vat_address);
        }
        
        $vat_tax_code = $order->get_meta('_vat_tax_code');
        if ($vat_tax_code) {
            $custom_fields[] = array('label' => 'Tax Code', 'value' => $vat_tax_code);
        }
    }
    
    return array_merge($fields, $custom_fields);
}

// 6. Add custom content to order emails
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
    $coupon_code = $order->get_meta('_coupon_code');
    $discount_amount = $order->get_meta('_discount_amount');
    
    if ($plain_text) {
        echo "\n========================================\n";
        echo "DELIVERY INFORMATION\n";
        echo "========================================\n\n";
        
        if ($delivery_type) echo "Delivery Type: " . ucfirst($delivery_type) . "\n";
        if ($delivery_date) echo "Delivery Date: " . $delivery_date . "\n";
        if ($delivery_time) echo "Delivery Time: " . $delivery_time . "\n";
        if ($branch && $delivery_type === 'delivery') echo "Branch: " . ucfirst($branch) . "\n";
        if ($coupon_code) echo "Coupon: " . $coupon_code . " (-₱" . number_format($discount_amount, 2) . ")\n";
        
        if ($need_vat === 'yes') {
            echo "\n========================================\n";
            echo "VAT INVOICE INFORMATION\n";
            echo "========================================\n\n";
            
            $vat_company = $order->get_meta('_vat_company_name');
            $vat_address = $order->get_meta('_vat_company_address');
            $vat_tax_code = $order->get_meta('_vat_tax_code');
            
            if ($vat_company) echo "Company Name: " . $vat_company . "\n";
            if ($vat_address) echo "Company Address: " . $vat_address . "\n";
            if ($vat_tax_code) echo "Tax Code: " . $vat_tax_code . "\n";
        }
        
        echo "\n";
        
    } else {
        ?>
        <div style="margin-bottom: 40px; padding: 20px; background-color: #f7fafc; border-radius: 8px; border-left: 4px solid #4299e1;">
            <h2 style="color: #2d3748; margin-top: 0; font-size: 20px;">🚚 Delivery Information</h2>
            
            <?php if ($delivery_type): ?>
                <p style="margin: 8px 0;"><strong>Delivery Type:</strong> <?php echo esc_html(ucfirst($delivery_type)); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_date): ?>
                <p style="margin: 8px 0;"><strong>Delivery Date:</strong> <?php echo esc_html($delivery_date); ?></p>
            <?php endif; ?>
            
            <?php if ($delivery_time): ?>
                <p style="margin: 8px 0;"><strong>Delivery Time:</strong> <?php echo esc_html($delivery_time); ?></p>
            <?php endif; ?>
            
            <?php if ($branch && $delivery_type === 'delivery'): ?>
                <p style="margin: 8px 0;"><strong>Branch:</strong> <?php echo esc_html(ucfirst($branch)); ?></p>
            <?php endif; ?>
            
            <?php if ($coupon_code): ?>
                <p style="margin: 8px 0;"><strong>Coupon:</strong> <?php echo esc_html($coupon_code); ?> (-₱<?php echo number_format($discount_amount, 2); ?>)</p>
            <?php endif; ?>
        </div>
        
        <?php if ($need_vat === 'yes'): ?>
            <div style="margin-bottom: 40px; padding: 20px; background-color: #fffaf0; border-radius: 8px; border-left: 4px solid #ed8936;">
                <h2 style="color: #2d3748; margin-top: 0; font-size: 20px;">🧾 VAT Invoice Information</h2>
                
                <?php 
                $vat_company = $order->get_meta('_vat_company_name');
                $vat_address = $order->get_meta('_vat_company_address');
                $vat_tax_code = $order->get_meta('_vat_tax_code');
                ?>
                
                <?php if ($vat_company): ?>
                    <p style="margin: 8px 0;"><strong>Company Name:</strong> <?php echo esc_html($vat_company); ?></p>
                <?php endif; ?>
                
                <?php if ($vat_address): ?>
                    <p style="margin: 8px 0;"><strong>Company Address:</strong> <?php echo esc_html($vat_address); ?></p>
                <?php endif; ?>
                
                <?php if ($vat_tax_code): ?>
                    <p style="margin: 8px 0;"><strong>Tax Code:</strong> <?php echo esc_html($vat_tax_code); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php
    }
}

?>
