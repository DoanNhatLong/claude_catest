<?php
/**
 * Hiển thị danh sách các card lựa chọn loại đề thi và tự động điều hướng sang trang exam kèm mã code.
 */
add_shortcode('show_exam_categories', 'render_exam_category_list');
function render_exam_category_list() {
    $exam_categories = get_terms( array(
        'taxonomy'   => 'exam_category',
        'hide_empty' => false,
    ));

    if ( empty( $exam_categories ) || is_wp_error( $exam_categories ) ) {
        return '<p>Chưa có loại đề thi nào được tạo trong hệ thống.</p>';
    }

    $output = '<style>
      .custom-driver-grid-colored {
          list-style: none !important;
          padding: 0 !important;
          display: grid !important;
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 20px !important;
          margin-top: 20px !important;
      }
      .custom-driver-grid-colored li {
          background: #ffffff !important;
          border: 1px solid #e2e8f0 !important;
          border-radius: 14px !important;
          transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02) !important;
          overflow: hidden;
          position: relative;
      }
      .custom-driver-grid-colored li:nth-child(7n+1):hover {
          background: #eff6ff !important;
          border-color: #3b82f6 !important;
          box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+2):hover {
          background: #fdf2f8 !important;
          border-color: #ec4899 !important;
          box-shadow: 0 12px 25px -5px rgba(236, 72, 153, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+3):hover {
          background: #fefce8 !important;
          border-color: #eab308 !important;
          box-shadow: 0 12px 25px -5px rgba(234, 179, 8, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+4):hover {
          background: #f0fdf4 !important;
          border-color: #22c55e !important;
          box-shadow: 0 12px 25px -5px rgba(34, 197, 94, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+5):hover {
          background: #faf5ff !important;
          border-color: #a855f7 !important;
          box-shadow: 0 12px 25px -5px rgba(168, 85, 247, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+6):hover {
          background: #fff7ed !important;
          border-color: #f97316 !important;
          box-shadow: 0 12px 25px -5px rgba(249, 115, 22, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li:nth-child(7n+0):hover {
          background: #ecfeff !important;
          border-color: #06b6d4 !important;
          box-shadow: 0 12px 25px -5px rgba(6, 182, 212, 0.2) !important;
          transform: translateY(-4px) !important;
      }
      .custom-driver-grid-colored li a {
          display: flex !important;
          align-items: center !important;
          padding: 20px 22px !important;
          color: #1e293b !important;
          font-weight: 600 !important;
          font-size: 15px !important;
          text-decoration: none !important;
          gap: 16px;
      }
      .card-badge {
          width: 40px;
          height: 40px;
          background: #f1f5f9;
          color: #475569;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 700;
          font-size: 15px;
          flex-shrink: 0;
          transition: all 0.3s ease;
      }
      .custom-driver-grid-colored li:nth-child(7n+1):hover .card-badge { background: #3b82f6; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+2):hover .card-badge { background: #ec4899; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+3):hover .card-badge { background: #eab308; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+4):hover .card-badge { background: #22c55e; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+5):hover .card-badge { background: #a855f7; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+6):hover .card-badge { background: #f97316; color: #fff; }
      .custom-driver-grid-colored li:nth-child(7n+0):hover .card-badge { background: #06b6d4; color: #fff; }
	  @media (max-width: 768px) {
    .custom-driver-grid-colored {
        grid-template-columns: 1fr !important;
    }
}
    </style>';

    $output .= '<ul class="custom-driver-grid-colored" id="driver-test-list">';

    foreach ( $exam_categories as$cat ) {
        $exam_code = get_term_meta($cat->term_id, 'region_code', true );
        $class_val = !empty($exam_code ) ? $exam_code : strtoupper( substr($cat->slug, 0, 1 ) );
        $badge_char = strtoupper( substr($class_val, 0, 1 ) );

        $output .= '<li>';
        $output .= '<a href="#" data-class="' . esc_attr( $class_val ) . '">';
        $output .= '<span class="card-badge">' . esc_html( $badge_char ) . '</span>';
        $output .= esc_html($cat->name );
        $output .= '</a>';$output .= '</li>';
    }

    $output .= '</ul>';

    $output .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const regionVal = (urlParams.get("region") || "").toUpperCase();
        const targetBaseUrl = "https://catest.ca/exam/";

        const links = document.querySelectorAll("#driver-test-list a");
        links.forEach(link => {
            const classVal = link.getAttribute("data-class");
            if (classVal === "SIGN") {
                link.href = `${targetBaseUrl}?code=SIGN`;
            } else if (regionVal) {
                link.href = `${targetBaseUrl}?code=${regionVal}${classVal}`;
            } else {
                link.href = `${targetBaseUrl}?code=${classVal}`;
            }
        });
    });
    </script>';

    return $output;
}

add_shortcode('show_exam_list', 'render_exam_selection_list');

function render_exam_selection_list() {
    $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';

    if (empty($code)) {
        return '<p>Please select a region and test category from the navigation menu.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'aysquiz_quizcategories';$categories = $wpdb->get_results($wpdb->prepare(
        "SELECT id, title FROM {$table_name} WHERE title LIKE %s AND published = 1",
        '%' . $wpdb->esc_like($code) . '%'
    ));

    if (empty($categories)) {
        return '<p>No practice tests found </p>';
    }

    $output = '<h3>Select practice test version </h3>';$output .= '<ul class="exam-list-options" style="list-style: none; padding: 0;">';
    
    foreach ($categories as $cat) {$exam_version = str_replace($code, '',$cat->title);
        if (empty($exam_version)) {$exam_version = 'Official'; 
        }

        // --- MÃ HÓA ID THÀNH TOKEN BẢO MẬT ---
        $secret_key = wp_salt('auth');$encrypted_data = openssl_encrypt($cat->id, 'AES-128-ECB',$secret_key);
        $token = rtrim(strtr(base64_encode($encrypted_data), '+/', '-_'), '=');

        // Thay vì truyền ?quiz_cat_id=16, ta truyền ?quiz_token=...
        $quiz_link = site_url('/exam/take-exam/?quiz_token=' . $token);$output .= '<li style="margin-bottom: 10px;">';
        $output .= '<a href="' . esc_url($quiz_link) . '" style="display: block; padding: 12px 20px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #333; font-weight: bold;">';
        $output .= 'Practice Test - Version ' . esc_html($exam_version);
        $output .= '</a>';$output .= '</li>';
    }
    
    $output .= '</ul>';

    return $output;
}
    // --- BẢO MẬT KHI RENDER BÀI THI ---
add_shortcode('render_quiz_by_url', 'display_quiz_from_url_id');

function display_quiz_from_url_id() {
    $token = isset($_GET['quiz_token']) ? sanitize_text_field($_GET['quiz_token']) : '';
    $cat_id = 0;

    if (!empty($token)) {
        $secret_key = wp_salt('auth');
        $base64_decoded = base64_decode(strtr($token, '-_', '+/'));
        if ($base64_decoded !== false) {
            $decrypted = openssl_decrypt($base64_decoded, 'AES-128-ECB', $secret_key);
            if ($decrypted !== false && is_numeric($decrypted)) {
                $cat_id = intval($decrypted);
            }
        }
    }

    if ($cat_id <= 0) {
        return '<p>Invalid or modified URL path.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'aysquiz_quizes';

    $is_logged_in = is_user_logged_in();
    $attempts_left = 1;

    if (!$is_logged_in) {
        $guest_table = $wpdb->prefix . 'quiz_guest_attempts';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        $used_record = $wpdb->get_var($wpdb->prepare(
            "SELECT attempts_used FROM $guest_table WHERE ip_address = %s AND user_agent = %s",
            $ip_address,
            $user_agent
        ));
        
        $used = $used_record ? intval($used_record) : 0;
        $attempts_left = 3 - $used;
    }

    if (isset($_POST['start_quiz_action']) && isset($_POST['secure_quiz_id'])) {
        if (!$is_logged_in && $attempts_left <= 0) {
            return '<p style="color:red; font-weight:bold;">Sorry, you have reached your test attempt limit. Please log in for unlimited access.</p>';
        }

        $quiz_id_to_run = intval($_POST['secure_quiz_id']);
        
        $belongs_to_category = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE id = %d AND quiz_category_id = %d AND published = 1",
            $quiz_id_to_run,
            $cat_id
        ));

        if (!$belongs_to_category) {
            return '<p>Access denied. This test does not belong to the current category.</p>';
        }

        if (!$is_logged_in && function_exists('snnj_track_guest_attempt_on_click')) {
            snnj_track_guest_attempt_on_click();
        }

        $session_key = wp_generate_password(20, false);
        set_transient('quiz_session_' . $session_key, $quiz_id_to_run, 30 * MINUTE_IN_SECONDS);
        setcookie('active_quiz_cookie', $session_key, time() + (30 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        $clean_redirect_url = add_query_arg(array('active_quiz' => $session_key), remove_query_arg(array('action', 'quiz_item_id')));
        wp_safe_redirect($clean_redirect_url);
        exit;
    }

    $output = '';
    
    $output .= '
    <style>
        .quiz-selection-wrapper { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; }
        .quiz-card { border: 1px solid #e2e8f0; padding: 20px 24px; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; }
        .quiz-card h4 { margin: 0; color: #1e293b; font-size: 16px; font-weight: 600; }
        .quiz-card form { margin: 0; min-width: 140px; }
        .start-quiz-btn { width: 100%; padding: 10px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .start-quiz-btn:hover { background: #1d4ed8; }
        .custom-quiz-modal { display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; }
        .custom-quiz-modal-content { background: #fff; padding: 30px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); text-align: center; animation: modalFadeIn 0.3s ease; }
        .custom-quiz-modal-content h3 { margin-top: 0; color: #1e293b; font-size: 18px; margin-bottom: 12px; }
        .custom-quiz-modal-content p { color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5; }
        .custom-quiz-modal-actions { display: flex; gap: 12px; }
        .modal-btn { flex: 1; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; }
        .modal-btn-cancel { background: #f1f5f9; color: #334155; }
        .modal-btn-cancel:hover { background: #cbd5e1; color: #0f172a; }
        .modal-btn-confirm { background: #2563eb; color: #fff; }
        .modal-btn-confirm:hover { background: #1d4ed8; }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>';

    if (isset($_GET['active_quiz'])) {
        $session_key = sanitize_text_field($_GET['active_quiz']);
        $cookie_session = isset($_COOKIE['active_quiz_cookie']) ? sanitize_text_field($_COOKIE['active_quiz_cookie']) : '';
        
        if (empty($cookie_session) || $cookie_session !== $session_key) {
            return '<p>Access denied. This test session belongs to another browser or session.</p>';
        }

        $quiz_id_to_run = get_transient('quiz_session_' . $session_key);

        if ($quiz_id_to_run) {
            $belongs_to_category = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE id = %d AND quiz_category_id = %d AND published = 1",
                $quiz_id_to_run,
                $cat_id
            ));

            if (!$belongs_to_category) {
                return '<p>Access denied. Invalid test session.</p>';
            }

            $back_url = remove_query_arg('active_quiz');
            $output .= '<p><button type="button" onclick="openBackModal(\'' . esc_url($back_url) . '\')" style="display:inline-block; margin-bottom:15px; padding:6px 12px; background:#f1f5f9; color:#2563eb; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px;">&larr; Back to test list</button></p>';
            $output .= do_shortcode('[ays_quiz id="' . intval($quiz_id_to_run) . '"]');
        } else {
            return '<p>The test session has expired or is invalid. Please select your test again.</p>';
        }

    } else {
        $limit = ($cat_id == 17) ? 2 : 3;
        $quizzes = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title FROM {$table_name} WHERE quiz_category_id = %d AND published = 1 LIMIT %d",
            $cat_id,
            $limit
        ));

        if (empty($quizzes)) {
            return '<p>No tests are currently available in this category.</p>';
        }

        if (!$is_logged_in && $attempts_left <= 0) {
            $output .= '<div style="background:#fee2e2; border:1px solid #f87171; padding:15px; border-radius:8px; color:#b91c1c; margin-top:20px; margin-bottom:15px;">
                You have run out of free test attempts. Please <a href="https://catest.ca/upgrade-premium/" style="text-decoration:underline; font-weight:bold;">upgrade to Premium</a> for unlimited access.
            </div>';
        }

        $output .= '<div class="quiz-selection-wrapper">';
        foreach ($quizzes as $quiz) {
            $quiz_id = intval($quiz->id);
            $quiz_title = esc_html($quiz->title);
            
            $output .= '<div class="quiz-card">';
            $output .= '<h4>' . $quiz_title . '</h4>';
            
            if ($is_logged_in || $attempts_left > 0) {
                $output .= '<form method="POST" action="" class="quiz-form-submit" style="margin: 0;">';
                $output .= '<input type="hidden" name="start_quiz_action" value="1">';
                $output .= '<input type="hidden" name="secure_quiz_id" value="' . $quiz_id . '">';
                $output .= '<button type="button" class="start-quiz-btn" onclick="openCustomQuizModal(this)">Start Test</button>';
                $output .= '</form>';
            } else {
                $output .= '<div style="font-size: 14px; color: #ef4444; font-weight: 600; text-align:right;">Limit Reached <br><a href="https://catest.ca/upgrade-premium/" style="color:#2563eb; text-decoration:underline;">Upgrade to view</a></div>';
            }
            
            $output .= '</div>';
        }
        $output .= '</div>';
    }

    $output .= '
    <div id="customQuizModal" class="custom-quiz-modal">
        <div class="custom-quiz-modal-content">
            <h3 id="modalTitle">Start Confirmation</h3>
            <p id="modalDesc">Do you want to do this test? It will cost 1 test attempt.</p>
            <div class="custom-quiz-modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeCustomQuizModal()">Cancel</button>
                <button type="button" class="modal-btn modal-btn-confirm" id="confirmQuizBtn">Confirm</button>
            </div>
        </div>
    </div>';

    $is_logged_js = $is_logged_in ? 'true' : 'false';
    
    $output .= '
    <script>
        let activeQuizForm = null;
        let targetBackUrl = "";
        const isUserLoggedIn = ' . $is_logged_js . ';
		
		window.addEventListener("pageshow", function (event) {
            // event.persisted: Trình duyệt lấy trang từ BFCache (Cache của nút Back)
            // performance.navigation.type === 2: Hành động điều hướng là nút Back/Forward
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                window.location.reload(); // Ép tải lại trang để PHP cập nhật số lượt
            }
        });

        function openCustomQuizModal(button) {
            activeQuizForm = button.closest("form");
            targetBackUrl = "";
            
            document.getElementById("modalTitle").innerText = "Start Confirmation";
            const modalDesc = document.getElementById("modalDesc");
            if (isUserLoggedIn) {
                modalDesc.innerText = "You are currently logged in with unlimited access to this test.";
            } else {
                modalDesc.innerText = "Do you want to do this test? It will cost 1 free test attempt.";
            }

            document.getElementById("customQuizModal").style.display = "flex";
        }

        function openBackModal(url) {
            activeQuizForm = null;
            targetBackUrl = url;

            document.getElementById("modalTitle").innerText = "Leave Test Warning";
            document.getElementById("modalDesc").innerText = "If you leave this page, your test attempt will still be counted as used. Are you sure you want to go back?";

            document.getElementById("customQuizModal").style.display = "flex";
        }

        function closeCustomQuizModal() {
            document.getElementById("customQuizModal").style.display = "none";
            activeQuizForm = null;
            targetBackUrl = "";
        }

        document.getElementById("confirmQuizBtn").onclick = function() {
            if (activeQuizForm) {
                activeQuizForm.submit();
            } else if (targetBackUrl !== "") {
                window.location.href = targetBackUrl;
            }
        };
    </script>';

    return $output;
}


// CUSTOM_MEMBER_EXAM_LIST_RENDER_CLIENT_PAGINATION

add_shortcode('member_exam_list', 'render_member_exam_list');
function render_member_exam_list() {
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Please log in to view exams.</div>';
    }

    $token = isset($_GET['quiz_token']) ? sanitize_text_field($_GET['quiz_token']) : '';
    $cat_id = 0;

    if (!empty($token)) {
        $secret_key = wp_salt('auth');
        $base64_decoded = base64_decode(strtr($token, '-_', '+/'));
        if ($base64_decoded !== false) {
            $decrypted = openssl_decrypt($base64_decoded, 'AES-128-ECB', $secret_key);
            if ($decrypted !== false && is_numeric($decrypted)) {
                $cat_id = intval($decrypted);
            }
        }
    }

    if ($cat_id <= 0) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Invalid or missing test category token.</div>';
    }

    global $wpdb;
    
    $cat_table = $wpdb->prefix . 'aysquiz_quizcategories';
    $category = $wpdb->get_row($wpdb->prepare("SELECT title FROM {$cat_table} WHERE id = %d", $cat_id));

    if (!$category) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Category not found.</div>';
    }

    $cat_title_upper = strtoupper(trim($category->title));
    $cat_prefix = ($cat_title_upper === 'SIGN') ? 'SIGN' : substr($cat_title_upper, 0, 2);

    if ($cat_title_upper !== 'SIGN') {
        $user_province = strtoupper(trim(get_user_meta($current_user_id, 'user_province', true)));

        if (empty($user_province) || $cat_prefix !== $user_province) {
            return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Access denied. This test category does not belong to your assigned province.</div>';
        }
    }

    $table_name = $wpdb->prefix . 'aysquiz_quizes';
    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT id, title, options FROM {$table_name} WHERE quiz_category_id = %d AND published = 1",
        $cat_id
    ));

    if (empty($exams)) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; color: #64748b;">No exams are currently available in this category.</div>';
    }

    // Lấy lịch sử làm bài của user cho các quiz thuộc category này
    $reports_table = 'snnj_aysquiz_reports';
    $exam_ids = wp_list_pluck($exams, 'id');
    $reports_map = [];

    if (!empty($exam_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($exam_ids), '%d'));
        $reports_query = $wpdb->prepare(
            "SELECT quiz_id, score FROM {$reports_table} WHERE user_id = %d AND quiz_id IN ($ids_placeholder)",
            array_merge([$current_user_id], $exam_ids)
        );
        $user_reports = $wpdb->get_results($reports_query);

        // Gom nhóm kết quả và so sánh với pass_score (mặc định 65)
        foreach ($user_reports as $rep) {
            $q_id = $rep->quiz_id;
            $score = intval($rep->score);
            
            $pass_score = 65; 
            foreach ($exams as $ex) {
                if ($ex->id == $q_id && !empty($ex->options)) {
                    $opt = json_decode($ex->options, true);
                    if (isset($opt['pass_score'])) {
                        $pass_score = intval($opt['pass_score']);
                    }
                    break;
                }
            }

            if (!isset($reports_map[$q_id])) {
                $reports_map[$q_id] = [
                    'has_attempted' => true,
                    'is_completed' => false,
                    'best_score' => $score
                ];
            } else {
                if ($score > $reports_map[$q_id]['best_score']) {
                    $reports_map[$q_id]['best_score'] = $score;
                }
            }

            if ($score >= $pass_score) {
                $reports_map[$q_id]['is_completed'] = true;
            }
        }
    }

    $output = '
    <style>
        .member-exam-container {
            font-family: inherit;
            margin-top: 10px;
        }
        .member-exam-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .member-exam-header {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        .member-exam-search-wrapper {
            position: relative;
            display: inline-block;
        }
        .member-exam-search-input {
            padding: 8px 14px 8px 36px !important;
            border: 1px solid #cbd5e0 !important;
            border-radius: 20px !important;
            font-size: 14px !important;
            outline: none !important;
            width: 300px !important;
            transition: all 0.2s;
            box-sizing: border-box !important;
            background: #fff !important;
            color: #1a202c !important;
            line-height: normal !important;
            box-shadow: none !important;
        }
        .member-exam-search-input:focus {
            border-color: #2b6cb0 !important;
            box-shadow: 0 0 0 3px rgba(43, 108, 176, 0.1) !important;
        }
        .member-exam-search-icon {
            position: absolute !important;
            left: 14px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #a0aec0 !important;
            font-size: 14px !important;
            pointer-events: none !important;
            line-height: 1 !important;
            z-index: 2;
        }
        /* Filter Tabs */
        .member-exam-filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .member-exam-filter-btn {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #cbd5e0;
            background: #fff;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.2s;
        }
        .member-exam-filter-btn:hover {
            background: #edf2f7;
        }
        .member-exam-filter-btn.active {
            background: #2b6cb0;
            color: #fff;
            border-color: #2b6cb0;
        }
        .member-exam-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        @media (max-width: 768px) {
            .member-exam-grid {
                grid-template-columns: 1fr;
            }
            .member-exam-search-input {
                width: 100% !important;
            }
        }
        .member-exam-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .member-exam-card:hover {
            border-color: #cbd5e0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .exam-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .exam-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 15px;
        }
        .exam-status-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            width: fit-content;
        }
        .badge-completed { background: #def7ec; color: #03543f; }
        .badge-inprogress { background: #fef08a; color: #713f12; }
        .badge-notstarted { background: #f1f5f9; color: #475569; }
    </style>';

    $output .= '<div class="member-exam-container">';
    
    // Top Bar
    $output .= '<div class="member-exam-top-bar">';
    $output .= '<h2 class="member-exam-header">Available Practice Tests</h2>';
    $output .= '<div class="member-exam-search-wrapper">';
    $output .= '<span class="member-exam-search-icon">🔍</span>';
    $output .= '<input type="text" id="member-exam-search-input" class="member-exam-search-input" placeholder="Search tests...">';
    $output .= '</div>';
    $output .= '</div>';

    // Filter Tabs
    $output .= '<div class="member-exam-filter-tabs">';
    $output .= '<button type="button" class="member-exam-filter-btn active" data-filter="all">All Tests</button>';
    $output .= '<button type="button" class="member-exam-filter-btn" data-filter="completed">Completed</button>';
    $output .= '<button type="button" class="member-exam-filter-btn" data-filter="inprogress">In Progress</button>';
    $output .= '<button type="button" class="member-exam-filter-btn" data-filter="notstarted">Not Started</button>';
    $output .= '</div>';

    $output .= '<div class="member-exam-grid" id="member-exam-list-grid">';

    $secret_key = wp_salt('auth');
    $target_base_url = home_url('/memberhome/do-exam/');

    foreach ($exams as $exam) {
        $raw_data = $cat_prefix . '_' . $exam->id;
        $encrypted_data = openssl_encrypt($raw_data, 'AES-128-ECB', $secret_key);
        $exam_token = rtrim(strtr(base64_encode($encrypted_data), '+/', '-_'), '=');
        $exam_url = add_query_arg('exam_token', $exam_token, $target_base_url);

        // Phân loại trạng thái
        $status_class = 'badge-notstarted';
        $status_text = 'Not Started';
        $status_filter = 'notstarted';

       if (isset($reports_map[$exam->id])) {
            if ($reports_map[$exam->id]['is_completed']) {
                $status_class = 'badge-completed';
                $status_text = 'Completed';
                $status_filter = 'completed';
            } else {
                $status_class = 'badge-inprogress';
                $status_text = 'In Progress';
                $status_filter = 'inprogress';
            }
        }

        $output .= '<a href="' . esc_url($exam_url) . '" class="member-exam-card" data-title="' . esc_attr(strtolower($exam->title)) . '" data-status="' . $status_filter . '">';
        $output .= '<div class="exam-info">';
        $output .= '<span class="exam-title">' . esc_html($exam->title) . '</span>';
        $output .= '<span class="exam-status-badge ' . $status_class . '">' . $status_text . '</span>';
        $output .= '</div>';
        $output .= '<span style="color: #94a3b8; font-size: 18px; font-weight: bold;">&rarr;</span>';
        $output .= '</a>';
    }

    $output .= '</div>';
    $output .= '</div>';

    // Script xử lý lọc theo tab & tìm kiếm trực tiếp siêu mượt
    $output .= '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("member-exam-search-input");
        const filterBtns = document.querySelectorAll(".member-exam-filter-btn");
        const cards = document.querySelectorAll("#member-exam-list-grid .member-exam-card");

        let currentFilter = "all";

        function filterCards() {
            const keyword = searchInput.value.toLowerCase().trim();

            cards.forEach(card => {
                const title = card.getAttribute("data-title");
                const status = card.getAttribute("data-status");

                let matchesFilter = (currentFilter === "all" || status === currentFilter);
                let matchesSearch = (keyword === "" || title.includes(keyword));

                if (matchesFilter && matchesSearch) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                filterBtns.forEach(b => b.classList.remove("active"));
                this.classList.add("active");
                currentFilter = this.getAttribute("data-filter");
                filterCards();
            });
        });

        if (searchInput) {
            searchInput.addEventListener("input", function() {
                filterCards();
            });
        }
    });
    </script>';

    return $output;
}

add_shortcode('member_do_exam', 'render_member_do_exam');

// Xử lý bảo mật, kiểm tra thông tin bang và render bài thi tương ứng cho thành viên
function render_member_do_exam() {
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Please log in to take the exam.</div>';
    }

    $token = isset($_GET['exam_token']) ? sanitize_text_field($_GET['exam_token']) : '';
    if (empty($token)) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Missing exam token.</div>';
    }

    $secret_key = wp_salt('auth');
    $base64_decoded = base64_decode(strtr($token, '-_', '+/'));
    
    if ($base64_decoded === false) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Invalid token format.</div>';
    }

    $decrypted = openssl_decrypt($base64_decoded, 'AES-128-ECB', $secret_key);
    
    if ($decrypted === false) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Decryption failed.</div>';
    }

    if (strpos($decrypted, '_') !== false) {
        list($token_province, $exam_id) = explode('_', $decrypted, 2);
    } else {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Malformed exam data.</div>';
    }

    $token_province = strtoupper(trim($token_province));
    $exam_id = intval($exam_id);

    if ($exam_id <= 0) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Invalid exam ID.</div>';
    }

    if ($token_province !== 'SIGN') {
        $user_province = strtoupper(trim(get_user_meta($current_user_id, 'user_province', true)));

        if (empty($user_province) || $token_province !== $user_province) {
            return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Access denied. This exam does not belong to your assigned province.</div>';
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'aysquiz_quizes';
    $exam_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE id = %d AND published = 1",
        $exam_id
    ));

    if (!$exam_exists) {
        return '<div style="padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #fed7d7; color: #c53030;">Exam not found or not published.</div>';
    }

    $output = '<div class="member-do-exam-container" style="margin-top: 20px;">';
    $output .= do_shortcode('[ays_quiz id="' . $exam_id . '"]');
    $output .= '</div>';

    return $output;
}