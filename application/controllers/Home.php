<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Home extends Secure_Controller 
{
	public function __construct()
	{
		parent::__construct(NULL, NULL, 'home');
	}

	public function index()
	{
		$person_id = $this->session->userdata('person_id');

		$data['todays_transactions'] = $this->get_todays_transactions(5);
		$data['low_stock_items'] = $this->get_low_stock_items(5);
		$data['has_sales_access'] = $this->Employee->has_module_grant('sales', $person_id);
		$data['has_receivings_access'] = $this->Employee->has_module_grant('receivings', $person_id);
		$data['has_cashups_access'] = $this->Employee->has_module_grant('cashups', $person_id);
		$data['has_customers_access'] = $this->Employee->has_module_grant('customers', $person_id);

		$this->load->view('home/home', $data);
	}

	public function logout()
	{
		json_log('logout', []);

		$this->Employee->logout();
	}

	/*
	Load "change employee password" form
	*/
	public function change_password($employee_id = -1)
	{
		$person_info = $this->Employee->get_info($employee_id);
		foreach(get_object_vars($person_info) as $property => $value)
		{
			$person_info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $person_info;

		$this->load->view('home/form_change_password', $data);
	}

	/*
	Change employee password
	*/
	public function save($employee_id = -1)
	{
		if($this->input->post('current_password') != '' && $employee_id != -1)
		{
			if($this->Employee->check_password($this->input->post('username'), $this->input->post('current_password')))
			{
				$employee_data = array(
					'username' => $this->input->post('username'),
					'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
					'hash_version' => 2
				);

				if($this->Employee->change_password($employee_data, $employee_id))
				{
					echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('employees_successful_change_password'), 'id' => $employee_id));
				}
				else//failure
				{
					echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('employees_unsuccessful_change_password'), 'id' => -1));
				}
			}
			else
			{
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('employees_current_password_invalid'), 'id' => -1));
			}
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('employees_current_password_invalid'), 'id' => -1));
		}
	}

	/*
	Get today's last transactions for the command center live feed
	*/
	private function get_todays_transactions($limit = 5)
	{
		$today = date('Y-m-d');

		$this->db->select('
			sales.sale_id,
			sales.sale_time,
			COALESCE(sales.invoice_number, sales.sale_id::text) AS receipt_number,
			COALESCE(MAX(pay.total), 0) AS sale_amount
		');
		$this->db->from('sales AS sales');
		$this->db->join('(
			SELECT sale_id, SUM(payment_amount) AS total
			FROM ' . $this->db->dbprefix('sales_payments') . ' AS sales_payments
			GROUP BY sale_id
		) AS pay', 'pay.sale_id = sales.sale_id', 'left');
		$this->db->where('sales.sale_time >=', $today . ' 00:00:00');
		$this->db->where('sales.sale_time <=', $today . ' 23:59:59');
		$this->db->where('sales.sale_status', COMPLETED);
		$this->db->group_by('sales.sale_id');
		$this->db->order_by('sales.sale_time', 'DESC');
		$this->db->limit($limit);

		$rows = $this->db->get()->result_array();

		foreach ($rows as &$row)
		{
			$row['sale_time_formatted'] = date($this->config->item('timeformat'), strtotime($row['sale_time']));
			$row['sale_amount_formatted'] = to_currency($row['sale_amount']);
			$row['payment_type'] = $this->get_sale_payment_type($row['sale_id']);
		}

		return $rows;
	}

	/*
	Get the primary payment type for a given sale
	*/
	private function get_sale_payment_type($sale_id)
	{
		$this->db->select('payment_type');
		$this->db->from('sales_payments');
		$this->db->where('sale_id', $sale_id);
		$this->db->limit(1);
		$row = $this->db->get()->row();

		return $row ? $row->payment_type : '';
	}

	/*
	Get items below their reorder level for the command center inventory panel
	*/
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
}
?>
