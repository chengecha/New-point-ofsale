<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="config_auth_overlay" class="config-auth-overlay"></div>

<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#info_tab" title="<?php echo $this->lang->line('config_info_configuration'); ?>"><?php echo $this->lang->line('config_info'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#general_tab" title="<?php echo $this->lang->line('config_general_configuration'); ?>"><?php echo $this->lang->line('config_general'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_tab" title="<?php echo $this->lang->line('config_tax_configuration'); ?>"><?php echo $this->lang->line('config_tax'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#locale_tab" title="<?php echo $this->lang->line('config_locale_configuration'); ?>"><?php echo $this->lang->line('config_locale'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#barcode_tab" title="<?php echo $this->lang->line('config_barcode_configuration'); ?>"><?php echo $this->lang->line('config_barcode'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#stock_tab" title="<?php echo $this->lang->line('config_location_configuration'); ?>"><?php echo $this->lang->line('config_location'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#receipt_tab" title="<?php echo $this->lang->line('config_receipt_configuration'); ?>"><?php echo $this->lang->line('config_receipt'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#invoice_tab" title="<?php echo $this->lang->line('config_invoice_configuration'); ?>"><?php echo $this->lang->line('config_invoice'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#reward_tab" title="<?php echo $this->lang->line('config_reward_configuration'); ?>"><?php echo $this->lang->line('config_reward'); ?></a>
	</li>
	<?php /* ?>
	<li role="presentation">
		<a data-toggle="tab" href="#table_tab" title="<?php echo $this->lang->line('config_table_configuration'); ?>"><?php echo $this->lang->line('config_table'); ?></a>
	</li>
	<?php */ ?>
	<li role="presentation">
		<a data-toggle="tab" href="#system_tab" title="<?php echo $this->lang->line('config_system_conf'); ?>"><?php echo $this->lang->line('config_system_conf'); ?></a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="info_tab">
		<?php $this->load->view("configs/info_config"); ?>
	</div>
	<div class="tab-pane" id="general_tab">
		<?php $this->load->view("configs/general_config"); ?>
	</div>
	<div class="tab-pane" id="tax_tab">
		<?php $this->load->view("configs/tax_config"); ?>
	</div>
	<div class="tab-pane" id="locale_tab">
		<?php $this->load->view("configs/locale_config"); ?>
	</div>
	<div class="tab-pane" id="barcode_tab">
		<?php $this->load->view("configs/barcode_config"); ?>
	</div>
	<div class="tab-pane" id="stock_tab">
		<?php $this->load->view("configs/stock_config"); ?>
	</div>
	<div class="tab-pane" id="receipt_tab">
		<?php $this->load->view("configs/receipt_config"); ?>
	</div>
	<div class="tab-pane" id="invoice_tab">
		<?php $this->load->view("configs/invoice_config"); ?>
	</div>
	<div class="tab-pane" id="reward_tab">
		<?php $this->load->view("configs/reward_config"); ?>
	</div>
	<?php /* ?>
	<div class="tab-pane" id="table_tab">
		<?php $this->load->view("configs/table_config"); ?>
	</div>
	<?php */ ?>
	<div class="tab-pane" id="system_tab">
		<?php $this->load->view("configs/system_config"); ?>
	</div>
</div>

<?php if (!$config_authed): ?>
<div id="config_auth_prompt" class="config-auth-prompt">
	<div class="alert alert-warning text-center">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
		<?php echo $this->lang->line('config_auth_locked'); ?>
	</div>
</div>
<?php endif; ?>

<?php $this->load->view("configs/config_auth_modal"); ?>

<?php $this->load->view("partial/footer"); ?>

<style type="text/css">
	.config-auth-overlay {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 0, 0, 0.5);
		z-index: 1040;
		display: none;
	}
	.config-auth-overlay.visible {
		display: block;
	}
	.config-auth-prompt {
		padding: 10px 15px;
		margin: 15px 0;
		border: 1px solid #f8d7da;
		background-color: #f8d7da;
		border-radius: 4px;
	}
</style>

<script type="text/javascript">
(function($) {
	'use strict';

	var serverAuthed = <?php echo $config_authed ? 'true' : 'false'; ?>;
	var isAuthenticated = serverAuthed;

	// When the auth modal is shown/hidden, toggle the page overlay
	$('#config_auth_modal').on('shown.bs.modal', function() {
		$('#config_auth_overlay').addClass('visible');
	});
	$('#config_auth_modal').on('hidden.bs.modal', function() {
		$('#config_auth_overlay').removeClass('visible');
	});

	if (serverAuthed) {
		$('#config_auth_overlay').removeClass('visible');
	}

	// Intercept clicks on the "Setup & Conf" tab.
	// Every time the user clicks this tab from another tab, the auth
	// modal is shown. On success the tab is programmatically activated.
	$('a[data-toggle="tab"][href="#system_tab"]').on('click', function(e) {
		var $link = $(this);

		// If this tab is already active, allow it (no modal needed)
		if ($link.parent().hasClass('active')) {
			return;
		}

		// If already authenticated, just switch to the tab
		if (isAuthenticated) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		if (typeof window.configAuth !== 'undefined') {
			window.configAuth.show(function() {
				// After successful auth, update client-side state,
				// hide the auth prompt, and switch to the Setup & Conf tab
				isAuthenticated = true;
				$('#config_auth_prompt').hide();
				$link.tab('show');
			});
		}
	});

	// Config auth persists across tab switches; it expires via the
	// server-side timeout check in _is_config_authenticated().

})(jQuery);
</script>
