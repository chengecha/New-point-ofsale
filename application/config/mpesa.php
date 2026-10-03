<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Set to 'sandbox' for testing, or 'production' for live sales
$config['mpesa_env']            = 'sandbox'; 

// Paste your keys from the Daraja portal here
$config['mpesa_consumer_key']     = 'YOUR_CONSUMER_KEY_HERE';
$config['mpesa_consumer_secret']  = 'YOUR_CONSUMER_SECRET_HERE';

// Your Business Shortcode (Paybill or Buy Goods Till Number)
$config['mpesa_shortcode']        = '174379'; // Sandbox default paybill

// The Lipa na M-Pesa Online Passkey provided by Safaricom
$config['mpesa_passkey']          = 'YOUR_PASSKEY_HERE'; 

// Your public callback URL (use ngrok for local testing)
$config['mpesa_callback_url']     = 'https://yourdomain.com/index.php/sales/mpesa_callback';