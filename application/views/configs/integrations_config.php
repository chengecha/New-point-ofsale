<?php echo form_open('config/save_mpesa/', array('id' => 'mpesa_config_form', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<div id="integrations_header"><?php echo $this->lang->line('config_mpesa_configuration')?></div>
			<ul id="mpesa_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_env'), 'mpesa_env', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-globe"></span></span>
						<?php echo form_dropdown('mpesa_env',
							array('sandbox' => $this->lang->line('config_mpesa_env_sandbox'), 'production' => $this->lang->line('config_mpesa_env_production')),
							!empty($mpesa['env']) ? $mpesa['env'] : 'sandbox',
							array('id' => 'mpesa_env', 'class' => 'form-control input-sm')); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_consumer_key'), 'mpesa_consumer_key', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-cloud"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_consumer_key',
							'id' => 'mpesa_consumer_key',
							'class' => 'form-control input-sm',
							'value' => $mpesa['consumer_key'])); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_consumer_secret'), 'mpesa_consumer_secret', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_consumer_secret',
							'id' => 'mpesa_consumer_secret',
							'class' => 'form-control input-sm',
							'value' => $mpesa['consumer_secret'])); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_shortcode'), 'mpesa_shortcode', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-credit-card"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_shortcode',
							'id' => 'mpesa_shortcode',
							'class' => 'form-control input-sm',
							'value' => $mpesa['shortcode'])); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_passkey'), 'mpesa_passkey', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-key"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_passkey',
							'id' => 'mpesa_passkey',
							'class' => 'form-control input-sm',
							'value' => $mpesa['passkey'])); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_callback_url'), 'mpesa_callback_url', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-8">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-link"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_callback_url',
							'id' => 'mpesa_callback_url',
							'class' => 'form-control input-sm',
							'value' => $mpesa['callback_url'])); ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_stk_push_url'), 'mpesa_stk_push_url', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-8">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-flash"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_stk_push_url',
							'id' => 'mpesa_stk_push_url',
							'class' => 'form-control input-sm',
							'value' => $mpesa['stk_push_url'])); ?>
					</div>
					<span class="help-block"><?php echo $this->lang->line('config_mpesa_stk_push_url_help'); ?></span>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_query_status_url'), 'mpesa_query_status_url', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-8">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-search"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_query_status_url',
							'id' => 'mpesa_query_status_url',
							'class' => 'form-control input-sm',
							'value' => $mpesa['query_status_url'])); ?>
					</div>
					<span class="help-block"><?php echo $this->lang->line('config_mpesa_query_status_url_help'); ?></span>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mpesa_ais_url'), 'mpesa_ais_url', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-8">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
						<?php echo form_input(array(
							'name' => 'mpesa_ais_url',
							'id' => 'mpesa_ais_url',
							'class' => 'form-control input-sm',
							'value' => $mpesa['ais_url'])); ?>
					</div>
					<span class="help-block"><?php echo $this->lang->line('config_mpesa_ais_url_help'); ?></span>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<div class="col-xs-8 col-xs-offset-2">
					<?php echo form_submit(array(
						'name' => 'submit_mpesa',
						'id' => 'submit_mpesa',
						'value' => $this->lang->line('common_submit'),
						'class' => 'btn btn-primary btn-sm pull-right')); ?>
					<button type="button" id="test_mpesa_connection" class="btn btn-info btn-sm pull-right" style="margin-right: 5px;">
						<span class="glyphicon glyphicon-wrench"></span> <?php echo $this->lang->line('config_mpesa_test_connection'); ?>
					</button>
				</div>
			</div>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#test_mpesa_connection').click(function() {
		var env = $('#mpesa_env').val();
		var consumer_key = $('#mpesa_consumer_key').val();
		var consumer_secret = $('#mpesa_consumer_secret').val();
		var shortcode = $('#mpesa_shortcode').val();
		var passkey = $('#mpesa_passkey').val();

		$.post("<?php echo site_url($controller_name . '/ajax_test_mpesa_connection')?>", {
			'mpesa_env': env,
			'mpesa_consumer_key': consumer_key,
			'mpesa_consumer_secret': consumer_secret,
			'mpesa_shortcode': shortcode,
			'mpesa_passkey': passkey
		},
			function(response) {
				$.notify({message: response.message}, {type: response.success ? 'success' : 'danger'} );
			},
			'json'
		);
	});

	$('#mpesa_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response) {
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#mpesa_error_message_box'
	}));
});
</script>
