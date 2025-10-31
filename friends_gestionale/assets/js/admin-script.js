/**
 * Friends Gestionale - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize Select2 for donor dropdown in payments
        if (typeof $.fn.select2 !== 'undefined') {
            $('.fg-select2-donor').select2({
                placeholder: 'Cerca donatore per nome...',
                allowClear: true,
                width: '100%'
            });
        }
        
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
            var selectedOption = select.find('option:selected');
            var socioName = selectedOption.text();
            var tipoDonatore = selectedOption.data('tipo');
            
            if (!socioId) {
                alert('Seleziona un donatore dalla lista');
                return;
            }
            
            // Check if already added
            if ($('.fg-partecipante-item[data-socio-id="' + socioId + '"]').length > 0) {
                alert('Questo donatore è già stato aggiunto');
                return;
            }
            
            // Remove "no partecipanti" message if exists
            $('.fg-no-partecipanti').remove();
            
            // Extract clean name (remove label)
            var cleanName = socioName.replace(/\s*\[(Socio|Donatore)\]\s*$/, '');
            
            // Create badge HTML
            var badgeHtml = '';
            if (tipoDonatore === 'anche_socio') {
                badgeHtml = '<span class="fg-badge fg-stato-attivo" style="margin-left: 5px; font-size: 10px;">Socio</span>';
            } else {
                badgeHtml = '<span class="fg-badge" style="margin-left: 5px; font-size: 10px;">Donatore</span>';
            }
            
            var partecipanteHtml = '<div class="fg-partecipante-item" data-socio-id="' + socioId + '">' +
                '<span class="fg-partecipante-name">' + cleanName + badgeHtml + '</span>' +
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
        
        // Handle donor type change to show/hide relevant sections
        $('#fg_tipo_donatore').on('change', function() {
            var tipoDonatore = $(this).val();
            
            if (tipoDonatore === 'solo_donatore') {
                // Show donor category section, hide membership section
                $('.fg-categoria-donatore-section').show();
                $('.fg-iscrizione-section').hide();
            } else if (tipoDonatore === 'anche_socio') {
                // Show membership section, hide donor category section
                $('.fg-categoria-donatore-section').hide();
                $('.fg-iscrizione-section').show();
            }
        });
        
        // Handle person type change (privato/società)
        $('input[name="fg_tipo_persona"]').on('change', function() {
            var tipoPersona = $(this).val();
            
            if (tipoPersona === 'societa') {
                // Show ragione sociale, change labels, remove required from nome/cognome
                $('.fg-ragione-sociale-field').show();
                $('#fg_ragione_sociale').prop('required', true);
                $('#fg_nome').prop('required', false);
                $('#fg_cognome').prop('required', false);
                $('.fg-nome-required').hide();
                $('.fg-cognome-required').hide();
                $('#fg_nome_label strong').text('Nome Referente:');
                $('#fg_cognome_label strong').text('Cognome Referente:');
            } else {
                // Hide ragione sociale, change labels back, add required to nome/cognome
                $('.fg-ragione-sociale-field').hide();
                $('#fg_ragione_sociale').prop('required', false);
                $('#fg_nome').prop('required', true);
                $('#fg_cognome').prop('required', true);
                $('.fg-nome-required').show();
                $('.fg-cognome-required').show();
                $('#fg_nome_label strong').text('Nome:');
                $('#fg_cognome_label strong').text('Cognome:');
            }
        });
        
        // Trigger on page load
        if ($('input[name="fg_tipo_persona"]:checked').length > 0) {
            $('input[name="fg_tipo_persona"]:checked').trigger('change');
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
        
        // Auto-populate payment amount from member's quota and manage payment type options
        $('#fg_socio_id').on('change', function() {
            var socioId = $(this).val();
            if (socioId) {
                // Get donor type from the selected option text
                var selectedText = $(this).find('option:selected').text();
                var isDonorOnly = selectedText.indexOf('[Donatore]') !== -1;
                
                // Hide/show "Quota Associativa" option based on donor type
                var quotaOption = $('#fg_tipo_pagamento option[value="quota"]');
                if (isDonorOnly) {
                    // Simple donor - hide quota option and select another option if quota was selected
                    quotaOption.hide();
                    if ($('#fg_tipo_pagamento').val() === 'quota') {
                        $('#fg_tipo_pagamento').val('donazione');
                        togglePaymentFields();
                    }
                    // Also hide categoria socio field for simple donors
                    $('#fg_categoria_socio_field').hide();
                } else {
                    // Member donor - show quota option
                    quotaOption.show();
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fg_get_member_quota',
                        nonce: '<?php echo wp_create_nonce("fg_get_member_quota"); ?>',
                        socio_id: socioId
                    },
                    success: function(response) {
                        if (response.success && response.data.quota) {
                            $('#fg_importo').val(response.data.quota);
                        }
                    }
                });
            } else {
                // No donor selected - show all options
                $('#fg_tipo_pagamento option[value="quota"]').show();
            }
        });
        
        // Trigger donor type check on page load
        if ($('#fg_socio_id').val()) {
            $('#fg_socio_id').trigger('change');
        }
        
        // Show/hide conditional payment fields based on payment type
        function togglePaymentFields() {
            var tipoPagamento = $('#fg_tipo_pagamento').val();
            
            // Hide all conditional fields
            $('#fg_evento_field').hide();
            $('#fg_evento_custom_field').hide();
            $('#fg_categoria_socio_field').hide();
            $('#fg_raccolta_field').hide();
            $('#fg_quota_warning').hide();
            
            // Show fields based on payment type
            if (tipoPagamento === 'evento') {
                $('#fg_evento_field').show();
                
                // Check if "Altro Evento" is selected
                var eventoId = $('#fg_evento_id').val();
                if (eventoId === 'altro_evento') {
                    $('#fg_evento_custom_field').show();
                }
                // Unlock importo field for evento payments
                $('#fg_importo').prop('readonly', false);
                $('#fg_importo').css('background-color', '');
            } else if (tipoPagamento === 'quota') {
                $('#fg_categoria_socio_field').show();
                $('#fg_quota_warning').show();
                // Auto-populate amount when quota type is selected
                updatePaymentAmountFromCategory();
            } else if (tipoPagamento === 'raccolta') {
                $('#fg_raccolta_field').show();
                // Unlock importo field for raccolta payments
                $('#fg_importo').prop('readonly', false);
                $('#fg_importo').css('background-color', '');
            } else if (tipoPagamento === 'donazione' || tipoPagamento === 'altro') {
                // Unlock importo field for donazione and altro payments
                $('#fg_importo').prop('readonly', false);
                $('#fg_importo').css('background-color', '');
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
        
        // Unlock/Lock Categoria Socio button handler
        $('#fg_unlock_categoria_socio').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $select = $('#fg_categoria_socio_id');
            var $icon = $button.find('.dashicons');
            
            if ($select.prop('disabled')) {
                // Unlock the field
                $select.prop('disabled', false);
                $select.css('background-color', '');
                $icon.removeClass('dashicons-lock').addClass('dashicons-unlock');
                $button.html('<span class="dashicons dashicons-unlock" style="margin-top: 3px;"></span> Blocca');
                $('#fg_categoria_socio_description').html('Il campo è sbloccato. La modifica aggiornerà la tipologia socio del donatore.');
            } else {
                // Lock the field
                $select.prop('disabled', true);
                $select.css('background-color', '#f0f0f0');
                $icon.removeClass('dashicons-unlock').addClass('dashicons-lock');
                $button.html('<span class="dashicons dashicons-lock" style="margin-top: 3px;"></span> Sblocca');
                $('#fg_categoria_socio_description').html('Clicca su "Sblocca" per modificare la categoria. La modifica aggiornerà anche la tipologia socio del donatore.');
            }
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
        
        // Add payment button from donor page - opens modal
        $('#fg_add_payment_btn').on('click', function(e) {
            e.preventDefault();
            var donorId = $(this).data('donor-id');
            var donorName = $(this).data('donor-name');
            
            // Create modal HTML
            var modalHtml = '<div id="fg-add-payment-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);">' +
                '<div style="background-color: #fff; margin: 50px auto; padding: 0; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">' +
                '<div style="padding: 20px; border-bottom: 1px solid #ddd; background: #f9f9f9; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">' +
                '<h2 style="margin: 0;">Aggiungi Nuovo Pagamento per ' + donorName + '</h2>' +
                '<button id="fg-close-payment-modal" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; line-height: 1;">&times;</button>' +
                '</div>' +
                '<div id="fg-payment-form-container" style="padding: 25px;"></div>' +
                '</div>' +
                '</div>';
            
            $('body').append(modalHtml);
            $('#fg-add-payment-modal').fadeIn(300);
            
            // Load payment form via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_get_payment_form',
                    donor_id: donorId,
                    nonce: fg_admin_ajax.nonce || ''
                },
                success: function(response) {
                    if (response.success) {
                        $('#fg-payment-form-container').html(response.data.html);
                        
                        // Reinitialize form elements
                        if (typeof $.fn.select2 !== 'undefined') {
                            $('#fg-payment-form-container .fg-select2-donor').select2({
                                placeholder: 'Cerca donatore per nome...',
                                allowClear: true,
                                width: '100%'
                            });
                        }
                        
                        // Set today's date as default
                        var today = new Date().toISOString().split('T')[0];
                        $('#fg_modal_data_pagamento').val(today);
                        
                        // Initialize payment type change handlers
                        initializePaymentFormHandlers();
                    } else {
                        $('#fg-payment-form-container').html('<p style="color: #d63638;">Errore nel caricamento del form.</p>');
                    }
                },
                error: function() {
                    $('#fg-payment-form-container').html('<p style="color: #d63638;">Errore nella comunicazione con il server.</p>');
                }
            });
            
            // Close modal handlers
            $(document).on('click', '#fg-close-payment-modal', function() {
                $('#fg-add-payment-modal').fadeOut(300, function() {
                    $(this).remove();
                });
            });
            
            $(document).on('click', '#fg-add-payment-modal', function(e) {
                if (e.target.id === 'fg-add-payment-modal') {
                    $('#fg-add-payment-modal').fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
            
            // Handle form submission
            $(document).on('submit', '#fg-modal-payment-form', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                var originalText = $submitBtn.html();
                
                $submitBtn.prop('disabled', true).html('Salvataggio in corso...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $form.serialize() + '&action=fg_save_payment&nonce=' + (fg_admin_ajax.nonce || ''),
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#fg-payment-form-container').html(
                                '<div style="text-align: center; padding: 40px;">' +
                                '<div style="font-size: 48px; color: #00a32a; margin-bottom: 20px;">✓</div>' +
                                '<h3 style="color: #00a32a; margin-bottom: 10px;">Pagamento salvato con successo!</h3>' +
                                '<p style="color: #666;">Il pagamento è stato aggiunto e la lista verrà aggiornata...</p>' +
                                '</div>'
                            );
                            
                            // Reload the page after 1.5 seconds to show the new payment
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            alert('Errore nel salvataggio: ' + (response.data || 'Errore sconosciuto'));
                            $submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        alert('Errore nella comunicazione con il server.');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
        
        // Function to initialize payment form handlers in modal
        function initializePaymentFormHandlers() {
            var $tipoPagamento = $('#fg_modal_tipo_pagamento');
            var donorId = $('#fg-modal-payment-form input[name="donor_id"]').val();
            
            function toggleModalPaymentFields() {
                var tipo = $tipoPagamento.val();
                
                $('.fg-modal-conditional-field').hide();
                
                if (tipo === 'evento') {
                    $('#fg_modal_evento_field').show();
                    // Unlock amount field
                    $('#fg_modal_importo').prop('readonly', false).css('background-color', '');
                } else if (tipo === 'quota') {
                    $('#fg_modal_categoria_socio_field').show();
                    // Auto-load member's category and quota
                    updateModalPaymentAmount();
                } else if (tipo === 'raccolta') {
                    $('#fg_modal_raccolta_field').show();
                    // Unlock amount field
                    $('#fg_modal_importo').prop('readonly', false).css('background-color', '');
                } else {
                    // Unlock amount field for other types
                    $('#fg_modal_importo').prop('readonly', false).css('background-color', '');
                }
            }
            
            function updateModalPaymentAmount() {
                if (donorId && $tipoPagamento.val() === 'quota') {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_member_quota',
                            socio_id: donorId,
                            nonce: fg_admin_ajax.nonce || ''
                        },
                        success: function(response) {
                            if (response.success) {
                                // Auto-select the member's category
                                if (response.data.categoria_id) {
                                    $('#fg_modal_categoria_socio_id').val(response.data.categoria_id);
                                }
                                
                                // Auto-populate amount and make it readonly
                                if (response.data.quota) {
                                    $('#fg_modal_importo').val(response.data.quota);
                                    $('#fg_modal_importo').prop('readonly', true);
                                    $('#fg_modal_importo').css('background-color', '#f0f0f0');
                                }
                            }
                        }
                    });
                }
            }
            
            // Lock/Unlock button handler for modal categoria socio
            $('#fg_modal_unlock_categoria_socio').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $select = $('#fg_modal_categoria_socio_id');
                var $icon = $button.find('.dashicons');
                
                if ($select.prop('disabled')) {
                    // Unlock the field
                    $select.prop('disabled', false);
                    $select.css('background-color', '');
                    $icon.removeClass('dashicons-lock').addClass('dashicons-unlock');
                    $button.html('<span class="dashicons dashicons-unlock" style="margin-top: 3px;"></span> Blocca');
                    $('#fg_modal_categoria_socio_description').html('Il campo è sbloccato. La modifica aggiornerà la tipologia socio del donatore.');
                } else {
                    // Lock the field
                    $select.prop('disabled', true);
                    $select.css('background-color', '#f0f0f0');
                    $icon.removeClass('dashicons-unlock').addClass('dashicons-lock');
                    $button.html('<span class="dashicons dashicons-lock" style="margin-top: 3px;"></span> Sblocca');
                    $('#fg_modal_categoria_socio_description').html('Clicca su "Sblocca" per modificare la categoria. La modifica aggiornerà anche la tipologia socio del donatore.');
                }
            });
            
            // Update amount when modal categoria socio changes
            $('#fg_modal_categoria_socio_id').on('change', function() {
                var categoriaId = $(this).val();
                if (donorId && $tipoPagamento.val() === 'quota' && categoriaId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_member_quota',
                            socio_id: donorId,
                            categoria_id: categoriaId,
                            nonce: fg_admin_ajax.nonce || ''
                        },
                        success: function(response) {
                            if (response.success && response.data.quota) {
                                $('#fg_modal_importo').val(response.data.quota);
                                $('#fg_modal_importo').prop('readonly', true);
                                $('#fg_modal_importo').css('background-color', '#f0f0f0');
                            }
                        }
                    });
                }
            });
            
            $tipoPagamento.on('change', toggleModalPaymentFields);
            toggleModalPaymentFields();
        }
        
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
        
        // Pre-fill payment form when coming from calendar
        var urlParams = new URLSearchParams(window.location.search);
        var socioIdFromUrl = urlParams.get('socio_id');
        
        if (socioIdFromUrl && $('#fg_socio_id').length) {
            // Set the socio
            $('#fg_socio_id').val(socioIdFromUrl).trigger('change');
            
            // Set payment type to quota
            setTimeout(function() {
                $('#fg_tipo_pagamento').val('quota').trigger('change');
            }, 500);
        }
        
        // Category selector for auto-filling quota in member form
        $('#fg_categoria_socio_selector').on('change', function() {
            var categoryId = $(this).val();
            var quota = $(this).find('option:selected').data('quota');
            
            if (categoryId && quota) {
                // Fill in the quota field
                $('#fg_quota_annuale').val(quota);
                
                // Also check the category in the sidebar taxonomy checkboxes
                $('#fg_categoria_sociotaxonomy input[type="checkbox"][value="' + categoryId + '"]').prop('checked', true);
            }
        });
        
        // Handle click on event donations count to show details
        $(document).on('click', '.fg-donatori-evento-count', function(e) {
            e.preventDefault();
            var eventoId = $(this).data('evento-id');
            var eventoTitolo = $(this).closest('tr').find('strong a').text();
            
            if (!eventoId) {
                return;
            }
            
            // Show loading message
            var modalHtml = '<div id="fg-donations-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 100000; display: flex; align-items: center; justify-content: center;">' +
                '<div style="background: #fff; border-radius: 8px; padding: 20px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">' +
                '<h2 style="margin: 0; color: #0073aa;">Donazioni per: ' + eventoTitolo + '</h2>' +
                '<button id="fg-close-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>' +
                '</div>' +
                '<div id="fg-modal-content" style="text-align: center; padding: 20px;"><p>Caricamento...</p></div>' +
                '</div>' +
                '</div>';
            
            $('body').append(modalHtml);
            
            // Load donations via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_get_evento_donations',
                    evento_id: eventoId
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        $('#fg-modal-content').html(response.data.html);
                    } else {
                        $('#fg-modal-content').html('<p>Nessuna donazione trovata per questo evento.</p>');
                    }
                },
                error: function() {
                    $('#fg-modal-content').html('<p style="color: #d63638;">Errore nel caricamento dei dati.</p>');
                }
            });
            
            // Close modal on button click or background click
            $(document).on('click', '#fg-close-modal, #fg-donations-modal', function(e) {
                if (e.target.id === 'fg-close-modal' || e.target.id === 'fg-donations-modal') {
                    $('#fg-donations-modal').remove();
                }
            });
        });
        
        // Make taxonomy metaboxes read-only for fg_categoria_socio and fg_categoria_donatore
        // on the donor edit page, with a notice
        if ($('body').hasClass('post-type-fg_socio')) {
            // Helper function to add read-only notice
            function makeReadOnly($element) {
                // Disable all checkboxes
                $element.find('input[type="checkbox"]').prop('disabled', true);
                $element.find('input[type="checkbox"]').css('opacity', '0.5');
                
                // Add notice if not already present
                if (!$element.find('.fg-readonly-notice').length) {
                    $element.append(
                        '<p class="fg-readonly-notice">Solo visualizzazione, modificare il dato nel form.</p>'
                    );
                }
            }
            
            // Make Tipologia Socio (fg_categoria_socio) read-only - correct WordPress metabox div
            var $categoriaSocioBox = $('#fg_categoria_sociodiv');
            if ($categoriaSocioBox.length) {
                makeReadOnly($categoriaSocioBox);
            }
            
            // Make Categoria Donatore (fg_categoria_donatore) read-only - correct WordPress metabox div
            var $categoriaDonatore = $('#fg_categoria_donatorediv');
            if ($categoriaDonatore.length) {
                makeReadOnly($categoriaDonatore);
            }
        }
        
    });
    
})(jQuery);
