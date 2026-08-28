<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/connection/connection.php';

if (!isset($_SESSION['u_id'])) { header("Location: login.php"); exit; }
$uid = (int) $_SESSION['u_id'];
$st = mysqli_prepare($con_db, "SELECT u_type FROM users WHERE u_id = ?");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$me = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (($me['u_type'] ?? '') !== 'admin') { header("Location: index.php"); exit; }

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="orders-' . date('Y-m-d') . '.csv"');
echo "\xEF\xBB\xBF"; // BOM عشان العربي يطلع سليم بالـ Excel

$out = fopen('php://output', 'w');
fputcsv($out, ['رقم الطلب', 'التاريخ', 'العميل', 'الهاتف', 'المدينة', 'العنوان', 'طريقة الدفع', 'الحالة', 'الإجمالي', 'الخصم', 'الكوبون']);

$res = mysqli_query($con_db, "SELECT o.*, u.u_name FROM orders o LEFT JOIN users u ON u.u_id = o.u_id ORDER BY o.order_id DESC");
while ($o = mysqli_fetch_assoc($res)) {
    fputcsv($out, [
        $o['order_id'], $o['created_at'], $o['u_name'] ?? '', $o['customer_phone'] ?? '',
        $o['city'] ?? '', $o['address'] ?? '', $o['payment_method'], $o['status'],
        $o['total'], $o['discount'] ?? 0, $o['coupon_code'] ?? ''
    ]);
}
fclose($out);
exit;