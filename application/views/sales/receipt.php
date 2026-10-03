<?php $this->load->view("partial/header"); ?>

<?php
if (isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
	exit;
}
?>

<?php if(!empty($customer_email)): ?>
	<script type="text/javascript">
	$(document).ready(function()
	{
		var open_email_modal = function()
		{
			$.get('<?php echo site_url() . "/sales/email_modal/" . $sale_id_num . "/receipt"; ?>',
				function(response)
				{
					$('body').append(response);
					$('#emailModal').modal('show');
				}
			);
		};

		$("#show_email_button").click(open_email_modal);

		<?php if(!empty($email_receipt)): ?>
			open_email_modal();
		<?php endif; ?>
	});
	</script>
<?php endif; ?>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale, 'selected_printer'=>'receipt_printer')); ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm", id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div></a>
	<?php if(!empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm", id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . $this->lang->line('sales_send_receipt'); ?></div></a>
	<?php endif; ?>
	<?php echo anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_register'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_sales_button')); ?>
	<?php if($this->Employee->has_grant('reports_sales', $this->session->userdata('person_id'))): ?>
		<?php echo anchor("sales/manage", '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . $this->lang->line('sales_takings'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_takings_button')); ?>
	<?php endif; ?>
</div>

<?php $this->load->view("sales/" . $this->config->item('receipt_template')); ?>

<?php $this->load->view("partial/footer"); ?>