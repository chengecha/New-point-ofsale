<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Office extends Secure_Controller
{
	function __construct()
	{
		parent::__construct('office', NULL, 'office');

		$this->load->model('Employee');
	}

	public function index()
	{
		$person_id = $this->session->userdata('person_id');

		$data['active_staff_count'] = $this->get_active_staff_count();
		$data['branch_count'] = $this->Stock_location->get_all()->num_rows();
		$data['total_employees'] = $this->Employee->get_total_rows();
		$data['last_backup'] = $this->get_last_backup_info();
		$data['backup_files'] = $this->get_backup_files(3);
		$data['recent_logs'] = $this->get_recent_logs(5);
		$data['can_trigger_backup'] = $this->Employee->has_module_grant('config', $person_id);
		$data['can_add_employee'] = $this->Employee->has_module_grant('employees', $person_id);

		$this->load->view('home/office', $data);
	}

	private function get_active_staff_count()
	{
		$session_path = $this->config->item('sess_save_path') ?: APPPATH . 'sessions/';
		$count = 0;
		$now = time();
		$session_expiration = (int) $this->config->item('sess_expiration');
		if ($session_expiration <= 0) {
			$session_expiration = 7200;
		}

		if (is_dir($session_path))
		{
			foreach (glob($session_path . 'fluxwave_session*') as $session_file)
			{
				if (filemtime($session_file) > ($now - $session_expiration))
				{
					$count++;
				}
			}
		}

		return $count;
	}

	private function get_last_backup_info()
	{
		$uploads_path = FCPATH . 'uploads/';
		$files = glob($uploads_path . 'fluxwave-*.zip');

		if (!empty($files))
		{
			$latest = max($files);
			$mtime = filemtime($latest);
			return date($this->config->item('timeformat'), $mtime) . ' · ' . date($this->config->item('dateformat'), $mtime);
		}

		return $this->lang->line('config_no_backups') ?: 'No backups found';
	}

	private function get_backup_files($limit = 3)
	{
		$uploads_path = FCPATH . 'uploads/';
		$backups = array();

		$files = glob($uploads_path . 'fluxwave-*.zip');
		if (!empty($files))
		{
			usort($files, function($a, $b)
			{
				return filemtime($b) - filemtime($a);
			});

			$files = array_slice($files, 0, $limit);
			foreach ($files as $file)
			{
				$backups[] = array(
					'filename' => basename($file),
					'datetime' => date($this->config->item('timeformat'), filemtime($file)),
					'date' => date($this->config->item('dateformat'), filemtime($file)),
					'size' => round(filesize($file) / 1024 / 1024, 2) . ' MB',
					'download_url' => site_url('office/download_backup/' . basename($file))
				);
			}
		}

		return $backups;
	}

	private function get_recent_logs($limit = 5)
	{
		$log_dir = ($this->config->item('log_path') ?: APPPATH . 'logs/json/');
		$logs = array();

		$files = glob($log_dir . '*.log');
		if (!empty($files))
		{
			rsort($files);

			foreach ($files as $file)
			{
				$lines = array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
				foreach ($lines as $line)
				{
					if (empty($line))
					{
						continue;
					}

					$entry = json_decode($line, true);
					if ($entry)
					{
						$logs[] = array(
							'timestamp' => $entry['timestamp'] ?? '',
							'time' => isset($entry['timestamp']) ? date($this->config->item('timeformat'), strtotime($entry['timestamp'])) : '',
							'user_name' => $entry['user_name'] ?? '',
							'user_id' => $entry['user_id'] ?? '',
							'event' => $entry['event'] ?? '',
							'ip' => $entry['request']['ip'] ?? '',
							'uri' => $entry['request']['uri'] ?? ''
						);

						if (count($logs) >= $limit)
						{
							return array_reverse($logs);
						}
					}
				}

				if (count($logs) >= $limit)
				{
					break;
				}
			}
		}

		return array_reverse($logs);
	}

	public function trigger_backup()
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if ($this->Employee->has_module_grant('config', $employee_id))
		{
			$this->load->dbutil();

			$prefs = array(
				'format' => 'zip',
				'filename' => 'fluxwave.sql'
			);

			$backup = $this->dbutil->backup($prefs);

			$file_name = 'fluxwave-' . date('Y-m-d-H-i-s') . '.zip';
			$save_path = FCPATH . 'uploads/' . $file_name;

			file_put_contents($save_path, $backup);

			json_log('database_backup_triggered', array('filename' => $file_name));

			echo json_encode(array(
				'success' => TRUE,
				'message' => $this->lang->line('config_backup_complete') ?: 'Backup created successfully',
				'file_name' => $file_name
			));
		}
		else
		{
			echo json_encode(array(
				'success' => FALSE,
				'message' => $this->lang->line('common_no_access') ?: 'Permission denied'
			));
		}
	}

	public function download_backup($filename)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if ($this->Employee->has_module_grant('config', $employee_id))
		{
			$file_path = FCPATH . 'uploads/' . $filename;

			if (is_file($file_path) && preg_match('/^fluxwave-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.zip$/', $filename))
			{
				$this->load->helper('download');
				force_download($filename, file_get_contents($file_path));
			}
			else
			{
				redirect('office');
			}
		}
		else
		{
			redirect('no_access/config');
		}
	}

	public function logout()
	{
		$this->Employee->logout();
	}
}
