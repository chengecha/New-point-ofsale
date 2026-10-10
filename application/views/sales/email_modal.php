<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel"><?php echo $this->lang->line('sales_email_compose'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="emailForm" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                 <style>
                #emailModal label { font-size: 0.85rem; }
                #emailModal .form-control, #emailModal .form-control-file { font-size: 0.85rem; padding: 2px 6px; height: auto; }
                #emailModal textarea.form-control { padding: 2px 6px; }
                #emailModal .form-group { margin-bottom: 0.5rem; }
                #emailModal .form-text { font-size: 0.75rem; }
                #emailLoadingOverlay {
                    display: none;
                    position: fixed;
                    top: 0; left: 0;
                    width: 100%; height: 100%;
                    background: rgba(255, 255, 255, 0.85);
                    z-index: 1060;
                    text-align: center;
                    padding-top: 20%;
                }
                #emailLoadingOverlay .rotating {
                    display: inline-block;
                    animation: emailRotate 1s linear infinite;
                }
                @keyframes emailRotate {
                    100% { transform: rotate(360deg); }
                }
                </style>
                <div id="emailLoadingOverlay">
                    <div class="rotating">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Sending...</span>
                        </div>
                    </div>
                    <p class="mt-3" style="font-size: 1rem;">Sending email, please wait...</p>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email_to"><?php echo $this->lang->line('sales_email_to'); ?></label>
                        <input type="email" class="form-control input-sm" id="email_to" name="to" value="<?php echo htmlspecialchars($customer_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email_cc"><?php echo $this->lang->line('sales_email_cc'); ?></label>
                        <input type="email" class="form-control input-sm" id="email_cc" name="cc" placeholder="<?php echo $this->lang->line('sales_email_cc_placeholder'); ?>">
                        <small class="form-text text-muted"><?php echo $this->lang->line('sales_email_cc_help'); ?></small>
                    </div>
                    <div class="form-group">
                        <label for="email_subject"><?php echo $this->lang->line('sales_email_subject'); ?></label>
                        <input type="text" class="form-control input-sm" id="email_subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email_message"><?php echo $this->lang->line('sales_email_message'); ?></label>
                        <textarea class="form-control input-sm" id="email_message" name="message" rows="8" required><?php echo $message; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email_attachments"><?php echo $this->lang->line('sales_email_attachments'); ?></label>
                        <input type="file" class="form-control-file" id="email_attachments" name="attachments[]" multiple accept=".pdf,.png,.jpg,.jpeg,.csv,.xls,.xlsx,.doc,.docx,.txt">
                        <small class="form-text text-muted"><?php echo $this->lang->line('sales_email_attachments_help'); ?></small>
                        <div id="attachment_list" class="mt-2"></div>
                    </div>
                    <input type="hidden" name="sale_id" value="<?php echo $sale_id; ?>">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <?php if (!empty($default_attachment)): ?>
                        <input type="hidden" name="default_attachment" value="<?php echo $default_attachment; ?>">
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->lang->line('common_cancel'); ?></button>
                    <button type="submit" class="btn btn-primary" id="email_send_btn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <?php echo $this->lang->line('sales_email_send'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var attachmentFiles = [];
    
    $('#email_attachments').on('change', function(e) {
        var files = e.target.files;
        var $list = $('#attachment_list');
        $list.empty();
        
        for (var i = 0; i < files.length; i++) {
            attachmentFiles.push(files[i]);
            var badge = $('<span class="badge badge-info mr-1 mb-1"></span>')
                .text(files[i].name + ' (' + formatFileSize(files[i].size) + ')')
                .append($('<button type="button" class="close ml-1" aria-label="Remove">&times;</button>')
                    .click({index: i}, function(e) {
                        attachmentFiles.splice(e.data.index, 1);
                        $(this).parent().remove();
                        updateFileInput();
                    }));
            $list.append(badge);
        }
        updateFileInput();
    });
    
    function updateFileInput() {
        var dt = new DataTransfer();
        for (var i = 0; i < attachmentFiles.length; i++) {
            dt.items.add(attachmentFiles[i]);
        }
        $('#email_attachments')[0].files = dt.files;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    $('#emailForm').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $('#email_send_btn');
        var $spinner = $btn.find('.spinner-border');
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        
        var formData = new FormData(this);
        
        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', csrf_token());
        
        for (var i = 0; i < attachmentFiles.length; i++) {
            formData.append('attachments[]', attachmentFiles[i]);
        }
        
        $.ajax({
            url: '<?php echo site_url("sales/send_email_modal"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#emailLoadingOverlay').show();
            },
            success: function(response) {
                $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger' });
                if (response.success) {
                    $('#emailModal').modal('hide');
                }
            },
            error: function() {
                $.notify({ message: '<?php echo $this->lang->line('sales_email_error'); ?>' }, { type: 'danger' });
            },
            complete: function() {
                $('#emailLoadingOverlay').hide();
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        });
    });
    
    $('#emailModal').on('hidden.bs.modal', function() {
        $('#emailForm')[0].reset();
        attachmentFiles = [];
        $('#attachment_list').empty();
        updateFileInput();
    });
});
</script>