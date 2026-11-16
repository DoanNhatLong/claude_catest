<?php
/**
 * Excel Quiz Importer (shortcode: [excel_quiz_importer])
 *
 * Changes from the previous version:
 * 1. Only administrators (manage_options) can see the form or run an import.
 *    Question, answer and explanation text is filtered with wp_kses_post().
 * 2. The import runs on template_redirect and then redirects (Post/Redirect/Get),
 *    so refreshing the page cannot submit the same import twice.
 *    The import is refused if any question title already exists in the database
 *    or is repeated inside the file.
 * 7. Skipped rows are reported with their Excel row number and reason.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Transient key that holds the result message for the current user.
 */
function excel_quiz_import_result_key() {
    return 'excel_quiz_import_result_' . get_current_user_id();
}

/**
 * Store the result for the current user and redirect back to the form page.
 */
function excel_quiz_import_finish( $type, $message, $details = array() ) {
    set_transient(
        excel_quiz_import_result_key(),
        array(
            'type'    => $type,
            'message' => $message,
            'details' => $details,
        ),
        5 * MINUTE_IN_SECONDS
    );

    $redirect = wp_get_referer();
    if ( ! $redirect ) {
        $redirect = home_url( '/' );
    }
    wp_safe_redirect( $redirect );
    exit;
}

/**
 * Default AYS quiz options used for every generated quiz pack.
 */
function excel_quiz_import_default_options() {
    return '{"quiz_version":"6.7.1.80","core_version":"7.1","php_version":"8.4.27","color":"","bg_color":"","text_color":"","height":0,"width":0,"enable_logged_users":"off","information_form":"after","form_name":null,"form_email":null,"form_phone":null,"image_width":0,"image_height":"","enable_correction":"on","enable_progress_bar":"on","enable_questions_result":"off","randomize_questions":"off","randomize_answers":"off","enable_questions_counter":"off","enable_restriction_pass":"off","restriction_pass_message":"","user_role":[],"custom_css":"","limit_users":"off","limitation_message":"","redirect_url":"","redirection_delay":0,"answers_view":"list","enable_rtl_direction":"off","enable_logged_users_message":"","questions_count":"0","enable_question_bank":"off","enable_live_progress_bar":"off","enable_percent_view":"off","enable_average_statistical":"off","enable_next_button":"on","enable_previous_button":"off","enable_arrows":"off","timer_text":"","quiz_theme":"classic_light","enable_social_buttons":"off","result_text":"<p>Thanks for taking part in the quiz. Keep up the good work!  %%user_name%%</p>\n","enable_pass_count":"on","hide_score":"off","rate_form_title":"<p>Please click the stars to rate the quiz</p>\n","box_shadow_color":"#c9c9c9","quiz_border_radius":8,"quiz_mobile_border_radius":8,"quiz_bg_image":"","quiz_border_width":"1","quiz_border_style":"solid","quiz_border_color":"#000","quiz_loader":"default","create_date":"2026-10-25 23:11:11","author":"{\"id\":\"5\",\"name\":\"nhatlong\"}","quest_animation":"none","form_title":"","enable_bg_music":"off","quiz_bg_music":"","answers_font_size":15,"show_create_date":"off","show_author":"off","enable_early_finish":"on","answers_rw_texts":"on_passing","disable_store_data":"off","enable_background_gradient":"off","background_gradient_color_1":"#000","background_gradient_color_2":"#fff","quiz_gradient_direction":"vertical","redirect_after_submit":"off","submit_redirect_url":"","submit_redirect_delay":"0","progress_bar_style":"third","enable_exit_button":"off","exit_redirect_url":"","image_sizing":"cover","quiz_bg_image_position":"center center","custom_class":"","enable_social_links":"off","social_links":{"linkedin_link":"","facebook_link":"","twitter_link":"","vkontakte_link":"","instagram_link":"","youtube_link":"","behance_link":""},"show_quiz_title":"on","show_quiz_desc":"on","show_quiz_image":"on","show_login_form":"off","mobile_max_width":"","limit_users_by":"ip","active_date_check":"off","activeInterval":"2026-10-25 23:43:18","deactiveInterval":"2026-10-25 23:43:18","active_date_pre_start_message":"The quiz will be available soon!","active_date_message":"This quiz has expired!","explanation_time":"1","enable_clear_answer":"off","show_category":"off","show_question_category":"off","display_score":"by_correctness","enable_rw_asnwers_sounds":"off","ans_right_wrong_icon":"none","quiz_bg_img_in_finish_page":"off","finish_after_wrong_answer":"off","after_timer_text":"","enable_enter_key":"on","buttons_text_color":"#000000","buttons_position":"center","show_questions_explanation":"on_passing","enable_audio_autoplay":"off","buttons_size":"medium","buttons_font_size":"18","buttons_width":"","buttons_left_right_padding":"36","buttons_top_bottom_padding":"14","buttons_border_radius":"8","enable_leave_page":"on","enable_tackers_count":"off","tackers_count":"0","enable_pass_score":"on","pass_score":60,"pass_score_message":"<h4 style=\"text-align: center\">Congratulations!</h4><p style=\"text-align: center\">You passed the quiz!</p>","fail_score_message":"<h4 style=\"text-align: center\">Oops!</h4><p style=\"text-align: center\">You have not passed the quiz! <br> Try again!</p>","question_font_size":16,"quiz_width_by_percentage_px":"pixels","questions_hint_icon_or_text":"hide","questions_hint_value":"","enable_early_finsh_comfirm_box":"on","enable_questions_ordering_by_cat":"off","show_schedule_timer":"off","show_timer_type":"countdown","quiz_loader_text_value":"","hide_correct_answers":"off","show_information_form":"on","quiz_loader_custom_gif":"","disable_hover_effect":"off","quiz_loader_custom_gif_width":100,"progress_live_bar_style":"default","quiz_title_transformation":"uppercase","show_answers_numbering":"none","quiz_image_width_by_percentage_px":"pixels","quiz_image_height":0,"quiz_bg_img_on_start_page":"off","quiz_box_shadow_x_offset":0,"quiz_box_shadow_y_offset":0,"quiz_box_shadow_z_offset":15,"quiz_question_text_alignment":"center","quiz_arrow_type":"default","quiz_show_wrong_answers_first":"off","quiz_display_all_questions":"off","quiz_timer_red_warning":"off","quiz_schedule_timezone":"UTC-8","questions_hint_button_value":"","quiz_tackers_message":"<p>This quiz is expired!</p>\n","quiz_enable_linkedin_share_button":"on","quiz_enable_facebook_share_button":"on","quiz_enable_twitter_share_button":"on","quiz_enable_whatsapp_share_button":"off","quiz_make_responses_anonymous":"off","quiz_make_all_review_link":"off","show_questions_numbering":"1.","quiz_message_before_timer":"","enable_password":"off","password_quiz":"","quiz_password_message":"","enable_see_result_confirm_box":"off","display_fields_labels":"off","enable_full_screen_mode":"on","quiz_enable_password_visibility":"off","question_mobile_font_size":16,"answers_mobile_font_size":15,"social_buttons_heading":"","quiz_enable_vkontakte_share_button":"on","answers_border":"on","answers_border_width":1,"answers_border_style":"solid","answers_border_color":"#dddddd","ans_img_height":"150","ans_img_mobile_height":"150","show_answers_caption":"on","ans_img_caption_position":"bottom","social_links_heading":"","quiz_enable_question_category_description":"off","answers_margin":12,"quiz_message_before_redirect_timer":"","buttons_mobile_font_size":18,"answers_box_shadow":"off","answers_box_shadow_color":"#000","quiz_answer_box_shadow_x_offset":0,"quiz_answer_box_shadow_y_offset":0,"quiz_answer_box_shadow_z_offset":10,"quiz_create_author":5,"quiz_enable_title_text_shadow":"off","quiz_title_text_shadow_color":"#333","quiz_title_text_shadow_x_offset":2,"quiz_title_text_shadow_y_offset":2,"quiz_title_text_shadow_z_offset":2,"quiz_show_only_wrong_answers":"off","quiz_title_font_size":28,"quiz_title_mobile_font_size":20,"quiz_password_width":"","quiz_review_placeholder_text":"","quiz_make_review_required":"off","quiz_enable_results_toggle":"off","quiz_review_thank_you_message":"","quiz_review_enable_comment_field":"on","quest_explanation_font_size":16,"quest_explanation_mobile_font_size":16,"quiz_waiting_time":"off","wrong_answers_font_size":16,"wrong_answers_mobile_font_size":16,"quiz_enable_question_image_zoom":"off","right_answers_font_size":16,"right_answers_mobile_font_size":16,"quiz_display_messages_before_buttons":"off","quiz_enable_user_choosing_anonymous_assessment":"off","note_text_font_size":14,"note_text_mobile_font_size":14,"quiz_questions_numbering_by_category":"off","quiz_enable_custom_texts_for_buttons":"off","quiz_custom_texts_start_button":"Start","quiz_custom_texts_next_button":"Next","quiz_custom_texts_prev_button":"Prev","quiz_custom_texts_clear_button":"Clear","quiz_custom_texts_finish_button":"Finish","quiz_custom_texts_see_results_button":"See Result","quiz_custom_texts_restart_quiz_button":"Restart quiz","quiz_custom_texts_send_feedback_button":"Send feedback","quiz_custom_texts_load_more_button":"Load more","quiz_custom_texts_exit_button":"Exit","quiz_custom_texts_check_button":"Check","quiz_custom_texts_login_button":"Log In","quiz_enable_quiz_category_description":"off","quiz_admin_note_text_transform":"none","quiz_quest_explanation_text_transform":"none","quiz_right_answer_text_transform":"none","quiz_wrong_answer_text_transform":"none","quiz_admin_note_text_decoration":"none","quiz_quest_explanation_text_decoration":"none","quiz_right_answers_text_decoration":"none","quiz_wrong_answers_text_decoration":"none","quiz_admin_note_letter_spacing":"0","quiz_bg_img_during_the_quiz":"off","quiz_quest_explanation_letter_spacing":"0","quiz_right_answers_letter_spacing":"0","quiz_wrong_answers_letter_spacing":"0","quiz_admin_note_font_weight":"normal","quiz_quest_explanation_font_weight":"normal","quiz_right_answers_font_weight":"normal","quiz_wrong_answers_font_weight":"normal","quiz_content_max_width":90,"quiz_content_mobile_max_width":90,"quiz_timer_warning_text_color":"#ff0000","quiz_enable_default_hide_results_toggle":"off","quiz_show_restart_button_on_quiz_fail":"off","quiz_disable_input_focusing":"off","quiz_disable_input_focusing":"off","quiz_enable_keyboard_navigation":"on","quiz_admin_note_mobile_text_transform":"none","quiz_quest_explanation_mobile_text_transform":"none","quiz_right_answer_mobile_text_transform":"none","quiz_wrong_answer_mobile_text_transform":"none","quiz_admin_note_mobile_text_decoration":"none","quiz_dont_show_quiz":"off","enable_questions_reporting":"off","quiz_quest_explanation_mobile_text_decoration":"none","quiz_right_answers_mobile_text_decoration":"none","quiz_wrong_answers_mobile_text_decoration":"none","quiz_admin_note_mobile_letter_spacing":"0","quiz_quest_explanation_mobile_letter_spacing":"0","quiz_right_answers_mobile_letter_spacing":"0","quiz_wrong_answers_mobile_letter_spacing":"0","quiz_admin_note_mobile_font_weight":"normal","quiz_quest_explanation_font_weight":"normal","quiz_right_answers_font_weight":"normal","quiz_question_image_border_radius":0,"quiz_question_image_mobile_border_radius":0,"quiz_wrong_answers_font_weight":"normal","buttons_mobile_border_radius":"8","required_fields":null,"enable_timer":"off","enable_quiz_rate":"off","enable_rate_avg":"off","enable_box_shadow":"on","enable_border":"off","quiz_timer_in_title":"off","enable_rate_comments":"off","enable_restart_button":"on","autofill_user_data":"off","timer":100}';
}

/**
 * Handle the import request before any output is sent, then redirect (PRG).
 */
function excel_quiz_import_handle_request() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['import_quiz_nonce'] ) ) {
        return;
    }

    // Fix 1: only administrators may run the import.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have permission to import quizzes.', 'Forbidden', array( 'response' => 403 ) );
    }

    if ( ! wp_verify_nonce( $_POST['import_quiz_nonce'], 'process_excel_quiz_import' ) ) {
        excel_quiz_import_finish( 'error', 'Security check failed (nonce verification failed). Please reload the page and try again.' );
    }

    if ( empty( $_FILES['excel_file']['tmp_name'] ) || ! file_exists( $_FILES['excel_file']['tmp_name'] ) ) {
        excel_quiz_import_finish( 'error', 'No Excel file was uploaded.' );
    }

    global $wpdb;

    $filePath        = $_FILES['excel_file']['tmp_name'];
    $categoryId      = intval( $_POST['category_id'] );
    $prefixTitle     = sanitize_text_field( $_POST['prefix_title'] );
    $quizTitlePrefix = sanitize_text_field( $_POST['quiz_title_prefix'] );
    $packSize        = intval( $_POST['pack_size'] );
    $authorId        = 5; // Default author ID.
    $quizOptionsJson = excel_quiz_import_default_options();

    $xlsxFilepath = WP_PLUGIN_DIR . '/simplexlsx/SimpleXLSX.php';
    if ( ! file_exists( $xlsxFilepath ) ) {
        excel_quiz_import_finish( 'error', 'SimpleXLSX.php was not found in wp-content/plugins/simplexlsx/.' );
    }
    require_once $xlsxFilepath;

    $xlsx = \Shuchkin\SimpleXLSX::parse( $filePath );
    if ( ! $xlsx ) {
        excel_quiz_import_finish( 'error', 'Could not read the Excel file: ' . \Shuchkin\SimpleXLSX::parseError() );
    }

    $rows = $xlsx->rows();
    if ( empty( $rows ) ) {
        excel_quiz_import_finish( 'error', 'The Excel file is empty.' );
    }

    // ------------------------------------------------------------------
    // Step 1: parse and validate every row before touching the database.
    // ------------------------------------------------------------------
    $questions   = array();
    $skippedRows = array(); // Fix 7: collect skipped rows with a reason.
    $seenTitles  = array();

    foreach ( $rows as $index => $row ) {
        $excelRow = $index + 1; // Excel rows are 1-based.

        $sttStr       = isset( $row[0] ) ? trim( (string) $row[0] ) : '';
        $questionText = isset( $row[1] ) ? trim( (string) $row[1] ) : '';

        // Completely empty rows are ignored silently.
        if ( '' === $sttStr && '' === $questionText ) {
            continue;
        }

        if ( ! is_numeric( $sttStr ) ) {
            // A non-numeric first row is treated as the header row.
            if ( 0 === $index ) {
                continue;
            }
            $skippedRows[] = 'Row ' . $excelRow . ': the No. column (A) is not a number ("' . $sttStr . '").';
            continue;
        }

        if ( '' === $questionText ) {
            $skippedRows[] = 'Row ' . $excelRow . ': the question column (B) is empty.';
            continue;
        }

        $stt           = (int) $sttStr;
        $questionTitle = $prefixTitle . str_pad( $stt, 4, '0', STR_PAD_LEFT );

        // Fix 2: the same question title must not appear twice in one file.
        if ( isset( $seenTitles[ $questionTitle ] ) ) {
            excel_quiz_import_finish(
                'error',
                'Import cancelled: question number ' . $stt . ' appears more than once in the file (rows ' . $seenTitles[ $questionTitle ] . ' and ' . $excelRow . '). Nothing was saved.'
            );
        }
        $seenTitles[ $questionTitle ] = $excelRow;

        $questions[] = array(
            'stt'         => $stt,
            'title'       => $questionTitle,
            // Fix 1: strip unsafe HTML (scripts, event handlers) from imported text.
            'question'    => wp_kses_post( $questionText ),
            'answers'     => array(
                isset( $row[2] ) ? wp_kses_post( trim( (string) $row[2] ) ) : '',
                isset( $row[3] ) ? wp_kses_post( trim( (string) $row[3] ) ) : '',
                isset( $row[4] ) ? wp_kses_post( trim( (string) $row[4] ) ) : '',
                isset( $row[5] ) ? wp_kses_post( trim( (string) $row[5] ) ) : '',
            ),
            'explanation' => isset( $row[6] ) ? wp_kses_post( trim( (string) $row[6] ) ) : '',
            'correct'     => isset( $row[7] ) ? strtoupper( trim( (string) $row[7] ) ) : 'A',
            'category_id' => ( isset( $row[8] ) && '' !== trim( (string) $row[8] ) ) ? trim( (string) $row[8] ) : $categoryId,
        );
    }

    if ( empty( $questions ) ) {
        excel_quiz_import_finish( 'error', 'No valid question rows were found in the Excel file.', $skippedRows );
    }

    $qTable = $wpdb->prefix . 'aysquiz_questions';
    $aTable = $wpdb->prefix . 'aysquiz_answers';
    $zTable = $wpdb->prefix . 'aysquiz_quizes';

    // ------------------------------------------------------------------
    // Fix 2: refuse the import if any question title already exists.
    // ------------------------------------------------------------------
    $titles       = array_keys( $seenTitles );
    $placeholders = implode( ',', array_fill( 0, count( $titles ), '%s' ) );
    $existing     = $wpdb->get_col(
        $wpdb->prepare( "SELECT question_title FROM {$qTable} WHERE question_title IN ($placeholders)", $titles )
    );

    if ( ! empty( $existing ) ) {
        $preview = array_slice( $existing, 0, 10 );
        $more    = count( $existing ) > 10 ? ' (and ' . ( count( $existing ) - 10 ) . ' more)' : '';
        excel_quiz_import_finish(
            'error',
            'Import cancelled: ' . count( $existing ) . ' question(s) with these codes already exist: ' . implode( ', ', $preview ) . $more . '. This file was probably imported before. Nothing was saved.'
        );
    }

    // ------------------------------------------------------------------
    // Step 2: insert everything inside one transaction (all or nothing).
    // ------------------------------------------------------------------
    $wpdb->query( 'START TRANSACTION' );

    try {
        $allQuestionIds = array();

        foreach ( $questions as $q ) {
            $inserted_q = $wpdb->insert( $qTable, array(
                'author_id'      => $authorId,
                'category_id'    => $q['category_id'],
                'question'       => $q['question'],
                'question_title' => $q['title'],
                'explanation'    => $q['explanation'],
                'type'           => 'radio',
                'published'      => 1,
                'weight'         => 1,
            ) );

            if ( false === $inserted_q ) {
                throw new \Exception( 'Failed to insert question (No. ' . $q['stt'] . '): ' . $wpdb->last_error );
            }

            $currentQuestionId = $wpdb->insert_id;
            $allQuestionIds[]  = $currentQuestionId;

            foreach ( $q['answers'] as $j => $answerText ) {
                $currentLetter = chr( ord( 'A' ) + $j );

                $inserted_a = $wpdb->insert( $aTable, array(
                    'question_id' => $currentQuestionId,
                    'answer'      => $answerText,
                    'correct'     => ( $currentLetter === $q['correct'] ) ? 1 : 0,
                    'ordering'    => $j + 1,
                    'weight'      => 0,
                ) );

                if ( false === $inserted_a ) {
                    throw new \Exception( 'Failed to insert answers for question ID ' . $currentQuestionId . ': ' . $wpdb->last_error );
                }
            }
        }

        // Group questions into quiz packs.
        $totalPacks = ceil( count( $allQuestionIds ) / $packSize );
        for ( $p = 0; $p < $totalPacks; $p++ ) {
            $chunkIds = array_slice( $allQuestionIds, $p * $packSize, $packSize );
            $packName = $quizTitlePrefix . ' ' . str_pad( $p + 1, 2, '0', STR_PAD_LEFT );

            $inserted_z = $wpdb->insert( $zTable, array(
                'author_id'        => $authorId,
                'title'            => $packName,
                'description'      => '',
                'quiz_category_id' => $categoryId,
                'question_ids'     => implode( ',', $chunkIds ),
                'ordering'         => 0,
                'published'        => 1,
                'options'          => $quizOptionsJson,
            ) );

            if ( false === $inserted_z ) {
                throw new \Exception( 'Failed to create quiz pack ' . $packName . ': ' . $wpdb->last_error );
            }
        }

        $wpdb->query( 'COMMIT' );
    } catch ( \Throwable $e ) {
        $wpdb->query( 'ROLLBACK' );
        excel_quiz_import_finish( 'error', 'Import failed and all changes were rolled back: ' . $e->getMessage(), $skippedRows );
    }

    excel_quiz_import_finish(
        'success',
        'Imported ' . count( $allQuestionIds ) . ' question(s) and created ' . $totalPacks . ' quiz pack(s).'
            . ( $skippedRows ? ' ' . count( $skippedRows ) . ' row(s) were skipped (see below).' : '' ),
        $skippedRows
    );
}
add_action( 'template_redirect', 'excel_quiz_import_handle_request' );

/**
 * Shortcode: render the import form and the result of the last import.
 */
function render_excel_quiz_importer_form() {
    // Fix 1: the form is only shown to administrators.
    if ( ! current_user_can( 'manage_options' ) ) {
        return '';
    }

    $output = '';

    $result = get_transient( excel_quiz_import_result_key() );
    if ( $result ) {
        delete_transient( excel_quiz_import_result_key() );

        $color   = ( 'success' === $result['type'] ) ? 'green' : 'red';
        $label   = ( 'success' === $result['type'] ) ? '[Success]' : '[Error]';
        $output .= '<p style="color:' . $color . '; font-weight:bold;">' . esc_html( $label . ' ' . $result['message'] ) . '</p>';

        if ( ! empty( $result['details'] ) ) {
            $output .= '<div style="background:#fff8e1; border:1px solid #f0c36d; padding:10px 15px; margin-bottom:15px; max-width:600px;">';
            $output .= '<strong>Skipped rows (' . count( $result['details'] ) . '):</strong><ul style="margin:8px 0 0 18px;">';
            foreach ( $result['details'] as $detail ) {
                $output .= '<li>' . esc_html( $detail ) . '</li>';
            }
            $output .= '</ul></div>';
        }
    }

    $output .= '
    <form action="" method="POST" enctype="multipart/form-data" style="background:#f9f9f9; padding:20px; border:1px solid #ddd; max-width:600px;">
        ' . wp_nonce_field( 'process_excel_quiz_import', 'import_quiz_nonce', true, false ) . '

        <h3>Excel Quiz Importer (SimpleXLSX)</h3>

        <p>
            <label>Excel file (.xlsx):</label><br>
            <input type="file" name="excel_file" accept=".xlsx" required style="width:100%;">
        </p>

        <p>
            <label>Quiz Category ID:</label><br>
            <input type="number" name="category_id" value="26" required style="width:100%;">
        </p>

        <p>
            <label>Question code prefix (Prefix Title):</label><br>
            <input type="text" name="prefix_title" value="YTK5" required style="width:100%;">
        </p>

        <p>
            <label>Quiz pack title prefix:</label><br>
            <input type="text" name="quiz_title_prefix" value="Yukon Knowledge - Pack" required style="width:100%;">
        </p>

        <p>
            <label>Questions per pack (Pack Size):</label><br>
            <input type="number" name="pack_size" value="20" required style="width:100%;">
        </p>

        <p>
            <button type="submit" name="submit_import" style="background:#0073aa; color:#fff; padding:10px 20px; border:none; cursor:pointer;">Start Import</button>
        </p>
    </form>
    ';

    return $output;
}
add_shortcode( 'excel_quiz_importer', 'render_excel_quiz_importer_form' );
