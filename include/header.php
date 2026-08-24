<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/csrf.php';
?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <link rel="stylesheet" type="text/css" href="engine1/style.css" />
    <script type="text/javascript" src="engine1/jquery.js"></script>
</head>

<body dir="rtl" style="min-width:762px">
    <div id="body_div">
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
                                                if (isset($_SESSION['u_id'])) {
                                                    $uid = (int) $_SESSION['u_id'];
                                                    $res = mysqli_query($con_db, "SELECT SUM(c_total) AS t, COUNT(id) AS c FROM cart WHERE u_id = $uid");
                                                    $row = $res ? mysqli_fetch_assoc($res) : null;
                                                    echo "<h6 id='h6'>الناتج الإجمالي : " . (int)($row['t'] ?? 0) . " $</h6>";
                                                    echo "<h6 id='h6'> عدد المنتجات: " . (int)($row['c'] ?? 0) . "</h6>";
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
                        <div style="text-align:center; color:#fff; padding:25px 0;">
                            <span style="font-size:36px;">🌿</span><br>
                            <b style="font-size:24px;">EDMARK</b><br>
                            <span style="font-size:14px;">شركة إدمارك العالمية</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
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