# 🌿 Edmark E-Commerce Platform

A full-featured e-commerce store built with **PHP 8 + MySQL + Bootstrap 5** — evolved from a 2017 class project into a modern, secure shop: live cart, multi-method payments, admin dashboard, reports, coupons, reviews and more.

> 🇾🇪 Arabic version: [README.ar.md](README.ar.md)

## 🎥 Demo (60s)

[![Watch demo](https://img.youtube.com/vi/VIDEO_ID/hqdefault.jpg)](https://youtu.be/VIDEO_ID)

## ✨ Features

- 🛒 Live per-user cart (AJAX), smart quantity stepper, coupons, real-time stock
- 💳 Checkout: COD, 13 Yemeni wallets, 7 exchange companies, card simulation (Luhn + brand detection), BNPL
- 🔐 bcrypt + lazy hash upgrade, CSRF, rate limiting, password reset with email countdown
- 🧑‍ Admin: orders (confirm/cancel + auto restock), AJAX product/user/coupon editing, backups
- 📊 KPI reports with Chart.js + Arabic-ready CSV export
- 🔍 Search, categories, sorting, pagination, SEO, reviews with star ratings
- 📱 RTL Bootstrap 5 UI with animations + floating WhatsApp

## 📸 Screenshots

| Home | Reports |
|---|---|
| ![home](Captures/home.png) | ![reports](Captures/reports.png) |

| Cart + Coupon | Checkout |
|---|---|
| ![cart](Captures/cart.png) | ![checkout](Captures/checkout.png) |

| Admin Orders | Password Reset |
|---|---|
| ![admin-orders](Captures/admin-orders.png) | ![reset](Captures/reset.png) |

## 🚀 Run Locally

1. Install XAMPP, place project in `htdocs/`.
2. Import `Db/database.sql` via phpMyAdmin.
3. Copy `connection/connection.example.php` → `connection/connection.php` and fill credentials.
4. Copy `config/email.example.php` → `config/email.php` and set a Gmail App Password.

**Demo admin:** `alasbahi123@gmail.com / 123`

## 🧰 Stack

PHP 8.2 · MySQL · Bootstrap 5 RTL · Chart.js · AOS · PHPMailer · Vanilla JS (Fetch API)
