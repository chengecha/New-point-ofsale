<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

	table_support.init({
		resource: '<?php echo site_url($controller_name);?>',
		headers: <?php echo $table_headers; ?>,
		pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
		uniqueId: 'people.person_id',
		enableActions: function()
		{
			var email_disabled = $("tr.selected").find("td a.email-link").length == 0;
			$("#email").prop('disabled', email_disabled);
		}
	});

	$("#email").click(function(event)
	{
		var $selected = $("tr.selected");
		if ($selected.length === 1) {
			var $emailLink = $selected.find("td a.email-link");
			if ($emailLink.length) {
				var personId = $emailLink.data('person-id');
				var email = $emailLink.data('email');
				var name = $emailLink.data('name');
				openEmailModal(personId, email, name);
			}
		} else if ($selected.length > 1) {
			var recipients = [];
			$selected.find("td a.email-link").each(function() {
				recipients.push($(this).data('email'));
			});
			if (recipients.length > 0) {
				openEmailModal(null, recipients.join(","), "");
			}
		}
	});

	// Handle clicking on email link in table
	$(document).on('click', 'a.email-link', function(e) {
		e.preventDefault();
		var personId = $(this).data('person-id');
		var email = $(this).data('email');
		var name = $(this).data('name');
		openEmailModal(personId, email, name);
	});

	function openEmailModal(personId, email, name) {
		var url = '<?php echo site_url($controller_name . "/email_modal"); ?>';
		if (personId) {
			url += '/' + personId;
		}
		if (email) {
			url += '?email=' + encodeURIComponent(email);
		}
		if (name) {
			url += '&name=' + encodeURIComponent(name);
		}
		
		$.get(url, function(response) {
			$('body').append(response);
			$('#emailModal').modal('show');
		});
	}
});
</script>

<div id="title_bar" class="btn-toolbar">
	<?php
	if ($controller_name == 'customers')
	{
	?>
		<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name."/csv_import"); ?>'
				title='<?php echo $this->lang->line('customers_import_items_csv'); ?>'>
			<span class="glyphicon glyphicon-import">&nbsp</span><?php echo $this->lang->line('common_import_csv'); ?>
		</button>
	<?php
	}
	?>
	<button class='btn btn-info btn-sm pull-right modal-dlg<?php echo ($controller_name == 'customers') ? ' modal-dlg-customer' : ''; ?>' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name."/view"); ?>'
			title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
		<span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
		</button>
		<button id="email" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-envelope">&nbsp</span><?php echo $this->lang->line("common_email");?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
