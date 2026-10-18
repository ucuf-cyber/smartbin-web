<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Server PHP defaultnya bukan WIB (kadang ke-set ke timezone lain sesuai
// konfigurasi hosting), jadi dipaksa ke Asia/Jakarta biar semua date()/timestamp
// di aplikasi (termasuk created_at yang ditulis manual di Sensor_model) konsisten
// dengan jam Indonesia Barat.
date_default_timezone_set('Asia/Jakarta');

/*
| -------------------------------------------------------------------
|  File ini WAJIB ada — CodeIgniter 3 me-require-nya langsung di
|  system/core/CodeIgniter.php sebelum konstanta VIEWPATH didefinisikan.
|  Kalau file ini hilang, PHP fatal duluan dan menyebabkan error
|  "Undefined constant VIEWPATH" yang membingungkan (itu cuma efek
|  samping, bukan error aslinya).
| -------------------------------------------------------------------
*/

// Kode HTTP status umum
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0);
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1);
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3);
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4);
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5);
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6);
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7);
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8);
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9);
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125);

/*
| -------------------------------------------------------------------
|  Konstanta khusus proyek SmartBin IoT (opsional, bisa dipakai
|  di controller/model sebagai pengganti angka mentah)
| -------------------------------------------------------------------
*/
defined('SMARTBIN_WASTE_LOGAM')     OR define('SMARTBIN_WASTE_LOGAM', 'Logam');
defined('SMARTBIN_WASTE_NONLOGAM')  OR define('SMARTBIN_WASTE_NONLOGAM', 'Non-Logam');
defined('SMARTBIN_WASTE_UNKNOWN')   OR define('SMARTBIN_WASTE_UNKNOWN', 'Unknown');
defined('SMARTBIN_STATUS_ONLINE')   OR define('SMARTBIN_STATUS_ONLINE', 'Online');
defined('SMARTBIN_STATUS_OFFLINE')  OR define('SMARTBIN_STATUS_OFFLINE', 'Offline');
