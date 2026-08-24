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
        // ✅ فحص تكرار الإيميل بـ Prepared Statement
        $chk = mysqli_prepare($con_db, "SELECT u_id FROM users WHERE u_email = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);

        if (mysqli_stmt_num_rows($chk) > 0) {
            $errors[] = 'هذا الإيميل مسجل مسبقاً';
        } else {
            // ✅ تشفير bcrypt بدل md5
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $type = 'user';
            $ins = mysqli_prepare($con_db, "INSERT INTO users (u_name, u_email, u_pass, u_type) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $name, $email, $hash, $type);

            if (mysqli_stmt_execute($ins)) {
                header("Location: login.php?added=1");
                exit;
            } else {
                $errors[] = 'خطأ أثناء الحفظ: ' . mysqli_error($con_db);
            }
        }
    }
}
include("include/header.php");
?>
<center>
<h3>انشاء حساب جديد</h3>
<?php
if (!empty($errors)) {
    echo "<p style='color:red'>" . implode('<br>', $errors) . "</p>";
}
?>
<form method="post">
<?php echo csrf_field(); ?>
<p>الاسم:</p>
<input type="text" name="name" required>
<p>الإيميل:</p>
<input type="email" name="email" required>
<p>كلمة المرور:</p>
<input type="password" name="pass" required>
<br/><br/>
<input type="submit" name="add" value="add">
</form>
</center>
<?php
include("include/footer.php");
?>