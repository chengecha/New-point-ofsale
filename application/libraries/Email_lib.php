<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Email library
 *
 * Library with utilities to configure and send emails
 */

class Email_lib
{
	private $CI;

   	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->library('email');

		$this->_load_config();
	}

	private function _load_config()
	{
		$smtp_pass = $this->CI->config->item('smtp_pass');

		$config = array(
			'mailtype' => 'html',
			'useragent' => 'Fluxwave POS',
			'validate' => TRUE,
			'protocol' => $this->CI->config->item('protocol'),
			'mailpath' => $this->CI->config->item('mailpath'),
			'smtp_host' => $this->CI->config->item('smtp_host'),
			'smtp_user' => $this->CI->config->item('smtp_user'),
			'smtp_pass' => ($smtp_pass !== '' && $smtp_pass !== NULL)
				? $this->CI->encryption->decrypt($smtp_pass)
				: '',
			'smtp_port' => $this->CI->config->item('smtp_port'),
			'smtp_timeout' => $this->CI->config->item('smtp_timeout'),
			'smtp_crypto' => $this->CI->config->item('smtp_crypto')
		);

		$this->CI->email->initialize($config);
	}

	/**
	 * Email sending function
	 * Example of use: $response = sendEmail('john@doe.com', 'Hello', 'This is a message', $filename);
	 */
	public function sendEmail($to, $subject, $message, $attachments = NULL, $cc = NULL, $bcc = NULL, $reply_to = NULL)
	{
		$this->_load_config();

		$email = $this->CI->email;

		$email->from($this->CI->config->item('email'), $this->CI->config->item('company'));
		
		if (!empty($reply_to)) {
			$email->reply_to($reply_to);
		}
		
		$email->to($to);
		
		if (!empty($cc)) {
			$email->cc($cc);
		}
		
		if (!empty($bcc)) {
			$email->bcc($bcc);
		}
		
		$email->subject($subject);
		$email->message($message);
		
		if (!empty($attachments)) {
			if (is_array($attachments)) {
				foreach ($attachments as $attachment) {
					if (!empty($attachment)) {
						$email->attach($attachment);
					}
				}
			} else {
				$email->attach($attachments);
			}
		}

		$result = $email->send();

		if (!$result)
		{
			log_message('error', 'Email send failed to ' . $to . ': ' . $email->print_debugger());
		}
		else
		{
			log_message('info', 'Email sent successfully to ' . $to . ' with subject: ' . $subject);
		}

		return $result;
	}
}

?>