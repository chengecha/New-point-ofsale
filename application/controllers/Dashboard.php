<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

/**
 * Dashboard controller
 *
 * Aggregates KPIs, chart data and recent activity for the
 * main dashboard view. Uses the same CodeIgniter 3 + OSPOS
 * conventions as the rest of the application.
 */
class Dashboard extends Secure_Controller
{
    public function __construct()
    {
        parent::__construct('dashboard');

        $this->load->model('Customer');

        $this->load->library('Dashboard_lib');
    }

    // ---------------------------------------------------------------------
    // Main dashboard page
    // ---------------------------------------------------------------------
    public function index()
    {
        $data['controller_name']  = 'dashboard';
        $data['lines_per_page']   = $this->config->item('lines_per_page');
        $data['currency_symbol']  = $this->config->item('currency_symbol');
        $data['date_format']      = $this->config->item('dateformat');
        $data['time_format']      = $this->config->item('timeformat');

        // ---- Stat tiles -------------------------------------------------
        $data['stats'] = $this->get_stat_tiles();

        // ---- Chart data (JSON-encoded for the view) --------------------
        $data['chart_sales_over_time']  = json_encode($this->get_sales_over_time());
        $data['chart_payment_methods']  = json_encode($this->get_payment_methods());
        $data['chart_top_items']        = json_encode($this->get_top_items());
        $data['chart_top_customers']    = json_encode($this->get_top_customers());
        $data['chart_top_suppliers']    = json_encode($this->get_top_suppliers());
        $data['chart_revenue_vs_cost']  = json_encode($this->get_revenue_vs_cost());
        $data['chart_hourly']           = json_encode($this->get_hourly_transactions());

        // ---- Recent activity -------------------------------------------
        $data['recent_transactions']    = $this->get_recent_transactions(10);
        $data['recent_receivings']      = $this->get_recent_receivings(5);
        $data['low_stock_items']        = $this->get_low_stock_items(5);

        // ---- Alerts / notices ------------------------------------------
        $data['alerts'] = array(
            'low_stock'      => $data['stats']['low_stock_items'],
            'expiring_items' => $this->get_expiring_items_count(),
            'pending_po'     => $this->get_pending_purchases_count()
        );

        // The view is self-contained (loads partial/header + partial/footer)
        $this->load->view('Dashboard/dashboard', $data);
    }

    // ---------------------------------------------------------------------
    // AJAX: refresh a specific widget (used by the Refresh button / polls)
    // ---------------------------------------------------------------------
    public function refresh()
    {
        $widget = $this->input->post('widget');
        $from   = $this->input->post('date_from');
        $to     = $this->input->post('date_to');
        $group  = $this->input->post('granularity');

        $result = array();

        switch ($widget)
        {
            case 'sales_over_time':
                $result = $this->get_sales_over_time($from, $to, $group);
                break;
            case 'payment_methods':
                $result = $this->get_payment_methods($from, $to);
                break;
            case 'top_items':
                $result = $this->get_top_items($from, $to);
                break;
            case 'top_customers':
                $result = $this->get_top_customers($from, $to);
                break;
            case 'top_suppliers':
                $result = $this->get_top_suppliers($from, $to);
                break;
            case 'revenue_vs_cost':
                $result = $this->get_revenue_vs_cost($from, $to, $group);
                break;
            case 'hourly':
                $result = $this->get_hourly_transactions($from, $to);
                break;
            case 'stats':
                $result = $this->get_stat_tiles($from, $to);
                break;
            default:
                echo json_encode(array('success' => FALSE, 'message' => 'Unknown widget'));
                return;
        }

        echo json_encode(array('success' => TRUE, 'data' => $result));
    }

    // =====================================================================
    // Stat tiles
    // =====================================================================
    private function get_stat_tiles($from = NULL, $to = NULL)
    {
        return array(
            'today_sales'       => $this->dashboard_lib->today_sales(),
            'monthly_revenue'   => $this->dashboard_lib->monthly_revenue(),
            'transaction_count' => $this->dashboard_lib->transaction_count($from, $to),
            'items_sold'        => $this->dashboard_lib->items_sold($from, $to),
            'customer_count'    => $this->Customer->get_total_rows(),
            'low_stock_items'   => $this->dashboard_lib->low_stock_count()
        );
    }

    // =====================================================================
    // Chart: Sales & Transactions over time (dual axis line)
    // =====================================================================
    private function get_sales_over_time($from = NULL, $to = NULL, $group = 'day')
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select("
            TO_CHAR(sales.sale_time, 'YYYY-MM-DD') AS period,
            SUM(sales_payments.payment_amount) AS total_sales,
            COUNT(DISTINCT sales.sale_id) AS total_transactions
        ");
        $this->db->from('sales AS sales');
        $this->db->join('sales_payments AS sales_payments', 'sales_payments.sale_id = sales.sale_id', 'left');
        $this->db->where('sales.sale_time >=', $from . ' 00:00:00');
        $this->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        $this->db->group_by('period');
        $this->db->order_by('period', 'ASC');
        $rows = $this->db->get()->result_array();

        $labels = array();
        $sales  = array();
        $counts = array();

        foreach ($rows as $r)
        {
            $labels[] = date('M d', strtotime($r['period']));
            $sales[]  = (float) $r['total_sales'];
            $counts[] = (int)   $r['total_transactions'];
        }

        return array(
            'labels'       => $labels,
            'sales'        => $sales,
            'transactions' => $counts
        );
    }

    // =====================================================================
    // Chart: Payment Methods (doughnut)
    // =====================================================================
    private function get_payment_methods($from = NULL, $to = NULL)
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select('payment_type, SUM(payment_amount) AS total');
        $this->db->from('sales_payments AS sales_payments');
        $this->db->join('sales AS sales', 'sales.sale_id = sales_payments.sale_id');
        $this->db->where('sales.sale_time >=', $from . ' 00:00:00');
        $this->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        $this->db->group_by('payment_type');
        $this->db->order_by('total', 'DESC');
        $rows = $this->db->get()->result_array();

        $labels = array();
        $data   = array();

        foreach ($rows as $r)
        {
            $labels[] = $this->lang->line('sales_' . strtolower($r['payment_type'])) ?: $r['payment_type'];
            $data[]   = (float) $r['total'];
        }

        return array('labels' => $labels, 'data' => $data);
    }

    // =====================================================================
    // Chart: Top Selling Items (horizontal bar)
    // =====================================================================
    private function get_top_items($from = NULL, $to = NULL, $limit = 10)
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select('items.name, SUM(sales_items.quantity_purchased) AS qty');
        $this->db->from('sales_items AS sales_items');
        $this->db->join('items AS items', 'items.item_id = sales_items.item_id');
        $this->db->join('sales AS sales', 'sales.sale_id = sales_items.sale_id');
        $this->db->where('sales.sale_time >=', $from . ' 00:00:00');
        $this->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        $this->db->group_by('items.item_id, items.name');
        $this->db->order_by('qty', 'DESC');
        $this->db->limit($limit);
        $rows = $this->db->get()->result_array();

        $labels = array();
        $data   = array();

        foreach ($rows as $r)
        {
            $labels[] = $r['name'];
            $data[]   = (float) $r['qty'];
        }

        return array('labels' => $labels, 'data' => $data);
    }

    // =====================================================================
    // Chart: Top Customers (bar)
    // =====================================================================
    private function get_top_customers($from = NULL, $to = NULL, $limit = 10)
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select("
            CONCAT(people.first_name, ' ', people.last_name) AS name,
            SUM(sales_payments.payment_amount) AS total
        ");
        $this->db->from('sales AS sales');
        $this->db->join('customers AS customers', 'customers.person_id = sales.customer_id', 'left');
        $this->db->join('people AS people', 'people.person_id = customers.person_id', 'left');
        $this->db->join('sales_payments AS sales_payments', 'sales_payments.sale_id = sales.sale_id', 'left');
        $this->db->where('sales.sale_time >=', $from . ' 00:00:00');
        $this->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        $this->db->group_by('sales.customer_id, people.first_name, people.last_name');
        $this->db->order_by('total', 'DESC');
        $this->db->limit($limit);
        $rows = $this->db->get()->result_array();

        $labels = array();
        $data   = array();

        foreach ($rows as $r)
        {
            $labels[] = $r['name'];
            $data[]   = (float) $r['total'];
        }

        return array('labels' => $labels, 'data' => $data);
    }

    // =====================================================================
    // Chart: Top Suppliers (polar area) — based on receivings
    // =====================================================================
    private function get_top_suppliers($from = NULL, $to = NULL, $limit = 8)
    {
        $from = $from ?: date('Y-m-d', strtotime('-90 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select("
            CONCAT(suppliers.company_name) AS name,
            SUM(ri.item_cost_price * ri.quantity_purchased) AS total
        ");
        $this->db->from('receivings AS receivings');
        $this->db->join('suppliers AS suppliers', 'suppliers.person_id = receivings.supplier_id');
        $this->db->join('receivings_items AS ri', 'ri.receiving_id = receivings.receiving_id');
        $this->db->where('receivings.receiving_time >=', $from . ' 00:00:00');
        $this->db->where('receivings.receiving_time <=', $to   . ' 23:59:59');
        $this->db->group_by('receivings.supplier_id, suppliers.company_name');
        $this->db->order_by('total', 'DESC');
        $this->db->limit($limit);
        $rows = $this->db->get()->result_array();

        $labels = array();
        $data   = array();

        foreach ($rows as $r)
        {
            $labels[] = $r['name'];
            $data[]   = (float) $r['total'];
        }

        return array('labels' => $labels, 'data' => $data);
    }

    // =====================================================================
    // Chart: Revenue vs Cost (grouped bar)
    // =====================================================================
    private function get_revenue_vs_cost($from = NULL, $to = NULL, $group = 'day')
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        // Revenue and cost are queried separately and merged in PHP. Doing
        // them in one query would multiply rows (a sale with N payments and
        // M line items produces N*M rows), inflating both totals.
        $revenue_rows = $this->db
            ->select("TO_CHAR(sales.sale_time, 'YYYY-MM-DD') AS period, SUM(sales_payments.payment_amount) AS total")
            ->from('sales_payments AS sales_payments')
            ->join('sales AS sales', 'sales.sale_id = sales_payments.sale_id')
            ->where('sales.sale_time >=', $from . ' 00:00:00')
            ->where('sales.sale_time <=', $to   . ' 23:59:59')
            ->group_by('period')
            ->order_by('period', 'ASC')
            ->get()->result_array();

        $cost_rows = $this->db
            ->select("TO_CHAR(sales.sale_time, 'YYYY-MM-DD') AS period, SUM(sales_items.quantity_purchased * items.cost_price) AS total")
            ->from('sales_items AS sales_items')
            ->join('sales AS sales', 'sales.sale_id = sales_items.sale_id')
            ->join('items AS items', 'items.item_id = sales_items.item_id')
            ->where('sales.sale_time >=', $from . ' 00:00:00')
            ->where('sales.sale_time <=', $to   . ' 23:59:59')
            ->group_by('period')
            ->order_by('period', 'ASC')
            ->get()->result_array();

        $revenue_by_period = array();
        foreach ($revenue_rows as $r)
        {
            $revenue_by_period[$r['period']] = (float) $r['total'];
        }

        $cost_by_period = array();
        foreach ($cost_rows as $r)
        {
            $cost_by_period[$r['period']] = (float) $r['total'];
        }

        $periods = array_keys($revenue_by_period + $cost_by_period);
        sort($periods);

        $labels  = array();
        $revenue = array();
        $cost    = array();

        foreach ($periods as $p)
        {
            $labels[]  = date('M d', strtotime($p));
            $revenue[] = isset($revenue_by_period[$p]) ? $revenue_by_period[$p] : 0.0;
            $cost[]    = isset($cost_by_period[$p])    ? $cost_by_period[$p]    : 0.0;
        }

        return array(
            'labels'  => $labels,
            'revenue' => $revenue,
            'cost'    => $cost
        );
    }

    // =====================================================================
    // Chart: Transactions by Hour of Day (smooth line)
    // =====================================================================
    private function get_hourly_transactions($from = NULL, $to = NULL)
    {
        $from = $from ?: date('Y-m-d', strtotime('-30 days'));
        $to   = $to   ?: date('Y-m-d');

        $this->db->select("
            EXTRACT(HOUR FROM sale_time) AS hr,
            COUNT(*)        AS total
        ");
        $this->db->from('sales AS sales');
        $this->db->where('sales.sale_time >=', $from . ' 00:00:00');
        $this->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        $this->db->group_by('hr');
        $this->db->order_by('hr', 'ASC');
        $rows = $this->db->get()->result_array();

        // Fill all 24 hours so the chart is continuous
        $by_hour = array_fill(0, 24, 0);
        foreach ($rows as $r)
        {
            $by_hour[(int) $r['hr']] = (int) $r['total'];
        }

        $labels = array();
        $data   = array();

        foreach ($by_hour as $hr => $count)
        {
            $labels[] = sprintf('%02d:00', $hr);
            $data[]   = $count;
        }

        return array('labels' => $labels, 'data' => $data);
    }

    // =====================================================================
    // Recent transactions
    // =====================================================================
    private function get_recent_transactions($limit = 10)
    {
        $this->db->select("
            sales.sale_id,
            sales.sale_time,
            sales.comment,
            COALESCE(MAX(pay.total), 0) AS sale_amount,
            CONCAT(people_c.first_name, ' ', people_c.last_name) AS customer_name,
            CONCAT(people_e.first_name, ' ', people_e.last_name) AS employee_name,
            COUNT(sales_items.item_id) AS item_count
        ");
        $this->db->from('sales AS sales');
        $this->db->join('customers AS customers', 'customers.person_id = sales.customer_id', 'left');
        $this->db->join('people AS people_c', 'people_c.person_id = customers.person_id', 'left');
        $this->db->join('employees AS employees', 'employees.person_id = sales.employee_id', 'left');
        $this->db->join('people AS people_e', 'people_e.person_id = employees.person_id', 'left');
        $this->db->join('sales_items AS sales_items', 'sales_items.sale_id = sales.sale_id', 'left');
        $this->db->join('(
            SELECT sale_id, SUM(payment_amount) AS total
            FROM ' . $this->db->dbprefix('sales_payments') . ' AS sales_payments
            GROUP BY sale_id
        ) AS pay', 'pay.sale_id = sales.sale_id', 'left');
        $this->db->group_by('sales.sale_id, people_c.first_name, people_c.last_name, people_e.first_name, people_e.last_name, sales.sale_time, sales.comment');
        $this->db->order_by('sales.sale_time', 'DESC');
        $this->db->limit($limit);
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r)
        {
            $r['sale_time_formatted'] = date(
                $this->config->item('dateformat') . ' ' . $this->config->item('timeformat'),
                strtotime($r['sale_time'])
            );
            $r['sale_amount_formatted'] = to_currency($r['sale_amount']);
            $r['customer_name']         = $r['customer_name'] ?: $this->lang->line('sales_walkin');
            $r['payment_type']          = $this->get_sale_payment_type($r['sale_id']);
        }

        return $rows;
    }

    // =====================================================================
    // Recent receivings
    // =====================================================================
    private function get_recent_receivings($limit = 5)
    {
        $this->db->select("
            receivings.receiving_id,
            receivings.receiving_time,
            COALESCE(ri.total, 0) AS total,
            suppliers.company_name
        ");
        $this->db->from('receivings AS receivings');
        $this->db->join('suppliers AS suppliers', 'suppliers.person_id = receivings.supplier_id', 'left');
        $this->db->join('(
            SELECT receiving_id, SUM(item_cost_price * quantity_purchased) AS total
            FROM ' . $this->db->dbprefix('receivings_items') . ' AS receivings_items
            GROUP BY receiving_id
        ) AS ri', 'ri.receiving_id = receivings.receiving_id', 'left');
        $this->db->order_by('receivings.receiving_time', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    // =====================================================================
    // Low stock items
    // =====================================================================
    private function get_low_stock_items($limit = 5)
    {
        $this->db->select('
            items.item_id,
            items.name,
            COALESCE(item_quantities.quantity, 0) AS quantity,
            items.reorder_level
        ');
        $this->db->from('items AS items');
        $this->db->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id', 'left');
        $this->db->where('items.deleted', 0);
        $this->db->where('COALESCE(item_quantities.quantity, 0) <= items.reorder_level', NULL, FALSE);
        $this->db->order_by('quantity', 'ASC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    // =====================================================================
    // Helpers
    // =====================================================================
    private function get_sale_payment_type($sale_id)
    {
        $this->db->select('payment_type');
        $this->db->from('sales_payments');
        $this->db->where('sale_id', $sale_id);
        $this->db->limit(1);
        $row = $this->db->get()->row();
        return $row ? $row->payment_type : '';
    }

    private function get_expiring_items_count()
    {
        // Only if your schema tracks expiry
        if (!$this->db->field_exists('expire_date', 'items'))
        {
            return 0;
        }

        return (int) $this->db
            ->from('items')
            ->where('expire_date <=', date('Y-m-d', strtotime('+30 days')))
            ->where('expire_date >=', date('Y-m-d'))
            ->where('deleted', 0)
            ->count_all_results();
    }

    private function get_pending_purchases_count()
    {
        // Only if a purchase-orders module exists
        if (!$this->db->table_exists('purchase_orders'))
        {
            return 0;
        }

        return (int) $this->db
            ->from('purchase_orders')
            ->where('status', 'pending')
            ->count_all_results();
    }
}