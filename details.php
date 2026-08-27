<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require("connection/connection.php");

$id = (int) ($_GET['id'] ?? 0);

if (isset($_POST['addtocart'])) {
    csrf_check();
    if (!isset($_SESSION['u_id'])) {
        header("Location: login.php");
        exit;
    }
    $uid = (int) $_SESSION['u_id'];
    $pid = (int) $_POST['id'];
    $res = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $pid");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $check = mysqli_query($con_db, "SELECT * FROM cart WHERE id = $pid AND u_id = $uid");
        if ($check && mysqli_num_rows($check) == 0) {
            $name_esc = mysqli_real_escape_string($con_db, $row['p_name']);
            $img_esc  = mysqli_real_escape_string($con_db, $row['p_img']);
            $price    = (int) $row['p_price'];
            mysqli_query($con_db, "INSERT INTO cart (u_id, id, c_name, c_price, c_total, c_img) VALUES ($uid, $pid, '$name_esc', $price, $price, '$img_esc')");
        }
    }
    if ((int)$row['p_quantity'] <= 0) {
        header("Location: index.php?out=1");
        exit;
    }
    header("Location: index.php?added=1");
    exit;
}

include("include/header.php");

$result = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $id");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $stock = (int)$row['p_quantity'];
?>
    <div class="container my-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-5 text-center" data-aos="fade-left">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <img src="<?php echo htmlspecialchars($row['p_img'], ENT_QUOTES); ?>" class="img-fluid" style="max-height:320px" alt="<?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?>">
                </div>
            </div>
            <div class="col-md-7" data-aos="fade-right">
                <h3 class="fw-bold"><?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?></h3>
                <p class="text-muted mt-3"><?php echo htmlspecialchars($row['p_describe'], ENT_QUOTES); ?></p>
                <h4 class="text-brand fw-bold mt-3"><?php echo (int)$row['p_price']; ?>$</h4>
                <?php if ($stock <= 0): ?>
                    <span class="badge text-bg-danger fs-6 mt-3">نفد المخزون حاليًا</span>
                <?php elseif (isset($_SESSION['u_id'])): ?>
                    <form method="post" class="js-add-form mt-3">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" name="addtocart" class="btn btn-brand btn-lg"><i class="bi bi-cart-plus"></i> أضف للسلة</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-lg mt-3">سجل الدخول للشراء</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
} else {
    echo '<div class="container"><div class="alert alert-warning mt-4">المنتج غير موجود</div></div>';
}
include("include/footer.php");
?>