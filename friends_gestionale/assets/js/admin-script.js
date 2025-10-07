/**
 * Friends Gestionale - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Document Upload Handler
        $('#fg-upload-document').on('click', function(e) {
            e.preventDefault();
            
            var custom_uploader = wp.media({
                title: 'Seleziona Documento',
                button: {
                    text: 'Usa questo documento'
                },
                multiple: false
            });
            
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                var documentsList = $('#fg-documents-list');
                var index = documentsList.children().length;
                
                var documentHtml = '<div class="fg-document-item">' +
                    '<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>' +
                    '<button type="button" class="button fg-remove-document" data-index="' + index + '">Rimuovi</button>' +
                    '<input type="hidden" name="fg_documents[' + index + '][url]" value="' + attachment.url + '" />' +
                    '<input type="hidden" name="fg_documents[' + index + '][name]" value="' + attachment.filename + '" />' +
                    '</div>';
                
                documentsList.append(documentHtml);
            });
            
            custom_uploader.open();
        });
        
        // Remove Document Handler
        $(document).on('click', '.fg-remove-document', function(e) {
            e.preventDefault();
            if (confirm('Sei sicuro di voler rimuovere questo documento?')) {
                $(this).closest('.fg-document-item').remove();
            }
        });
        
        // Initialize datepickers
        if ($.fn.datepicker) {
            $('input[type="date"]').not('.hasDatepicker').each(function() {
                var $input = $(this);
                if ($input.attr('type') === 'date' && !$input[0].type === 'date') {
                    // Browser doesn't support HTML5 date input, use jQuery UI datepicker
                    $input.datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: '-100:+10'
                    });
                }
            });
        }
        
        // Auto-calculate expiry date based on subscription date
        $('#fg_data_iscrizione').on('change', function() {
            var dataIscrizione = $(this).val();
            if (dataIscrizione) {
                var date = new Date(dataIscrizione);
                date.setFullYear(date.getFullYear() + 1);
                var year = date.getFullYear();
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var day = String(date.getDate()).padStart(2, '0');
                var dataScadenza = year + '-' + month + '-' + day;
                $('#fg_data_scadenza').val(dataScadenza);
            }
        });
        
        // Auto-populate payment amount from member's quota
        $('#fg_socio_id').on('change', function() {
            var socioId = $(this).val();
            if (socioId) {
                $.ajax({
                    url: friendsGestionale.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'fg_get_socio_quota',
                        nonce: friendsGestionale.nonce,
                        socio_id: socioId
                    },
                    success: function(response) {
                        if (response.success && response.data.quota) {
                            $('#fg_importo').val(response.data.quota);
                        }
                    }
                });
            }
        });
        
        // Confirm before deleting
        $('.submitdelete').on('click', function(e) {
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Dashboard statistics refresh
        $('.fg-refresh-stats').on('click', function(e) {
            e.preventDefault();
            location.reload();
        });
        
        // Export buttons
        $('.fg-export-button').on('click', function(e) {
            var exportType = $(this).data('export-type');
            if (exportType) {
                $(this).text('Esportazione in corso...').prop('disabled', true);
            }
        });
        
        // Highlight expired members
        $('.fg-data-scadenza').each(function() {
            var dataScadenza = $(this).data('date');
            if (dataScadenza) {
                var today = new Date();
                var scadenza = new Date(dataScadenza);
                if (scadenza < today) {
                    $(this).closest('tr').addClass('fg-row-expired');
                }
            }
        });
        
        // Add custom admin menu styling
        if ($('#adminmenu').length) {
            var menuItems = [
                '#menu-posts-fg_socio',
                '#menu-posts-fg_pagamento',
                '#menu-posts-fg_raccolta'
            ];
            
            $.each(menuItems, function(index, item) {
                $(item).addClass('fg-custom-menu-item');
            });
        }
        
        // Real-time search for members
        var searchTimeout;
        $('#fg-search-soci').on('keyup', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val().toLowerCase();
            
            searchTimeout = setTimeout(function() {
                $('.fg-socio-row').each(function() {
                    var text = $(this).text().toLowerCase();
                    if (text.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }, 300);
        });
        
        // Initialize tooltips if available
        if ($.fn.tooltip) {
            $('[data-tooltip]').tooltip({
                position: {
                    my: 'center bottom-20',
                    at: 'center top'
                }
            });
        }
        
    });
    
})(jQuery);
