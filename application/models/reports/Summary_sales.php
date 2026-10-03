 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_sales extends Summary_report
{
   protected function _get_data_columns()
   {
      return array(
         array('sale_date' => $this->lang->line('reports_date'), 'sortable' => FALSE),
         array('sales' => $this->lang->line('reports_sales'), 'sorter' => 'number_sorter'),
         array('quantity' => $this->lang->line('reports_quantity'), 'sorter' => 'number_sorter'),
         array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
         array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
         array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
         array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
         array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'));
   }

   protected function _select(array $inputs)
   {
      parent::_select($inputs);

      // Pass FALSE strictly as the 2nd parameter to disable CI identifier escaping
      $this->db->select('
            DATE(sales.sale_time) AS sale_date,
            SUM(sales_items.quantity_purchased) AS quantity_purchased,
            COUNT(DISTINCT sales.sale_id) AS sales
      ', FALSE);
   }

   protected function _group_order()
   {
      // Matches the SELECT expression exactly to prevent PostgreSQL mismatch errors
      $this->db->group_by('DATE(sales.sale_time)');
      $this->db->order_by('sale_date', 'ASC');
   }
}
?>
 
 
 <!-- if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_sales extends Summary_report
{
   protected function _get_data_columns()
   {
      return array(
         array('sale_date' => $this->lang->line('reports_date'), 'sortable' => FALSE),
         array('sales' => $this->lang->line('reports_sales'), 'sorter' => 'number_sorter'),
         array('quantity' => $this->lang->line('reports_quantity'), 'sorter' => 'number_sorter'),
         array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
         array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
         array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
         array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
         array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'));
   }

   protected function _select(array $inputs)
   {
      parent::_select($inputs);

      $this->db->select('
            sales.sale_time::date AS sale_date,
            SUM(sales_items.quantity_purchased) AS quantity_purchased,
            COUNT(DISTINCT sales.sale_id) AS sales
      ', NULL, FALSE);
   }

   // protected function _group_order()
   // {
   //    $this->db->group_by('sales.sale_time::date', NULL, FALSE);
   //    $this->db->order_by('sale_date');
   // }
protected function _group_order()
{
    $this->db->group_by('DATE(sales.sale_time)');
    $this->db->order_by('sale_date');
}

}

 -->
