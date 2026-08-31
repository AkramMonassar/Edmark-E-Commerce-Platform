<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/connection/connection.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: Login.php");
    exit;
}

$uid = (int)$_SESSION['u_id'];

$stmt = mysqli_prepare($con_db, "SELECT u_type FROM users WHERE u_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$me = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (($me['u_type'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

$backupDir = __DIR__ . '/backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
$filepath = $backupDir . '/' . $filename;

$output  = "-- Database Backup\n";
$output .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

$tables = [];
$resTables = mysqli_query($con_db, "SHOW TABLES");

while ($t = mysqli_fetch_array($resTables)) {
    $tables[] = $t[0];
}

foreach ($tables as $table) {
    $output .= "\n-- =============================\n";
    $output .= "-- Table: `$table`\n";
    $output .= "-- =============================\n\n";

    $createRes = mysqli_query($con_db, "SHOW CREATE TABLE `$table`");
    if ($createRes) {
        $createRow = mysqli_fetch_assoc($createRes);
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= $createRow['Create Table'] . ";\n\n";
    }

    $dataRes = mysqli_query($con_db, "SELECT * FROM `$table`");

    if ($dataRes && mysqli_num_rows($dataRes) > 0) {
        while ($row = mysqli_fetch_assoc($dataRes)) {
            $cols = array_map(fn($c) => "`$c`", array_keys($row));
            $vals = [];

            foreach ($row as $val) {
                if ($val === null) {
                    $vals[] = "NULL";
                } else {
                    $vals[] = "'" . mysqli_real_escape_string($con_db, $val) . "'";
                }
            }

            $output .= "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        $output .= "\n";
    }
}

$output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents($filepath, $output);

header("Location: admin.php?tab=backup&backup_done=1");
exit;