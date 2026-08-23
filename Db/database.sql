-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 01, 2017 at 06:57 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `bil`
--

CREATE TABLE IF NOT EXISTS `bil` (
  `b_id` int(11) NOT NULL AUTO_INCREMENT,
  `b_value` int(11) NOT NULL,
  `b_u_id` int(11) NOT NULL,
  `city` varchar(10) NOT NULL,
  `addrss` varchar(10) NOT NULL,
  `phone` int(11) NOT NULL,
  PRIMARY KEY (`b_id`),
  KEY `b_u_id` (`b_u_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `bil`
--

INSERT INTO `bil` (`b_id`, `b_value`, `b_u_id`, `city`, `addrss`, `phone`) VALUES
(1, 1000, 1, 'sana''a', 'fredom str', 775180222);

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE IF NOT EXISTS `bill` (
  `b_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `b_value` int(11) NOT NULL,
  `p_u_id` int(11) NOT NULL,
  `city` varchar(10) NOT NULL,
  `address` text NOT NULL,
  `phone` int(11) NOT NULL,
  PRIMARY KEY (`b_id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `bill`
--

INSERT INTO `bill` (`b_id`, `b_value`, `p_u_id`, `city`, `address`, `phone`) VALUES
(1, 1000, 1, 'sana''a', 'street alragas', 770733109);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL,
  `c_name` varchar(20) NOT NULL,
  `c_price` int(11) NOT NULL,
  `c_qty` int(11) NOT NULL DEFAULT '1',
  `c_total` int(11) NOT NULL,
  `c_img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `c_name`, `c_price`, `c_qty`, `c_total`, `c_img`) VALUES
(3, 'Bio-Elixir', 44, 1, 44, 'photo/3.png'),
(4, 'Spiro', 50, 2, 100, 'photo/4.png'),
(1, 'Red Rubble Tea', 200, 1, 200, 'photo/2.png');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardNumber` int(11) NOT NULL,
  `cvc` int(11) NOT NULL,
  `fullName` varchar(30) NOT NULL,
  `expiration` date NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `cardNumber`, `cvc`, `fullName`, `expiration`, `amount`) VALUES
(1, 12356, 123, 'akram hamod abduh', '0000-00-00', 22),
(2, 123456, 123, 'akram hamod abduh', '0000-00-00', 200),
(3, 123456, 123, 'akram hamod abduh', '0000-00-00', 200),
(4, 123456, 123, 'akram hamod abduh', '0000-00-00', 200),
(5, 1255, 44, 'ali abdulah saleh', '2012-01-05', 500),
(6, 1255, 44, 'ali abdulah saleh', '2012-01-05', 500),
(7, 1255, 44, 'ali abdulah saleh', '2012-01-05', 500),
(8, 1255, 44, 'ali abdulah saleh', '2012-01-05', 500);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` text NOT NULL,
  `p_quantity` int(11) NOT NULL,
  `p_price` int(11) NOT NULL,
  `p_describe` text NOT NULL,
  `p_img` text NOT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`p_id`, `p_name`, `p_quantity`, `p_price`, `p_describe`, `p_img`) VALUES
(1, 'Red Rubble Tea', 100, 200, 'علاج مهم جداً ومفيد جداً للمفاصل والعظام وهو ايضاً مهم لكل انواع الجروح والأكسار هنا هذا علاج رائع جداً جداً ', 'photo/2.png'),
(2, 'splina', 100, 50, 'علاج حلو جداً له فوائد حلوه ', 'photo/1.png'),
(3, 'Bio-Elixir', 200, 44, 'علاج من النوع المفيد والذي يحتوي على مياة الدجاج المفيد والذي يحتوي على مواد واشياء مهمة جداً في الحياة الآجتماعية والدينية والمالية كلها اشياء مهمة كذلك الحياة الرائعة والمهمة جداً خاصة عندما تنوي ان تنوم في الصباح الجميل الذي كان ومازال رائع ججاص . ', 'photo/3.png'),
(4, 'Spiro', 400, 50, 'علاج ال سبايروا علاج جيد وفعال ', 'photo/4.png'),
(5, 'pjdlanl', 400, 55, 'kdjsljkdlakjd', 'photo/5.png'),
(6, 'Edmark Modcha', 20, 500, 'علاج من شركة ادمارك العالمية وهو منتج فعال ومفيد ', 'photo/6.png'),
(7, 'Splana', 800, 600, 'مجموعة برامج شركة ادمارك العالمية التي مقرها اندونسيا والتي تنتج ادوية عالمية', 'photo/10.png'),
(8, 'Shake off', 1, 510, 'احد انواع العلاجات في شركة ادمارك العالمية ', 'photo/8.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_name` text NOT NULL,
  `u_email` text NOT NULL,
  `u_pass` text NOT NULL,
  `u_type` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`u_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `u_name`, `u_email`, `u_pass`, `u_type`) VALUES
(1, 'akram hamod abduh', 'alasbahi123@gmail.com', '0000', 'admin'),
(2, 'asem', 'asem@gmail.com', '1111', 'user'),
(3, 'eeadj;lakd', 'alkda@gmail...', '9ea909076685907c772b15c5f6b226fb', NULL),
(4, 'eeadj;lakd', 'alkda@gmail...', '9ea909076685907c772b15c5f6b226fb', NULL),
(5, 'eeadj;lakd', 'alkda@gmail...', '9ea909076685907c772b15c5f6b226fb', NULL),
(6, 'eoapiojfoaiejefa;', 'alakkldskgmail.com', 'e610608967d3563c3b4f82c65b8bcc16', NULL),
(7, 'eoapiojfoaiejefa;', 'alakkldskgmail.com', 'e610608967d3563c3b4f82c65b8bcc16', NULL),
(13, 'ali1', 'ali1@gmail.com', '86318e52f5ed4801abe1d13d509443de', NULL),
(14, 'savwan', 'savwan@gmail.com', '98cb4878405474cf0d653e40908dce24', NULL),
(15, 'akram', 'akram@gmail.com', '3248f28c2b5f704b8c214924b7218f11', NULL),
(16, 'akram', 'akram@gmail.com', '691766aa4f7d9f23fd41fc53e0ea2d2e', NULL),
(17, 'AKRAM', 'AKRAM@gmail.com', '3248f28c2b5f704b8c214924b7218f11', NULL),
(18, 'hamod', 'hamod@gmail.com', '955577c9990fde18f68bcd0007f5eff2', NULL),
(19, 'hamod', 'hamod@gmail.com', '955577c9990fde18f68bcd0007f5eff2', NULL),
(20, 'hamod', 'hamod@gmail.com', '955577c9990fde18f68bcd0007f5eff2', NULL),
(21, 'ak', 'ala@gmail.com', '9ea909076685907c772b15c5f6b226fb', NULL),
(22, 'ak', 'ala@gmail.com', '9ea909076685907c772b15c5f6b226fb', 'user'),
(23, 'cart', 'cart@gmail.com', '54013ba69c196820e56801f1ef5aad54', 'user'),
(24, 'asem', 'asem@gmail.com', '3248f28c2b5f704b8c214924b7218f11', 'user'),
(25, 'cat', 'cat@gmail.com', 'd077f244def8a70e5ea758bd8352fcd8', 'user'),
(26, 'ali', 'ali@gmail.com', '3861a60523ef89a017be166c5b325409', 'user'),
(27, 'asem', 'asem@gmail.com', '839c46a5d1272dd54e20a4d06acac519', 'user');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bil`
--
ALTER TABLE `bil`
  ADD CONSTRAINT `bil_ibfk_1` FOREIGN KEY (`b_u_id`) REFERENCES `users` (`u_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
