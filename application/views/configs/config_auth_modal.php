<div id="config_auth_modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">
					<span class="glyphicon glyphicon-lock"></span> <?php echo $this->lang->line('config_auth_required'); ?>
				</h4>
			</div>
			<form id="config_auth_form" method="post">
				<div class="modal-body">
					<ul id="config_auth_error_message_box" class="error_message_box"></ul>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('config_auth_password'), 'config_auth_password', array('class' => 'control-label col-xs-4')); ?>
						<div class="col-xs-8">
							<div class="input-group">
								<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
								<?php echo form_input(array(
									'name' => 'password',
									'id' => 'config_auth_password',
									'class' => 'form-control input-sm',
									'type' => 'password',
									'autocomplete' => 'new-password'
								)); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="config_auth_cancel" class="btn btn-default btn-sm" data-dismiss="modal">
						<?php echo $this->lang->line('config_auth_cancel'); ?>
					</button>
					<?php echo form_submit(array(
						'name' => 'config_auth_submit',
						'id' => 'config_auth_submit',
						'value' => $this->lang->line('common_submit'),
						'class' => 'btn btn-primary btn-sm'
					)); ?>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
(function($) {
	'use strict';

	var $modal     = $('#config_auth_modal');
	var $form      = $('#config_auth_form');
	var $errBox    = $('#config_auth_error_message_box');
	var $submitBtn = $('#config_auth_submit');
	var $cancelBtn = $('#config_auth_cancel');

	function showError(msg)
	{
		$errBox.empty();
		if (msg) { $errBox.append('<li>' + msg + '</li>'); }
	}

	function clearError()
	{
		$errBox.empty();
	}

	function hideModal()
	{
		if (!$modal.length) { return; }
		$('#config_auth_overlay').removeClass('visible');
		$modal.modal('hide');
	}

	function redirectToDashboard()
	{
		window.location.href = '<?php echo site_url("home/office"); ?>';
	}

	// Called by the tab-click handler when the user successfully
	// authenticates. Switches the active tab to #system_tab.
	function activateSystemTab()
	{
		// Trigger Bootstrap tab activation on the Setup & Conf tab link
		var $sysTab = $('a[data-toggle="tab"][href="#system_tab"]');
		if ($sysTab.length) {
			$sysTab.tab('show');
		}
	}

	function showModal(onAuthed)
	{
		if (!$modal.length) { return; }
		clearError();
		$('#config_auth_password').val('');
		$('#config_auth_overlay').addClass('visible');
		$modal.modal('show');

		// Once the modal is shown, hook the submit to activate the tab
		$form.off('submit.configAuth').on('submit.configAuth', function(e) {
			e.preventDefault();
			clearError();
			$submitBtn.prop('disabled', true).css('opacity', 0.6);

			$.ajax({
				url: '<?php echo site_url($controller_name . "/verify_config_auth"); ?>',
				type: 'POST',
				dataType: 'json',
				data: {
					password: $('#config_auth_password').val()
				},
				success: function(response) {
					$submitBtn.prop('disabled', false).css('opacity', 1);
					if (response && response.success) {
						hideModal();
						if (typeof onAuthed === 'function') {
							onAuthed();
						}
					} else {
						showError(response && response.message ? response.message : '<?php echo $this->lang->line("config_auth_invalid_credentials"); ?>');
					}
				},
				error: function() {
					$submitBtn.prop('disabled', false).css('opacity', 1);
					showError('<?php echo $this->lang->line("config_auth_invalid_credentials"); ?>');
				}
			});
		});
	}

	$cancelBtn.on('click', function(e) {
		e.preventDefault();
		hideModal();
		redirectToDashboard();
	});

	$modal.on('hidden.bs.modal', function() {
		// If the modal was dismissed without authenticating, go to dashboard
		$('#config_auth_overlay').removeClass('visible');
	});

	// Expose helpers so manage.php can trigger the modal
	window.configAuth = {
		show: function(onAuthed) { showModal(onAuthed); },
		hide: hideModal
	};

})(jQuery);
</script>
