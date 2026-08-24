<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require("connection/connection.php");

if (!isset($_SESSION['u_id'])) {
    header("Location: login.php");
    exit;
}
$uid = (int) $_SESSION['u_id'];

if (isset($_POST['change2'])) {
    csrf_check();
    $inputQuantity = trim($_POST['howQuantity'] ?? '');
    $id = (int) ($_POST['change1'] ?? 0);
    if ($inputQuantity !== '' && is_numeric($inputQuantity) && (int)$inputQuantity >= 1 && (int)$inputQuantity <= 10) {
        $quant = (int) $inputQuantity;
        $r3 = mysqli_query($con_db, "SELECT c_price FROM cart WHERE id = $id AND u_id = $uid");
        if ($r3 && mysqli_num_rows($r3) > 0) {
            $row = mysqli_fetch_assoc($r3);
            $price1 = ((int)$row['c_price']) * $quant;
            mysqli_query($con_db, "UPDATE cart SET c_total = $price1, c_qty = $quant WHERE id = $id AND u_id = $uid");
        }
    }
}

if (isset($_POST['delete2'])) {
    csrf_check();
    $id = (int) ($_POST['delete1'] ?? 0);
    mysqli_query($con_db, "DELETE FROM cart WHERE id = $id AND u_id = $uid");
}

if (isset($_POST['deleteAll'])) {
    csrf_check();
    mysqli_query($con_db, "DELETE FROM cart WHERE u_id = $uid");
}

include("include/header.php");

$result2 = mysqli_query($con_db, "SELECT * FROM cart WHERE u_id = $uid");
$count = $result2 ? mysqli_num_rows($result2) : 0;
?>
<div class="container">
    <h4 class="mb-4" data-aos="fade-right"><i class="bi bi-cart3 text-brand"></i> سلة الشراء</h4>

    <?php if ($count === 0): ?>
        <div class="alert alert-light border text-center py-5" data-aos="zoom-in">
            <i class="bi bi-cart-x text-brand" style="font-size:4rem"></i>
            <h5 class="mt-3">سلتك فارغة حاليًا</h5>
            <a href="index.php" class="btn btn-brand mt-2">تصفح المنتجات</a>
        </div>
    <?php else: ?>
        <div class="table-responsive bg-white rounded-3 shadow-sm p-3" data-aos="fade-up">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th>الاسم</th>
                        <th>السعر</th>
                        <th class="text-center">الكمية</th>
                        <th>الإجمالي</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result2)): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($row['c_img'], ENT_QUOTES); ?>" style="width:60px;height:60px;object-fit:contain"></td>
                            <td><?php echo htmlspecialchars($row['c_name'], ENT_QUOTES); ?></td>
                            <td><?php echo (int)$row['c_price']; ?>$</td>
                            <td class="text-center">
                                <form method="post" class="d-flex justify-content-center align-items-center gap-1">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="change1" value="<?php echo (int)$row['id']; ?>">
                                    <input type="number" name="howQuantity" min="1" max="10" value="<?php echo (int)$row['c_qty']; ?>" class="form-control form-control-sm" style="width:70px">
                                    <button type="submit" name="change2" class="btn btn-sm btn-outline-secondary" title="تعديل"><i class="bi bi-pencil-square"></i></button>
                                </form>
                            </td>
                            <td class="fw-bold text-brand"><?php echo (int)$row['c_total']; ?>$</td>
                            <td>
                                <form method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="delete1" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" name="delete2" class="btn btn-sm btn-outline-danger" title="حذف"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" data-aos="fade-up">
            <form method="post">
                <?php echo csrf_field(); ?>
                <button type="submit" name="deleteAll" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> تفريغ السلة</button>
            </form>
            <a href="checkout.php" class="btn btn-brand btn-lg"><i class="bi bi-credit-card"></i> إتمام الدفع</a>
        </div>
    <?php endif; ?>
</div>
<?php
include("include/footer.php");
?>