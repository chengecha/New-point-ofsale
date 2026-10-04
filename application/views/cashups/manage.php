<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidden.bs.select', function(e) {
		table_support.refresh();
	});

	// load the preset datarange picker
	<?php $this->load->view('partial/daterangepicker'); ?>

	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
		table_support.refresh();
	});

	<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

	table_support.init({
		resource: '<?php echo site_url($controller_name);?>',
		headers: <?php echo $table_headers; ?>,
		pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
		uniqueId: 'cashup_id',
		queryParams: function() {
			return $.extend(arguments[0], {
				start_date: start_date,
				end_date: end_date,
				filters: $("#filters").val() || [""]
			});
		}
	});

	$('#new_cashup_btn').off('click').on('click', function(event) {
		event.preventDefault();

		$.get('<?php echo site_url($controller_name."/check_open_cashup_exists"); ?>', function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;

			if(data.exists) {
				$.notify('<?php echo $this->lang->line('cashups_duplicate_entry'); ?>', {type: 'danger'});
				return;
			}

			var $link = $('#new_cashup_btn');
			BootstrapDialog.show({
				title: $link.attr('title'),
				message: (function() {
					var node = $('<div></div>');
					$.get($link.data('href'), function(data) {
						node.html(data);
					});
					return node;
				})(),
				buttons: [{
					id: 'submit',
					label: $link.data('btnSubmit'),
					cssClass: 'btn-primary',
					hotkey: 13,
					action: function(dialog_ref) {
						dialog_support.submit('submit')(dialog_ref);
						return false;
					}
				}, {
					id: 'close',
					label: '<?php echo $this->lang->line('common_close'); ?>',
					cssClass: 'btn-primary',
					action: function(dialog_ref) {
						dialog_ref.close();
					}
				}],
				cssClass: 'modal-dlg'
			});
		});
	});
});
</script>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>

<div id="title_bar" class="print_hide btn-toolbar">
	<button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
		<span class="glyphicon glyphicon-print">&nbsp;</span><?php echo $this->lang->line('common_print'); ?>
	</button>
	<button id="new_cashup_btn" class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name."/view"); ?>'
			title='<?php echo $this->lang->line($controller_name.'_new'); ?>'>
		<span class="glyphicon glyphicon-tags">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left form-inline" role="toolbar">
		<button id="delete" class="btn btn-default btn-sm print_hide">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
		</button>

		<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
		<?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'data-none-selected-text'=>$this->lang->line('common_none_selected_text'), 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 1', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
