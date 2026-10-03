<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

abstract class Persons extends Secure_Controller
{
	public function __construct($module_id = NULL)
	{
		parent::__construct($module_id);
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_people_manage_table_headers());

		$this->load->view('people/manage', $data);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Person->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}

	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_person_data_row($this->Person->get_info($row_id)));

		echo json_encode($data_row);
	}

/*
Load email modal for sending email to a person
*/
public function email_modal($person_id = NULL)
{
	$email = $this->input->get('email');
	$name = $this->input->get('name');
	$person = NULL;

	if (!empty($person_id) && $person_id !== -1) {
		$person = $this->Person->get_info($person_id);
		if (empty($person->email)) {
			if (empty($email)) {
				show_404();
				return;
			}
		}
	}

	if (empty($email) && !empty($person)) {
		$email = $person->email;
	}

	$subject = $this->input->get('subject');
	$message = $this->input->get('message');

	$this->load->view('people/email_modal', array(
		'person' => $person,
		'person_id' => $person_id,
		'email' => $email,
		'name' => $name,
		'subject' => $subject,
		'message' => $message
	));
}

	/*
	Send email to a person
	*/
	public function send_email_modal()
	{
		$person_id = $this->input->post('person_id');
		$to = $this->input->post('to');
		$cc = $this->input->post('cc');
		$bcc = $this->input->post('bcc');
		$subject = $this->input->post('subject');
		$message = $this->input->post('message');

		$result = FALSE;
		$message_text = $this->lang->line('sales_email_error');

		if (!empty($to) && !empty($subject) && !empty($message)) {
			$attachments = array();

			if (!empty($_FILES['attachments']['name'][0])) {
				$upload_path = sys_get_temp_dir() . '/email_attachments/';
				if (!is_dir($upload_path)) {
					mkdir($upload_path, 0755, TRUE);
				}

				for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
					if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
						$filename = $upload_path . basename($_FILES['attachments']['name'][$i]);
						if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $filename)) {
							$attachments[] = $filename;
						}
					}
				}
			}

			$this->load->library('email_lib');
			$result = $this->email_lib->sendEmail($to, $subject, $message, $attachments, $cc, $bcc);

			$message_text = $this->lang->line($result ? 'sales_email_sent' : 'sales_email_unsent') . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message_text));
	}

	/*
	Capitalize segments of a name, and put the rest into lower case.
	You can pass the characters you want to use as delimiters as exceptions.
	The function supports UTF-8 string.

	Example:
		i.e. <?php echo nameize("john o'grady-smith"); ?>

		returns John O'Grady-Smith
	*/

	protected function nameize($string)
	{
		return str_name_case($string);
	}
}
?>
