-- Edmark E-Commerce Platform - Clean schema (no personal data)
-- PHP 8.2 + MySQL/MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `database` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `database`;

-- ===== المستخدمين =====
CREATE TABLE IF NOT EXISTS `users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_name` text NOT NULL,
  `u_email` text NOT NULL,
  `u_pass` text NOT NULL,
  `u_type` varchar(15) DEFAULT 'user',
  PRIMARY KEY (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- حساب أدمن افتراضي: admin@gmail.com / 0000
-- (عند أول تسجيل دخول بيترقى تلقائياً لتشفير bcrypt)
INSERT INTO `users` (`u_id`, `u_name`, `u_email`, `u_pass`, `u_type`) VALUES
(1, 'admin', 'admin@gmail.com', '0000', 'admin');

-- ===== المنتجات =====
CREATE TABLE IF NOT EXISTS `product` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` text NOT NULL,
  `p_quantity` int(11) NOT NULL,
  `p_price` int(11) NOT NULL,
  `p_describe` text NOT NULL,
  `p_img` text NOT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product` (`p_id`, `p_name`, `p_quantity`, `p_price`, `p_describe`, `p_img`) VALUES
(1, 'Red Rubble Tea', 100, 200, 'علاج مفيد للمفاصل والعظام', 'photo/2.png'),
(2, 'splina', 100, 50, 'منتج عشبي مفيد', 'photo/1.png'),
(3, 'Bio-Elixir', 200, 44, 'مشروب صحي مفيد', 'photo/3.png'),
(4, 'Spiro', 400, 50, 'علاج سبايرو فعال', 'photo/4.png'),
(5, 'pjdlanl', 400, 55, 'وصف المنتج', 'photo/5.png'),
(6, 'Edmark Modcha', 20, 500, 'منتج من شركة إدمارك العالمية', 'photo/6.png'),
(7, 'Splana', 800, 600, 'مجموعة منتجات شركة إدمارك', 'photo/10.png'),
(8, 'Shake off', 1, 510, 'أحد علاجات شركة إدمارك', 'photo/6.png');

-- ===== السلة (مرتبطة بالمستخدم) =====
CREATE TABLE IF NOT EXISTS `cart` (
  `u_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `c_name` varchar(20) NOT NULL,
  `c_price` int(11) NOT NULL,
  `c_qty` int(11) NOT NULL DEFAULT '1',
  `c_total` int(11) NOT NULL,
  `c_img` text NOT NULL,
  PRIMARY KEY (`u_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ===== الطلبات =====
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'paid',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `order_items` (
  `oi_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`oi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ===== الدفع (مرتبط بالمستخدم) =====
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardNumber` int(11) NOT NULL,
  `cvc` int(11) NOT NULL,
  `fullName` varchar(30) NOT NULL,
  `expiration` date NOT NULL,
  `amount` int(11) NOT NULL,
  `u_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;