<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';

if (isset($_POST['reset'])) {
    header("Location: index.php");
    exit;
}

$msg = '';
$msgType = 'success';
if (isset($_POST['submit'])) {
    require('connection/connection.php');
    csrf_check();

    if (!isset($_SESSION['u_id'])) {
        header("Location: login.php");
        exit;
    }
    $uid = (int) $_SESSION['u_id'];

    if (empty($_POST['cardNumber']) || empty($_POST['cvc']) || empty($_POST['fullName']) || empty($_POST['expire'])) {
        $msg = "خطأ : كل الحقول يجب ان تملأ";
        $msgType = 'danger';
    } else {
        $cardNumber = (int) $_POST['cardNumber'];
        $cvc        = (int) $_POST['cvc'];
        $fullName   = mysqli_real_escape_string($con_db, trim($_POST['fullName']));
        $ts         = strtotime(trim($_POST['expire']));

        if ($ts === false) {
            $msg = "خطأ : صيغة التاريخ غير صحيحة، استخدم YYYY/MM/DD";
            $msgType = 'danger';
        } else {
            $expire = date('Y-m-d', $ts);
            $r = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT SUM(c_total) AS t FROM cart WHERE u_id = $uid"));
            $cartTotal = (int) ($r['t'] ?? 0);

            if ($cartTotal <= 0) {
                $msg = "سلتك فارغة — أضف منتجات أولاً";
                $msgType = 'warning';
            } else {
                $sql = "INSERT INTO payment (cardNumber, cvc, fullName, expiration, amount, u_id) VALUES ($cardNumber, $cvc, '$fullName', '$expire', $cartTotal, $uid)";
                if (!mysqli_query($con_db, $sql)) {
                    $msg = 'خطأ: ' . mysqli_error($con_db);
                    $msgType = 'danger';
                } else {
                    mysqli_query($con_db, "INSERT INTO orders (u_id, total) VALUES ($uid, $cartTotal)");
                    $orderId = mysqli_insert_id($con_db);
                    $items = mysqli_query($con_db, "SELECT * FROM cart WHERE u_id = $uid");
                    while ($it = mysqli_fetch_assoc($items)) {
                        $pn = mysqli_real_escape_string($con_db, $it['c_name']);
                        mysqli_query($con_db, "INSERT INTO order_items (order_id, product_id, product_name, qty, price) VALUES ($orderId, " . (int)$it['id'] . ", '$pn', " . (int)$it['c_qty'] . ", " . (int)$it['c_price'] . ")");
                    }
                    mysqli_query($con_db, "DELETE FROM cart WHERE u_id = $uid");
                    $msg = "شكرًا لك! تم تسجيل طلبك رقم #$orderId بمبلغ $cartTotal$";
                }
            }
        }
    }
}
include("include/header.php");
?>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5" data-aos="zoom-in">
      <div class="card shadow border-0 mt-4 mb-5">
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <i class="bi bi-credit-card-2-front text-brand" style="font-size:3.5rem"></i>
            <h4>استمارة الدفع</h4>
          </div>
          <?php if ($msg !== ''): ?>
            <div class="alert alert-<?php echo $msgType; ?> py-2 small"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
          <?php endif; ?>
          <form action="checkout.php" method="post">
            <?php echo csrf_field(); ?>
            <div class="form-floating mb-3">
              <input type="text" name="cardNumber" class="form-control" id="cardNumber" placeholder="رقم البطاقة" required>
              <label for="cardNumber">رقم البطاقة</label>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <div class="form-floating mb-3">
                  <input type="text" name="cvc" class="form-control" id="cvc" placeholder="CVC" required>
                  <label for="cvc">CVC</label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-floating mb-3">
                  <input type="text" name="expire" class="form-control" id="expire" placeholder="التاريخ" required>
                  <label for="expire">تاريخ الانتهاء</label>
                </div>
              </div>
            </div>
            <div class="form-floating mb-4">
              <input type="text" name="fullName" class="form-control" id="fullName" placeholder="الاسم الكامل" required>
              <label for="fullName">الاسم الكامل</label>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" name="submit" class="btn btn-brand w-100"><i class="bi bi-bag-check"></i> شراء</button>
              <button type="submit" name="reset" class="btn btn-outline-secondary w-50">إلغاء</button>
            </div>
            <p class="small text-muted mt-2 mb-0">صيغة التاريخ: YYYY/MM/DD</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
include("include/footer.php");
?>