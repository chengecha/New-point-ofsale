<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the environment to development
$_SERVER['CI_ENV'] = 'development';

echo "Starting test...<br>";

// Change to the CodeIgniter root
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$system_path = '/home/felix/Projects/NEWOPEN/opensourcepos-master/opensourcepos-master/system';
$application_folder = '/home/felix/Projects/NEWOPEN/opensourcepos-master/opensourcepos-master/application';

// Load CodeIgniter
require_once $system_path . '/core/CodeIgniter.php';
?>