<?php
// ===== API مصغر: يكلم JavaScript بلغة JSON بدون تحميل صفحات =====
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../include/csrf.php';
require_once __DIR__ . '/../connection/connection.php';

header('Content-Type: application/json; charset=utf-8');

function respond($ok, $data = [])
{
    echo json_encode(array_merge(['ok' => $ok], $data));
    exit;
}

function cartStatus($con_db, $uid)
{
    $res = mysqli_query($con_db, "SELECT SUM(c_total) AS t, COUNT(id) AS c FROM cart WHERE u_id = $uid");
    $r = $res ? mysqli_fetch_assoc($res) : null;
    return ['count' => (int)($r['c'] ?? 0), 'total' => (int)($r['t'] ?? 0)];
}

if (!isset($_SESSION['u_id'])) respond(false, ['error' => 'login_required']);
$uid = (int) $_SESSION['u_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// أي عملية تعديل (POST) لازم تتحقق من CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        respond(false, ['error' => 'csrf']);
    }
}

if ($action === 'status') {
    respond(true, cartStatus($con_db, $uid));
}

if ($action === 'add') {
    $id = (int)($_POST['id'] ?? 0);
    $res = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $id");
    if (!$res || !($p = mysqli_fetch_assoc($res))) respond(false, ['error' => 'product_not_found']);
    $chk = mysqli_query($con_db, "SELECT id FROM cart WHERE id = $id AND u_id = $uid");
    if ($chk && mysqli_num_rows($chk) > 0) respond(false, ['error' => 'already_in_cart']);
    $ne = mysqli_real_escape_string($con_db, $p['p_name']);
    $ie = mysqli_real_escape_string($con_db, $p['p_img']);
    $price = (int)$p['p_price'];
    if (!mysqli_query($con_db, "INSERT INTO cart (u_id, id, c_name, c_price, c_total, c_img) VALUES ($uid, $id, '$ne', $price, $price, '$ie')")) {
        respond(false, ['error' => 'db']);
    }
    respond(true, cartStatus($con_db, $uid) + ['message' => 'تمت الإضافة إلى السلة ✅']);
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    mysqli_query($con_db, "DELETE FROM cart WHERE id = $id AND u_id = $uid");
    respond(true, cartStatus($con_db, $uid));
}

if ($action === 'qty') {
    $id = (int)($_POST['id'] ?? 0);
    $q  = (int)($_POST['qty'] ?? 0);
    if ($q < 1 || $q > 10) respond(false, ['error' => 'qty_range']);
    $r3 = mysqli_query($con_db, "SELECT c_price FROM cart WHERE id = $id AND u_id = $uid");
    if (!$r3 || !($row = mysqli_fetch_assoc($r3))) respond(false, ['error' => 'not_found']);
    $t = ((int)$row['c_price']) * $q;
    mysqli_query($con_db, "UPDATE cart SET c_total = $t, c_qty = $q WHERE id = $id AND u_id = $uid");
    respond(true, cartStatus($con_db, $uid));
}

if ($action === 'clear') {
    mysqli_query($con_db, "DELETE FROM cart WHERE u_id = $uid");
    respond(true, cartStatus($con_db, $uid));
}

respond(false, ['error' => 'unknown_action']);
