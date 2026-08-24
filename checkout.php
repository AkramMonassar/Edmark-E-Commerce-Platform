<?php
if (isset($_POST['reset'])) {
    header("Location: index.php");
    exit;
}

$msg = '';
if (isset($_POST['submit'])) {
    require('connection/connection.php');
    require_once __DIR__ . '/include/csrf.php';
    csrf_check();

    if (empty($_POST['cardNumber']) || empty($_POST['cvc']) || empty($_POST['fullName']) || empty($_POST['expire']) || empty($_POST['amount'])) {
        $msg = "خطأ : كل الحقول يجب ان تملأ";
    } else {
        $cardNumber = (int) $_POST['cardNumber'];
        $cvc        = (int) $_POST['cvc'];
        $amount     = (int) $_POST['amount'];
        $fullName   = mysqli_real_escape_string($con_db, trim($_POST['fullName']));
        $ts         = strtotime(trim($_POST['expire']));

        if ($ts === false) {
            $msg = "خطأ : صيغة التاريخ غير صحيحة، استخدم YYYY/MM/DD";
        } else {
            $expire = date('Y-m-d', $ts);
            $sql = "INSERT INTO payment (cardNumber, cvc, fullName, expiration, amount)
                    VALUES ($cardNumber, $cvc, '$fullName', '$expire', $amount)";
            $result = mysqli_query($con_db, $sql);

            if (!$result) {
                $msg = 'Errormessage1: ' . mysqli_error($con_db);
            } else {
                mysqli_query($con_db, "DELETE FROM cart");
                $msg = "شكراً جزيلاً";
            }
        }
    }
}
include("include/header.php");
?>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="checkout.css">
</head>
<body>
<h2>استمارة الدفع</h2>
<?php if ($msg !== '') echo "<p>$msg</p>";  ?>
<form action="checkout.php" method="post">
<?php echo csrf_field(); ?>    
رقم البطاقة:<br/>
<input type="text" name="cardNumber" class="form-control"><br/>
CVC:<br/>
<input type="text" name="cvc" class="form-control"><br/>
الأسم الكامل:<br/>
<input type="text" name="fullName" class="form-control"><br/>
التأريخ(YYYY/MM/DD):<br/>
<input type="text" name="expire" class="form-control"><br/>
الكمية:<br/>
<input type="text" name="amount"><br/>
<input type="submit" name="submit" value="شراء">
<input type="submit" name="reset" value="الغاء">
</form>

</body>
</html>
<?php
include("include/footer.php");
?>