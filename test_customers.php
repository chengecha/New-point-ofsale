<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the environment
define('ENVIRONMENT', 'development');

// Change to the public directory
chdir('/home/felix/Projects/NEWOPEN/opensourcepos-master/opensourcepos-master/public');

// Include the index.php file
require_once 'index.php';
?>