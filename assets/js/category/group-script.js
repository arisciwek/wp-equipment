/**
 * Group Management Script
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/group-script.js
 *
 * Description: Menangani interaksi UI dan AJAX untuk manajemen grup.
 */

jQuery(document).ready(function($) {
    let dataTable;
    const rightPanel = $('#right-panel');
    
    // Initialize DataTable
    function initDataTable() {
        dataTable = $('#groups-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                data: function(d) {
                    d.action = 'handle_group_datatable';
                    d.nonce = wp_equipment.nonce;
                    d.service_id = $('#service-filter').val();
                }
            },
            columns: [
                { data: 'nama', name: 'nama' },
                { data: 'service_nama', name: 'service_nama' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'dokumen', name: 'dokumen' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false }
            ],
            order: [[0, 'asc']],
            pageLength: wp_equipment.page_length || 10,
            language: wp_equipment.datatable_language
        });
    }

    // Initialize right panel
    function initRightPanel() {
        rightPanel.on('click', '.close-panel', function() {
            rightPanel.removeClass('active');
            resetForm();
        });
    }

    // Load group details
    function loadGroupDetails(id) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_group',
                id: id,
                nonce: wp_equipment.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayGroupDetails(response.data.group);
                    updateRightPanelActions(response.data.meta);
                    rightPanel.addClass('active');
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(wp_equipment.error_message);
            }
        });
    }

    // Display group details
    function displayGroupDetails(group) {
        // Basic information
        $('#group-nama').text(group.nama);
        $('#group-service').text(group.service_nama);
        $('#group-keterangan').text(group.keterangan || '-');
        
        // Status badge
        const statusTemplate = $('#status-badge-template').html();
        const statusLabel = group.status === 'active' ? 'Aktif' : 'Tidak Aktif';
        const statusBadge = statusTemplate
            .replace('{status}', group.status)
            .replace('{label}', statusLabel);
        $('#group-status').html(statusBadge);

        // Document information
        const docContainer = $('#group-doc-container');
        docContainer.empty();
        
        if (group.dokumen_path && group.dokumen_type) {
            const docTemplate = $('#group-doc-template').html();
            const docLink = docTemplate
                .replace('{link}', wp_equipment.site_url + '/' + group.dokumen_path)
                .replace('{type}', group.dokumen_type.toUpperCase());
            docContainer.html(docLink);
        } else {
            docContainer.html('<em>Tidak ada dokumen</em>');
        }
        
        $('#group-doc-type').text(group.dokumen_type ? group.dokumen_type.toUpperCase() : '-');

        // Timeline information
        $('#group-created-by').text(group.created_by_name || '-');
        $('#group-created-at').text(group.created_at || '-');
        $('#group-updated-at').text(group.updated_at || '-');
    }

    // Update right panel actions
    function updateRightPanelActions(meta) {
        const actionButtons = rightPanel.find('.panel-actions');
        actionButtons.empty();

        if (meta.can_edit) {
            actionButtons.append(`
                <button type="button" class="button edit-group" data-id="${meta.group_id}">
                    <i class="dashicons dashicons-edit"></i>
                    Edit
                </button>
            `);
        }

        if (meta.can_delete) {
            actionButtons.append(`
                <button type="button" class="button delete-group" data-id="${meta.group_id}">
                    <i class="dashicons dashicons-trash"></i>
                    Delete
                </button>
            `);
        }
    }

    // Handle create/edit form submission
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(form[0]);
        const isEdit = form.data('mode') === 'edit';
        
        formData.append('action', isEdit ? 'update_group' : 'create_group');
        formData.append('nonce', wp_equipment.nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    dataTable.ajax.reload();
                    rightPanel.removeClass('active');
                    showSuccess(response.data.message);
                    resetForm();
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(wp_equipment.error_message);
            }
        });
    }

    // Handle group deletion
    function handleDelete(id) {
        if (!confirm(wp_equipment.confirm_delete)) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_group',
                id: id,
                nonce: wp_equipment.nonce
            },
            success: function(response) {
                if (response.success) {
                    dataTable.ajax.reload();
                    rightPanel.removeClass('active');
                    showSuccess(response.data.message);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(wp_equipment.error_message);
            }
        });
    }

    // Reset form
    function resetForm() {
        const form = $('#group-form');
        form[0].reset();
        form.removeData('mode');
        form.removeData('id');
        $('#group-doc-upload-container').hide();
    }

    // Show success message
    function showSuccess(message) {
        const notice = $('<div class="notice notice-success is-dismissible"><p></p></div>');
        notice.find('p').text(message);
        $('#wpbody-content').prepend(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Show error message
    function showError(message) {
        const notice = $('<div class="notice notice-error is-dismissible"><p></p></div>');
        notice.find('p').text(message);
        $('#wpbody-content').prepend(notice);
        
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Event bindings
    $(document).on('click', '.view-group', function() {
        const id = $(this).data('id');
        loadGroupDetails(id);
    });

    $(document).on('click', '.edit-group', function() {
        const id = $(this).data('id');
        // Load edit form...
    });

    $(document).on('click', '.delete-group', function() {
        const id = $(this).data('id');
        handleDelete(id);
    });

    $('#group-form').on('submit', handleFormSubmit);

    $('#service-filter').on('change', function() {
        dataTable.ajax.reload();
    });

    // Initialize components
    initDataTable();
    initRightPanel();
});
