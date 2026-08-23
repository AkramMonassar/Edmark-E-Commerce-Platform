<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    require("connection/connection.php");

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';

    if ($name === '' || $email === '' || $pass === '') {
        $err = 'كل الحقول مطلوبة';
    } else {
        $name_esc  = mysqli_real_escape_string($con_db, $name);
        $email_esc = mysqli_real_escape_string($con_db, $email);
        $pass_md5  = md5($pass);
        $type      = 'user';

        $query = "INSERT INTO users (u_name, u_email, u_pass, u_type)
                  VALUES ('$name_esc', '$email_esc', '$pass_md5', '$type')";

        if (mysqli_query($con_db, $query)) {
            header("Location: login.php?added=1");
            exit;
        } else {
            $err = 'خطأ: ' . mysqli_error($con_db);
        }
    }
}
include("include/header.php");
?>
<center>
<h3>انشاء حساب جديد</h3>
<?php if (!empty($err)) echo "<p style='color:red'>$err</p>"; ?>
<form method="post">
<p>الاسم:</p>
<input type="text" name="name" placeholder="Enter user name" required>
<p>الإيميل:</p>
<input type="email" name="email" placeholder="Enter your email" required>
<p>كلمة المرور:</p>
<input type="password" name="pass" placeholder="Enter your password" required>
<br/><br/>
<input type="submit" name="add" value="add">
</form>
</center>
<?php
include("include/footer.php");
?>