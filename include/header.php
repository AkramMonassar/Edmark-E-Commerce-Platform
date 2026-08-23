<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- header -->
<html>
<head>
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="HTML,CSS,XML,JavaScript">
<meta name="author" content="Hege Refsnes">
<meta charset="UTF-8">
<title>Home</title>
<link rel="stylesheet" type="text/css" href="index.css">
<!-- Start WOWSlider.com HEAD section -->
<link rel="stylesheet" type="text/css" href="engine1/style.css" />
<script type="text/javascript" src="engine1/jquery.js"></script>
<!-- End WOWSlider.com HEAD section -->
</head>
<body dir="rtl" style="min-width:762px">
<div id="body_div">

<!-- part of header -->
<div class="header">
<table id="table" border="0">
<tr>
<td id="usersss" style="width:34%;">
<center>
<?php
if (isset($_SESSION['user'])) {
    echo "<h3 id='h33'>welcom to " . htmlspecialchars($_SESSION['user'], ENT_QUOTES) . "</h3><br/>";
}
?>
</center>
</td>
<td style="width:34%">
<div>
<table border="0" id="table">
<tr>
<td id="td_cart">
<div id="cart_all">
<div id="cart_2">
<a href="cart.php" style="font-size:38px; text-decoration:none;" title="السلة">🛒</a>
</div>
<div id="cart_2">
<?php
require("connection/connection.php");

$q   = "SELECT sum(c_total), count(id) FROM cart";
$res = mysqli_query($con_db, $q);
$row = $res ? mysqli_fetch_row($res) : false;

$total = $row[0] ?? 0;
$count = $row[1] ?? 0;

if (isset($_SESSION['user'])) {
    echo "<h6 id='h6'>الناتج الإجمالي : $total $</h6>";
    echo "<h6 id='h6'> عدد المنتجات: $count</h6>";
} else {
    // نفس منطق كودك القديم: إذا ما فيه مستخدم مسجل فرّغ السلة
    mysqli_query($con_db, "DELETE FROM cart");
}
?>
</div>
</div>
</td>
</tr>
<tr>
<td id="td_cart"><b></b></td>
</tr>
</table>
</div>
</td>
<td style="width:34%" id="img_header">
    <!-- <img src="photo/emblem.png" style="height:150px;width:201px;"> -->
    <div style="text-align:center; color:#fff; padding:25px 0;">
    <span style="font-size:36px;">🌿</span><br>
    <b style="font-size:24px;">EDMARK</b><br>
    <span style="font-size:14px;">شركة إدمارك العالمية</span>
</div>
</td>
</tr>
</table>
</div>

<!-- part of menu -->
<div id="menu">
<ul>
<li><a id="a_menu" href="index.php"> الصفحة الرئيسية </a></li>
<li><a id="a_menu" href="index.php"> الأصناف </a></li>
<li><a id="a_menu" href="index.php"> الخدمات </a></li>
<li><a id="a_menu" href="about_us.php"> من نحن </a></li>
<li><a id="a_menu" href="contact_us.php"> تواصل معنا </a></li>
<?php
if (!isset($_SESSION['user'])) {
    echo "<li><a id='a_menu' href='login.php'> تسجيل الدخول </a></li>";
} else {
    echo "<li><a id='a_menu' href='logout.php'> تسجيل الخروج </a></li>";
}
?>
</ul>
</div>