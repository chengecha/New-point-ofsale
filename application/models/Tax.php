<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tax class
 */

class Tax extends CI_Model
{
	/**
	 * Determines if a given row is on file
	 */
	public function exists($tax_rate_id)
	{
		$this->db->from('tax_rates');
		$this->db->where('tax_rate_id', $tax_rate_id);

		return ($this->db->get()->num_rows() == 1);
	}

	/**
	 * Gets total of rows
	 */
	public function get_total_rows()
	{
		$this->db->from('tax_rates');

		return $this->db->count_all_results();
	}

	/**
	 * Gets list of tax rates that are assigned to a particular tax category
	 */
	public function get_tax_category_usage($tax_category_id)
	{
		$this->db->from('tax_rates');
		$this->db->where('rate_tax_category_id', $tax_category_id);

		return $this->db->count_all_results();
	}

	/**
	 * Gets the row for a particular id
	 */
	public function get_info($tax_rate_id)
	{
		$this->db->select('tax_rate_id');
		$this->db->select('rate_tax_code_id');
		$this->db->select('tax_code');
		$this->db->select('tax_code_name');
		$this->db->select('rate_jurisdiction_id');
		$this->db->select('jurisdiction_name');
		$this->db->select('rate_tax_category_id');
		$this->db->select('tax_category');
		$this->db->select('tax_rate');
		$this->db->select('tax_rounding_code');
		$this->db->from('tax_rates');
		$this->db->join('tax_codes',
			'rate_tax_code_id = tax_code_id', 'LEFT');
		$this->db->join('tax_categories',
			'rate_tax_category_id = tax_category_id', 'LEFT');
		$this->db->join('tax_jurisdictions',
			'rate_jurisdiction_id = jurisdiction_id', 'LEFT');
		$this->db->where('tax_rate_id', $tax_rate_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_rate_obj = new stdClass();
			$tax_rate_obj->tax_rate_id = NULL;
			$tax_rate_obj->rate_tax_code_id = NULL;
			$tax_rate_obj->tax_code = '';
			$tax_rate_obj->tax_code_name = '';
			$tax_rate_obj->rate_tax_category_id = NULL;
			$tax_rate_obj->tax_category = '';
			$tax_rate_obj->tax_rate = 0.0;
			$tax_rate_obj->tax_rounding_code = '0';
			$tax_rate_obj->rate_jurisdiction_id = NULL;
			$tax_rate_obj->jurisdiction_name = '';

			//Get all the fields from tax_codes table

			return $tax_rate_obj;
		}
	}

	/**
	 * Get taxes to be collected for a given tax code
	 */
	 public function get_taxes($tax_code_id, $tax_category_id)
	{
		// PostgreSQL uses || for string concatenation instead of CONCAT
		// PostgreSQL handles arithmetic with NULL values differently
		$query = $this->db->query('
			SELECT tax_rate_id, rate_tax_code_id, tax_code, tax_code_name, tax_type, cascade_sequence, 
				rate_tax_category_id, tax_category, rate_jurisdiction_id, jurisdiction_name, tax_group, 
				tax_rate, tax_rounding_code, 
				COALESCE(tax_categories.tax_group_sequence, 0) + COALESCE(tax_jurisdictions.tax_group_sequence, 0) AS tax_group_sequence 
			FROM ' . $this->db->dbprefix('tax_rates') . ' 
			LEFT OUTER JOIN ' . $this->db->dbprefix('tax_codes') . ' ON rate_tax_code_id = tax_code_id 
			LEFT OUTER JOIN ' . $this->db->dbprefix('tax_categories') . ' AS tax_categories ON rate_tax_category_id = tax_category_id 
			LEFT OUTER JOIN ' . $this->db->dbprefix('tax_jurisdictions') . ' AS tax_jurisdictions ON rate_jurisdiction_id = jurisdiction_id 
			WHERE rate_tax_code_id = ' . $this->db->escape($tax_code_id) . ' AND rate_tax_category_id = ' . $this->db->escape($tax_category_id) . '
			ORDER BY cascade_sequence, tax_group, jurisdiction_name, COALESCE(tax_jurisdictions.tax_group_sequence, 0) + COALESCE(tax_categories.tax_group_sequence, 0)');

		return $query->result_array();
	}

	/**
	 * Gets information about a particular tax_code
	 */
	public function get_rate_info($tax_code_id, $tax_category_id)
	{
		$this->db->from('tax_rates');
		$this->db->join('tax_categories', 'rate_tax_category_id = tax_category_id');
		$this->db->where('rate_tax_code_id', $tax_code_id);
		$this->db->where('rate_tax_category_id', $tax_category_id);

		$query = $this->db->get();

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object
			$tax_rate_obj = new stdClass();

			//Get all the fields from tax_codes table
			foreach($this->db->list_fields('tax_rates') as $field)
			{
				$tax_rate_obj->$field = '';
			}
			//Get all the fields from tax_rates table
			foreach($this->db->list_fields('tax_categories') as $field)
			{
				$tax_rate_obj->$field = '';
			}

			return $tax_rate_obj;
		}
	}

	/**
	Inserts or updates a tax_rates entry
	*/
	public function save(&$tax_rate_data, $tax_rate_id = -1)
	{
		if(!$this->exists($tax_rate_id))
		{
			if($this->db->insert('tax_rates', $tax_rate_data))
			{
				$tax_rate_data['tax_rate_id'] = $this->db->insert_id();

				return TRUE;
			}
		}
		else
		{
			$this->db->where('tax_rate_id', $tax_rate_id);

			if($this->db->update('tax_rates', $tax_rate_data))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Deletes a single tax rate entry
	 */
	public function delete($tax_rate_id)
	{
		return $this->db->delete('tax_rates', array('tax_rate_id' => $tax_rate_id));
	}

	/**
	 * Deletes a list of tax rates
	 */
	public function delete_list($tax_rate_ids)
	{
		$this->db->where_in('tax_rate_id', $tax_rate_ids);

		return $this->db->delete('tax_rates');
	}

	/**
	 * Gets tax_codes
	 */
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'tax_code_name', 'asc', TRUE);
	}

	/**
	 * Performs a search on tax_rates
	 */
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'tax_code_name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case

		if($count_only == TRUE)
		{
			$this->db->select('COUNT(tax_rate_id) as count');
		} else
		{
			$this->db->select('tax_rate_id');
			$this->db->select('tax_code');
			$this->db->select('rate_tax_code_id');
			$this->db->select('tax_code_name');
			$this->db->select('rate_jurisdiction_id');
			$this->db->select('jurisdiction_name');
			$this->db->select('rate_tax_category_id');
			$this->db->select('tax_category');
			$this->db->select('tax_rate');
			$this->db->select('tax_rounding_code');
		}
		$this->db->from('tax_rates');
		$this->db->join('tax_codes',
			'rate_tax_code_id = tax_code_id', 'LEFT');
		$this->db->join('tax_categories',
			'rate_tax_category_id = tax_category_id', 'LEFT');
		$this->db->join('tax_jurisdictions',
			'rate_jurisdiction_id = jurisdiction_id', 'LEFT');

		if(!empty($search))
		{
			$this->db->group_start();
			$this->db->like('tax_code', $search);
			$this->db->or_like('tax_code_name', $search);
			$this->db->or_like('tax_category', $search);
			$this->db->or_like('jurisdiction_name', $search);
			$this->db->group_end();
		}

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $this->db->get()->row()->count;
		}

		$this->db->order_by($sort, $order);

		if($rows > 0)
		{
			$this->db->limit($rows, $limit_from);
		}

		return $this->db->get();
	}

	public function get_tax_code_type_name($tax_code_type)
	{
		if($tax_code_type == '0')
		{
			return $this->lang->line('taxes_tax_included');
		}
		else
		{
			return $this->lang->line('taxes_tax_excluded');
		}
	}

	public function get_tax_category($tax_category_id)
	{
		$this->db->select('tax_category');
		$this->db->from('tax_categories');
		$this->db->where('tax_category_id', $tax_category_id);

		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			return $query->row()->tax_category;
		}
		return '';
	}

	public function get_all_tax_categories()
	{
		$this->db->from('tax_categories');
		$this->db->order_by('tax_category_id');

		return $this->db->get();
	}

	public function get_tax_category_id($tax_category)
	{
		$this->db->select('tax_category_id');
		$this->db->from('tax_categories');
		$this->db->where('tax_category', $tax_category);

		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			return $query->row()->tax_category_id;
		}
		return NULL;
	}

}
?>