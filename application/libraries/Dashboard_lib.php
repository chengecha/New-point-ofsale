<?php
class Dashboard_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function today_sales()
    {
        $row = $this->CI->db
            ->select('COALESCE(SUM(sales_payments.payment_amount), 0) AS total')
            ->from('sales_payments AS sales_payments')
            ->join('sales AS sales', 'sales.sale_id = sales_payments.sale_id')
            ->where('sales.sale_time >=', date('Y-m-d') . ' 00:00:00')
            ->where('sales.sale_time <', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00')
            ->get()->row();

        return (float) $row->total;
    }

    public function monthly_revenue()
    {
        $row = $this->CI->db
            ->select('COALESCE(SUM(sales_payments.payment_amount), 0) AS total')
            ->from('sales_payments AS sales_payments')
            ->join('sales AS sales', 'sales.sale_id = sales_payments.sale_id')
            ->where("EXTRACT(YEAR FROM sales.sale_time) =", date('Y'))
            ->where("EXTRACT(MONTH FROM sales.sale_time) =", date('m'))
            ->get()->row();

        return (float) $row->total;
    }

public function transaction_count($from = NULL, $to = NULL)
    {
        $this->CI->db->from('sales AS sales');
        if ($from) $this->CI->db->where('sales.sale_time >=', $from . ' 00:00:00');
        if ($to)   $this->CI->db->where('sales.sale_time <=', $to   . ' 23:59:59');
        return (int) $this->CI->db->count_all_results();
    }

    public function items_sold($from = NULL, $to = NULL)
    {
        $this->CI->db
            ->select('COALESCE(SUM(sales_items.quantity_purchased), 0) AS qty')
            ->from('sales_items AS sales_items')
            ->join('sales AS sales', 'sales.sale_id = sales_items.sale_id');

        if ($from) $this->CI->db->where('sales.sale_time >=', $from . ' 00:00:00');
        if ($to)   $this->CI->db->where('sales.sale_time <=', $to   . ' 23:59:59');

        $row = $this->CI->db->get()->row();
        return (float) $row->qty;
    }

    public function low_stock_count()
    {
        return (int) $this->CI->db
            ->from('items AS items')
            ->join('item_quantities AS item_quantities', 'item_quantities.item_id = items.item_id', 'left')
            ->where('items.deleted', 0)
            ->where('COALESCE(item_quantities.quantity, 0) <= items.reorder_level', NULL, FALSE)
            ->count_all_results();
    }
}