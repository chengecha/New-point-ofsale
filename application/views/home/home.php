<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<style type="text/css">
	body.module-home { background-color: #F8FAFC; }

	.command-center {
		max-width: 1200px;
		margin: 0 auto;
	}

	.cc-title {
		font-size: 1.6em;
		font-weight: 600;
		color: #1e293b;
		margin-bottom: 24px;
	}

	/* ---- Quick Action Cards ---- */
	.action-btn {
		display: block;
		padding: 24px 12px;
		border-radius: 12px;
		text-align: center;
		text-decoration: none;
		color: #1e293b;
		font-weight: 600;
		font-size: 14px;
		border: 1px solid #e2e8f0;
		background: #ffffff;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		transition: all 0.15s ease;
	}
	.action-btn:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
		color: #1e293b;
		text-decoration: none;
	}
	.action-btn .action-icon {
		font-size: 30px;
		display: block;
		margin-bottom: 8px;
	}
	.action-btn-primary {
		background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
		color: #ffffff;
		border-color: #2563eb;
	}
	.action-btn-primary:hover {
		background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
		color: #ffffff;
	}
	.action-btn-primary .action-icon {
		color: #ffffff;
	}

	/* ---- Card modules ---- */
	.cc-card {
		background: #ffffff;
		border-radius: 12px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		border: 1px solid rgba(226, 232, 240, 0.8);
		padding: 16px;
		height: 100%;
	}
	.cc-card .card-title {
		font-size: 1.1em;
		font-weight: 600;
		color: #1e293b;
		border-bottom: 2px solid #f1f5f9;
		padding-bottom: 8px;
		margin-top: 0;
		margin-bottom: 12px;
	}

	/* ---- Transactions table ---- */
	.table-txn td {
		padding: 8px 10px;
		vertical-align: middle;
	}
	.table-txn .receipt-time {
		color: #64748b;
		font-size: 0.85em;
		white-space: nowrap;
	}
	.table-txn .receipt-id {
		font-weight: 600;
		color: #1e293b;
	}
	.table-txn .payment-method {
		color: #475569;
		font-size: 0.9em;
	}
	.table-txn .amount {
		text-align: right;
		font-weight: 600;
		color: #1e293b;
	}
	.table-txn .print-btn {
		font-size: 10px;
		padding: 3px 10px;
	}

	/* ---- Low stock list ---- */
	.low-stock-item {
		padding: 10px 0;
		border-bottom: 1px solid #f1f5f9;
		position: relative;
	}
	.low-stock-item:last-child {
		border-bottom: none;
	}
	.low-stock-item .item-name {
		font-weight: 600;
		color: #1e293b;
	}
	.low-stock-item .stock-qty {
		font-size: 0.85em;
		color: #64748b;
	}
	.reorder-btn {
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateY(-50%);
		font-size: 10px;
		padding: 3px 14px;
	}

	/* ---- Empty states ---- */
	.cc-empty {
		text-align: center;
		color: #94a3b8;
		padding: 24px;
		font-style: italic;
		font-size: 0.9em;
	}

	/* ---- Section gap ---- */
	.section-gap {
		margin-bottom: 24px;
	}
</style>

<div class="command-center">
	<h3 class="cc-title">Operational Command Center</h3>

	<?php /* ---- Quick Action Cards ---- */ ?>
	<div class="row section-gap">
		<?php if ($has_sales_access): ?>
			<div class="col-xs-6 col-sm-3" style="padding: 6px;">
				<a href="<?php echo site_url('sales'); ?>" class="action-btn action-btn-primary" data-toggle="tooltip" title="Press F1">
					<span class="glyphicon glyphicon-shopping-cart action-icon"></span>
					Open POS / New Sale
				</a>
			</div>
		<?php endif; ?>
		<?php if ($has_receivings_access): ?>
			<div class="col-xs-6 col-sm-3" style="padding: 6px;">
				<a href="<?php echo site_url('receivings'); ?>" class="action-btn">
					<span class="glyphicon glyphicon-import action-icon"></span>
					Stock Receiving / Add Inventory
				</a>
			</div>
		<?php endif; ?>
		<?php if ($has_cashups_access): ?>
			<div class="col-xs-6 col-sm-3" style="padding: 6px;">
				<a href="<?php echo site_url('cashups'); ?>" class="action-btn">
					<span class="glyphicon glyphicon-save-file action-icon"></span>
					Close Shift / Cashup
				</a>
			</div>
		<?php endif; ?>
		<?php if ($has_customers_access): ?>
			<div class="col-xs-6 col-sm-3" style="padding: 6px;">
				<a href="<?php echo site_url('customers'); ?>" class="action-btn">
					<span class="glyphicon glyphicon-user-add action-icon"></span>
					Add Customer
				</a>
			</div>
		<?php endif; ?>
	</div>

	<?php /* ---- Live Operations Panel ---- */ ?>
	<div class="row">
		<div class="col-sm-6" style="padding: 6px;">
			<div class="cc-card">
				<h4 class="card-title">Today's Register Activity</h4>
				<?php if (!empty($todays_transactions)): ?>
					<table class="table table-txn" style="margin-bottom: 0;">
						<tbody>
							<?php foreach ($todays_transactions as $txn): ?>
								<tr>
									<td class="receipt-time"><?php echo $txn['sale_time_formatted']; ?></td>
									<td class="receipt-id">#<?php echo htmlspecialchars($txn['receipt_number']); ?></td>
									<td class="payment-method"><?php echo htmlspecialchars($txn['payment_type']); ?></td>
									<td class="amount"><?php echo $txn['sale_amount_formatted']; ?></td>
									<td>
										<?php if ($has_sales_access): ?>
											<a href="<?php echo site_url('sales/receipt/' . $txn['sale_id']); ?>" target="_blank" class="btn btn-xs btn-default print-btn" title="Quick Print Receipt">
												<span class="glyphicon glyphicon-print"></span>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="cc-empty">No transactions recorded today.</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="col-sm-6" style="padding: 6px;">
			<div class="cc-card">
				<h4 class="card-title">Inventory Action Required</h4>
				<?php if (!empty($low_stock_items)): ?>
					<?php foreach ($low_stock_items as $item): ?>
						<div class="low-stock-item">
							<div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
							<div class="stock-qty">
								Stock: <?php echo to_quantity_decimals($item['quantity']); ?>
								/ Reorder: <?php echo to_quantity_decimals($item['reorder_level']); ?>
							</div>
							<?php if ($has_receivings_access): ?>
								<a href="<?php echo site_url('receivings'); ?>" class="btn btn-xs btn-primary reorder-btn">Reorder</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="cc-empty">All items are sufficiently stocked.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function()
	{
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>

<?php $this->load->view("partial/footer"); ?>
