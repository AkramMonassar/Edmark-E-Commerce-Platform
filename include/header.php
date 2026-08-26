<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../connection/connection.php';

$cart_count = 0;
$cart_total = 0;
if (isset($_SESSION['u_id'])) {
    $uid = (int) $_SESSION['u_id'];
    $res = mysqli_query($con_db, "SELECT SUM(c_total) AS t, COUNT(id) AS c FROM cart WHERE u_id = $uid");
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $cart_total = (int) ($r['t'] ?? 0);
        $cart_count = (int) ($r['c'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إدمارك | متجر المنتجات العشبية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --brand: #2e7d32;
            --brand-dark: #1b5e20;
        }

        html {
            scroll-behavior: smooth;
        }

        .bg-brand {
            background-color: var(--brand) !important;
        }

        .text-brand {
            color: var(--brand) !important;
        }

        /* أزرار تنبض عند اللمس */
        .btn {
            transition: all .25s ease;
        }

        .btn:active {
            transform: scale(.95);
        }

        .btn-brand {
            background-color: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(27, 94, 32, .35);
        }

        /* شريط التنقل: ظل يظهر عند التمرير + خط ذهبي ينزلق تحت الروابط */
        .navbar {
            transition: box-shadow .3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: 2px;
            width: 0;
            height: 2px;
            background: #ffd54f;
            transition: width .3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* بطاقات المنتجات: ترتفع وتتنفس عند التمرير */
        .card {
            transition: transform .35s ease, box-shadow .35s ease;
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, .14);
        }

        .img-wrap {
            overflow: hidden;
            background: #fff;
        }

        .img-wrap img {
            height: 200px;
            object-fit: contain;
            padding: 1rem;
            transition: transform .5s ease;
        }

        .card:hover .img-wrap img {
            transform: scale(1.08) rotate(1deg);
        }

        /* السلايدر: تأثير Ken Burns (الصورة تتنفس بالتقريب) */
        .hero-img {
            max-height: 340px;
            object-fit: contain;
            background: linear-gradient(180deg, #e8f5e9, #f6fbf6);
        }

        .carousel-item.active .hero-img {
            animation: kenburns 6s ease-out forwards;
        }

        @keyframes kenburns {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.1);
            }
        }

        /* شارة السلة: نبضة قلب */
        .badge.pulse {
            animation: pulse 1.2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1)
            }

            50% {
                transform: scale(1.25)
            }

            100% {
                transform: scale(1)
            }
        }

        /* زر العودة للأعلى */
        #toTop {
            position: fixed;
            left: 20px;
            bottom: 20px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 0;
            background: var(--brand);
            color: #fff;
            font-size: 20px;
            opacity: 0;
            pointer-events: none;
            transition: all .3s;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .25);
            z-index: 1050;
        }

        #toTop.show {
            opacity: 1;
            pointer-events: auto;
        }

        #toTop:hover {
            background: var(--brand-dark);
            transform: translateY(-4px);
        }

        /* التنبيهات تنزل بنعومة */
        .alert {
            animation: fadeDown .5s ease;
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>
    <script>window.CSRF = '<?php echo csrf_token(); ?>';</script>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-brand sticky-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-leaf"></i> EDMARK</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#products">المنتجات</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">من نحن</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">تواصل معنا</a></li>
                </ul>
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning pulse"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                            <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning pulse <?php echo $cart_count === 0 ? 'd-none' : ''; ?>"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                    <span id="cartTotal" class="badge text-bg-light text-brand <?php echo $cart_total === 0 ? 'd-none' : ''; ?>"><?php echo $cart_total; ?>$</span>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item"><span class="nav-link text-warning">أهلاً، <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES); ?></span></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">تسجيل الدخول</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm" href="create_acount.php">حساب جديد</a></li>
                        <?php if (isset($_SESSION['u_type']) && $_SESSION['u_type'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-warning" href="admin.php"><i class="bi bi-speedometer2"></i> لوحة التحكم</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">

    