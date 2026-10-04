<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Cashup class
 */

class Cashup extends CI_Model
{
	/*
	Determines if a given Cashup_id is an Cashup
	*/
	public function exists($cashup_id)
	{
		$this->db->from('cash_up');
		$this->db->where('cashup_id', $cashup_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/*
	Gets employee info
	*/
	public function get_employee($cashup_id)
	{
		$this->db->from('cash_up');
		$this->db->where('cashup_id', $cashup_id);

		return $this->Employee->get_info($this->db->get()->row()->employee_id);
	}

public function get_multiple_info($cash_up_ids)
{
    $this->db->from('cash_up');
    $this->db->where_in('cashup_id', $cash_up_ids);  // Changed from $cashup_ids to $cash_up_ids
    $this->db->order_by('cashup_id', 'asc');

    return $this->db->get();
}

	/*
	Gets rows
	*/
	public function get_found_rows($search, $filters)
	{
		return $this->search($search, $filters, 0, 0, 'cashup_id', 'asc', TRUE);
	}

	/*
	Searches cashups
	*/
	public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'cashup_id', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$this->db->select('COUNT(cash_up.cashup_id) as count');
		}

		$this->db->select('
			cash_up.cashup_id,
			MAX(cash_up.open_date) AS open_date,
			MAX(cash_up.close_date) AS close_date,
			MAX(cash_up.open_amount_cash) AS open_amount_cash,
			MAX(cash_up.transfer_amount_cash) AS transfer_amount_cash,
			MAX(cash_up.cash_in_amount) AS cash_in_amount,
			MAX(cash_up.cash_in_type) AS cash_in_type,
			MAX(cash_up.cash_out_amount) AS cash_out_amount,
			MAX(cash_up.cash_out_type) AS cash_out_type,
			MAX(cash_up.closed_amount_cash) AS closed_amount_cash,
			MAX(cash_up.closed_amount_due) AS closed_amount_due,
			MAX(cash_up.closed_amount_card) AS closed_amount_card,
			MAX(cash_up.closed_amount_check) AS closed_amount_check,
			MAX(cash_up.closed_amount_mpesa) AS closed_amount_mpesa,
			MAX(cash_up.expected_cash) AS expected_cash,
			MAX(cash_up.closed_amount_total) AS closed_amount_total,
			MAX(cash_up.total_trx_amount) AS total_trx_amount,
			MAX(cash_up.total_expense) AS total_expense,
			MAX(cash_up.actual_cash_counted) AS actual_cash_counted,
			MAX(cash_up.discrepancy_variance) AS discrepancy_variance,
			MAX(cash_up.description) AS description,
			MAX(cash_up.note) AS note,
			MAX(cash_up.open_employee_id) AS open_employee_id,
			MAX(cash_up.close_employee_id) AS close_employee_id,
			MAX(open_employees.first_name) AS open_first_name,
			MAX(open_employees.last_name) AS open_last_name,
			MAX(close_employees.first_name) AS close_first_name,
			MAX(close_employees.last_name) AS close_last_name
		');
		$this->db->from('cash_up AS cash_up');
		$this->db->join('people AS open_employees', 'open_employees.person_id = cash_up.open_employee_id', 'LEFT');
		$this->db->join('people AS close_employees', 'close_employees.person_id = cash_up.close_employee_id', 'LEFT');

		$this->db->group_start();
			$this->db->or_like('open_employees.first_name', $search);
			$this->db->or_like('open_employees.last_name', $search);
			$this->db->or_like('close_employees.first_name', $search);
			$this->db->or_like('close_employees.last_name', $search);
			// PostgreSQL uses || for concatenation instead of CONCAT()
			$this->db->or_like('(open_employees.first_name || \' \' || open_employees.last_name)', $search);
			$this->db->or_like('(close_employees.first_name || \' \' || close_employees.last_name)', $search);
		$this->db->group_end();

		$this->db->where('cash_up.deleted', $filters['is_deleted']);

		if(empty($this->config->item('date_or_time_format')))
		{
			// PostgreSQL uses TO_CHAR or casting for date formatting
			$this->db->where('cash_up.open_date::date BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']), NULL, FALSE);
		}
		else
		{
			$this->db->where('cash_up.open_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
		}

		$this->db->group_by('cashup_id');

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row_array()['count'];
		}

		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	/*
	Gets information about a particular cashup
	*/
	public function get_info($cashup_id)
	{
		$this->db->select('
			cash_up.cashup_id AS cashup_id,
			cash_up.open_date AS open_date,
			cash_up.close_date AS close_date,
			cash_up.open_amount_cash AS open_amount_cash,
			cash_up.transfer_amount_cash AS transfer_amount_cash,
			cash_up.cash_in_amount AS cash_in_amount,
			cash_up.cash_in_type AS cash_in_type,
			cash_up.cash_out_amount AS cash_out_amount,
			cash_up.cash_out_type AS cash_out_type,
			cash_up.closed_amount_cash AS closed_amount_cash,
			cash_up.closed_amount_due AS closed_amount_due,
			cash_up.closed_amount_card AS closed_amount_card,
			cash_up.closed_amount_check AS closed_amount_check,
			cash_up.closed_amount_mpesa AS closed_amount_mpesa,
			cash_up.expected_cash AS expected_cash,
			cash_up.closed_amount_total AS closed_amount_total,
			cash_up.total_trx_amount AS total_trx_amount,
			cash_up.total_expense AS total_expense,
			cash_up.actual_cash_counted AS actual_cash_counted,
			cash_up.discrepancy_variance AS discrepancy_variance,
			cash_up.description AS description,
			cash_up.note AS note,
			cash_up.open_employee_id AS open_employee_id,
			cash_up.close_employee_id AS close_employee_id,
			cash_up.deleted AS deleted,
			open_employees.first_name AS open_first_name,
			open_employees.last_name AS open_last_name,
			close_employees.first_name AS close_first_name,
			close_employees.last_name AS close_last_name
		');
		$this->db->from('cash_up AS cash_up');
		$this->db->join('people AS open_employees', 'open_employees.person_id = cash_up.open_employee_id', 'LEFT');
		$this->db->join('people AS close_employees', 'close_employees.person_id = cash_up.close_employee_id', 'LEFT');
		$this->db->where('cashup_id', $cashup_id);

		$query = $this->db->get();
		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$cash_up_obj = new stdClass();

			//Get all the fields from cashup table
			foreach($this->db->list_fields('cash_up') as $field)
			{
				$cash_up_obj->$field = '';
			}

			return $cash_up_obj;
		}
	}

	/*
	Inserts or updates an cashup
	*/
	public function save(&$cash_up_data, $cashup_id = FALSE)
	{
		if(!$cashup_id == -1 || !$this->exists($cashup_id))
		{
			if($this->db->insert('cash_up', $cash_up_data))
			{
				$cash_up_data['cashup_id'] = $this->db->insert_id();

				json_log('cashup_model_saved', ['cashup_id' => $cash_up_data['cashup_id'], 'is_new' => TRUE]);

				return TRUE;
			}

			json_log('cashup_model_save_failed', ['cashup_id' => $cashup_id, 'is_new' => TRUE]);

			return FALSE;
		}

		$this->db->where('cashup_id', $cashup_id);

		$result = $this->db->update('cash_up', $cash_up_data);

		if($result)
		{
			json_log('cashup_model_saved', ['cashup_id' => $cashup_id, 'is_new' => FALSE]);
		}
		else
		{
			json_log('cashup_model_save_failed', ['cashup_id' => $cashup_id, 'is_new' => FALSE]);
		}

		return $result;
	}

	/*
		Check if a cashup already exists for the same employee on the same day
	*/
	public function exists_open_for_employee_on_date($employee_id, $open_date)
	{
		$this->db->from('cash_up');
		$this->db->where('open_employee_id', $employee_id);
		$this->db->where('deleted', 0);

		if(empty($this->config->item('date_or_time_format')))
		{
			$open_date = date('Y-m-d', strtotime($open_date));
		}
		else
		{
			$open_date = substr($open_date, 0, 10);
		}

		$this->db->where('open_date::date = ' . $this->db->escape($open_date), NULL, FALSE);

		return $this->db->get()->num_rows() > 0;
	}

	/*
		Check if a cashup already exists for the same employee on the same day (excluding the given cashup_id)
	*/
	public function exists_open_for_employee_on_date_exclude($employee_id, $open_date, $exclude_id)
	{
		$this->db->from('cash_up');
		$this->db->where('open_employee_id', $employee_id);
		$this->db->where('deleted', 0);

		if(empty($this->config->item('date_or_time_format')))
		{
			$open_date = date('Y-m-d', strtotime($open_date));
		}
		else
		{
			$open_date = substr($open_date, 0, 10);
		}

		$this->db->where('open_date::date = ' . $this->db->escape($open_date), NULL, FALSE);
		$this->db->where('cashup_id !=', $exclude_id);

		return $this->db->get()->num_rows() > 0;
	}

	/*
		Deletes a list of cashups
	*/
	public function delete_list($cashup_ids)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
			$this->db->where_in('cashup_id', $cashup_ids);
			$success = $this->db->update('cash_up', array('deleted'=>1));
		$this->db->trans_complete();

		return $success;
	}
}
?>