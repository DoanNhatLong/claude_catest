<?php
/**
 * ==============================================================================
 * XỬ LÝ NGHIỆP VỤ USER & BẮT BUỘC CHỌN TỈNH BANG (PROVINCE)
 * ==============================================================================
 */

/**
 * 1. Ép buộc người dùng đã đăng nhập phải chọn Tỉnh bang nếu chưa có
 */
function enforce_province_selection() {
    // Chỉ áp dụng khi user đã đăng nhập và không phải đang ở trang quản trị admin
    if ( is_user_logged_in() && ! is_admin() ) {
        $user_id = get_current_user_id();
        
        // Lấy giá trị tỉnh bang trong User Meta
        $province = get_user_meta( $user_id, 'user_province', true );
        
        // Slug của trang chứa popup/form chọn tỉnh bang
        $province_page_slug = 'provinces'; 

        // Nếu chưa có province và hiện tại KHÔNG đứng ở trang chọn province thì chuyển hướng ngay lập tức
        if ( empty( $province ) && ! is_page( $province_page_slug ) ) {
            wp_redirect( home_url( '/' . $province_page_slug . '/' ) );
            exit;
        }
    }
}
add_action( 'template_redirect', 'enforce_province_selection' );


/**
 * 2. Xử lý lưu Tỉnh bang khi người dùng submit form chọn
 */
function save_user_selected_province() {
    if ( is_user_logged_in() && isset( $_POST['selected_province'] ) ) {
        $user_id  = get_current_user_id();
        $province = sanitize_text_field( $_POST['selected_province'] );
        
        // Lưu giá trị vào user meta
        update_user_meta( $user_id, 'user_province', $province );
        
        // Lưu xong thì đưa người dùng quay lại trang chủ
        wp_safe_redirect( home_url( '/memberhome' ) );
        exit;
    }
}
add_action( 'admin_post_save_province', 'save_user_selected_province' );
add_action( 'admin_post_nopriv_save_province', 'save_user_selected_province' );


/**
 * 3. Shortcode hiển thị form chọn tỉnh bang ngoài Frontend 
 */
function render_province_selection_form() {
    if ( ! is_user_logged_in() ) {
        return '<p style="text-align: center; padding: 40px; font-size: 16px;">Please log in to select your province.</p>';
    }
	
	$user_id = get_current_user_id();
    $has_province = get_user_meta( $user_id, 'user_province', true );

    if ( ! empty( $has_province ) ) {
        return '<div style="max-width: 600px; margin: 60px auto; padding: 30px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 16px; text-align: center; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                    <div style="font-size: 40px; margin-bottom: 15px;">🔒</div>
                    <h3 style="color: #0f172a; font-size: 20px; font-weight: 700; margin-bottom: 10px;">Access Restricted</h3>
                    <p style="color: #64748b; font-size: 15px; line-height: 1.5; margin: 0;">Bạn đã hoàn tất việc chọn tỉnh/thành phố trước đó. Không thể thực hiện lại thao tác này hoặc truy cập lại trang thiết lập.</p>
                </div>';
    }

    ob_start();
    ?>
    <div class="province-page-wrapper" style="display: flex; gap: 30px; max-width: 1100px; margin: 40px auto; align-items: flex-start; padding: 0 20px; box-sizing: border-box;">
        
        <!-- CỘT BÊN TRÁI: Khung hướng dẫn có thể thu gọn / mở rộng lại -->
        <div id="notice-column" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 35px; box-sizing: border-box; text-align: center; transition: all 0.4s ease;">
            <div id="notice-icon" style="font-size: 40px; margin-bottom: 15px; transition: all 0.3s ease;">👋</div>
            <h3 id="notice-title" style="margin: 0 0 15px 0; color: #0f172a; font-size: 22px; font-weight: 700;">First-Time Setup: Select Your Province</h3>
            
            <div id="notice-body">
    <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px; line-height: 1.6; text-align: left;">
        Welcome! Please select your province carefully. Your selection dictates the content and tests available to you, as they vary by region.
    </p>

    <div style="background-color: #fef9c3; border: 1px solid #fde047; border-radius: 8px; padding: 15px; margin-bottom: 25px; text-align: left;">
        <div style="color: #b45309; font-size: 14px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <span>⚠</span> Please Note: Choosing correctly from the start is highly recommended.
        </div>
        <p style="margin: 0; color: #78350f; font-size: 13px; line-height: 1.5;">
            While you can change your province later, this action requires Admin review and may take 24–48 hours to process.
        </p>
    </div>
</div>

            <button type="button" id="notice-action-btn" onclick="toggleNoticeState()" style="width: 100%; background: #0284c7; color: #ffffff; border: none; padding: 15px; font-size: 15px; font-weight: 600; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2); transition: background 0.2s;">
                Understand & Select Province
            </button>
        </div>

        <!-- CỘT BÊN PHẢI: Form chọn tỉnh -->
        <div id="form-column" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 35px; box-sizing: border-box; opacity: 0.4; pointer-events: none; transition: all 0.3s ease;">
            <div style="margin-bottom: 25px;">
                <h3 style="margin: 0 0 8px 0; color: #0f172a; font-size: 22px; font-weight: 700;">Select Your Province</h3>
                <p style="margin: 0; color: #64748b; font-size: 14px;">Please choose one of the provinces below:</p>
            </div>
            
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <input type="hidden" name="action" value="save_province">
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; max-height: 350px; overflow-y: auto; padding-right: 5px;">
                    <?php
                    $regions = get_terms( array(
                        'taxonomy'   => 'site_region',
                        'hide_empty' => false,
                    ) );

                    if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
                        foreach ( $regions as $region ) {
                            $region_code = get_term_meta( $region->term_id, 'region_code', true );
                            if ( empty( $region_code ) ) {
                                $region_code = $region->slug;
                            }
                            echo '<label style="display: flex; align-items: center; padding: 14px 18px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.2s ease;">';
                            echo '<input type="radio" name="selected_province" value="' . esc_attr( $region_code ) . '" required style="width: 18px; height: 18px; margin-right: 15px; cursor: pointer; accent-color: #0284c7;"> ';
                            echo '<span style="font-size: 15px; font-weight: 500; color: #334155;">' . esc_html( $region->name ) . '</span>';
                            echo '</label>';
                        }
                    } else {
                        echo '<p style="color: #ef4444; text-align: center; padding: 20px;">No provinces found.</p>';
                    }
                    ?>
                </div>
                
                <button type="submit" style="width: 100%; background: #0284c7; color: #ffffff; border: none; padding: 15px; font-size: 16px; font-weight: 600; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2); transition: background 0.2s;">
                    Confirm and Continue
                </button>
            </form>
        </div>

    </div>

    <!-- JavaScript xử lý bật/tắt (toggle) trạng thái xem lại notice -->
    <script>
    var isNoticeCollapsed = false;

    function toggleNoticeState() {
        var formCol = document.getElementById('form-column');
        var noticeCol = document.getElementById('notice-column');
        var noticeBody = document.getElementById('notice-body');
        var noticeIcon = document.getElementById('notice-icon');
        var noticeTitle = document.getElementById('notice-title');
        var btn = document.getElementById('notice-action-btn');

        if (!isNoticeCollapsed) {
            // Lần bấm đầu tiên: Mở khóa form bên phải & thu gọn cột trái lại
            formCol.style.opacity = '1';
            formCol.style.pointerEvents = 'auto';
            
            noticeBody.style.display = 'none';
            noticeIcon.style.fontSize = '24px';
            noticeIcon.style.marginBottom = '5px';
            noticeIcon.innerHTML = 'ℹ️';
            
            noticeTitle.style.fontSize = '16px';
            noticeTitle.style.marginBottom = '10px';
            noticeTitle.innerText = 'First-Time Setup Notice';
            
            // Đổi nút thành dạng cho phép click xem lại
            btn.style.background = '#e2e8f0';
            btn.style.color = '#334155';
            btn.innerText = 'Click to Read Again 🔄';
            
            noticeCol.style.padding = '25px';
            noticeCol.style.background = '#f8fafc';
            noticeCol.style.borderColor = '#cbd5e1';
            
            isNoticeCollapsed = true;
        } else {
            // Các lần bấm tiếp theo: Bung rộng cột trái ra để đọc lại
            noticeBody.style.display = 'block';
            noticeIcon.style.fontSize = '40px';
            noticeIcon.style.marginBottom = '15px';
            noticeIcon.innerHTML = '👋';
            
            noticeTitle.style.fontSize = '22px';
            noticeTitle.style.marginBottom = '15px';
            noticeTitle.innerText = 'First-Time Setup: Select Your Province';
            
            // Đổi nút về trạng thái thu gọn
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';
            btn.innerText = 'Hide Notice ✕';
            
            noticeCol.style.padding = '35px';
            noticeCol.style.background = '#ffffff';
            noticeCol.style.borderColor = '#e2e8f0';
            
            isNoticeCollapsed = false;
        }
    }
    </script>

    <style>
        .province-page-wrapper label:hover {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
        }
        .province-page-wrapper input[type="radio"]:checked + span {
            color: #0284c7 !important;
            font-weight: 600 !important;
        }
        .province-page-wrapper label:has(input[type="radio"]:checked) {
            background: #f0f9ff !important;
            border-color: #0284c7 !important;
        }
        @media (max-width: 768px) {
            .province-page-wrapper {
                flex-direction: column !important;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'province_form', 'render_province_selection_form' );

/**
 * =======================================================
 * XỬ LÝ NHIỆP VỤ ĐỔI TỈNH BANG (PROVINCE CHANGE REQUEST)
 * =======================================================
 */

function render_province_change_request_form() {
	 if ( is_user_logged_in() ) {
    global $wpdb;
    $current_user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'user_requests';

    // Đếm xem user này có đơn transfer_province nào đang pending VÀ chưa bị xóa mềm (is_deleted = 0) không
    $has_pending = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND request_type = 'transfer_province' AND status = 'pending' AND is_deleted = 0",
        $current_user_id
    ) );

    if ( $has_pending > 0 ) {
        // Nếu có, hiển thị thông báo chặn form
        return '<div style="max-width: 600px; margin: 40px auto; text-align: center; padding: 40px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; font-family: inherit;">
            <div style="font-size: 45px; margin-bottom: 15px;">⏳</div>
            <h3 style="color: #b45309; font-size: 22px; font-weight: 700; margin-bottom: 12px;">Request in Progress</h3>
            <p style="color: #92400e; font-size: 15px; font-weight: 500; line-height: 1.6; margin: 0;">
                Your province transfer request is currently being processed by our administrators. Please wait patiently as this may take 24-48 hours.
            </p>
            <a href="/memberhome" style="display: inline-block; margin-top: 25px; background: #b45309; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Return to Member Home</a>
        </div>';
    }
}
	
    $regions = get_terms( array(
        'taxonomy'   => 'site_region',
        'hide_empty' => false,
    ) );

    $region_map = array();
    if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
        foreach ( $regions as $reg ) {
            $code = get_term_meta( $reg->term_id, 'region_code', true );
            if ( empty( $code ) ) {
                $code = $reg->slug;
            }
            $region_map[strtoupper( trim( $code ) )] = $reg->name;
        }
    }

    $target_page_url = home_url( '/memberhome/confirm-change/' );

    ob_start();
    ?>
    <div class="province-page-wrapper" style="display: flex; gap: 30px; max-width: 1100px; margin: 40px auto; align-items: flex-start; padding: 0 20px; box-sizing: border-box;">
        
        <!-- CỘT BÊN TRÁI -->
        <div id="notice-column" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 35px; box-sizing: border-box; text-align: center; transition: all 0.4s ease;">
            <div id="notice-icon" style="font-size: 40px; margin-bottom: 15px; transition: all 0.3s ease;">📋</div>
            <h3 id="notice-title" style="margin: 0 0 15px 0; color: #0f172a; font-size: 22px; font-weight: 700;">Region Change Request</h3>
            
            <div id="notice-body">
                <p style="margin: 0 0 16px 0; color: #64748b; font-size: 14px; line-height: 1.6; text-align: left;">
                    You are requesting to change your region. This requires manual review by an administrator to ensure data integrity. To maintain system stability and testing consistency, you are limited in the number of region changes you can make.
                </p>
                
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; margin-bottom: 25px; text-align: left;">
                    <p style="margin: 0 0 8px 0; color: #b45309; font-size: 13px; font-weight: 700; line-height: 1.5;">
                        ⚠️ Please note: Your request will be reviewed and responded to within 24–48 hours.
                    </p>
                    <p style="margin: 0; color: #92400e; font-size: 13px; font-weight: 600; line-height: 1.5;">
                        We strongly advise you to ensure your new selection is accurate and final before submitting this request. Once submitted, you cannot change it until this request is processed.
                    </p>
                </div>
            </div>

            <button type="button" id="notice-action-btn" onclick="toggleChangeNoticeState()" style="width: 100%; background: #0284c7; color: #ffffff; border: none; padding: 15px; font-size: 15px; font-weight: 600; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2); transition: background 0.2s;">
                Understand & Proceed
            </button>
        </div>

        <!-- CỘT BÊN PHẢI -->
        <div id="form-column" style="flex: 1; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 35px; box-sizing: border-box; opacity: 0.4; pointer-events: none; transition: all 0.3s ease;">
            
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 14px; font-weight: 600; color: #0f172a;">
                Your Current Region: <span id="current-region-display" style="color: #0284c7; font-weight: 700;">Loading...</span>
            </div>

            <div style="margin-bottom: 20px;">
                <h3 style="margin: 0 0 8px 0; color: #0f172a; font-size: 22px; font-weight: 700;">Select Your Province</h3>
                <p style="margin: 0; color: #64748b; font-size: 14px;">Please choose one of the provinces below:</p>
            </div>
            
            <!-- [VỊ TRÍ 2] Cập nhật action sang trang đích và thêm input ẩn chứa current_province -->
            <form action="<?php echo esc_url( $target_page_url ); ?>" method="POST" onsubmit="return validateProvinceForm()">
                <input type="hidden" name="current_province" id="input-current-province" value="">

                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; max-height: 380px; overflow-y: auto; padding-right: 5px;">
                    <?php
                    if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
                        foreach ( $regions as $region ) {
                            $region_code = get_term_meta( $region->term_id, 'region_code', true );
                            if ( empty( $region_code ) ) {
                                $region_code = $region->slug;
                            }
                            echo '<label class="province-item-label" data-code="' . esc_attr( strtoupper( trim( $region_code ) ) ) . '" style="display: flex; align-items: center; padding: 14px 18px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.2s ease;">';
                            echo '<input type="radio" name="new_selected_province" value="' . esc_attr( $region_code ) . '" style="width: 18px; height: 18px; margin-right: 15px; cursor: pointer; accent-color: #0284c7;"> ';
                            echo '<span class="province-name-text" style="font-size: 15px; font-weight: 500; color: #334155;">' . esc_html( $region->name ) . '</span>';
                            echo '</label>';
                        }
                    } else {
                        echo '<p style="color: #ef4444; text-align: center; padding: 20px;">No provinces found.</p>';
                    }
                    ?>
                </div>
                
                <button type="submit" style="width: 100%; background: #0284c7; color: #ffffff; border: none; padding: 15px; font-size: 16px; font-weight: 600; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2); transition: background 0.2s;">
                    Send Request
                </button>
            </form>
        </div>

    </div>

    <script>
    const regionNameMap = <?php echo json_encode( $region_map ); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        try {
            const rawData = sessionStorage.getItem('snnj_user_info');
            if (rawData) {
                const userInfo = JSON.parse(rawData);
                const currentProvinceCode = userInfo.province ? userInfo.province.toUpperCase().trim() : '';

                // [VỊ TRÍ 3A] Đưa giá trị currentProvinceCode vào input ẩn
                const hiddenInput = document.getElementById('input-current-province');
                if (hiddenInput) {
                    hiddenInput.value = currentProvinceCode;
                }

                const displayEl = document.getElementById('current-region-display');
                if (currentProvinceCode && regionNameMap[currentProvinceCode]) {
                    displayEl.innerText = regionNameMap[currentProvinceCode];
                } else if (currentProvinceCode) {
                    displayEl.innerText = currentProvinceCode;
                } else {
                    displayEl.innerText = 'None';
                }

                if (currentProvinceCode) {
                    const labels = document.querySelectorAll('.province-item-label');
                    labels.forEach(label => {
                        const code = label.getAttribute('data-code');
                        if (code === currentProvinceCode) {
                            const radio = label.querySelector('input[type="radio"]');
                            const spanText = label.querySelector('.province-name-text');

                            if (radio) {
                                radio.disabled = true;
                                radio.style.cursor = 'not-allowed';
                            }
                            
                            label.style.background = '#f1f5f9';
                            label.style.borderColor = '#cbd5e1';
                            label.style.cursor = 'not-allowed';
                            
                            if (spanText) {
                                spanText.style.color = '#94a3b8';
                                spanText.style.fontWeight = '400';
                                spanText.innerHTML += ' <em style="font-size: 12px; font-style: normal; color: #0284c7; font-weight: 600;">(Current)</em>';
                            }
                        }
                    });
                }
            } else {
                document.getElementById('current-region-display').innerText = 'None';
            }
        } catch (e) {
            console.error('Error parsing snnj_user_info from sessionStorage:', e);
            document.getElementById('current-region-display').innerText = 'None';
        }
    });

    // [VỊ TRÍ 3B] Thêm hàm kiểm tra validate trước khi submit
    function validateProvinceForm() {
        const selectedRadio = document.querySelector('input[name="new_selected_province"]:checked');
        if (!selectedRadio) {
            alert('Please select a target province before sending your request.');
            return false;
        }
        return true;
    }

    var isChangeNoticeCollapsed = false;

    function toggleChangeNoticeState() {
        var formCol = document.getElementById('form-column');
        var noticeCol = document.getElementById('notice-column');
        var noticeBody = document.getElementById('notice-body');
        var noticeIcon = document.getElementById('notice-icon');
        var noticeTitle = document.getElementById('notice-title');
        var btn = document.getElementById('notice-action-btn');

        if (!isChangeNoticeCollapsed) {
            formCol.style.opacity = '1';
            formCol.style.pointerEvents = 'auto';
            
            noticeBody.style.display = 'none';
            noticeIcon.style.fontSize = '24px';
            noticeIcon.style.marginBottom = '5px';
            noticeIcon.innerHTML = 'ℹ️';
            
            noticeTitle.style.fontSize = '16px';
            noticeTitle.style.marginBottom = '10px';
            noticeTitle.innerText = 'Region Change Notice';
            
            btn.style.background = '#e2e8f0';
            btn.style.color = '#334155';
            btn.innerText = 'Click to Read Again 🔄';
            
            noticeCol.style.padding = '25px';
            noticeCol.style.background = '#f8fafc';
            noticeCol.style.borderColor = '#cbd5e1';
            
            isChangeNoticeCollapsed = true;
        } else {
            noticeBody.style.display = 'block';
            noticeIcon.style.fontSize = '40px';
            noticeIcon.style.marginBottom = '15px';
            noticeIcon.innerHTML = '📋';
            
            noticeTitle.style.fontSize = '22px';
            noticeTitle.style.marginBottom = '15px';
            noticeTitle.innerText = 'Region Change Request';
            
            btn.style.background = '#0284c7';
            btn.style.color = '#ffffff';
            btn.innerText = 'Understand & Proceed';
            
            noticeCol.style.padding = '35px';
            noticeCol.style.background = '#ffffff';
            noticeCol.style.borderColor = '#e2e8f0';
            
            isChangeNoticeCollapsed = false;
        }
    }
    </script>

    <style>
        .province-page-wrapper label:not(:has(input:disabled)):hover {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
        }
        .province-page-wrapper input[type="radio"]:checked + span {
            color: #0284c7 !important;
            font-weight: 600 !important;
        }
        .province-page-wrapper label:not(:has(input:disabled)):has(input[type="radio"]:checked) {
            background: #f0f9ff !important;
            border-color: #0284c7 !important;
        }
        @media (max-width: 768px) {
            .province-page-wrapper {
                flex-direction: column !important;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'province_change_request_form', 'render_province_change_request_form' );

add_shortcode( 'member_dashboard_info', 'render_member_dashboard_info' );
function render_member_dashboard_info() {
    $current_user_id = get_current_user_id();

    if ( ! $current_user_id ) {
        return '<p>Vui lòng đăng nhập để xem thông tin.</p>';
    }

    // 1. Lấy Province (tỉnh thành)
    $province = get_user_meta( $current_user_id, 'user_province', true );
    if ( empty( $province ) ) {
        $province = 'Chưa cập nhật';
    }

    // 2. Lấy danh sách các gói đã mua
    $purchased_packages = get_user_meta( $current_user_id, 'user_purchased_package', false );

    // Gom nội dung vào biến để trả về (return) cho shortcode
    $output = '<div style="padding: 20px; background: #f9f9f9; border: 1px solid #ddd; margin: 20px 0;">';
    $output .= '<h3>Thông tin thành viên (Test Data)</h3>';
    $output .= '<p><strong>Tỉnh thành (Province):</strong> ' . esc_html( $province ) . '</p>';
    $output .= '<p><strong>Danh sách gói đã mua:</strong></p>';

    if ( ! empty( $purchased_packages ) ) {
        $output .= '<ul>';
        foreach ( $purchased_packages as $package ) {
            $output .= '<li>' . esc_html( $package ) . '</li>';
        }
        $output .= '</ul>';
    } else {
        $output .= '<p>Bạn chưa mua gói nào.</p>';
    }
    $output .= '</div>';

    return $output;
}

add_shortcode( 'member_filtered_quizzes', 'render_member_filtered_quizzes' );
function render_member_filtered_quizzes() {
    $current_user_id = get_current_user_id();

    if ( ! $current_user_id ) {
        return '<p>Vui lòng đăng nhập để xem thông tin.</p>';
    }

    // 1. Lấy tỉnh của user (ví dụ: ON)
    $province = get_user_meta( $current_user_id, 'user_province', true );
    $province = strtoupper( trim( $province ) );

    // 2. Kiểm tra xem user đã mua gói nào chưa
    $purchased_packages = get_user_meta( $current_user_id, 'user_purchased_package', false );

    if ( empty( $purchased_packages ) ) {
        return '<p>Bạn chưa mua gói nào.</p>';
    }

    global $wpdb;
    // Đúng tên bảng trong database của bạn: snnj_aysquiz_quizcategories
    $table_name = $wpdb->prefix . 'aysquiz_quizcategories'; // hoặc gõ thẳng 'snnj_aysquiz_quizcategories'
    
    // Nếu tiền tố database của bạn là snnj_, ta gọi trực tiếp:
    $query = "SELECT id, title FROM snnj_aysquiz_quizcategories WHERE published = 1";
    $quizzes = $wpdb->get_results( $query );

    $filtered_quizzes = array();

    if ( ! empty( $quizzes ) ) {
        foreach ( $quizzes as $quiz ) {
            $id          = $quiz->id;
            $title       = trim( $quiz->title );
            $title_upper = strtoupper( $title );

            // Điều kiện 1: Nếu tiêu đề là SIGN
            if ( $title_upper === 'SIGN' ) {
                $filtered_quizzes[] = array( 'id' => $id, 'title' => $title );
                continue;
            }

            // Điều kiện 2: Kiểm tra 2 ký tự đầu là tỉnh (VD: ON) và ký tự thứ 3 là K (VD: ONK3)
            if ( ! empty( $province ) && strlen( $title ) >= 3 ) {
                $prefix_2chars = substr( $title_upper, 0, 2 );
                $third_char    = substr( $title_upper, 2, 1 );

                if ( $prefix_2chars === $province && $third_char === 'K' ) {
                    $filtered_quizzes[] = array( 'id' => $id, 'title' => $title );
                }
            }
        }
    }

    // Hiển thị kết quả ra ngoài giao diện
    $output = '<div style="padding: 20px; background: #fff; border: 1px solid #ddd; margin: 20px 0;">';
    $output .= '<h3>Danh sách bộ đề của bạn (Tỉnh: ' . esc_html( $province ) . ')</h3>';

    if ( ! empty( $filtered_quizzes ) ) {
        $output .= '<ul>';
        foreach ( $filtered_quizzes as $q ) {
            $output .= '<li><strong>ID: ' . esc_html( $q['id'] ) . '</strong> - ' . esc_html( $q['title'] ) . '</li>';
        }
        $output .= '</ul>';
    } else {
        $output .= '<p>Không tìm thấy bộ đề nào khớp với tỉnh <strong>' . esc_html( $province ) . '</strong> và điều kiện ký tự K.</p>';
    }
    
    $output .= '</div>';

    return $output;
}

// CUSTOM_DASHBOARD_QUIZ_RENDER_2026
// add_shortcode( 'member_dashboard_quizzes', 'render_member_dashboard_quizzes' );
// function render_member_dashboard_quizzes() {
//     $current_user_id = get_current_user_id();

//     if ( ! $current_user_id ) {
//         return '<p style="text-align: center; color: #666;">Please log in to view your dashboard.</p>';
//     }

//     $province = get_user_meta( $current_user_id, 'user_province', true );
//     $province = strtoupper( trim( $province ) );

//     $purchased_packages = get_user_meta( $current_user_id, 'user_purchased_package', false );

//     global $wpdb;
//     $query = "SELECT id, title FROM snnj_aysquiz_quizcategories WHERE published = 1";
//     $quizzes = $wpdb->get_results( $query );

//     $filtered_quizzes = array();

//     if ( ! empty( $quizzes ) ) {
//         $count_k = 1;
//         $count_c = 1;
//         $count_m = 1;

//         foreach ( $quizzes as $quiz ) {
//             $id          = $quiz->id;
//             $title       = trim( $quiz->title );
//             $title_upper = strtoupper( $title );

//             if ( $title_upper === 'SIGN' ) {
//                 $filtered_quizzes[] = array(
//                     'id'          => $id,
//                     'displayName' => 'Special Sign Practice',
//                     'original'    => $title
//                 );
//                 continue;
//             }

//             if ( ! empty( $province ) && strlen( $title ) >= 3 ) {
//                 $prefix_2chars = substr( $title_upper, 0, 2 );
//                 $third_char    = substr( $title_upper, 2, 1 );

//                 if ( $prefix_2chars === $province ) {
//                     if ( $third_char === 'K' ) {
//                         $filtered_quizzes[] = array(
//                             'id'          => $id,
//                             'displayName' => 'Knowledge Practice Test ' . $count_k,
//                             'original'    => $title
//                         );
//                         $count_k++;
//                     } elseif ( $third_char === 'C' ) {
//                         $filtered_quizzes[] = array(
//                             'id'          => $id,
//                             'displayName' => 'Commercial Driver Test ' . $count_c,
//                             'original'    => $title
//                         );
//                         $count_c++;
//                     } elseif ( $third_char === 'M' ) {
//                         $filtered_quizzes[] = array(
//                             'id'          => $id,
//                             'displayName' => 'Motorcycle Test ' . $count_m,
//                             'original'    => $title
//                         );
//                         $count_m++;
//                     }
//                 }
//             }
//         }
//     }

//     $output = '<div style="background: #fffcf0; border-radius: 16px; padding: 24px; border: 1px solid #fae19c; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">';
    
//     $inner_content = '';
//     $inner_content .= '<h3 style="font-size: 18px; font-weight: 700; color: #2c3e50; margin-top: 0; margin-bottom: 15px;">Purchased Packages</h3>';
//     $inner_content .= '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">';
    
//     if ( ! empty( $purchased_packages ) ) {
//         foreach ( $purchased_packages as $pkg ) {
//             $inner_content .= '<div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">';
//             $inner_content .= '<div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">' . esc_html( $pkg ) . '</div>';
//             $inner_content .= '<div style="font-size: 12px; color: #27ae60; font-weight: 500;">✓ Active / Owned</div>';
//             $inner_content .= '</div>';
//         }
//     } else {
//         $inner_content .= '<div style="grid-column: span 2; color: #7f8c8d; font-size: 14px;">No packages purchased yet.</div>';
//     }
//     $inner_content .= '</div>';

//     $inner_content .= '<div style="background: #fff; padding: 12px 15px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 20px; font-size: 13px; color: #555; text-align: center;">';
//     $inner_content .= 'All practice tests below have been fully unlocked for your preparation.';
//     $inner_content .= '</div>';

//     $inner_content .= '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">';

//     if ( ! empty( $filtered_quizzes ) ) {
//         foreach ( $filtered_quizzes as $q ) {
//             // --- MÃ HÓA ID THÀNH TOKEN BẢO MẬT ---
//             $secret_key     = wp_salt( 'auth' );
//             $encrypted_data = openssl_encrypt( $q['id'], 'AES-128-ECB', $secret_key );
//             $token          = rtrim( strtr( base64_encode( $encrypted_data ), '+/', '-_' ), '=' );

//             // Đường dẫn trỏ tới trang con /memberhome/exam/ kèm theo token bảo mật
//             $quiz_link      = site_url( '/memberhome/exam/?quiz_token=' . $token );

//             // Thay thẻ div bằng thẻ a để làm nút bấm tương tác
//             $inner_content .= '<a href="' . esc_url( $quiz_link ) . '" style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-decoration: none; transition: all 0.2s ease;">'; 
//             $inner_content .= '<span style="font-weight: 600; color: #2d3748; font-size: 14px;">' . esc_html( $q['displayName'] ) . '</span>';
//             $inner_content .= '<span style="background: #27ae60; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>';
//             $inner_content .= '</a>';
//         }
//     } else {
//         $inner_content .= '<div style="grid-column: span 2; color: #7f8c8d; font-size: 14px; text-align: center;">No available practice tests found for your province (' . esc_html( $province ) . ').</div>';
//     }

//     $inner_content .= '</div>';
    
//     $output .= $inner_content;
//     $output .= '</div>';

//     return $output;
// }

// Shortcode hiển thị danh sách gói đã mua và các bài quiz phù hợp theo tỉnh bang + SIGN

add_shortcode('snnj_user_profile', 'snnj_render_user_profile_shortcode');
function snnj_render_user_profile_shortcode() {
    $rest_nonce = wp_create_nonce('wp_rest');
    ob_start();
    ?>
    <div id="snnj-profile-wrapper" class="snnj-profile-card">
        <div class="snnj-profile-loading">Loading account information...</div>
    </div>

    <style>
        .snnj-profile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 36px;
            max-width: 620px;
            margin: 0 auto;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            font-family: inherit;
        }
        .snnj-profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        .snnj-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 6px 16px rgba(3, 105, 161, 0.25);
            flex-shrink: 0;
        }
        .snnj-user-meta h2 {
            margin: 0 0 4px 0;
            font-size: 22px;
            color: #0f172a;
            font-weight: 700;
        }
        .snnj-user-meta p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        .snnj-form-group {
            margin-bottom: 22px;
        }
        .snnj-form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .snnj-form-control {
            width: 100%;
            padding: 13px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 15px;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .snnj-form-control:focus {
            outline: none;
            border-color: #0369a1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(3, 105, 161, 0.1);
        }
        .snnj-form-control:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }
        .snnj-row {
            display: flex;
            gap: 16px;
        }
        .snnj-row .snnj-form-group {
            flex: 1;
        }
        .snnj-packages-box {
            background: linear-gradient(135deg, #fdfbf7 0%, #f4f0ea 100%);
            border: 1px solid #e7e2d8;
            border-radius: 14px;
            padding: 22px;
            margin-top: 28px;
        }
        .snnj-packages-title {
            font-size: 13px;
            font-weight: 700;
            color: #78350f;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .snnj-package-item {
            background: #ffffff;
            border: 1px solid #e6decb;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        }
        .snnj-package-item:last-child {
            margin-bottom: 0;
        }
        .snnj-actions {
            margin-top: 32px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 14px;
            border-top: 1px solid #f1f5f9;
            padding-top: 24px;
        }
        .snnj-btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .snnj-btn-secondary {
            background: transparent;
            color: #64748b;
        }
        .snnj-btn-secondary:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .snnj-btn-primary {
            background: #0369a1;
            color: #fff;
            box-shadow: 0 4px 12px rgba(3, 105, 161, 0.25);
        }
        .snnj-btn-primary:hover {
            background: #0284c7;
        }
    </style>

    <script>
    // Hàm hiển thị Toast Popup (chỉ dùng cho nút Save)
    window.snnjShowToast = function(message, type = 'success') {
        let toast = document.getElementById('snnj-global-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'snnj-global-toast';
            document.body.appendChild(toast);
            
            toast.style.cssText = `
                position: fixed;
                bottom: 30px;
                right: 30px;
                padding: 14px 24px;
                border-radius: 12px;
                color: #fff;
                font-size: 14px;
                font-weight: 600;
                z-index: 99999;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                transform: translateY(100px);
                opacity: 0;
                font-family: inherit;
            `;
        }

        toast.style.background = type === 'success' ? '#0369a1' : '#ef4444';
        toast.textContent = message;
        
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
        }, 3000);
    };

    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById("snnj-profile-wrapper");
        const rawData = sessionStorage.getItem("snnj_user_info");
        const restNonce = "<?php echo esc_js($rest_nonce); ?>";

        if (!rawData) {
            container.innerHTML = "<p style='color: #ef4444; text-align:center;'>Session data not found.</p>";
            return;
        }

        try {
            const user = JSON.parse(rawData);
            const initials = ((user.first_name?.[0] || "") + (user.last_name?.[0] || "")).toUpperCase() || "U";
            const fullName = (user.first_name || user.last_name) ? `${user.first_name || ""} ${user.last_name || ""}` : (user.name || "User");

            let packagesHtml = "";
            if (user.packages && user.packages.length > 0) {
                user.packages.forEach(pkg => {
                    packagesHtml += `<div class="snnj-package-item"><span>👑 ${pkg}</span> <span style="font-size:11px; background:#fef3c7; color:#b45309; padding:3px 10px; border-radius:99px; font-weight:700;">ACTIVE</span></div>`;
                });
            } else {
                packagesHtml = "<p style='color:#78350f; font-size:14px; margin:0;'>No active packages found.</p>";
            }

            container.innerHTML = `
                <div class="snnj-profile-header">
                    <div class="snnj-avatar">${initials}</div>
                    <div class="snnj-user-meta">
                        <h2 id="snnj-display-name">${fullName}</h2>
                        <p>${user.email || ""}</p>
                    </div>
                </div>
                
                <div class="snnj-row">
                    <div class="snnj-form-group">
                        <label>First Name</label>
                        <input type="text" id="snnj-first-name" class="snnj-form-control" value="${user.first_name || ""}">
                    </div>
                    <div class="snnj-form-group">
                        <label>Last Name</label>
                        <input type="text" id="snnj-last-name" class="snnj-form-control" value="${user.last_name || ""}">
                    </div>
                </div>

                <div class="snnj-form-group">
                    <label>Email Address</label>
                    <input type="email" class="snnj-form-control" value="${user.email || ""}" disabled>
                </div>

                <div class="snnj-packages-box">
                    <div class="snnj-packages-title">🎁 Current Packages</div>
                    ${packagesHtml}
                </div>

                <div class="snnj-actions">
                    <button type="button" id="snnj-btn-discard" class="snnj-btn snnj-btn-secondary">Discard Changes</button>
                    <button type="button" id="snnj-btn-save" class="snnj-btn snnj-btn-primary">Save</button>
                </div>
            `;

            const btnDiscard = document.getElementById("snnj-btn-discard");
            const btnSave = document.getElementById("snnj-btn-save");
            const inputFirstName = document.getElementById("snnj-first-name");
            const inputLastName = document.getElementById("snnj-last-name");
            const displayName = document.getElementById("snnj-display-name");
            const avatarBox = container.querySelector(".snnj-avatar");

            // Nút Discard khôi phục lại giá trị cũ mà không gọi popup
            btnDiscard.addEventListener("click", function() {
                inputFirstName.value = user.first_name || "";
                inputLastName.value = user.last_name || "";
            });

            // Nút Save gọi API và hiển thị Toast Popup
btnSave.addEventListener("click", function() {
    const newFirstName = inputFirstName.value.trim();
    const newLastName = inputLastName.value.trim();

    // Regex kiểm tra chỉ chứa chữ cái (có dấu Tiếng Việt) và khoảng trắng, độ dài từ 1 đến 30 ký tự
    const nameRegex = /^[A-Za-zÀ-ỹ\s]{1,30}$/;

    if (!nameRegex.test(newFirstName) || !nameRegex.test(newLastName)) {
        window.snnjShowToast("Tên đổi không hợp lệ", "error");
        return;
    }

    btnSave.disabled = true;
    btnSave.textContent = "Saving...";

    fetch('/wp-json/snnj/v1/update-profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': restNonce
        },
        body: JSON.stringify({
            first_name: newFirstName,
            last_name: newLastName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            user.first_name = newFirstName;
            user.last_name = newLastName;
            sessionStorage.setItem("snnj_user_info", JSON.stringify(user));

            const newFull = (newFirstName || newLastName) ? `${newFirstName} ${newLastName}` : "User";
            displayName.textContent = newFull;
            avatarBox.textContent = ((newFirstName?.[0] || "") + (newLastName?.[0] || "")).toUpperCase() || "U";

            window.snnjShowToast("Profile updated successfully!", "success");
        } else {
            window.snnjShowToast(data.message || "Tên đổi không hợp lệ", "error");
        }
    })
    .catch(error => {
        window.snnjShowToast("Tên đổi không hợp lệ", "error");
    })
    .finally(() => {
        btnSave.disabled = false;
        btnSave.textContent = "Save";
    });
});

} catch (e) {
    container.innerHTML = "<p style='color: #ef4444; text-align:center;'>Error parsing user session data.</p>";
}
});
</script>
<?php
return ob_get_clean();
}

add_action('rest_api_init', function () {
    register_rest_route('snnj/v1', '/update-profile', [
        'methods' => 'POST',
        'callback' => 'snnj_handle_update_profile',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);
});

function snnj_handle_update_profile($request) {$user_id = get_current_user_id();
    
    if (!$user_id) {
        return new WP_REST_Response(['success' => false, 'message' => 'Unauthorized.'], 401);
    }

    $params =$request->get_json_params();
    $first_name = sanitize_text_field($params['first_name'] ?? '');
    $last_name = sanitize_text_field($params['last_name'] ?? '');

    // Validate chặt chẽ phía Server
    $pattern = '/^[A-Za-zÀ-ỹ\s]{1,30}$/u';
    if (!preg_match($pattern,$first_name) || !preg_match($pattern,$last_name)) {
        return new WP_REST_Response(['success' => false, 'message' => 'Tên đổi không hợp lệ'], 400);
    }

    $current_first = get_user_meta($user_id, 'first_name', true);
    $current_last = get_user_meta($user_id, 'last_name', true);

    if ($current_first !==$first_name) {
        update_user_meta($user_id, 'first_name',$first_name);
    }

    if ($current_last !==$last_name) {
        update_user_meta($user_id, 'last_name',$last_name);
    }

    return new WP_REST_Response(['success' => true, 'message' => 'Updated successfully.'], 200);
}

add_shortcode( 'member_dashboard_quizzes', 'render_member_dashboard_quizzes' );
function render_member_dashboard_quizzes() {
    $current_user_id = get_current_user_id();

    if ( ! $current_user_id ) {
        return '<p style="text-align: center; color: #666;">Please log in to view your dashboard.</p>';
    }

    $province = get_user_meta( $current_user_id, 'user_province', true );
    $province = strtoupper( trim( $province ) );

    // ==========================================
    // 1. LÀM SẠCH VÀ CHỌN GÓI CÓ THỜI GIAN XA NHẤT (LỌC BỎ GÓI CŨ)
    // ==========================================
    $raw_packages = get_user_meta( $current_user_id, 'user_purchased_package', false );
    $today = date( 'Y-m-d' ); // Ngày hiện tại chuẩn YYYY-MM-DD

    // Mảng lưu ngày hết hạn xa nhất cho từng loại gói
    $max_expiry = array(
        'knowledge'  => '',
        'commercial' => '',
        'motorcycle' => ''
    );

    if ( ! empty( $raw_packages ) ) {
        foreach ( $raw_packages as $pkg ) {
            $pkg_string = is_array( $pkg ) ? implode( ' ', $pkg ) : $pkg;
            
            // Tách chuỗi để lấy ngày ở cuối (10 ký tự cuối: YYYY-MM-DD)
            if ( preg_match( '/(\d{4}-\d{2}-\d{2})$/', trim( $pkg_string ), $matches ) ) {
                $expiry_date  = $matches[1];
                $package_name = trim( str_replace( ' - ' . $expiry_date, '', $pkg_string ) );
            } else {
                $expiry_date  = '2099-12-31'; // Mặc định vĩnh viễn nếu không có ngày
                $package_name = $pkg_string;
            }

            // Nếu gói đã hết hạn so với hôm nay thì bỏ qua
            if ( $expiry_date < $today ) {
                continue;
            }

            $p_lower = strtolower( $package_name );

            // So sánh và chỉ giữ lại ngày hết hạn xa nhất (mới nhất) cho từng loại
            if ( strpos( $p_lower, 'knowledge' ) !== false ) {
                if ( empty( $max_expiry['knowledge'] ) || $expiry_date > $max_expiry['knowledge'] ) {
                    $max_expiry['knowledge'] = $expiry_date;
                }
            }
            if ( strpos( $p_lower, 'commercial' ) !== false ) {
                if ( empty( $max_expiry['commercial'] ) || $expiry_date > $max_expiry['commercial'] ) {
                    $max_expiry['commercial'] = $expiry_date;
                }
            }
            if ( strpos( $p_lower, 'motorcycle' ) !== false ) {
                if ( empty( $max_expiry['motorcycle'] ) || $expiry_date > $max_expiry['motorcycle'] ) {
                    $max_expiry['motorcycle'] = $expiry_date;
                }
            }
        }
    }

    // ==========================================
    // 2. TẠO CỜ KIỂM TRA QUYỀN VÀ TÍNH SỐ NGÀY CÒN LẠI (DAYS LEFT)
    // ==========================================
    $has_knowledge  = ! empty( $max_expiry['knowledge'] );
    $has_commercial = ! empty( $max_expiry['commercial'] );
    $has_motorcycle = ! empty( $max_expiry['motorcycle'] );

    // Hàm phụ trợ để tính và format thời gian hiển thị (Days left / Lifetime)
    $get_package_badge = function( $expiry_date ) use ( $today ) {
        if ( empty( $expiry_date ) ) return '';
        
        $datetime1 = new DateTime( $today );
        $datetime2 = new DateTime( $expiry_date );
        $interval  = $datetime1->diff( $datetime2 );
        $days_left = (int) $interval->format( '%r%a' );

        if ( $days_left > 18000 ) {
            return '<span style="color: #2b6cb0; font-weight: 600; font-size: 13px; margin-left: 8px;">(Lifetime)</span>';
        } elseif ( $days_left >= 0 ) {
            return '<span style="color: #e53e3e; font-weight: 600; font-size: 13px; margin-left: 8px;">(' . $days_left . ' days left)</span>';
        } else {
            return '<span style="color: #a0aec0; font-weight: 600; font-size: 13px; margin-left: 8px;">(Expired)</span>';
        }
    };

    // Tạo sẵn các chuỗi badge thời hạn cho từng gói để đưa ra giao diện
    $badge_knowledge  = $has_knowledge ? $get_package_badge( $max_expiry['knowledge'] ) : '';
    $badge_commercial = $has_commercial ? $get_package_badge( $max_expiry['commercial'] ) : '';
    $badge_motorcycle = $has_motorcycle ? $get_package_badge( $max_expiry['motorcycle'] ) : '';

    // ==========================================
    // 3. QUERY DATABASE
    // ==========================================
    global $wpdb;
    $table_name = $wpdb->prefix . 'aysquiz_quizcategories';
    
    if ( ! empty( $province ) ) {
        $query = $wpdb->prepare(
            "SELECT id, title FROM {$table_name} WHERE published = 1 AND (UPPER(title) LIKE %s OR UPPER(title) = 'SIGN')",
            '%' . $wpdb->esc_like( $province ) . '%'
        );
    } else {
        $query = "SELECT id, title FROM {$table_name} WHERE published = 1 AND UPPER(title) = 'SIGN'";
    }
    
    $quizzes = $wpdb->get_results( $query );

    $commercial_tests = array();
    $knowledge_tests  = array();
    $motorcycle_tests = array();
    $sign_tests       = array();

    if ( ! empty( $quizzes ) ) {
        $count_k = 1;
        $count_c = 1;
        $count_m = 1;

        foreach ( $quizzes as $quiz ) {
            $id          = $quiz->id;
            $title       = trim( $quiz->title );
            $title_upper = strtoupper( $title );

            $secret_key     = wp_salt( 'auth' );
            $encrypted_data = openssl_encrypt( $id, 'AES-128-ECB', $secret_key );
            $token          = rtrim( strtr( base64_encode( $encrypted_data ), '+/', '-_' ), '=' );
            $quiz_link      = site_url( '/memberhome/exam/?quiz_token=' . $token );

            if ( $title_upper === 'SIGN' ) {
                $sign_tests[] = array(
                    'link'        => $quiz_link,
                    'displayName' => 'Special Sign Practice',
                    'category'    => 'sign'
                );
                continue;
            }

            if ( ! empty( $province ) && strlen( $title ) >= 3 ) {
                $prefix_2chars = substr( $title_upper, 0, 2 );
                $third_char    = substr( $title_upper, 2, 1 );

                if ( $prefix_2chars === $province ) {
                    // ==========================================
                    // 4. ÁP DỤNG CỜ ĐỂ PHÂN LOẠI
                    // ==========================================
                    if ( $third_char === 'K' && $has_knowledge ) {
                        $knowledge_tests[] = array(
                            'link'        => $quiz_link,
                            'displayName' => 'Knowledge Practice Test ' . $count_k,
                            'category'    => 'knowledge'
                        );
                        $count_k++;

                    } elseif ($third_char !== 'M' && $has_commercial) {
                        $commercial_tests[] = array(
                            'link'        => $quiz_link,
                            'displayName' => 'Commercial Driver Test ' . $count_c,
                            'category'    => 'commercial'
                        );
                        $count_c++;

                    } elseif (( $third_char === 'M' || $third_char === 'K' ) && $has_motorcycle ) {
                        $motorcycle_tests[] = array(
                            'link'        => $quiz_link,
                            'displayName' => 'Motorcycle Test ' . $count_m,
                            'category'    => 'motorcycle'
                        );
                        $count_m++;
                    }
                }
            }
        }
    }

    // ==========================================
    // 5. HIỂN THỊ GIAO DIỆN HTML/JS
    // ==========================================
    $output = '<div class="member-dashboard-container" style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
    
    $output .= '<h2 style="font-size: 22px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">Available Practice Tests</h2>';
    
    $output .= '<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">';
    
    $output .= '<div style="display: flex; gap: 8px; flex-wrap: wrap;" id="quiz-filter-tabs">';
    $output .= '<button class="quiz-tab-btn active" data-filter="all" style="background: #2b6cb0; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">All Tests</button>';
    
    if ( ! empty( $commercial_tests ) || ! empty( $has_commercial ) ) {
        $output .= '<button class="quiz-tab-btn" data-filter="commercial" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">Commercial Driver</button>';
    }
    if ( ! empty( $knowledge_tests ) || ! empty( $has_knowledge ) ) {
        $output .= '<button class="quiz-tab-btn" data-filter="knowledge" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">Knowledge</button>';
    }
    if ( ! empty( $motorcycle_tests ) || ! empty( $has_motorcycle ) ) {
        $output .= '<button class="quiz-tab-btn" data-filter="motorcycle" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">Motorcycle</button>';
    }
    $output .= '</div>';

    $output .= '<div style="position: relative;">';
    $output .= '<input type="text" id="quiz-search-input" placeholder="Search tests..." style="padding: 8px 14px 8px 34px; border: 1px solid #cbd5e0; border-radius: 20px; font-size: 14px; outline: none; width: 400px; transition: all 0.2s;">';
    $output .= '<span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a0aec0; font-size: 14px;">🔍</span>';
    $output .= '</div>';

    $output .= '</div>';

    $output .= '<div id="quiz-items-wrapper">';

    $render_cards = function($title_section, $items, $cat_slug) {
        if ( empty($items) ) return '';
        $html = '<div class="quiz-section-group" data-category="' . esc_attr($cat_slug) . '" style="margin-bottom: 25px;">';
        
        $html .= '<div class="section-toggle-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; cursor: pointer; user-select: none; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #edf2f7;">';
        // Ở đây cho phép hiển thị HTML của badge nằm trong tiêu đề
        $html .= '<h3 style="font-size: 16px; font-weight: 600; color: #2d3748; margin: 0;">' . $title_section . '</h3>';
        $html .= '<span class="toggle-arrow" style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; background: #edf2f7; border-radius: 50%; font-size: 12px; color: #4a5568; transition: transform 0.2s; font-weight: bold;">▼</span>';
        $html .= '</div>';

        $html .= '<div class="section-content-grid" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px;">';
        foreach ( $items as $item ) {
            $html .= '<a href="' . esc_url( $item['link'] ) . '" class="member-exam-card quiz-pagination-item" data-category="' . esc_attr($cat_slug) . '" data-title="' . esc_attr( strtolower($item['displayName']) ) . '" style="margin-bottom: 0;">';
            $html .= '<span class="exam-title">' . esc_html( $item['displayName'] ) . '</span>';
            $html .= '</a>';
        }
        $html .= '</div></div>';
        return $html;
    };

    // Nối thêm các biến badge ($badge_commercial, $badge_knowledge, $badge_motorcycle) vào tiêu đề
    $output .= $render_cards( 'Commercial Driver Package Tests ' . $badge_commercial, $commercial_tests, 'commercial' );
    $output .= $render_cards( 'Knowledge Package Tests ' . $badge_knowledge, $knowledge_tests, 'knowledge' );
    
    if ( ! empty( $motorcycle_tests ) ) {
        $output .= $render_cards( 'Motorcycle Package Tests ' . $badge_motorcycle, $motorcycle_tests, 'motorcycle' );
    } elseif ( ! empty( $has_motorcycle ) ) {
        $output .= '<div class="quiz-section-group" data-category="motorcycle" style="margin-bottom: 25px;">';
        $output .= '<div class="section-toggle-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #edf2f7;">';
        $output .= '<h3 style="font-size: 16px; font-weight: 600; color: #2d3748; margin: 0;">Motorcycle Package Tests ' . $badge_motorcycle . '</h3>';
        $output .= '</div>';
        $output .= '<p style="color: #718096; font-size: 14px; padding: 10px;">No tests found matching this package yet.</p>';
        $output .= '</div>';
    }

    $output .= $render_cards( 'Bonus: Special Sign Practice', $sign_tests, 'sign' );

    $output .= '</div>';
    $output .= '</div>';

    $output .= '
    <style>
    @media (max-width: 1024px) { .section-content-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; } }
    @media (max-width: 640px) { 
        .section-content-grid { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; } 
        #quiz-search-input { width: 100% !important; } 
    }
    .section-toggle-header:hover { background: #edf2f7 !important; }

    .member-exam-card {
        transition: all 0.25s ease-in-out !important;
    }
    .member-exam-card:hover {
        background-color: #f0f7ff !important;
        border-color: #2b6cb0 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(43, 108, 176, 0.1);
    }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentFilter = "all";
        let searchQuery = "";

        const allCards = Array.from(document.querySelectorAll(".quiz-pagination-item"));
        const sections = document.querySelectorAll(".quiz-section-group");
        const tabBtns = document.querySelectorAll(".quiz-tab-btn");
        const searchInput = document.getElementById("quiz-search-input");

        document.querySelectorAll(".section-toggle-header").forEach(header => {
            header.addEventListener("click", function() {
                const grid = this.nextElementSibling;
                if (!grid || !grid.classList.contains("section-content-grid")) return;
                const arrow = this.querySelector(".toggle-arrow");
                if (grid.style.display === "none") {
                    grid.style.display = "grid";
                    if (arrow) arrow.style.transform = "rotate(0deg)";
                } else {
                    grid.style.display = "none";
                    if (arrow) arrow.style.transform = "rotate(-90deg)";
                }
            });
        });

        function render() {
            allCards.forEach(card => {
                const cat = card.getAttribute("data-category");
                const title = card.getAttribute("data-title");

                const matchTab = (currentFilter === "all" || cat === currentFilter);
                const matchSearch = (title.includes(searchQuery));

                if (matchTab && matchSearch) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });

            sections.forEach(sec => {
                const visibleInSec = sec.querySelectorAll(".quiz-pagination-item");
                let hasVisible = false;
                visibleInSec.forEach(card => {
                    if (card.style.display === "flex") hasVisible = true;
                });

                const secCat = sec.getAttribute("data-category");
                const matchTabSec = (currentFilter === "all" || secCat === currentFilter);

                if (visibleInSec.length === 0) {
                    sec.style.display = matchTabSec ? "block" : "none";
                } else {
                    sec.style.display = (matchTabSec && hasVisible) ? "block" : "none";
                }
            });
        }

        tabBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                tabBtns.forEach(b => {
                    b.style.background = "#edf2f7";
                    b.style.color = "#4a5568";
                });
                this.style.background = "#2b6cb0";
                this.style.color = "#fff";

                currentFilter = this.getAttribute("data-filter");
                render();
            });
        });

        searchInput.addEventListener("input", function() {
            searchQuery = this.value.toLowerCase().trim();
            render();
        });

        render();
    });
    </script>';

    return $output;
}

 // ==========================================
    // GIỜI HẠN TRUY CẬP PROVINCE
    // ==========================================
add_action('template_redirect', 'block_province_page_if_selected');
function block_province_page_if_selected() {
    if (is_page('provinces') && is_user_logged_in()) {
        $user_id = get_current_user_id();
        $has_province = get_user_meta($user_id, 'user_province', true);

        if (!empty($has_province)) {
            wp_redirect(home_url('/memberhome/'));
            exit;
        }
    }
}