-- BC Marl Drinks Database Schema
-- Based on iOS SwiftData models

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `bcmarl_drinks` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bcmarl_drinks`;

-- Users table
CREATE TABLE `users` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `firstName` VARCHAR(100) NOT NULL,
  `lastName` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) NOT NULL,
  `pinHash` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `balanceCents` INT NOT NULL DEFAULT 0,
  `lowBalanceThresholdCents` INT NOT NULL DEFAULT 500,
  `locked` BOOLEAN NOT NULL DEFAULT FALSE,
  `failedLoginAttempts` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_mobile` (`mobile`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table  
CREATE TABLE `products` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `priceCents` INT NOT NULL,
  `category` ENUM('DRINKS', 'SNACKS', 'ACCESSORIES', 'MEMBERSHIP') NOT NULL,
  `active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE `bookings` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `userId` VARCHAR(36) NOT NULL,
  `productId` VARCHAR(36) NOT NULL,
  `quantity` INT NOT NULL,
  `unitPriceCents` INT NOT NULL,
  `totalCents` INT NOT NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('booked', 'voided') NOT NULL DEFAULT 'booked',
  `voidedByAdminId` VARCHAR(36) DEFAULT NULL,
  `voidedAt` TIMESTAMP NULL DEFAULT NULL,
  `originalBookingId` VARCHAR(36) DEFAULT NULL,
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`productId`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`voidedByAdminId`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_userId` (`userId`),
  INDEX `idx_productId` (`productId`),
  INDEX `idx_timestamp` (`timestamp`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table
CREATE TABLE `transactions` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `userId` VARCHAR(36) NOT NULL,
  `type` ENUM('DEPOSIT', 'DEBIT', 'REVERSAL') NOT NULL,
  `amountCents` INT NOT NULL,
  `reference` VARCHAR(100) DEFAULT NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `enteredByAdminId` VARCHAR(36) DEFAULT NULL,
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`enteredByAdminId`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_userId` (`userId`),
  INDEX `idx_type` (`type`),
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites table
CREATE TABLE `favorites` (
  `userId` VARCHAR(36) NOT NULL,
  `productId` VARCHAR(36) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`, `productId`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`productId`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

