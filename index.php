<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

// إضافة للسلة قبل أي output + إعادة توجيه (PRG) لمنع الإضافة المكررة عند التحديث
if (isset($_POST['addtocart'])) {
    csrf_check();
    if (!isset($_SESSION['u_id'])) {
        header("Location: login.php");
        exit;
    }
    $uid = (int) $_SESSION['u_id'];
    $id  = (int) $_POST['id'];
    $res = mysqli_query($con_db, "SELECT * FROM product WHERE p_id = $id");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $check = mysqli_query($con_db, "SELECT * FROM cart WHERE id = $id AND u_id = $uid");
        if ($check && mysqli_num_rows($check) == 0) {
            $name_esc = mysqli_real_escape_string($con_db, $row['p_name']);
            $img_esc  = mysqli_real_escape_string($con_db, $row['p_img']);
            $price    = (int) $row['p_price'];
            mysqli_query($con_db, "INSERT INTO cart (u_id, id, c_name, c_price, c_total, c_img) VALUES ($uid, $id, '$name_esc', $price, $price, '$img_esc')");
        }
    }
    header("Location: index.php?added=1");
    exit;
}
include("include/header.php");
?>

<?php if (isset($_GET['added'])): ?>
<div class="container"><div class="alert alert-success py-2">تمت إضافة المنتج إلى السلة ✅</div></div>
<?php endif; ?>

<!-- سلايدر Bootstrap -->
<div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active"><img src="photo/2.png" class="d-block w-100 hero-img" alt="منتج"></div>
    <div class="carousel-item"><img src="photo/1.png" class="d-block w-100 hero-img" alt="منتج"></div>
    <div class="carousel-item"><img src="photo/3.png" class="d-block w-100 hero-img" alt="منتج"></div>
    <div class="carousel-item"><img src="photo/4.png" class="d-block w-100 hero-img" alt="منتج"></div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- شبكة المنتجات -->
<div class="container" id="products">
  <h4 class="mb-3"><i class="bi bi-basket2 text-brand"></i> منتجاتنا</h4>
  <div class="row g-4">
    <?php
    $result = mysqli_query($con_db, "SELECT * FROM product");
    if ($result && mysqli_num_rows($result) > 0) {
        while ($p = mysqli_fetch_assoc($result)) {
            $pid = (int) $p['p_id'];
    ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card h-100 shadow-sm">
        <a href="details.php?id=<?php echo $pid; ?>">
          <img src="<?php echo htmlspecialchars($p['p_img'], ENT_QUOTES); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['p_name'], ENT_QUOTES); ?>">
        </a>
        <div class="card-body text-center d-flex flex-column">
          <h6 class="card-title"><?php echo htmlspecialchars($p['p_name'], ENT_QUOTES); ?></h6>
          <p class="fw-bold text-brand mb-2"><?php echo (int) $p['p_price']; ?>$</p>
          <?php if (isset($_SESSION['u_id'])): ?>
          <form method="post" action="index.php" class="mt-auto">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo $pid; ?>">
            <button type="submit" name="addtocart" class="btn btn-brand btn-sm w-100"><i class="bi bi-cart-plus"></i> أضف للسلة</button>
          </form>
          <?php else: ?>
          <a href="login.php" class="btn btn-outline-secondary btn-sm w-100 mt-auto">سجل الدخول للشراء</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php
        }
    }
    ?>
  </div>
</div>

<?php
include("include/footer.php");
?>