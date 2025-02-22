/**
 * Service DataTable Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Category
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/service-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables layanan.
 *              Includes state management, server-side processing,
 *              dan error handling.
 *              Terintegrasi dengan form handlers dan toast.
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - EquipmentToast for notifications
 *
 * Changelog:
 * 1.0.0 - 2024-02-21
 * - Initial implementation
 * - Added state management
 * - Added export functionality
 * - Enhanced error handling
 */
/**
 * Service DataTable Handler
 * @package WP_Equipment 
 *//**
 * Service DataTable Handler
 * @package WP_Equipment 
 */(function($) {
    'use strict';

    const ServiceDataTable = {
        table: null,
        initialized: false,

        init() {
            // Debug: cek elemen table
            if ($('#services-table').length === 0) {
                console.error('Table element tidak ditemukan');
                return;
            }
            
            // Debug: cek inisialisasi
            if ($('#services-table').length && !this.initialized) {
                console.log('Inisialisasi ServiceDataTable...');
                this.initializeDataTable();
                this.bindEvents();
                this.initialized = true;
            }
        },

        initializeDataTable() {
            // Debug: cek wpEquipmentData
            console.log('wpEquipmentData:', wpEquipmentData);
            
            if (!wpEquipmentData || !wpEquipmentData.ajaxUrl || !wpEquipmentData.nonce) {
                console.error('wpEquipmentData tidak tersedia', {
                    exists: !!wpEquipmentData,
                    ajaxUrl: wpEquipmentData?.ajaxUrl,
                    nonce: wpEquipmentData?.nonce
                });
                return;
            }

            // Debug: destroy existing table jika ada
            if ($.fn.DataTable.isDataTable('#services-table')) {
                console.log('Destroying existing DataTable...');
                $('#services-table').DataTable().destroy();
            }

            // Debug: log DataTable configuration
            const config = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        const requestData = {
                            ...d,
                            action: 'handle_service_datatable',
                            nonce: wpEquipmentData.nonce
                        };
                        // Debug: log request data
                        console.log('DataTable request data:', requestData);
                        return requestData;
                    },
                    beforeSend: (xhr) => {
                        // Debug: log headers
                        console.log('Request headers:', xhr.getAllResponseHeaders());
                    },
                    error: (xhr, error, thrown) => {
                        // Debug: detailed error logging
                        console.error('DataTable request failed:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            thrown: thrown
                        });
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Parsed error response:', response);
                        } catch (e) {
                            console.log('Raw error response:', xhr.responseText);
                        }
                        EquipmentToast.error('Gagal memuat data layanan');
                    }
                },
                columns: [
                    { data: 'singkatan' },
                    { data: 'nama' },
                    { 
                        data: 'status',
                        render: (data) => {
                            return `<span class="status-badge ${data}">${data}</span>`;
                        }
                    },
                    { 
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[1, 'asc']],
                language: {
                    processing: 'Memproses...',
                    search: 'Pencarian:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data yang ditampilkan',
                    infoFiltered: '(filter dari _MAX_ total data)',
                    loadingRecords: 'Mengambil data...',
                    zeroRecords: 'Tidak ditemukan data yang sesuai',
                    emptyTable: 'Tidak ada data',
                    paginate: {
                        first: 'Pertama',
                        previous: 'Sebelumnya',
                        next: 'Selanjutnya',
                        last: 'Terakhir'
                    }
                }
            };

            console.log('Initializing DataTable with config:', config);

            try {
                this.table = $('#services-table').DataTable(config);
                console.log('DataTable initialized successfully');
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }
        },

        refresh() {
            console.log('Refreshing ServiceDataTable...');
            if (this.table) {
                this.table.ajax.reload(null, false);
            } else {
                console.warn('Table not initialized, reinitializing...');
                this.init();
            }
        },

        bindEvents() {
            console.log('Binding ServiceDataTable events...');
            $(document)
                .on('service:created service:updated service:deleted', () => this.refresh())
                .on('click', '.delete-service', (e) => {
                    e.preventDefault();
                    const id = $(e.currentTarget).data('id');
                    if (!confirm('Anda yakin ingin menghapus layanan ini?')) {
                        return;
                    }
                    this.handleDelete(id);
                });
        }
    };

    // Export ke window object
    window.ServiceDataTable = ServiceDataTable;

})(jQuery);
