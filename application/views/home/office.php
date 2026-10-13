<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<style type="text/css">
	body.module-office { background-color: #F8FAFC; }

	.office-container {
		max-width: 1280px;
		margin: 0 auto;
		padding: 24px;
	}

	.office-title {
		font-size: 1.8em;
		font-weight: 700;
		color: #1e293b;
		margin-bottom: 8px;
	}

	.office-subtitle {
		font-size: 0.95em;
		color: #64748b;
		margin-bottom: 24px;
	}

	/* ---- Summary Cards ---- */
	.summary-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
		gap: 16px;
		margin-bottom: 24px;
	}

	.summary-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		padding: 20px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
		transition: all 0.15s ease;
	}

	.summary-card:hover {
		border-color: #cbd5e1;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
		transform: translateY(-2px);
	}

	.summary-card .summary-icon {
		width: 36px;
		height: 36px;
		opacity: 0.6;
		margin-bottom: 12px;
	}

	.summary-card .summary-value {
		font-size: 1.8em;
		font-weight: 700;
		color: #1e293b;
		margin-bottom: 4px;
	}

	.summary-card .summary-label {
		font-size: 0.85em;
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	/* ---- Action Bar ---- */
	.action-bar {
		display: flex;
		gap: 12px;
		margin-bottom: 24px;
		flex-wrap: wrap;
	}

	.action-btn {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 10px 20px;
		border-radius: 8px;
		text-decoration: none;
		font-weight: 600;
		font-size: 0.9em;
		border: 1px solid #e2e8f0;
		background: #ffffff;
		color: #1e293b;
		cursor: pointer;
		transition: all 0.15s ease;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
	}

	.action-btn:hover {
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
		border-color: #cbd5e1;
		color: #1e293b;
		text-decoration: none;
	}

	.action-btn-primary {
		background: #2563eb;
		color: #ffffff;
		border-color: #2563eb;
	}

	.action-btn-primary:hover {
		background: #1d4ed8;
		color: #ffffff;
		border-color: #1d4ed8;
	}

	.action-btn .action-icon {
		width: 18px;
		height: 18px;
		fill: currentColor;
	}

	/* ---- Spinner ---- */
	.office-spinner {
		display: inline-block;
		width: 16px;
		height: 16px;
		border: 2px solid #ffffff;
		border-top-color: transparent;
		border-radius: 50%;
		animation: office-spin 0.6s linear infinite;
		margin-right: 6px;
		vertical-align: middle;
	}

	@keyframes office-spin {
		to { transform: rotate(360deg); }
	}

	/* ---- Panel Grid ---- */
	.panel-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
		gap: 16px;
		margin-bottom: 24px;
	}

	.panel-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		padding: 20px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
		transition: all 0.15s ease;
	}

	.panel-card:hover {
		border-color: #cbd5e1;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
	}

	.panel-card .panel-title {
		font-size: 1.1em;
		font-weight: 600;
		color: #1e293b;
		margin-bottom: 16px;
		padding-bottom: 8px;
		border-bottom: 1px solid #f1f5f9;
	}

	/* ---- Activity Log Table ---- */
	.table-activity th {
		font-size: 0.8em;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: #94a3b8;
		border: none;
		padding: 8px 12px;
		font-weight: 600;
	}

	.table-activity td {
		padding: 8px 12px;
		border-bottom: 1px solid #f1f5f9;
		vertical-align: middle;
	}

	.table-activity tr:last-child td {
		border-bottom: none;
	}

	.table-activity .log-time {
		color: #94a3b8;
		font-size: 0.85em;
		white-space: nowrap;
	}

	.table-activity .log-event {
		font-size: 0.9em;
		color: #334155;
		font-weight: 500;
	}

	.table-activity .log-user {
		font-size: 0.85em;
		color: #64748b;
	}

	.table-activity .log-ip {
		color: #94a3b8;
		font-size: 0.85em;
		white-space: nowrap;
	}

	/* ---- Backups List ---- */
	.backup-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 10px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.backup-item:last-child {
		border-bottom: none;
	}

	.backup-info {
		flex: 1;
		min-width: 0;
	}

	.backup-info .backup-name {
		font-size: 0.95em;
		font-weight: 600;
		color: #1e293b;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.backup-info .backup-meta {
		font-size: 0.8em;
		color: #94a3b8;
		margin-top: 2px;
	}

	.backup-size {
		color: #64748b;
		font-size: 0.85em;
		font-weight: 500;
		margin-right: 12px;
		white-space: nowrap;
	}

	.backup-download {
		font-size: 0.8em;
		padding: 4px 12px;
		border-radius: 6px;
		text-decoration: none;
		color: #2563eb;
		border: 1px solid #bfdbfe;
		background: #eff6ff;
		transition: all 0.15s ease;
		white-space: nowrap;
	}

	.backup-download:hover {
		background: #dbeafe;
		color: #1d4ed8;
		border-color: #93c5fd;
	}

	.empty-state {
		text-align: center;
		color: #94a3b8;
		padding: 24px;
		font-size: 0.9em;
	}

	/* ---- Module Grid ---- */
	.module-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
		gap: 16px;
	}

	.module-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		padding: 20px;
		text-align: center;
		text-decoration: none;
		color: #1e293b;
		transition: all 0.15s ease;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.module-card:hover {
		border-color: #cbd5e1;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
		transform: translateY(-2px);
		color: #1e293b;
		text-decoration: none;
	}

	.module-card .module-icon {
		width: 42px;
		height: 42px;
		margin: 0 auto 14px;
		opacity: 0.7;
		fill: currentColor;
	}

	.module-card .module-title {
		font-size: 1em;
		font-weight: 700;
		color: #1e293b;
		margin-bottom: 6px;
	}

	.module-card .module-desc {
		font-size: 0.8em;
		color: #64748b;
		line-height: 1.4;
		margin-top: auto;
	}
</style>

<div class="office-container">
	<h3 class="office-title"><?php echo $this->lang->line('module_office'); ?> — Admin Dashboard</h3>
	<p class="office-subtitle">Quick overview of your store operations and system status</p>

	<?php /* ---- Top Admin Summary Cards ---- */ ?>
	<div class="summary-grid">
		<div class="summary-card">
			<svg class="summary-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/></svg>
			<div class="summary-value"><?php echo $active_staff_count; ?> Online</div>
			<div class="summary-label">Active Staff</div>
		</div>

		<div class="summary-card">
			<svg class="summary-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h10v14zm2 0v-6h-2v6z"/><path d="M6 10h2v6H6zm4 0h2v6h-2z"/></svg>
			<div class="summary-value"><?php echo $branch_count; ?> Active</div>
			<div class="summary-label">Branch Outlets</div>
		</div>

		<div class="summary-card">
			<svg class="summary-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM9 7h6M9 11h6M9 15h6"/><circle cx="9" cy="7" r="1"/><circle cx="15" cy="7" r="1"/></svg>
			<div class="summary-value"><?php echo $total_employees; ?> Total</div>
			<div class="summary-label">Employees</div>
		</div>

		<div class="summary-card">
			<svg class="summary-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19h16v-2H4zM4 5v2h16V5l-2-2H6L4 5zm0 8h16v6H4z"/><path d="M9 15V9h6v6z"/><circle cx="12" cy="12" r="3"/></svg>
			<div class="summary-value">Last Backup</div>
			<div class="summary-label"><?php echo $last_backup; ?></div>
		</div>
	</div>

	<?php /* ---- Quick Admin Action Bar ---- */ ?>
	<div class="action-bar">
		<?php if ($this->Employee->has_grant('employees', $this->session->userdata('person_id'))): ?>
			<a href="<?php echo site_url('employees'); ?>" class="action-btn action-btn-primary">
				<svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 13h-2V7h-2v6h-2V7H7v6H5V7H3v10h2v-6h2v6h6v-6h2v6h2V7h-2z"/></svg>
				+ Add New Employee
			</a>
		<?php endif; ?>

		<?php if ($can_trigger_backup): ?>
			<button type="button" class="action-btn" id="btn_trigger_backup">
				<svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l7-7v4h6v6h4l7-7-7-7v4h-6V5l-7 7z"/></svg>
				Trigger DB Backup
			</button>
		<?php endif; ?>
	</div>

	<?php /* ---- Administrative Activity & System Logs Panel ---- */ ?>
	<div class="panel-grid">
		<div class="panel-card">
			<div class="panel-title">Recent Activity Log</div>
			<?php if (!empty($recent_logs)): ?>
				<div class="table-responsive">
					<table class="table table-activity" style="margin-bottom: 0;">
						<thead>
							<tr>
								<th>Time</th>
								<th>Event</th>
								<th>User</th>
								<th>IP</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($recent_logs as $log): ?>
								<tr>
									<td class="log-time"><?php echo htmlspecialchars($log['time']); ?></td>
									<td class="log-event"><?php echo htmlspecialchars($log['event']); ?></td>
									<td class="log-user"><?php echo htmlspecialchars($log['user_name'] ?: $log['user_id']); ?></td>
									<td class="log-ip"><?php echo htmlspecialchars($log['ip']); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<div class="empty-state">No recent activity logs.</div>
			<?php endif; ?>
		</div>

		<div class="panel-card">
			<div class="panel-title">Database Backups</div>
			<?php if (!empty($backup_files)): ?>
				<?php foreach ($backup_files as $backup): ?>
					<div class="backup-item">
						<div class="backup-info">
							<div class="backup-name"><?php echo htmlspecialchars($backup['filename']); ?></div>
							<div class="backup-meta"><?php echo htmlspecialchars($backup['date']); ?></div>
						</div>
						<div class="backup-size"><?php echo htmlspecialchars($backup['size']); ?></div>
						<a href="<?php echo $backup['download_url']; ?>" class="backup-download">Download</a>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="empty-state">No backup files found yet.</div>
			<?php endif; ?>
		</div>
	</div>

	<?php /* ---- Modernized Role-Based Module Grid (permission loop preserved) ---- */ ?>
	<div class="module-grid" id="office_module_list">
		<?php
		foreach ($allowed_modules as $module)
		{
			$icon_svg = office_module_icon($module->module_id);
		?>
			<a href="<?php echo site_url("$module->module_id"); ?>" class="module-card" title="<?php echo $this->lang->line('module_'.$module->module_id.'_desc');?>">
				<svg class="module-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><?php echo $icon_svg; ?></svg>
				<div class="module-title"><?php echo $this->lang->line("module_".$module->module_id) ?></div>
				<div class="module-desc"><?php echo $this->lang->line('module_'.$module->module_id.'_desc');?></div>
			</a>
		<?php
		}
		?>
	</div>
</div>

<script type="text/javascript">
	$(function()
	{
		$('#btn_trigger_backup').click(function()
		{
			var btn = $(this);
			var original_html = btn.html();
			btn.prop('disabled', true);
			btn.html('<span class="office-spinner"></span>Backing up...');

			$.ajax({
				url: '<?php echo site_url("office/trigger_backup"); ?>',
				type: 'POST',
				success: function(response)
				{
					var data = JSON.parse(response);
					if (data.success)
					{
						btn.html(original_html);
						btn.prop('disabled', false);
						$.notify({ message: data.message }, { type: 'success' });
						location.reload();
					}
					else
					{
						btn.html(original_html);
						btn.prop('disabled', false);
						$.notify({ message: data.message }, { type: 'danger' });
					}
				},
				error: function()
				{
					btn.html(original_html);
					btn.prop('disabled', false);
				}
			});
		});
	});
</script>

<?php
function office_module_icon($module_id)
{
	$icons = array(
		'attributes'  => '<circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>',
		'cashups'     => '<path d="M19 7v1H5v-1c0-1.1-.9-2-2-2H3v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1c0 1.1-.9 2-2 2H9"/><path d="M9 12h6M9 12v-2M9 12v10"/>',
		'config'      => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4l2 2M8 12h8"/>',
		'customers'   => '<path d="M17 21v-2a4 4 0 0 0-3-3.87M9 21v-2a4 4 0 0 1 3-3.87M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>',
		'dashboard'   => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10z"/>',
		'employees'   => '<path d="M20 21V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM9 7h6M9 11h6M9 15h6"/><circle cx="9" cy="7" r="1"/><circle cx="15" cy="7" r="1"/>',
		'expenses'    => '<path d="M12 2v20M4 8h16M4 12h16M4 16h16"/><rect x="6" y="4" width="12" height="16" rx="2"/>',
		'expenses_categories' => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 14h10"/>',
		'giftcards'   => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h8v8H8zM8 16l4-4 4 4"/>',
		'home'        => '<path d="M3 12l9-9 9 9M5 10v10h14V10"/><path d="M12 15V5"/>',
		'item_kits'   => '<path d="M6 9l6 6 6-6"/>',
		'items'       => '<path d="M20.59 13l-8.59 8.59L6 15.41 6 9.41 12 4.91l8.59 8.59z"/>',
		'messages'    => '<path d="M4 3h16v1H4zM4 7h16v17a2 2 0 0 0 2 2v1H2v-1a2 2 0 0 1 2-2V7zm8 5 4 3v-6l-4 3z"/>',
		'migrate'     => '<path d="M4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0zm8-6a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/><path d="M12 8v4l2 2"/>',
		'office'      => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M15 8h-6v8h6zM15 4v4"/>',
		'receivings'  => '<path d="M3 8h18v1H3zM3 12h18v1H3zM3 16h18v1H3zM8 4h8v4H8z"/>',
		'reports'     => '<path d="M4 19h16v-2H4zM4 5v2h16V5l-2-2H6L4 5zm0 8h16v6H4z"/>',
		'sales'       => '<path d="M16 17v2a2 2 0 0 0 2 2h2M2 17h2a2 2 0 0 0 2-2v-4H2v4zm18-6.5V7a2 2 0 0 0-2-2h-3.5l-1-1H9.5l-1 1H5a2 2 0 0 0-2 2v4"/><line x1="8" y1="11" x2="16" y2="11"/><circle cx="12" cy="15" r="2"/>',
		'suppliers'   => '<path d="M16 21V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zm0 0h2a2 2 0 0 0 2-2v-2"/><circle cx="9" cy="9" r="2"/>',
		'taxes'       => '<path d="M12 2v20M4 12h16"/><circle cx="12" cy="12" r="8"/>',
	);

	if (isset($icons[$module_id]))
	{
		return $icons[$module_id];
	}

	return '<circle cx="12" cy="12" r="10"/>';
}
?>
<?php $this->load->view("partial/footer"); ?>
