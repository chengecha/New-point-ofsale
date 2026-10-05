<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Cashups extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('cashups');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_cashups_manage_table_headers());

		// filters that will be loaded in the multiselect dropdown
		$data['filters'] = array('is_deleted' => $this->lang->line('cashups_is_deleted'));

		$this->load->view('cashups/manage', $data);
	}

	public function search()
	{
		$cash_up = 0;
		$search   = $this->input->get('search');
		$limit    = $this->input->get('limit');
		$offset   = $this->input->get('offset');
		$sort     = $this->input->get('sort');
		$order    = $this->input->get('order');
		$filters  = array(
					 'start_date' => $this->input->get('start_date'),
					 'end_date' => $this->input->get('end_date'),
					 'is_deleted' => FALSE);

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);
		$cash_ups = $this->Cashup->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Cashup->get_found_rows($search, $filters);
		$data_rows = array();
		foreach($cash_ups->result() as $cash_up)
		{
			$data_rows[] = $this->xss_clean(get_cash_up_data_row($cash_up));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function view($cashup_id = -1)
	{
		$data = array();
		$data['is_close_mode'] = FALSE;
		foreach($this->Employee->get_all()->result() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$data['payment_types'] = array(
			'cash' => $this->lang->line('sales_cash'),
			'mpesa' => $this->lang->line('sales_mpesa')
		);

		$cash_ups_info = $this->Cashup->get_info($cashup_id);

		foreach(get_object_vars($cash_ups_info) as $property => $value)
		{
			$cash_ups_info->$property = $this->xss_clean($value);
		}

		// open cashup
		if(empty($cash_ups_info->cashup_id))
		{
			$logged_in_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$open_date = date('Y-m-d H:i:s');

			if($this->Cashup->exists_open_for_employee_on_date($logged_in_employee_id, $open_date))
			{
				echo '<div class="alert alert-danger">' . $this->lang->line('cashups_duplicate_entry') . '</div>';
				return;
			}

			$cash_ups_info->open_date = $open_date;
			$cash_ups_info->open_employee_id = $logged_in_employee_id;

			$cash_ups_info->cash_in_amount = 0;
			$cash_ups_info->cash_in_type = 'cash';
			$cash_ups_info->cash_out_amount = 0;
			$cash_ups_info->cash_out_type = 'cash';
			$cash_ups_info->total_trx_amount = 0;
			$cash_ups_info->total_expense = 0;
			$cash_ups_info->actual_cash_counted = 0;
			$cash_ups_info->discrepancy_variance = 0;
		}
		// if all the amounts are null or 0 that means it's a close cashup
		elseif(floatval($cash_ups_info->closed_amount_cash) == 0 &&
			floatval($cash_ups_info->closed_amount_due) == 0 &&
			floatval($cash_ups_info->closed_amount_card) == 0 &&
			floatval($cash_ups_info->closed_amount_check) == 0 &&
			floatval($cash_ups_info->closed_amount_mpesa) == 0)
		{
			$data['is_close_mode'] = TRUE;
			error_log('DEBUG Cashup view: close cashup block entered for cashup_id=' . $cashup_id . ', open_date=' . $cash_ups_info->open_date . ', open_amount_cash=' . $cash_ups_info->open_amount_cash);

			// set the close date and time to the actual as this is a close session
			$cash_ups_info->close_date = date('Y-m-d H:i:s');

			// if it's date mode only and not date & time truncate the open and end date to date only
			if(empty($this->config->item('date_or_time_format')))
			{
				// search for all the payments given the time range
				$inputs = array('start_date' => substr($cash_ups_info->open_date, 0, 10), 'end_date' => substr($cash_ups_info->close_date, 0, 10), 'sale_type' => 'complete', 'location_id' => 'all');
			}
			else
			{
				// search for all the payments given the time range
				$inputs = array('start_date' => $cash_ups_info->open_date, 'end_date' => $cash_ups_info->close_date, 'sale_type' => 'complete', 'location_id' => 'all');
			}

			// get all the transactions payment summaries
			$this->load->model('reports/Summary_payments');
			$reports_data = $this->Summary_payments->getData($inputs);

			$cash_payments = 0;
			$mpesa_payments = 0;

			foreach($reports_data as $row)
			{
				if($row['trans_group'] == $this->lang->line('reports_trans_payments'))
				{
					if($row['trans_type'] == $this->lang->line('sales_cash'))
					{
						$cash_payments += $this->xss_clean($row['trans_amount']);
					}
					elseif($row['trans_type'] == $this->lang->line('sales_mpesa'))
					{
						$mpesa_payments += $this->xss_clean($row['trans_amount']);
					}
				}
			}

			// Set closed amounts (for backward compatibility)
			$cash_ups_info->closed_amount_cash = $cash_payments;
			$cash_ups_info->closed_amount_mpesa = $mpesa_payments;

			// Total trx amount = cash + mpesa payments
			$cash_ups_info->total_trx_amount = $cash_payments + $mpesa_payments;

			// lookup total expenses for the day
			$this->db->select('(SUM(amount)) AS total_amount', FALSE);
			$this->db->from('expenses');
			$this->db->where('deleted', 0);

			if(empty($this->config->item('date_or_time_format')))
			{
				$this->db->where('date::date = ' . $this->db->escape(date('Y-m-d')), NULL, FALSE);
			}
			else
			{
				$this->db->where('date::date = ' . $this->db->escape(date('Y-m-d')), NULL, FALSE);
			}

			$query = $this->db->get();
			$exp_row = $query->row();
			$total_expense = $exp_row && $exp_row->total_amount ? floatval($exp_row->total_amount) : 0;
			$cash_ups_info->total_expense = $total_expense;

			json_log('cashup_view_expense', ['cashup_id' => $cashup_id, 'open_date' => $cash_ups_info->open_date, 'close_date' => $cash_ups_info->close_date, 'total_expense' => $total_expense, 'rows' => $query->num_rows(), 'driver' => $this->db->dbdriver]);

			// Initialize new fields
			$cash_ups_info->cash_in_amount = 0;
			$cash_ups_info->cash_in_type = 'cash';
			$cash_ups_info->cash_out_amount = 0;
			$cash_ups_info->cash_out_type = 'cash';
			$cash_ups_info->actual_cash_counted = 0;
			$cash_ups_info->discrepancy_variance = 0;

			// Total = opencash + cashin + trxAmount
			$cash_ups_info->closed_amount_total = $this->_calculate_total(
				floatval($cash_ups_info->open_amount_cash),
				floatval($cash_ups_info->cash_in_amount),
				floatval($cash_ups_info->total_trx_amount)
			);

			// Expected Cash = Total - (cashout + expense_cash)
			$cash_ups_info->expected_cash = $this->_calculate_expected_cash(
				floatval($cash_ups_info->closed_amount_total),
				floatval($cash_ups_info->cash_out_amount),
				floatval($cash_ups_info->total_expense)
			);
		}

		$data['cash_ups_info'] = $cash_ups_info;

		$this->load->view("cashups/form", $data);
	}

	public function initiate_close()
	{
		$cashup_id = $this->input->post('cashup_id');
		$cash_ups_info = $this->Cashup->get_info($cashup_id);

		foreach(get_object_vars($cash_ups_info) as $property => $value)
		{
			$cash_ups_info->$property = $this->xss_clean($value);
		}

		// set the close date and time to the actual as this is a close session
		$cash_ups_info->close_date = date('Y-m-d H:i:s');

		// if it's date mode only and not date & time truncate the open and end date to date only
		if(empty($this->config->item('date_or_time_format')))
		{
			$inputs = array('start_date' => substr($cash_ups_info->open_date, 0, 10), 'end_date' => substr($cash_ups_info->close_date, 0, 10), 'sale_type' => 'complete', 'location_id' => 'all');
		}
		else
		{
			$inputs = array('start_date' => $cash_ups_info->open_date, 'end_date' => $cash_ups_info->close_date, 'sale_type' => 'complete', 'location_id' => 'all');
		}

		// get all the transactions payment summaries
		$this->load->model('reports/Summary_payments');
		$reports_data = $this->Summary_payments->getData($inputs);

		$cash_payments = 0;
		$mpesa_payments = 0;

		foreach($reports_data as $row)
		{
			if($row['trans_group'] == $this->lang->line('reports_trans_payments'))
			{
				if($row['trans_type'] == $this->lang->line('sales_cash'))
				{
					$cash_payments += $this->xss_clean($row['trans_amount']);
				}
				elseif($row['trans_type'] == $this->lang->line('sales_mpesa'))
				{
					$mpesa_payments += $this->xss_clean($row['trans_amount']);
				}
			}
		}

		// Set closed amounts (for backward compatibility)
		$cash_ups_info->closed_amount_cash = $cash_payments;
		$cash_ups_info->closed_amount_mpesa = $mpesa_payments;

		// Total trx amount = cash + mpesa payments
		$cash_ups_info->total_trx_amount = $cash_payments + $mpesa_payments;

		// lookup total expenses for the day
		$this->db->select('(SUM(amount)) AS total_amount', FALSE);
		$this->db->from('expenses');
		$this->db->where('deleted', 0);

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('date::date = ' . $this->db->escape(date('Y-m-d')), NULL, FALSE);
		}
		else
		{
			$this->db->where('date::date = ' . $this->db->escape(date('Y-m-d')), NULL, FALSE);
		}

		$query = $this->db->get();
		$exp_row = $query->row();
		$total_expense = $exp_row && $exp_row->total_amount ? floatval($exp_row->total_amount) : 0;
		$cash_ups_info->total_expense = $total_expense;

		json_log('cashup_initiate_close_expense', ['cashup_id' => $cashup_id, 'open_date' => $cash_ups_info->open_date, 'close_date' => $cash_ups_info->close_date, 'total_expense' => $total_expense, 'rows' => $query->num_rows(), 'driver' => $this->db->dbdriver]);

		// Total = opencash + cashin + trxAmount
		$cash_ups_info->closed_amount_total = $this->_calculate_total(
			floatval($cash_ups_info->open_amount_cash),
			floatval($cash_ups_info->cash_in_amount),
			floatval($cash_ups_info->total_trx_amount)
		);

		// Expected Cash = Total - (cashout + expense_cash)
		$cash_ups_info->expected_cash = $this->_calculate_expected_cash(
			floatval($cash_ups_info->closed_amount_total),
			floatval($cash_ups_info->cash_out_amount),
			floatval($cash_ups_info->total_expense)
		);

		echo json_encode(array(
			'close_date' => to_datetime(strtotime($cash_ups_info->close_date)),
			'close_employee_id' => $cash_ups_info->close_employee_id,
			'closed_amount_cash' => to_currency_no_money($cash_payments),
			'closed_amount_mpesa' => to_currency_no_money($mpesa_payments),
			'total_trx_amount' => to_currency_no_money($cash_ups_info->total_trx_amount),
			'total_expense' => to_currency_no_money($total_expense),
			'closed_amount_total' => to_currency_no_money($cash_ups_info->closed_amount_total),
			'expected_cash' => to_currency_no_money($cash_ups_info->expected_cash),
			'success' => TRUE
		));
	}

	public function get_row($row_id)
	{
		$cash_ups_info = $this->Cashup->get_info($row_id);
		$data_row = $this->xss_clean(get_cash_up_data_row($cash_ups_info));

		echo json_encode($data_row);
	}

	public function save($cashup_id = -1)
	{
		$open_date = $this->input->post('open_date');
		$open_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $open_date);

		$close_date = $this->input->post('close_date');
		$close_date_formatter = $close_date ? date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $close_date) : false;
		$formatted_close_date = $close_date_formatter ? $close_date_formatter->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

		$cash_up_data = array(
			'open_date' => $open_date_formatter->format('Y-m-d H:i:s'),
			'close_date' => $formatted_close_date,
			'open_amount_cash' => $this->input->post('open_amount_cash') == '' ? 0 : parse_decimals($this->input->post('open_amount_cash')),
			'cash_in_amount' => $this->input->post('cash_in_amount') == '' ? 0 : parse_decimals($this->input->post('cash_in_amount')),
			'cash_in_type' => $this->input->post('cash_in_type'),
			'cash_out_amount' => $this->input->post('cash_out_amount') == '' ? 0 : parse_decimals($this->input->post('cash_out_amount')),
			'cash_out_type' => $this->input->post('cash_out_type'),
			'total_trx_amount' => $this->input->post('total_trx_amount') == '' ? 0 : parse_decimals($this->input->post('total_trx_amount')),
			'total_expense' => $this->input->post('total_expense') == '' ? 0 : parse_decimals($this->input->post('total_expense')),
			'actual_cash_counted' => $this->input->post('actual_cash_counted') == '' ? 0 : parse_decimals($this->input->post('actual_cash_counted')),
			'discrepancy_variance' => $this->input->post('discrepancy_variance') == '' ? 0 : parse_decimals($this->input->post('discrepancy_variance')),
			'transfer_amount_cash' => $this->input->post('transfer_amount_cash') == '' ? 0 : parse_decimals($this->input->post('transfer_amount_cash')),
			'closed_amount_cash' => $this->input->post('closed_amount_cash') == '' ? 0 : parse_decimals($this->input->post('closed_amount_cash')),
			'closed_amount_due' => $this->input->post('closed_amount_due') == '' ? 0 : parse_decimals($this->input->post('closed_amount_due')),
			'closed_amount_card' => $this->input->post('closed_amount_card') == '' ? 0 : parse_decimals($this->input->post('closed_amount_card')),
			'closed_amount_check' => $this->input->post('closed_amount_check') == '' ? 0 : parse_decimals($this->input->post('closed_amount_check')),
			'closed_amount_mpesa' => $this->input->post('closed_amount_mpesa') == '' ? 0 : parse_decimals($this->input->post('closed_amount_mpesa')),
			'expected_cash' => $this->input->post('expected_cash') == '' ? 0 : parse_decimals($this->input->post('expected_cash')),
			'closed_amount_total' => $this->input->post('closed_amount_total') == '' ? 0 : parse_decimals($this->input->post('closed_amount_total')),
			'description' => $this->input->post('description'),
			'note' => 0,
			'open_employee_id' => $this->input->post('open_employee_id'),
			'close_employee_id' => $this->input->post('close_employee_id'),
			'deleted' => $this->input->post('deleted') != NULL
		);

		if($cashup_id == -1 || !$this->Cashup->exists($cashup_id))
		{
			$duplicate_exists = $this->Cashup->exists_open_for_employee_on_date($cash_up_data['open_employee_id'], $cash_up_data['open_date']);
		}
		else
		{
			$duplicate_exists = $this->Cashup->exists_open_for_employee_on_date_exclude($cash_up_data['open_employee_id'], $cash_up_data['open_date'], $cashup_id);
		}

		if($duplicate_exists)
		{
			json_log('cashup_duplicate', ['cashup_id' => $cashup_id, 'open_employee_id' => $cash_up_data['open_employee_id'], 'open_date' => $cash_up_data['open_date']]);
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('cashups_duplicate_entry'), 'id' => -1));
			return;
		}

		if($this->Cashup->save($cash_up_data, $cashup_id))
		{
			$cash_up_data = $this->xss_clean($cash_up_data);

			//New cashup_id
			if($cashup_id == -1)
			{
				json_log('cashup_created', ['cashup_id' => $cash_up_data['cashup_id'], 'open_amount_cash' => $cash_up_data['open_amount_cash'], 'closed_amount_total' => $cash_up_data['closed_amount_total']]);
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_adding'), 'id' => $cash_up_data['cashup_id']));
			}
			else // Existing Cashup
			{
				json_log('cashup_updated', ['cashup_id' => $cashup_id, 'closed_amount_cash' => $cash_up_data['closed_amount_cash'], 'closed_amount_total' => $cash_up_data['closed_amount_total']]);
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_updating'), 'id' => $cashup_id));
			}
		}
		else//failure
		{
			json_log('cashup_save_failed', ['cashup_id' => $cashup_id]);
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('cashups_error_adding_updating'), 'id' => -1));
		}
	}

	public function check_open_cashup_exists()
	{
		$logged_in_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$open_date = date('Y-m-d H:i:s');

		$exists = $this->Cashup->exists_open_for_employee_on_date($logged_in_employee_id, $open_date);

		echo json_encode(array('exists' => $exists));
	}

	public function delete()
	{
		$cash_ups_to_delete = $this->input->post('ids');

		if($this->Cashup->delete_list($cash_ups_to_delete))
		{
			json_log('cashup_deleted', ['cashup_ids' => $cash_ups_to_delete]);
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('cashups_successful_deleted') . ' ' . count($cash_ups_to_delete) . ' ' . $this->lang->line('cashups_one_or_multiple'), 'ids' => $cash_ups_to_delete));
		}
		else
		{
			json_log('cashup_delete_failed', ['cashup_ids' => $cash_ups_to_delete]);
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('cashups_cannot_be_deleted'), 'ids' => $cash_ups_to_delete));
		}
	}

	/*
		AJAX call from cashup input form to calculate the total
	*/
	public function ajax_cashup_total()
	{
		$open_amount_cash = parse_decimals($this->input->post('open_amount_cash'));
		$cash_in_amount = parse_decimals($this->input->post('cash_in_amount'));
		$cash_out_amount = parse_decimals($this->input->post('cash_out_amount'));
		$total_trx_amount = parse_decimals($this->input->post('total_trx_amount'));
		$total_expense = parse_decimals($this->input->post('total_expense'));
		$actual_cash_counted = parse_decimals($this->input->post('actual_cash_counted'));

		// Total = opencash + cashin + trxAmount
		$total = $this->_calculate_total($open_amount_cash, $cash_in_amount, $total_trx_amount);

		// Expected Cash = Total - (cashout + expense_cash)
		$expected_cash = $this->_calculate_expected_cash($total, $cash_out_amount, $total_expense);

		// Discrepancy Variance = actual_cash_counted - expected_cash
		$discrepancy_variance = $actual_cash_counted - $expected_cash;

		echo json_encode(array(
			'total' => to_currency_no_money($total),
			'expected_cash' => to_currency_no_money($expected_cash),
			'discrepancy_variance' => to_currency_no_money($discrepancy_variance)
		));
	}

	/*
		Calculate total = opencash + cashin + trxAmount
	*/
	private function _calculate_total($open_amount_cash, $cash_in_amount, $total_trx_amount)
	{
		return ($open_amount_cash + $cash_in_amount + $total_trx_amount);
	}

	/*
		Calculate expected cash = total - (cashout + expense_cash)
	*/
	private function _calculate_expected_cash($total, $cash_out_amount, $total_expense)
	{
		return ($total - $cash_out_amount - $total_expense);
	}
}
?>
