<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

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
        // ✅ فحص المخزون قبل الإدراج وليس بعده
        if ((int)$row['p_quantity'] <= 0) {
            header("Location: index.php?out=1");
            exit;
        }
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
  <div class="container">
    <div class="alert alert-success py-2"><i class="bi bi-check-circle"></i> تمت إضافة المنتج إلى السلة</div>
  </div>
<?php endif; ?>
<?php if (isset($_GET['out'])): ?>
  <div class="container">
    <div class="alert alert-warning py-2">هذا المنتج غير متوفر بالمخزون حاليًا</div>
  </div>
<?php endif; ?>

<div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel" data-aos="zoom-in">
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

<div class="container" id="products">
  <h4 class="mb-3" data-aos="fade-right"><i class="bi bi-basket2 text-brand"></i> منتجاتنا</h4>

  <?php
  $q    = trim($_GET['q'] ?? '');
  $cat  = (int) ($_GET['cat'] ?? 0);
  $sort = $_GET['sort'] ?? '';

  $cats = [];
  $rc = mysqli_query($con_db, "SELECT * FROM categories ORDER BY cat_id");
  if ($rc) while ($c = mysqli_fetch_assoc($rc)) $cats[] = $c;

  $where = [];
  if ($q !== '') {
    $qe = mysqli_real_escape_string($con_db, $q);
    $where[] = "(p_name LIKE '%$qe%' OR p_describe LIKE '%$qe%')";
  }
  if ($cat > 0) $where[] = "category_id = $cat";
  $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

  $orderSql = match ($sort) {
    'price_asc'  => 'ORDER BY p_price ASC',
    'price_desc' => 'ORDER BY p_price DESC',
    'name'       => 'ORDER BY p_name ASC',
    'rating'     => 'ORDER BY (SELECT AVG(rating) FROM reviews r WHERE r.product_id = product.p_id) DESC',
    default      => 'ORDER BY p_id',
  };
  ?>

  <form method="get" action="index.php" class="row g-2 mb-4" data-aos="fade-up">
    <div class="col-md-5">
      <div class="input-group">
        <input type="search" name="q" class="form-control" placeholder="ابحث عن منتج..." value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
        <button class="btn btn-brand" type="submit"><i class="bi bi-search"></i></button>
      </div>
    </div>
    <div class="col-md-4">
      <select name="cat" class="form-select" onchange="this.form.submit()">
        <option value="0">كل التصنيفات</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?php echo (int)$c['cat_id']; ?>" <?php echo $cat === (int)$c['cat_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['cat_name'], ENT_QUOTES); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="sort" class="form-select" onchange="this.form.submit()">
        <option value="">ترتيب: الافتراضي</option>
        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>السعر: من الأقل</option>
        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>السعر: من الأعلى</option>
        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>الاسم أبجديًا</option>
        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>الأعلى تقييمًا</option>
      </select>
    </div>
  </form>

  <div class="row g-4">
    <?php
    $result = mysqli_query($con_db, "SELECT * FROM product $whereSql $orderSql");
    $i = 0;
    if ($result && mysqli_num_rows($result) > 0) {
      while ($p = mysqli_fetch_assoc($result)) {
        $pid   = (int) $p['p_id'];
        $stock = (int) $p['p_quantity'];
        $delay = ($i % 4) * 100;
        $i++;
    ?>

        <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
          <div class="card h-100 shadow-sm">
            <a href="details.php?id=<?php echo $pid; ?>">
              <div class="img-wrap">
                <img src="<?php echo htmlspecialchars($p['p_img'], ENT_QUOTES); ?>" class="w-100" alt="<?php echo htmlspecialchars($p['p_name'], ENT_QUOTES); ?>">
              </div>
            </a>
            <div class="card-body text-center d-flex flex-column">
              <h6 class="card-title"><?php echo htmlspecialchars($p['p_name'], ENT_QUOTES); ?></h6>
              <p class="fw-bold text-brand mb-1"><?php echo (int) $p['p_price']; ?>$</p>
              <?php
              $ra = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT AVG(rating) a, COUNT(*) c FROM reviews WHERE product_id = $pid"));
              if ((int)$ra['c'] > 0): ?>
                <small class="text-warning d-block mb-2"><?php echo str_repeat('★', (int) round((float)$ra['a'])); ?> <span class="text-muted">(<?php echo (int)$ra['c']; ?>)</span></small>
              <?php endif; ?>
              <?php if ($stock > 0 && $stock <= 5): ?>
                <small class="text-warning mb-2">⚠ باقي <?php echo $stock; ?> فقط</small>
              <?php endif; ?>
              <?php if ($stock <= 0): ?>
                <button class="btn btn-secondary btn-sm w-100 mt-auto" disabled><i class="bi bi-x-circle"></i> نفد المخزون</button>
              <?php elseif (isset($_SESSION['u_id'])): ?>
                <form method="post" action="index.php" class="js-add-form mt-auto">
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
    } else {
      ?>
      <div class="col-12">
        <div class="alert alert-light border text-center py-5">
          <i class="bi bi-search text-brand" style="font-size:3rem"></i>
          <h5 class="mt-3">لا توجد نتائج مطابقة</h5>
          <a href="index.php" class="btn btn-brand btn-sm mt-2">عرض كل المنتجات</a>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
</div>
</div>

<?php
include("include/footer.php");
?>