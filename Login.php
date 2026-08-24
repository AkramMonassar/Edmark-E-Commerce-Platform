<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    csrf_check();
    require("connection/connection.php");

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'يرجى إدخال الإيميل وكلمة المرور';
    } else {
        // ✅ Prepared Statement: يمنع SQL Injection نهائيًا
        $stmt = mysqli_prepare($con_db, "SELECT u_id, u_name, u_email, u_pass FROM users WHERE u_email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($row) {
            $ok = false;

            if (password_verify($pass, $row['u_pass'])) {
                $ok = true; // حساب محدّث (bcrypt)
            } elseif ($row['u_pass'] === md5($pass) || $row['u_pass'] === $pass) {
                // حساب قديم (md5 أو نص صريح): سجّل دخول وارتقِ بالتشفير تلقائيًا
                $ok = true;
                $newHash = password_hash($pass, PASSWORD_DEFAULT);
                $up = mysqli_prepare($con_db, "UPDATE users SET u_pass = ? WHERE u_id = ?");
                mysqli_stmt_bind_param($up, "si", $newHash, $row['u_id']);
                mysqli_stmt_execute($up);
            }

            if ($ok) {
                session_regenerate_id(true); // يمنع Session Fixation
                $_SESSION['user']    = $row['u_name'];
                $_SESSION['u_email'] = $row['u_email'];
                header("Location: index.php");
                exit;
            }
        }
        $error = 'الإيميل أو كلمة المرور غير صحيحة';
    }
}
include("include/header.php");
?>
<center>
<h3>تسجيل الدخول</h3>
<?php
if (isset($_GET['added'])) echo "<p style='color:green'>تم إنشاء الحساب بنجاح، سجل دخولك الآن</p>";
if ($error !== '') echo "<p style='color:red'>$error</p>";
?>
<form method="post">
<?php echo csrf_field(); ?>    
<p>الإيميل:</p>
<input type="email" name="email" required>
<p>كلمة المرور:</p>
<input type="password" name="pass" required>
<br/><br/>
<input type="submit" name="login" value="دخول">
</form>
<p>اذا لم تملك حساب فسجل حسابك: <a href="create_acount.php">إنشاء حساب</a></p>
</center>
<?php
include("include/footer.php");
?>