-- ========================================================
-- LUXE JEWELS DATABASE DUMP
-- Database: luxe_jewels_db
-- Generated: 2026-10-04 01:02:58
-- ========================================================

CREATE DATABASE IF NOT EXISTS `luxe_jewels_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `luxe_jewels_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for `rules`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules` (
  `RuleID` varchar(10) NOT NULL,
  `RuleName` varchar(50) NOT NULL,
  PRIMARY KEY (`RuleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `rules`
INSERT INTO `rules` (`RuleID`, `RuleName`) VALUES
('R01', 'Admin'),
('R02', 'Manager'),
('R03', 'Cashier'),
('R04', 'Goldsmith');

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `UserID` varchar(10) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `RegistrationTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `BirthDate` date DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `RuleID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UserName` (`UserName`),
  UNIQUE KEY `UserID` (`UserID`),
  KEY `RuleID` (`RuleID`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`RuleID`) REFERENCES `rules` (`RuleID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `users`
INSERT INTO `users` (`id`, `UserID`, `UserName`, `FullName`, `Email`, `Password`, `RegistrationTime`, `BirthDate`, `Phone`, `RuleID`) VALUES
('1', 'UJW001', 'admin', 'System Administrator', 'admin@anajakjewelry.com', '$2y$12$80kvUYOpiJoiDZIJZs.gWeuhk/oiN2uyg77BbpnKYROJuuKqpLN2W', '2026-10-04 00:19:54', NULL, '+855 12 000 111', 'R01'),
('2', 'UJW002', 'cashier', 'Sokha Cashier', 'cashier@anajakjewelry.com', '$2y$12$2dWCy/NPDJJi6eDwq1MiIOha41IXkY8h6SySJ4GuY4NKVt7HKmjDW', '2026-10-04 00:19:54', NULL, '+855 12 000 222', 'R03'),
('3', 'UJW003', 'goldsmith', 'Master Vanna', 'vanna@anajakjewelry.com', '$2y$12$JsiPKCBx2HSSkgGWgTWg2.TNt0jgcmNdJ6WBIkelAZ/Nsg3X/aSei', '2026-10-04 00:19:54', NULL, '+855 12 000 333', 'R04');

-- --------------------------------------------------------
-- Table structure for `memberships`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `memberships`;
CREATE TABLE `memberships` (
  `id` int NOT NULL AUTO_INCREMENT,
  `MembershipID` varchar(10) NOT NULL,
  `MembershipName` varchar(50) NOT NULL,
  `DiscountRate` decimal(5,2) DEFAULT '0.00',
  `MinimumSpend` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `MembershipID` (`MembershipID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `memberships`
INSERT INTO `memberships` (`id`, `MembershipID`, `MembershipName`, `DiscountRate`, `MinimumSpend`) VALUES
('1', 'MJW001', 'Standard', '0.00', '0.00'),
('2', 'MJW002', 'Silver', '5.00', '500.00'),
('3', 'MJW003', 'Gold', '10.00', '2000.00'),
('4', 'MJW004', 'Platinum', '15.00', '5000.00');

-- --------------------------------------------------------
-- Table structure for `customers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CustomerID` varchar(10) NOT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `MembershipID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CustomerID` (`CustomerID`),
  KEY `MembershipID` (`MembershipID`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`MembershipID`) REFERENCES `memberships` (`MembershipID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `customers`
INSERT INTO `customers` (`id`, `CustomerID`, `CustomerName`, `Phone`, `Address`, `BirthDate`, `MembershipID`) VALUES
('1', 'CJW001', 'Emma Wilson', '+855 12 345 678', 'Street 240, Phnom Penh', '1992-05-14', 'MJW003'),
('2', 'CJW002', 'Michael Chen', '+855 10 222 333', 'Wat Bo, Siem Reap', '1988-11-20', 'MJW002'),
('3', 'CJW003', 'Lisa Wang', '+855 97 555 666', 'BKK1, Phnom Penh', '1995-02-18', 'MJW004'),
('4', 'CJW004', 'James Brown', '+855 11 888 999', 'Riverside, Battambang', '1985-08-09', 'MJW001'),
('5', 'CJW005', 'Sophea Seng', '+855 15 777 888', 'Toul Kork, Phnom Penh', '1990-07-23', 'MJW003'),
('6', 'CJW006', 'Davi Kim', '+855 16 999 111', 'Mittapheap, Sihanoukville', '1993-12-05', 'MJW001');

-- --------------------------------------------------------
-- Table structure for `product_categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CategoryID` varchar(15) NOT NULL,
  `CategoryName` varchar(100) NOT NULL,
  `Description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CategoryID` (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `product_categories`
INSERT INTO `product_categories` (`id`, `CategoryID`, `CategoryName`, `Description`) VALUES
('1', 'JW-CAT01', 'Rings', 'Fine gold and diamond rings'),
('2', 'JW-CAT02', 'Necklaces', 'Exquisite gold and gem-set necklaces'),
('3', 'JW-CAT03', 'Bracelets', 'Precious metal and diamond bracelets'),
('4', 'JW-CAT04', 'Earrings', 'Stud, drop and hoop earrings'),
('5', 'JW-CAT05', 'Pendants', 'Custom and religious pendants'),
('6', 'JW-CAT06', 'Bangles', 'Traditional and modern luxury bangles'),
('7', 'JW-CAT07', 'Chains', 'Solid gold and platinum chains'),
('8', 'JW-CAT08', 'Watches', 'Luxury gemstone and diamond watches'),
('9', 'JW-CAT09', 'Wedding Jewelry', 'Bridal sets, bands and tiaras'),
('10', 'JW-CAT10', 'Luxury Collections', 'High jewelry limited editions');

-- --------------------------------------------------------
-- Table structure for `product_brand`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_brand`;
CREATE TABLE `product_brand` (
  `id` int NOT NULL AUTO_INCREMENT,
  `BrandID` varchar(15) NOT NULL,
  `BrandName` varchar(100) NOT NULL,
  `Country` varchar(100) DEFAULT NULL,
  `Website` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `BrandID` (`BrandID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `product_brand`
INSERT INTO `product_brand` (`id`, `BrandID`, `BrandName`, `Country`, `Website`) VALUES
('1', 'JW-BRD01', 'ANAJAK', 'Cambodia', 'https://anajakjewelry.com'),
('2', 'JW-BRD02', 'Luxury Gold', 'Switzerland', 'https://luxurygold.ch'),
('3', 'JW-BRD03', 'Royal Jewelry', 'France', 'https://royaljewelry.fr'),
('4', 'JW-BRD04', 'Cartier', 'France', 'https://cartier.com'),
('5', 'JW-BRD05', 'Tiffany & Co.', 'USA', 'https://tiffany.com'),
('6', 'JW-BRD06', 'Bvlgari', 'Italy', 'https://bulgari.com');

-- --------------------------------------------------------
-- Table structure for `product_status`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_status`;
CREATE TABLE `product_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `PStatusID` varchar(15) NOT NULL,
  `PStatusName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PStatusID` (`PStatusID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `product_status`
INSERT INTO `product_status` (`id`, `PStatusID`, `PStatusName`) VALUES
('1', 'JW-ST01', 'Active'),
('2', 'JW-ST02', 'Inactive'),
('3', 'JW-ST03', 'Out of Stock'),
('4', 'JW-ST04', 'Discontinued');

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ProductID` varchar(10) NOT NULL,
  `ProductName` varchar(150) NOT NULL,
  `SKU` varchar(50) NOT NULL,
  `Barcode` varchar(100) DEFAULT NULL,
  `PurchasePrice` decimal(12,2) NOT NULL,
  `SellingPrice` decimal(12,2) NOT NULL,
  `StockQuantity` int DEFAULT '0',
  `MinStock` int DEFAULT '5',
  `WarrantyPeriod` int DEFAULT '12',
  `Image` varchar(255) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `Description` text,
  `Material` varchar(100) DEFAULT 'Gold',
  `Purity` varchar(50) DEFAULT '18K',
  `Weight` decimal(10,2) DEFAULT '0.00',
  `GemstoneType` varchar(100) DEFAULT NULL,
  `GemstoneWeight` decimal(10,2) DEFAULT '0.00',
  `CategoryID` varchar(15) DEFAULT NULL,
  `BrandID` varchar(15) DEFAULT NULL,
  `PStatusID` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SKU` (`SKU`),
  UNIQUE KEY `ProductID` (`ProductID`),
  KEY `CategoryID` (`CategoryID`),
  KEY `BrandID` (`BrandID`),
  KEY `PStatusID` (`PStatusID`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `product_categories` (`CategoryID`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`BrandID`) REFERENCES `product_brand` (`BrandID`) ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_3` FOREIGN KEY (`PStatusID`) REFERENCES `product_status` (`PStatusID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `products`
INSERT INTO `products` (`id`, `ProductID`, `ProductName`, `SKU`, `Barcode`, `PurchasePrice`, `SellingPrice`, `StockQuantity`, `MinStock`, `WarrantyPeriod`, `Image`, `CreatedAt`, `Description`, `Material`, `Purity`, `Weight`, `GemstoneType`, `GemstoneWeight`, `CategoryID`, `BrandID`, `PStatusID`) VALUES
('1', 'PJW0001', 'Gold Diamond Ring', 'RING-001', '8851234567890', '600.00', '850.00', '12', '5', '12', 'assets/images/ring-001.jpg', '2026-10-04 00:19:54', 'Elegant 18K yellow gold solitaire ring featuring a 0.50ct brilliant round diamond.', 'Yellow Gold', '18K', '5.25', '0', '0.50', 'JW-CAT01', 'JW-BRD01', 'JW-ST01'),
('2', 'PJW0002', 'Diamond Necklace', 'NECK-001', '8851234567891', '900.00', '1250.00', '8', '5', '12', 'assets/images/necklace-001.jpg', '2026-10-04 00:19:54', 'Stunning 18K white gold floral cluster diamond necklace.', 'White Gold', '18K', '12.50', '0', '1.20', 'JW-CAT02', 'JW-BRD02', 'JW-ST01'),
('3', 'PJW0003', 'Platinum Bracelet', 'BRAC-001', '8851234567892', '700.00', '950.00', '15', '5', '12', 'assets/images/bracelet-001.jpg', '2026-10-04 00:19:54', 'Handcrafted solid platinum tennis bracelet with blue Ceylon sapphires.', 'Platinum', '24K', '8.75', '0', '0.80', 'JW-CAT03', 'JW-BRD03', 'JW-ST01'),
('4', 'PJW0004', 'Sapphire Earrings', 'EARR-001', '8851234567893', '450.00', '650.00', '6', '5', '12', 'assets/images/earrings-001.jpg', '2026-10-04 00:19:54', 'Royal halo blue sapphire drop earrings set in 18K yellow gold.', 'Yellow Gold', '18K', '4.20', '0', '0.60', 'JW-CAT04', 'JW-BRD01', 'JW-ST01'),
('5', 'PJW0005', 'Gold Pendant', 'PEND-001', '8851234567894', '350.00', '480.00', '20', '5', '12', 'assets/images/pendant-001.jpg', '2026-10-04 00:19:54', 'Traditional Khmer motif 22K yellow gold pendant featuring natural Cambodian ruby.', 'Yellow Gold', '22K', '6.10', '0', '0.40', 'JW-CAT05', 'JW-BRD02', 'JW-ST01'),
('6', 'PJW0006', 'Luxury Diamond Watch', 'WAT-001', '8851234567895', '2400.00', '3200.00', '4', '5', '12', 'assets/images/watch-001.jpg', '2026-10-04 00:19:54', 'Swiss movement timepiece with 18K rose gold bezel encrusted with 48 diamonds.', 'Rose Gold & Steel', '18K', '45.00', '0', '0.75', 'JW-CAT08', 'JW-BRD03', 'JW-ST01'),
('7', 'PJW0007', 'Rose Gold Bangle', 'BANG-001', '8851234567896', '580.00', '790.00', '10', '5', '12', 'assets/images/bangle-001.jpg', '2026-10-04 00:19:54', 'Sleek minimalist 18K rose gold hinged luxury bangle.', 'Rose Gold', '18K', '9.80', '0', '0.00', 'JW-CAT06', 'JW-BRD01', 'JW-ST01'),
('8', 'PJW0008', 'Cuban Chain Necklace', 'CHN-001', '8851234567897', '1400.00', '1850.00', '5', '5', '12', 'assets/images/chain-001.jpg', '2026-10-04 00:19:54', 'Heavy solid 24K pure gold 6mm Miami Cuban link chain.', 'Yellow Gold', '24K', '18.50', '0', '0.00', 'JW-CAT07', 'JW-BRD02', 'JW-ST01'),
('9', 'PJW0009', 'Vintage Emerald Ring', 'RING-002', '8851234567898', '1100.00', '1550.00', '2', '5', '12', 'assets/images/ring-002.jpg', '2026-10-04 00:19:54', 'Art deco inspired ring featuring an octagon step-cut vivid green emerald.', 'White Gold', '18K', '6.10', '0', '1.10', 'JW-CAT01', 'JW-BRD04', 'JW-ST01'),
('10', 'PJW0010', 'Solitaire Diamond Ring', 'RING-003', '8851234567899', '1500.00', '2100.00', '3', '5', '12', 'assets/images/ring-003.jpg', '2026-10-04 00:19:54', 'Classic 6-prong platinum solitaire engagement ring with 1.00ct certified diamond.', 'Platinum', '18K', '3.80', '0', '1.00', 'JW-CAT01', 'JW-BRD05', 'JW-ST01');

-- --------------------------------------------------------
-- Table structure for `custom_order_status`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `custom_order_status`;
CREATE TABLE `custom_order_status` (
  `StatusID` int NOT NULL AUTO_INCREMENT,
  `StatusName` varchar(50) NOT NULL,
  PRIMARY KEY (`StatusID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `custom_order_status`
INSERT INTO `custom_order_status` (`StatusID`, `StatusName`) VALUES
('1', 'Pending'),
('2', 'Designing'),
('3', 'Manufacturing'),
('4', 'Quality Inspection'),
('5', 'Ready for Pickup'),
('6', 'Delivered'),
('7', 'Cancelled');

-- --------------------------------------------------------
-- Table structure for `custom_orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `custom_orders`;
CREATE TABLE `custom_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CustomOrderID` varchar(15) NOT NULL,
  `OrderDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `DueDate` date NOT NULL,
  `DeliveryDate` date DEFAULT NULL,
  `Deposit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `TotalAmount` decimal(12,2) NOT NULL,
  `CustomerDescription` text,
  `CustomerID` varchar(10) DEFAULT NULL,
  `CustomerName` varchar(100) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  `Goldsmith` varchar(100) DEFAULT NULL,
  `DesignImage` varchar(255) DEFAULT NULL,
  `StatusID` int DEFAULT NULL,
  `StatusName` varchar(50) DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `CustomOrderID` (`CustomOrderID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `StatusID` (`StatusID`),
  CONSTRAINT `custom_orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_ibfk_2` FOREIGN KEY (`StatusID`) REFERENCES `custom_order_status` (`StatusID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `custom_orders`
INSERT INTO `custom_orders` (`id`, `CustomOrderID`, `OrderDate`, `DueDate`, `DeliveryDate`, `Deposit`, `TotalAmount`, `CustomerDescription`, `CustomerID`, `CustomerName`, `Phone`, `Goldsmith`, `DesignImage`, `StatusID`, `StatusName`) VALUES
('1', 'CJW-ORD001', '2026-10-04 00:19:54', '2026-10-13', '2026-10-13', '500.00', '1200.00', 'Custom Engagement Ring with halo diamonds', 'CJW001', 'Emma Wilson', '+855 12 345 678', 'Sokha', 'uploads/designs/ring-design.jpg', '2', 'Designing'),
('2', 'CJW-ORD002', '2026-10-04 00:19:54', '2026-10-18', '2026-10-18', '800.00', '1800.00', 'Custom White Gold Diamond Floral Necklace', 'CJW002', 'Michael Chen', '+855 10 222 333', 'Vanna', 'uploads/designs/necklace-design.jpg', '3', 'Manufacturing'),
('3', 'CJW-ORD003', '2026-10-04 00:19:54', '2026-10-10', '2026-10-10', '600.00', '1400.00', 'Platinum Tennis Bracelet with Sapphires', 'CJW003', 'Lisa Wang', '+855 97 555 666', 'Sokha', 'uploads/designs/bracelet-design.jpg', '4', 'Quality Inspection'),
('4', 'CJW-ORD004', '2026-10-04 00:19:54', '2026-10-23', '2026-10-23', '300.00', '950.00', 'Vintage Gold Signet Ring with Monogram', 'CJW004', 'James Brown', '+855 11 888 999', 'Dara', '', '1', 'Pending'),
('5', 'CJW-ORD005', '2026-10-04 00:19:54', '2026-10-06', '2026-10-06', '700.00', '1100.00', 'Emerald and Diamond Cluster Earrings', 'CJW005', 'Sophea Seng', '+855 15 777 888', 'Vanna', '', '5', 'Ready for Pickup');

-- --------------------------------------------------------
-- Table structure for `sales`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SaleID` varchar(10) NOT NULL,
  `InvoiceNumber` varchar(50) DEFAULT NULL,
  `SaleDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `TotalAmount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `PaymentMethod` varchar(50) DEFAULT 'Cash',
  `Subtotal` decimal(12,2) DEFAULT '0.00',
  `DiscountAmount` decimal(12,2) DEFAULT '0.00',
  `TaxAmount` decimal(12,2) DEFAULT '0.00',
  `PaidAmount` decimal(12,2) DEFAULT '0.00',
  `ChangeAmount` decimal(12,2) DEFAULT '0.00',
  `Notes` text,
  `CustomerID` varchar(10) DEFAULT NULL,
  `CustomerName` varchar(100) DEFAULT 'Walk-in Customer',
  `UserID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SaleID` (`SaleID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `sales`
INSERT INTO `sales` (`id`, `SaleID`, `InvoiceNumber`, `SaleDate`, `TotalAmount`, `PaymentMethod`, `Subtotal`, `DiscountAmount`, `TaxAmount`, `PaidAmount`, `ChangeAmount`, `Notes`, `CustomerID`, `CustomerName`, `UserID`) VALUES
('1', 'SJW0001', 'INV-20261001-001', '2026-10-01 17:19:54', '850.00', 'Card', '850.00', '0.00', '0.00', '850.00', '0.00', NULL, 'CJW001', 'Emma Wilson', 'UJW001'),
('2', 'SJW0002', 'INV-20261002-002', '2026-10-02 17:19:54', '1900.00', 'QR Payment', '1900.00', '0.00', '0.00', '1900.00', '0.00', NULL, 'CJW002', 'Michael Chen', 'UJW001'),
('3', 'SJW0003', 'INV-20261003-003', '2026-10-03 17:19:54', '950.00', 'Cash', '950.00', '0.00', '0.00', '950.00', '0.00', NULL, 'CJW003', 'Lisa Wang', 'UJW001'),
('4', 'SJW0004', 'INV-20261003-004', '2026-10-03 17:19:54', '480.00', 'Cash', '480.00', '0.00', '0.00', '480.00', '0.00', NULL, NULL, 'Walk-in Customer', 'UJW001');

-- --------------------------------------------------------
-- Table structure for `sale_details`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sale_details`;
CREATE TABLE `sale_details` (
  `SaleDetailID` int NOT NULL AUTO_INCREMENT,
  `SaleID` varchar(10) DEFAULT NULL,
  `ProductID` varchar(10) DEFAULT NULL,
  `ProductName` varchar(150) DEFAULT NULL,
  `Quantity` int NOT NULL DEFAULT '1',
  `UnitPrice` decimal(12,2) NOT NULL,
  `Subtotal` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`SaleDetailID`),
  KEY `SaleID` (`SaleID`),
  KEY `ProductID` (`ProductID`),
  CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`SaleID`) REFERENCES `sales` (`SaleID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `sale_details`
INSERT INTO `sale_details` (`SaleDetailID`, `SaleID`, `ProductID`, `ProductName`, `Quantity`, `UnitPrice`, `Subtotal`) VALUES
('1', 'SJW0001', 'PJW0001', 'Gold Diamond Ring', '1', '850.00', '850.00'),
('2', 'SJW0002', 'PJW0002', 'Diamond Necklace', '1', '1250.00', '1250.00'),
('3', 'SJW0002', 'PJW0004', 'Sapphire Earrings', '1', '650.00', '650.00'),
('4', 'SJW0003', 'PJW0003', 'Platinum Bracelet', '1', '950.00', '950.00'),
('5', 'SJW0004', 'PJW0005', 'Gold Pendant', '1', '480.00', '480.00');

-- --------------------------------------------------------
-- Table structure for `suppliers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SupplierID` varchar(15) NOT NULL,
  `SupplierName` varchar(150) NOT NULL,
  `ContactName` varchar(100) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text,
  `SupplyType` varchar(100) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Active',
  `CreatedDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SupplierID` (`SupplierID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `suppliers`
INSERT INTO `suppliers` (`id`, `SupplierID`, `SupplierName`, `ContactName`, `Phone`, `Email`, `Address`, `SupplyType`, `Status`, `CreatedDate`) VALUES
('1', 'SUP-001', 'Pailin Gemstone Import Co.', 'Dara Kem', '+855 23 111 222', 'dara@pailingems.com', 'No. 12, St 136, Phnom Penh', 'Natural Gemstones', 'Active', '2026-10-04 00:19:54'),
('2', 'SUP-002', 'Angkor Gold Refinery', 'Chanthy Mao', '+855 23 333 444', 'contact@angkorgold.com', 'Sangkat Phsar Thmei, Phnom Penh', 'Pure 24K Gold & Platinum', 'Active', '2026-10-04 00:19:54'),
('3', 'SUP-003', 'Italian Fine Jewelry Chains', 'Marco Rossi', '+39 02 555 666', 'export@rossijewelry.it', 'Via Montenapoleone 8, Milan, Italy', 'Chains & Findings', 'Active', '2026-10-04 00:19:54'),
('4', 'SUP-004', 'Swiss Precision Watch Parts', 'Hans Meier', '+41 22 777 888', 'sales@precisionwatch.ch', 'Geneva, Switzerland', 'Watch Movements & Crystals', 'Active', '2026-10-04 00:19:54');

-- --------------------------------------------------------
-- Table structure for `inventory_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `inventory_logs`;
CREATE TABLE `inventory_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ProductID` varchar(15) NOT NULL,
  `Type` varchar(30) NOT NULL,
  `Quantity` int NOT NULL,
  `PreviousStock` int NOT NULL,
  `NewStock` int NOT NULL,
  `Reason` varchar(255) DEFAULT NULL,
  `Reference` varchar(100) DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT 'Admin',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Table structure for `store_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `store_settings`;
CREATE TABLE `store_settings` (
  `setting_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `store_settings`
INSERT INTO `store_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('currency', 'USD ($)', '2026-10-04 00:56:02'),
('receipt_note', 'Thank you for choosing ANAJAK JEWELRY. Certified authentic luxury items.', '2026-10-04 00:56:02'),
('store_address', '#88 Preah Norodom Blvd, Phnom Penh, Cambodia', '2026-10-04 00:56:02'),
('store_email', 'contact@anajakjewelry.com', '2026-10-04 00:56:02'),
('store_name', 'ANAJAK JEWELRY', '2026-10-04 00:56:02'),
('store_phone', '+855 23 888 999', '2026-10-04 00:56:02'),
('store_tagline', 'Luxury & Fine Gold Jewelry', '2026-10-04 00:56:02'),
('tax_rate', '10', '2026-10-04 00:56:02');

-- --------------------------------------------------------
-- Table structure for `user_login`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `user_login`;
CREATE TABLE `user_login` (
  `UserLoginID` int NOT NULL AUTO_INCREMENT,
  `UserID` varchar(10) DEFAULT NULL,
  `LoginTime` datetime DEFAULT CURRENT_TIMESTAMP,
  `LoginCount` int DEFAULT '0',
  PRIMARY KEY (`UserLoginID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `user_login_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================================
-- TRIGGERS
-- ========================================================

DROP TRIGGER IF EXISTS `before_insert_custom_orders`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_custom_orders` BEFORE INSERT ON `custom_orders` FOR EACH ROW BEGIN
            IF NEW.CustomOrderID IS NULL OR NEW.CustomOrderID = '' THEN
                SET NEW.CustomOrderID = CONCAT('CJW-ORD', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM custom_orders), 3, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_customers`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_customers` BEFORE INSERT ON `customers` FOR EACH ROW BEGIN
            IF NEW.CustomerID IS NULL OR NEW.CustomerID = '' THEN
                SET NEW.CustomerID = CONCAT('CJW', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM customers), 3, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_memberships`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_memberships` BEFORE INSERT ON `memberships` FOR EACH ROW BEGIN
            IF NEW.MembershipID IS NULL OR NEW.MembershipID = '' THEN
                SET NEW.MembershipID = CONCAT('MJW', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM memberships), 3, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_brands`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_brands` BEFORE INSERT ON `product_brand` FOR EACH ROW BEGIN
            IF NEW.BrandID IS NULL OR NEW.BrandID = '' THEN
                SET NEW.BrandID = CONCAT('JW-BRD', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM product_brand), 2, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_categories`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_categories` BEFORE INSERT ON `product_categories` FOR EACH ROW BEGIN
            IF NEW.CategoryID IS NULL OR NEW.CategoryID = '' THEN
                SET NEW.CategoryID = CONCAT('JW-CAT', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM product_categories), 2, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_product_status`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_product_status` BEFORE INSERT ON `product_status` FOR EACH ROW BEGIN
            IF NEW.PStatusID IS NULL OR NEW.PStatusID = '' THEN
                SET NEW.PStatusID = CONCAT('JW-ST', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM product_status), 2, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_products`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_products` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
            IF NEW.ProductID IS NULL OR NEW.ProductID = '' THEN
                SET NEW.ProductID = CONCAT('PJW', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM products), 4, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_sales`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_sales` BEFORE INSERT ON `sales` FOR EACH ROW BEGIN
            IF NEW.SaleID IS NULL OR NEW.SaleID = '' THEN
                SET NEW.SaleID = CONCAT('SJW', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM sales), 4, '0'));
            END IF;
        END;;
DELIMITER ;

DROP TRIGGER IF EXISTS `before_insert_users`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` TRIGGER `before_insert_users` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
            IF NEW.UserID IS NULL OR NEW.UserID = '' THEN
                SET NEW.UserID = CONCAT('UJW', LPAD((SELECT IFNULL(MAX(id), 0) + 1 FROM users), 3, '0'));
            END IF;
        END;;
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
