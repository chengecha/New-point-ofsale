<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('cashups/save/'.$cash_ups_info->cashup_id, array('id'=>'cashups_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<?php echo form_hidden('closed_amount_cash', $cash_ups_info->closed_amount_cash); ?>
		<?php echo form_hidden('closed_amount_mpesa', $cash_ups_info->closed_amount_mpesa); ?>
		<?php echo form_hidden('closed_amount_due', $cash_ups_info->closed_amount_due); ?>
		<?php echo form_hidden('closed_amount_card', $cash_ups_info->closed_amount_card); ?>
		<?php echo form_hidden('closed_amount_check', $cash_ups_info->closed_amount_check); ?>
		<?php echo form_hidden('transfer_amount_cash', $cash_ups_info->transfer_amount_cash); ?>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_info'), 'cash_ups_info', array('class'=>'control-label col-xs-3')); ?>
			<?php echo form_label(!empty($cash_ups_info->cashup_id) ? $this->lang->line('cashups_id') . ' ' . $cash_ups_info->cashup_id : '', 'cashup_id', array('class'=>'control-label col-xs-8', 'style'=>'text-align:left')); ?>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_open_date'), 'open_date', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
					<?php echo form_input(array(
							'name'=>'open_date',
							'id'=>'open_date',
							'class'=>'form-control input-sm datepicker',
							'value'=>to_datetime(strtotime($cash_ups_info->open_date)))
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_open_employee'), 'open_employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('open_employee_id', $employees, $cash_ups_info->open_employee_id, 'id="open_employee_id" class="form-control"');?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_open_amount_cash'), 'open_amount_cash', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'open_amount_cash',
							'id'=>'open_amount_cash',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->open_amount_cash))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

	<?php echo form_input(array('name'=>'cash_in_amount', 'id'=>'cash_in_amount', 'value'=>$cash_ups_info->cash_in_amount, 'type'=>'hidden')); ?>
	<?php echo form_input(array('name'=>'cash_in_type', 'id'=>'cash_in_type', 'value'=>$cash_ups_info->cash_in_type, 'type'=>'hidden')); ?>
	<?php echo form_input(array('name'=>'cash_out_amount', 'id'=>'cash_out_amount', 'value'=>$cash_ups_info->cash_out_amount, 'type'=>'hidden')); ?>
	<?php echo form_input(array('name'=>'cash_out_type', 'id'=>'cash_out_type', 'value'=>$cash_ups_info->cash_out_type, 'type'=>'hidden')); ?>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_cash_in_amount'), 'cash_in_entries', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div id="cash_in_entries" style="margin-bottom: 5px;">
					<div class="input-group input-group-sm cash_in_row" style="margin-bottom: 5px;">
						<?php if (!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input(array(
								'name'=>'cash_in_amount_1',
								'class'=>'form-control input-sm cash_in_amount',
								'style'=>'width: 120px;',
								'value'=>to_currency_no_money($cash_ups_info->cash_in_amount))
								);?>
						<?php if (currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->lang->line('cashups_cash_in_type'); ?></b></span>
						<?php echo form_dropdown('cash_in_type_1', $payment_types, $cash_ups_info->cash_in_type, 'class="form-control input-sm cash_in_type" style="width: 100px;"');?>
						<span class="input-group-btn">
							<button type="button" class="btn btn-info btn-sm cash_in_add" title="<?php echo $this->lang->line('common_add'); ?>">
								<span class="glyphicon glyphicon-plus"></span>
							</button>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_cash_out_amount'), 'cash_out_entries', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div id="cash_out_entries" style="margin-bottom: 5px;">
					<div class="input-group input-group-sm cash_out_row" style="margin-bottom: 5px;">
						<?php if (!currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<?php echo form_input(array(
								'name'=>'cash_out_amount_1',
								'class'=>'form-control input-sm cash_out_amount',
								'style'=>'width: 120px;',
								'value'=>to_currency_no_money($cash_ups_info->cash_out_amount))
								);?>
						<?php if (currency_side()): ?>
							<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
						<?php endif; ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->lang->line('cashups_cash_out_type'); ?></b></span>
						<?php echo form_dropdown('cash_out_type_1', $payment_types, $cash_ups_info->cash_out_type, 'class="form-control input-sm cash_out_type" style="width: 100px;"');?>
						<span class="input-group-btn">
							<button type="button" class="btn btn-info btn-sm cash_out_add" title="<?php echo $this->lang->line('common_add'); ?>">
								<span class="glyphicon glyphicon-plus"></span>
							</button>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_total_expense'), 'total_expense', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'total_expense',
							'id'=>'total_expense',
							'readonly'=>'true',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->total_expense))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_total_trx_amount'), 'total_trx_amount', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'total_trx_amount',
							'id'=>'total_trx_amount',
							'readonly'=>'true',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->total_trx_amount))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_closed_amount_total'), 'closed_amount_total', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'closed_amount_total',
							'id'=>'closed_amount_total',
							'readonly'=>'true',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->closed_amount_total)
							));?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_expected_cash'), 'expected_cash', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'expected_cash',
							'id'=>'expected_cash',
							'readonly'=>'true',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->expected_cash))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_actual_cash_counted'), 'actual_cash_counted', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'actual_cash_counted',
							'id'=>'actual_cash_counted',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->actual_cash_counted))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_discrepancy_variance'), 'discrepancy_variance', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'discrepancy_variance',
							'id'=>'discrepancy_variance',
							'readonly'=>'true',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($cash_ups_info->discrepancy_variance))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

	<?php if(!empty($cash_ups_info->cashup_id)): ?>
	<div class="form-group form-group-sm" id="close_fields_section">
		<?php if(empty($cash_ups_info->closed_amount_total)): ?>
		<div class='col-xs-2 col-xs-offset-3'>
			<?php echo form_button(array(
				'name' => 'initiate_close_btn',
				'id' => 'initiate_close_btn',
				'content' => $this->lang->line('cashups_initiate_close'),
				'class' => 'btn btn-primary btn-sm'
			)); ?>
		</div>
		<div class='col-xs-2' style="padding-top: 5px; padding-left: 10px;">
			<?php echo form_checkbox(array(
				'name'=>'close_day',
				'id'=>'close_day',
				'value'=>1,
				'checked'=>0,
				'style'=>'margin-left: 10px;'
			)); ?>
			<?php echo form_label($this->lang->line('cashups_close_day'), 'close_day', array('class'=>'control-label')); ?>
		</div>
		<?php endif; ?>
		<div class='col-xs-3 close_fields_inner' style="display:<?php echo !empty($cash_ups_info->cashup_id) && empty($cash_ups_info->closed_amount_total) ? 'none' : 'block'; ?>;">
			<?php echo form_label($this->lang->line('cashups_close_date'), 'close_date', array('class'=>'required control-label')); ?>
			<?php echo form_input(array(
					'name'=>'close_date',
					'id'=>'close_date',
					'class'=>'form-control input-sm datepicker',
					'value'=>to_datetime(strtotime($cash_ups_info->close_date)),
					'readonly'=>'true'
					));?>
		</div>
	</div>

	<div class="form-group form-group-sm" id="close_employee_section" style="display:<?php echo !empty($cash_ups_info->cashup_id) && empty($cash_ups_info->closed_amount_total) ? 'none' : 'block'; ?>;">
		<div class='col-xs-3 col-xs-offset-3'>
			<?php echo form_label($this->lang->line('cashups_close_employee'), 'close_employee', array('class'=>'control-label')); ?>
		</div>
		<div class='col-xs-3'>
			<?php echo form_dropdown('close_employee_id', $employees, $cash_ups_info->close_employee_id, 'id="close_employee_id" class="form-control"');?>
		</div>
	</div>
	<?php endif; ?>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('cashups_note'), 'description', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_textarea(array(
					'name'=>'description',
					'id'=>'description',
					'class'=>'form-control input-sm',
					'value'=>$cash_ups_info->description)
					);?></div>
		</div>

		<?php
		if(!empty($cash_ups_info->cashup_id))
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('cashups_is_deleted').':', 'deleted', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-5'>
					<?php echo form_checkbox(array(
						'name'=>'deleted',
						'id'=>'deleted',
						'value'=>1,
						'checked'=>($cash_ups_info->deleted) ? 1 : 0)
					);?>
				</div>
			</div>
		<?php
		}
		?>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	<?php $this->load->view('partial/datepicker_locale'); ?>

	$('#open_date').datetimepicker({
		format: "<?php echo dateformat_bootstrap($this->config->item('dateformat')) . ' ' . dateformat_bootstrap($this->config->item('timeformat'));?>",
		startDate: "<?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), mktime(0, 0, 0, 1, 1, 2010));?>",
		<?php
		$t = $this->config->item('timeformat');
		$m = $t[strlen($t)-1];
		if( strpos($this->config->item('timeformat'), 'a') !== false || strpos($this->config->item('timeformat'), 'A') !== false )
		{
		?>
			showMeridian: true,
		<?php
		}
		else
		{
		?>
			showMeridian: false,
		<?php
		}
		?>
		minuteStep: 1,
		autoclose: true,
		todayBtn: true,
		todayHighlight: true,
		bootcssVer: 3,
		language: '<?php echo current_language_code(); ?>'
	});

	$('#close_date').datetimepicker({
		format: "<?php echo dateformat_bootstrap($this->config->item('dateformat')) . ' ' . dateformat_bootstrap($this->config->item('timeformat'));?>",
		startDate: "<?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), mktime(0, 0, 0, 1, 1, 2010));?>",
		<?php
		$t = $this->config->item('timeformat');
		$m = $t[strlen($t)-1];
		if( strpos($this->config->item('timeformat'), 'a') !== false || strpos($this->config->item('timeformat'), 'A') !== false )
		{
		?>
			showMeridian: true,
		<?php
		}
		else
		{
		?>
			showMeridian: false,
		<?php
		}
		?>
		minuteStep: 1,
		autoclose: true,
		todayBtn: true,
		todayHighlight: true,
		bootcssVer: 3,
		language: '<?php echo current_language_code(); ?>'
	});

	// Sum up multiple cash in/out entries and update hidden fields
	var update_cash_in_out_totals = function() {
		var cash_in_total = 0;
		$('.cash_in_amount').each(function() {
			var val = parseFloat($(this).val().replace(/[^\d.\-]/g, '')) || 0;
			cash_in_total += val;
		});
		var cash_out_total = 0;
		$('.cash_out_amount').each(function() {
			var val = parseFloat($(this).val().replace(/[^\d.\-]/g, '')) || 0;
			cash_out_total += val;
		});

		$('#cash_in_amount').val(cash_in_total > 0 ? cash_in_total : '');
		$('#cash_out_amount').val(cash_out_total > 0 ? cash_out_total : '');

		// Update hidden type from first row
		var first_cash_in_type = $('.cash_in_type').first().val();
		var first_cash_out_type = $('.cash_out_type').first().val();
		$('#cash_in_type').val(first_cash_in_type);
		$('#cash_out_type').val(first_cash_out_type);
	};

	var recalculate_totals = function() {
		update_cash_in_out_totals();
		$.post("<?php echo site_url($controller_name . '/ajax_cashup_total')?>", {
				'open_amount_cash': $('#open_amount_cash').val(),
				'cash_in_amount': $('#cash_in_amount').val(),
				'cash_out_amount': $('#cash_out_amount').val(),
				'total_trx_amount': $('#total_trx_amount').val(),
				'total_expense': $('#total_expense').val(),
				'actual_cash_counted': $('#actual_cash_counted').val()
			},
			function(response) {
				$('#closed_amount_total').val(response.total);
				$('#expected_cash').val(response.expected_cash);
				$('#discrepancy_variance').val(response.discrepancy_variance);
			},
			'json'
		);
	};

	// Bind keyup to all amount fields (including dynamically added)
	$(document).on('keyup', '#open_amount_cash, #cash_in_amount, #cash_out_amount, #actual_cash_counted, #total_trx_amount, #total_expense, .cash_in_amount, .cash_out_amount', function() {
		recalculate_totals();
	});

	// Add new cash in entry - only if current amount is filled
	var cash_in_counter = 1;
	$(document).on('click', '.cash_in_add', function() {
		var $row = $(this).closest('.cash_in_row');
		var amount = parseFloat($row.find('.cash_in_amount').val().replace(/[^\d.\-]/g, '')) || 0;
		if(amount === 0) {
			return;
		}
		cash_in_counter++;
		var $newRow = $row.clone();
		$newRow.find('.cash_in_amount').val('').attr('name', 'cash_in_amount_' + cash_in_counter);
		$newRow.find('.cash_in_type').val($row.find('.cash_in_type').val()).attr('name', 'cash_in_type_' + cash_in_counter);
		$row.after($newRow);
	});

	// Add new cash out entry - only if current amount is filled
	var cash_out_counter = 1;
	$(document).on('click', '.cash_out_add', function() {
		var $row = $(this).closest('.cash_out_row');
		var amount = parseFloat($row.find('.cash_out_amount').val().replace(/[^\d.\-]/g, '')) || 0;
		if(amount === 0) {
			return;
		}
		cash_out_counter++;
		var $newRow = $row.clone();
		$newRow.find('.cash_out_amount').val('').attr('name', 'cash_out_amount_' + cash_out_counter);
		$newRow.find('.cash_out_type').val($row.find('.cash_out_type').val()).attr('name', 'cash_out_type_' + cash_out_counter);
		$row.after($newRow);
	});

	$('#initiate_close_btn').click(function() {
		var cashup_id = $('#cashup_id').text().trim().match(/\d+/)[0];
		$.post("<?php echo site_url($controller_name . '/initiate_close')?>", {
				'cashup_id': cashup_id
			},
			function(response) {
				if(response.success) {
					$('#close_date').val(response.close_date);
					$('#close_employee_id').val('<?php echo $this->Employee->get_logged_in_employee_info()->person_id; ?>');
					$('#closed_amount_cash').val(response.closed_amount_cash);
					$('#closed_amount_mpesa').val(response.closed_amount_mpesa);
					$('#total_trx_amount').val(response.total_trx_amount);
					$('#total_expense').val(response.total_expense);
					$('#closed_amount_total').val(response.closed_amount_total);
					$('#expected_cash').val(response.expected_cash);
					$('#initiate_close_btn').hide();
					$('#close_fields_section .close_fields_inner, #close_employee_section').show();
					recalculate_totals();
				}
			},
			'json'
		);
	});

	// Close Day checkbox - triggers close process when checked
	$('#close_day').change(function() {
		if($(this).is(':checked')) {
			var cashup_id = $('#cashup_id').text().trim().match(/\d+/)[0];
			$.post("<?php echo site_url($controller_name . '/initiate_close')?>", {
					'cashup_id': cashup_id
				},
				function(response) {
					if(response.success) {
						$('#close_date').val(response.close_date);
						$('#close_employee_id').val('<?php echo $this->Employee->get_logged_in_employee_info()->person_id; ?>');
						$('#closed_amount_cash').val(response.closed_amount_cash);
						$('#closed_amount_mpesa').val(response.closed_amount_mpesa);
						$('#total_trx_amount').val(response.total_trx_amount);
						$('#total_expense').val(response.total_expense);
						$('#closed_amount_total').val(response.closed_amount_total);
						$('#expected_cash').val(response.expected_cash);
						$('#initiate_close_btn').hide();
						$('#close_fields_section .close_fields_inner, #close_employee_section').show();
						recalculate_totals();

						// Validate notes if variance is negative and close day is checked
						setTimeout(function() {
							if($('#close_day').is(':checked')) {
								var discrepancy = parseFloat($('#discrepancy_variance').val().replace(/[^0-9.\-]/g, ''));
								if(discrepancy < 0) {
									$('#description').prop('required', true);
									$.notify('<?php echo $this->lang->line('cashups_notes_required_if_negative'); ?>', {type: 'warning'});
								}
							}
						}, 500);
					}
				},
				'json'
			);
		}
	});

	var submit_form = function()
	{
		$(this).ajaxSubmit(
		{
			success: function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('cashups'); ?>', response);
			},
			dataType: 'json'
		});
	};

	$('#cashups_edit_form').validate($.extend(
	{
		submitHandler: function(form)
		{
			update_cash_in_out_totals();
			submit_form.call(form);
		},
		rules:
		{
			open_date:
			{
				required: true
			},
			close_date:
			{
				required: true
			}
		},
		messages:
		{
			open_date:
			{
				required: '<?php echo $this->lang->line('cashups_date_required'); ?>'
			},
			close_date:
			{
				required: '<?php echo $this->lang->line('cashups_date_required'); ?>'
			},
			amount:
			{
				required: '<?php echo $this->lang->line('cashups_amount_required'); ?>',
				number: '<?php echo $this->lang->line('cashups_amount_number'); ?>'
			}
		},
		errorPlacement: function(error, element) {
			if(element.attr('name') == 'description' && $('#close_day').is(':checked') && parseFloat($('#discrepancy_variance').val().replace(/[^0-9.\-]/g, '')) < 0) {
				error.insertAfter('#description');
			} else {
				form_support.error(error, element);
			}
		}
	}, form_support.error));
		$.validator.addMethod('notes_required_if_negative', function(value, element) {
		var discrepancy = parseFloat($('#discrepancy_variance').val().replace(/[^0-9.\-]/g, ''));
		if($('#close_day').is(':checked') && discrepancy < 0) {
			return value.trim().length > 0;
		}
		return true;
	}, '<?php echo $this->lang->line('cashups_notes_required_if_negative'); ?>');
	$('#description').rules('add', 'notes_required_if_negative', true);
});
</script>
