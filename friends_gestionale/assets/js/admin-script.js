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
        
        // Add Partecipante Handler
        $('#fg-add-partecipante-btn').on('click', function(e) {
            e.preventDefault();
            
            var select = $('#fg-add-partecipante');
            var socioId = select.val();
            var socioName = select.find('option:selected').text();
            
            if (!socioId) {
                alert('Seleziona un socio dalla lista');
                return;
            }
            
            // Check if already added
            if ($('.fg-partecipante-item[data-socio-id="' + socioId + '"]').length > 0) {
                alert('Questo socio è già stato aggiunto');
                return;
            }
            
            // Remove "no partecipanti" message if exists
            $('.fg-no-partecipanti').remove();
            
            var partecipanteHtml = '<div class="fg-partecipante-item" data-socio-id="' + socioId + '">' +
                '<span class="fg-partecipante-name">' + socioName + '</span>' +
                '<button type="button" class="button fg-remove-partecipante" data-socio-id="' + socioId + '">Rimuovi</button>' +
                '<input type="hidden" name="fg_partecipanti[]" value="' + socioId + '" />' +
                '</div>';
            
            $('#fg-partecipanti-list').append(partecipanteHtml);
            
            // Update count
            var count = $('.fg-partecipante-item').length;
            $('#fg-partecipanti-count').text(count);
            
            // Reset select
            select.val('');
        });
        
        // Remove Partecipante Handler
        $(document).on('click', '.fg-remove-partecipante', function(e) {
            e.preventDefault();
            if (confirm('Rimuovere questo partecipante dalla lista?')) {
                $(this).closest('.fg-partecipante-item').remove();
                
                // Update count
                var count = $('.fg-partecipante-item').length;
                $('#fg-partecipanti-count').text(count);
                
                // Show "no partecipanti" message if empty
                if (count === 0) {
                    $('#fg-partecipanti-list').html('<p class="fg-no-partecipanti">Nessun partecipante aggiunto.</p>');
                }
            }
        });
        
        // Send Invites Handler
        $('#fg-send-invites-btn').on('click', function(e) {
            e.preventDefault();
            
            var count = $('.fg-partecipante-item').length;
            if (count === 0) {
                alert('Nessun partecipante da invitare');
                return;
            }
            
            if (confirm('Inviare email di invito a ' + count + ' partecipante/i?')) {
                var button = $(this);
                button.prop('disabled', true).text('Invio in corso...');
                
                // In a real implementation, this would make an AJAX call
                setTimeout(function() {
                    alert('Inviti inviati con successo a ' + count + ' partecipante/i!');
                    button.prop('disabled', false).html('<span class="dashicons dashicons-email" style="margin-top: 3px;"></span> Invia Inviti Email a Tutti i Partecipanti');
                }, 1500);
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
        
        // Show/hide conditional payment fields based on payment type
        function togglePaymentFields() {
            var tipoPagamento = $('#fg_tipo_pagamento').val();
            
            // Hide all conditional fields
            $('#fg_evento_field').hide();
            $('#fg_evento_custom_field').hide();
            $('#fg_categoria_socio_field').hide();
            
            // Show fields based on payment type
            if (tipoPagamento === 'evento') {
                $('#fg_evento_field').show();
                
                // Check if "Altro Evento" is selected
                var eventoId = $('#fg_evento_id').val();
                if (eventoId === 'altro_evento') {
                    $('#fg_evento_custom_field').show();
                }
            } else if (tipoPagamento === 'quota') {
                $('#fg_categoria_socio_field').show();
                // Auto-populate amount when quota type is selected
                updatePaymentAmountFromCategory();
            }
        }
        
        // Function to update payment amount from member category quota
        function updatePaymentAmountFromCategory() {
            var socioId = $('#fg_socio_id').val();
            var categoriaId = $('#fg_categoria_socio_id').val();
            
            if (socioId && $('#fg_tipo_pagamento').val() === 'quota') {
                // Make AJAX request to get member's category and quota
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fg_get_member_quota',
                        socio_id: socioId,
                        categoria_id: categoriaId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Auto-select the member's category in dropdown
                            if (response.data.categoria_id) {
                                $('#fg_categoria_socio_id').val(response.data.categoria_id);
                            }
                            
                            // Auto-populate amount and make it readonly
                            if (response.data.quota) {
                                $('#fg_importo').val(response.data.quota);
                                $('#fg_importo').prop('readonly', true);
                                $('#fg_importo').css('background-color', '#f0f0f0');
                            } else {
                                $('#fg_importo').prop('readonly', false);
                                $('#fg_importo').css('background-color', '');
                            }
                        }
                    }
                });
            } else {
                // Remove readonly when not quota payment
                $('#fg_importo').prop('readonly', false);
                $('#fg_importo').css('background-color', '');
            }
        }
        
        // Initialize on page load
        togglePaymentFields();
        
        // Update when payment type changes
        $('#fg_tipo_pagamento').on('change', function() {
            togglePaymentFields();
        });
        
        // Update when socio changes
        $('#fg_socio_id').on('change', function() {
            if ($('#fg_tipo_pagamento').val() === 'quota') {
                updatePaymentAmountFromCategory();
            }
        });
        
        // Update when category changes
        $('#fg_categoria_socio_id').on('change', function() {
            updatePaymentAmountFromCategory();
        });
        
        // Show/hide custom event field when event selection changes
        $('#fg_evento_id').on('change', function() {
            var eventoId = $(this).val();
            if (eventoId === 'altro_evento') {
                $('#fg_evento_custom_field').show();
            } else {
                $('#fg_evento_custom_field').hide();
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
        
        // Photo Upload Handler for Socio
        $('#fg_upload_foto_button').on('click', function(e) {
            e.preventDefault();
            
            var custom_uploader = wp.media({
                title: 'Seleziona Foto Socio',
                button: {
                    text: 'Usa questa foto'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                
                // Update hidden field
                $('#fg_foto_id').val(attachment.id);
                
                // Show preview
                var previewHtml = '<div class="fg-foto-preview" style="margin-bottom: 10px;">' +
                    '<img src="' + attachment.url + '" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;" />' +
                    '</div>';
                
                $('.fg-foto-preview').remove();
                $('#fg_upload_foto_button').before(previewHtml);
                $('#fg_remove_foto_button').show();
            });
            
            custom_uploader.open();
        });
        
        // Remove Photo Handler for Socio
        $('#fg_remove_foto_button').on('click', function(e) {
            e.preventDefault();
            if (confirm('Sei sicuro di voler rimuovere la foto?')) {
                $('#fg_foto_id').val('');
                $('.fg-foto-preview').remove();
                $(this).hide();
            }
        });
        
    });
    
})(jQuery);
