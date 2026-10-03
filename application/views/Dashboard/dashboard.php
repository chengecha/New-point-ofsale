<?php $this->load->view("partial/header"); ?>

<!-- ============================================================= -->
<!-- Dashboard CSS    application/views/dashboard.php                                              -->
<!-- ============================================================= -->
<style>
  /* --- Stat tiles --- */
  .stat-tile { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 14px; margin-bottom: 15px; }
  .stat-tile .icon { float: left; width: 55px; height: 55px; line-height: 55px; text-align: center; font-size: 24px; color: #fff; border-radius: 4px; margin-right: 12px; }
  .stat-tile .value { font-size: 22px; font-weight: 600; color: #333; }
  .stat-tile .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
  .bg-blue   { background: #3498db; }
  .bg-green  { background: #2ecc71; }
  .bg-orange { background: #e67e22; }
  .bg-purple { background: #9b59b6; }
  .bg-red    { background: #e74c3c; }
  .bg-teal   { background: #1abc9c; }

  /* --- Chart cards --- */
  .chart-card { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 10px 14px 14px; margin-bottom: 15px; }
  .chart-card h4 { margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #444; border-bottom: 1px solid #eee; padding-bottom: 8px; }
  .chart-card h4 .glyphicon { color: #3498db; margin-right: 5px; }
  .chart-card canvas { width: 100% !important; height: 240px !important; }

  /* --- Toolbar --- */
  #dashboard_toolbar { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; overflow-x: auto; overflow-y: hidden; }
  #dashboard_filter { display: flex; align-items: center; flex: 0 0 auto; flex-wrap: nowrap; gap: 12px; white-space: nowrap; }
  #dashboard_filter .form-group { display: flex; align-items: center; gap: 6px; margin-bottom: 0; white-space: nowrap; }
  #dashboard_filter .form-control { display: inline-block; width: auto; vertical-align: bottom; }
  #dashboard_filter button { white-space: nowrap; }

  /* --- Recent transactions table --- */
  .recent-table { margin-bottom: 0; font-size: 13px; }
  .recent-table thead th { background: #f0f0f0; font-size: 12px; }

  /* --- Low stock / alerts --- */
  .alert-item { padding: 8px 10px; border-left: 4px solid #e74c3c; background: #fff8f8; margin-bottom: 8px; border-radius: 2px; font-size: 13px; }
  .alert-item.warn { border-left-color: #e67e22; background: #fffbf3; }
  .alert-item.info { border-left-color: #3498db; background: #f3f9ff; }
  .alert-item .badge { float: right; }

  /* --- Spin animation for refresh --- */
  .spin { -webkit-animation: spin 0.6s linear; animation: spin 0.6s linear; }
  @-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
  @keyframes spin { 100% { transform: rotate(360deg); } }

  .widget-loader { display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
  .chart-card { position: relative; }
</style>

<!-- ============================================================= -->
<!-- Title bar                                                     -->
<!-- ============================================================= -->

 <!--  <button type="button" id="refresh_dashboard" class="btn btn-default btn-sm pull-right" style="margin-right:6px;">
    <span class="glyphicon glyphicon-refresh"></span>&nbsp; -->
    <!-- <?php echo $this->lang->line('common_refresh'); ?> -->
  <!-- </button>
</div>  -->

<!-- ============================================================= -->
<!-- Toolbar: date range + granularity                             -->
<!-- ============================================================= -->
 
<div id="dashboard_toolbar">
  <form class="form-inline" id="dashboard_filter">

      <h3 class="pull-left" style="margin-top:6px;">
    <span class="glyphicon glyphicon-dashboard"></span>
     <?php echo $this->lang->line('dashboard'); ?> 
  </h3> 


    <div class="form-group">
      <label><?php echo $this->lang->line('common_from'); ?>:</label>
      <input type="date" class="form-control input-sm" id="date_from"
             value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
      <label><?php echo $this->lang->line('common_to'); ?>:</label>
      <input type="date" class="form-control input-sm" id="date_to"
             value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
      <label><?php echo $this->lang->line('common_group_by'); ?>:</label>
      <select class="form-control input-sm" id="granularity">
        <option value="day"><?php echo $this->lang->line('common_day'); ?></option>
        <option value="week" selected><?php echo $this->lang->line('common_week'); ?></option>
        <option value="month"><?php echo $this->lang->line('common_month'); ?></option>
        <option value="year"><?php echo $this->lang->line('common_year'); ?></option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm" style="margin-left:8px;">
      <span class="glyphicon glyphicon-filter"></span>
      <?php echo $this->lang->line('common_apply'); ?>
    </button>

   <button type="button" class="btn btn-default btn-sm" id="reset_filter" style="margin-left: 4px; margin-right: 16px;">
      <span class="glyphicon glyphicon-repeat"></span>
      <?php echo $this->lang->line('common_reset'); ?>
    </button>

     
    <button type="button" id="refresh_dashboard" class="btn btn-default btn-sm pull-right" style="margin-right: 16px;">
      <span class="glyphicon glyphicon-refresh"></span>&nbsp; 
      <?php echo $this->lang->line('common_refresh'); ?> 
    </button>


  </form>
</div>

<!-- ============================================================= -->
<!-- Stat tiles                                                    -->
<!-- ============================================================= -->
<div class="row" id="stat_tiles">
  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-blue"><span class="glyphicon glyphicon-shopping-cart"></span></div>
      <div class="value" id="stat_today_sales">
        <?php echo to_currency($stats['today_sales']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_today_sales'); ?></div>
    </div>
  </div>

  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-green"><span class="glyphicon glyphicon-stats"></span></div>
      <div class="value" id="stat_monthly_revenue">
        <?php echo to_currency($stats['monthly_revenue']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_monthly_revenue'); ?></div>
    </div>
  </div>

  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-orange"><span class="glyphicon glyphicon-file"></span></div>
      <div class="value" id="stat_transaction_count">
        <?php echo number_format($stats['transaction_count']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_transactions'); ?></div>
    </div>
  </div>

  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-purple"><span class="glyphicon glyphicon-th-large"></span></div>
      <div class="value" id="stat_items_sold">
        <?php echo number_format($stats['items_sold']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_items_sold'); ?></div>
    </div>
  </div>

  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-teal"><span class="glyphicon glyphicon-user"></span></div>
      <div class="value" id="stat_customer_count">
        <?php echo number_format($stats['customer_count']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_customers'); ?></div>
    </div>
  </div>

  <div class="col-md-2 col-sm-4 col-xs-6">
    <div class="stat-tile">
      <div class="icon bg-red"><span class="glyphicon glyphicon-warning-sign"></span></div>
      <div class="value" id="stat_low_stock">
        <?php echo number_format($stats['low_stock_items']); ?>
      </div>
      <div class="label"><?php echo $this->lang->line('dashboard_low_stock'); ?></div>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- Row 1: Sales over time + Payment methods                      -->
<!-- ============================================================= -->
<div class="row">
  <div class="col-md-8">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-signal"></span>
        <?php echo $this->lang->line('dashboard_sales_over_time'); ?>
      </h4>
      <canvas id="chart_sales_over_time"></canvas>
    </div>
  </div>
  <div class="col-md-4">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-pie-chart"></span>
        <?php echo $this->lang->line('dashboard_payment_methods'); ?>
      </h4>
      <canvas id="chart_payment_methods"></canvas>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- Row 2: Top items / customers / suppliers                      -->
<!-- ============================================================= -->
<div class="row">
  <div class="col-md-4">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-equalizer"></span>
        <?php echo $this->lang->line('dashboard_top_items'); ?>
      </h4>
      <canvas id="chart_top_items"></canvas>
    </div>
  </div>
  <div class="col-md-4">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-user"></span>
        <?php echo $this->lang->line('dashboard_top_customers'); ?>
      </h4>
      <canvas id="chart_top_customers"></canvas>
    </div>
  </div>
  <div class="col-md-4">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-truck"></span>
        <?php echo $this->lang->line('dashboard_top_suppliers'); ?>
      </h4>
      <canvas id="chart_top_suppliers"></canvas>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- Row 3: Revenue vs Cost + Hourly                               -->
<!-- ============================================================= -->
<div class="row">
  <div class="col-md-6">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-usd"></span>
        <?php echo $this->lang->line('dashboard_revenue_vs_cost'); ?>
      </h4>
      <canvas id="chart_revenue_vs_cost"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-time"></span>
        <?php echo $this->lang->line('dashboard_hourly_transactions'); ?>
      </h4>
      <canvas id="chart_hourly"></canvas>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- Row 4: Recent transactions + Alerts side panel                -->
<!-- ============================================================= -->
<div class="row">
  <div class="col-md-8">
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-list-alt"></span>
        <?php echo $this->lang->line('dashboard_recent_transactions'); ?>
      </h4>
      <table class="table table-striped table-hover recent-table">
        <thead>
          <tr>
            <th width="8%"><?php echo $this->lang->line('common_id'); ?></th>
            <th width="18%"><?php echo $this->lang->line('common_date'); ?></th>
            <th><?php echo $this->lang->line('common_customer'); ?></th>
            <th><?php echo $this->lang->line('common_employee'); ?></th>
            <th width="8%" class="text-center"><?php echo $this->lang->line('common_items'); ?></th>
            <th width="12%"><?php echo $this->lang->line('common_payment'); ?></th>
            <th width="12%" class="text-right"><?php echo $this->lang->line('common_total'); ?></th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($recent_transactions)): ?>
          <?php foreach ($recent_transactions as $sale): ?>
            <tr>
              <td>
                <a href="<?php echo site_url('sales/receipt/' . $sale['sale_id']); ?>" target="_blank">
                  <?php echo $sale['sale_id']; ?>
                </a>
              </td>
              <td><?php echo $sale['sale_time_formatted']; ?></td>
              <td><?php echo $sale['customer_name']; ?></td>
              <td><?php echo $sale['employee_name']; ?></td>
              <td class="text-center"><?php echo $sale['item_count']; ?></td>
              <td><?php echo $sale['payment_type']; ?></td>
              <td class="text-right"><?php echo $sale['sale_amount_formatted']; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted">
              <?php echo $this->lang->line('common_no_records'); ?>
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-md-4">
    <!-- Alerts -->
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-bell"></span>
        <?php echo $this->lang->line('dashboard_alerts'); ?>
      </h4>

      <div class="alert-item">
        <span class="badge"><?php echo $alerts['low_stock']; ?></span>
        <strong><?php echo $this->lang->line('dashboard_low_stock_items'); ?></strong><br>
        <small><?php echo $this->lang->line('dashboard_low_stock_hint'); ?></small>
      </div>

      <div class="alert-item warn">
        <span class="badge"><?php echo $alerts['expiring_items']; ?></span>
        <strong><?php echo $this->lang->line('dashboard_expiring_items'); ?></strong><br>
        <small><?php echo $this->lang->line('dashboard_expiring_hint'); ?></small>
      </div>

      <div class="alert-item info">
        <span class="badge"><?php echo $alerts['pending_po']; ?></span>
        <strong><?php echo $this->lang->line('dashboard_pending_po'); ?></strong><br>
        <small><?php echo $this->lang->line('dashboard_pending_hint'); ?></small>
      </div>
    </div>

    <!-- Low stock list -->
    <div class="chart-card">
      <h4>
        <span class="glyphicon glyphicon-list"></span>
        <?php echo $this->lang->line('dashboard_low_stock_items'); ?>
      </h4>
      <?php if (!empty($low_stock_items)): ?>
        <table class="table table-condensed recent-table">
          <tbody>
          <?php foreach ($low_stock_items as $item): ?>
            <tr>
              <td><?php echo $item['name']; ?></td>
              <td class="text-right">
                <span class="label label-danger">
                  <?php echo $item['quantity'] . ' / ' . $item['reorder_level']; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted text-center" style="margin:0;">
          <?php echo $this->lang->line('common_no_records'); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============================================================= -->
<!-- Chart.js + jQuery                                             -->
<!-- ============================================================= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{
  /* ============================================================= */
  /* Data payloads from the controller                             */
  /* ============================================================= */
  var salesOverTime  = <?php echo $chart_sales_over_time; ?>;
  var paymentData    = <?php echo $chart_payment_methods; ?>;
  var topItems       = <?php echo $chart_top_items; ?>;
  var topCustomers   = <?php echo $chart_top_customers; ?>;
  var topSuppliers   = <?php echo $chart_top_suppliers; ?>;
  var revenueCost    = <?php echo $chart_revenue_vs_cost; ?>;
  var hourlyData     = <?php echo $chart_hourly; ?>;

  var currencySymbol = "<?php echo $currency_symbol; ?>";

  // DEBUG: log all chart data payloads
  console.log('[Dashboard] currencySymbol:', currencySymbol);
  console.log('[Dashboard] salesOverTime:', JSON.stringify(salesOverTime));
  console.log('[Dashboard] paymentData:', JSON.stringify(paymentData));
  console.log('[Dashboard] topItems:', JSON.stringify(topItems));
  console.log('[Dashboard] topCustomers:', JSON.stringify(topCustomers));
  console.log('[Dashboard] topSuppliers:', JSON.stringify(topSuppliers));
  console.log('[Dashboard] revenueCost:', JSON.stringify(revenueCost));
  console.log('[Dashboard] hourlyData:', JSON.stringify(hourlyData));

  /* ============================================================= */
  /* Shared palette                                                */
  /* ============================================================= */
  var palette = ['#3498db','#2ecc71','#e67e22','#9b59b6','#e74c3c','#1abc9c','#f1c40f','#34495e'];

  /* ============================================================= */
  /* Chart defaults                                                */
  /* ============================================================= */
  Chart.defaults.global.defaultFontSize  = 11;
  Chart.defaults.global.defaultFontColor = '#555';
  Chart.defaults.global.legend.position  = 'bottom';

  var charts = {};

  /* ============================================================= */
  /* 1. Sales & Transactions over time (line, dual axis)           */
  /* ============================================================= */
  // console.log('[Dashboard] constructing salesOverTime chart, labels:', salesOverTime.labels, 'sales len:', salesOverTime.sales.length, 'transactions len:', salesOverTime.transactions.length);
  // charts.salesOverTime = new Chart(document.getElementById('chart_sales_over_time'), {
  //   type: 'line',
  //   data: {
  //     labels: salesOverTime.labels,
  //     datasets: [
  //       {
  //         label: '<?php echo $this->lang->line("dashboard_sales"); ?>',
  //         data: salesOverTime.sales,
  //         borderColor: '#3498db',
  //         backgroundColor: 'rgba(52,152,219,.15)',
  //         fill: true,
  //         yAxisID: 'y',
  //         tension: .35,
  //         pointRadius: 3
  //       },
  //       {
  //         label: '<?php echo $this->lang->line("dashboard_transactions"); ?>',
  //         data: salesOverTime.transactions,
  //         borderColor: '#2ecc71',
  //         backgroundColor: 'rgba(46,204,113,.1)',
  //         fill: false,
  //         yAxisID: 'y1',
  //         tension: .35,
  //         pointRadius: 3,
  //         borderDash: [5, 3]
  //       }
  //     ]
  //   },
  //   options: {
  //     responsive: true,
  //     maintainAspectRatio: false,
  //     scales: {
  //       y:  { beginAtZero: true, position: 'left',
  //             ticks: { callback: function(v) { return currencySymbol + v; } } },
  //       y1: { beginAtZero: true, position: 'right',
  //             gridLines: { drawOnChartArea: false } }
  //     }
  //   }
  // });

  /* ============================================================= */
  /* 1. Sales & Transactions over time (line, dual axis)           */
  /* ============================================================= */
  // console.log('[Dashboard] constructing salesOverTime chart, labels:', salesOverTime.labels, 'sales len:', salesOverTime.sales.length, 'transactions len:', salesOverTime.transactions.length);
  charts.salesOverTime = new Chart(document.getElementById('chart_sales_over_time'), {
    type: 'line',
    data: {
      labels: salesOverTime.labels,
      datasets: [
        {
          label: '<?php echo $this->lang->line("dashboard_sales"); ?>',
          data: salesOverTime.sales,
          borderColor: '#3498db',
          backgroundColor: 'rgba(52,152,219,.15)',
          fill: true,
          yAxisID: 'y-axis-sales', // Updated ID
          tension: .35,
          pointRadius: 3
        },
        {
          label: '<?php echo $this->lang->line("dashboard_transactions"); ?>',
          data: salesOverTime.transactions,
          borderColor: '#2ecc71',
          backgroundColor: 'rgba(46,204,113,.1)',
          fill: false,
          yAxisID: 'y-axis-trans', // Updated ID
          tension: .35,
          pointRadius: 3,
          borderDash: [5, 3]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [ // Fixed from v3 'y:' to v2 array syntax
          {
            id: 'y-axis-sales',
            type: 'linear',
            position: 'left',
            ticks: { beginAtZero: true, callback: function(v) { return currencySymbol + v; } }
          },
          {
            id: 'y-axis-trans',
            type: 'linear',
            position: 'right',
            gridLines: { drawOnChartArea: false },
            ticks: { beginAtZero: true }
          }
        ]
      }
    }
  });

  /* ============================================================= */
  /* 2. Payment methods (doughnut)                                 */
  /* ============================================================= */
  // console.log('[Dashboard] constructing paymentMethods chart, labels:', paymentData.labels, 'data len:', paymentData.data.length);
  charts.paymentMethods = new Chart(document.getElementById('chart_payment_methods'), {
    type: 'doughnut',
    data: {
      labels: paymentData.labels,
      datasets: [{
        data: paymentData.data,
        backgroundColor: palette
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { position: 'bottom' },
      // tooltip: {
      //   callbacks: {
      //     label: function(item, data) {
      //       var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
      //       var pct = total ? ((item.y / total) * 100).toFixed(1) : 0;
      //       return data.labels[item.index] + ': ' + currencySymbol + item.y + ' (' + pct + '%)';
      //     }
      //   }
      // }

      tooltip: {
  callbacks: {
    label: function(item, data) {
      var val = data.datasets[item.datasetIndex].data[item.index];
      var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
      var pct = total ? ((val / total) * 100).toFixed(1) : 0;
      return data.labels[item.index] + ': ' + currencySymbol + val + ' (' + pct + '%)';
    }
  }
}
    }
  });

  /* ============================================================= */
  /* 3. Top selling items (horizontal bar)                         */
  /* ============================================================= */
  // console.log('[Dashboard] constructing topItems chart, labels:', topItems.labels, 'data len:', topItems.data.length);
  charts.topItems = new Chart(document.getElementById('chart_top_items'), {
    type: 'horizontalBar',
    data: {
      labels: topItems.labels,
      datasets: [{
        label: '<?php echo $this->lang->line("dashboard_units_sold"); ?>',
        data: topItems.data,
        backgroundColor: palette
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { display: false },
      scales: {
        xAxes: [{ ticks: { beginAtZero: true } }]
      }
    }
  });

  /* ============================================================= */
  /* 4. Top customers (bar)                                        */
  /* ============================================================= */
  // console.log('[Dashboard] constructing topCustomers chart, labels:', topCustomers.labels, 'data len:', topCustomers.data.length);
  charts.topCustomers = new Chart(document.getElementById('chart_top_customers'), {
    type: 'bar',
    data: {
      labels: topCustomers.labels,
      datasets: [{
        label: '<?php echo $this->lang->line("dashboard_revenue"); ?>',
        data: topCustomers.data,
        backgroundColor: '#9b59b6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { display: false },
      scales: {
        yAxes: [{ ticks: {
          beginAtZero: true,
          callback: function(v) { return currencySymbol + v; }
        }}]
      }
    }
  });

  /* ============================================================= */
  /* 5. Top suppliers (polar area)                                 */
  /* ============================================================= */
  console.log('[Dashboard] constructing topSuppliers chart, labels:', topSuppliers.labels, 'data len:', topSuppliers.data.length);
  charts.topSuppliers = new Chart(document.getElementById('chart_top_suppliers'), {
    type: 'polarArea',
    data: {
      labels: topSuppliers.labels,
      datasets: [{
        data: topSuppliers.data,
        backgroundColor: palette
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { position: 'bottom' }
    }
  });

  /* ============================================================= */
  /* 6. Revenue vs Cost (grouped bar)                              */
  /* ============================================================= */
  // console.log('[Dashboard] constructing revenueVsCost chart, labels:', revenueCost.labels, 'revenue len:', revenueCost.revenue.length, 'cost len:', revenueCost.cost.length);
  charts.revenueVsCost = new Chart(document.getElementById('chart_revenue_vs_cost'), {
    type: 'bar',
    data: {
      labels: revenueCost.labels,
      datasets: [
        {
          label: '<?php echo $this->lang->line("dashboard_revenue"); ?>',
          data: revenueCost.revenue,
          backgroundColor: '#2ecc71'
        },
        {
          label: '<?php echo $this->lang->line("dashboard_cost"); ?>',
          data: revenueCost.cost,
          backgroundColor: '#e74c3c'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [{ ticks: {
          beginAtZero: true,
          callback: function(v) { return currencySymbol + v; }
        }}]
      }
    }
  });

  /* ============================================================= */
  /* 7. Transactions by hour (smooth line)                         */
  /* ============================================================= */
  // console.log('[Dashboard] constructing hourly chart, labels:', hourlyData.labels, 'data len:', hourlyData.data.length);
  charts.hourly = new Chart(document.getElementById('chart_hourly'), {
    type: 'line',
    data: {
      labels: hourlyData.labels,
      datasets: [{
        label: '<?php echo $this->lang->line("dashboard_transactions"); ?>',
        data: hourlyData.data,
        borderColor: '#e67e22',
        backgroundColor: 'rgba(230,126,34,.15)',
        fill: true,
        tension: .35,
        pointRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { display: false },
      scales: {
        yAxes: [{ ticks: { beginAtZero: true } }]
      }
    }
  });

  /* ============================================================= */
  /* Filter submission                                             */
  /* ============================================================= */
  $('#dashboard_filter').submit(function(e) {
    e.preventDefault();
    refresh_all_widgets();
  });

  $('#reset_filter').click(function() {
    $('#date_from').val('<?php echo date("Y-m-d", strtotime("-30 days")); ?>');
    $('#date_to').val('<?php echo date("Y-m-d"); ?>');
    $('#granularity').val('week');
    refresh_all_widgets();
  });

  /* ============================================================= */
  /* Refresh button                                                */
  /* ============================================================= */
  $('#refresh_dashboard').click(function() {
    $(this).find('.glyphicon').addClass('spin');
    var btn = $(this);
    refresh_all_widgets(function() {
      btn.find('.glyphicon').removeClass('spin');
    });
  });

  /* ============================================================= */
  /* Widget refresh helper                                         */
  /* ============================================================= */
  function refresh_all_widgets(done) {
    var params = {
      date_from:   $('#date_from').val(),
      date_to:     $('#date_to').val(),
      granularity: $('#granularity').val()
    };

    var endpoints = [
      { widget: 'sales_over_time' },
      { widget: 'payment_methods' },
      { widget: 'top_items' },
      { widget: 'top_customers' },
      { widget: 'top_suppliers' },
      { widget: 'revenue_vs_cost' },
      { widget: 'hourly' },
      { widget: 'stats' }
    ];

    var pending = endpoints.length;

    $.each(endpoints, function(i, ep) {
      $.post(
        '<?php echo site_url("dashboard/refresh"); ?>',
        $.extend({}, params, { widget: ep.widget }),
        function(res) {
          if (res.success) {
            apply_widget_data(ep.widget, res.data);
          }
        },
        'json'
      ).always(function() {
        pending--;
        if (pending === 0 && typeof done === 'function') { done(); }
      });
    });
  }

  /* ============================================================= */
  /* Apply refreshed data to a widget                              */
  /* ============================================================= */
  function apply_widget_data(widget, data) {
    // DEBUG: log refresh data
    // console.log('[Dashboard] apply_widget_data:', widget, JSON.stringify(data));
    switch (widget) {

      case 'sales_over_time':
        charts.salesOverTime.data.labels = data.labels;
        charts.salesOverTime.data.datasets[0].data = data.sales;
        charts.salesOverTime.data.datasets[1].data = data.transactions;
        charts.salesOverTime.update();
        break;

      case 'payment_methods':
        charts.paymentMethods.data.labels = data.labels;
        charts.paymentMethods.data.datasets[0].data = data.data;
        charts.paymentMethods.update();
        break;

      case 'top_items':
        charts.topItems.data.labels = data.labels;
        charts.topItems.data.datasets[0].data = data.data;
        charts.topItems.update();
        break;

      case 'top_customers':
        charts.topCustomers.data.labels = data.labels;
        charts.topCustomers.data.datasets[0].data = data.data;
        charts.topCustomers.update();
        break;

      case 'top_suppliers':
        charts.topSuppliers.data.labels = data.labels;
        charts.topSuppliers.data.datasets[0].data = data.data;
        charts.topSuppliers.update();
        break;

      case 'revenue_vs_cost':
        charts.revenueVsCost.data.labels = data.labels;
        charts.revenueVsCost.data.datasets[0].data = data.revenue;
        charts.revenueVsCost.data.datasets[1].data = data.cost;
        charts.revenueVsCost.update();
        break;

      case 'hourly':
        charts.hourly.data.labels = data.labels;
        charts.hourly.data.datasets[0].data = data.data;
        charts.hourly.update();
        break;

      case 'stats':
        $('#stat_today_sales').text(currencySymbol + parseFloat(data.today_sales).toFixed(2));
        $('#stat_monthly_revenue').text(currencySymbol + parseFloat(data.monthly_revenue).toFixed(2));
        $('#stat_transaction_count').text(Number(data.transaction_count).toLocaleString());
        $('#stat_items_sold').text(Number(data.items_sold).toLocaleString());
        $('#stat_customer_count').text(Number(data.customer_count).toLocaleString());
        $('#stat_low_stock').text(Number(data.low_stock_items).toLocaleString());
        break;
    }
  }

  /* ============================================================= */
  /* Auto refresh every 60 seconds (optional)                      */
  /* ============================================================= */
  // setInterval(function() {
  //   refresh_all_widgets();
  // }, 60000);

});
</script>

<?php $this->load->view("partial/footer"); ?>