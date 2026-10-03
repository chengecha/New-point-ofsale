<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mpesa_lib {

    protected $CI;
    protected $env;
    protected $consumer_key;
    protected $consumer_secret;
    protected $shortcode;
    protected $passkey;
    protected $callback_url;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('mpesa', TRUE);

        $this->env             = $this->CI->config->item('mpesa_env', 'mpesa');
        $this->consumer_key    = $this->CI->config->item('mpesa_consumer_key', 'mpesa');
        $this->consumer_secret = $this->CI->config->item('mpesa_consumer_secret', 'mpesa');
        $this->shortcode       = $this->CI->config->item('mpesa_shortcode', 'mpesa');
        $this->passkey         = $this->CI->config->item('mpesa_passkey', 'mpesa');
        $this->callback_url    = $this->CI->config->item('mpesa_callback_url', 'mpesa');
    }

    /**
     * Generate Safaricom Daraja OAuth Access Token
     */
    public function generate_access_token() {
        $url = ($this->env == 'production') 
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' 
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($curl_response);
        return isset($result->access_token) ? $result->access_token : false;
    }

    /**
     * Send STK Push (Lipa na M-Pesa Online) Request
     * 
     * @param string $phone Customer phone number (Format: 2547XXXXXXXX)
     * @param float $amount Amount to charge
     * @param string $account_reference Reference text (e.g., OSPOS Sale ID or Receipt)
     * @param string $transaction_desc Description of the transaction
     * @return array Response from Safaricom API
     */
    public function stk_push($phone, $amount, $account_reference = 'OSPOS Sale', $transaction_desc = 'Payment for goods') {
        $access_token = $this->generate_access_token();

        if (!$access_token) {
            return array('status' => false, 'message' => 'Failed to generate M-Pesa access token.');
        }

        $url = ($this->env == 'production')
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        // Format phone number to ensure it starts with 254
        $phone = preg_replace('/^\+/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        $curl_post_data = array(
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'CheckoutRequestID' => 'CustomerPayBillOnline', // Correct type for till/paybill
            'Amount'            => round($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->callback_url,
            'AccountReference'  => $account_reference,
            'TransactionDesc'   => $transaction_desc
        );

        $data_string = json_encode($curl_post_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return array('status' => false, 'message' => 'CURL Error: ' . $curl_error);
        }

        $res = json_decode($curl_response);

        if (isset($res->ResponseCode) && $res->ResponseCode == "0") {
            return array(
                'status' => true,
                'checkout_request_id' => $res->CheckoutRequestID,
                'merchant_request_id' => $res->MerchantRequestID,
                'message' => 'STK push sent successfully. Enter PIN on phone.'
            );
        } else {
            $err_msg = isset($res->errorMessage) ? $res->errorMessage : (isset($res->ResponseDescription) ? $res->ResponseDescription : 'Unknown error');
            return array('status' => false, 'message' => $err_msg);
        }
    }

    /**
     * Query the payment status of an STK Push request.
     *
     * Safaricom's Daraja API provides a STK Push Query endpoint that
     * allows the merchant to check the status of a transaction.
     *
     * @param string $checkout_request_id The CheckoutRequestID from the STK Push response
     * @return array Response containing status information
     */
    public function query_payment_status($checkout_request_id) {
        $access_token = $this->generate_access_token();

        if (!$access_token) {
            return array('status' => false, 'message' => 'Failed to generate M-Pesa access token.');
        }

        $url = ($this->env == 'production')
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/query';

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $curl_post_data = array(
            'BusinessShortCode'    => $this->shortcode,
            'Password'             => $password,
            'Timestamp'            => $timestamp,
            'CheckoutRequestID'    => $checkout_request_id
        );

        $data_string = json_encode($curl_post_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return array('status' => false, 'message' => 'CURL Error: ' . $curl_error);
        }

        $res = json_decode($curl_response);

        if (isset($res->ResponseCode) && $res->ResponseCode == "0") {
            $result_code = isset($res->ResultCode) ? $res->ResultCode : -1;
            $result_desc = isset($res->ResultDesc) ? $res->ResultDesc : '';

            return array(
                'status'            => true,
                'checkout_request_id' => $checkout_request_id,
                'result_code'       => $result_code,
                'result_desc'       => $result_desc,
                'amount'            => isset($res->Amount) ? $res->Amount : 0,
                'receipt_no'        => isset($res->ReceiptNo) ? $res->ReceiptNo : '',
                'message'           => $result_desc,
                'paid'              => ($result_code == 0)
            );
        } else {
            $err_msg = isset($res->errorMessage) ? $res->errorMessage : (isset($res->ResponseDescription) ? $res->ResponseDescription : 'Unknown error');
            return array('status' => false, 'message' => $err_msg);
        }
    }
}