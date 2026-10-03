<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * M-Pesa Transaction class
 *
 * Tracks STK Push callback results and offline M-Pesa payments
 * from Safaricom Daraja API.
 */
class Mpesa_transaction extends CI_Model
{
	const STATUS_PENDING = 0;
	const STATUS_COMPLETED = 1;
	const STATUS_FAILED = 2;

	/**
	 * Save STK Push callback data from Safaricom.
	 * Updates existing record if found, otherwise inserts a new one.
	 */
	public function save_callback_result($checkout_request_id, $merchant_request_id, $amount, $transaction_id, $receipt_no, $success, $sale_id = NULL)
	{
		$existing = $this->get_by_checkout_request_id($checkout_request_id);

		$data = array(
			'checkout_request_id'   => $checkout_request_id,
			'merchant_request_id'   => $merchant_request_id,
			'amount'                => $amount,
			'transaction_id'        => $transaction_id,
			'receipt_no'            => $receipt_no,
			'success'               => $success ? 1 : 0,
			'status'                => $success ? self::STATUS_COMPLETED : self::STATUS_FAILED,
			'created_at'            => date('Y-m-d H:i:s')
		);

		if($sale_id !== NULL)
		{
			$data['sale_id'] = $sale_id;
		}

		if($existing && $existing->num_rows() > 0)
		{
			$this->db->where('checkout_request_id', $checkout_request_id);
			$this->db->order_by('created_at', 'DESC');
			$this->db->limit(1);
			return $this->db->update('mpesa_transactions', $data);
		}
		else
		{
			return $this->db->insert('mpesa_transactions', $data);
		}
	}

	/**
	 * Save STK Push request data and associate with a sale
	 */
	public function save_stk_push($checkout_request_id, $merchant_request_id, $amount, $phone_number, $sale_id)
	{
		$data = array(
			'checkout_request_id' => $checkout_request_id,
			'merchant_request_id' => $merchant_request_id,
			'amount'              => $amount,
			'phone_number'        => $phone_number,
			'sale_id'             => $sale_id,
			'success'             => 0,
			'status'              => self::STATUS_PENDING,
			'created_at'          => date('Y-m-d H:i:s')
		);

		return $this->db->insert('mpesa_transactions', $data);
	}

	/**
	 * Save an offline M-Pesa payment with a manual transaction code
	 */
	public function save_offline_payment($manual_code, $amount, $phone_number, $sale_id)
	{
		$data = array(
			'checkout_request_id' => uniqid('OFFLINE_'),
			'merchant_request_id' => '0',
			'amount'              => $amount,
			'manual_code'         => $manual_code,
			'phone_number'        => $phone_number,
			'sale_id'             => $sale_id,
			'transaction_id'      => $manual_code,
			'receipt_no'          => $manual_code,
			'success'             => 1,
			'status'              => self::STATUS_COMPLETED,
			'created_at'          => date('Y-m-d H:i:s')
		);

		return $this->db->insert('mpesa_transactions', $data);
	}

	/**
	 * Get transaction by checkout request ID
	 */
	public function get_by_checkout_request_id($checkout_request_id)
	{
		$this->db->from('mpesa_transactions');
		$this->db->where('checkout_request_id', $checkout_request_id);
		$this->db->order_by('created_at', 'DESC');

		return $this->db->get();
	}

	/**
	 * Get transaction by manual code (offline M-Pesa receipt)
	 */
	public function get_by_manual_code($manual_code)
	{
		$this->db->from('mpesa_transactions');
		$this->db->where('manual_code', $manual_code);
		$this->db->order_by('created_at', 'DESC');

		return $this->db->get();
	}

	/**
	 * Get transaction by sale ID
	 */
	public function get_by_sale_id($sale_id)
	{
		$this->db->from('mpesa_transactions');
		$this->db->where('sale_id', $sale_id);
		$this->db->order_by('created_at', 'DESC');

		return $this->db->get();
	}

	/**
	 * Check the current payment status for a given checkout request ID
	 */
	public function get_payment_status($checkout_request_id)
	{
		$this->db->from('mpesa_transactions');
		$this->db->where('checkout_request_id', $checkout_request_id);
		$this->db->order_by('created_at', 'DESC');

		$query = $this->db->get();

		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array(
				'success'    => (bool)$row->success,
				'status'     => (int)$row->status,
				'amount'     => $row->amount,
				'transaction_id' => $row->transaction_id,
				'receipt_no'     => $row->receipt_no,
				'manual_code'    => $row->manual_code,
				'phone_number'   => $row->phone_number,
				'created_at'     => $row->created_at
			);
		}

		return FALSE;
	}

	/**
	 * Update transaction status after verification
	 */
	public function update_status($checkout_request_id, $success, $transaction_id = '', $receipt_no = '')
	{
		$data = array(
			'success'      => $success ? 1 : 0,
			'status'       => $success ? self::STATUS_COMPLETED : self::STATUS_FAILED,
			'transaction_id' => $transaction_id,
			'receipt_no'     => $receipt_no
		);

		$this->db->where('checkout_request_id', $checkout_request_id);

		return $this->db->update('mpesa_transactions', $data);
	}

	/**
	 * Update the checkout request ID for an offline transaction to link callback
	 */
	public function link_checkout_request($transaction_id, $checkout_request_id)
	{
		$this->db->where('id', $transaction_id);

		return $this->db->update('mpesa_transactions', array(
			'checkout_request_id' => $checkout_request_id
		));
	}
}
?>
