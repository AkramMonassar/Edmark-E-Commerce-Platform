<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

// ===== حماية: أدمن فقط =====
if (!isset($_SESSION['u_id'])) {
  header("Location: login.php");
  exit;
}
$uid = (int) $_SESSION['u_id'];
$st = mysqli_prepare($con_db, "SELECT u_type FROM users WHERE u_id = ?");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$me = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (($me['u_type'] ?? '') !== 'admin') {
  header("Location: index.php");
  exit;
}

$tab = $_GET['tab'] ?? 'stats';

// ===== معالجة العمليات (PRG) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';
  $flash = '';

  if ($action === 'confirm_order') {
    $oid = (int) $_POST['order_id'];
    mysqli_query($con_db, "UPDATE orders SET status='confirmed' WHERE order_id=$oid");
    // إشعار تأكيد للعميل
    try {
      require_once __DIR__ . '/include/mailer.php';
      $o = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT o.*, u.u_email, u.u_name FROM orders o LEFT JOIN users u ON u.u_id = o.u_id WHERE o.order_id = $oid"));
      if ($o && !empty($o['u_email'])) {
        $cname = htmlspecialchars($o['customer_name'] ?: $o['u_name'], ENT_QUOTES);
        $body = "<div dir='rtl' style='font-family:Tahoma,sans-serif;max-width:600px;margin:auto;background:#f8f9fa;padding:20px;border-radius:12px'>
                    <h2 style='color:#2e7d32'>✅ طلبك #$oid تم تأكيده</h2>
                    <p>عزيزنا <b>$cname</b>،</p>
                    <p>تم تأكيد طلبك وهو الآن <b>قيد التجهيز</b>. الإجمالي: <b>" . (int)$o['total'] . "$</b>.</p>
                    <p>سنتواصل معك على <b>" . htmlspecialchars($o['customer_phone'] ?? '', ENT_QUOTES) . "</b> لتنسيق التوصيل إلى: " . htmlspecialchars(($o['city'] ?? '') . ' - ' . ($o['address'] ?? ''), ENT_QUOTES) . "</p>
                    <p style='color:#888;font-size:12px;text-align:center'>رسالة آلية من متجر إدمارك</p>
                </div>";
        send_mail($o['u_email'], $o['customer_name'] ?: $o['u_name'], "✅ تم تأكيد طلبك #$oid — متجر إدمارك", $body);
        $flash = "تم تأكيد الطلب #$oid وإرسال إشعار للعميل 📧";
      } else {
        $flash = "تم تأكيد الطلب #$oid";
      }
    } catch (\Throwable $e) {
      error_log('Confirm email failed: ' . $e->getMessage());
      $flash = "تم تأكيد الطلب #$oid (تعذر إرسال الإشعار)";
    }
    $tab = 'orders';
  } elseif ($action === 'cancel_order') {
    $oid = (int) $_POST['order_id'];
    $o = mysqli_fetch_assoc(mysqli_query($con_db, "SELECT status FROM orders WHERE order_id = $oid"));
    if ($o && $o['status'] !== 'cancelled') {
      $its = mysqli_query($con_db, "SELECT product_id, qty FROM order_items WHERE order_id = $oid");
      while ($it = mysqli_fetch_assoc($its)) {
        $pid2 = (int)$it['product_id'];
        $q2 = (int)$it['qty'];
        mysqli_query($con_db, "UPDATE product SET p_quantity = p_quantity + $q2 WHERE p_id = $pid2");
      }
      mysqli_query($con_db, "UPDATE orders SET status='cancelled' WHERE order_id = $oid");
      $flash = "تم إلغاء الطلب #$oid وإرجاع الكميات للمخزون ♻️";
    }
    $tab = 'orders';
  } elseif ($action === 'add_product') {
    $name  = trim($_POST['p_name'] ?? '');
    $price = (int) ($_POST['p_price'] ?? 0);
    $qty   = (int) ($_POST['p_quantity'] ?? 0);
    $desc  = trim($_POST['p_describe'] ?? '');
    if ($name === '' || $price <= 0) {
      $flash = 'أدخل اسم المنتج وسعرًا صحيحًا';
      $tab = 'products';
    } else {
      $img = 'photo/1.png';
      if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExt  = ['png', 'jpg', 'jpeg', 'webp'];
        $allowedMime = ['image/png', 'image/jpeg', 'image/webp'];
        $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['image']['tmp_name']);
        if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
          $new = 'photo/' . bin2hex(random_bytes(8)) . '.' . $ext;
          move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $new);
          $img = $new;
        } else {
          $flash = 'تنبيه: الصورة غير صالحة (نوع/حجم) — استُخدمت صورة افتراضية. ';
        }
      }
      $ne = mysqli_real_escape_string($con_db, $name);
      $de = mysqli_real_escape_string($con_db, $desc);
      mysqli_query($con_db, "INSERT INTO product (p_name, p_quantity, p_price, p_describe, p_img) VALUES ('$ne', $qty, $price, '$de', '$img')");
      $flash .= "تمت إضافة المنتج ✅";
      $tab = 'products';
    }
  } elseif ($action === 'edit_product') {
    $pid   = (int) ($_POST['p_id'] ?? 0);
    $name  = mysqli_real_escape_string($con_db, trim($_POST['p_name'] ?? ''));
    $price = (int) ($_POST['p_price'] ?? 0);
    $qty   = (int) ($_POST['p_quantity'] ?? 0);
    $desc  = mysqli_real_escape_string($con_db, trim($_POST['p_describe'] ?? ''));
    mysqli_query($con_db, "UPDATE product SET p_name='$name', p_price=$price, p_quantity=$qty, p_describe='$desc' WHERE p_id=$pid");
    $flash = "تم تحديث المنتج #$pid ✅";
    $tab = 'products';
  } elseif ($action === 'delete_user') {
    $tid = (int) ($_POST['user_id'] ?? 0);
    if ($tid !== $uid) {
      mysqli_query($con_db, "DELETE FROM users WHERE u_id=$tid AND (u_type IS NULL OR u_type != 'admin')");
      $flash = mysqli_affected_rows($con_db) > 0
        ? "تم حذف المستخدم #$tid ✅"
        : "تعذر حذف المستخدم #$tid — غير موجود أو حساب أدمن";
    }
    $tab = 'users';
  } elseif ($action === 'toggle_admin') {
    $tid = (int) ($_POST['user_id'] ?? 0);
    if ($tid !== $uid) {
      mysqli_query($con_db, "UPDATE users SET u_type = IF(u_type='admin','user','admin') WHERE u_id=$tid");
      $flash = "تم تغيير صلاحية المستخدم #$tid";
    }
    $tab = 'users';
  } elseif ($action === 'delete_user') {
    $tid = (int) ($_POST['user_id'] ?? 0);
    if ($tid !== $uid) {
      mysqli_query($con_db, "DELETE FROM users WHERE u_id=$tid AND u_type!='admin'");
      $flash = "تم حذف المستخدم #$tid";
    }
    $tab = 'users';
  }

  header("Location: admin.php?tab=$tab" . ($flash !== '' ? '&msg=' . urlencode($flash) : ''));
  exit;
}

$flash = htmlspecialchars($_GET['msg'] ?? '', ENT_QUOTES);
include("include/header.php");

// ===== بيانات اللوحة =====
$cnt = fn($q) => ($r = mysqli_fetch_assoc(mysqli_query($con_db, $q))) ? (int) array_values($r)[0] : 0;
$sUsers    = $cnt("SELECT COUNT(*) FROM users");
$sProducts = $cnt("SELECT COUNT(*) FROM product");
$sOrders   = $cnt("SELECT COUNT(*) FROM orders");
$sPending  = $cnt("SELECT COUNT(*) FROM orders WHERE status='pending' OR status='cod'");
$sRevenue  = $cnt("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('paid','confirmed')");

$orders  = mysqli_query($con_db, "SELECT o.*, u.u_name FROM orders o LEFT JOIN users u ON u.u_id = o.u_id ORDER BY o.order_id DESC");
$products = mysqli_query($con_db, "SELECT * FROM product ORDER BY p_id");
$users   = mysqli_query($con_db, "SELECT * FROM users ORDER BY u_id");

$badge = ['pending' => 'warning', 'paid' => 'success', 'confirmed' => 'info', 'cod' => 'secondary', 'cancelled' => 'danger'];
$label = ['pending' => 'بانتظار التأكيد', 'paid' => 'مدفوع بطاقة', 'confirmed' => 'مؤكد', 'cod' => 'عند الاستلام', 'cancelled' => 'ملغي'];
?>

<div class="container my-4">
  <h4 class="mb-4" data-aos="fade-right"><i class="bi bi-speedometer2 text-brand"></i> لوحة التحكم</h4>

  <?php if ($flash !== ''): ?><div class="alert alert-light border py-2"><?php echo $flash; ?></div><?php endif; ?>

  <ul class="nav nav-pills gap-2 mb-4 flex-wrap">
    <li class="nav-item"><a class="nav-link <?php echo $tab === 'stats' ? 'active bg-brand' : ''; ?>" href="admin.php?tab=stats">📊 الإحصائيات</a></li>
    <li class="nav-item"><a class="nav-link" href="reports.php">📈 التقارير</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab === 'orders' ? 'active bg-brand' : ''; ?>" href="admin.php?tab=orders">🧾 الطلبات <?php if ($sPending > 0): ?><span class="badge text-bg-warning"><?php echo $sPending; ?></span><?php endif; ?></a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab === 'products' ? 'active bg-brand' : ''; ?>" href="admin.php?tab=products">📦 المنتجات</a></li>
    <li class="nav-item"><a class="nav-link <?php echo $tab === 'users' ? 'active bg-brand' : ''; ?>" href="admin.php?tab=users">👥 المستخدمون</a></li>
  </ul>

  <!-- ===== الإحصائيات ===== -->
  <?php if ($tab === 'stats'): ?>
    <div class="row g-3">
      <div class="col-6 col-lg" data-aos="fade-up">
        <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-people fs-3 text-brand"></i>
          <h4 class="mt-2 mb-0"><?php echo $sUsers; ?></h4><small class="text-muted">مستخدم</small>
        </div>
      </div>
      <div class="col-6 col-lg" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-box-seam fs-3 text-brand"></i>
          <h4 class="mt-2 mb-0"><?php echo $sProducts; ?></h4><small class="text-muted">منتج</small>
        </div>
      </div>
      <div class="col-6 col-lg" data-aos="fade-up" data-aos-delay="200">
        <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-bag fs-3 text-brand"></i>
          <h4 class="mt-2 mb-0"><?php echo $sOrders; ?></h4><small class="text-muted">طلب</small>
        </div>
      </div>
      <div class="col-6 col-lg" data-aos="fade-up" data-aos-delay="300">
        <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-hourglass-split fs-3 text-warning"></i>
          <h4 class="mt-2 mb-0"><?php echo $sPending; ?></h4><small class="text-muted">بانتظار الإجراء</small>
        </div>
      </div>
      <div class="col-6 col-lg" data-aos="fade-up" data-aos-delay="400">
        <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-cash-coin fs-3 text-success"></i>
          <h4 class="mt-2 mb-0"><?php echo $sRevenue; ?>$</h4><small class="text-muted">الإيرادات المؤكدة</small>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ===== الطلبات ===== -->
  <?php if ($tab === 'orders'): ?>
    <div class="table-responsive bg-white rounded-3 shadow-sm p-3" data-aos="fade-up">
      <table class="table align-middle small">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>العميل</th>
            <th>الإجمالي</th>
            <th>الطريقة</th>
            <th>التفاصيل</th>
            <th>الحالة</th>
            <th>التاريخ</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($o = mysqli_fetch_assoc($orders)): ?>
            <tr>
              <td><?php echo (int)$o['order_id']; ?></td>
              <td><?php echo htmlspecialchars($o['u_name'] ?? '—', ENT_QUOTES); ?></td>
              <td class="fw-bold text-brand"><?php echo (int)$o['total']; ?>$</td>
              <td><?php echo htmlspecialchars($o['payment_method'], ENT_QUOTES); ?></td>
              <td style="max-width:220px" class="text-muted"><?php echo htmlspecialchars($o['method_details'] ?? '', ENT_QUOTES); ?></td>
              <td style="max-width:200px" class="small">
                <b><?php echo htmlspecialchars($o['customer_name'] ?? '', ENT_QUOTES); ?></b><br>
                📞 <a href="tel:<?php echo htmlspecialchars($o['customer_phone'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($o['customer_phone'] ?? '—', ENT_QUOTES); ?></a><br>
                <span class="text-muted">📍 <?php echo htmlspecialchars(($o['city'] ?? '') . ' - ' . ($o['address'] ?? ''), ENT_QUOTES); ?></span>
              </td>
              <td><span class="badge text-bg-<?php echo $badge[$o['status']] ?? 'secondary'; ?>"><?php echo $label[$o['status']] ?? htmlspecialchars($o['status'], ENT_QUOTES); ?></span></td>
              <td class="text-muted"><?php echo htmlspecialchars($o['created_at'], ENT_QUOTES); ?></td>
              <td class="text-nowrap">
                <?php if (in_array($o['status'], ['pending', 'cod'])): ?>
                  <form method="post" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="confirm_order">
                    <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
                    <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> تأكيد</button>
                  </form>
                <?php endif; ?>
                <?php if ($o['status'] !== 'cancelled' && $o['status'] !== 'confirmed'): ?>
                  <form method="post" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="cancel_order">
                    <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- ===== المنتجات ===== -->
  <?php if ($tab === 'products'): ?>
    <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
      <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-plus-circle text-brand"></i> إضافة منتج جديد</h6>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="add_product">
          <div class="col-md-3"><input type="text" name="p_name" class="form-control" placeholder="اسم المنتج" required></div>
          <div class="col-md-2"><input type="number" name="p_price" class="form-control" placeholder="السعر $" min="1" required></div>
          <div class="col-md-2"><input type="number" name="p_quantity" class="form-control" placeholder="الكمية" min="0"></div>
          <div class="col-md-3"><input type="file" name="image" class="form-control" accept=".png,.jpg,.jpeg,.webp"></div>
          <div class="col-12"><textarea name="p_describe" class="form-control" rows="2" placeholder="وصف المنتج"></textarea></div>
          <div class="col-12"><button class="btn btn-brand"><i class="bi bi-plus-lg"></i> إضافة</button></div>
        </form>
      </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm p-3" data-aos="fade-up">
      <table class="table align-middle small">
        <thead class="table-light">
          <tr>
            <th>صورة</th>
            <th>الاسم</th>
            <th>السعر</th>
            <th>الكمية</th>
            <th>الوصف</th>
            <th>المخزون</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($p['p_img'], ENT_QUOTES); ?>" style="width:45px;height:45px;object-fit:contain"></td>
              <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="p_id" value="<?php echo (int)$p['p_id']; ?>">
                <td><input type="text" name="p_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['p_name'], ENT_QUOTES); ?>"></td>
                <td style="width:90px"><input type="number" name="p_price" class="form-control form-control-sm" value="<?php echo (int)$p['p_price']; ?>"></td>
                <td style="width:90px"><input type="number" name="p_quantity" class="form-control form-control-sm" value="<?php echo (int)$p['p_quantity']; ?>"></td>
                <td><?php $qq = (int)$p['p_quantity'];
                    echo $qq <= 0 ? '<span class="badge text-bg-danger">نفد</span>' : ($qq <= 5 ? '<span class="badge text-bg-warning">منخفض: ' . $qq . '</span>' : '<span class="badge text-bg-success">متوفر</span>'); ?></td>
                <td><input type="text" name="p_describe" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['p_describe'], ENT_QUOTES); ?>"></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-save"></i> حفظ</button>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('حذف المنتج نهائيًا؟')">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="p_id" value="<?php echo (int)$p['p_id']; ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- ===== المستخدمون ===== -->
  <?php if ($tab === 'users'): ?>
    <div class="table-responsive bg-white rounded-3 shadow-sm p-3" data-aos="fade-up">
      <table class="table align-middle small">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>الإيميل</th>
            <th>الصلاحية</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <tr>
              <td><?php echo (int)$u['u_id']; ?></td>
              <td><?php echo htmlspecialchars($u['u_name'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($u['u_email'], ENT_QUOTES); ?></td>
              <td><span class="badge text-bg-<?php echo $u['u_type'] === 'admin' ? 'danger' : 'secondary'; ?>"><?php echo htmlspecialchars($u['u_type'] ?? 'user', ENT_QUOTES); ?></span></td>
              <td class="text-nowrap">
                <?php if ((int)$u['u_id'] !== $uid): ?>
                  <form method="post" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="toggle_admin">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['u_id']; ?>">
                    <button class="btn btn-sm btn-outline-warning"><i class="bi bi-shield-toggle"></i> تبديل الصلاحية</button>
                  </form>
                  <?php if ($u['u_type'] !== 'admin'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('حذف المستخدم؟')">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?php echo (int)$u['u_id']; ?>">
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-x"></i></button>
                    </form>
                <?php endif;
                endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php
include("include/footer.php");
?>