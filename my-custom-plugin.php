<?php
/*
Plugin Name: Custom Shortcodes 
Description: Plugin tự viết để chứa các tùy chỉnh (hiển thị bài test, nút mua hàng...)
Version: 1.0
Author: Nhatlong
*/

// Bảo mật: Ngăn chặn truy cập trực tiếp vào file này
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ====================================================================
   1. TẠO MENU CHÍNH "QUẢN LÝ" VÀ FIX SÁNG ĐÚNG MENU ADMIN
==================================================================== */
function register_exam_admin_menu() {
    add_menu_page(
        'Quản lý',
        'Quản lý',
        'manage_options',
        'quan-ly-chung',
        'render_quan_ly_main_page',
        'dashicons-welcome-learn-more',
        6
    );

    add_submenu_page(
        'quan-ly-chung',
        'Tỉnh thành',
        'Tỉnh thành',
        'manage_options',
        'edit-tags.php?taxonomy=site_region'
    );

    add_submenu_page(
        'quan-ly-chung',
        'Loại đề thi',
        'Loại đề thi',
        'manage_options',
        'edit-tags.php?taxonomy=exam_category'
    );
}
add_action( 'admin_menu', 'register_exam_admin_menu' );

function render_quan_ly_main_page() {
    echo '<div class="wrap"><h1>Khu vực Quản lý hệ thống</h1><p>Vui lòng chọn các mục con bên trái (Tỉnh thành, Loại đề thi) để quản lý dữ liệu.</p></div>';
}

function fix_admin_menu_highlight() {
    global $parent_file, $submenu_file;
    $screen = get_current_screen();
    
    if ( $screen && in_array( $screen->taxonomy, array( 'site_region', 'exam_category' ) ) ) {
        $parent_file = 'quan-ly-chung';
        $submenu_file = 'edit-tags.php?taxonomy=' . $screen->taxonomy;
    }
}
add_action( 'admin_head', 'fix_admin_menu_highlight' );

function register_custom_management_taxonomies() {
    $region_labels = array(
        'name'              => 'Tỉnh thành (Regions)',
        'singular_name'     => 'Region',
        'search_items'      => 'Tìm kiếm tỉnh thành',
        'all_items'         => 'Tất cả tỉnh thành',
        'edit_item'         => 'Sửa tỉnh thành',
        'update_item'       => 'Cập nhật tỉnh thành',
        'add_new_item'      => 'Thêm tỉnh thành mới',
        'new_item_name'     => 'Tên tỉnh thành mới',
        'menu_name'         => 'Tỉnh thành',
    );
    register_taxonomy( 'site_region', array( 'post', 'page' ), array(
        'hierarchical'      => false,
        'labels'            => $region_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'show_in_menu'      => false,
    ) );

    $exam_labels = array(
        'name'              => 'Loại đề thi',
        'singular_name'     => 'Exam Category',
        'search_items'      => 'Tìm kiếm loại đề thi',
        'all_items'         => 'Tất cả loại đề thi',
        'edit_item'         => 'Sửa loại đề thi',
        'update_item'       => 'Cập nhật loại đề thi',
        'add_new_item'      => 'Thêm loại đề thi mới',
        'new_item_name'     => 'Tên loại đề thi mới',
        'menu_name'         => 'Loại đề thi',
    );
    register_taxonomy( 'exam_category', array( 'post', 'page' ), array(
        'hierarchical'      => false,
        'labels'            => $exam_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'show_in_menu'      => false,
    ) );
}
add_action( 'init', 'register_custom_management_taxonomies' );

function render_custom_value_field($term) {
    $t_id = is_object($term) ? $term->term_id : 0;
    $region_code = $t_id ? get_term_meta($t_id, 'region_code', true) : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="region_code">Mã viết tắt (Value)</label></th>
        <td>
            <input type="text" name="region_code" id="region_code" value="<?php echo esc_attr($region_code); ?>" style="width:50%;">
            <p class="description">Ví dụ: AB, BC, ON.</p>
        </td>
    </tr>
    <?php
}
add_action('site_region_add_form_fields', 'render_custom_value_field', 10, 2);
add_action('site_region_edit_form_fields', 'render_custom_value_field', 10, 2);
add_action('exam_category_add_form_fields', 'render_custom_value_field', 10, 2);
add_action('exam_category_edit_form_fields', 'render_custom_value_field', 10, 2);

function save_custom_value_field($term_id) {
    if (isset($_POST['region_code'])) {
        update_term_meta($term_id, 'region_code', sanitize_text_field($_POST['region_code']));
    }
}
add_action('created_site_region', 'save_custom_value_field', 10, 2);
add_action('edited_site_region', 'save_custom_value_field', 10, 2);
add_action('created_exam_category', 'save_custom_value_field', 10, 2);
add_action('edited_exam_category', 'save_custom_value_field', 10, 2);

function hide_slug_and_description_in_admin() {
    $screen = get_current_screen();
    if ( $screen && in_array( $screen->taxonomy, array( 'site_region', 'exam_category' ) ) ) {
        echo '<style>.term-slug-wrap, .term-description-wrap { display: none !important; }</style>';
    }
}
add_action( 'admin_head', 'hide_slug_and_description_in_admin' );

function custom_table_columns_config( $columns ) {
    return array(
        'cb'          => $columns['cb'], 
        'name'        => 'Tên',
        'region_code' => 'Mã viết tắt (Value)'
    );
}
add_filter( 'manage_edit-site_region_columns', 'custom_table_columns_config' );
add_filter( 'manage_edit-exam_category_columns', 'custom_table_columns_config' );

function fill_custom_table_column_data( $row_output, $column_name, $term_id ) {
    if ( 'region_code' === $column_name ) {
        $region_code = get_term_meta( $term_id, 'region_code', true );
        return !empty( $region_code ) ? '<strong>' . esc_html( $region_code ) . '</strong>' : '—';
    }
    return $row_output;
}
add_filter( 'manage_site_region_custom_column', 'fill_custom_table_column_data', 10, 3 );
add_filter( 'manage_exam_category_custom_column', 'fill_custom_table_column_data', 10, 3 );

/* ====================================================================
   5. SHORTCODE TEST & POPUP LẤY DỮ LIỆU ĐỘNG TỪ DATABASE
==================================================================== */

function render_region_selector_popup() {
    // Lấy danh sách các tỉnh thành từ Database thay vì fix cứng
    $regions = get_terms( array(
        'taxonomy'   => 'site_region',
        'hide_empty' => false,
    ) );

    ob_start();
    ?>
    <div id="region-popup-modal" class="region-popup-overlay">
        <div class="region-popup-content">
            <h3>Select Your Region</h3>
            <p class="region-subtitle">Choose your province or territory to get started:</p>
            
            <div class="region-grid">
                <?php
                if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
                    foreach ( $regions as $region ) {
                        $region_code = get_term_meta( $region->term_id, 'region_code', true );
                        $display_code = ! empty( $region_code ) ? $region_code : strtoupper( substr( $region->slug, 0, 2 ) );
                       echo '<a href="/drivers-license-class/?region=' . esc_attr( $region_code ) . '">' . esc_html( $region->name ) . '</a>';
                    }
                }
                ?>
            </div>
            
            <button class="region-close-btn" type="button">&times;</button>
        </div>
    </div>
    
    <style>
        .region-popup-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; }
        .region-popup-overlay.is-open { display: flex; }
        .region-popup-content { background: #fff; padding: 30px; border-radius: 12px; width: 92%; max-width: 600px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto; }
        .region-popup-content h3 { margin-top: 0; font-size: 22px; color: #111; }
        .region-subtitle { color: #666; font-size: 14px; margin-bottom: 15px; }
        .region-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .region-grid a { padding: 10px 14px; background: #f8f9fa; text-decoration: none; color: #333; border-radius: 6px; text-align: left; font-size: 14px; font-weight: 500; border: 1px solid #e5e7eb; transition: all 0.2s; }
        .region-grid a:hover { background: #0073aa; color: #fff; border-color: #0073aa; }
   .region-close-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    background: transparent;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: all 0.2s ease;
}
.region-close-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
        @media (max-width: 480px) {
            .region-grid { grid-template-columns: 1fr; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggers = document.querySelectorAll('.region-trigger-btn');
            const modal = document.getElementById('region-popup-modal');
            const closeBtn = document.querySelector('.region-close-btn');

            if(modal) {
                triggers.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        modal.classList.add('is-open');
                    });
                });

                if(closeBtn) {
                    closeBtn.addEventListener('click', () => modal.classList.remove('is-open'));
                }

                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('is-open');
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode( 'region_selector_popup', 'render_region_selector_popup' );

// [MỐC 2026-09-29] - Hàm phân nhánh trang chủ và bảo vệ trang memberhome chống Guest xâm nhập
// function redirect_user_to_memberhome() {
//     if ( is_front_page() && is_user_logged_in() && ! is_admin() ) {
//         $user_id = get_current_user_id();
//         $province = get_user_meta( $user_id, 'user_province', true );
        
//         $member_home_slug = 'memberhome';

//         if ( ! empty( $province ) && ! is_page( $member_home_slug ) ) {
//             wp_redirect( home_url( '/' . $member_home_slug . '/' ) );
//             exit;
//         }
//     }
// }
// add_action( 'template_redirect', 'redirect_user_to_memberhome' );

// function protect_member_home_page() {
//     $member_home_slug = 'memberhome';

//     if ( is_page( $member_home_slug ) ) {
//         if ( ! is_user_logged_in() ) {
//             wp_redirect( home_url( '/' ) );
//             exit;
//         }

//         $user_id = get_current_user_id();
//         $province = get_user_meta( $user_id, 'user_province', true );
        
//         if ( empty( $province ) ) {
//             wp_redirect( home_url( '/provinces/' ) );
//             exit;
//         }
//     }
// }
// add_action( 'template_redirect', 'protect_member_home_page' );

// Nạp file exam-functions.php
$exam_file = plugin_dir_path( __FILE__ ) . 'includes/exam-functions.php';
if ( file_exists( $exam_file ) ) {
    require_once $exam_file;
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p><strong>Custom Plugin Error:</strong> Missing file <code>includes/exam-functions.php</code>.</p></div>';
    });
}

// Nạp file user_function.php

$user_func_file = plugin_dir_path( __FILE__ ) . 'includes/user_function.php';
if ( file_exists( $user_func_file ) ) {
    require_once $user_func_file;
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p><strong>Custom Plugin Error:</strong> Missing file <code>includes/user_function.php</code>.</p></div>';
    });
}

// Nạp file utils-function.php
$utils_file = plugin_dir_path( __FILE__ ) . 'utils/utils-function.php';
if ( file_exists( $utils_file ) ) {
    require_once $utils_file;
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p><strong>Custom Plugin Error:</strong> Missing file <code>utils/utils-function.php</code>.</p></div>';
    });
}
$admin_func_file = plugin_dir_path( __FILE__ ) . 'includes/admin-functions.php';
if ( file_exists( $admin_func_file ) ) {
    require_once $admin_func_file;
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p><strong>Custom Plugin Error:</strong> Missing file <code>includes/admin-functions.php</code>.</p></div>';
    });
}


?>