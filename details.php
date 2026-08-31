<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require("connection/connection.php");

$id = (int) ($_GET['id'] ?? 0);

if (isset($_POST['addtocart'])) {
    csrf_check();
    if (!isset($_SESSION['u_id'])) {
        header("Location: Login.php");
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

if (isset($_POST['add_review'])) {
    csrf_check();
    if (isset($_SESSION['u_id'])) {
        $uidR   = (int) $_SESSION['u_id'];
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = mysqli_real_escape_string($con_db, trim($_POST['comment'] ?? ''));
        if ($rating >= 1 && $rating <= 5) {
            mysqli_query($con_db, "INSERT INTO reviews (product_id, u_id, rating, comment) VALUES ($id, $uidR, $rating, '$comment') ON DUPLICATE KEY UPDATE rating = $rating, comment = '$comment'");
        }
    }
    header("Location: details.php?id=$id");
    exit;
}

// متوسط التقييم
$avg = 0;
$cnt = 0;
$rvq = mysqli_query($con_db, "SELECT rating FROM reviews WHERE product_id = $id");
while ($r = mysqli_fetch_assoc($rvq)) {
    $avg += (int)$r['rating'];
    $cnt++;
}
$avg = $cnt ? round($avg / $cnt, 1) : 0;

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
                <p class="text-warning mb-1"><?php echo $cnt > 0 ? str_repeat('★', (int) round($avg)) . str_repeat('☆', 5 - (int) round($avg)) . " <small class='text-muted'>($avg من $cnt تقييم)</small>" : '<small class="text-muted">لا تقييمات بعد</small>'; ?></p>
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
                    <a href="Login.php" class="btn btn-outline-secondary btn-lg mt-3">سجل الدخول للشراء</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container my-4">
        <div class="row g-4">
            <div class="col-lg-7" data-aos="fade-up">
                <h5 class="mb-3"><i class="bi bi-chat-square-text text-brand"></i> المراجعات (<?php echo $cnt; ?>)</h5>
                <?php
                $list = mysqli_query($con_db, "SELECT r.*, u.u_name FROM reviews r LEFT JOIN users u ON u.u_id = r.u_id WHERE r.product_id = $id ORDER BY r.review_id DESC");
                if (mysqli_num_rows($list) === 0): ?>
                    <p class="text-muted">لا توجد مراجعات بعد — كن أول من يقيّم!</p>
                    <?php else: while ($rvw = mysqli_fetch_assoc($list)): ?>
                        <div class="card border-0 shadow-sm mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <b><?php echo htmlspecialchars($rvw['u_name'] ?? 'مستخدم', ENT_QUOTES); ?></b>
                                    <span class="text-warning"><?php echo str_repeat('★', (int)$rvw['rating']) . str_repeat('☆', 5 - (int)$rvw['rating']); ?></span>
                                </div>
                                <?php if (($rvw['comment'] ?? '') !== ''): ?><p class="small text-muted mb-0"><?php echo htmlspecialchars($rvw['comment'], ENT_QUOTES); ?></p><?php endif; ?>
                            </div>
                        </div>
                <?php endwhile;
                endif; ?>
            </div>
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm p-3">
                    <h6>قيّم هذا المنتج</h6>
                    <?php if (isset($_SESSION['u_id'])): ?>
                        <form method="post">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="mb-2">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                    <input type="radio" class="btn-check" name="rating" id="rate<?php echo $s; ?>" value="<?php echo $s; ?>" <?php echo $s == 5 ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-warning btn-sm" for="rate<?php echo $s; ?>"><?php echo $s; ?> ★</label>
                                <?php endfor; ?>
                            </div>
                            <textarea name="comment" class="form-control mb-2" rows="3" placeholder="اكتب انطباعك (اختياري)"></textarea>
                            <button class="btn btn-brand btn-sm w-100" name="add_review" type="submit">إرسال التقييم</button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted small mb-0"><a href="Login.php" class="text-brand">سجل الدخول</a> لتتمكن من التقييم.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    echo '<div class="container"><div class="alert alert-warning mt-4">المنتج غير موجود</div></div>';
}

include("include/footer.php");
?>