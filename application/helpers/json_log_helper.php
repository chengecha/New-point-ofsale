<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function json_log($event, $data = [])
{
    $CI =& get_instance();

    $sensitive_fields = ['password', 'password2', 'password_confirm', 'csrf_fluxwave_v3', 'csrf_token'];

    $redact = function ($value, $key) use (&$redact, $sensitive_fields) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $redact($v, $k);
            }
            return $out;
        }
        if (in_array(strtolower((string) $key), $sensitive_fields, true)) {
            return '[REDACTED]';
        }
        return $value;
    };

    $post = $CI->input->post();
    $get = $CI->input->get();
    $clean_post = is_array($post) ? $redact($post, '') : $post;
    $clean_get = is_array($get) ? $redact($get, '') : $get;

    $log = [
        'timestamp' => date('c'),
        'user_id' => $CI->session->userdata('person_id'),
        'user_name' => $CI->session->userdata('username'),
        'request' => [
            'uri' => $CI->input->server('REQUEST_URI'),
            'method' => $CI->input->method(),
            'ip' => $CI->input->ip_address(),
            'post' => $clean_post,
            'get' => $clean_get,
        ],
        'event' => $event,
        'data' => $data,
    ];
    $logPath = ($CI->config->item('log_path') ?: APPPATH . 'logs/json/') . date('Y-m-d') . '.log';
    $dir = dirname($logPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($logPath, json_encode($log, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
}
?>
