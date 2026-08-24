<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../connection/connection.php';

$cart_count = 0;
$cart_total = 0;
if (isset($_SESSION['u_id'])) {
    $uid = (int) $_SESSION['u_id'];
    $res = mysqli_query($con_db, "SELECT SUM(c_total) AS t, COUNT(id) AS c FROM cart WHERE u_id = $uid");
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $cart_total = (int) ($r['t'] ?? 0);
        $cart_count = (int) ($r['c'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إدمارك | متجر المنتجات العشبية</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root { --brand:#2e7d32; }
.bg-brand { background-color: var(--brand) !important; }
.text-brand { color: var(--brand) !important; }
.btn-brand { background-color: var(--brand); border-color: var(--brand); color:#fff; }
.btn-brand:hover { background-color:#1b5e20; border-color:#1b5e20; color:#fff; }
.card-img-top { height:200px; object-fit:contain; padding:1rem; background:#fff; }
.hero-img { max-height:320px; object-fit:contain; background:#e8f5e9; }
</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-brand sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-leaf"></i> EDMARK</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">الرئيسية</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#products">المنتجات</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">من نحن</a></li>
        <li class="nav-item"><a class="nav-link" href="contact_us.php">تواصل معنا</a></li>
      </ul>
      <ul class="navbar-nav align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart3 fs-5"></i>
            <?php if ($cart_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning"><?php echo $cart_count; ?></span>
            <?php endif; ?>
            <span class="d-none d-lg-inline">السلة</span>
          </a>
        </li>
        <?php if ($cart_total > 0): ?>
        <li class="nav-item"><span class="badge text-bg-light text-brand"><?php echo $cart_total; ?>$</span></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
        <li class="nav-item"><span class="nav-link text-warning">أهلاً، <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES); ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">تسجيل الخروج</a></li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="login.php">تسجيل الدخول</a></li>
        <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="create_acount.php">حساب جديد</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="py-4">