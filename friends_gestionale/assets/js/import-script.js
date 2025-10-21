/**
 * Friends Gestionale - Import JavaScript
 */

(function($) {
    'use strict';
    
    var FG_Import = {
        currentStep: 'upload',
        importId: null,
        headers: [],
        mapping: {},
        fileData: null,
        
        init: function() {
            this.bindEvents();
            this.setupDragDrop();
        },
        
        bindEvents: function() {
            var self = this;
            
            // File selection
            $('#fg-select-file-btn').on('click', function() {
                $('#fg-file-input').click();
            });
            
            $('#fg-file-input').on('change', function(e) {
                if (this.files.length > 0) {
                    self.handleFileSelect(this.files[0]);
                }
            });
            
            // Navigation buttons
            $('#fg-back-to-upload-btn').on('click', function() {
                self.showStep('upload');
            });
            
            $('#fg-back-to-mapping-btn').on('click', function() {
                self.showStep('mapping');
            });
            
            $('#fg-preview-import-btn').on('click', function() {
                self.previewImport();
            });
            
            $('#fg-execute-import-btn').on('click', function() {
                self.executeImport();
            });
            
            $('#fg-new-import-btn').on('click', function() {
                location.reload();
            });
            
            // Mapping change handler
            $(document).on('change', '.fg-mapping-select', function() {
                var field = $(this).data('field');
                var value = $(this).val();
                self.mapping[field] = value;
                
                // Show/hide static value input
                var $row = $(this).closest('.fg-mapping-row');
                if (value === 'static') {
                    $row.find('.fg-mapping-static').show();
                } else {
                    $row.find('.fg-mapping-static').hide();
                }
            });
            
            // Static value handler
            $(document).on('input', '.fg-mapping-static', function() {
                var field = $(this).data('field');
                var value = $(this).val();
                self.mapping[field] = 'static:' + value;
            });
            
            // Template handlers
            $('#fg-mapping-template').on('change', function() {
                var templateKey = $(this).val();
                if (templateKey) {
                    self.loadMappingTemplate(templateKey);
                }
            });
            
            $('#fg-save-template-btn').on('click', function() {
                self.saveMappingTemplate();
            });
        },
        
        setupDragDrop: function() {
            var self = this;
            var $dropZone = $('#fg-drop-zone');
            
            $dropZone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            
            $dropZone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            
            $dropZone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    self.handleFileSelect(files[0]);
                }
            });
        },
        
        handleFileSelect: function(file) {
            var self = this;
            
            // Validate file type
            var fileName = file.name.toLowerCase();
            if (!fileName.endsWith('.csv') && !fileName.endsWith('.xlsx') && !fileName.endsWith('.xls')) {
                alert('Formato file non supportato. Usa CSV o XLSX.');
                return;
            }
            
            // Show progress
            $('#fg-drop-zone').hide();
            $('#fg-upload-progress').show();
            $('#fg-upload-error').hide();
            
            // Upload file
            var formData = new FormData();
            formData.append('action', 'fg_upload_import_file');
            formData.append('nonce', fg_import_vars.nonce);
            formData.append('file', file);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.importId = response.data.import_id;
                        self.headers = response.data.headers;
                        self.fileData = response.data;
                        self.showMappingStep();
                    } else {
                        self.showError(response.data.message);
                    }
                },
                error: function() {
                    self.showError('Errore di connessione');
                }
            });
        },
        
        showError: function(message) {
            $('#fg-upload-progress').hide();
            $('#fg-upload-error').show().find('p').text(message);
            $('#fg-drop-zone').show();
        },
        
        showMappingStep: function() {
            var self = this;
            
            // Build mapping UI
            var html = '';
            var fields = fg_import_vars.fields;
            var tooltips = fg_import_vars.tooltips || {};
            
            $.each(fields, function(key, label) {
                html += '<div class="fg-mapping-row">';
                html += '<div class="fg-mapping-label">' + label;
                
                // Add tooltip if exists
                if (tooltips[key]) {
                    html += ' <span class="fg-tooltip" title="' + tooltips[key] + '" style="cursor: help; color: #0073aa;">ⓘ</span>';
                }
                
                html += '</div>';
                html += '<select class="fg-mapping-select" data-field="' + key + '">';
                html += '<option value="">-- Non importare --</option>';
                
                // Add column options
                var hasSelected = false;
                $.each(self.headers, function(i, header) {
                    var selected = self.autoMapField(key, header) ? ' selected' : '';
                    if (selected) {
                        hasSelected = true;
                    }
                    html += '<option value="' + header + '"' + selected + '>' + header + '</option>';
                });
                
                html += '<option value="static">Valore statico...</option>';
                html += '</select>';
                html += '<input type="text" class="fg-mapping-static" data-field="' + key + '" placeholder="Inserisci valore statico" />';
                html += '</div>';
                
                // Set initial mapping based on auto-match
                var matched = self.findMatchingHeader(key);
                if (matched) {
                    self.mapping[key] = matched;
                }
            });
            
            $('#fg-mapping-container').html(html);
            
            this.showStep('mapping');
        },
        
        autoMapField: function(field, header) {
            return this.findMatchingHeader(field) === header;
        },
        
        findMatchingHeader: function(field) {
            var self = this;
            var fieldLower = field.toLowerCase();
            var matches = {
                'ragione_sociale': ['ragione sociale', 'ragione_sociale', 'company', 'azienda'],
                'nome': ['nome', 'name', 'first name', 'firstname', 'first_name'],
                'cognome': ['cognome', 'surname', 'last name', 'lastname', 'last_name'],
                'email': ['email', 'e-mail', 'mail', 'posta'],
                'telefono': ['telefono', 'phone', 'tel', 'cellulare', 'mobile'],
                'indirizzo': ['indirizzo', 'address', 'via', 'street'],
                'citta': ['citta', 'città', 'city', 'comune'],
                'cap': ['cap', 'zip', 'postal code', 'codice postale'],
                'provincia': ['provincia', 'prov', 'province', 'state'],
                'nazione': ['nazione', 'country', 'paese', 'stato'],
                'ruolo': ['ruolo', 'tipo', 'role', 'type', 'socio', 'donatore'],
                'data_iscrizione': ['data iscrizione', 'data_iscrizione', 'registration date', 'date', 'data'],
                'data_scadenza': ['data scadenza', 'data_scadenza', 'expiration date', 'expiry date', 'scadenza'],
                'partita_iva': ['partita iva', 'partita_iva', 'vat', 'p.iva', 'piva'],
                'codice_fiscale': ['codice fiscale', 'codice_fiscale', 'cf', 'tax code', 'fiscal code'],
                'note': ['note', 'notes', 'commenti', 'comments', 'descrizione']
            };
            
            if (matches[fieldLower]) {
                for (var i = 0; i < self.headers.length; i++) {
                    var headerLower = self.headers[i].toLowerCase();
                    for (var j = 0; j < matches[fieldLower].length; j++) {
                        if (headerLower === matches[fieldLower][j] || 
                            headerLower.indexOf(matches[fieldLower][j]) !== -1) {
                            return self.headers[i];
                        }
                    }
                }
            }
            
            return null;
        },
        
        previewImport: function() {
            var self = this;
            
            // Show loading
            $('#fg-preview-import-btn').prop('disabled', true).text('Caricamento...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_preview_import',
                    nonce: fg_import_vars.nonce,
                    import_id: self.importId,
                    mapping: self.mapping,
                    update_existing: $('#fg-update-existing').is(':checked'),
                    skip_existing: $('#fg-skip-existing').is(':checked')
                },
                success: function(response) {
                    $('#fg-preview-import-btn').prop('disabled', false).text('Anteprima Import');
                    
                    if (response.success) {
                        self.showPreviewStep(response.data);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    $('#fg-preview-import-btn').prop('disabled', false).text('Anteprima Import');
                    alert('Errore di connessione');
                }
            });
        },
        
        showPreviewStep: function(preview) {
            var self = this;
            
            // Build summary with total count
            var summaryHtml = '';
            summaryHtml += '<div class="fg-preview-header">';
            summaryHtml += '<h3>Totale righe nel file: <strong>' + preview.total + '</strong></h3>';
            summaryHtml += '<p class="description">Le statistiche seguenti si basano su tutte le ' + preview.total + ' righe del file.</p>';
            summaryHtml += '</div>';
            
            summaryHtml += '<div class="fg-preview-stats">';
            summaryHtml += '<div class="fg-preview-stat success">';
            summaryHtml += '<div class="fg-preview-stat-value">' + preview.will_create + '</div>';
            summaryHtml += '<div class="fg-preview-stat-label">Da creare</div>';
            summaryHtml += '</div>';
            
            summaryHtml += '<div class="fg-preview-stat">';
            summaryHtml += '<div class="fg-preview-stat-value">' + preview.will_update + '</div>';
            summaryHtml += '<div class="fg-preview-stat-label">Da aggiornare</div>';
            summaryHtml += '</div>';
            
            summaryHtml += '<div class="fg-preview-stat warning">';
            summaryHtml += '<div class="fg-preview-stat-value">' + preview.will_skip + '</div>';
            summaryHtml += '<div class="fg-preview-stat-label">Da saltare</div>';
            summaryHtml += '</div>';
            
            summaryHtml += '<div class="fg-preview-stat error">';
            summaryHtml += '<div class="fg-preview-stat-value">' + preview.has_errors + '</div>';
            summaryHtml += '<div class="fg-preview-stat-label">Con errori</div>';
            summaryHtml += '</div>';
            summaryHtml += '</div>';
            
            $('#fg-preview-summary').html(summaryHtml);
            
            // Build preview table (showing first 50 rows as sample)
            var tableHtml = '<div class="fg-preview-table-header">';
            tableHtml += '<p><strong>Anteprima (prime 50 righe):</strong></p>';
            tableHtml += '</div>';
            tableHtml += '<table class="fg-preview-table">';
            tableHtml += '<thead><tr>';
            tableHtml += '<th>Azione</th>';
            tableHtml += '<th>Nome</th>';
            tableHtml += '<th>Email</th>';
            tableHtml += '<th>Ruolo</th>';
            tableHtml += '<th>Messaggi</th>';
            tableHtml += '</tr></thead><tbody>';
            
            $.each(preview.rows.slice(0, 50), function(i, row) {
                var rowClass = 'row-' + row.status;
                tableHtml += '<tr class="' + rowClass + '">';
                tableHtml += '<td><span class="fg-row-status ' + row.status + '">' + row.action_label + '</span></td>';
                
                var displayName = '';
                if (row.data.ragione_sociale) {
                    displayName = row.data.ragione_sociale;
                } else {
                    displayName = (row.data.nome || '') + ' ' + (row.data.cognome || '');
                }
                tableHtml += '<td>' + displayName + '</td>';
                tableHtml += '<td>' + (row.data.email || '-') + '</td>';
                tableHtml += '<td>' + (row.data.tipo_donatore === 'anche_socio' ? 'Socio' : 'Donatore') + '</td>';
                
                var messages = [];
                if (row.errors.length > 0) {
                    messages = messages.concat(row.errors);
                }
                if (row.warnings.length > 0) {
                    messages = messages.concat(row.warnings);
                }
                tableHtml += '<td>' + (messages.length > 0 ? messages.join('<br>') : '-') + '</td>';
                tableHtml += '</tr>';
            });
            
            if (preview.rows.length > 50) {
                tableHtml += '<tr><td colspan="5" style="text-align: center; font-style: italic;">';
                tableHtml += 'Mostrate solo le prime 50 righe di ' + preview.total + ' totali';
                tableHtml += '</td></tr>';
            }
            
            tableHtml += '</tbody></table>';
            
            $('#fg-preview-table-container').html(tableHtml);
            
            this.showStep('preview');
        },
        
        executeImport: function() {
            var self = this;
            
            if (!confirm('Confermi di voler procedere con l\'import?')) {
                return;
            }
            
            // Show loading
            $('#fg-execute-import-btn').prop('disabled', true).text('Import in corso...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_execute_import',
                    nonce: fg_import_vars.nonce,
                    import_id: self.importId,
                    mapping: self.mapping,
                    update_existing: $('#fg-update-existing').is(':checked'),
                    skip_existing: $('#fg-skip-existing').is(':checked')
                },
                success: function(response) {
                    if (response.success) {
                        self.showResultsStep(response.data);
                    } else {
                        alert(response.data.message);
                        $('#fg-execute-import-btn').prop('disabled', false).text('Esegui Import');
                    }
                },
                error: function() {
                    alert('Errore di connessione');
                    $('#fg-execute-import-btn').prop('disabled', false).text('Esegui Import');
                }
            });
        },
        
        showResultsStep: function(results) {
            var html = '';
            
            html += '<div class="fg-result-box success">';
            html += '<h3>✓ Import Completato</h3>';
            html += '<ul>';
            html += '<li><strong>' + results.created + '</strong> donatori creati</li>';
            html += '<li><strong>' + results.updated + '</strong> donatori aggiornati</li>';
            html += '<li><strong>' + results.skipped + '</strong> donatori saltati</li>';
            html += '<li><strong>' + results.errors.length + '</strong> errori</li>';
            html += '</ul>';
            html += '</div>';
            
            if (results.errors.length > 0) {
                html += '<div class="fg-result-box" style="background: #ffeaea; border-color: #dc3232;">';
                html += '<h3>Errori Rilevati</h3>';
                html += '<p>Alcune righe non sono state importate a causa di errori.</p>';
                if (results.error_csv_url) {
                    html += '<p><a href="' + results.error_csv_url + '" class="button" download>Scarica CSV Errori</a></p>';
                }
                html += '<details><summary>Mostra primi 10 errori</summary><ul>';
                $.each(results.errors.slice(0, 10), function(i, error) {
                    html += '<li>Riga ' + error.row + ': ' + error.errors.join(', ') + '</li>';
                });
                html += '</ul></details>';
                html += '</div>';
            }
            
            $('#fg-import-results').html(html);
            this.showStep('results');
        },
        
        saveMappingTemplate: function() {
            var templateName = prompt('Nome del template:');
            if (!templateName) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_save_mapping_template',
                    nonce: fg_import_vars.nonce,
                    template_name: templateName,
                    mapping: this.mapping
                },
                success: function(response) {
                    if (response.success) {
                        alert('Template salvato con successo');
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        },
        
        loadMappingTemplate: function(templateKey) {
            var self = this;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fg_load_mapping_template',
                    nonce: fg_import_vars.nonce,
                    template_key: templateKey
                },
                success: function(response) {
                    if (response.success) {
                        self.mapping = response.data.mapping;
                        
                        // Update UI
                        $.each(self.mapping, function(field, value) {
                            var $select = $('.fg-mapping-select[data-field="' + field + '"]');
                            if (value.indexOf('static:') === 0) {
                                $select.val('static');
                                $select.closest('.fg-mapping-row').find('.fg-mapping-static').val(value.substr(7)).show();
                            } else {
                                $select.val(value);
                            }
                        });
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        },
        
        showStep: function(step) {
            $('.fg-import-step').hide();
            $('#fg-import-step-' + step).show();
            this.currentStep = step;
        }
    };
    
    $(document).ready(function() {
        if ($('.fg-import-wrap').length > 0) {
            FG_Import.init();
        }
    });
    
})(jQuery);
