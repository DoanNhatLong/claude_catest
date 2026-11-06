<?php
// Hàm này dùng để lấy thông tin user hiện tại qua shortcode [user_info], 
// có thể tùy chọn hiển thị tên ngắn gọn hoặc đầy đủ thông tin.
function render_user_info_shortcode( $atts ) {
    if ( ! is_user_logged_in() ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'quiz_guest_attempts';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        $max_free = 3;
        $used = 0;

        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT attempts_used FROM $table_name WHERE ip_address = %s AND user_agent = %s",
            $ip_address,
            $user_agent
        ));

        if ($record) {
            $used = intval($record->attempts_used);
        }

        $left = max(0, $max_free - $used);

        // Link to the upgrade/checkout page
        $upgrade_url = home_url('/checkout/?add-to-cart=904');

        return sprintf(
            '<div style="display: flex; flex-direction: column; gap: 6px; font-family: inherit;">' .
                '<div style="font-size: 15px; color: #2c3e50; font-weight: 500;">Hi, <span style="color: #0073aa; font-weight: 600;">Guest</span> &bull; <span style="color: #666;">You have <strong style="color: #d9534f;">%d</strong> free tests left</span></div>' .
                '<div style="font-size: 13px; color: #7f8c8d; line-height: 1.4;">Unlock unlimited access, <a href="https://catest.ca/upgrade-premium/" style="color: #0073aa; text-decoration: none; font-weight: 500; border-bottom: 1px dashed #0073aa;">click here to upgrade</a></div>' .
            '</div>',
            $left,
            esc_url($upgrade_url)
        );
    }

    $current_user = wp_get_current_user();

    // Link to the province selection / change page
    $user_id = $current_user->ID;
    $current_province_code = get_user_meta( $user_id, 'user_province', true );
    $current_province_name = 'None';

    if ( ! empty( $current_province_code ) ) {
        $terms = get_terms( array(
            'taxonomy'   => 'site_region',
            'hide_empty' => false,
        ) );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $code = get_term_meta( $term->term_id, 'region_code', true );
                if ( empty( $code ) ) {
                    $code = $term->slug;
                }
                if ( $code === $current_province_code ) {
                    $current_province_name = $term->name;
                    break;
                }
            }
        }
    }

   return sprintf(
        '<div style="display: flex; flex-direction: column; gap: 6px; font-family: inherit;">' .
            '<div style="font-size: 15px; color: #2c3e50; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">' .
                'Hi, <span style="color: #d35400; font-weight: 600;">%s</span>' .
                '<span style="color: #dcdde1;">|</span>' .
                '<span style="color: #0284c7; background: #f0f9ff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; letter-spacing: 0.3px;">%s</span>' .
            '</div>' .
            '<div style="font-size: 13px; color: #7f8c8d; line-height: 1.4;">To change your province, <a href="https://catest.ca/change-province/" style="color: #0073aa; text-decoration: none; font-weight: 500; border-bottom: 1px dashed #0073aa;">click here</a></div>' .
        '</div>',
        esc_html( $current_user->display_name ),
        esc_html( $current_province_name )
    );
}
add_shortcode('user_info', 'render_user_info_shortcode');

add_shortcode('list_all_shortcodes', 'show_all_registered_shortcodes');
function show_all_registered_shortcodes() {
    global $shortcode_tags;
    
    if ( empty( $shortcode_tags ) ) {
        return '<p>Không có shortcode nào được đăng ký.</p>';
    }

    $output = '<div style="background: #f8f9fa; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
    $output .= '<h4>Danh sách các Shortcode hiện có trên hệ thống (' . count($shortcode_tags) . '):</h4>';
    $output .= '<ul style="column-count: 3; margin-top: 10px;">';
    
    ksort($shortcode_tags); // Sắp xếp theo bảng chữ cái
    foreach ( $shortcode_tags as $tag => $code ) {
        $output .= '<li><code>[' . esc_html( $tag ) . ']</code></li>';
    }
    
    $output .= '</ul></div>';
    return $output;
}

function snnj_track_guest_attempt_on_click() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_guest_attempts';
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';

    // Kiểm tra xem thiết bị này đã có trong DB chưa
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE ip_address = %s AND user_agent = %s",
        $ip_address,
        $user_agent
    ));

    if (!$existing) {
        // Chưa có -> Tạo mới bản ghi với attempts_used = 1
        $wpdb->insert(
            $table_name,
            array(
                'ip_address'    => $ip_address,
                'user_agent'    => $user_agent,
                'attempts_used' => 1,
                'last_updated'  => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s')
        );
    } else {
        // Đã có -> Chỉ việc update tăng attempts_used lên 1 (không báo lỗi gì cả)
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET attempts_used = attempts_used + 1, last_updated = %s WHERE id = %d",
            current_time('mysql'),
            $existing->id
        ));
    }
}

add_shortcode('woo_product_name', function($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts);
    $product_id = intval($atts['id']);
    if (!$product_id) return '';

    $display_name = get_post_meta($product_id, 'display_name', true);
    if (empty($display_name)) {
        $product = wc_get_product($product_id);
        $display_name = $product ? $product->get_name() : '';
    }
    return esc_html($display_name);
});

// 2. Shortcode lấy giá sản phẩm theo ID
add_shortcode('woo_product_price', function($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts);
    $product_id = intval($atts['id']);
    if (!$product_id) return '';

    $product = wc_get_product($product_id);
    return $product ? $product->get_price_html() : '';
});

add_shortcode('woo_product_cart_url', function($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts);
    $product_id = intval($atts['id']);
    if (!$product_id) return '#';

    return esc_url(home_url('/checkout/?add-to-cart=' . $product_id));
});

add_shortcode('show_cart_id', function() {
    // Check if the add-to-cart parameter exists in the URL
    if (isset($_GET['add-to-cart'])) {
        $product_id = intval($_GET['add-to-cart']);
        return '<p style="background: #e7f5ff; padding: 10px; border: 1px solid #74c0fc;">Caught Product ID from URL: <strong>' . $product_id . '</strong></p>';
    }
    
    return '<p style="color: red;">No add-to-cart parameter detected in the URL yet.</p>';
});

function render_woo_upgrade_button($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
        'text' => 'UPGRADE'
    ), $atts);

    $product_id = intval($atts['id']);
    if ($product_id <= 0) {
        return '';
    }

    // Generate checkout URL with add-to-cart parameter
    $checkout_url = home_url('/checkout/?add-to-cart=' . $product_id);
    
    // Return the HTML button matching your design style
    return '<a href="' . esc_url($checkout_url) . '" class="button wp-element-button" style="display: block; text-align: center; text-decoration: none;">' . esc_html($atts['text']) . '</a>';
}
add_shortcode('woo_upgrade_button', 'render_woo_upgrade_button');

// add_action( 'woocommerce_payment_complete', 'save_single_package_on_checkout', 10, 1 );
// function save_single_package_on_checkout( $order_id ) {
//     $order = wc_get_order( $order_id );
//     $user_id = $order->get_user_id();

//     // Nếu thanh toán dạng khách vãng lai (chưa đăng nhập) thì bỏ qua
//     if ( ! $user_id ) {
//         return;
//     }

//     // Lấy sản phẩm trong đơn hàng
//     $items = $order->get_items();
//     if ( empty( $items ) ) {
//         return;
//     }

//     $item = reset( $items );
//     $product_name = $item->get_name(); // Lấy tên gói sản phẩm vừa mua

//     // Lấy danh sách các gói hiện tại của user (dùng tham số false để lưu thành nhiều dòng riêng biệt)
//     $existing_packages = get_user_meta( $user_id, 'user_purchased_package', false );

//     // Kiểm tra nếu chưa có gói này trong DB thì tiến hành add thêm dòng mới
//     if ( ! in_array( $product_name, $existing_packages ) ) {
//         add_user_meta( $user_id, 'user_purchased_package', $product_name );
//     }
// }

function render_region_inline_grid() {
    // Lấy danh sách các tỉnh thành động từ Database
    $regions = get_terms( array(
        'taxonomy'   => 'site_region',
        'hide_empty' => false,
    ) );

    // Lấy region đang được chọn hiện tại (ưu tiên từ URL ?region=...)
    $current_region = isset( $_GET['region'] ) ? sanitize_text_field( $_GET['region'] ) : '';

    ob_start();
    ?>
    <div class="region-inline-wrapper">
        <div class="region-inline-grid">
            <?php
            if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
                foreach ( $regions as $region ) {
                    $region_code = get_term_meta( $region->term_id, 'region_code', true );
                    if ( empty( $region_code ) ) {
                        $region_code = $region->slug;
                    }
                    
                    // Kiểm tra xem nút này có phải là bang đang được chọn không
                    $is_active = ( $current_region === $region_code ) ? 'is-active' : '';

                    echo '<a href="/drivers-license-class/?region=' . esc_attr( $region_code ) . '" class="region-item-btn ' . esc_attr( $is_active ) . '">';
                    echo '<span class="region-name">' . esc_html( $region->name ) . '</span>';
                    echo '</a>';
                }
            }
            ?>
        </div>
    </div>

    <style>
        .region-inline-wrapper {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.04);
            width: 100%;
            box-sizing: border-box;
        }

        /* Lưới các nút chia thành 4 cột trên một hàng */
        .region-inline-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .region-item-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 12px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.25s ease;
        }

        .region-item-btn:hover {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        /* Trạng thái khi bang đó ĐÃ ĐƯỢC CHỌN (Active) */
        .region-item-btn.is-active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        .region-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Responsive: Màn hình trung bình thì 2 hoặc 3 cột, màn hình nhỏ co về 1 cột */
        @media (max-width: 1200px) {
            .region-inline-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .region-inline-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .region-inline-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'region_inline_grid', 'render_region_inline_grid' );
