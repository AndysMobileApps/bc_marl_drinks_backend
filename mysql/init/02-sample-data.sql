-- BC Marl Drinks Sample Data
-- Insert admin user and sample products

USE `bcmarl_drinks`;

-- Insert admin user (PIN will be set on first login)
INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `mobile`, `role`, `balanceCents`, `created_at`, `updated_at`) VALUES
('admin-uuid-bcmarl-2025', 'Max', 'Mustermann', 'admin@bcmarl.de', '01234567890', 'admin', 10000, NOW(), NOW());

-- Insert sample users
INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `mobile`, `role`, `balanceCents`, `lowBalanceThresholdCents`, `created_at`, `updated_at`) VALUES
('user-anna-uuid-001', 'Anna', 'Schmidt', 'anna@example.com', '01111111111', 'user', 2500, 500, NOW(), NOW()),
('user-tom-uuid-002', 'Tom', 'Mueller', 'tom@example.com', '02222222222', 'user', 1800, 500, NOW(), NOW()),
('user-lisa-uuid-003', 'Lisa', 'Weber', 'lisa@example.com', '03333333333', 'user', 3200, 500, NOW(), NOW()),
('user-chris-uuid-004', 'Chris', 'Fischer', 'chris@example.com', '04444444444', 'user', 150, 500, NOW(), NOW());

-- Insert sample products
INSERT INTO `products` (`id`, `name`, `icon`, `priceCents`, `category`, `active`, `created_at`, `updated_at`) VALUES
-- Getraenke
('prod-pils-uuid-001', 'Pils 0,5l', '/images/icons/beer.png', 250, 'DRINKS', TRUE, NOW(), NOW()),
('prod-cola-uuid-002', 'Cola 0,33l', '/images/icons/cola.png', 180, 'DRINKS', TRUE, NOW(), NOW()),
('prod-wasser-uuid-003', 'Mineralwasser 0,5l', '/images/icons/water.png', 120, 'DRINKS', TRUE, NOW(), NOW()),
('prod-kaffee-uuid-004', 'Kaffee', '/images/icons/coffee.png', 150, 'DRINKS', TRUE, NOW(), NOW()),
('prod-apfelsaft-uuid-005', 'Apfelsaft 0,25l', '/images/icons/juice.png', 160, 'DRINKS', TRUE, NOW(), NOW()),

-- Snacks  
('prod-erdnuesse-uuid-006', 'Erdnuesse gesalzen', '/images/icons/nuts.png', 200, 'SNACKS', TRUE, NOW(), NOW()),
('prod-chips-uuid-007', 'Chips Paprika', '/images/icons/chips.png', 150, 'SNACKS', TRUE, NOW(), NOW()),
('prod-schoko-uuid-008', 'Schokoriegel', '/images/icons/chocolate.png', 120, 'SNACKS', TRUE, NOW(), NOW()),
('prod-brezel-uuid-009', 'Brezel', '/images/icons/pretzel.png', 80, 'SNACKS', TRUE, NOW(), NOW()),

-- Zubehoer
('prod-tshirt-uuid-010', 'Vereins T-Shirt M', '/images/icons/tshirt.png', 1500, 'ACCESSORIES', TRUE, NOW(), NOW()),
('prod-kappe-uuid-011', 'Vereinskappe', '/images/icons/cap.png', 800, 'ACCESSORIES', TRUE, NOW(), NOW()),
('prod-aufkleber-uuid-012', 'Aufkleber Set', '/images/icons/sticker.png', 300, 'ACCESSORIES', TRUE, NOW(), NOW()),

-- Mitgliedschaften
('prod-jahr-uuid-013', 'Jahresmitgliedschaft Erwachsene', '/images/icons/membership.png', 5000, 'MEMBERSHIP', TRUE, NOW(), NOW()),
('prod-familie-uuid-014', 'Familienmitgliedschaft Familie', '/images/icons/family.png', 8000, 'MEMBERSHIP', TRUE, NOW(), NOW());

-- Sample favorites
INSERT INTO `favorites` (`userId`, `productId`, `created_at`) VALUES
('user-anna-uuid-001', 'prod-pils-uuid-001', NOW()),
('user-anna-uuid-001', 'prod-cola-uuid-002', NOW()),
('user-tom-uuid-002', 'prod-pils-uuid-001', NOW()),
('user-tom-uuid-002', 'prod-erdnuesse-uuid-006', NOW());

-- Sample bookings
INSERT INTO `bookings` (`id`, `userId`, `productId`, `quantity`, `unitPriceCents`, `totalCents`, `timestamp`, `status`) VALUES
('booking-uuid-001', 'user-anna-uuid-001', 'prod-pils-uuid-001', 2, 250, 500, DATE_SUB(NOW(), INTERVAL 2 DAY), 'booked'),
('booking-uuid-002', 'user-tom-uuid-002', 'prod-cola-uuid-002', 1, 180, 180, DATE_SUB(NOW(), INTERVAL 1 DAY), 'booked'),
('booking-uuid-003', 'user-lisa-uuid-003', 'prod-kaffee-uuid-004', 3, 150, 450, DATE_SUB(NOW(), INTERVAL 5 HOUR), 'booked');

-- Sample transactions (corresponding to bookings above)
INSERT INTO `transactions` (`id`, `userId`, `type`, `amountCents`, `reference`, `timestamp`) VALUES
('trans-debit-001', 'user-anna-uuid-001', 'DEBIT', 500, 'booking-uuid-001', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('trans-debit-002', 'user-tom-uuid-002', 'DEBIT', 180, 'booking-uuid-002', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('trans-debit-003', 'user-lisa-uuid-003', 'DEBIT', 450, 'booking-uuid-003', DATE_SUB(NOW(), INTERVAL 5 HOUR));

-- Sample deposits (admin adding money)
INSERT INTO `transactions` (`id`, `userId`, `type`, `amountCents`, `timestamp`, `enteredByAdminId`) VALUES
('trans-deposit-001', 'user-anna-uuid-001', 'DEPOSIT', 3000, DATE_SUB(NOW(), INTERVAL 7 DAY), 'admin-uuid-bcmarl-2025'),
('trans-deposit-002', 'user-tom-uuid-002', 'DEPOSIT', 2000, DATE_SUB(NOW(), INTERVAL 6 DAY), 'admin-uuid-bcmarl-2025'),
('trans-deposit-003', 'user-lisa-uuid-003', 'DEPOSIT', 3650, DATE_SUB(NOW(), INTERVAL 5 DAY), 'admin-uuid-bcmarl-2025'),
('trans-deposit-004', 'user-chris-uuid-004', 'DEPOSIT', 1000, DATE_SUB(NOW(), INTERVAL 4 DAY), 'admin-uuid-bcmarl-2025');

