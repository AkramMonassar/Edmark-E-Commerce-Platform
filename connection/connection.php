<?php
// ===== إعدادات الاتصال =====
if (!defined('LOCALHOST')) {
    define('LOCALHOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'database');
    define('DB_CHARSET', 'utf8');
}

// نفس أسلوب الكود القديم: أخطاء ترجع false بدل Exceptions (تصرف PHP 8 الافتراضي اختلف)
mysqli_report(MYSQLI_REPORT_OFF);

// السطر اللي كان يكسر الموقع: بدون علامات اقتباس
error_reporting(E_ALL & ~E_NOTICE);

if (!isset($con_db)) {
    $con_db = mysqli_connect(LOCALHOST, DB_USER, DB_PASS, DB_NAME);

    if (!$con_db) {
        echo "error: " . mysqli_connect_error();
        exit();
    }

    mysqli_set_charset($con_db, DB_CHARSET);
}
?>