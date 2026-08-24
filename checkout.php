<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

if (!isset($_SESSION['u_id'])) { header("Location: login.php"); exit; }
$uid = (int) $_SESSION['u_id'];

// ================= مزودو الدفع (عدّل بحرية) =================
$providers = [
    'wallets' => [
        'mahfazati'   => 'محفظتي (بنك التضامن)',
        'jawwali'     => 'جوالي',
        'onecash'     => 'ون كاش (One Cash)',
        'karimi'      => 'كريمي فلوس / محفظة الكريمي',
        'cash'        => 'كاش (تمكين)',
        'floosak'     => 'فلوسك',
        'mobilemoney' => 'موبايل موني (Mobile Money)',
        'jayeb'       => 'جيب (الحزمي)',
        'mfloos'      => 'أم فلوس (M-Floos)',
        'yemenwallet' => 'يمن والت (Yemen Wallet)',
        'yemenipay'   => 'يمن باي (Yemeni Pay)',
        'shamil'      => 'شامل',
        'sabacash'    => 'سبأ كاش (بنك سبأ الإسلامي)',
    ],
    'exchange' => [
        'alkarimi'  => 'بنك الكريمي',
        'alhazmi'   => 'الحزمي للصرافة',
        'almarisi'  => 'المريسي للصرافة',
        'alqutaibi' => 'بنك القطيبي الإسلامي',
        'albusiri'  => 'بنك البسيري الإسلامي',
        'bandol'    => 'بنك بن دول الإسلامي',
        'yft'       => 'الشركة اليمنية للتحويلات المالية (YFT)',
    ],
    'bnpl' => [
        'tamara' => 'تمارا (Tamara)',
        'tabby'  => 'تابي (Tabby)',
    ],
];
$storeInfo = [
    'wallet'   => 'رقم محفظة المتجر: <b class="text-brand">777-123-456</b>',
    'exchange' => 'اسم المستفيد للتحويل: <b class="text-brand">متجر إدمارك — أكرم منصّر</b>',
];

function luhnOk($num) {
    $s = 0; $d = false;
    for ($i = strlen($num) - 1; $i >= 0; $i--) {
        $n = (int) $num[$i];
        if ($d) { $n *= 2; if ($n > 9) $n -= 9; }
        $s += $n; $d = !$d;
    }
    return $s % 10 === 0;
}

$msg = ''; $msgType = 'success';

if (isset($_POST['submit'])) {
    csrf_check();

    $r = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT SUM(c_total) AS t FROM cart WHERE u_id = $uid"));
    $cartTotal = (int) ($r['t'] ?? 0);

    if ($cartTotal <= 0) {
        $msg = "سلتك فارغة — أضف منتجات أولاً"; $msgType = 'warning';
    } else {
        $method = $_POST['method'] ?? '';
        $status = ''; $details = ''; $ok = true;
        $esc = fn($v) => mysqli_real_escape_string($con_db, $v);

        if ($method === 'cod') {
            $status = 'cod';
            $details = 'الدفع عند الاستلام';

        } elseif ($method === 'wallet') {
            $w = $_POST['wallet'] ?? ''; $wn = trim($_POST['walletNumber'] ?? ''); $ref = trim($_POST['transferRef'] ?? '');
            if (!isset($providers['wallets'][$w]) || $wn === '' || $ref === '') {
                $ok = false; $msg = 'أكمل بيانات المحفظة: رقم محفظتك ورقم مرجع العملية'; $msgType = 'danger';
            } else {
                $status = 'pending';
                $details = 'محفظة: ' . $providers['wallets'][$w] . ' | رقم المحوِّل: ' . $esc($wn) . ' | مرجع: ' . $esc($ref);
            }

        } elseif ($method === 'exchange') {
            $x = $_POST['exchange'] ?? ''; $sn = trim($_POST['senderName'] ?? ''); $ref = trim($_POST['transferRef2'] ?? '');
            if (!isset($providers['exchange'][$x]) || $sn === '' || $ref === '') {
                $ok = false; $msg = 'أكمل بيانات الحوالة: اسم المرسل ورقم المرجع'; $msgType = 'danger';
            } else {
                $status = 'pending';
                $details = 'صرافة: ' . $providers['exchange'][$x] . ' | المرسل: ' . $esc($sn) . ' | مرجع: ' . $esc($ref);
            }

        } elseif ($method === 'bnpl') {
            $b = $_POST['bnpl'] ?? '';
            if (!isset($providers['bnpl'][$b])) {
                $ok = false; $msg = 'اختر منصة التقسيط'; $msgType = 'danger';
            } else {
                $status = 'pending';
                $details = 'تقسيط عبر: ' . $providers['bnpl'][$b];
            }

        } elseif ($method === 'card') {
            $num  = preg_replace('/\s+/', '', $_POST['cardNumber'] ?? '');
            $cvc  = $_POST['cvc'] ?? '';
            $exp  = $_POST['expire'] ?? '';
            $name = $esc(trim($_POST['fullName'] ?? ''));
            if (!preg_match('/^\d{13,19}$/', $num) || !luhnOk($num) || !preg_match('/^\d{3,4}$/', $cvc) || !preg_match('#^\d{2}/\d{2}$#', $exp) || $name === '') {
                $ok = false; $msg = 'بيانات البطاقة غير صحيحة — تحقق من الرقم والتاريخ وCVC'; $msgType = 'danger';
            } else {
                $status = 'paid';
                $details = 'بطاقة **** ' . substr($num, -4);
            }

        } else { $ok = false; $msg = 'اختر طريقة دفع'; $msgType = 'danger'; }

        if ($ok) {
            $det_esc = $esc($details);
            mysqli_query($con_db, "INSERT INTO orders (u_id, total, status, payment_method, method_details) VALUES ($uid, $cartTotal, '$status', '$method', '$det_esc')");
            $orderId = mysqli_insert_id($con_db);

            $items = mysqli_query($con_db, "SELECT * FROM cart WHERE u_id = $uid");
            while ($it = mysqli_fetch_assoc($items)) {
                $pn = $esc($it['c_name']);
                mysqli_query($con_db, "INSERT INTO order_items (order_id, product_id, product_name, qty, price) VALUES ($orderId, " . (int)$it['id'] . ", '$pn', " . (int)$it['c_qty'] . ", " . (int)$it['c_price'] . ")");
            }
            mysqli_query($con_db, "DELETE FROM cart WHERE u_id = $uid");

            $labels = ['cod' => 'سيتم الدفع عند الاستلام 💵', 'pending' => 'بانتظار تأكيد التحويل ⏳', 'paid' => 'تم الدفع بنجاح 💳'];
            $msg = "شكرًا لك! طلبك رقم #$orderId — " . ($labels[$status] ?? '');
        }
    }
}
include("include/header.php");

$cartTotal = 0; $cartItems = [];
$res = mysqli_query($con_db, "SELECT * FROM cart WHERE u_id = $uid");
if ($res) { while ($c = mysqli_fetch_assoc($res)) { $cartItems[] = $c; $cartTotal += (int)$c['c_total']; } }
?>
<style>
.brand-pill { background:#e9ecef; color:#495057; padding:.5em .8em; border-radius:.6rem; font-weight:600; transition: all .25s; }
.brand-pill.active { background:var(--brand); color:#fff; transform: scale(1.12); box-shadow:0 4px 10px rgba(27,94,32,.35); }
.pay-sec { animation: fadeDown .4s ease; }
</style>

<div class="container my-4">
  <div class="row g-4">

    <!-- ملخص الطلب -->
    <div class="col-lg-5" data-aos="fade-left">
      <div class="card shadow border-0">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-receipt text-brand"></i> ملخص الطلب</h5>
          <?php if (empty($cartItems)): ?>
            <p class="text-muted">سلتك فارغة — <a href="index.php" class="text-brand">تصفح المنتجات</a></p>
          <?php else: ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($cartItems as $c): ?>
              <li class="list-group-item d-flex justify-content-between px-0 small">
                <span><?php echo htmlspecialchars($c['c_name'], ENT_QUOTES); ?> × <?php echo (int)$c['c_qty']; ?></span>
                <b><?php echo (int)$c['c_total']; ?>$</b>
              </li>
              <?php endforeach; ?>
            </ul>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
              <span>الإجمالي</span><span class="text-brand"><?php echo $cartTotal; ?>$</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- طرق الدفع -->
    <div class="col-lg-7" data-aos="fade-right">
      <div class="card shadow border-0">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-wallet2 text-brand"></i> اختر طريقة الدفع</h5>
          <?php if ($msg !== ''): ?>
            <div class="alert alert-<?php echo $msgType; ?> py-2"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
          <?php endif; ?>

          <form method="post">
            <?php echo csrf_field(); ?>
            <div class="row g-2 mb-3">
              <div class="col-6 col-lg">
                <input type="radio" class="btn-check" name="method" id="m-cod" value="cod" checked>
                <label class="btn btn-outline-success w-100 py-3" for="m-cod"><i class="bi bi-cash-stack d-block fs-4 mb-1"></i> عند الاستلام</label>
              </div>
              <div class="col-6 col-lg">
                <input type="radio" class="btn-check" name="method" id="m-wallet" value="wallet">
                <label class="btn btn-outline-success w-100 py-3" for="m-wallet"><i class="bi bi-phone d-block fs-4 mb-1"></i> محفظة يمنية</label>
              </div>
              <div class="col-6 col-lg">
                <input type="radio" class="btn-check" name="method" id="m-exchange" value="exchange">
                <label class="btn btn-outline-success w-100 py-3" for="m-exchange"><i class="bi bi-building d-block fs-4 mb-1"></i> صرافة / حوالة</label>
              </div>
              <div class="col-6 col-lg">
                <input type="radio" class="btn-check" name="method" id="m-card" value="card">
                <label class="btn btn-outline-success w-100 py-3" for="m-card"><i class="bi bi-credit-card d-block fs-4 mb-1"></i> بطاقة / عالمية</label>
              </div>
              <div class="col-6 col-lg">
                <input type="radio" class="btn-check" name="method" id="m-bnpl" value="bnpl">
                <label class="btn btn-outline-success w-100 py-3" for="m-bnpl"><i class="bi bi-hourglass-split d-block fs-4 mb-1"></i> تقسيط BNPL</label>
              </div>
            </div>

            <!-- 1) عند الاستلام -->
            <div id="sec-cod" class="pay-sec">
              <p class="text-muted small mb-0"><i class="bi bi-truck text-brand"></i> ادفع نقدًا عند استلام طلبك، وسيتم تأكيده هاتفيًا.</p>
            </div>

            <!-- 2) المحافظ اليمنية -->
            <div id="sec-wallet" class="pay-sec d-none">
              <label class="form-label">اختر المحفظة:</label>
              <select name="wallet" class="form-select mb-2">
                <?php foreach ($providers['wallets'] as $k => $w): ?>
                <option value="<?php echo $k; ?>"><?php echo htmlspecialchars($w, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="alert alert-light border small"><?php echo $storeInfo['wallet']; ?> — حوّل المبلغ ثم أدخل بيانات تحويلك:</div>
              <div class="form-floating mb-2">
                <input type="text" name="walletNumber" class="form-control" id="walletNumber" placeholder="رقم محفظتك">
                <label for="walletNumber">رقم محفظتك</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" name="transferRef" class="form-control" id="transferRef" placeholder="رقم مرجع العملية">
                <label for="transferRef">رقم مرجع العملية</label>
              </div>
            </div>

            <!-- 3) الصرافات -->
            <div id="sec-exchange" class="pay-sec d-none">
              <label class="form-label">اختر شركة الصرافة / البنك:</label>
              <select name="exchange" class="form-select mb-2">
                <?php foreach ($providers['exchange'] as $k => $x): ?>
                <option value="<?php echo $k; ?>"><?php echo htmlspecialchars($x, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="alert alert-light border small"><?php echo $storeInfo['exchange']; ?> — حوّل المبلغ ثم أدخل بيانات الحوالة:</div>
              <div class="form-floating mb-2">
                <input type="text" name="senderName" class="form-control" id="senderName" placeholder="اسم المرسل">
                <label for="senderName">اسم المرسل</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" name="transferRef2" class="form-control" id="transferRef2" placeholder="رقم مرجع الحوالة">
                <label for="transferRef2">رقم مرجع الحوالة</label>
              </div>
            </div>

            <!-- 4) البطاقات والعالمية -->
            <div id="sec-card" class="pay-sec d-none">
              <div class="d-flex flex-wrap gap-2 mb-3" id="brandRow">
                <span class="brand-pill" data-brand="visa">VISA</span>
                <span class="brand-pill" data-brand="mastercard">Mastercard</span>
                <span class="brand-pill" data-brand="amex">AMEX</span>
                <span class="brand-pill">مدى</span>
                <span class="brand-pill">KNET</span>
                <span class="brand-pill">Benefit</span>
                <span class="brand-pill">OmanNet</span>
                <span class="brand-pill">Qatar Pay</span>
                <span class="brand-pill">e&amp; money</span>
                <span class="brand-pill"><i class="bi bi-apple"></i> Apple Pay</span>
                <span class="brand-pill"><i class="bi bi-google"></i> Google Pay</span>
                <span class="brand-pill"><i class="bi bi-paypal"></i> PayPal</span>
              </div>
              <div class="form-floating mb-2">
                <input type="text" name="cardNumber" id="cardNumber" class="form-control" placeholder="رقم البطاقة" inputmode="numeric" autocomplete="cc-number">
                <label for="cardNumber">رقم البطاقة (يُكتشف نوعها تلقائيًا)</label>
              </div>
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <div class="form-floating">
                    <input type="text" name="expire" id="expire" class="form-control" placeholder="MM/YY" inputmode="numeric">
                    <label for="expire">تاريخ الانتهاء (MM/YY)</label>
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-floating">
                    <input type="password" name="cvc" id="cvc" class="form-control" placeholder="CVC" inputmode="numeric" maxlength="4" autocomplete="cc-csc">
                    <label for="cvc">CVC</label>
                  </div>
                </div>
              </div>
              <div class="form-floating mb-2">
                <input type="text" name="fullName" id="fullName" class="form-control" placeholder="الاسم على البطاقة" autocomplete="cc-name">
                <label for="fullName">الاسم على البطاقة</label>
              </div>
              <p class="small text-muted mb-0"><i class="bi bi-shield-lock text-brand"></i> وضع تعليمي (محاكاة بوابة) — لا تُدخل بطاقة حقيقية. بالإنتاج تُربط بوابات مثل Stripe/PayPal/مدى.</p>
            </div>

            <!-- 5) تقسيط -->
            <div id="sec-bnpl" class="pay-sec d-none">
              <label class="form-label">اختر منصة الشراء الآن والدفع لاحقًا:</label>
              <select name="bnpl" class="form-select mb-2">
                <?php foreach ($providers['bnpl'] as $k => $b): ?>
                <option value="<?php echo $k; ?>"><?php echo htmlspecialchars($b, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
              </select>
              <p class="text-muted small mb-0"><i class="bi bi-info-circle text-brand"></i> سيتم إنشاء الطلب كطلب تقسيط ويُستكمل عبر المنصة المختارة (محاكاة تعليمية).</p>
            </div>

            <button type="submit" name="submit" class="btn btn-brand btn-lg w-100 mt-3"><i class="bi bi-bag-check"></i> تأكيد الطلب</button>
            <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">العودة للسلة</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// تبديل الأقسام بحيوية
const secs = { cod:'sec-cod', wallet:'sec-wallet', exchange:'sec-exchange', card:'sec-card', bnpl:'sec-bnpl' };
document.querySelectorAll('input[name=method]').forEach(r => r.addEventListener('change', () => {
    Object.values(secs).forEach(id => document.getElementById(id).classList.add('d-none'));
    document.getElementById(secs[r.value]).classList.remove('d-none');
}));

// ===== برمجة رقم البطاقة: كشف الشبكة + تنسيق تلقائي =====
function detectBrand(v) {
    if (/^4/.test(v)) return 'visa';
    if (/^(5[1-5]|2[2-7])/.test(v)) return 'mastercard';
    if (/^3[47]/.test(v)) return 'amex';
    return '';
}
const cardIn = document.getElementById('cardNumber');
cardIn.addEventListener('input', () => {
    let v = cardIn.value.replace(/\D/g, '');
    const brand = detectBrand(v);

    if (brand === 'amex') {           // Amex: 15 رقمًا بتنسيق 4-6-5
        v = v.substring(0, 15);
        cardIn.value = [v.slice(0,4), v.slice(4,10), v.slice(10,15)].filter(s => s).join(' ');
    } else {                          // الباقي: مجموعات رباعية حتى 16
        v = v.substring(0, 16);
        cardIn.value = v.replace(/(.{4})/g, '$1 ').trim();
    }
    document.querySelectorAll('#brandRow .brand-pill').forEach(p =>
        p.classList.toggle('active', p.dataset.brand === brand && brand !== ''));
});

// تنسيق التاريخ MM/YY
const expIn = document.getElementById('expire');
expIn.addEventListener('input', () => {
    const v = expIn.value.replace(/\D/g, '').substring(0, 4);
    expIn.value = v.length > 2 ? v.substring(0, 2) + '/' + v.substring(2) : v;
});
// CVC أرقام فقط
const cvcIn = document.getElementById('cvc');
cvcIn.addEventListener('input', () => { cvcIn.value = cvcIn.value.replace(/\D/g, ''); });
</script>

<?php
include("include/footer.php");
?>