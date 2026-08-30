# 🌿 Edmark E-Commerce Platform

متجر إلكتروني متكامل مبني بـ **PHP + MySQL + Bootstrap 5** — تطوّر من مشروع جامعي (2017) إلى منصة بيع حديثة وآمنة: سلة حية، دفع متعدد الطرق، لوحة تحكم، تقارير، وتسويق.

## ✨ الميزات

### 🛒 تجربة العميل
- كتالوج منتجات مع **بحث + تصنيفات + فرز + ترقيم صفحات**
- سلة حية لكل مستخدم (AJAX بدون تحميل) مع عدّاد كمية ذكي وكوبونات خصم
- دفع متعدد: عند الاستلام، **13 محفظة يمنية**، 7 صرافات، بطاقات عالمية (محاكاة بوابة مع كشف نوع البطاقة)، وتقسيط BNPL
- تتبع الطلبات بصفحة "طلباتي" + إيميلات تأكيد تلقائية
- تقييمات ومراجعات بنجوم + زر واتساب عائم + استعادة كلمة مرور برابط مؤقت وعدّاد تنازلي

### 🛡️ الأمان
- كلمات مرور **bcrypt** مع ترقية تلقائية للقديم، Prepared Statements، CSRF Tokens، XSS escaping
- Rate Limiting للدخول والاستعادة، صلاحيات أدمن، رفع صور مُتحقق منه (نوع + حجم)

### 👨‍💼 لوحة التحكم والتقارير
- إدارة: طلبات (تأكيد/إلغاء مع إرجاع مخزون)، منتجات + تصنيفات، مستخدمون وصلاحيات، كوبونات
- **مخزون حي**: خصم تلقائي مع كل طلب + تنبيهات انخفاض/نفاد
- تقارير KPI برسوم Chart.js + قراءات إدارية تلقائية + تصدير CSV للعربية

## 📸 لقطات

| الرئيسية | التقارير |
|---|---|
| ![home](Captures/home.png) | ![reports](Captures/reports.png) |

| السلة + كوبون | الدفع |
|---|---|
| ![cart](Captures/cart.png) | ![checkout](Captures/checkout.png) |

| لوحة الطلبات | استعادة كلمة المرور |
|---|---|
| ![admin-orders](Captures/admin-orders.png) | ![reset](Captures/reset.png) |

| عرض الجوال | التفاصيل والتقييمات |
|---|---|
| ![mobile](Captures/mobile.png) | ![details](Captures/details.png) |

## 🚀 التشغيل محليًا

1. ثبّت **XAMPP** وضع المشروع بـ `htdocs/`.
2. استورد `Db/database.sql` من phpMyAdmin.
3. انسخ `connection/connection.example.php` إلى `connection/connection.php` وعبّئ بياناتك.
4. انسخ `config/email.example.php` إلى `config/email.php` وعبّئ **Gmail App Password** (للإيميلات).

## 👤 حساب تجريبي

| الدور | الإيميل | كلمة المرور |
|---|---|---|
| أدمن | alasbahi123@gmail.com | 0000 |

> عند أول دخول تُرقّى كلمة المرور تلقائيًا لتشفير bcrypt.

## 🧰 التقنيات

PHP 8.2 · MySQL/MariaDB · Bootstrap 5 RTL · Chart.js · AOS · PHPMailer · Vanilla JS (Fetch API)

## 📁 الهيكل

```
├── api/            # نقاط JSON للسلة والكوبونات
├── assets/         # custom.css
├── backups/        # نسخ احتياطية (مستبعد من git)
├── config/         # إعدادات البريد (مستبعد من git)
├── connection/     # اتصال DB (مستبعد من git)
├── include/        # header/footer/csrf/coupon/mailer/pagination/rate_limit
├── screenshots/    # لقطات README
└── *.php           # صفحات المتجر واللوحة
```

## 🗺️ خارطة التطوير (الرحلات الأربع)

1. **القلب التجاري** — توصيل + مخزون + طلباتي + إيميلات
2. **تجربة التسوق** — بحث/تصنيفات/فرز + واتساب + استعادة كلمة مرور
3. **التسويق** — كوبونات + تقييمات + تصدير CSV
4. **النضج** — Rate limiting + Pagination + Backups + SEO

---
مشروع تعليمي تطبيقي — أكرم منصّر
