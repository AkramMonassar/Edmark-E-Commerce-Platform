<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/include/rate_limit.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    csrf_check();
    require("connection/connection.php");

    if (!rate_limit_check('login', 5, 300)) {
        $error = 'تجاوزت عدد محاولات تسجيل الدخول. انتظر 5 دقائق ثم حاول مرة أخرى.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['pass'] ?? '';

        if ($email === '' || $pass === '') {
            $error = 'يرجى إدخال الإيميل وكلمة المرور';
        } else {
            $stmt = mysqli_prepare($con_db, "SELECT u_id, u_name, u_email, u_pass, u_type FROM users WHERE u_email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($row) {
                $ok = false;

                if (password_verify($pass, $row['u_pass'])) {
                    $ok = true;
                } elseif ($row['u_pass'] === md5($pass) || $row['u_pass'] === $pass) {
                    $ok = true;

                    $newHash = password_hash($pass, PASSWORD_DEFAULT);
                    $up = mysqli_prepare($con_db, "UPDATE users SET u_pass = ? WHERE u_id = ?");
                    mysqli_stmt_bind_param($up, "si", $newHash, $row['u_id']);
                    mysqli_stmt_execute($up);
                }

                if ($ok) {
                    rate_limit_reset('login');

                    session_regenerate_id(true);
                    $_SESSION['u_id']    = (int)$row['u_id'];
                    $_SESSION['user']    = $row['u_name'];
                    $_SESSION['u_email'] = $row['u_email'];
                    $_SESSION['u_type']  = $row['u_type'] ?? 'user';

                    header("Location: index.php");
                    exit;
                }
            }

            $error = 'الإيميل أو كلمة المرور غير صحيحة';
        }
    }
}
include("include/header.php");
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4" data-aos="zoom-in">
            <div class="card shadow border-0 mt-4 mb-5">
                <div class="card-body p-4 text-center">

                    <div class="mb-3"><i class="bi bi-person-circle text-brand" style="font-size:3.5rem"></i></div>
                    <h4 class="mb-3">تسجيل الدخول</h4>

                    <?php if (isset($_GET['added'])): ?>
                        <div class="alert alert-success py-2 small">تم إنشاء الحساب بنجاح، سجل دخولك الآن</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['reset'])): ?>
                        <div class="alert alert-success py-2 small">تم تغيير كلمة المرور، سجل دخولك الآن</div>
                    <?php endif; ?>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <?php echo csrf_field(); ?>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="الإيميل" required>
                            <label for="email">الإيميل</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="pass" class="form-control" id="pass" placeholder="كلمة المرور" required>
                            <label for="pass">كلمة المرور</label>
                        </div>
                        <button type="submit" name="login" class="btn btn-brand w-100">دخول <i class="bi bi-box-arrow-in-left"></i></button>
                    </form>
                    <p class="small mt-2 mb-0"><a href="reset.php" class="text-muted">نسيت كلمة المرور؟</a></p>
                    <p class="small mt-3 mb-0">ما عندك حساب؟ <a href="create_acount.php" class="text-brand fw-bold">أنشئ حسابًا</a></p>

                </div>
            </div>
        </div>
    </div>
</div>
<?php
include("include/footer.php");
?>