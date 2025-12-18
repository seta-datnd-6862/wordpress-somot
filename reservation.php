<?php
/**
 * Somot Restaurant Reservation System - INLINE VERSION
 * 
 * This snippet replaces the Elementor form with a custom reservation system
 * that integrates with Goodriver Laravel backend.
 * All CSS and JavaScript are included inline - no separate files needed.
 * 
 * Installation:
 * 1. Add this code to your theme's functions.php or use Code Snippets plugin
 * 2. Update GOODRIVER_API_URL and GOODRIVER_API_KEY constants below
 * 3. Create a new page and add shortcode: [somot_reservation_form]
 * 4. Done!
 */

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================================================

define('GOODRIVER_API_URL', 'https://your-goodriver-domain.com/api');
define('GOODRIVER_API_KEY', 'your-secure-api-key-here'); // From .env WORDPRESS_API_KEY

// ============================================================================
// BRANCH CONFIGURATION (matches Goodriver branches)
// ============================================================================

function somot_get_branches() {
    return array(
        array('id' => 1, 'name' => 'Tayuman Branch, Manila', 'lat' => 14.6175959, 'lng' => 120.9837713, 'address' => '1960 Oroquieta Rd, Santa Cruz, Manila, 1008, Santa Cruz, Manila, 1014 Metro Manila, Philippines'),
        array('id' => 2, 'name' => 'Pioneer Branch, Pasig', 'lat' => 14.5731404, 'lng' => 121.0164509, 'address' => 'Pioneer Center, Pioneer St, Pasig, Metro Manila, Philippines'),
        array('id' => 3, 'name' => 'Unimart Branch, Capitol Commons, Pasig', 'lat' => 14.574848, 'lng' => 121.0618259, 'address' => 'Ground Floor, Unimart at Capitol Commons, Shaw Blvd, Pasig, Metro Manila, Philippines'),
    );
}

// ============================================================================
// REGISTER CUSTOM POST TYPE
// ============================================================================

function somot_register_reservation_post_type() {
    register_post_type('reservation', array(
        'labels' => array(
            'name' => 'Reservations',
            'singular_name' => 'Reservation',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Reservation',
            'edit_item' => 'Edit Reservation',
            'view_item' => 'View Reservation',
            'search_items' => 'Search Reservations',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title'),
        'has_archive' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap' => true,
    ));
}
add_action('init', 'somot_register_reservation_post_type');

// ============================================================================
// META BOXES
// ============================================================================

function somot_add_reservation_meta_boxes() {
    add_meta_box(
        'reservation_details',
        'Reservation Details',
        'somot_reservation_details_callback',
        'reservation',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'somot_add_reservation_meta_boxes');

function somot_reservation_details_callback($post) {
    $customer_name = get_post_meta($post->ID, '_customer_name', true);
    $customer_email = get_post_meta($post->ID, '_customer_email', true);
    $customer_phone = get_post_meta($post->ID, '_customer_phone', true);
    $number_of_guests = get_post_meta($post->ID, '_number_of_guests', true);
    $reservation_date = get_post_meta($post->ID, '_reservation_date', true);
    $reservation_time = get_post_meta($post->ID, '_reservation_time', true);
    $branch_id = get_post_meta($post->ID, '_branch_id', true);
    $branch_name = get_post_meta($post->ID, '_branch_name', true);
    $additional_notes = get_post_meta($post->ID, '_additional_notes', true);
    $goodriver_id = get_post_meta($post->ID, '_goodriver_id', true);
    $order_key = get_post_meta($post->ID, '_order_key', true);
    $status = get_post_meta($post->ID, '_status', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th>Order Key:</th>
            <td><strong><?php echo esc_html($order_key); ?></strong></td>
        </tr>
        <tr>
            <th>Goodriver ID:</th>
            <td><?php echo esc_html($goodriver_id); ?></td>
        </tr>
        <tr>
            <th>Status:</th>
            <td><?php echo esc_html($status ?: 'Waiting'); ?></td>
        </tr>
        <tr>
            <th>Customer Name:</th>
            <td><?php echo esc_html($customer_name); ?></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?php echo esc_html($customer_email); ?></td>
        </tr>
        <tr>
            <th>Phone:</th>
            <td><?php echo esc_html($customer_phone); ?></td>
        </tr>
        <tr>
            <th>Number of Guests:</th>
            <td><?php echo esc_html($number_of_guests); ?></td>
        </tr>
        <tr>
            <th>Reservation Date:</th>
            <td><?php echo esc_html($reservation_date); ?></td>
        </tr>
        <tr>
            <th>Reservation Time:</th>
            <td><?php echo esc_html($reservation_time); ?></td>
        </tr>
        <tr>
            <th>Branch:</th>
            <td><?php echo esc_html($branch_name); ?></td>
        </tr>
        <?php if ($additional_notes): ?>
        <tr>
            <th>Additional Notes:</th>
            <td><?php echo esc_html($additional_notes); ?></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php
}

// ============================================================================
// SHORTCODE
// ============================================================================

function somot_reservation_form_shortcode($atts) {
    ob_start();
    ?>
    <div class="somot-reservation-wrapper">
        <div id="reservation-messages"></div>
        
        <form id="somot-reservation-form" class="somot-reservation-form">
            <div class="form-section">
                <h3>Booking A Table Online Is Easy</h3>
                
                <div class="form-group">
                    <label for="customer_name">Your Name <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" required placeholder="Enter your name">
                </div>

                <div class="form-group">
                    <label for="customer_email">Your Email <span class="required">*</span></label>
                    <input type="email" id="customer_email" name="customer_email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="customer_phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="customer_phone" name="customer_phone" required placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="number_of_guests">Total Guests <span class="required">*</span></label>
                    <select id="number_of_guests" name="number_of_guests" required>
                        <option value="">Number of Guests</option>
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?></option>
                        <?php endfor; ?>
                        <option value="21">More than 20 guests</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="branch_id">Select Branch <span class="required">*</span></label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Choose a branch</option>
                        <?php foreach (somot_get_branches() as $branch): ?>
                            <option value="<?php echo esc_attr($branch['id']); ?>">
                                <?php echo esc_html($branch['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reservation_date">Date <span class="required">*</span></label>
                    <input type="text" id="reservation_date" name="reservation_date" required placeholder="Select date" readonly>
                </div>

                <div class="form-group">
                    <label for="reservation_time">Time <span class="required">*</span></label>
                    <select id="reservation_time" name="reservation_time" required>
                        <option value="">Select time</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="additional_notes">Additional Information</label>
                    <textarea id="additional_notes" name="additional_notes" rows="4" placeholder="Enter your message here"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" id="request-cancellation" class="btn btn-secondary">Request Cancellation</button>
                    <button type="submit" id="submit-reservation" class="btn btn-primary">Book a table</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('somot_reservation_form', 'somot_reservation_form_shortcode');

// ============================================================================
// ENQUEUE SCRIPTS (INLINE)
// ============================================================================

function somot_reservation_enqueue_scripts() {
    if (is_page() && has_shortcode(get_post()->post_content, 'somot_reservation_form')) {
        // jQuery UI Datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // Localize script data
        wp_localize_script('jquery', 'somot_reservation', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('somot_reservation_nonce'),
            'branches' => somot_get_branches(),
        ));
        
        // Add inline CSS
        add_action('wp_head', 'somot_reservation_inline_css');
        
        // Add inline JavaScript
        add_action('wp_footer', 'somot_reservation_inline_js');
    }
}
add_action('wp_enqueue_scripts', 'somot_reservation_enqueue_scripts');

// ============================================================================
// INLINE CSS
// ============================================================================

function somot_reservation_inline_css() {
    ?>
    <style type="text/css">
        /* Somot Reservation Form Styles */
        .somot-reservation-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .somot-reservation-form {
            background: #f9f9f9;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-section h3 {
            color: #2d5016;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group .required {
            color: #dc3545;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background-color: #fff;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d5016;
            box-shadow: 0 0 0 3px rgba(45, 80, 22, 0.1);
        }

        .form-group input.error {
            border-color: #dc3545;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
            padding-right: 40px;
        }

        .form-group select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: space-between;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: #2d5016;
            color: #fff;
            flex: 1;
        }

        .btn-primary:hover {
            background-color: #1f3710;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(45, 80, 22, 0.3);
        }

        .btn-primary:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Alert Messages */
        #reservation-messages {
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 15px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .alert .close {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
            padding: 0;
            width: 24px;
            height: 24px;
            line-height: 1;
        }

        .alert .close:hover {
            opacity: 1;
        }

        /* jQuery UI Datepicker Customization */
        .ui-datepicker {
            font-family: inherit !important;
            border: 1px solid #ddd !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .ui-datepicker-header {
            background: #2d5016 !important;
            color: #fff !important;
            border: none !important;
        }

        .ui-state-default {
            text-align: center !important;
            border: 1px solid transparent !important;
        }

        .ui-state-highlight {
            background: #2d5016 !important;
            color: #fff !important;
        }

        .ui-state-active {
            background: #1f3710 !important;
            color: #fff !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .somot-reservation-wrapper {
                padding: 10px;
            }

            .somot-reservation-form {
                padding: 20px;
            }

            .form-section h3 {
                font-size: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        /* Loading State */
        .form-group input:disabled,
        .form-group select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        /* Placeholder Styles */
        ::placeholder {
            color: #999;
            opacity: 1;
        }

        :-ms-input-placeholder {
            color: #999;
        }

        ::-ms-input-placeholder {
            color: #999;
        }

        /* Focus visible for accessibility */
        *:focus-visible {
            outline: 2px solid #2d5016;
            outline-offset: 2px;
        }
    </style>
    <?php
}

// ============================================================================
// INLINE JAVASCRIPT
// ============================================================================

function somot_reservation_inline_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            'use strict';

            // Initialize datepicker
            $('#reservation_date').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                maxDate: '+3M',
                beforeShowDay: function(date) {
                    return [date >= new Date().setHours(0,0,0,0)];
                },
                onSelect: function(dateText) {
                    loadTimeSlots();
                }
            });

            // Load time slots when date or branch changes
            $('#branch_id').on('change', function() {
                if ($('#reservation_date').val()) {
                    loadTimeSlots();
                }
            });

            // Load available time slots
            function loadTimeSlots() {
                var date = $('#reservation_date').val();
                var branchId = $('#branch_id').val();

                if (!date || !branchId) {
                    return;
                }

                $('#reservation_time').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: somot_reservation.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'get_time_slots',
                        nonce: somot_reservation.nonce,
                        date: date,
                        branch_id: branchId
                    },
                    success: function(response) {
                        if (response.success) {
                            var options = '<option value="">Select time</option>';
                            
                            $.each(response.data.slots, function(index, slot) {
                                if (slot.available) {
                                    options += '<option value="' + slot.value + '">' + slot.label + '</option>';
                                } else {
                                    options += '<option value="' + slot.value + '" disabled>' + slot.label + ' (Booked)</option>';
                                }
                            });
                            
                            $('#reservation_time').html(options).prop('disabled', false);
                        } else {
                            showMessage('error', response.data.message || 'Failed to load time slots.');
                            $('#reservation_time').html('<option value="">Select time</option>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        showMessage('error', 'Failed to load time slots. Please try again.');
                        $('#reservation_time').html('<option value="">Select time</option>').prop('disabled', false);
                    }
                });
            }

            // Form submission
            $('#somot-reservation-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $submitBtn = $('#submit-reservation');
                var formData = $form.serialize();

                $submitBtn.prop('disabled', true).text('Submitting...');
                $('#reservation-messages').empty();

                $.ajax({
                    url: somot_reservation.ajax_url,
                    type: 'POST',
                    data: formData + '&action=submit_reservation&nonce=' + somot_reservation.nonce,
                    success: function(response) {
                        if (response.success) {
                            showMessage('success', response.data.message);
                            $form[0].reset();
                            $('#reservation_time').html('<option value="">Select time</option>');
                            
                            $('html, body').animate({
                                scrollTop: $('#reservation-messages').offset().top - 100
                            }, 500);
                        } else {
                            showMessage('error', response.data.message || 'Failed to submit reservation. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'An error occurred. Please try again later.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMsg = xhr.responseJSON.data.message;
                        }
                        
                        showMessage('error', errorMsg);
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text('Book a table');
                    }
                });
            });

            // Request cancellation
            $('#request-cancellation').on('click', function() {
                alert('To cancel your reservation, please contact us directly via phone or email.');
            });

            // Show message function
            function showMessage(type, message) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                var iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                
                var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                           '<strong>' + message + '</strong>' +
                           '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                           '<span aria-hidden="true">&times;</span>' +
                           '</button>' +
                           '</div>';
                
                $('#reservation-messages').html(html);
                
                // Manual close button handler
                $('.alert .close').on('click', function() {
                    $(this).closest('.alert').fadeOut(function() {
                        $(this).remove();
                    });
                });
                
                // Auto dismiss after 10 seconds
                setTimeout(function() {
                    $('#reservation-messages .alert').fadeOut(function() {
                        $(this).remove();
                    });
                }, 10000);
            }

            // Phone number formatting
            $('#customer_phone').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                if (value.length > 0 && value[0] !== '+') {
                    if (value.length <= 12) {
                        $(this).val('+' + value);
                    }
                }
            });

            // Email validation
            $('#customer_email').on('blur', function() {
                var email = $(this).val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('error');
                    showMessage('error', 'Please enter a valid email address.');
                } else {
                    $(this).removeClass('error');
                }
            });

            // Number of guests validation
            $('#number_of_guests').on('change', function() {
                var guests = parseInt($(this).val());
                
                if (guests > 20) {
                    showMessage('info', 'For parties larger than 20 guests, please contact us directly to arrange your reservation.');
                }
            });
        });
    </script>
    <?php
}

// ============================================================================
// AJAX HANDLERS
// ============================================================================

// Handle form submission
function somot_handle_reservation_submission() {
    check_ajax_referer('somot_reservation_nonce', 'nonce');
    
    $customer_name = sanitize_text_field($_POST['customer_name']);
    $customer_email = sanitize_email($_POST['customer_email']);
    $customer_phone = sanitize_text_field($_POST['customer_phone']);
    $number_of_guests = intval($_POST['number_of_guests']);
    $branch_id = intval($_POST['branch_id']);
    $reservation_date = sanitize_text_field($_POST['reservation_date']);
    $reservation_time = sanitize_text_field($_POST['reservation_time']);
    $additional_notes = sanitize_textarea_field($_POST['additional_notes']);
    
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || 
        empty($number_of_guests) || empty($branch_id) || empty($reservation_date) || empty($reservation_time)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields.'));
        return;
    }
    
    if (!is_email($customer_email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        return;
    }
    
    $branches = somot_get_branches();
    $branch = null;
    foreach ($branches as $b) {
        if ($b['id'] == $branch_id) {
            $branch = $b;
            break;
        }
    }
    
    if (!$branch) {
        wp_send_json_error(array('message' => 'Invalid branch selected.'));
        return;
    }
    
    $post_id = wp_insert_post(array(
        'post_type' => 'reservation',
        'post_title' => $customer_name . ' - ' . $reservation_date . ' ' . $reservation_time,
        'post_status' => 'publish',
    ));
    
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to create reservation. Please try again.'));
        return;
    }
    
    $api_data = array(
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'number_of_guests' => $number_of_guests,
        'branch_id' => $branch_id,
        'reservation_date' => $reservation_date,
        'reservation_time' => $reservation_time,
        'additional_notes' => $additional_notes,
        'wordpress_post_id' => $post_id,
    );
    
    $response = wp_remote_post(GOODRIVER_API_URL . '/reservations', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => GOODRIVER_API_KEY,
        ),
        'body' => json_encode($api_data),
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        wp_delete_post($post_id, true);
        wp_send_json_error(array('message' => 'Failed to connect to reservation system. Please try again later.'));
        return;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!$body['success']) {
        wp_delete_post($post_id, true);
        $error_message = isset($body['message']) ? $body['message'] : 'Failed to create reservation.';
        wp_send_json_error(array('message' => $error_message));
        return;
    }
    
    $reservation_data = $body['data'];
    update_post_meta($post_id, '_customer_name', $customer_name);
    update_post_meta($post_id, '_customer_email', $customer_email);
    update_post_meta($post_id, '_customer_phone', $customer_phone);
    update_post_meta($post_id, '_number_of_guests', $number_of_guests);
    update_post_meta($post_id, '_branch_id', $branch_id);
    update_post_meta($post_id, '_branch_name', $branch['name']);
    update_post_meta($post_id, '_reservation_date', $reservation_date);
    update_post_meta($post_id, '_reservation_time', $reservation_time);
    update_post_meta($post_id, '_additional_notes', $additional_notes);
    update_post_meta($post_id, '_goodriver_id', $reservation_data['id']);
    update_post_meta($post_id, '_order_key', $reservation_data['order_key']);
    update_post_meta($post_id, '_status', 'Waiting');
    
    wp_send_json_success(array(
        'message' => 'Your reservation has been submitted successfully! We will contact you shortly to confirm.',
        'reservation' => $reservation_data,
    ));
}
add_action('wp_ajax_submit_reservation', 'somot_handle_reservation_submission');
add_action('wp_ajax_nopriv_submit_reservation', 'somot_handle_reservation_submission');

// Get available time slots
function somot_get_available_time_slots() {
    check_ajax_referer('somot_reservation_nonce', 'nonce');
    
    $date = sanitize_text_field($_POST['date']);
    $branch_id = intval($_POST['branch_id']);
    
    if (empty($date) || empty($branch_id)) {
        wp_send_json_error(array('message' => 'Invalid parameters.'));
        return;
    }
    
    $response = wp_remote_post(GOODRIVER_API_URL . '/reservations/check-availability', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => GOODRIVER_API_KEY,
        ),
        'body' => json_encode(array(
            'date' => $date,
            'branch_id' => $branch_id,
        )),
        'timeout' => 15,
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to check availability.'));
        return;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!$body['success']) {
        wp_send_json_error(array('message' => 'Failed to check availability.'));
        return;
    }
    
    $slots = array();
    $start_time = strtotime('10:00');
    $end_time = strtotime('22:00');
    $interval = 30 * 60;
    
    $booked_slots = isset($body['data']['booked_slots']) ? $body['data']['booked_slots'] : array();
    
    for ($time = $start_time; $time <= $end_time; $time += $interval) {
        $time_string = date('H:i', $time);
        $time_display = date('g:i A', $time);
        
        $slots[] = array(
            'value' => $time_string,
            'label' => $time_display,
            'available' => !in_array($time_string, $booked_slots),
        );
    }
    
    wp_send_json_success(array('slots' => $slots));
}
add_action('wp_ajax_get_time_slots', 'somot_get_available_time_slots');
add_action('wp_ajax_nopriv_get_time_slots', 'somot_get_available_time_slots');
