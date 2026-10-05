<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_cashup_nullable_close_employee extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating cashup_nullable_close_employee');

		execute_script(APPPATH . 'migrations/sqlscripts/3.3.8_cashup_nullable_close_employee.sql');

		error_log('Migrated cashup_nullable_close_employee');
	}

	public function down()
	{
	}
}
?>
