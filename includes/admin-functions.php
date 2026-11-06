<?php

function catest_render_admin_requests_dashboard() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return '<p style="text-align: center; color: #666;">Access denied. Administrator privileges required.</p>';
    }

    global $wpdb;
    $table_name =$wpdb->prefix . 'user_requests';

    // Xử lý AJAX request khi bấm đổi status hoặc xóa mềm (is_deleted = 1)
    if ( isset( $_POST['catest_action_nonce'] ) && wp_verify_nonce($_POST['catest_action_nonce'], 'catest_dashboard_action' ) ) {
        if ( isset( $_POST['req_id'] ) ) {
            $req_id = intval($_POST['req_id'] );
            
            // Nếu là yêu cầu xóa mềm
            if ( isset( $_POST['action_type'] ) &&$_POST['action_type'] === 'soft_delete' ) {
                $wpdb->update($table_name,
                    array(
                        'is_deleted' => 1,
                        'updated_at' => current_time( 'mysql' )
                    ),
                    array( 'id' => $req_id ),
                    array( '%d', '%s' ),
                    array( '%d' )
                );
            } 
            // Nếu là đổi status thông thường
            elseif ( isset( $_POST['new_status'] ) ) {
                $new_status = sanitize_text_field($_POST['new_status'] );
                if ( in_array( $new_status, array( 'pending', 'resolved' ) ) ) {
                    $wpdb->update($table_name,
                        array(
                            'status'     => $new_status,
                            'updated_at' => current_time( 'mysql' )
                        ),
                        array( 'id' => $req_id ),
                        array( '%s', '%s' ),
                        array( '%d' )
                    );
                }
            }
            wp_send_json_success();
        }
    }

    // Chỉ lấy các request chưa bị xóa mềm (is_deleted = 0)
    $results = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE is_deleted = 0 ORDER BY created_at DESC" );

    $get_region_name_by_code = function($code ) {
        if ( empty( $code ) || $code === 'N/A' ) return 'N/A';$terms = get_terms( array( 'taxonomy' => 'site_region', 'hide_empty' => false ) );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as$term ) {
                $term_region_code = get_term_meta($term->term_id, 'region_code', true );
                if ( $code ===$term_region_code || $code === strtoupper($term->slug ) || $code ===$term->slug ) {
                    return $term->name;
                }
            }
        }
        return $code;
    };

    $clean_data = array();
    if ( ! empty( $results ) ) {
        foreach ( $results as$row ) {
            $user_info = get_userdata($row->user_id );
            $row->username =$user_info ? $user_info->user_login : 'Unknown';$row->email    = $user_info ? $user_info->user_email : 'N/A';
            
            if ( $row->request_type === 'transfer_province' ) {
                $row->current_province =$get_region_name_by_code( $row->current_province );$row->target_province  = $get_region_name_by_code($row->target_province );
            }

            $clean_data[] =$row;
        }
    }

    $ajax_url = admin_url( 'admin-ajax.php' );$nonce    = wp_create_nonce( 'catest_dashboard_action' );

    $json_data = wp_json_encode($clean_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

    $output = <<<'HTML'
<div class="member-dashboard-container" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;" data-ajax-url="[AJAX_URL]" data-nonce="[NONCE]">
    <h2 style="font-size: 22px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">User Requests Management</h2>
    
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="snnj-req-tabs">
            <button class="req-tab-btn active" data-tab="all" style="background: #2b6cb0; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">All Requests</button>
            <button class="req-tab-btn" data-tab="transfer_province" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">Province Change</button>
            <button class="req-tab-btn" data-tab="contact_message" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px;">Contact Messages</button>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px; font-weight: 600; color: #4a5568; background: #edf2f7; padding: 8px 14px; border-radius: 20px;">
                <input type="checkbox" id="filter-hide-resolved" style="width: 14px; height: 14px; cursor: pointer; accent-color: #2b6cb0;">
                Hide Resolved
            </label>

            <div style="position: relative;">
                <input type="text" id="snnj-search-input" placeholder="Search user or email..." style="padding: 8px 14px 8px 34px; border: 1px solid #cbd5e0; border-radius: 20px; font-size: 14px; outline: none; width: 260px; transition: all 0.2s;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a0aec0; font-size: 14px;">🔍</span>
            </div>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead style="background: #f8fafc; color: #4a5568;">
                <tr>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; width: 25%;">User Info</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; width: 35%;">Request Details</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; width: 20%;">Date</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; width: 20%;">Action / Status</th>
                </tr>
            </thead>
            <tbody id="snnj-req-tbody">
            </tbody>
        </table>
    </div>

    <div id="snnj-req-pagination" style="display: flex; justify-content: flex-end; gap: 6px; margin-top: 20px;">
    </div>

    <!-- CUSTOM POPUP CONFIRM -->
    <div id="snnj-custom-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99999; align-items: center; justify-content: center;">
        <div style="background: #fff; padding: 24px; border-radius: 12px; width: 100%; max-width: 360px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 32px; margin-bottom: 10px;">⚠️</div>
            <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1a202c; font-weight: 700;">Are you sure?</h3>
            <p style="margin: 0 0 20px 0; font-size: 14px; color: #718096;">This request will be deleted and removed from the list.</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button id="snnj-modal-cancel" style="background: #edf2f7; color: #4a5568; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">Cancel</button>
                <button id="snnj-modal-confirm" style="background: #e53e3e; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 640px) { 
    #snnj-search-input { width: 100% !important; } 
}
.req-tab-btn:hover {
    opacity: 0.9;
}
.toggle-resolve-btn {
    cursor: pointer;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}
.toggle-resolve-btn.pending {
    background: #fefcbf;
    color: #d69e2e;
    border: 1px solid #f6e05e;
}
.toggle-resolve-btn.pending:hover {
    background: #fef08a;
}
.toggle-resolve-btn.resolved {
    background: #c6f6d5;
    color: #276749;
    border: 1px solid #9ae6b4;
}
.toggle-resolve-btn.resolved:hover {
    background: #bbf7d0;
}
.delete-req-btn {
    background: #fff5f5;
    border: 1px solid #feb2b2;
    color: #e53e3e;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.delete-req-btn:hover {
    background: #fed7d7;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var container = document.querySelector(".member-dashboard-container");
    var ajaxUrl = container.getAttribute("data-ajax-url");
    var nonce = container.getAttribute("data-nonce");
    
    let allRequests = JSON.parse('[JSON_DATA_PLACEHOLDER]');
    
    let currentTab = "all";
    let hideResolved = false;
    let searchQuery = "";
    let currentPage = 1;
    const itemsPerPage = 10;
    let deletingRequestId = null;

    const tbody = document.getElementById("snnj-req-tbody");
    const paginationContainer = document.getElementById("snnj-req-pagination");
    const tabBtns = document.querySelectorAll(".req-tab-btn");
    const hideResolvedCheckbox = document.getElementById("filter-hide-resolved");
    const searchInput = document.getElementById("snnj-search-input");
    
    const modal = document.getElementById("snnj-custom-modal");
    const modalCancel = document.getElementById("snnj-modal-cancel");
    const modalConfirm = document.getElementById("snnj-modal-confirm");

    function formatDate(dateString) {
        const d = new Date(dateString);
        return `${d.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" })}<br><span style="color:#a0aec0; font-size: 12px;">${d.toLocaleTimeString("en-US", {hour: "2-digit", minute:"2-digit"})}</span>`;
    }

    function getFilteredData() {
        return allRequests.filter(function(req) {
            const matchTab = (currentTab === "all" || req.request_type === currentTab);
            const matchStatus = hideResolved ? (req.status === "pending") : true;
            const matchSearch = searchQuery === "" || 
                req.username.toLowerCase().indexOf(searchQuery) !== -1 || 
                req.email.toLowerCase().indexOf(searchQuery) !== -1;
            return matchTab && matchStatus && matchSearch;
        });
    }

    function renderTable() {
        const filtered = getFilteredData();
        const totalPages = Math.ceil(filtered.length / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        
        const start = (currentPage - 1) * itemsPerPage;
        const currentData = filtered.slice(start, start + itemsPerPage);

        tbody.innerHTML = "";

        if (currentData.length === 0) {
            tbody.innerHTML = "<tr><td colspan=\"4\" style=\"text-align: center; padding: 30px; color: #718096;\">No requests found matching your filters.</td></tr>";
        } else {
            currentData.forEach(function(req) {
                const isTransfer = req.request_type === "transfer_province";
                
                let detailsHTML = "";
                if (isTransfer) {
                    detailsHTML = '<div style="margin-bottom: 4px;"><span style="background:#ebf8ff; color:#3182ce; font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: bold;">PROVINCE CHANGE</span></div>' +
                                  '<div style="font-weight: 600; color: #2d3748;">' + req.current_province + ' ➔ <span style="color:#3182ce;">' + req.target_province + '</span></div>';
                } else {
                    detailsHTML = '<div style="margin-bottom: 4px;"><span style="background:#edf2f7; color:#4a5568; font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: bold;">CONTACT</span></div>' +
                                  '<div style="color: #2d3748; font-size: 13px;">' + (req.message || "") + '</div>';
                }

                const isPending = (req.status === "pending");
                const btnClass = isPending ? "pending" : "resolved";
                const btnText = isPending ? "⏳ Pending" : "✓ Resolved";

                const actionHtml = '<div style="display: flex; align-items: center; gap: 8px;">' +
                                   '<button class="toggle-resolve-btn ' + btnClass + '" onclick="handleToggleResolve(' + req.id + ')">' + btnText + '</button>' +
                                   '<button class="delete-req-btn" onclick="handleOpenDeleteModal(' + req.id + ')" title="Xóa">🗑️</button>' +
                                   '</div>';

                const tr = document.createElement("tr");
                tr.style.borderBottom = "1px solid #edf2f7";
                tr.innerHTML = '<td style="padding: 15px; vertical-align: top;">' +
                               '<div style="font-weight: 600; color: #1a202c;">' + req.username + '</div>' +
                               '<div style="color: #718096; font-size: 12px;">' + req.email + '</div>' +
                               '</td>' +
                               '<td style="padding: 15px; vertical-align: top;">' + detailsHTML + '</td>' +
                               '<td style="padding: 15px; vertical-align: top; font-size: 13px; color: #2d3748;">' + formatDate(req.created_at) + '</td>' +
                               '<td style="padding: 15px; vertical-align: top;">' + actionHtml + '</td>';
                tbody.appendChild(tr);
            });
        }

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationContainer.innerHTML = "";
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.innerText = i;
            btn.style.padding = "6px 12px";
            btn.style.border = i === currentPage ? "none" : "1px solid #cbd5e0";
            btn.style.background = i === currentPage ? "#2b6cb0" : "#fff";
            btn.style.color = i === currentPage ? "#fff" : "#4a5568";
            btn.style.borderRadius = "6px";
            btn.style.cursor = "pointer";
            btn.style.fontSize = "13px";
            btn.style.fontWeight = "600";

            btn.addEventListener("click", function() {
                currentPage = i;
                renderTable();
            });

            paginationContainer.appendChild(btn);
        }
    }

    tabBtns.forEach(function(btn) {
        btn.addEventListener("click", function() {
            tabBtns.forEach(function(b) {
                b.style.background = "#edf2f7";
                b.style.color = "#4a5568";
            });
            this.style.background = "#2b6cb0";
            this.style.color = "#fff";

            currentTab = this.getAttribute("data-tab");
            currentPage = 1;
            renderTable();
        });
    });

    hideResolvedCheckbox.addEventListener("change", function() {
        hideResolved = this.checked;
        currentPage = 1;
        renderTable();
    });

    searchInput.addEventListener("input", function() {
        searchQuery = this.value.toLowerCase().trim();
        currentPage = 1;
        renderTable();
    });

    window.handleToggleResolve = function(requestId) {
        const targetReq = allRequests.find(req => Number(req.id) === Number(requestId));
        if (targetReq) {
            const nextStatus = (targetReq.status === "pending") ? "resolved" : "pending";
            
            const formData = new URLSearchParams();
            formData.append("action", "catest_dashboard_action");
            formData.append("catest_action_nonce", nonce);
            formData.append("req_id", requestId);
            formData.append("new_status", nextStatus);

            fetch(window.location.href, {
                method: "POST",
                body: formData
            }).then(function(res) {
                if (res.ok) {
                    targetReq.status = nextStatus;
                    renderTable();
                } else {
                    alert("Có lỗi xảy ra khi cập nhật trạng thái!");
                }
            });
        }
    };

    window.handleOpenDeleteModal = function(requestId) {
        deletingRequestId = requestId;
        modal.style.display = "flex";
    };

    modalCancel.addEventListener("click", function() {
        deletingRequestId = null;
        modal.style.display = "none";
    });

    modalConfirm.addEventListener("click", function() {
        if (deletingRequestId !== null) {
            const formData = new URLSearchParams();
            formData.append("action", "catest_dashboard_action");
            formData.append("catest_action_nonce", nonce);
            formData.append("req_id", deletingRequestId);
            formData.append("action_type", "soft_delete"); // Gửi cờ xóa mềm

            fetch(window.location.href, {
                method: "POST",
                body: formData
            }).then(function(res) {
                if (res.ok) {
                    // Xóa item khỏi mảng giao diện hiện tại
                    allRequests = allRequests.filter(req => Number(req.id) !== Number(deletingRequestId));
                    renderTable();
                } else {
                    alert("Có lỗi xảy ra khi xóa!");
                }
                deletingRequestId = null;
            });
        }
        modal.style.display = "none";
    });

    modal.addEventListener("click", function(e) {
        if (e.target === modal) {
            modal.style.display = "none";
            deletingRequestId = null;
        }
    });

    renderTable();
});
</script>
HTML;

    $output = str_replace(
        array( '[AJAX_URL]', '[NONCE]', '[JSON_DATA_PLACEHOLDER]' ),
        array( esc_url( $ajax_url ), esc_attr($nonce ), $json_data ),$output
    );

    return $output;
}
add_shortcode( 'admin_requests_dashboard', 'catest_render_admin_requests_dashboard' );