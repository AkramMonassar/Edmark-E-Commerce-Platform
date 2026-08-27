<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

if (!isset($_SESSION['u_id'])) { header("Location: login.php"); exit; }
$uid = (int) $_SESSION['u_id'];
include("include/header.php");

$badge = ['pending'=>'warning', 'paid'=>'success', 'confirmed'=>'info', 'cod'=>'secondary', 'cancelled'=>'danger'];
$label = ['pending'=>'بانتظار تأكيد التحويل', 'paid'=>'مدفوع', 'confirmed'=>'مؤكد', 'cod'=>'عند الاستلام', 'cancelled'=>'ملغي'];

$orders = mysqli_query($con_db, "SELECT * FROM orders WHERE u_id = $uid ORDER BY order_id DESC");
?>
<div class="container my-4">
  <h4 class="mb-4" data-aos="fade-right"><i class="bi bi-box-seam text-brand"></i> طلباتي</h4>
  <?php if (mysqli_num_rows($orders) === 0): ?>
    <div class="alert alert-light border text-center py-5" data-aos="zoom-in">
      <i class="bi bi-inboxes text-brand" style="font-size:4rem"></i>
      <h5 class="mt-3">لا توجد طلبات بعد</h5>
      <a href="index.php" class="btn btn-brand mt-2">ابدأ التسوق</a>
    </div>
  <?php else: $d = 0; while ($o = mysqli_fetch_assoc($orders)): $d += 100; ?>
    <div class="card border-0 shadow-sm mb-3" data-aos="fade-up" data-aos-delay="<?php echo min($d, 400); ?>">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold">#<?php echo (int)$o['order_id']; ?> <small class="text-muted">— <?php echo htmlspecialchars($o['created_at'], ENT_QUOTES); ?></small></span>
        <span class="badge text-bg-<?php echo $badge[$o['status']] ?? 'secondary'; ?>"><?php echo $label[$o['status']] ?? htmlspecialchars($o['status'], ENT_QUOTES); ?></span>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush small mb-2">
          <?php
          $its = mysqli_query($con_db, "SELECT * FROM order_items WHERE order_id = " . (int)$o['order_id']);
          while ($it = mysqli_fetch_assoc($its)): ?>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span><?php echo htmlspecialchars($it['product_name'], ENT_QUOTES); ?> × <?php echo (int)$it['qty']; ?></span>
              <b><?php echo (int)$it['price'] * (int)$it['qty']; ?>$</b>
            </li>
          <?php endwhile; ?>
        </ul>
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars(($o['city'] ?? '') . ' - ' . ($o['address'] ?? ''), ENT_QUOTES); ?></small>
          <span class="fw-bold text-brand fs-5"><?php echo (int)$o['total']; ?>$</span>
        </div>
      </div>
    </div>
  <?php endwhile; endif; ?>
</div>
<?php
include("include/footer.php");
?>