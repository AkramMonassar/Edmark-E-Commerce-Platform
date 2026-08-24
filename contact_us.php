<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';

$sent = false;
if (isset($_POST['ارسال'])) {
    csrf_check();
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name !== '' && $email !== '') $sent = true;
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
                        <div class="alert alert-success text-center">شكرًا لتواصلك معنا! تم استلام رسالتك بنجاح 💚</div>
                    <?php else: ?>
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
                                <textarea name="subject" class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" name="ارسال" class="btn btn-brand w-100">إرسال <i class="bi bi-send"></i></button>
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