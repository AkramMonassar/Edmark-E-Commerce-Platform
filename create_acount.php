<?php
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    require("connection/connection.php");
    require_once __DIR__ . '/include/csrf.php';
    csrf_check();

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';

    if ($name === '') $errors[] = 'الاسم مطلوب';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'صيغة الإيميل غير صالحة';
    if (strlen($pass) < 6) $errors[] = 'كلمة المرور 6 أحرف على الأقل';

    if (empty($errors)) {
        $chk = mysqli_prepare($con_db, "SELECT u_id FROM users WHERE u_email = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $errors[] = 'هذا الإيميل مسجل مسبقاً';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $type = 'user';
            $ins = mysqli_prepare($con_db, "INSERT INTO users (u_name, u_email, u_pass, u_type) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $name, $email, $hash, $type);
            if (mysqli_stmt_execute($ins)) {
                header("Location: Login.php?added=1");
                exit;
            } else {
                $errors[] = 'خطأ أثناء الحفظ: ' . mysqli_error($con_db);
            }
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
                    <div class="mb-3"><i class="bi bi-person-plus text-brand" style="font-size:3.5rem"></i></div>
                    <h4 class="mb-3">إنشاء حساب جديد</h4>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars(implode(' — ', $errors), ENT_QUOTES); ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <?php echo csrf_field(); ?>
                        <div class="form-floating mb-3">
                            <input type="text" name="name" class="form-control" id="name" placeholder="الاسم" required>
                            <label for="name">الاسم</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="الإيميل" required>
                            <label for="email">الإيميل</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="pass" class="form-control" id="pass" placeholder="كلمة المرور" required>
                            <label for="pass">كلمة المرور</label>
                        </div>
                        <button type="submit" name="add" class="btn btn-brand w-100">إنشاء الحساب <i class="bi bi-check-circle"></i></button>
                    </form>
                    <p class="small mt-3 mb-0">عندك حساب؟ <a href="Login.php" class="text-brand fw-bold">سجل دخولك</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include("include/footer.php");
?>