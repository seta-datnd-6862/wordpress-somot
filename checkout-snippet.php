<?php



// ========================================
// CUSTOM CHECKOUT PAGE
// ========================================

// 1. Detect custom checkout page và hiển thị nội dung
add_filter('the_content', 'custom_checkout_page_content');
function custom_checkout_page_content($content) {
    // THAY 'custom-checkout' BẰNG SLUG CỦA TRANG BẠN TẠO
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
    
    // Lấy thông tin chi nhánh từ settings (bạn có thể custom)
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
                            <input type="time" name="delivery_time" id="delivery_time" required>
                        </div>
                    </div>

                    <!-- Branch Selection (for delivery only) -->
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
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="need_vat" id="need_vat" value="1" style="width: auto;">
                                Do you need VAT invoice?
                            </label>
                        </div>

                        <!-- VAT Fields (hidden by default) -->
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

                        <div class="form-group">
                            <label>Country / Region <span class="required">*</span></label>
                            <select name="country" id="country" required>
                                <option value="Philippines">Philippines</option>
                                <option value="Vietnam" selected>Vietnam</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Street address <span class="required">*</span></label>
                            <input type="text" name="address" id="address-autocomplete" required placeholder="Start typing address...">
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
                                <input type="text" name="city" id="city" required>
                            </div>
                            <div class="form-group">
                                <label>State / County <span class="required">*</span></label>
                                <select name="state" id="state" required>
                                    <option value="Metro Manila">Metro Manila</option>
                                    <option value="Hanoi">Hanoi</option>
                                    <option value="Da Nang">Da Nang</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Postcode / ZIP <span class="required">*</span></label>
                                <input type="text" name="postcode" id="postcode" required>
                            </div>
                            <div class="form-group">
                                <label>Phone <span class="required">*</span></label>
                                <input type="tel" name="phone" id="phone" required>
                            </div>
                        </div>

                        <!-- Distance Info (for delivery) -->
                        <div class="delivery-info-box hidden" id="distance-info-box">
                            <p><strong>🚚 Khoảng cách:</strong> <span id="distance-display">0</span> km</p>
                            <p><strong>💰 Phí giao hàng:</strong> <span id="shipping-fee-display">0</span></p>
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

                        <div id="order-items">
                            <?php
                            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                                
                                if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                    ?>
                                    <div class="order-item">
                                        <img src="<?php echo esc_url(wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail')); ?>" alt="<?php echo esc_attr($_product->get_name()); ?>">
                                        <div class="order-item-info">
                                            <div class="order-item-name"><?php echo wp_kses_post($_product->get_name()); ?></div>
                                            <div class="order-item-quantity">Quantity: <?php echo $cart_item['quantity']; ?></div>
                                            <?php
                                            // Display variations
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
        let branchLocation = {lat: 14.6175959, lng: 120.9837713}; // Default branch
        let calculatedShippingFee = 0;
        
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
        
        // Calculate distance using Haversine formula
        function calculateDistance(destLat, destLng) {
            const R = 6371; // Earth radius in km
            const dLat = (destLat - branchLocation.lat) * Math.PI / 180;
            const dLon = (destLng - branchLocation.lng) * Math.PI / 180;
            
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                     Math.cos(branchLocation.lat * Math.PI / 180) * Math.cos(destLat * Math.PI / 180) *
                     Math.sin(dLon/2) * Math.sin(dLon/2);
            
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            $('#distance-display').text(distance.toFixed(2));
            $('#distance-info-box').removeClass('hidden');
            
            // Call API to get shipping fee
            getShippingFee(distance);
        }
        
        // Get shipping fee from API
        function getShippingFee(distance) {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            const cashOnDelivery = (paymentMethod === 'cod') ? 1 : 0;
            // get delivery time to determine night shift
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
                    console.log('response', response);
                    // Adjust based on your API response structure
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
            if ($(this).val() === 'delivery') {
                $('#branch-section').removeClass('hidden');
                calculatedShippingFee = 0; // Reset until address is selected
                
                // If address already selected, calculate
                if ($('#address_lat').val() && $('#address_lng').val()) {
                    calculateDistance(
                        parseFloat($('#address_lat').val()),
                        parseFloat($('#address_lng').val())
                    );
                }
            } else {
                calculatedShippingFee = 0;
                updateOrderTotal();
            }
        });
        
        // Branch selection change
        $('#branch-select').change(function() {
            const selectedOption = $(this).find('option:selected');
            branchLocation.lat = parseFloat(selectedOption.data('lat'));
            branchLocation.lng = parseFloat(selectedOption.data('lng'));
            
            // Recalculate if address already selected
            if ($('#address_lat').val() && $('#address_lng').val()) {
                calculateDistance(
                    parseFloat($('#address_lat').val()),
                    parseFloat($('#address_lng').val())
                );
            }
        });
        
        // Payment method change
        $('input[name="payment_method"]').change(function() {
            // Recalculate shipping fee if delivery selected
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
            
            // Validation
            if ($('input[name="delivery_type"]:checked').val() === 'delivery') {
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
            
            // Submit via AJAX
            let formData = $(this).serialize();
            formData += '&action=process_custom_checkout';
            formData += '&shipping_fee=' + calculatedShippingFee;
            
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

// 3. AJAX handler for processing custom checkout
add_action('wp_ajax_process_custom_checkout', 'process_custom_checkout');
add_action('wp_ajax_nopriv_process_custom_checkout', 'process_custom_checkout');
function process_custom_checkout() {
    try {
        // Create order
        $order = wc_create_order();
        
        // Add products from cart
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $order->add_product($cart_item['data'], $cart_item['quantity']);
        }
        
        // Add billing info
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
        
        // Add shipping info
        $order->set_shipping_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_shipping_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_shipping_address_1(sanitize_text_field($_POST['address']));
        $order->set_shipping_address_2(sanitize_text_field($_POST['apartment']));
        $order->set_shipping_city(sanitize_text_field($_POST['city']));
        $order->set_shipping_state(sanitize_text_field($_POST['state']));
        $order->set_shipping_postcode(sanitize_text_field($_POST['postcode']));
        $order->set_shipping_country(sanitize_text_field($_POST['country']));
        
        // Add custom meta data
        $order->update_meta_data('_delivery_type', sanitize_text_field($_POST['delivery_type']));
        $order->update_meta_data('_delivery_date', sanitize_text_field($_POST['delivery_date']));
        $order->update_meta_data('_delivery_time', sanitize_text_field($_POST['delivery_time']));
        $order->update_meta_data('_order_notes', sanitize_textarea_field($_POST['order_notes']));
        
        if ($_POST['delivery_type'] === 'delivery') {
            $order->update_meta_data('_selected_branch', sanitize_text_field($_POST['branch']));
            $order->update_meta_data('_delivery_latitude', sanitize_text_field($_POST['address_lat']));
            $order->update_meta_data('_delivery_longitude', sanitize_text_field($_POST['address_lng']));
            
            // Add shipping fee
            $shipping_fee = floatval($_POST['shipping_fee']);
            if ($shipping_fee > 0) {
                $item = new WC_Order_Item_Shipping();
                $item->set_method_title('Delivery');
                $item->set_method_id('custom_delivery');
                $item->set_total($shipping_fee);
                $order->add_item($item);
            }
        }
        
        // Set payment method
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $order->set_payment_method($payment_method);
        $order->set_payment_method_title($payment_method === 'cod' ? 'Cash on Delivery' : 'Direct Bank Transfer');
        
        // Calculate totals
        $order->calculate_totals();
        
        // Save order
        $order->save();
        
        // Empty cart
        WC()->cart->empty_cart();
        
        // Send response
        wp_send_json_success(array(
            'redirect' => $order->get_checkout_order_received_url()
        ));
        
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}

// 4. Display custom checkout info in admin order page
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_checkout_info_in_admin');
function display_custom_checkout_info_in_admin($order) {
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch = $order->get_meta('_selected_branch');
    $lat = $order->get_meta('_delivery_latitude');
    $lng = $order->get_meta('_delivery_longitude');
    
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
    
    echo '</div>';
}

