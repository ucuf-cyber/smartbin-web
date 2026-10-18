<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Autoload sesuai kebutuhan SmartBin IoT.
| session   -> untuk autentikasi admin
| database  -> load otomatis via $autoload['libraries']
*/

$autoload['packages'] = array();
$autoload['libraries'] = array('database', 'session', 'form_validation');
$autoload['drivers'] = array();
$autoload['helper'] = array('url', 'form', 'security');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array();
