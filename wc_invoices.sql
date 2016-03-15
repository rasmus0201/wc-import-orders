-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Vært: 127.0.0.1
-- Genereringstid: 15. 03 2016 kl. 21:36:37
-- Serverversion: 5.6.24
-- PHP-version: 5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wc_invoices`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `invoices`
--

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `owner_site` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL,
  `owner_site` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL,
  `completed_at` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `currency` varchar(255) NOT NULL,
  `total` double NOT NULL,
  `subtotal` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_shipping` double NOT NULL,
  `shipping_tax` double NOT NULL,
  `cart_tax` double NOT NULL,
  `total_discount` double NOT NULL,
  `shipping_methods` text NOT NULL,
  `payment_details` text NOT NULL,
  `billing_address` text NOT NULL,
  `shipping_address` text NOT NULL,
  `total_line_items_quantity` int(11) NOT NULL,
  `note` text NOT NULL,
  `customer_ip` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `view_order_url` text NOT NULL,
  `line_items` mediumtext NOT NULL,
  `shipping_lines` text NOT NULL,
  `tax_lines` text NOT NULL,
  `fee_lines` text NOT NULL,
  `coupon_lines` text NOT NULL,
  `export_csv` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL,
  `setting_name` text NOT NULL,
  `setting_value` mediumtext NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Data dump for tabellen `settings`
--

INSERT INTO `settings` (`id`, `setting_name`, `setting_value`) VALUES
(1, 'next_invoice', '1'),
(2, 'last_pull_date', '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `consumer_key` varchar(255) NOT NULL,
  `consumer_secret` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Data dump for tabellen `sites`
--

INSERT INTO `sites` (`id`, `name`, `url`, `consumer_key`, `consumer_secret`) VALUES
(1, 'jellybeans.dk', 'http://jellybeans.dk', 'ck_44384696901943c68c1981971590c31954b4dd07', 'cs_5a3d6e6cbced38c1299e983b0f35d93f975722b3'),
(2, 'slikworld.dk', 'http://slikworld.dk', 'ck_217f17f1a60cf68e84ab9d8e16bfda9282d282c3', 'cs_5189295b26e8531a2be8f9b56042d46d74c61a0c');

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_invoice_id` (`invoice_id`,`order_id`,`owner_site`);

--
-- Indeks for tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_order_id` (`order_id`,`owner_site`);

--
-- Indeks for tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tilføj AUTO_INCREMENT i tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tilføj AUTO_INCREMENT i tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- Tilføj AUTO_INCREMENT i tabel `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
