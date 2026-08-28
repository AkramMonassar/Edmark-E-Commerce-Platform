<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

$msg = ''; $msgType = 'info';
$debug = ''; // 🩺 تشخيص مؤقت — احذفه بعد ما تحل المشكلة

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$validToken = null;
$expiresAt = null;

// ✅ التحقق بوقت PHP فقط (لا يعتمد على ساعة MySQL)
if ($token !== '') {
    $te = mysqli_real_escape_string($con_db, $token);
    $rowT = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT u_id, u_name, reset_expires FROM users WHERE reset_token = '$te' LIMIT 1"));
    if ($rowT) {
        if (strtotime($rowT['reset_expires']) > time()) {
            $validToken = $rowT;
            $expiresAt = strtotime($rowT['reset_expires']);
        } else {
            $debug = 'الرمز موجود لكنه منتهي: مخزّن=' . $rowT['reset_expires'] . ' / الآن=' . date('Y-m-d H:i:s');
        }
    } else {
        $debug = 'الرمز غير موجود بالجدول أصلًا: إما طلب أحدثُ استبدله، أو لم يُحفظ عند الإرسال.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['request_reset'])) {
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $ee = mysqli_real_escape_string($con_db, $email);
            $u = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT u_id, u_name FROM users WHERE u_email = '$ee' LIMIT 1"));
            if ($u) {
                $tok = bin2hex(random_bytes(16));
                $exp = date('Y-m-d H:i:s', time() + 86400);
                if (!mysqli_query($con_db, "UPDATE users SET reset_token = '$tok', reset_expires = '$exp' WHERE u_id = " . (int)$u['u_id'])) {
                    error_log('reset update failed: ' . mysqli_error($con_db));
                }
                try {
                    require_once __DIR__ . '/include/mailer.php';
                    $base = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                    $body = "<div dir='rtl' style='font-family:Tahoma,sans-serif;max-width:600px;margin:auto;background:#f8f9fa;padding:20px;border-radius:12px'>
                        <h2 style='color:#2e7d32'>🔑 استعادة كلمة المرور</h2>
                        <p>وصلتنا طلب استعادة لحسابك. اضغط الزر أدناه خلال 24 ساعة:</p>
                        <p style='text-align:center'><a href='$base/reset.php?token=$tok' style='background:#2e7d32;color:#fff;padding:12px 30px;border-radius:8px;text-decoration:none'>تعيين كلمة مرور جديدة</a></p>
                        <p style='color:#888;font-size:12px'>إذا لم تطلب هذا، تجاهل الرسالة.</p>
                    </div>";
                    send_mail($email, $u['u_name'], 'استعادة كلمة المرور — متجر إدمارك', $body);
                } catch (\Throwable $e) {
                    error_log('reset mail: ' . $e->getMessage());
                }
            }
        }
        $msg = 'إذا كان الإيميل مسجلًا لدينا، فقد أُرسل رابط استعادة صالح لـ 24 ساعة.';
        $msgType = 'success';
    }

    if (isset($_POST['set_new'])) {
        $pass = $_POST['pass'] ?? '';
        if ($validToken && strlen($pass) >= 6) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($con_db, "UPDATE users SET u_pass = '$hash', reset_token = NULL, reset_expires = NULL WHERE u_id = " . (int)$validToken['u_id']);
            header('Location: login.php?reset=1');
            exit;
        }
        $msg = 'الرابط غير صالح أو منتهي، أو كلمة المرور أقل من 6 أحرف.';
        $msgType = 'danger';
    }
}
include("include/header.php");
?>
<style>
/* ===== عداد تنازلي احترافي ===== */
.countdown {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 15px 0;
    direction: ltr;
}
.countdown .unit {
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    color: #fff;
    border-radius: 10px;
    padding: 10px 14px;
    min-width: 60px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(27,94,32,.25);
}
.countdown .num {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.countdown .lbl {
    font-size: .7rem;
    opacity: .85;
    margin-top: 4px;
    text-transform: uppercase;
}
.countdown.danger .unit { background: linear-gradient(135deg, #e53935, #b71c1c); animation: pulse .8s infinite; }
.countdown.expired { filter: grayscale(1); opacity: .6; }
.countdown .sep {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--brand);
    line-height: 2.3;
}
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4" data-aos="zoom-in">
      <div class="card shadow border-0 mt-4 mb-5">
        <div class="card-body p-4 text-center">
          <div class="mb-3"><i class="bi bi-key text-brand" style="font-size:3.5rem"></i></div>
          <h4 class="mb-3">استعادة كلمة المرور</h4>

          <?php if ($msg !== ''): ?>
            <div class="alert alert-<?php echo $msgType; ?> py-2 small"><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></div>
          <?php endif; ?>

          <?php if ($token !== '' && !$validToken): ?>
            <p class="text-danger small">هذا الرابط غير صالح أو منتهي الصلاحية.</p>
            <?php if ($debug !== ''): ?>
              <div class="alert alert-warning py-2 small text-start">🩺 تشخيص مؤقت:<br><?php echo htmlspecialchars($debug, ENT_QUOTES); ?></div>
            <?php endif; ?>
            <a href="reset.php" class="btn btn-outline-secondary btn-sm">طلب رابط جديد</a>
          <?php elseif ($validToken): ?>
            <!-- العداد التنازلي -->
            <div class="countdown" id="countdown" data-expires="<?php echo $expiresAt; ?>">
              <div class="unit"><span class="num" id="cd-d">0</span><span class="lbl">يوم</span></div>
              <span class="sep">:</span>
              <div class="unit"><span class="num" id="cd-h">0</span><span class="lbl">ساعة</span></div>
              <span class="sep">:</span>
              <div class="unit"><span class="num" id="cd-m">0</span><span class="lbl">دقيقة</span></div>
              <span class="sep">:</span>
              <div class="unit"><span class="num" id="cd-s">0</span><span class="lbl">ثانية</span></div>
            </div>
            <p class="small text-muted mb-3" id="cd-msg">الوقت المتبقي لاستخدام هذا الرابط</p>

            <form method="post" id="resetForm">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
              <div class="form-floating mb-3">
                <input type="password" name="pass" class="form-control" id="newpass" required minlength="6">
                <label for="newpass">كلمة المرور الجديدة</label>
              </div>
              <button type="submit" name="set_new" class="btn btn-brand w-100" id="submitBtn">حفظ كلمة المرور</button>
            </form>
          <?php else: ?>
            <form method="post">
              <?php echo csrf_field(); ?>
              <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" id="remail" required>
                <label for="remail">الإيميل المسجل</label>
              </div>
              <button type="submit" name="request_reset" class="btn btn-brand w-100">أرسل رابط الاستعادة</button>
            </form>
          <?php endif; ?>
          <p class="small mt-3 mb-0"><a href="login.php" class="text-brand">العودة لتسجيل الدخول</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ===== العداد التنازلي الحي =====
(function() {
    const cd = document.getElementById('countdown');
    if (!cd) return;
    const expires = parseInt(cd.dataset.expires, 10) * 1000;
    const d = document.getElementById('cd-d');
    const h = document.getElementById('cd-h');
    const m = document.getElementById('cd-m');
    const s = document.getElementById('cd-s');
    const msg = document.getElementById('cd-msg');
    const btn = document.getElementById('submitBtn');

    const pad = n => String(n).padStart(2, '0');

    const tick = () => {
        const diff = expires - Date.now();
        if (diff <= 0) {
            d.textContent = '00'; h.textContent = '00'; m.textContent = '00'; s.textContent = '00';
            cd.classList.add('expired');
            msg.innerHTML = '<span class="text-danger">⏰ انتهى وقت هذا الرابط — اطلب رابطًا جديدًا</span>';
            if (btn) btn.disabled = true;
            clearInterval(timer);
            return;
        }
        const dd = Math.floor(diff / 86400000);
        const hh = Math.floor((diff % 86400000) / 3600000);
        const mm = Math.floor((diff % 3600000) / 60000);
        const ss = Math.floor((diff % 60000) / 1000);
        d.textContent = pad(dd); h.textContent = pad(hh); m.textContent = pad(mm); s.textContent = pad(ss);

        // تحذير أحمر عند آخر 5 دقائق
        if (diff < 5 * 60 * 1000) cd.classList.add('danger'); else cd.classList.remove('danger');
    };

    tick();
    const timer = setInterval(tick, 1000);
})();
</script>
<?php
include("include/footer.php");
?>