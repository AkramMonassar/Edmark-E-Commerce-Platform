<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../include/csrf.php';
require_once __DIR__ . '/../connection/connection.php';

header('Content-Type: application/json; charset=utf-8');
function respond($ok, $data = []) { echo json_encode(array_merge(['ok' => $ok], $data)); exit; }

if (!isset($_SESSION['u_id'])) respond(false, ['error' => 'login']);
$uid = (int) $_SESSION['u_id'];
$st = mysqli_prepare($con_db, "SELECT u_type FROM users WHERE u_id = ?");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$me = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (($me['u_type'] ?? '') !== 'admin') respond(false, ['error' => 'admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, ['error' => 'method']);

$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) respond(false, ['error' => 'csrf']);

$action = $_POST['action'] ?? '';
$esc = fn($v) => mysqli_real_escape_string($con_db, $v);

if ($action === 'save_product') {
    $pid = (int)($_POST['p_id'] ?? 0);
    $name = $esc(trim($_POST['p_name'] ?? ''));
    $price = (int)($_POST['p_price'] ?? 0);
    $qty = (int)($_POST['p_quantity'] ?? 0);
    $desc = $esc(trim($_POST['p_describe'] ?? ''));
    $cat = (int)($_POST['category_id'] ?? 0);
    $catSql = $cat > 0 ? $cat : 'NULL';
    if ($name === '' || $price <= 0) respond(false, ['error' => 'fields']);
    mysqli_query($con_db, "UPDATE product SET p_name='$name', p_price=$price, p_quantity=$qty, p_describe='$desc', category_id=$catSql WHERE p_id=$pid");
    respond(true, ['message' => "تم حفظ المنتج #$pid ✅"]);
}

if ($action === 'save_products') {
    $rows = json_decode($_POST['rows'] ?? '[]', true);
    if (!is_array($rows)) $rows = [];
    $count = 0; $skip = 0;
    foreach ($rows as $r) {
        $pid   = (int) ($r['p_id'] ?? 0);
        $name  = $esc(trim($r['p_name'] ?? ''));
        $price = (int) ($r['p_price'] ?? 0);
        $qty   = (int) ($r['p_quantity'] ?? 0);
        $desc  = $esc(trim($r['p_describe'] ?? ''));
        $cat   = (int) ($r['category_id'] ?? 0);
        $catSql = $cat > 0 ? $cat : 'NULL';
        if ($pid > 0 && $name !== '' && $price > 0) {
            mysqli_query($con_db, "UPDATE product SET p_name='$name', p_price=$price, p_quantity=$qty, p_describe='$desc', category_id=$catSql WHERE p_id=$pid");
            $count++;
        } else {
            $skip++;
        }
    }
    respond(true, ['message' => "وصل: " . count($rows) . " صف | حُفظ: $count | تُخطي: $skip"]);
}

if ($action === 'toggle_user') {
    $tid = (int)($_POST['user_id'] ?? 0);
    if ($tid === $uid) respond(false, ['error' => 'self']);
    mysqli_query($con_db, "UPDATE users SET u_type = IF(u_type='admin','user','admin') WHERE u_id = $tid");
    $r = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT u_type FROM users WHERE u_id = $tid"));
    respond(true, ['type' => $r['u_type'] ?? 'user']);
}

if ($action === 'delete_user') {
    $tid = (int)($_POST['user_id'] ?? 0);
    if ($tid === $uid) respond(false, ['error' => 'self']);
    mysqli_query($con_db, "DELETE FROM users WHERE u_id = $tid AND u_type != 'admin'");
    respond(mysqli_affected_rows($con_db) > 0);
}

if ($action === 'toggle_coupon') {
    $cid = (int)($_POST['coupon_id'] ?? 0);
    mysqli_query($con_db, "UPDATE coupons SET active = 1 - active WHERE coupon_id = $cid");
    $r = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT active FROM coupons WHERE coupon_id = $cid"));
    respond(true, ['active' => (int) $r['active']]);
}

if ($action === 'delete_coupon') {
    $cid = (int)($_POST['coupon_id'] ?? 0);
    mysqli_query($con_db, "DELETE FROM coupons WHERE coupon_id = $cid");
    respond(true);
}

respond(false, ['error' => 'unknown']);