# 🌿 Edmark E-Commerce Platform

A full-featured e-commerce store built with **PHP 8 + MySQL + Bootstrap 5** — evolved from a 2017 class project into a modern, secure shop: live cart, multi-method payments, admin dashboard, KPI reports, coupons, reviews and more.

> 🇾🇪 Arabic version: [README.ar.md](README.ar.md)

## 🎥 Demo (60s)

[![Watch demo](https://img.youtube.com/vi/VIDEO_ID/hqdefault.jpg)](https://youtu.be/VIDEO_ID)

## ✨ Features

### 🛒 Customer Experience
- Product catalog with **search, categories, sorting & pagination**
- Live per-user cart (AJAX — no page reloads) with smart quantity stepper & discount coupons
- Multi-method checkout: Cash on Delivery, **13 Yemeni e-wallets**, 7 exchange companies, international card simulation (Luhn + auto brand detection), BNPL (Tamara / Tabby)
- Order tracking ("My Orders") + automatic confirmation emails
- Star reviews & ratings, floating WhatsApp button, password reset with a live countdown timer

### 🛡️ Security
- **bcrypt** password hashing with automatic legacy-hash upgrade
- Prepared Statements, CSRF tokens, XSS output escaping
- Rate limiting (login & reset), admin-only areas, validated image uploads (MIME + size)

### 👨‍💼 Admin Dashboard
- Orders: confirm / cancel with automatic stock restore + delivery details
- Products + categories, users & roles, coupons — edited live via AJAX (single & bulk save)
- **Live inventory**: automatic deduction per order + low-stock / out-of-stock badges
- KPI reports (Chart.js) with auto-generated business insights + Arabic-ready CSV export
- One-click database backups

## 📸 Screenshots

### 🛍️ Storefront

| Home | Search & Categories | Product Details & Reviews |
|:---:|:---:|:---:|
| ![home](Captures/home.png) | ![search](Captures/search.png) | ![details](Captures/details.png) |

| Cart + Coupon | Checkout | My Orders |
|:---:|:---:|:---:|
| ![cart](Captures/cart.png) | ![checkout](Captures/checkout.png) | ![my-orders](Captures/my-orders.png) |

| Password Reset ⏱️ | Confirmation Email | Mobile View 📱 |
|:---:|:---:|:---:|
| ![reset](Captures/reset.png) | ![email](Captures/email.png) | ![mobile](Captures/mobile.png) |

### 👨‍💼 Admin Panel

| KPI Statistics | Orders Management |
|:---:|:---:|
| ![admin-stats](Captures/admin-stats.png) | ![admin-orders](Captures/admin-orders.png) |

| Business Reports 📊 | Coupons 🎟️ |
|:---:|:---:|
| ![reports](Captures/reports.png) | ![coupons](Captures/coupons.png) |

## 🚀 Run Locally

1. Install **XAMPP** and place the project inside `htdocs/`.
2. Import `Db/database.sql` via phpMyAdmin.
3. Copy `connection/connection.example.php` → `connection/connection.php` and fill in your DB credentials.
4. Copy `config/email.example.php` → `config/email.php` and set a **Gmail App Password** (for transactional emails).

**Demo admin:** `alasbahi123@gmail.com` / `Admin@2026`

> On first login the password is automatically upgraded to bcrypt.

## 🧰 Tech Stack

PHP 8.2 · MySQL/MariaDB · Bootstrap 5 RTL · Chart.js · AOS · PHPMailer · Vanilla JS (Fetch API)

## 📁 Project Structure

```
├── api/            # JSON endpoints (cart, coupons, admin AJAX)
├── assets/         # custom.css, admin.js
├── backups/        # DB backups (git-ignored)
├── Captures/       # README screenshots
├── config/         # mail settings (git-ignored)
├── connection/     # DB connection (git-ignored)
├── include/        # header / footer / csrf / coupon / mailer / pagination / rate_limit
└── *.php           # store & admin pages
```

## 🗺️ Development Roadmap (4 Batches)

1. **Commercial Core** — delivery info, live inventory, my-orders, confirmation emails
2. **Shopping Experience** — search / categories / sorting, WhatsApp, password reset
3. **Marketing** — coupons, reviews, CSV export
4. **Maturity** — rate limiting, pagination, backups, SEO

---
Educational applied project — Akram Mansour
