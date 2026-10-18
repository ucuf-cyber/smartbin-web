<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Halaman
$route['login']            = 'Login/index';
$route['login/process']    = 'Login/process';
$route['logout']           = 'Login/logout';
$route['dashboard']        = 'Dashboard/index';
$route['monitoring']       = 'Monitoring/index';
$route['history']          = 'History/index';
$route['settings']         = 'Settings/index';
$route['settings/save']    = 'Settings/save';

// REST API untuk NodeMCU & AJAX polling front-end
$route['api/send_data']    = 'Api/send_data';
$route['api/latest']       = 'Api/latest';
$route['api/history']      = 'Api/history';
$route['api/summary']      = 'Api/summary';
