<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/csrf.php';
require_once __DIR__ . '/connection/connection.php';

// حماية: أدمن فقط
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

// ===== فلتر الفترة =====
$period = $_GET['period'] ?? 'month';
$periods = [
    'today' => ['label' => 'اليوم',        'sql' => "DATE(created_at) = CURDATE()"],
    'week'  => ['label' => 'آخر 7 أيام',  'sql' => "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"],
    'month' => ['label' => 'آخر 30 يوم',  'sql' => "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"],
    'year'  => ['label' => 'السنة',       'sql' => "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)"],
    'all'   => ['label' => 'كل الفترات',  'sql' => "1=1"],
];
if (!isset($periods[$period])) $period = 'month';
$P  = $periods[$period]['sql'];
$PO = str_replace('created_at', 'o.created_at', $P);

$row1 = fn($q) => mysqli_fetch_assoc(mysqli_query($con_db, $q));
$val  = fn($q) => (int) array_values($row1($q))[0];

// ===== مؤشرات KPI =====
$k = $row1("SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM orders WHERE status IN ('paid','confirmed') AND $P");
$revenue    = (int) $k['rev'];
$paidOrders = (int) $k['cnt'];
$aov        = $paidOrders > 0 ? round($revenue / $paidOrders) : 0;
$allOrders  = $val("SELECT COUNT(*) c FROM orders WHERE $P");
$pending    = $val("SELECT COUNT(*) c FROM orders WHERE status IN ('pending','cod') AND $P");
$cancelled  = $val("SELECT COUNT(*) c FROM orders WHERE status='cancelled' AND $P");
$customers  = $val("SELECT COUNT(DISTINCT u_id) c FROM orders WHERE $P");
$cartValue  = $val("SELECT COALESCE(SUM(c_total),0) c FROM cart");
$cancelRate = $allOrders > 0 ? round($cancelled / $allOrders * 100) : 0;

// ===== سلسلة الإيرادات اليومية (آخر 14 يوم) =====
$labels = [];
$series = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('m/d', strtotime($d));
    $series[$d] = 0;
}
$res = mysqli_query($con_db, "SELECT DATE(created_at) d, SUM(total) s FROM orders WHERE status IN ('paid','confirmed') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)");
while ($r = mysqli_fetch_assoc($res)) {
    if (isset($series[$r['d']])) $series[$r['d']] = (int) $r['s'];
}
$revSeries = array_values($series);

// ===== توزيع طرق الدفع =====
$payLabels = [];
$payData = [];
$res = mysqli_query($con_db, "SELECT payment_method m, COUNT(*) c FROM orders WHERE $P GROUP BY payment_method ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($res)) {
    $payLabels[] = $r['m'];
    $payData[] = (int) $r['c'];
}

// ===== أفضل المنتجات =====
$prodLabels = [];
$prodQty = [];
$res = mysqli_query($con_db, "SELECT oi.product_name n, SUM(oi.qty) q, SUM(oi.qty*oi.price) rev FROM order_items oi JOIN orders o ON o.order_id = oi.order_id WHERE $PO GROUP BY oi.product_id ORDER BY q DESC LIMIT 5");
while ($r = mysqli_fetch_assoc($res)) {
    $prodLabels[] = $r['n'];
    $prodQty[] = (int) $r['q'];
}

// ===== أفضل العملاء =====
$topCustomers = [];
$res = mysqli_query($con_db, "SELECT u.u_name n, COUNT(o.order_id) oc, SUM(o.total) s FROM orders o JOIN users u ON u.u_id = o.u_id WHERE o.status IN ('paid','confirmed') AND $PO GROUP BY o.u_id ORDER BY s DESC LIMIT 5");
while ($r = mysqli_fetch_assoc($res)) $topCustomers[] = $r;

// ===== القراءات الإدارية الذهبية =====
$insights = [];
if ($revenue > 0) $insights[] = "💰 إيرادات مؤكدة بـ <b>{$revenue}$</b> خلال «{$periods[$period]['label']}» بمتطلب سلة <b>{$aov}$</b>.";
if ($payLabels)   $insights[] = "💳 قناة الدفع الأبرز: <b>" . htmlspecialchars($payLabels[0], ENT_QUOTES) . "</b> بنسبة " . round($payData[0] / max(1, array_sum($payData)) * 100) . "% من الطلبات — ركّز جهودك التسويقية عليها.";
if ($prodLabels)  $insights[] = "🏆 النجم المبيعات: <b>" . htmlspecialchars($prodLabels[0], ENT_QUOTES) . "</b> ({$prodQty[0]} قطعة) — أمّن مخزونه وروّج له.";
if ($pending > 0) $insights[] = "⏳ <b>$pending</b> طلبات بانتظار الإجراء — كل ساعة تأخير تقلل ثقة العميل.";
if ($cancelRate > 20) $insights[] = "⚠️ نسبة الإلغاء <b>{$cancelRate}%</b> — راجع الأسباب (توصيل؟ أسعار؟ تواصل؟).";
if ($cartValue > 0) $insights[] = "🛒 مبيعات معلّقة بالسلات بقيمة <b>{$cartValue}$</b> — فرصة ذهبية لعروض تذكيرية.";

include("include/header.php");
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4" data-aos="fade-right">
        <h4 class="mb-0"><i class="bi bi-graph-up-arrow text-brand"></i> تقارير الإدارة</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> طباعة / PDF</button>
            <a href="admin.php" class="btn btn-outline-secondary btn-sm">لوحة التحكم</a>
        </div>
    </div>

    <!-- فلتر الفترة -->
    <ul class="nav nav-pills gap-2 mb-4 flex-wrap" data-aos="fade-up">
        <?php foreach ($periods as $key => $pp): ?>
            <li class="nav-item"><a class="nav-link py-1 <?php echo $period === $key ? 'active bg-brand' : ''; ?>" href="reports.php?period=<?php echo $key; ?>"><?php echo $pp['label']; ?></a></li>
        <?php endforeach; ?>
    </ul>

    <!-- KPI -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-cash-coin fs-3 text-success"></i>
                <h5 class="mt-2 mb-0"><?php echo $revenue; ?>$</h5><small class="text-muted">الإيرادات المؤكدة</small>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up" data-aos-delay="50">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-bag-check fs-3 text-brand"></i>
                <h5 class="mt-2 mb-0"><?php echo $allOrders; ?></h5><small class="text-muted">إجمالي الطلبات</small>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-receipt fs-3 text-info"></i>
                <h5 class="mt-2 mb-0"><?php echo $aov; ?>$</h5><small class="text-muted">متوسط قيمة الطلب</small>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up" data-aos-delay="150">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-people fs-3 text-primary"></i>
                <h5 class="mt-2 mb-0"><?php echo $customers; ?></h5><small class="text-muted">عملاء نشطون</small>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-hourglass-split fs-3 text-warning"></i>
                <h5 class="mt-2 mb-0"><?php echo $pending; ?></h5><small class="text-muted">بانتظار الإجراء</small>
            </div>
        </div>
        <div class="col-6 col-lg-4 col-xl-2" data-aos="fade-up" data-aos-delay="250">
            <div class="card border-0 shadow-sm text-center p-3"><i class="bi bi-x-octagon fs-3 text-danger"></i>
                <h5 class="mt-2 mb-0"><?php echo $cancelRate; ?>%</h5><small class="text-muted">نسبة الإلغاء</small>
            </div>
        </div>
    </div>

    <!-- القراءات الذهبية -->
    <?php if ($insights): ?>
        <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-lightbulb text-warning"></i> قراءات إدارية جاهزة</h6>
                <ul class="list-group list-group-flush small">
                    <?php foreach ($insights as $in): ?><li class="list-group-item px-0"><?php echo $in; ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- الرسوم البيانية -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="card border-0 shadow-sm p-3">
                <h6>📈 الإيرادات اليومية (آخر 14 يوم)</h6><canvas id="salesChart" height="90"></canvas>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm p-3">
                <h6>💳 توزيع طرق الدفع</h6><canvas id="payChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6" data-aos="fade-up">
            <div class="card border-0 shadow-sm p-3">
                <h6>🏆 أفضل المنتجات مبيعًا</h6><canvas id="prodChart" height="160"></canvas>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 shadow-sm p-3">
                <h6>👑 أفضل العملاء إنفاقًا</h6>
                <table class="table table-sm align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>العميل</th>
                            <th>طلبات</th>
                            <th>الإنفاق</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topCustomers)): ?><tr>
                                <td colspan="3" class="text-muted">لا بيانات للفترة المحددة</td>
                            </tr><?php endif; ?>
                        <?php foreach ($topCustomers as $tc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tc['n'], ENT_QUOTES); ?></td>
                                <td><?php echo (int)$tc['oc']; ?></td>
                                <td class="fw-bold text-brand"><?php echo (int)$tc['s']; ?>$</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'الإيرادات ($)',
                data: <?php echo json_encode($revSeries); ?>,
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46,125,50,.15)',
                fill: true,
                tension: .35,
                pointRadius: 3
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    new Chart(document.getElementById('payChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($payLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                data: <?php echo json_encode($payData); ?>,
                backgroundColor: ['#2e7d32', '#ffc00e', '#00a9e0', '#e8112d', '#7b1fa2']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    new Chart(document.getElementById('prodChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($prodLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                label: 'الكمية المباعة',
                data: <?php echo json_encode($prodQty); ?>,
                backgroundColor: '#00a757',
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>
<?php
include("include/footer.php");
?>