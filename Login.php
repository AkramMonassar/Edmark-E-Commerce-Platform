<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    require("connection/connection.php");

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'يرجى إدخال الإيميل وكلمة المرور';
    } else {
        $email_esc = mysqli_real_escape_string($con_db, $email);
        $pass_md5  = md5($pass);
        $pass_esc  = mysqli_real_escape_string($con_db, $pass);

        $query  = "SELECT * FROM users WHERE u_email = '$email_esc' AND (u_pass = '$pass_md5' OR u_pass = '$pass_esc') LIMIT 1";
        $result = mysqli_query($con_db, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user']    = $row['u_name'];
            $_SESSION['u_email'] = $row['u_email'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'الإيميل أو كلمة المرور غير صحيحة';
        }
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