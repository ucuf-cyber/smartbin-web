<?php
/*
 * =====================================================================
 * SmartBin IoT — Front Controller (index.php)
 * =====================================================================
 * File ini adalah SALINAN dari index.php BAWAAN CodeIgniter 3.1.x.
 * Isinya standar framework — TIDAK perlu diubah kecuali baris
 * $system_path dan $application_folder di bawah jika struktur folder
 * Anda berbeda dari default.
 *
 * Cara pakai:
 *  1. Unduh core CodeIgniter 3.1.x (folder "system/" resmi dari
 *     https://github.com/bcit-ci/CodeIgniter, branch 3.1-stable).
 *  2. Taruh folder "system/" itu SEJAJAR dengan file index.php ini
 *     (di root proyek "smartbin-web/").
 *  3. Taruh folder "application/" dan "assets/" dari paket ini juga
 *     sejajar dengan index.php (root "smartbin-web/").
 *  4. File index.php ini TIDAK PERLU diganti dari versi resmi CI3 —
 *     cukup pastikan konstanta di bawah menunjuk ke folder yang benar.
 * =====================================================================
 */

// PENTING: SET SEBELUM PRODUKSI. 'development' menampilkan error detail,
// 'production' menyembunyikan error demi keamanan.
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

switch (ENVIRONMENT) {
    case 'development':
        // E_DEPRECATED dikecualikan: CodeIgniter 3 core (system/) memakai
        // dynamic property yang deprecated di PHP 8.2+. Ini bukan bug di
        // kode proyek ini, tapi noise-nya bisa membuat output terkirim
        // sebelum header (session_start/redirect), menyebabkan error
        // "headers already sent" saat login.
        error_reporting(E_ALL & ~E_DEPRECATED);
        ini_set('display_errors', 1);
        break;
    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;
    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 * Folder core CodeIgniter 3 (diunduh terpisah, lihat catatan di atas).
 */
$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 * Folder "application/" dari paket SmartBin ini.
 */
$application_folder = 'application';

/*
 * --------------------------------------------------------------------
 * DO NOT CHANGE ANYTHING BELOW THIS LINE, KECUALI ANDA PAHAM RISIKONYA
 * (Ini adalah bootstrap resmi CodeIgniter 3 — sama seperti versi asli)
 * --------------------------------------------------------------------
 */
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (($_temp = realpath($system_path)) !== FALSE) {
    $system_path = $_temp . DIRECTORY_SEPARATOR;
} else {
    $system_path = strtr(
        rtrim($system_path, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    ) . DIRECTORY_SEPARATOR;
}

if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Folder "system" CodeIgniter 3 tidak ditemukan. Unduh core CodeIgniter 3.1.x '
        . 'dan letakkan folder "system" sejajar dengan index.php ini. Lihat komentar di atas file ini.';
    exit(3);
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_path);
define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('SYSDIR', basename(BASEPATH));

if (is_dir($application_folder)) {
    define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);
} else {
    if (!is_dir(BASEPATH . $application_folder . DIRECTORY_SEPARATOR)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Folder "application" tidak ditemukan.';
        exit(3);
    }
    define('APPPATH', BASEPATH . $application_folder . DIRECTORY_SEPARATOR);
}

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 * WAJIB ADA — ini bagian yang sebelumnya kelewat sehingga menyebabkan
 * error "Undefined constant VIEWPATH". Kosongkan $view_folder untuk
 * memakai lokasi default (application/views).
 */
$view_folder = '';

if (!isset($view_folder[0]) && is_dir(APPPATH . 'views' . DIRECTORY_SEPARATOR)) {
    $view_folder = APPPATH . 'views';
} elseif (is_dir($view_folder)) {
    if (($_temp = realpath($view_folder)) !== FALSE) {
        $view_folder = $_temp;
    } else {
        $view_folder = strtr(
            rtrim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    }
} elseif (is_dir(APPPATH . $view_folder . DIRECTORY_SEPARATOR)) {
    $view_folder = APPPATH . strtr(
        trim($view_folder, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    );
} else {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Folder "views" tidak ditemukan. Periksa struktur folder application/views.';
    exit(3);
}

define('VIEWPATH', $view_folder . DIRECTORY_SEPARATOR);

require_once BASEPATH . 'core/CodeIgniter.php';
