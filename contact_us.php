<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ارسال'])) {
    csrf_check();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'الرجاء إدخال اسم وإيميل صحيح';
    } elseif (strlen($subject) > 2000) {
        $error = 'الرسالة طويلة جدًا';
    } else {
        // ===== إرسال البريد =====
        require __DIR__ . '/vendor/autoload.php';   // لو حملت يدويًا استخدم المسار الصحيح
        $cfg = require __DIR__ . '/config/email.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'];
            $mail->Port       = $cfg['port'];

            $mail->setFrom($cfg['username'], $cfg['from_name']);
            $mail->addAddress($cfg['to_email'], $cfg['to_name']);
            $mail->addReplyTo($email, $name);        // زر الرد يرد على العميل مباشرة

            $mail->isHTML(true);
            $mail->Subject = 'رسالة جديدة من نموذج التواصل-متجر ادمارك: ' . mb_substr($subject, 0, 80);

            $safe = fn($v) => htmlspecialchars($v, ENT_QUOTES);
            $mail->Body = "
                <div dir='rtl' style='font-family:Tahoma,sans-serif;max-width:600px;margin:auto;background:#f8f9fa;padding:20px;border-radius:10px'>
                    <h2 style='color:#2e7d32'>📬 رسالة جديدة من نموذج التواصل-متجر ادمارك</h2>
                    <table style='width:100%;border-collapse:collapse;background:#fff;padding:15px;border-radius:8px'>
                        <tr><td style='padding:8px;font-weight:bold;color:#1b5e20'>الاسم:</td><td style='padding:8px'>{$safe($name)}</td></tr>
                        <tr><td style='padding:8px;font-weight:bold;color:#1b5e20'>الإيميل:</td><td style='padding:8px'><a href='mailto:{$safe($email)}'>{$safe($email)}</a></td></tr>
                        <tr><td style='padding:8px;font-weight:bold;color:#1b5e20'>الهاتف:</td><td style='padding:8px'>{$safe($phone ?: '—')}</td></tr>
                        <tr><td colspan='2' style='padding:12px 8px;font-weight:bold;color:#1b5e20;border-top:1px solid #eee'>الرسالة:</td></tr>
                        <tr><td colspan='2' style='padding:12px 8px;background:#f1f8e9;white-space:pre-wrap'>{$safe($subject)}</td></tr>
                    </table>
                    <p style='text-align:center;color:#888;font-size:12px;margin-top:15px'>أُرسلت من متجر إدمارك - " . date('Y-m-d H:i') . "</p>
                </div>
            ";
            $mail->AltBody = "رسالة من: $name ($email)\nالهاتف: $phone\n\nالرسالة:\n$subject";

            $mail->send();
            $sent = true;
        } catch (PHPMailer\PHPMailer\Exception $e) {
            $error = 'تعذر إرسال الرسالة، حاول لاحقًا';
            error_log('Mailer Error: ' . $mail->ErrorInfo);
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
                        <i class="bi bi-envelope-heart text-brand" style="font-size:3.5rem"></i>
                        <h4>تواصل معنا</h4>
                    </div>
                    <?php if ($sent): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle"></i> شكرًا لتواصلك معنا! وصلتنا رسالتك بنجاح 💚
                        </div>
                        <a href="index.php" class="btn btn-brand w-100 mt-2">العودة للرئيسية</a>
                    <?php else: ?>
                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
                        <?php endif; ?>
                        <form method="post" action="contact_us.php">
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
                                <input type="text" name="phone" class="form-control" id="phone" placeholder="الهاتف">
                                <label for="phone">الهاتف (اختياري)</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الموضوع</label>
                                <textarea name="subject" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" name="ارسال" class="btn btn-brand w-100">
                                <i class="bi bi-send"></i> إرسال
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include("include/footer.php");
?>