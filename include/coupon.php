<?php
function couponInfo($con_db, $uid) {
    $res = mysqli_query($con_db, "SELECT SUM(c_total) AS t, COUNT(id) AS c FROM cart WHERE u_id = $uid");
    $r = $res ? mysqli_fetch_assoc($res) : null;
    $total = (int) ($r['t'] ?? 0);
    $count = (int) ($r['c'] ?? 0);
    $discount = 0;
    $code = $_SESSION['coupon_code'] ?? '';
    if ($code !== '' && $total > 0) {
        $ce = mysqli_real_escape_string($con_db, $code);
        $cp = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT * FROM coupons WHERE code = '$ce' AND active = 1"));
        $valid = $cp
            && (is_null($cp['expires_at']) || strtotime($cp['expires_at']) >= strtotime(date('Y-m-d')))
            && $total >= (int) $cp['min_total'];
        if ($valid) {
            $discount = $cp['type'] === 'percent'
                ? (int) round($total * (int) $cp['value'] / 100)
                : min((int) $cp['value'], $total);
        } else {
            unset($_SESSION['coupon_code']);
            $code = '';
        }
    }
    return ['count' => $count, 'total' => $total, 'discount' => $discount, 'total_after' => max(0, $total - $discount), 'code' => $code];
}