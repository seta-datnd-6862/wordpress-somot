/**
 * ═══════════════════════════════════════════════════════════════
 * CUSTOM CHECKOUT PAGE — MERGED
 * ═══════════════════════════════════════════════════════════════
 *
 * UI:    Document 2 — Mobile-first, single-page, card-based layout
 * Logic: Document 1 — Full feature set preserved
 *
 * MERGED FEATURES (from Doc1 into Doc2 UI):
 *   ✅ Delivery area validation (Metro Manila cities check)
 *   ✅ Multiple coupons support (appliedCoupons[] array)
 *   ✅ Full coupon validation (Smart Coupons Pro, per-user limits, email restrictions)
 *   ✅ Nearest branch suggestions with distance
 *   ✅ Full address fields (city dropdown, state, postcode, apartment)
 *   ✅ Rich available coupons cards (conditions, min amount, applied state)
 *   ✅ Auto-apply coupon from URL (?apply_coupon=CODE)
 *   ✅ User account creation with email notification
 *   ✅ E-receipt sidebar (Individual / Business)
 *   ✅ Branch time-slot filtering
 *   ✅ VAT invoice support
 *   ✅ Payment proof upload with drag & drop
 *   ✅ Add-ons display (prad + custom_addons)
 *   ✅ Distance-based shipping fee calculation via Goodriver API
 * ═══════════════════════════════════════════════════════════════
 */

// ============================================================
// HELPER: Calculate real cart item total (including add-ons)
// ============================================================
function calculate_cart_item_real_total($cart_item) {
    $_product      = $cart_item['data'];
    $quantity      = $cart_item['quantity'];
    $product_price = floatval($_product->get_price());
    $wc_line_total = floatval($cart_item['line_total']);
    $addon_total   = 0;

    // Add-ons from prad plugin (x1, NOT multiplied by quantity)
    if (!empty($cart_item['prad_selection']['extra_data'])) {
        foreach ($cart_item['prad_selection']['extra_data'] as $addon_data) {
            if (isset($addon_data['prad_additional']['field_raw'])) {
                $costs = $addon_data['prad_additional']['field_raw']['cost'] ?? [];
                foreach ((array) $costs as $cost) {
                    $addon_total += floatval($cost);
                }
            }
        }
    }

    // Add-ons from custom_addons (x1)
    if (!empty($cart_item['custom_addons'])) {
        foreach ($cart_item['custom_addons'] as $addon) {
            $addon_total += floatval($addon['price'] ?? 0) * intval($addon['qty'] ?? 1);
        }
    }

    return [
        'product_price' => $product_price,
        'addon_total'   => $addon_total,
        'wc_line_total' => $wc_line_total,
        'quantity'      => $quantity,
        'line_total'    => ($product_price * $quantity) + $addon_total,
    ];
}

// ============================================================
// Hook: Replace checkout page content
// ============================================================
add_filter('the_content', 'custom_checkout_page_content');
function custom_checkout_page_content($content) {
    if (is_page('checkout')) {
        ob_start();
        render_custom_checkout();
        return ob_get_clean();
    }
    return $content;
}

// ============================================================
// RENDER: Main checkout UI
// ============================================================
function render_custom_checkout() {
    if (WC()->cart->is_empty()) {
        echo '<div class="woocommerce-info">Your cart is empty. <a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '">Continue shopping</a></div>';
        return;
    }

    // ── Branch data (IDs match cookie sync: 136/137/138) ─────
    // 'id' = text slug used by Goodriver API
    // 'cookie_id' = numeric ID stored in somot_active_branch_id cookie
    $branches = [
        [
            'id' => 'pioneer',
            'cookie_id' => '136',
            'name' => 'So Mot Pioneer Center, Pasig city',
            'lat' => 14.5731404,
            'lng' => 121.0164509,
            'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines',
            'start_time' => '07:00',
            'end_time' => '23:00'
        ],
        [
            'id' => 'ayala',
            'cookie_id' => '138',
            'name' => 'So Mot Ayala Malls Cloverleaf',
            'lat' => 14.6550542,
            'lng' => 120.9630123,
            'address' => 'A. Bonifacio Ave, La Loma, Quezon City, 1115 Metro Manila, Philippines',
            'start_time' => '10:00',
            'end_time' => '22:00'
        ],
        [
            'id' => 'tayuman',
            'cookie_id' => '137',
            'name' => 'So Mot Tayuman, Santa Cruz, Manila',
            'lat' => 14.617797968904622,
            'lng' => 120.98393022997824,
            'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila',
            'start_time' => '08:00',
            'end_time' => '22:00'
        ],
    ];

    // ── Pre-calculate cart ─────────────────────────────────────
    $calculated_subtotal   = 0;
    $cart_items_calculated  = [];
    $cart_item_count        = 0;
    foreach (WC()->cart->get_cart() as $key => $item) {
        $p = $item['data'];
        if ($p && $p->exists() && $item['quantity'] > 0) {
            $calc = calculate_cart_item_real_total($item);
            $cart_items_calculated[$key] = $calc;
            $calculated_subtotal += $calc['line_total'];
            $cart_item_count     += $item['quantity'];
        }
    }
    ?>

    <!-- ==================== STYLES ==================== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Global ── */
        body {
            background-color: #ffffff !important;
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        }

        /* ── Layout ── */
        .m-checkout-wrapper {
            max-width: 650px; margin: 0 auto; padding: 0 15px 160px;
            font-family: 'Quicksand', sans-serif;
        }

        /* ── Sticky header ── */
        .m-header-sticky {
            position: sticky; top: 0;
            background: #fff; z-index: 999;
            display: flex; align-items: center; padding: 0 15px;
            height: 56px; border-bottom: 1px solid #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,.04); margin-bottom: 4px;
        }
        .m-header-sticky h1 {
            font-size: 20px; font-weight: 700; margin: 0 auto; color: #1a1a1a;
            font-family: 'Quicksand', sans-serif;
        }
        .m-back-btn { color: #3b7d3b; background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; }

        /* ── Cards ── */
        .m-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,.05);
            overflow: visible; margin-bottom: 16px; border: 1px solid #f0f0f0;
        }
        .m-card:first-of-type { margin-top: 16px; }
        .m-section { padding: 20px; border-bottom: 1px solid #f2f2f2; overflow: visible; }
        .m-section:last-child { border-bottom: none; }
        .m-section-title {
            font-size: 16px; font-weight: 700; color: #1a1a1a;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
            font-family: 'Quicksand', sans-serif;
        }
        .m-section-icon { color: #3b7d3b; }

        /* ── Delivery tabs ── */
        .m-type-tabs {
            display: flex; gap: 8px; background: #f0f0f0;
            border-radius: 12px; padding: 5px; margin-bottom: 18px;
        }
        .m-tab-btn {
            flex: 1; padding: 10px; border: none; border-radius: 9px;
            font-weight: 600; font-size: 14px; cursor: pointer;
            background: transparent; color: #777; transition: .2s;
            font-family: 'Quicksand', sans-serif;
        }
        .m-tab-btn.active { background: #fff; color: #3b7d3b; box-shadow: 0 2px 6px rgba(0,0,0,.09); }

        /* ── Form elements ── */
        .m-form-group { margin-bottom: 14px; }
        .m-label {
            font-size: 13px; font-weight: 600; color: #444;
            margin-bottom: 6px; display: block;
        }
        .m-label .req { color: #ef4444; margin-left: 2px; }
        .m-input {
            width: 100%; padding: 9px 14px; border: 1px solid #e5e7eb;
            border-radius: 10px; font-size: 14px; color: #1a1a1a;
            background: #fff; outline: none; box-sizing: border-box;
            transition: border-color .2s;
            font-family: 'Quicksand', sans-serif !important;
        }
        .m-input:focus { border-color: #3b7d3b; }
        .m-input-display {
            width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb;
            border-radius: 10px; font-size: 14px; color: #1a1a1a;
            background: #f9fafb; box-sizing: border-box;
            display: flex; justify-content: space-between; align-items: center;
            cursor: default; min-height: 46px;
        }
        textarea.m-input { resize: vertical; min-height: 80px; }
        .has-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
        .hidden { display: none !important; }

        /* ── 2-col grid ── */
        .m-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; overflow: visible; }

        /* ── Address row with Edit btn ── */
        .m-address-label-row {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;
        }
        .m-edit-btn {
            font-size: 13px; font-weight: 600; color: #3b7d3b; cursor: pointer;
            display: flex; align-items: center; gap: 4px; background: none; border: none; padding: 0;
        }

        /* ── Location button ── */
        .m-location-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; background: #f0fdf4; color: #3b7d3b;
            border: 1.5px solid #3b7d3b; border-radius: 8px;
            cursor: pointer; font-size: 13px; font-weight: 600;
            margin-top: 8px; transition: all .2s;
            font-family: 'Quicksand', sans-serif;
        }
        .m-location-btn:hover { background: #3b7d3b; color: #fff; }
        .m-location-btn:disabled { opacity: .5; cursor: not-allowed; }



        /* ── Delivery area error ── */
        .m-delivery-area-error {
            background: #fef2f2; border-left: 4px solid #ef4444;
            border-radius: 0 10px 10px 0; padding: 16px; margin-top: 12px;
            animation: slideDown .3s ease;
        }
        .m-delivery-area-error h4 { margin: 0 0 8px 0; color: #c62828; font-size: 15px; font-weight: 700; }
        .m-delivery-area-error p { margin: 4px 0; color: #c62828; font-size: 13px; line-height: 1.5; }
        .m-delivery-area-error ul {
            margin: 8px 0; padding-left: 20px; color: #c62828;
            display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 12px;
        }
        @media (max-width: 480px) {
            .m-delivery-area-error ul { grid-template-columns: 1fr; }
        }

        /* ── Distance info box ── */
        .m-distance-info {
            background: #f0fdf4; border-left: 3px solid #10b981;
            border-radius: 0 10px 10px 0; padding: 12px 16px; margin-top: 12px;
            font-size: 13px;
        }
        .m-distance-info p { margin: 4px 0; }

        /* ── E-receipt row ── */
        .m-ereceipt-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 20px; border-top: 1px solid #f2f2f2; cursor: pointer;
        }
        .m-ereceipt-row-left { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #1a1a1a; }
        .m-ereceipt-badge {
            display: none; background: #e8f5e9; color: #3b7d3b;
            font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px;
        }

        /* ── Order Summary items ── */
        .m-order-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 0;
            border-bottom: 1px solid #f8f8f8;
        }
        .m-order-item:last-child { border-bottom: none; }
        .m-item-img { width: 56px; height: 56px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
        .m-item-info { flex: 1; }
        .m-item-name { font-size: 14px; font-weight: 600; color: #1a1a1a; }
        .m-item-attr { font-size: 12px; color: #888; margin-top: 2px; }
        .m-item-price { font-size: 14px; font-weight: 700; color: #3b7d3b; margin-top: 4px; }
        .m-item-qty { font-size: 13px; color: #888; white-space: nowrap; }
        .m-items-badge {
            background: #e8f5e9; color: #3b7d3b; font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
        }

        /* ── Add-ons display ── */
        .m-addons-box {
            margin-top: 8px; background: #f0fdf4; border-left: 3px solid #3b7d3b;
            border-radius: 0 8px 8px 0; padding: 8px 12px;
        }
        .m-addons-label { font-size: 12px; font-weight: 700; color: #3b7d3b; margin-bottom: 5px; }
        .m-addon-line { font-size: 12px; color: #555; padding: 2px 0; }
        .m-addon-price { color: #3b7d3b; font-weight: 600; }

        /* ── Coupon section ── */
        .m-coupon-row { display: flex; gap: 10px; margin-bottom: 14px; }
        .m-coupon-input {
            flex: 1; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px;
            font-size: 14px; outline: none; font-family: 'Quicksand', sans-serif;
        }
        .m-coupon-input:focus { border-color: #3b7d3b; }
        .m-coupon-apply {
            background: #3b7d3b; color: #fff; border: none; border-radius: 10px;
            padding: 12px 22px; font-size: 14px; font-weight: 700; cursor: pointer;
            font-family: 'Quicksand', sans-serif; white-space: nowrap;
        }
        .m-coupon-apply:disabled { background: #ccc; cursor: not-allowed; }
        .m-coupon-message {
            padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 12px;
        }
        .m-coupon-message.success { background: #e8f5e9; color: #2e7d32; border-left: 3px solid #4caf50; }
        .m-coupon-message.error { background: #fef2f2; color: #c62828; border-left: 3px solid #ef4444; }

        /* Applied coupons tags */
        .m-applied-coupons { margin-bottom: 14px; }
        .m-coupon-tag {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 12px; background: #e8f5e9; border-radius: 20px;
            font-size: 13px; color: #3b7d3b; margin-right: 8px; margin-bottom: 8px;
            border: 1px solid #4caf50;
        }
        .m-coupon-tag-code { font-weight: 700; text-transform: uppercase; }
        .m-coupon-tag-discount { color: #1b5e20; }
        .m-coupon-tag-remove {
            background: none; border: none; color: #f44336; cursor: pointer;
            font-size: 16px; padding: 0; display: flex; align-items: center;
        }

        /* Available coupons */
        .m-avail-coupons-header {
            display: flex; align-items: center; gap: 6px; font-size: 13px;
            color: #3b7d3b; font-weight: 600; margin-bottom: 10px;
            cursor: pointer; user-select: none;
        }
        .m-avail-coupons-header:hover { color: #2d5016; }
        .m-coupon-toggle-icon { transition: transform .3s; display: inline-block; }
        .m-coupon-toggle-icon.open { transform: rotate(180deg); }

        .m-coupon-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #61c3a2 100%);
            border: 2px solid #3b7d3b; border-radius: 10px; padding: 12px 14px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 8px; cursor: pointer; position: relative;
            overflow: hidden; transition: all .3s;
        }
        .m-coupon-card::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 4px; height: 100%; background: #3b7d3b;
        }
        .m-coupon-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59,125,59,.2); }
        .m-coupon-card.disabled {
            opacity: .5; cursor: not-allowed; background: #f5f5f5; border-color: #ddd;
        }
        .m-coupon-card.disabled::before { background: #999; }
        .m-coupon-card.disabled:hover { transform: none; box-shadow: none; }
        .m-coupon-card.applied {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-color: #4caf50;
        }
        .m-coupon-card.applied::before { background: #4caf50; }
        .m-coupon-card-info { flex: 1; padding-left: 8px; }
        .m-coupon-card-code { font-size: 15px; font-weight: 700; color: #3b7d3b; text-transform: uppercase; letter-spacing: .5px; }
        .m-coupon-card-desc { font-size: 12px; color: #1e293b; margin-top: 2px; font-weight: 500; }
        .m-coupon-card-details { font-size: 11px; color: #64748b; margin-top: 4px; }
        .m-coupon-card-conditions { font-size: 11px; color: #ef4444; margin-top: 4px; }
        .m-coupon-card-discount { font-size: 17px; font-weight: 700; color: #3b7d3b; white-space: nowrap; margin-right: 10px; }
        .m-coupon-card-btn {
            padding: 8px 16px; background: #3b7d3b; color: #fff; border: none;
            border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer;
            white-space: nowrap; font-family: 'Quicksand', sans-serif;
        }
        .m-coupon-card-btn:disabled { background: #ccc; cursor: not-allowed; }
        .m-avail-coupons-list {
            max-height: 400px; overflow-y: auto; padding-right: 4px;
        }
        .m-avail-coupons-list::-webkit-scrollbar { width: 5px; }
        .m-avail-coupons-list::-webkit-scrollbar-thumb { background: #3b7d3b; border-radius: 3px; }

        /* ── Bank / Payment cards ── */
        .m-payment-label { font-size: 13px; color: #555; margin-bottom: 14px; }
        .m-bank-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }
        .m-bank-card {
            border: 1px solid #eee; border-radius: 14px; padding: 14px 12px;
            text-align: center;
        }
        .m-bank-name { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .m-bank-owner { font-size: 11px; color: #888; margin-bottom: 10px; text-transform: uppercase; letter-spacing: .3px; }
        .m-bank-acc-btn {
            background: #3b7d3b; color: #fff; border: none; border-radius: 8px;
            padding: 9px 12px; font-size: 12px; font-weight: 700; cursor: pointer;
            width: 100%; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;
            font-family: 'Quicksand', sans-serif;
        }
        .m-bank-qr { width: 100%; border-radius: 8px; margin-bottom: 8px; }
        .m-save-qr-btn {
            display: inline-block; border: 1.5px solid #3b7d3b; color: #3b7d3b;
            padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
            text-decoration: none; background: #f0fdf4; margin-top: 4px; transition: .15s;
            font-family: 'Quicksand', sans-serif;
        }
        .m-save-qr-btn:hover { background: #3b7d3b; color: #fff; }

        /* ── Upload Receipt ── */
        .m-upload-area {
            border: 2px dashed #a7f3d0; border-radius: 14px; padding: 26px 20px;
            text-align: center; background: #f0fdf4; cursor: pointer;
            transition: border-color .2s; margin-bottom: 4px;
        }
        .m-upload-area:hover, .m-upload-area.drag-over { border-color: #3b7d3b; }
        .m-upload-area.has-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
        .m-upload-icon { font-size: 28px; margin-bottom: 8px; }
        .m-upload-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .m-upload-hint { font-size: 12px; color: #888; margin-bottom: 12px; }
        .m-upload-btn {
            background: #3b7d3b; color: #fff; border: none; border-radius: 10px;
            padding: 10px 24px; font-size: 14px; font-weight: 600; cursor: pointer;
            font-family: 'Quicksand', sans-serif;
        }
        .m-upload-preview {
            display: none; margin-top: 12px; padding: 10px 14px;
            background: #fff; border-radius: 10px; border: 1px solid #d1fae5;
            align-items: center; gap: 10px;
        }
        .m-upload-preview.visible { display: flex; }
        .m-upload-preview-thumb { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; }
        .m-upload-preview-name { font-size: 13px; font-weight: 600; color: #3b7d3b; flex: 1; word-break: break-all; }
        .m-upload-clear { background: none; border: none; color: #999; font-size: 20px; cursor: pointer; }

        /* ── Totals ── */
        .m-totals-row {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 14px; color: #555; padding: 6px 0;
        }
        .m-totals-row.grand {
            font-size: 17px; font-weight: 700; color: #1a1a1a;
            border-top: 1px solid #f0f0f0; margin-top: 8px; padding-top: 14px;
        }
        .m-grand-amount { color: #3b7d3b; }
        .m-discount-row { color: #10b981; }

        /* ── Sticky footer ── */
        .m-sticky-footer {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 6px 20px; border-top: 1px solid #eee; z-index: 998;
            box-shadow: 0 -4px 15px rgba(0,0,0,.08);
        }
        @media (max-width: 767px) {
            .m-sticky-footer {
                bottom: 0;
                padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
            }
            .m-checkout-wrapper { padding-bottom: 220px; }
        }
        .m-proceed-btn {
            width: 100%; max-width: 620px; margin: 0 auto;
            background: #3b7d3b; color: #fff; border: none; border-radius: 14px;
            padding: 16px; font-size: 16px; font-weight: 700; cursor: pointer;
            display: flex; justify-content: center; align-items: center; gap: 8px;
            font-family: 'Quicksand', sans-serif;
        }
        .m-proceed-btn:disabled { opacity: .6; cursor: not-allowed; }

        /* ── Time popup ── */
        .m-time-popup-wrap { position: relative; z-index: 150; }
        .m-time-popup {
            position: absolute; top: calc(100% + 4px); left: 0; width: 100%;
            z-index: 500; background: #fff; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.18); border: 1px solid #d1fae5;
            max-height: 210px; overflow-y: auto;
        }
        .m-time-item {
            padding: 12px 16px; cursor: pointer; font-size: 14px;
            border-bottom: 1px solid #f9f9f9; font-family: 'Quicksand', sans-serif;
        }
        .m-time-item:first-child { border-radius: 12px 12px 0 0; }
        .m-time-item:last-child  { border-radius: 0 0 12px 12px; border-bottom: none; }
        .m-time-item:hover, .m-time-item.active { background: #f0fdf4; color: #3b7d3b; font-weight: 700; }

        /* ── Sidebar overlay ── */
        .m-sidebar-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.5);
            z-index: 2000; opacity: 0; visibility: hidden; transition: .3s;
        }
        .m-sidebar-overlay.active { opacity: 1; visibility: visible; }

        /* ── E-Receipt Sidebar ── */
        .m-receipt-sidebar {
            position: fixed; top: 0; right: -110%; width: 100%; max-width: 420px;
            height: 100%; background: #fff; z-index: 2001; transition: right .3s ease;
            display: flex; flex-direction: column; box-shadow: -6px 0 30px rgba(0,0,0,.12);
            overflow: hidden;
        }
        .m-receipt-sidebar.active { right: 0; }
        .m-rs-header {
            background: #3b7d3b; color: #fff; padding: 18px 20px;
            display: flex; align-items: center; gap: 12px; flex-shrink: 0;
        }
        .m-rs-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
        .m-rs-back { background: none; border: none; color: #fff; cursor: pointer; padding: 0; display: flex; }
        .m-rs-body { flex: 1; overflow-y: auto; padding: 20px; }
        .m-rs-tabs { display: flex; gap: 8px; background: #f0f0f0; border-radius: 10px; padding: 4px; margin-bottom: 20px; }
        .m-rs-tab {
            flex: 1; padding: 9px; border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            background: transparent; color: #777; transition: .2s;
            font-family: 'Quicksand', sans-serif;
        }
        .m-rs-tab.active { background: #fff; color: #3b7d3b; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
        .m-rs-note { font-size: 12px; color: #888; background: #f9fafb; border-radius: 8px; padding: 10px 12px; margin-bottom: 16px; }
        .m-rs-footer { padding: 16px 20px; border-top: 1px solid #f0f0f0; flex-shrink: 0; }
        .m-rs-save-btn {
            width: 100%; background: #3b7d3b; color: #fff; border: none;
            border-radius: 12px; padding: 15px; font-size: 15px; font-weight: 700; cursor: pointer;
            font-family: 'Quicksand', sans-serif;
        }

        /* ── SVG icons brand-colored ── */
        .m-back-btn svg, .m-edit-btn svg, .m-ereceipt-row svg:first-child,
        .m-section-title svg { stroke: #3b7d3b; }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .m-bank-grid { grid-template-columns: 1fr; }
        }

        /* ── Flatpickr overrides ── */
        .flatpickr-calendar {
            font-family: 'Quicksand', sans-serif !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,.20) !important;
            border: 1px solid #d1fae5 !important;
            z-index: 99999 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover {
            background: #3b7d3b !important; border-color: #3b7d3b !important; color: #fff !important;
        }
        .flatpickr-day.today { border-color: #3b7d3b !important; color: #3b7d3b !important; }
        .flatpickr-day:hover { background: #f0fdf4 !important; }
        .flatpickr-months .flatpickr-month, .flatpickr-weekdays {
            background: #3b7d3b !important; color: #fff !important; border-radius: 12px 12px 0 0;
        }
        span.flatpickr-weekday { color: rgba(255,255,255,.8) !important; }
        .flatpickr-current-month select, .flatpickr-current-month input.cur-year {
            color: #fff !important; font-family: 'Quicksand', sans-serif !important; font-weight: 700 !important;
        }
        .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { fill: #fff !important; }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.prevMonthDay { color: #ccc !important; }

        /* ── Google autocomplete ── */
        .pac-container {
            z-index: 99999 !important; border-radius: 10px !important;
            border: 1px solid #d1fae5 !important; box-shadow: 0 8px 24px rgba(0,0,0,.14) !important;
            font-family: 'Quicksand', sans-serif !important; margin-top: 4px;
        }
        .pac-item { padding: 8px 12px; font-size: 13px; cursor: pointer; }
        .pac-item:hover { background: #f0fdf4; }
        .pac-item-query { font-weight: 700; color: #1a1a1a; }
        .pac-matched { color: #3b7d3b; }
        .pac-icon { display: none; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 922px) {
            .ast-container { max-width: 1690px !important; }
        }
    </style>

    <!-- ==================== HTML ==================== -->
    <div class="m-checkout-wrapper">

        <!-- Sticky Header -->
        <div class="m-header-sticky">
            <button class="m-back-btn" onclick="history.back()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </button>
            <h1>Checkout</h1>
            <div style="width:39px"></div>
        </div>

        <form id="checkout-form" onsubmit="return false;">

            <!-- ===== 1. DELIVERY INFORMATION ===== -->
            <div class="m-card">
                <div class="m-section">
                    <div class="m-section-title">Delivery Information</div>

                    <!-- Delivery / Pickup tabs -->
                    <div class="m-type-tabs">
                        <button type="button" class="m-tab-btn active" id="tab-delivery" data-type="delivery">🚚 Delivery</button>
                        <button type="button" class="m-tab-btn" id="tab-pickup" data-type="pickup">📦 Pickup</button>
                    </div>

                    <!-- Branch display (locked — auto-mapped from cookie) -->
                    <div class="m-form-group">
                        <label class="m-label">Delivering from Branch</label>
                        <div class="m-input-display" id="val-branch" style="background:#f0fdf4; border-color:#d1fae5;">
                            <span id="branch-name-display" style="font-weight:600; color:#3b7d3b;">Loading...</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                    </div>
                    <!-- Hidden select keeps all branch data for JS to read -->
                    <select id="m_branch_select" style="display:none;">
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"
                                    data-cookie-id="<?php echo $b['cookie_id']; ?>"
                                    data-lat="<?php echo $b['lat']; ?>"
                                    data-lng="<?php echo $b['lng']; ?>"
                                    data-address="<?php echo esc_attr($b['address']); ?>"
                                    data-start-time="<?php echo $b['start_time']; ?>"
                                    data-end-time="<?php echo $b['end_time']; ?>">
                                <?php echo esc_html($b['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Delivery address (hidden when Pickup) -->
                    <div id="row-delivery-address">
                        <div class="m-address-label-row">
                            <label class="m-label" style="margin:0;">Delivery To <span class="req">*</span></label>
                            <button type="button" class="m-edit-btn" id="btn-edit-address">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                        </div>
                        <div class="m-input-display" id="val-address" style="min-height:50px; align-items:flex-start; padding-top:13px;">Please enter your address</div>
                        <div id="edit-address-container" style="display:none; margin-top:8px;">
                            <input type="text" id="edit_address_input" class="m-input" placeholder="Search delivery address...">
                            <button type="button" class="m-location-btn" id="get-location-btn">
                                📍 Use My Location
                            </button>
                        </div>

                        <!-- Delivery area warning (from Doc1) -->
                        <div id="delivery-area-warning" class="m-delivery-area-error hidden">
                            <h4>⚠️ Out of Delivery Area</h4>
                            <p>Sorry, we currently only deliver to the following cities in Metro Manila:</p>
                            <ul>
                                <li>PASAY</li><li>PARANAQUE</li><li>MAKATI</li><li>MANILA</li>
                                <li>MANDALUYONG</li><li>TAGUIG</li><li>PASIG</li><li>SAN JUAN</li>
                                <li>MALABON</li><li>MARIKINA</li><li>QUEZON CITY</li><li>LAS PIÑAS</li>
                                <li>VALENZUELA</li><li>CALOOCAN</li>
                            </ul>
                            <p><strong>Please select an address within these areas or choose "Pickup" instead.</strong></p>
                        </div>



                        <!-- Distance info (from Doc1) -->
                        <div id="distance-info-box" class="m-distance-info hidden">
                            <p><strong>🚚 Distance:</strong> <span id="distance-display">0</span> km</p>
                            <p><strong>💰 Shipping fee:</strong> <span id="shipping-fee-display">₱0</span></p>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="m-grid-2" style="margin-top: 14px;">
                        <div class="m-form-group" id="date-col">
                            <label class="m-label" id="date-label-title">Date <span class="req">*</span></label>
                            <div class="m-input-display" id="display_date" style="cursor:pointer;">
                                <span id="date-label-text">Today</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            </div>
                            <input type="text" id="sm-date-hidden" style="display:none;">
                        </div>

                        <div class="m-form-group m-time-popup-wrap">
                            <label class="m-label" id="time-label-title">Time <span class="req">*</span></label>
                            <div class="m-input-display" id="display_time" style="cursor:pointer;">
                                <span id="time-label-text">ASAP</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== 2. PERSONAL DETAILS ===== -->
            <div class="m-card">
                <div class="m-section">
                    <div class="m-section-title">👤 Personal Details</div>

                    <div class="m-grid-2">
                        <div class="m-form-group">
                            <label class="m-label">First Name <span class="req">*</span></label>
                            <input type="text" id="m_first_name" class="m-input" placeholder="First name"
                                   value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->first_name) : ''; ?>">
                        </div>
                        <div class="m-form-group">
                            <label class="m-label">Last Name <span class="req">*</span></label>
                            <input type="text" id="m_last_name" class="m-input" placeholder="Last name"
                                   value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->last_name) : ''; ?>">
                        </div>
                    </div>

                    <div class="m-grid-2">
                        <div class="m-form-group">
                            <label class="m-label">Phone <span class="req">*</span></label>
                            <input type="tel" id="m_phone" class="m-input" placeholder="09xxxxxxxxx"
                                   value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_phone', true)) : ''; ?>">
                        </div>
                        <div class="m-form-group">
                            <label class="m-label">Email <span class="req">*</span></label>
                            <input type="email" id="m_email" class="m-input" placeholder="email@example.com"
                                   value="<?php echo is_user_logged_in() ? esc_attr(wp_get_current_user()->user_email) : ''; ?>">
                        </div>
                    </div>

                    <!-- Extra address fields from Doc1 -->
                    <div class="m-form-group">
                        <label class="m-label">Apartment, suite, unit, etc. (optional)</label>
                        <input type="text" id="m_apartment" class="m-input" placeholder="Apartment, suite..."
                               value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_address_2', true)) : ''; ?>">
                    </div>

                    <div class="m-grid-2">
                        <div class="m-form-group">
                            <label class="m-label">Town / City <span class="req">*</span></label>
                            <select id="m_city" class="m-input">
                                <?php $user_city = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'billing_city', true) : ''; ?>
                                <option value="">-- Select City --</option>
                                <?php
                                $cities = ['PASAY','PARANAQUE','MAKATI','MANILA','MANDALUYONG','TAGUIG','PASIG','SAN JUAN','MALABON','MARIKINA','QUEZON CITY','LAS PIÑAS','VALENZUELA','CALOOCAN'];
                                foreach ($cities as $c):
                                ?>
                                    <option value="<?php echo $c; ?>" <?php echo ($user_city === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="m-form-group">
                            <label class="m-label">Postcode / ZIP <span class="req">*</span></label>
                            <input type="text" id="m_postcode" class="m-input" placeholder="Postcode"
                                   value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'billing_postcode', true)) : ''; ?>">
                        </div>
                    </div>

                    <div class="m-form-group">
                        <label class="m-label">Order Notes (Optional)</label>
                        <textarea id="m_order_notes" class="m-input" placeholder="Special instructions..."></textarea>
                    </div>
                </div>

                <!-- E-receipt row -->
                <div class="m-ereceipt-row" id="btn-open-receipt">
                    <div class="m-ereceipt-row-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        E-receipt / VAT Invoice
                        <span class="m-ereceipt-badge" id="ereceipt-badge">Added ✓</span>
                    </div>
                    <span style="color:#999; font-size:13px; display:flex; align-items:center; gap:4px;">
                        Request Now
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                </div>
            </div>

            <!-- ===== 3. ORDER SUMMARY ===== -->
            <div class="m-card">
                <div class="m-section">
                    <div class="m-section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Order Summary
                        <span class="m-items-badge"><?php echo $cart_item_count; ?> ITEMS</span>
                    </div>

                    <?php foreach (WC()->cart->get_cart() as $key => $item):
                        $p    = $item['data'];
                        $calc = $cart_items_calculated[$key];
                        $img  = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src();
                        $attrs = [];
                        if (!empty($item['variation'])) {
                            foreach ($item['variation'] as $attr_key => $attr_val) {
                                if ($attr_val) $attrs[] = ucfirst(str_replace(['attribute_pa_', 'attribute_'], '', $attr_key)) . ': ' . $attr_val;
                            }
                        }
                        $addon_lines = [];
                        if (!empty($item['prad_selection']['extra_data'])) {
                            foreach ($item['prad_selection']['extra_data'] as $addon_data) {
                                if (isset($addon_data['prad_additional']['field_raw'])) {
                                    $fr    = $addon_data['prad_additional']['field_raw'];
                                    $vals  = $fr['value'] ?? [];
                                    $costs = $fr['cost'] ?? [];
                                    foreach ((array)$vals as $idx => $val) {
                                        $addon_lines[] = ['label' => $val, 'price' => floatval($costs[$idx] ?? 0)];
                                    }
                                }
                            }
                        }
                        if (!empty($item['custom_addons'])) {
                            foreach ($item['custom_addons'] as $addon) {
                                $lbl = $addon['optionLabel'] ?? ($addon['label'] ?? '');
                                $grp = $addon['group'] ?? '';
                                if ($lbl) $addon_lines[] = ['label' => ($grp ? $grp . ': ' : '') . $lbl, 'price' => floatval($addon['price'] ?? 0)];
                            }
                        }
                    ?>
                    <div class="m-order-item">
                        <img src="<?php echo esc_url($img); ?>" alt="" class="m-item-img">
                        <div class="m-item-info">
                            <div class="m-item-name">
                                <?php echo esc_html($p->get_name()); ?>
                                <span style="font-weight:500; color:#888;"> — ₱<?php echo number_format($calc['product_price'], 2); ?></span>
                            </div>
                            <?php if (!empty($attrs)): ?>
                                <div class="m-item-attr"><?php echo esc_html(implode(', ', $attrs)); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($addon_lines)): ?>
                                <div class="m-addons-box">
                                    <div class="m-addons-label">Add-ons:</div>
                                    <?php foreach ($addon_lines as $al): ?>
                                        <div class="m-addon-line">
                                            <?php echo esc_html($al['label']); ?>
                                            <?php if ($al['price'] > 0): ?>
                                                <span class="m-addon-price"> — ₱<?php echo number_format($al['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="m-item-price" style="margin-top:6px;">₱<?php echo number_format($calc['line_total'], 2); ?></div>
                        </div>
                        <div class="m-item-qty">Qty: <?php echo $item['quantity']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Coupon section (FULL from Doc1) -->
                <div class="m-section">
                    <div id="coupon-message-area"></div>
                    <div id="applied-coupons-area" class="m-applied-coupons"></div>

                    <div class="m-coupon-row">
                        <input type="text" id="coupon-input" class="m-coupon-input" placeholder="Enter coupon code" maxlength="50">
                        <button type="button" class="m-coupon-apply" id="btn-apply-coupon">Apply</button>
                    </div>

                    <!-- Available coupons (rich cards from Doc1) -->
                    <div class="m-avail-coupons-header" id="toggle-avail-coupons">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        🎟️ Available Coupons
                        <span class="m-coupon-toggle-icon" id="coupon-toggle-icon">▼</span>
                    </div>
                    <div id="available-coupons-list" class="m-avail-coupons-list hidden">
                        <div style="color:#aaa; font-size:13px; padding:4px 0;">Loading coupons...</div>
                    </div>
                </div>
            </div>

            <!-- ===== 4. PAYMENT METHOD ===== -->
            <div class="m-card">
                <div class="m-section">
                    <div class="m-section-title">💳 Payment Method</div>
                    <p class="m-payment-label">Please transfer to one of the accounts below, then upload your receipt.</p>

                    <div class="m-bank-grid">
                        <div class="m-bank-card">
                            <div class="m-bank-name">📱 G-Cash</div>
                            <div class="m-bank-owner">ANATALIO JR FRANCISCO</div>
                            <button type="button" class="m-bank-acc-btn" onclick="copyAccNum('09277224868', this)">
                                09277224868
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <img src="https://so-mot.com/wp-content/uploads/2026/03/gcash-ANATALIO-JR-FRANCISCO.jpg" alt="GCash QR" class="m-bank-qr">
                            <a href="https://so-mot.com/wp-content/uploads/2026/03/gcash-ANATALIO-JR-FRANCISCO.jpg" download class="m-save-qr-btn">Save QR</a>
                        </div>
                        <div class="m-bank-card">
                            <div class="m-bank-name">🏦 BPI</div>
                            <div class="m-bank-owner">KEYSTONE VENTURE NETWORK CORPORATION</div>
                            <button type="button" class="m-bank-acc-btn" onclick="copyAccNum('0251000611', this)">
                                0251000611
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="m-bank-card">
                            <div class="m-bank-name">📱 TP Bank</div>
                            <div class="m-bank-owner">TRAN THI HOA</div>
                            <button type="button" class="m-bank-acc-btn" onclick="copyAccNum('10001327503', this)">
                                10001327503
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <img src="https://so-mot.com/wp-content/uploads/2026/03/tpbank-c-Hoa.jpg" alt="TP Bank QR" class="m-bank-qr">
                            <a href="https://so-mot.com/wp-content/uploads/2026/03/tpbank-c-Hoa.jpg" download class="m-save-qr-btn">Save QR</a>
                        </div>
                        <div class="m-bank-card">
                            <div class="m-bank-name">🏦 BDO</div>
                            <div class="m-bank-owner">Kha V Ngo</div>
                            <button type="button" class="m-bank-acc-btn" onclick="copyAccNum('007540182560', this)">
                                007540182560
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <img src="https://so-mot.com/wp-content/uploads/2026/03/Kha-V-Ngo-BDO.jpg" alt="BDO QR" class="m-bank-qr">
                            <a href="https://so-mot.com/wp-content/uploads/2026/03/Kha-V-Ngo-BDO.jpg" download class="m-save-qr-btn">Save QR</a>
                        </div>
                    </div>

                    <!-- Upload Receipt -->
                    <div id="upload-area" class="m-upload-area">
                        <div class="m-upload-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b7d3b" stroke-width="1.8"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                        </div>
                        <div class="m-upload-title">Upload Receipt <span style="color:#ef4444">*</span></div>
                        <div class="m-upload-hint">PNG, JPG, PDF — Max 5MB</div>
                        <button type="button" class="m-upload-btn" onclick="event.stopPropagation(); document.getElementById('payment-proof-file').click();">Select File</button>
                    </div>
                    <input type="file" id="payment-proof-file" accept=".png,.jpg,.jpeg,.pdf" style="display:none;">
                    <div class="m-upload-preview" id="upload-preview">
                        <img id="upload-thumb" class="m-upload-preview-thumb" src="" alt="">
                        <span class="m-upload-preview-name" id="upload-file-name"></span>
                        <button type="button" class="m-upload-clear" id="btn-clear-upload" title="Remove">×</button>
                    </div>
                </div>

                <!-- Totals -->
                <div class="m-section" style="background:#fafafa; border-radius: 0 0 16px 16px;">
                    <div class="m-totals-row"><span>Subtotal</span><span id="display-subtotal">₱<?php echo number_format($calculated_subtotal, 2); ?></span></div>
                    <div class="m-totals-row m-discount-row" id="discount-row" style="display:none;">
                        <span>Discount</span><span id="display-discount">-₱0.00</span>
                    </div>
                    <div class="m-totals-row"><span>Shipping Fee</span><span id="display-shipping-fee">₱0.00</span></div>
                    <div class="m-totals-row grand">
                        <span>Total Amount</span>
                        <span class="m-grand-amount" id="display-total">₱<?php echo number_format($calculated_subtotal, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Hidden fields -->
            <div style="display:none;">
                <input type="hidden" id="h_delivery_type" value="delivery">
                <input type="hidden" id="h_address" value="">
                <input type="hidden" id="h_address_lat" value="">
                <input type="hidden" id="h_address_lng" value="">
                <input type="hidden" id="h_branch" value="">
                <input type="hidden" id="h_delivery_date" value="<?php echo date('Y-m-d'); ?>">
                <input type="hidden" id="h_delivery_time" value="ASAP">
                <input type="hidden" id="h_shipping_fee" value="0">
                <input type="hidden" id="h_receipt_type" value="">
                <input type="hidden" id="h_receipt_name" value="">
                <input type="hidden" id="h_receipt_tin" value="">
                <input type="hidden" id="h_receipt_address" value="">
                <input type="hidden" id="h_receipt_email" value="">
            </div>
        </form>

        <!-- Sticky footer -->
        <div class="m-sticky-footer">
            <button type="button" id="m-place-order-btn" class="m-proceed-btn">
                Proceed to Payment • <span id="btn-total-display">₱<?php echo number_format($calculated_subtotal, 2); ?></span>
            </button>
        </div>
    </div>

    <!-- ===== SIDEBAR OVERLAY ===== -->
    <div class="m-sidebar-overlay" id="sidebar-overlay"></div>

    <!-- ===== E-RECEIPT / VAT SIDEBAR ===== -->
    <div class="m-receipt-sidebar" id="receipt-sidebar">
        <div class="m-rs-header">
            <button class="m-rs-back" id="btn-close-receipt">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </button>
            <h2>Request E-Invoice / VAT</h2>
        </div>
        <div class="m-rs-body">
            <div class="m-rs-tabs">
                <button type="button" class="m-rs-tab active" data-tab="individual">Individual</button>
                <button type="button" class="m-rs-tab" data-tab="business">Business</button>
            </div>
            <div id="rs-tab-individual">
                <div class="m-rs-note">For personal VAT receipts. Enter your TIN (optional for individuals).</div>
                <div class="m-form-group"><label class="m-label">Full Name <span class="req">*</span></label><input type="text" id="rs_ind_name" class="m-input" placeholder="Your full name"></div>
                <div class="m-form-group"><label class="m-label">TIN Number (Optional)</label><input type="text" id="rs_ind_tin" class="m-input" placeholder="e.g. 123-456-789-000"></div>
                <div class="m-form-group"><label class="m-label">Address</label><textarea id="rs_ind_address" class="m-input" rows="2" placeholder="Your address"></textarea></div>
                <div class="m-form-group"><label class="m-label">Email</label><input type="email" id="rs_ind_email" class="m-input" placeholder="email@example.com"></div>
            </div>
            <div id="rs-tab-business" style="display:none;">
                <div class="m-rs-note">For official business VAT invoices (OR). A registered TIN is required.</div>
                <div class="m-form-group"><label class="m-label">Company Name <span class="req">*</span></label><input type="text" id="rs_biz_name" class="m-input" placeholder="Registered business name"></div>
                <div class="m-form-group"><label class="m-label">TIN Number <span class="req">*</span></label><input type="text" id="rs_biz_tin" class="m-input" placeholder="e.g. 123-456-789-000"></div>
                <div class="m-form-group"><label class="m-label">Company Address <span class="req">*</span></label><textarea id="rs_biz_address" class="m-input" rows="2" placeholder="Registered address"></textarea></div>
                <div class="m-form-group"><label class="m-label">Email</label><input type="email" id="rs_biz_email" class="m-input" placeholder="accounts@company.com"></div>
            </div>
        </div>
        <div class="m-rs-footer">
            <button type="button" class="m-rs-save-btn" id="btn-save-receipt">Save Invoice Info</button>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXShFxiu-eawxmLBhT8NamWJK7giYd6Dc&libraries=places"></script>

    <script>
    (function($) {
        // ══════════════════════════════════════════
        // CONSTANTS
        // ══════════════════════════════════════════
        const SUBTOTAL  = <?php echo $calculated_subtotal; ?>;
        const BRANCHES  = <?php echo json_encode($branches); ?>;
        const AJAX_URL  = '<?php echo admin_url('admin-ajax.php'); ?>';

        const ALLOWED_CITIES = [
            'PASAY','PARANAQUE','PARAÑAQUE','MAKATI','MANILA',
            'MANDALUYONG','TAGUIG','PASIG','SAN JUAN','MALABON',
            'MARIKINA','QUEZON CITY','LAS PIÑAS','LAS PINAS',
            'VALENZUELA','CALOOCAN'
        ];

        // ══════════════════════════════════════════
        // STATE
        // ══════════════════════════════════════════
        let branchLocation     = { lat: BRANCHES[0].lat, lng: BRANCHES[0].lng };
        let calculatedShippingFee = 0;
        let selectedFile       = null;
        let selectedPlace      = null;
        let appliedCoupons     = [];
        let totalDiscount      = 0;
        let availableCouponsData = [];
        let receiptTab         = 'individual';
        let ac                 = null; // Google autocomplete

        // ══════════════════════════════════════════
        // HELPERS
        // ══════════════════════════════════════════
        function setCookie(n, v) { document.cookie = n + '=' + encodeURIComponent(v) + '; path=/; max-age=86400'; }
        function getCookie(n)    { const m = document.cookie.match(new RegExp('(?:^| )' + n + '=([^;]+)')); return m ? decodeURIComponent(m[1]) : ''; }
        function fmtMoney(n)     { return '₱' + parseFloat(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }

        function calculateDistanceKm(lat1, lng1, lat2, lng2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        // ══════════════════════════════════════════
        // UPDATE TOTALS
        // ══════════════════════════════════════════
        function updateTotals() {
            const total = Math.max(0, SUBTOTAL + calculatedShippingFee - totalDiscount);
            $('#display-shipping-fee').text(fmtMoney(calculatedShippingFee));
            $('#shipping-fee-display').text(fmtMoney(calculatedShippingFee));
            $('#display-total').text(fmtMoney(total));
            $('#btn-total-display').text(fmtMoney(total));
            if (totalDiscount > 0) {
                $('#discount-row').show();
                $('#display-discount').text('-' + fmtMoney(totalDiscount));
            } else {
                $('#discount-row').hide();
            }
            $('#h_shipping_fee').val(calculatedShippingFee);
        }

        // ══════════════════════════════════════════
        // DELIVERY AREA VALIDATION (from Doc1)
        // ══════════════════════════════════════════
        function validateDeliveryArea(placeResult, deliveryType) {
            if (deliveryType !== 'delivery') {
                $('#delivery-area-warning').addClass('hidden');
                enableProceedButton();
                return true;
            }
            let selectedCity = '';
            if (placeResult && placeResult.address_components) {
                for (let comp of placeResult.address_components) {
                    if (comp.types.includes('locality') || comp.types.includes('administrative_area_level_2')) {
                        selectedCity = comp.long_name.toUpperCase();
                        break;
                    }
                }
            }
            const isValid = ALLOWED_CITIES.some(city => selectedCity.includes(city) || city.includes(selectedCity));
            if (!isValid) {
                $('#delivery-area-warning').removeClass('hidden');
                disableProceedButton();
                $('#distance-info-box').addClass('hidden');
                calculatedShippingFee = 0;
                updateTotals();
                return false;
            }
            $('#delivery-area-warning').addClass('hidden');
            enableProceedButton();
            return true;
        }

        function disableProceedButton() {
            $('#m-place-order-btn').prop('disabled', true);
        }
        function enableProceedButton() {
            $('#m-place-order-btn').prop('disabled', false);
        }



        // ══════════════════════════════════════════
        // SHIPPING FEE CALCULATION
        // ══════════════════════════════════════════
        function calculateDistance(destLat, destLng) {
            const dist = calculateDistanceKm(branchLocation.lat, branchLocation.lng, destLat, destLng);
            $('#distance-display').text(dist.toFixed(2));
            $('#distance-info-box').removeClass('hidden');
            getShippingFee(dist);
        }

        function getShippingFee(distance) {
            const deliveryTime = $('#h_delivery_time').val() || 'ASAP';
            let nightShift = 0;
            if (deliveryTime !== 'ASAP') {
                const hour = parseInt(deliveryTime.split(':')[0]);
                nightShift = (hour >= 22 || hour <= 6) ? 1 : 0;
            }
            $.post('https://goodriver.online/api/setting/get-delivery-fee', {
                distance: distance.toFixed(2),
                cash_on_delivery: 0, holiday: 0, night_shift: nightShift, raining: 0
            }, function(response) {
                calculatedShippingFee = parseFloat(response?.data?.total_delivery_fee) || 0;
                updateTotals();
            }).fail(function() {
                calculatedShippingFee = 0;
                updateTotals();
            });
        }

        // ══════════════════════════════════════════
        // GOOGLE MAPS AUTOCOMPLETE
        // ══════════════════════════════════════════
        function initAutocomplete() {
            if (ac) return;
            ac = new google.maps.places.Autocomplete(
                document.getElementById('edit_address_input'),
                { componentRestrictions: { country: 'ph' } }
            );
            ac.addListener('place_changed', function() {
                selectedPlace = ac.getPlace();
                if (!selectedPlace.geometry) return;

                const lat  = selectedPlace.geometry.location.lat();
                const lng  = selectedPlace.geometry.location.lng();
                const addr = selectedPlace.formatted_address;

                $('#val-address').text(addr).removeClass('has-error');
                $('#h_address').val(addr);
                $('#h_address_lat').val(lat);
                $('#h_address_lng').val(lng);
                setCookie('somot_customer_address', addr);
                setCookie('somot_address_lat', lat);
                setCookie('somot_address_lng', lng);

                $('#edit-address-container').slideUp(200);

                const deliveryType = $('#h_delivery_type').val();
                const isValid = validateDeliveryArea(selectedPlace, deliveryType);
                if (isValid) {
                    if (deliveryType === 'delivery') calculateDistance(lat, lng);
                }
            });
        }

        // ══════════════════════════════════════════
        // DELIVERY / PICKUP TABS
        // ══════════════════════════════════════════
        $('.m-tab-btn').on('click', function() {
            const type = $(this).data('type');
            $('.m-tab-btn').removeClass('active');
            $(this).addClass('active');
            $('#h_delivery_type').val(type);
            setCookie('somot_order_type', type);

            if (type === 'pickup') {
                $('#row-delivery-address').slideUp(200);
                $('#delivery-area-warning').addClass('hidden');
                $('#distance-info-box').addClass('hidden');
                calculatedShippingFee = 0;
                updateTotals();
                enableProceedButton();
                $('#date-label-title').html('Pick up date <span class="req">*</span>');
                $('#time-label-title').html('Pick up time <span class="req">*</span>');
            } else {
                $('#row-delivery-address').slideDown(200);
                $('#date-label-title').html('Delivery date <span class="req">*</span>');
                $('#time-label-title').html('Delivery time <span class="req">*</span>');
                const lat = parseFloat($('#h_address_lat').val()) || 0;
                const lng = parseFloat($('#h_address_lng').val()) || 0;
                if (lat && lng) {
                    if (selectedPlace) validateDeliveryArea(selectedPlace, 'delivery');
                    calculateDistance(lat, lng);
                }
            }
        });

        // ══════════════════════════════════════════
        // BRANCH — LOCKED FROM COOKIE
        // ══════════════════════════════════════════
        // Branch is auto-set from cookie somot_active_branch_id
        // and cannot be changed during checkout.

        $('#m_branch_select').change(function() {
            const opt = $(this).find('option:selected');
            branchLocation.lat = parseFloat(opt.data('lat'));
            branchLocation.lng = parseFloat(opt.data('lng'));
            $('#h_branch').val($(this).val());

            // Update display name
            $('#branch-name-display').text(opt.text().trim());

            // Store branch hours for time popup
            $(this).data('current-start', opt.data('start-time'));
            $(this).data('current-end', opt.data('end-time'));

            // Recalc shipping if address set
            const lat = parseFloat($('#h_address_lat').val()) || 0;
            const lng = parseFloat($('#h_address_lng').val()) || 0;
            if (lat && lng && $('#h_delivery_type').val() === 'delivery') {
                calculateDistance(lat, lng);
            }
        });

        // Read branch from cookie and lock it
        // Cookie stores numeric ID (136/137/138), option value is text slug (pioneer/ayala/tayuman)
        // We match via data-cookie-id attribute
        (function initBranchFromCookie() {
            const cookieBranch = getCookie('somot_active_branch_id'); // e.g. '136'
            const urlBranch    = new URLSearchParams(window.location.search).get('branch'); // could be '136' or 'pioneer'

            const lookupId = urlBranch || cookieBranch || '136'; // fallback Pioneer

            // Try matching by data-cookie-id first (numeric), then by value (text slug)
            let matchOpt = $('#m_branch_select option').filter(function() {
                return $(this).data('cookie-id') == lookupId;
            });
            if (!matchOpt.length) {
                matchOpt = $('#m_branch_select option').filter(function() {
                    return $(this).val() == lookupId;
                });
            }

            if (matchOpt.length) {
                $('#m_branch_select').val(matchOpt.val());
            } else {
                $('#m_branch_select').val($('#m_branch_select option:first').val());
            }
            $('#m_branch_select').trigger('change');
        })();

        // ══════════════════════════════════════════
        // EDIT ADDRESS
        // ══════════════════════════════════════════
        $('#btn-edit-address').on('click', function() {
            $('#edit-address-container').slideToggle(200, function() {
                if ($(this).is(':visible')) {
                    initAutocomplete();
                    $('#edit_address_input').focus();
                }
            });
        });

        // Use My Location (from Doc1)
        $('#get-location-btn').click(function() {
            if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
            $(this).prop('disabled', true).text('Getting location...');
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                $('#h_address_lat').val(lat);
                $('#h_address_lng').val(lng);
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({location: {lat, lng}}, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        $('#edit_address_input').val(results[0].formatted_address);
                        $('#val-address').text(results[0].formatted_address);
                        $('#h_address').val(results[0].formatted_address);
                        selectedPlace = results[0];
                        const deliveryType = $('#h_delivery_type').val();
                        const isValid = validateDeliveryArea(results[0], deliveryType);
                        if (isValid) {
                            if (deliveryType === 'delivery') calculateDistance(lat, lng);
                        }
                    }
                    $('#get-location-btn').prop('disabled', false).text('📍 Use My Location');
                });
            }, function() {
                alert('Could not get your location.');
                $('#get-location-btn').prop('disabled', false).text('📍 Use My Location');
            });
        });

        // ══════════════════════════════════════════
        // FLATPICKR DATE
        // ══════════════════════════════════════════
        const fp = flatpickr('#sm-date-hidden', {
            minDate: 'today', dateFormat: 'Y-m-d', static: false,
            positionElement: document.getElementById('display_date'),
            appendTo: document.body, disableMobile: true,
            onChange: (dates, str) => {
                const todayStr = new Date().toISOString().split('T')[0];
                const lbl = str === todayStr ? 'Today' : str;
                $('#date-label-text').text(lbl);
                $('#h_delivery_date').val(str);
                fp.close();
            }
        });
        $('#display_date').on('click', function(e) { e.stopPropagation(); fp.isOpen ? fp.close() : fp.open(); });
        $(document).on('click', function(e) { if (!$(e.target).closest('#display_date, .flatpickr-calendar').length) fp.close(); });

        // ══════════════════════════════════════════
        // TIME POPUP (with branch hours filtering)
        // ══════════════════════════════════════════
        function buildTimeSlots() {
            const opt = $('#m_branch_select option:selected');
            const startTime = opt.data('start-time') || '07:00';
            const endTime   = opt.data('end-time') || '23:00';
            const [sh, sm] = startTime.split(':').map(Number);
            const [eh, em] = endTime.split(':').map(Number);

            let html = '<div class="m-time-popup" id="time-popup">';
            html += '<div class="m-time-item active" data-val="ASAP">ASAP</div>';

            let h = sh, m2 = sm;
            while (h < eh || (h === eh && m2 <= em)) {
                const ampm = h >= 12 ? 'PM' : 'AM';
                const hh = h % 12 || 12;
                const ts24 = (h < 10 ? '0' : '') + h + ':' + (m2 < 10 ? '0' : '') + m2;
                const ts12 = hh + ':' + (m2 < 10 ? '0' : '') + m2 + ' ' + ampm;
                html += '<div class="m-time-item" data-val="' + ts24 + '">' + ts12 + '</div>';
                m2 += 15;
                if (m2 >= 60) { m2 = 0; h++; }
            }
            html += '</div>';
            return $(html);
        }

        $('#display_time').on('click', function(e) {
            e.stopPropagation();
            $('#time-popup').remove();
            const box = buildTimeSlots();
            $(this).closest('.m-time-popup-wrap').append(box);
            box.find('.m-time-item').on('click', function() {
                const v = $(this).data('val');
                const label = v === 'ASAP' ? 'ASAP' : $(this).text();
                $('#time-label-text').text(label);
                $('#h_delivery_time').val(v);
                box.remove();
            });
        });
        $(document).on('click', function(e) { if (!$(e.target).closest('#display_time, .m-time-popup').length) $('#time-popup').remove(); });

        // ══════════════════════════════════════════
        // UPLOAD RECEIPT
        // ══════════════════════════════════════════
        const $uploadArea = $('#upload-area');
        const $fileInput  = $('#payment-proof-file');
        const $preview    = $('#upload-preview');
        const $thumb      = $('#upload-thumb');
        const $fname      = $('#upload-file-name');

        $uploadArea.on('click', function() { $fileInput.click(); });
        $fileInput.on('change', function() {
            const file = this.files[0];
            if (!file) return;
            const allowed = ['image/png', 'image/jpeg', 'application/pdf'];
            if (!allowed.includes(file.type)) { alert('Invalid file type. Only PNG, JPG, PDF.'); return; }
            if (file.size > 5 * 1024 * 1024) { alert('File too large. Max 5MB.'); return; }
            selectedFile = file;
            $fname.text(file.name);
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => $thumb.attr('src', e.target.result);
                reader.readAsDataURL(file);
            } else {
                $thumb.attr('src', '');
            }
            $preview.addClass('visible');
            $uploadArea.removeClass('has-error');
        });
        $('#btn-clear-upload').on('click', function(e) {
            e.stopPropagation();
            selectedFile = null; $fileInput.val(''); $preview.removeClass('visible'); $thumb.attr('src', '');
        });
        $uploadArea.on('dragover', e => { e.preventDefault(); $uploadArea.addClass('drag-over'); });
        $uploadArea.on('dragleave', () => $uploadArea.removeClass('drag-over'));
        $uploadArea.on('drop', function(e) {
            e.preventDefault(); $uploadArea.removeClass('drag-over');
            if (e.originalEvent.dataTransfer.files[0]) {
                $fileInput[0].files = e.originalEvent.dataTransfer.files;
                $fileInput.trigger('change');
            }
        });

        // ══════════════════════════════════════════
        // COPY ACCOUNT NUMBER
        // ══════════════════════════════════════════
        window.copyAccNum = function(num, btn) {
            navigator.clipboard.writeText(num).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = 'Copied! ✓';
                setTimeout(() => btn.innerHTML = orig, 2000);
            });
        };

        // ══════════════════════════════════════════
        // COUPONS — FULL SYSTEM FROM DOC1
        // ══════════════════════════════════════════

        // Toggle available coupons list
        $('#toggle-avail-coupons').on('click', function() {
            $('#available-coupons-list').toggleClass('hidden');
            $('#coupon-toggle-icon').toggleClass('open');
        });

        // Load available coupons
        function loadAvailableCoupons() {
            $.post(AJAX_URL, {
                action: 'get_available_coupons',
                cart_total: SUBTOTAL
            }, function(response) {
                if (response.success && response.data.coupons) {
                    availableCouponsData = response.data.coupons;
                    renderAvailableCoupons();
                } else {
                    $('#available-coupons-list').html('<div style="color:#aaa;font-size:13px;padding:4px 0;">No coupons available</div>');
                }
            }).fail(function() {
                $('#available-coupons-list').html('<div style="color:#aaa;font-size:13px;padding:4px 0;">Error loading coupons</div>');
            });
        }

        function renderAvailableCoupons() {
            const $list = $('#available-coupons-list');
            if (!availableCouponsData.length) {
                $list.html('<div style="color:#aaa;font-size:13px;padding:4px 0;">No coupons available</div>');
                return;
            }
            let html = '';
            availableCouponsData.forEach(function(c) {
                const isApplied  = appliedCoupons.some(a => a.code === c.code.toUpperCase());
                const isDisabled = c.minimum_amount > SUBTOTAL;
                let cls = 'm-coupon-card';
                if (isApplied) cls += ' applied';
                if (isDisabled) cls += ' disabled';

                let btnText = isApplied ? 'Applied ✓' : 'Apply';
                let conditions = '';
                if (isDisabled) {
                    conditions = '<div class="m-coupon-card-conditions">Min order: ' + fmtMoney(c.minimum_amount) + '</div>';
                }

                html += '<div class="' + cls + '" onclick="applyCouponFromCard(\'' + c.code + '\')">' +
                    '<div class="m-coupon-card-info">' +
                        '<div class="m-coupon-card-code">' + c.code + '</div>' +
                        '<div class="m-coupon-card-desc">' + c.description + '</div>' +
                        '<div class="m-coupon-card-details">' + c.details + '</div>' +
                        conditions +
                    '</div>' +
                    '<div class="m-coupon-card-discount">' + c.discount_text + '</div>' +
                    '<button type="button" class="m-coupon-card-btn" ' +
                        (isDisabled || isApplied ? 'disabled' : '') +
                        ' onclick="event.stopPropagation(); applyCouponFromCard(\'' + c.code + '\')">' +
                        btnText + '</button>' +
                '</div>';
            });
            $list.html(html);
        }

        window.applyCouponFromCard = function(code) {
            code = code.toUpperCase();
            if (appliedCoupons.some(c => c.code === code)) return;
            const cData = availableCouponsData.find(c => c.code.toUpperCase() === code);
            if (cData && cData.minimum_amount > SUBTOTAL) {
                showCouponMessage('Min order ' + fmtMoney(cData.minimum_amount) + ' required', 'error');
                return;
            }
            $('#coupon-input').val(code);
            $('#btn-apply-coupon').trigger('click');
        };

        // Apply coupon
        $('#btn-apply-coupon').on('click', function() {
            const code = $('#coupon-input').val().trim().toUpperCase();
            if (!code) { showCouponMessage('Please enter a coupon code', 'error'); return; }
            if (appliedCoupons.some(c => c.code === code)) {
                showCouponMessage('This coupon is already applied', 'error'); return;
            }

            // Check individual_use constraints
            const hasIndividualUse = appliedCoupons.some(c => c.individual_use === true);
            if (hasIndividualUse) {
                showCouponMessage('Cannot combine with current coupon', 'error'); return;
            }

            $(this).prop('disabled', true).text('Checking...');
            $.post(AJAX_URL, {
                action: 'validate_and_apply_coupon',
                coupon_code: code,
                cart_total: SUBTOTAL
            }, function(response) {
                $('#btn-apply-coupon').prop('disabled', false).text('Apply');
                if (response.success) {
                    const discAmt = parseFloat(response.data.discount_amount) || 0;
                    const isIndividual = response.data.individual_use === true;

                    if (isIndividual && appliedCoupons.length > 0) {
                        showCouponMessage('This coupon cannot be used with other coupons', 'error');
                        return;
                    }

                    appliedCoupons.push({
                        code: code,
                        discount: discAmt,
                        type: response.data.discount_type || 'percent',
                        description: response.data.description || '',
                        individual_use: isIndividual
                    });

                    updateAppliedCouponsUI();
                    updateTotalWithCoupons();
                    $('#coupon-input').val('');
                    showCouponMessage('Coupon applied! You saved ' + fmtMoney(discAmt), 'success');
                } else {
                    showCouponMessage(response.data?.message || 'Invalid coupon', 'error');
                }
            }).fail(function() {
                $('#btn-apply-coupon').prop('disabled', false).text('Apply');
                showCouponMessage('Error. Please try again.', 'error');
            });
        });

        // Enter key on coupon input
        $('#coupon-input').keypress(function(e) {
            if (e.which === 13) { e.preventDefault(); $('#btn-apply-coupon').click(); }
        });

        function showCouponMessage(msg, type) {
            const html = '<div class="m-coupon-message ' + type + '">' + msg + '</div>';
            $('#coupon-message-area').html(html);
            setTimeout(() => $('#coupon-message-area').empty(), 5000);
        }

        function updateAppliedCouponsUI() {
            const $area = $('#applied-coupons-area');
            if (!appliedCoupons.length) { $area.empty(); renderAvailableCoupons(); return; }

            let html = '<div style="margin-bottom:8px; font-weight:600; font-size:13px; color:#444;">Applied Coupons:</div>';
            appliedCoupons.forEach(c => {
                html += '<div class="m-coupon-tag">' +
                    '<span class="m-coupon-tag-code">' + c.code + '</span>' +
                    '<span class="m-coupon-tag-discount">-' + fmtMoney(c.discount) + '</span>' +
                    '<button type="button" class="m-coupon-tag-remove" onclick="removeCoupon(\'' + c.code + '\')">✕</button>' +
                '</div>';
            });
            $area.html(html);
            renderAvailableCoupons();
        }

        window.removeCoupon = function(code) {
            appliedCoupons = appliedCoupons.filter(c => c.code !== code);
            updateAppliedCouponsUI();
            updateTotalWithCoupons();
            showCouponMessage('Coupon removed', 'success');
        };

        function updateTotalWithCoupons() {
            totalDiscount = appliedCoupons.reduce((sum, c) => sum + parseFloat(c.discount), 0);
            updateTotals();
        }

        loadAvailableCoupons();

        // ══════════════════════════════════════════
        // E-RECEIPT / VAT SIDEBAR
        // ══════════════════════════════════════════
        function openSidebar()  { $('#receipt-sidebar, #sidebar-overlay').addClass('active'); $('body').css('overflow','hidden'); }
        function closeSidebar() { $('#receipt-sidebar, #sidebar-overlay').removeClass('active'); $('body').css('overflow',''); }

        $('#btn-open-receipt').on('click', openSidebar);
        $('#btn-close-receipt, #sidebar-overlay').on('click', closeSidebar);

        $('.m-rs-tab').on('click', function() {
            receiptTab = $(this).data('tab');
            $('.m-rs-tab').removeClass('active');
            $(this).addClass('active');
            $('#rs-tab-individual, #rs-tab-business').hide();
            $('#rs-tab-' + receiptTab).show();
        });

        $('#btn-save-receipt').on('click', function() {
            const isInd = (receiptTab === 'individual');
            const name  = isInd ? $('#rs_ind_name').val() : $('#rs_biz_name').val();
            const tin   = isInd ? $('#rs_ind_tin').val()  : $('#rs_biz_tin').val();
            const addr  = isInd ? $('#rs_ind_address').val() : $('#rs_biz_address').val();
            const email = isInd ? $('#rs_ind_email').val() : $('#rs_biz_email').val();

            if (!name) { alert('Please enter a name.'); return; }
            if (!isInd && !tin) { alert('TIN is required for business invoices.'); return; }

            $('#h_receipt_type').val(receiptTab);
            $('#h_receipt_name').val(name);
            $('#h_receipt_tin').val(tin);
            $('#h_receipt_address').val(addr);
            $('#h_receipt_email').val(email);
            $('#ereceipt-badge').show();
            closeSidebar();
        });

        // ══════════════════════════════════════════
        // INIT: Read address & order type from cookies
        // (Branch is already locked above from cookie)
        // ══════════════════════════════════════════
        function getParam(key) { return new URLSearchParams(window.location.search).get(key); }
        function getLs(key)    { try { return localStorage.getItem(key); } catch(e) { return null; } }
        function pick(...vals) { return vals.find(v => v != null && String(v).trim() !== '' && String(v).trim() !== 'null'); }

        // Delivery type from cookie (editable)
        const initType = pick(getParam('type'), getLs('somot_order_type'), getCookie('somot_order_type'), 'delivery');
        if (initType === 'pickup') {
            $('#tab-pickup').trigger('click');
        }

        // Address from cookie (editable)
        const initAddr = pick(decodeURIComponent(getParam('address') || ''), getLs('somot_customer_address'), getCookie('somot_customer_address'));
        const initLat  = parseFloat(pick(getParam('lat'), getLs('somot_address_lat'), getCookie('somot_address_lat')) || 0);
        const initLng  = parseFloat(pick(getParam('lng'), getLs('somot_address_lng'), getCookie('somot_address_lng')) || 0);

        if (initAddr) {
            $('#val-address').text(initAddr);
            $('#h_address').val(initAddr);
        }
        if (initLat && initLng) {
            $('#h_address_lat').val(initLat);
            $('#h_address_lng').val(initLng);
            if (initType !== 'pickup') {
                calculateDistance(initLat, initLng);
            }
        }

        updateTotals();

        // ══════════════════════════════════════════
        // AUTO-APPLY COUPON FROM URL (from Doc1)
        // ══════════════════════════════════════════
        const autoCoupon = getParam('apply_coupon');
        if (autoCoupon) {
            $(window).on('load', function() {
                setTimeout(function() {
                    $('#coupon-input').val(autoCoupon);
                    $('#btn-apply-coupon').trigger('click');
                    const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                    window.history.replaceState({}, '', newUrl);
                }, 1500);
            });
        }

        // ══════════════════════════════════════════
        // PLACE ORDER
        // ══════════════════════════════════════════
        $('#m-place-order-btn').on('click', function() {
            // Clear errors
            $('.has-error').removeClass('has-error');
            let ok = true;

            const delType = $('#h_delivery_type').val();

            // Validate delivery address
            if (delType === 'delivery') {
                if (!$('#h_address').val()) {
                    $('#val-address').addClass('has-error'); ok = false;
                }
                // Re-validate delivery area
                if (selectedPlace) {
                    if (!validateDeliveryArea(selectedPlace, 'delivery')) {
                        $('html,body').animate({ scrollTop: $('#delivery-area-warning').offset().top - 130 }, 400);
                        return;
                    }
                } else if ($('#h_address').val()) {
                    const addr = $('#h_address').val().toUpperCase();
                    const isInMetro = ALLOWED_CITIES.some(c => addr.includes(c));
                    if (!isInMetro) {
                        alert('Sorry, we only deliver to Metro Manila cities listed.');
                        $('#delivery-area-warning').removeClass('hidden');
                        return;
                    }
                }
            }

            if (!$('#m_first_name').val().trim()) { $('#m_first_name').addClass('has-error'); ok = false; }
            if (!$('#m_last_name').val().trim())  { $('#m_last_name').addClass('has-error'); ok = false; }
            if (!$('#m_phone').val().trim())      { $('#m_phone').addClass('has-error'); ok = false; }
            if (!$('#m_email').val().trim())      { $('#m_email').addClass('has-error'); ok = false; }
            if (!selectedFile) { $('#upload-area').addClass('has-error'); ok = false; }

            if (!ok) {
                $('html,body').animate({ scrollTop: $('.has-error').first().offset().top - 130 }, 400);
                return;
            }

            if (!confirm('Have you completed the payment? Please make sure your payment has been sent before confirming.')) return;

            $(this).prop('disabled', true).html('Processing...');

            const fd = new FormData();
            fd.append('action', 'process_complete_checkout');
            fd.append('delivery_type', delType);
            fd.append('first_name', $('#m_first_name').val().trim());
            fd.append('last_name', $('#m_last_name').val().trim());
            fd.append('email', $('#m_email').val().trim());
            fd.append('phone', $('#m_phone').val().trim());
            fd.append('address', $('#h_address').val());
            fd.append('address_lat', $('#h_address_lat').val());
            fd.append('address_lng', $('#h_address_lng').val());
            fd.append('apartment', $('#m_apartment').val());
            fd.append('city', $('#m_city').val());
            fd.append('state', 'Metro Manila');
            fd.append('postcode', $('#m_postcode').val());
            fd.append('country', 'Philippines');
            fd.append('branch', $('#h_branch').val());
            fd.append('delivery_date', $('#h_delivery_date').val());
            fd.append('delivery_time', $('#h_delivery_time').val());
            fd.append('shipping_fee', calculatedShippingFee);
            fd.append('order_notes', $('#m_order_notes').val());
            fd.append('applied_coupons', JSON.stringify(appliedCoupons));
            fd.append('payment_file', selectedFile);

            // VAT / E-receipt
            fd.append('need_vat', $('#h_receipt_type').val() ? 'yes' : 'no');
            fd.append('company', $('#h_receipt_name').val());
            fd.append('company_address', $('#h_receipt_address').val());
            fd.append('tax_code', $('#h_receipt_tin').val());
            fd.append('receipt_type', $('#h_receipt_type').val());
            fd.append('receipt_name', $('#h_receipt_name').val());
            fd.append('receipt_tin', $('#h_receipt_tin').val());
            fd.append('receipt_address', $('#h_receipt_address').val());

            $.ajax({
                url: AJAX_URL, type: 'POST', data: fd,
                processData: false, contentType: false,
                success: function(res) {
                    if (res.success) {
                        window.location.href = res.data.redirect;
                    } else {
                        alert(res.data.message || 'Error processing order.');
                        resetPlaceOrderBtn();
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                    resetPlaceOrderBtn();
                }
            });
        });

        function resetPlaceOrderBtn() {
            const total = Math.max(0, SUBTOTAL + calculatedShippingFee - totalDiscount);
            $('#m-place-order-btn').prop('disabled', false).html('Proceed to Payment • <span id="btn-total-display">' + fmtMoney(total) + '</span>');
        }

    })(jQuery);
    </script>
    <?php
}

// ════════════════════════════════════════════════════════════════
// AJAX: Process Complete Checkout (FULL from Doc1 — unchanged)
// ════════════════════════════════════════════════════════════════
add_action('wp_ajax_process_complete_checkout', 'process_complete_checkout');
add_action('wp_ajax_nopriv_process_complete_checkout', 'process_complete_checkout');
function process_complete_checkout() {
    try {
        $order = wc_create_order();

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['data']->get_id();
            $quantity   = $cart_item['quantity'];
            $item_id    = $order->add_product($cart_item['data'], $quantity);
            $order_item = $order->get_item($item_id);
            $item_calc  = calculate_cart_item_real_total($cart_item);

            // Save custom_addons meta
            if (!empty($cart_item['custom_addons'])) {
                foreach ($cart_item['custom_addons'] as $addon) {
                    $addon_label = isset($addon['optionLabel']) ? $addon['optionLabel'] : (isset($addon['label']) ? $addon['label'] : '');
                    $addon_group = isset($addon['group']) ? $addon['group'] : 'Add-on';
                    $addon_price = isset($addon['price']) ? floatval($addon['price']) : 0;
                    $addon_qty   = isset($addon['qty']) ? intval($addon['qty']) : 1;
                    if ($addon_label) {
                        wc_add_order_item_meta($item_id, $addon_group, $addon_label);
                        if ($addon_price > 0) wc_add_order_item_meta($item_id, '_addon_' . sanitize_title($addon_label) . '_price', $addon_price);
                        if ($addon_qty > 1)   wc_add_order_item_meta($item_id, '_addon_' . sanitize_title($addon_label) . '_qty', $addon_qty);
                    }
                }
            }

            // Save prad_selection meta
            if (!empty($cart_item['prad_selection']['extra_data'])) {
                foreach ($cart_item['prad_selection']['extra_data'] as $addon_data) {
                    if (isset($addon_data['name'], $addon_data['prad_additional']['field_raw'])) {
                        $field_raw = $addon_data['prad_additional']['field_raw'];
                        $values = isset($field_raw['value']) ? $field_raw['value'] : array();
                        $costs  = isset($field_raw['cost']) ? $field_raw['cost'] : array();
                        if (!empty($values) && is_array($values)) {
                            foreach ($values as $index => $value_name) {
                                $cost = isset($costs[$index]) ? floatval($costs[$index]) : 0;
                                wc_add_order_item_meta($item_id, $addon_data['name'], $value_name);
                                if ($cost > 0) wc_add_order_item_meta($item_id, '_addon_' . sanitize_title($value_name) . '_price', $cost);
                            }
                        }
                    }
                }
            }

            // Override line item prices with correct calculation
            if ($order_item) {
                $correct_subtotal = $item_calc['product_price'] * $quantity;
                $correct_total    = $item_calc['line_total'];
                $order_item->set_subtotal($correct_subtotal);
                $order_item->set_total($correct_total);
                $order_item->save();
            }
        }

        // Set billing details
        $order->set_billing_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_billing_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_billing_email(sanitize_email($_POST['email']));
        $order->set_billing_phone(sanitize_text_field($_POST['phone']));
        $order->set_billing_address_1(sanitize_text_field($_POST['address']));
        $order->set_billing_address_2(sanitize_text_field($_POST['apartment'] ?? ''));
        $order->set_billing_city(sanitize_text_field($_POST['city']));
        $order->set_billing_state(sanitize_text_field($_POST['state']));
        $order->set_billing_postcode(sanitize_text_field($_POST['postcode']));
        $order->set_billing_country(sanitize_text_field($_POST['country']));
        $order->set_billing_company(sanitize_text_field($_POST['company'] ?? ''));

        // Set shipping details
        $order->set_shipping_first_name(sanitize_text_field($_POST['first_name']));
        $order->set_shipping_last_name(sanitize_text_field($_POST['last_name']));
        $order->set_shipping_address_1(sanitize_text_field($_POST['address']));
        $order->set_shipping_address_2(sanitize_text_field($_POST['apartment'] ?? ''));
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

        // Save order first
        $order->save();
        $order = wc_get_order($order->get_id());

        // Calculate totals manually (avoids resetting addon prices)
        $items_subtotal = 0;
        foreach ($order->get_items() as $item) {
            $items_subtotal += floatval($item->get_total());
        }
        $shipping_total = 0;
        foreach ($order->get_items('shipping') as $item) {
            $shipping_total += floatval($item->get_total());
        }

        $order->set_shipping_total($shipping_total);
        $order->set_discount_total(0);
        $order->set_total($items_subtotal + $shipping_total);
        $order->save();

        // Apply coupons
        if (!empty($_POST['applied_coupons'])) {
            $coupons = json_decode(stripslashes($_POST['applied_coupons']), true);
            foreach ((array)$coupons as $coupon_data) {
                $coupon_code = sanitize_text_field($coupon_data['code']);
                $coupon = new WC_Coupon($coupon_code);
                if ($coupon->is_valid()) {
                    $coupon_item = new WC_Order_Item_Coupon();
                    $coupon_item->set_props(array(
                        'code'         => $coupon_code,
                        'discount'     => $coupon_data['discount'],
                        'discount_tax' => 0,
                    ));
                    $order->add_item($coupon_item);
                    $coupon->increase_usage_count();
                    $order->add_order_note(sprintf('Coupon "%s" applied. Discount: ₱%s', $coupon_code, number_format($coupon_data['discount'], 2)));
                }
            }
        }

        // Set custom meta
        $order->update_meta_data('_delivery_type', sanitize_text_field($_POST['delivery_type']));
        $order->update_meta_data('_delivery_date', sanitize_text_field($_POST['delivery_date']));
        $order->update_meta_data('_delivery_time', sanitize_text_field($_POST['delivery_time']));
        $order->update_meta_data('_order_notes', sanitize_textarea_field($_POST['order_notes'] ?? ''));
        $order->update_meta_data('_selected_branch', sanitize_text_field($_POST['branch']));

        if ($_POST['delivery_type'] === 'delivery') {
            $order->update_meta_data('_delivery_latitude', sanitize_text_field($_POST['address_lat'] ?? ''));
            $order->update_meta_data('_delivery_longitude', sanitize_text_field($_POST['address_lng'] ?? ''));
        }

        // E-receipt meta
        if (!empty($_POST['receipt_type'])) {
            $order->update_meta_data('_ereceipt_type', sanitize_text_field($_POST['receipt_type']));
            $order->update_meta_data('_ereceipt_name', sanitize_text_field($_POST['receipt_name']));
            $order->update_meta_data('_ereceipt_tin', sanitize_text_field($_POST['receipt_tin']));
            $order->update_meta_data('_ereceipt_address', sanitize_textarea_field($_POST['receipt_address']));
        }

        // VAT info
        if (($_POST['need_vat'] ?? '') === 'yes') {
            $order->update_meta_data('_need_vat_invoice', 'yes');
            $order->update_meta_data('_vat_company_name', sanitize_text_field($_POST['company'] ?? ''));
            $order->update_meta_data('_vat_company_address', sanitize_text_field($_POST['company_address'] ?? ''));
            $order->update_meta_data('_vat_tax_code', sanitize_text_field($_POST['tax_code'] ?? ''));
        }

        // Set payment method
        $order->set_payment_method('bacs');
        $order->set_payment_method_title('Online Bank Transfer');

        // Handle payment proof upload
        if (isset($_FILES['payment_file']) && !empty($_FILES['payment_file']['tmp_name'])) {
            $file       = $_FILES['payment_file'];
            $upload_dir = wp_upload_dir();
            $payment_dir = $upload_dir['basedir'] . '/payment-proofs';
            if (!file_exists($payment_dir)) mkdir($payment_dir, 0755, true);
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'order_pending_' . time() . '.' . $file_ext;
            $filepath = $payment_dir . '/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $file_url = $upload_dir['baseurl'] . '/payment-proofs/' . $filename;
                $order->update_meta_data('_payment_proof_file', $file_url);
                $order->add_order_note('Payment proof uploaded: ' . $filename);
            }
        }

        // Final total recalc after coupons
        $order->save();
        $order = wc_get_order($order->get_id());

        $items_subtotal = 0;
        foreach ($order->get_items() as $item) {
            $items_subtotal += floatval($item->get_total());
        }
        $shipping_total = 0;
        foreach ($order->get_items('shipping') as $item) {
            $shipping_total += floatval($item->get_total());
        }
        $discount_total = 0;
        foreach ($order->get_items('coupon') as $item) {
            $discount_total += floatval($item->get_discount());
        }

        $order->set_shipping_total($shipping_total);
        $order->set_discount_total($discount_total);
        $order->set_total($items_subtotal + $shipping_total - $discount_total);
        $order->save();

        // Create customer account or associate (FULL from Doc1 with email)
        $email      = sanitize_email($_POST['email']);
        $phone      = sanitize_text_field($_POST['phone']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name  = sanitize_text_field($_POST['last_name']);

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $order->set_customer_id($user_id);
            $order->save();
        } elseif (!email_exists($email) && !username_exists($email)) {
            $random_password = wp_generate_password(12, false);
            $user_id = wp_create_user($email, $random_password, $email);

            if (!is_wp_error($user_id)) {
                wp_update_user(array(
                    'ID'           => $user_id,
                    'first_name'   => $first_name,
                    'last_name'    => $last_name,
                    'display_name' => $first_name . ' ' . $last_name,
                    'role'         => 'customer'
                ));

                update_user_meta($user_id, 'billing_phone', $phone);
                update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['address']));
                update_user_meta($user_id, 'billing_address_2', sanitize_text_field($_POST['apartment'] ?? ''));
                update_user_meta($user_id, 'billing_city', sanitize_text_field($_POST['city']));
                update_user_meta($user_id, 'billing_state', sanitize_text_field($_POST['state']));
                update_user_meta($user_id, 'billing_postcode', sanitize_text_field($_POST['postcode']));
                update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['country']));

                // Send account creation email (from Doc1)
                wp_mail(
                    $email,
                    'Your Account Has Been Created - Somot',
                    "Hello $first_name,\n\nYour account has been created successfully!\n\nEmail: $email\nPassword: $random_password\n\nYou can login at: " . wp_login_url() . "\n\nThank you for shopping with us!",
                    array('Content-Type: text/plain; charset=UTF-8')
                );

                $order->set_customer_id($user_id);
                $order->save();
            }
        } else {
            $existing_user = get_user_by('email', $email);
            if ($existing_user) {
                $order->set_customer_id($existing_user->ID);
                $order->save();
            }
        }

        WC()->cart->empty_cart();

        wp_send_json_success(array(
            'redirect' => home_url('/thank-you-page/?order_id=' . $order->get_id() . '&key=' . $order->get_order_key())
        ));

    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}

// ════════════════════════════════════════════════════════════════
// AJAX: Validate and Apply Coupon (FULL from Doc1 — with Smart Coupons Pro)
// ════════════════════════════════════════════════════════════════
add_action('wp_ajax_validate_and_apply_coupon', 'validate_and_apply_coupon');
add_action('wp_ajax_nopriv_validate_and_apply_coupon', 'validate_and_apply_coupon');
function validate_and_apply_coupon() {
    $coupon_code = strtoupper(sanitize_text_field($_POST['coupon_code']));
    $cart_total  = floatval($_POST['cart_total']);

    if (empty($coupon_code)) { wp_send_json_error(array('message' => 'Please enter a coupon code')); }

    $coupon = new WC_Coupon($coupon_code);
    if (!$coupon->get_id()) { wp_send_json_error(array('message' => 'Invalid coupon code')); }
    if (!$coupon->is_valid()) { wp_send_json_error(array('message' => 'This coupon is not valid')); }

    // Check expiry
    $expiry_date = $coupon->get_date_expires();
    if ($expiry_date && $expiry_date->getTimestamp() < time()) {
        wp_send_json_error(array('message' => 'This coupon has expired'));
    }

    // Check usage limit
    $usage_limit = $coupon->get_usage_limit();
    $usage_count = $coupon->get_usage_count();
    if ($usage_limit > 0 && $usage_count >= $usage_limit) {
        wp_send_json_error(array('message' => 'This coupon has reached its usage limit'));
    }

    // Check usage limit per user
    $usage_limit_per_user = $coupon->get_usage_limit_per_user();
    if ($usage_limit_per_user > 0) {
        $user_id    = get_current_user_id();
        $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
        $used_by    = $coupon->get_used_by();
        $user_usage_count = 0;

        if ($user_id > 0) {
            $user_usage_count = count(array_filter($used_by, function($cid) use ($user_id) {
                return intval($cid) === $user_id;
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
                    WHERE order_item_name = %s AND order_item_type = 'coupon'
                )", $user_email, $coupon_code
            ));
            $user_usage_count = max($user_usage_count, intval($email_usage));
        }

        if ($user_usage_count >= $usage_limit_per_user) {
            wp_send_json_error(array('message' => 'You have reached the usage limit for this coupon'));
        }
    }

    // Check email restrictions
    $email_restrictions = $coupon->get_email_restrictions();
    if (!empty($email_restrictions)) {
        $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';
        if (empty($user_email)) {
            wp_send_json_error(array('message' => 'Please login to use this coupon'));
        }
        $is_email_valid = false;
        foreach ($email_restrictions as $restriction) {
            if (fnmatch($restriction, $user_email, FNM_CASEFOLD)) { $is_email_valid = true; break; }
        }
        if (!$is_email_valid) {
            wp_send_json_error(array('message' => 'This coupon is not valid for your email address'));
        }
    }

    // Get coupon restrictions
    $product_ids                 = $coupon->get_product_ids();
    $excluded_product_ids        = $coupon->get_excluded_product_ids();
    $product_categories          = $coupon->get_product_categories();
    $excluded_product_categories = $coupon->get_excluded_product_categories();
    $exclude_sale_items          = $coupon->get_exclude_sale_items();

    // Smart Coupons Pro settings
    $wt_sc_coupon_categories          = get_post_meta($coupon->get_id(), '_wt_sc_coupon_categories', true);
    $wt_enable_category_restriction   = get_post_meta($coupon->get_id(), '_wt_enable_product_category_restriction', true);
    $wt_use_individual_min_max        = get_post_meta($coupon->get_id(), '_wt_use_individual_min_max', true);
    $wt_min_matching_product_qty      = get_post_meta($coupon->get_id(), '_wt_min_matching_product_qty', true);
    $wt_max_matching_product_qty      = get_post_meta($coupon->get_id(), '_wt_max_matching_product_qty', true);

    // Filter eligible cart items
    $eligible_items    = array();
    $eligible_total    = 0;
    $eligible_quantity = 0;
    $category_quantities = array();

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product      = $cart_item['data'];
        $product_id   = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        $quantity     = $cart_item['quantity'];
        $item_calc    = calculate_cart_item_real_total($cart_item);
        $item_total   = $item_calc['line_total'];
        $is_eligible  = true;

        // Check excluded products
        if (!empty($excluded_product_ids)) {
            if (in_array($product_id, $excluded_product_ids) || ($variation_id && in_array($variation_id, $excluded_product_ids))) {
                $is_eligible = false;
            }
        }

        // Check required products
        if ($is_eligible && !empty($product_ids)) {
            if (!in_array($product_id, $product_ids) && !($variation_id && in_array($variation_id, $product_ids))) {
                $is_eligible = false;
            }
        }

        // Check categories
        if ($is_eligible) {
            $product_cats = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
            if (!empty($excluded_product_categories)) {
                foreach ($excluded_product_categories as $exc_cat) {
                    if (in_array($exc_cat, $product_cats)) { $is_eligible = false; break; }
                }
            }
            if ($is_eligible && !empty($product_categories)) {
                $has_required = false;
                foreach ($product_categories as $req_cat) {
                    if (in_array($req_cat, $product_cats)) {
                        $has_required = true;
                        if (!isset($category_quantities[$req_cat])) $category_quantities[$req_cat] = 0;
                        $category_quantities[$req_cat] += $quantity;
                        break;
                    }
                }
                if (!$has_required) $is_eligible = false;
            }
        }

        // Check sale items
        if ($is_eligible && $exclude_sale_items && $product->is_on_sale()) {
            $is_eligible = false;
        }

        if ($is_eligible) {
            $eligible_items[] = array(
                'cart_item_key' => $cart_item_key,
                'product_id'    => $product_id,
                'product_name'  => $product->get_name(),
                'quantity'      => $quantity,
                'item_total'    => $item_total
            );
            $eligible_total    += $item_total;
            $eligible_quantity += $quantity;
        }
    }

    if (empty($eligible_items)) {
        $error_msg = 'This coupon is not valid for any products in your cart.';
        if (!empty($product_categories)) {
            $cat_names = array();
            foreach ($product_categories as $cat_id) {
                $category = get_term($cat_id, 'product_cat');
                if ($category) $cat_names[] = $category->name;
            }
            if (!empty($cat_names)) $error_msg = 'This coupon only applies to: ' . implode(', ', $cat_names);
        }
        wp_send_json_error(array('message' => $error_msg));
    }

    // Check quantity restrictions — Individual per category
    if ($wt_enable_category_restriction === 'yes' && $wt_use_individual_min_max === 'yes' && !empty($wt_sc_coupon_categories) && is_array($wt_sc_coupon_categories)) {
        foreach ($wt_sc_coupon_categories as $cat_id => $qty_rules) {
            $min_qty     = !empty($qty_rules['min']) ? intval($qty_rules['min']) : 0;
            $max_qty     = !empty($qty_rules['max']) ? intval($qty_rules['max']) : 0;
            $current_qty = isset($category_quantities[$cat_id]) ? $category_quantities[$cat_id] : 0;
            $category    = get_term($cat_id, 'product_cat');
            $cat_name    = $category ? $category->name : 'required category';

            if ($min_qty > 0 && $current_qty < $min_qty) {
                wp_send_json_error(array('message' => sprintf('Need at least %d item(s) from "%s". Currently: %d', $min_qty, $cat_name, $current_qty)));
            }
            if ($max_qty > 0 && $current_qty > $max_qty) {
                wp_send_json_error(array('message' => sprintf('Maximum %d item(s) from "%s" allowed. Currently: %d', $max_qty, $cat_name, $current_qty)));
            }
        }
    } elseif ($wt_enable_category_restriction === 'yes' && $wt_use_individual_min_max === 'no') {
        $global_min = !empty($wt_min_matching_product_qty) ? intval($wt_min_matching_product_qty) : 0;
        $global_max = !empty($wt_max_matching_product_qty) ? intval($wt_max_matching_product_qty) : 0;
        if ($global_min > 0 && $eligible_quantity < $global_min) {
            wp_send_json_error(array('message' => sprintf('Need at least %d eligible item(s). Currently: %d', $global_min, $eligible_quantity)));
        }
        if ($global_max > 0 && $eligible_quantity > $global_max) {
            wp_send_json_error(array('message' => sprintf('Maximum %d eligible item(s) allowed. Currently: %d', $global_max, $eligible_quantity)));
        }
    }

    // Check minimum amount
    $minimum_amount = $coupon->get_minimum_amount();
    if ($minimum_amount > 0 && $eligible_total < $minimum_amount) {
        wp_send_json_error(array('message' => sprintf('Minimum order ₱%s required (Current: ₱%s)', number_format($minimum_amount, 2), number_format($eligible_total, 2))));
    }

    // Check maximum amount
    $maximum_amount = $coupon->get_maximum_amount();
    if ($maximum_amount > 0 && $eligible_total > $maximum_amount) {
        wp_send_json_error(array('message' => 'Maximum order ₱' . number_format($maximum_amount, 2) . ' exceeded'));
    }

    // Calculate discount
    $discount_type   = $coupon->get_discount_type();
    $coupon_amount   = $coupon->get_amount();
    $discount_amount = 0;

    if ($discount_type === 'fixed_cart') {
        $discount_amount = min($coupon_amount, $eligible_total);
    } elseif ($discount_type === 'fixed_product') {
        foreach ($eligible_items as $item) {
            $discount_amount += min($coupon_amount * $item['quantity'], $item['item_total']);
        }
    } elseif ($discount_type === 'percent') {
        $discount_amount = ($eligible_total * $coupon_amount) / 100;
        if ($maximum_amount > 0 && $discount_amount > $maximum_amount) $discount_amount = $maximum_amount;
    }

    $discount_amount = min($discount_amount, $eligible_total);

    // Format description
    $description = '';
    if ($discount_type === 'percent') {
        $description = $coupon_amount . '% off';
    } elseif ($discount_type === 'fixed_product') {
        $description = '₱' . number_format($coupon_amount, 2) . ' off per item';
    } else {
        $description = '₱' . number_format($coupon_amount, 2) . ' off';
    }

    $eligible_count = count($eligible_items);
    $total_items    = count(WC()->cart->get_cart());
    if ($eligible_count < $total_items) {
        $description .= ' (' . $eligible_quantity . ' eligible items)';
    }

    wp_send_json_success(array(
        'discount_amount'  => floatval($discount_amount),
        'discount_type'    => $discount_type,
        'description'      => $description,
        'code'             => $coupon_code,
        'individual_use'   => $coupon->get_individual_use(),
        'eligible_items'   => intval($eligible_count),
        'eligible_quantity' => intval($eligible_quantity),
        'total_items'      => intval($total_items),
        'eligible_total'   => floatval($eligible_total),
    ));
}

// ════════════════════════════════════════════════════════════════
// AJAX: Get Available Coupons (from Doc1)
// ════════════════════════════════════════════════════════════════
add_action('wp_ajax_get_available_coupons', 'get_available_coupons_ajax');
add_action('wp_ajax_nopriv_get_available_coupons', 'get_available_coupons_ajax');
function get_available_coupons_ajax() {
    $cart_total = isset($_POST['cart_total']) ? floatval($_POST['cart_total']) : 0;

    $args = array(
        'posts_per_page' => -1,
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $coupons_query    = new WP_Query($args);
    $available_coupons = array();

    if ($coupons_query->have_posts()) {
        while ($coupons_query->have_posts()) {
            $coupons_query->the_post();
            $coupon_id = get_the_ID();
            $coupon    = new WC_Coupon($coupon_id);

            if (!$coupon->is_valid()) continue;
            if ($coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit()) continue;

            $expiry_date = $coupon->get_date_expires();
            if ($expiry_date && $expiry_date->getTimestamp() < time()) continue;

            $hidden_coupons = array('WINSFOODTRIPS', 'DISCOUNT50', 'HAISELLER', 'HAISELLER2', 'HAISELLER3');
            if (in_array(strtoupper($coupon->get_code()), $hidden_coupons)) continue;

            $code           = $coupon->get_code();
            $discount_type  = $coupon->get_discount_type();
            $amount         = $coupon->get_amount();
            $minimum_amount = $coupon->get_minimum_amount();
            $maximum_amount = $coupon->get_maximum_amount();
            $description    = $coupon->get_description();

            $discount_text = '';
            if ($discount_type === 'percent') {
                $discount_text = $amount . '% OFF';
            } else {
                $discount_text = '₱' . number_format($amount, 0) . ' OFF';
            }

            $details_parts = array();
            if ($minimum_amount > 0) $details_parts[] = 'Min: ₱' . number_format($minimum_amount, 0);
            if ($maximum_amount > 0) $details_parts[] = 'Max discount: ₱' . number_format($maximum_amount, 0);
            if ($expiry_date) $details_parts[] = 'Expires: ' . $expiry_date->format('M d, Y');

            $usage_limit = $coupon->get_usage_limit();
            if ($usage_limit > 0) {
                $remaining = $usage_limit - $coupon->get_usage_count();
                $details_parts[] = $remaining . ' uses left';
            }

            $details = !empty($details_parts) ? implode(' • ', $details_parts) : 'No restrictions';

            if (empty($description)) {
                $description = $discount_type === 'percent'
                    ? 'Get ' . $amount . '% discount on your order'
                    : 'Get ₱' . number_format($amount, 2) . ' off your order';
            }

            $available_coupons[] = array(
                'code'           => $code,
                'discount_type'  => $discount_type,
                'amount'         => $amount,
                'discount_text'  => $discount_text,
                'description'    => $description,
                'details'        => $details,
                'minimum_amount' => $minimum_amount,
                'maximum_amount' => $maximum_amount,
                'expiry_date'    => $expiry_date ? $expiry_date->format('Y-m-d') : null
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array('coupons' => $available_coupons));
}

// ════════════════════════════════════════════════════════════════
// ADMIN: Display custom checkout info in order detail
// ════════════════════════════════════════════════════════════════
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_checkout_info_in_admin');
function display_custom_checkout_info_in_admin($order) {
    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch        = $order->get_meta('_selected_branch');
    $lat           = $order->get_meta('_delivery_latitude');
    $lng           = $order->get_meta('_delivery_longitude');
    $payment_proof = $order->get_meta('_payment_proof_file');
    $notes         = $order->get_meta('_order_notes');

    echo '<div style="padding:15px;background:#f0f9ff;margin-top:15px;border-radius:4px;">';
    echo '<h3 style="margin-top:0;">🚚 Delivery Information</h3>';
    if ($delivery_type) echo '<p><strong>Type:</strong> ' . esc_html(ucfirst($delivery_type)) . '</p>';
    if ($delivery_date) echo '<p><strong>Date:</strong> ' . esc_html($delivery_date) . '</p>';
    if ($delivery_time) echo '<p><strong>Time:</strong> ' . esc_html($delivery_time) . '</p>';
    if ($branch)        echo '<p><strong>Branch:</strong> ' . esc_html($branch) . '</p>';
    if ($notes)         echo '<p><strong>Order Notes:</strong> ' . esc_html($notes) . '</p>';
    if ($lat && $lng)   echo '<p><strong>Location:</strong> <a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" target="_blank">View on Google Maps</a></p>';
    if ($payment_proof) echo '<p><strong>Payment Proof:</strong> <a href="' . esc_url($payment_proof) . '" target="_blank">View File</a></p>';
    echo '</div>';

    // E-receipt info
    $er_type = $order->get_meta('_ereceipt_type');
    if ($er_type) {
        echo '<div style="padding:15px;background:#fff7ed;margin-top:15px;border-radius:4px;border-left:4px solid #f59e0b;">';
        echo '<h3 style="margin-top:0;">🧾 E-Invoice (' . ucfirst($er_type) . ')</h3>';
        echo '<p><strong>Name:</strong> ' . esc_html($order->get_meta('_ereceipt_name')) . '</p>';
        if ($order->get_meta('_ereceipt_tin'))     echo '<p><strong>TIN:</strong> ' . esc_html($order->get_meta('_ereceipt_tin')) . '</p>';
        if ($order->get_meta('_ereceipt_address')) echo '<p><strong>Address:</strong> ' . esc_html($order->get_meta('_ereceipt_address')) . '</p>';
        echo '</div>';
    }

    // VAT info (legacy)
    $need_vat = $order->get_meta('_need_vat_invoice');
    if ($need_vat === 'yes' && !$er_type) {
        $vat_company  = $order->get_meta('_vat_company_name');
        $vat_address  = $order->get_meta('_vat_company_address');
        $vat_tax_code = $order->get_meta('_vat_tax_code');
        echo '<div style="padding:15px;background:#fff7ed;margin-top:15px;border-radius:4px;border-left:4px solid #f59e0b;">';
        echo '<h3 style="margin-top:0;">🧾 VAT Invoice Required</h3>';
        if ($vat_company)  echo '<p><strong>Company:</strong> ' . esc_html($vat_company) . '</p>';
        if ($vat_address)  echo '<p><strong>Address:</strong> ' . esc_html($vat_address) . '</p>';
        if ($vat_tax_code) echo '<p><strong>Tax Code:</strong> ' . esc_html($vat_tax_code) . '</p>';
        echo '</div>';
    }
}

// ════════════════════════════════════════════════════════════════
// ADMIN: Coupon info in order
// ════════════════════════════════════════════════════════════════
add_action('woocommerce_admin_order_data_after_order_details', 'display_coupon_info_in_admin');
function display_coupon_info_in_admin($order) {
    $coupons = $order->get_coupon_codes();
    if (!empty($coupons)) {
        echo '<div style="padding:15px;background:#e8f5e9;margin-top:15px;border-radius:4px;border-left:4px solid #4caf50;">';
        echo '<h3 style="margin-top:0;">🎟️ Applied Coupons</h3>';
        foreach ($order->get_items('coupon') as $item_id => $item) {
            echo '<div style="padding:8px 12px;background:white;border-radius:4px;margin-bottom:8px;">';
            echo '<strong style="color:#2d5016;text-transform:uppercase;">' . esc_html($item->get_code()) . '</strong>';
            echo '<span style="float:right;color:#10b981;font-weight:600;">-₱' . number_format($item->get_discount(), 2) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
}

// ════════════════════════════════════════════════════════════════
// ADMIN: Order totals breakdown
// ════════════════════════════════════════════════════════════════
add_action('woocommerce_admin_order_totals_after_total', 'display_order_summary_breakdown');
function display_order_summary_breakdown($order_id) {
    $order          = wc_get_order($order_id);
    $shipping_total = $order->get_shipping_total();
    $discount_total = $order->get_discount_total();

    if ($shipping_total > 0 || $discount_total > 0) { ?>
        <tr><td colspan="2" style="padding-top:15px;border-top:2px solid #ddd;">
            <div style="background:#f7f7f7;padding:12px;border-radius:4px;">
                <?php if ($discount_total > 0): ?>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <strong>💰 Total Discount:</strong>
                    <span style="color:#10b981;font-weight:600;">-₱<?php echo number_format($discount_total, 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($shipping_total > 0): ?>
                <div style="display:flex;justify-content:space-between;">
                    <strong>🚚 Shipping Fee:</strong>
                    <span style="font-weight:600;">₱<?php echo number_format($shipping_total, 2); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </td></tr>
    <?php }
}

// ════════════════════════════════════════════════════════════════
// EMAIL: Delivery info in customer order emails
// ════════════════════════════════════════════════════════════════
add_action('woocommerce_email_before_order_table', 'add_custom_content_to_order_email', 20, 4);
function add_custom_content_to_order_email($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin) return;

    $delivery_type = $order->get_meta('_delivery_type');
    $delivery_date = $order->get_meta('_delivery_date');
    $delivery_time = $order->get_meta('_delivery_time');
    $branch        = $order->get_meta('_selected_branch');

    if ($plain_text) {
        echo "\n========================================\n";
        echo "DELIVERY INFORMATION\n";
        echo "========================================\n\n";
        if ($delivery_type) echo "Delivery Type: " . ucfirst($delivery_type) . "\n";
        if ($delivery_date) echo "Delivery Date: " . $delivery_date . "\n";
        if ($delivery_time) echo "Delivery Time: " . $delivery_time . "\n";
        if ($branch)        echo "Branch: " . ucfirst($branch) . "\n";
        echo "\n";
    } else { ?>
        <div style="margin-bottom:40px;padding:20px;background-color:#f7fafc;border-radius:8px;">
            <h2 style="color:#2d3748;margin-top:0;">🚚 Delivery Information</h2>
            <?php if ($delivery_type): ?><p><strong>Type:</strong> <?php echo esc_html(ucfirst($delivery_type)); ?></p><?php endif; ?>
            <?php if ($delivery_date): ?><p><strong>Date:</strong> <?php echo esc_html($delivery_date); ?></p><?php endif; ?>
            <?php if ($delivery_time): ?><p><strong>Time:</strong> <?php echo esc_html($delivery_time); ?></p><?php endif; ?>
            <?php if ($branch): ?><p><strong>Branch:</strong> <?php echo esc_html(ucfirst($branch)); ?></p><?php endif; ?>
        </div>
    <?php }
}
