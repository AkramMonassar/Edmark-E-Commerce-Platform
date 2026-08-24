<?php
include("include/header.php");
require("connection/connection.php");
?>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="cart.css">
</head>
<body>

<?php
// ===== تعديل الكمية (قبل العرض عشان تشوف التغيير فوراً) =====
if (isset($_POST['change2'])) {
    csrf_check();
    $inputQuantity = trim($_POST['howQuantity'] ?? '');
    $id = (int) ($_POST['change1'] ?? 0);

    if ($inputQuantity === '') {
        echo "<p>Error: enter value for field next to change button</p>";
    } elseif (!is_numeric($inputQuantity) || (int)$inputQuantity > 10 || (int)$inputQuantity < 1) {
        echo "<p>Error: the Quantity must be a number <= 10</p>";
    } else {
        $quant = (int) $inputQuantity;
        $result3 = mysqli_query($con_db, "SELECT c_price FROM cart WHERE id = $id");
        if (!$result3) {
            printf('Errormessage3: %s', mysqli_error($con_db));
        } elseif (mysqli_num_rows($result3) > 0) {
            $row = mysqli_fetch_assoc($result3);
            $price1 = ((int)$row['c_price']) * $quant;
            $result4 = mysqli_query($con_db, "UPDATE cart SET c_total = $price1, c_qty = $quant WHERE id = $id");
            if (!$result4) printf('Errormessage4: %s', mysqli_error($con_db));
        }
    }
}

// ===== حذف منتج واحد =====
if (isset($_POST['delete2'])) {
    csrf_check();
    $id = (int) ($_POST['delete1'] ?? 0);
    $result6 = mysqli_query($con_db, "DELETE FROM cart WHERE id = $id");
    if (!$result6) printf('Errormessage6: %s', mysqli_error($con_db));
}

// ===== تفريغ السلة =====
if (isset($_POST['deleteAll'])) {
    csrf_check();
    $result7 = mysqli_query($con_db, "DELETE FROM cart");
    if (!$result7) printf("Errormessage7: %s", mysqli_error($con_db));
}

// ===== عرض المنتجات =====
$result2 = mysqli_query($con_db, "SELECT * FROM cart");
if (!$result2) {
    printf('Errormessage2: %s', mysqli_error($con_db));
} elseif (mysqli_num_rows($result2) > 0) {
    while ($row = mysqli_fetch_assoc($result2)) {
?>
<div id="product_details">
    <div id="pro_det1">
        <img id='img3' src='<?php echo htmlspecialchars($row['c_img'], ENT_QUOTES); ?>'>
    </div>
    <br>
    <div id="pro_det2">
        <span>اسم المنتج: </span>&nbsp;&nbsp;<?php echo htmlspecialchars($row['c_name'], ENT_QUOTES); ?><br>
        <span>سعر المنتج: </span>&nbsp;&nbsp;<?php echo $row['c_price']; ?>$<br>
        <span>كمية المنتج: </span>&nbsp;&nbsp;<?php echo $row['c_qty']; ?><br>
        <span>السعر الإجمالي: </span>&nbsp;&nbsp;<?php echo $row['c_total']; ?>$
    </div>
    <br>
    <form action="cart.php" method="post">
        <?php echo csrf_field(); ?>
        تغير الكمية: <input type='number' placeholder="<=10" name="howQuantity">
        <input type="hidden" value="<?php echo (int)$row['id']; ?>" name="change1">
        <input type="submit" value="تعديل" name="change2">
    </form>
    <br>
    <form action="cart.php" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" value="<?php echo (int)$row['id']; ?>" name="delete1">
        <input type="submit" value="حذف المنتج" name="delete2">
    </form>
</div>
<?php
    }
}

$result8 = mysqli_query($con_db, "SELECT COUNT(*) AS allRows FROM cart");
if ($result8) {
    $r = mysqli_fetch_assoc($result8);
    echo "انت تملك حالياً " . $r['allRows'] . " منتجات مختارة في السلة.";
}
?>

<form action="cart.php" method="post">
<input type="submit" name="deleteAll" value="تفريغ السلة">
<?php echo csrf_field(); ?>
</form>
<form action="checkout.php" method="post">
<input type="submit" value="صفحة الدفع">
<?php echo csrf_field(); ?>
</form>
</body>
</html>
<?php
include("include/footer.php");
?>