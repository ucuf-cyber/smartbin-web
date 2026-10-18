<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  File ini SUDAH LENGKAP (semua key resmi CodeIgniter 3.1.x).
|  Tinggal TIMPA langsung application/config/config.php Anda dengan
|  file ini. Tidak perlu digabung manual lagi.
| -------------------------------------------------------------------
*/

// Deteksi otomatis dari domain/host yang benar-benar dipakai browser
// (bukan auto-detect bawaan CI3 yang memakai SERVER_ADDR / IP server —
// itu mengabaikan domain custom seperti *.test dari Laragon). Dengan ini
// base_url tetap benar baik di Laragon (http://smartbin-web.test/) maupun
// XAMPP (http://localhost/smartbin-web/) tanpa perlu diedit tiap pindah laptop.
$config['base_url'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '/') . '/';
$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array'] = TRUE;

$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';

$config['log_threshold'] = 1;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';

$config['error_views_path'] = '';

$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;

$config['encryption_key'] = 'GANTI_DENGAN_STRING_ACAK_32_KARAKTER';

$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'smartbin_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = NULL;
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;

$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = FALSE;
$config['cookie_httponly'] = TRUE;

$config['standardize_newlines'] = FALSE;

$config['global_xss_filtering'] = FALSE;

$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
$config['csrf_exclude_uris'] = array('api/send_data', 'api/latest', 'api/history', 'api/summary');

$config['compress_output'] = FALSE;

$config['time_reference'] = 'local';

$config['rewrite_short_tags'] = FALSE;

$config['proxy_ips'] = '';

$config['multipart_form_data'] = FALSE;
