<?php
include("include/header.php");
?>
<html>
<head>
    <meta name="description" content="Free Web tutorials">
    <meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="Hege Refsnes">
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="contact_us.css">
<style>
</style>     
</head>
<body>
<?php
if(isset($_SESSION['user'])) {
    echo htmlspecialchars($_SESSION['user'], ENT_QUOTES) . "<br/>";
}
?>   
<div id="page">
<?php
// معالجة النموذج عند الإرسال
$message_sent = false;
if(isset($_POST['ارسال'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    
    if($name !== '' && $email !== '') {
        // هنا تقدر تحفظ الرسالة في قاعدة البيانات أو ترسل إيميل
        // حالياً فقط نعرض رسالة تأكيد
        $message_sent = true;
    } else {
        echo "<p style='color:red;'>الرجاء تعبئة الحقول المطلوبة (الاسم والإيميل)</p>";
    }
}

if($message_sent) {
    echo "<h3 style='color:green;'>شكراً لتواصلك معنا! تم استلام رسالتك بنجاح.</h3>";
} else {
    echo '    
    <form action="contact_us.php" method="post">
    <h4> الاسم:</h4>
    <input type="text" name="name" required>

    <h4> الإيميل:</h4>
    <input type="email" name="email" required>

    <h4> الهاتف:</h4>
    <input type="text" name="phone">

    <h4> الموضوع:</h4>
    <textarea name="subject" style="width:100%;height:200px;"></textarea>
    <br/><br/>
    <input type="submit" name="ارسال" value="إرسال">        
    </form>';
}
?>    
</div>   
</body>
</html>
<?php
include("include/footer.php");
?>