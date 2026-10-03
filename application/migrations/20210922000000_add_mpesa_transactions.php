<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_add_mpesa_transactions extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating add_mpesa_transactions');

		execute_script(APPPATH . 'migrations/sqlscripts/3.3.5_add_mpesa_transactions.sql');

		error_log('Migrated add_mpesa_transactions');
	}

	public function down()
	{
	}
}
?>
