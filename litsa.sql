-- Adminer 4.8.1 MySQL 11.2.2-MariaDB-1:11.2.2+maria~ubu2204 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `aname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `accounts` (`id`, `aname`, `created_at`, `updated_at`) VALUES
(1,	'Loan Account',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(2,	'Saving Account',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19');

DROP TABLE IF EXISTS `activity_otps`;
CREATE TABLE `activity_otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(191) NOT NULL,
  `activity` varchar(191) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `expire_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_otps_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `arrears`;
CREATE TABLE `arrears` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `installment_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `arrears_loan_id_foreign` (`loan_id`),
  KEY `arrears_installment_id_foreign` (`installment_id`),
  CONSTRAINT `arrears_installment_id_foreign` FOREIGN KEY (`installment_id`) REFERENCES `installments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `arrears_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `bemail` varchar(191) NOT NULL,
  `bphone` varchar(191) NOT NULL,
  `paybill` int(11) NOT NULL,
  `C2B_Consumer_Key` varchar(191) DEFAULT NULL,
  `C2B_Consumer_Secret` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `branches` (`id`, `bname`, `created_at`, `updated_at`, `status`, `bemail`, `bphone`, `paybill`, `C2B_Consumer_Key`, `C2B_Consumer_Secret`) VALUES
(1,	'Nairobi',	'2023-11-10 08:39:25',	'2023-11-10 08:39:25',	1,	'martin@deveint.com',	'254724213205',	6636959,	NULL,	NULL),
(2,	'Migori',	'2023-11-30 14:31:47',	'2023-11-30 14:31:47',	1,	'migorycounty@gmail.com',	'254711591065',	3021069,	NULL,	NULL),
(4,	'Kakamega',	'2023-12-18 14:37:18',	'2023-12-18 14:37:18',	1,	'test@gmail.com',	'254700000000',	234666,	NULL,	NULL),
(5,	'Siaya',	'2023-12-18 16:53:55',	'2023-12-18 16:53:55',	1,	'litsaelizabeth@gmail.com',	'254711591065',	4123359,	NULL,	NULL),
(6,	'Bungoma',	'2023-12-18 16:54:22',	'2023-12-18 16:54:22',	1,	'litsaelizabeth@gmail.com',	'254711591065',	4123359,	NULL,	NULL),
(7,	'Homabay',	'2023-12-18 16:54:43',	'2023-12-18 16:54:43',	1,	'litsaelizabeth@gmail.com',	'254711591065',	4123359,	NULL,	NULL),
(8,	'Busia',	'2023-12-18 16:55:00',	'2023-12-18 16:55:00',	1,	'litsaelizabeth@gmail.com',	'254711591065',	4123359,	NULL,	NULL);

DROP TABLE IF EXISTS `business_types`;
CREATE TABLE `business_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bname` varchar(191) NOT NULL,
  `industry_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_types_industry_id_foreign` (`industry_id`),
  CONSTRAINT `business_types_industry_id_foreign` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `business_types` (`id`, `bname`, `industry_id`, `created_at`, `updated_at`) VALUES
(1,	'Dry Cleaning/Laundry',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(2,	'Tailoring Services',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(3,	'Beauty Salon',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(4,	'Photography',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(5,	'Entertainment, Party & Events Planning',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(6,	'Massage & Fitness Centre',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(7,	'Barber Shop',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(8,	'Cleaning Services',	1,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(9,	'Contractor, Plumbing & Interior Design',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(10,	'Warehouse & Equipment Rental',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(11,	'Workshop (Wood & Metal)',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(12,	'Concrete/Balast Manufacturing',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(13,	'Real Estate Broker/Agent',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(14,	'House Repair & Maintenance',	2,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(15,	'Security System Services',	3,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(16,	'Legal Services',	3,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(17,	'Security Guard Company',	3,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(18,	'Motor Bike Transportation',	4,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(19,	'Taxi & Rental Services',	4,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(20,	'Boat Services',	4,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(21,	'Towing',	4,	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(22,	'Firewood & Charcoal Vendor',	5,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(23,	'Oil & Gas Distribution',	5,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(24,	'Water Vending',	5,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(25,	'Dentistry',	6,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(26,	'Pharmacy/Dispensing Chemist',	6,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(27,	'Agro-vet',	6,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(28,	'Private Health Services',	6,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(29,	'Household Utensils',	7,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(30,	'Building & Construction Material',	7,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(31,	'Electronic Accessory shops/Repairs',	7,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(32,	'Green Grocery (Fruit/Vegetables)',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(33,	'Food Kiosks',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(34,	'Retail Shop',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(35,	'Guest House/Lodges',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(36,	'Bar/Restaurant',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(37,	'Ice Cream',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(38,	'Hawking - Mobile merchandise services',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(39,	'Caterer',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(40,	'Beverage Manufacturing - Juice etc',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(41,	'Seafood - Fish vendor',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(42,	'Cereals',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(43,	'Meat Vendor - Butchery',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(44,	'Bakery (Bread & Confectionaries)',	8,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(45,	'Video Production',	9,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(46,	'Travel Agency',	9,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(47,	'Bureu & Publishing Services',	9,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(48,	'Business Consultant (Records keeping)',	9,	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(49,	'Cosmetic Shop',	10,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(50,	'Footware Shop',	10,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(51,	'New Clothes',	10,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(52,	'Second Hand Clothes',	10,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(53,	'Book Keeping & Collections Agency',	11,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(54,	'Pawn Brokers (Shylock)',	11,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(55,	'Mobile Money Services (M-PESA)',	11,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(56,	'Insurance Services',	11,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(57,	'Motor Vehicle/Bike Repair',	12,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(58,	'Automotive Part Sale',	12,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(59,	'Car Wash/Detailing',	12,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(60,	'New Motor Vehicle/Bike Sales',	12,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(61,	'Farming',	13,	'2023-05-08 16:00:10',	'2023-05-08 16:00:10'),
(62,	'Welding',	14,	'2023-05-08 16:00:29',	'2023-05-08 16:00:29'),
(63,	'Tailoring',	10,	'2023-05-08 16:00:57',	'2023-05-08 16:00:57'),
(64,	'Wines & Spirits Retailer',	15,	'2023-05-08 16:01:27',	'2023-05-08 16:01:27');

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  UNIQUE KEY `cache_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `days` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `days`, `created_at`, `updated_at`) VALUES
(1,	'1-30 Days',	'30',	NULL,	NULL),
(2,	'31-60 Days',	'60',	NULL,	NULL),
(3,	'61-90 Days',	'90',	NULL,	NULL),
(4,	'91-120 Days',	'120',	NULL,	NULL),
(5,	'121-150 Days',	'150',	NULL,	NULL),
(6,	'Over 150 Days',	'151',	NULL,	NULL);

DROP TABLE IF EXISTS `checkoff_employer_smses`;
CREATE TABLE `checkoff_employer_smses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employer_id` bigint(20) unsigned NOT NULL,
  `message` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkoff_employer_smses_employer_id_foreign` (`employer_id`),
  CONSTRAINT `checkoff_employer_smses_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `check_off_employers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_employees`;
CREATE TABLE `check_off_employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referee_id` bigint(20) unsigned NOT NULL,
  `next_of_kin_id` bigint(20) unsigned NOT NULL,
  `employer_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `phone_number` varchar(191) NOT NULL,
  `id_number` varchar(191) NOT NULL,
  `primary_email` varchar(191) NOT NULL,
  `institution_email` varchar(191) DEFAULT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `marital_status` enum('Married','Single') NOT NULL,
  `date_of_employment` date NOT NULL,
  `terms_of_employment` enum('Permanent','Contract','Casual') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_off_employees_phone_number_unique` (`phone_number`),
  UNIQUE KEY `check_off_employees_id_number_unique` (`id_number`),
  KEY `check_off_employees_referee_id_foreign` (`referee_id`),
  KEY `check_off_employees_next_of_kin_id_foreign` (`next_of_kin_id`),
  KEY `check_off_employees_employer_id_foreign` (`employer_id`),
  CONSTRAINT `check_off_employees_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `check_off_employers` (`id`),
  CONSTRAINT `check_off_employees_next_of_kin_id_foreign` FOREIGN KEY (`next_of_kin_id`) REFERENCES `check_off_employee_next_of_kin` (`id`),
  CONSTRAINT `check_off_employees_referee_id_foreign` FOREIGN KEY (`referee_id`) REFERENCES `check_off_employee_referees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_employee_next_of_kin`;
CREATE TABLE `check_off_employee_next_of_kin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `phone_number` varchar(191) NOT NULL,
  `relationship` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_employee_referees`;
CREATE TABLE `check_off_employee_referees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `address` varchar(191) DEFAULT NULL,
  `phone_number` varchar(191) NOT NULL,
  `relationship` varchar(191) NOT NULL,
  `occupation` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_employee_sms`;
CREATE TABLE `check_off_employee_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned DEFAULT NULL,
  `sms` longtext NOT NULL,
  `phone_number` mediumtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_off_employee_sms_employee_id_foreign` (`employee_id`),
  CONSTRAINT `check_off_employee_sms_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `check_off_employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_employers`;
CREATE TABLE `check_off_employers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `location` varchar(191) DEFAULT NULL,
  `contact_name` varchar(191) DEFAULT NULL,
  `contact_phone_number` varchar(191) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `otp` varchar(191) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_off_employers_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_loans`;
CREATE TABLE `check_off_loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `loan_amount` int(10) unsigned NOT NULL,
  `interest` decimal(8,2) unsigned NOT NULL,
  `total_amount` decimal(8,2) unsigned NOT NULL,
  `end_date` date NOT NULL,
  `effective_date` date NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_date` varchar(191) DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `disbursed` tinyint(1) NOT NULL DEFAULT 0,
  `disbursed_by` bigint(20) unsigned DEFAULT NULL,
  `settled` tinyint(1) NOT NULL DEFAULT 0,
  `settled_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `rejected` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `employer_approval_id` bigint(20) unsigned DEFAULT NULL,
  `rejected_by_employer` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_off_loans_product_id_foreign` (`product_id`),
  KEY `check_off_loans_employee_id_foreign` (`employee_id`),
  KEY `check_off_loans_approved_by_foreign` (`approved_by`),
  KEY `check_off_loans_disbursed_by_foreign` (`disbursed_by`),
  KEY `check_off_loans_rejected_by_foreign` (`rejected_by`),
  KEY `check_off_loans_employer_approval_id_foreign` (`employer_approval_id`),
  CONSTRAINT `check_off_loans_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `check_off_loans_disbursed_by_foreign` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `check_off_loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `check_off_employees` (`id`),
  CONSTRAINT `check_off_loans_employer_approval_id_foreign` FOREIGN KEY (`employer_approval_id`) REFERENCES `check_off_employers` (`id`),
  CONSTRAINT `check_off_loans_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `check_off_products` (`id`),
  CONSTRAINT `check_off_loans_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_mpesa_disbursement_requests`;
CREATE TABLE `check_off_mpesa_disbursement_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `ConversationID` varchar(191) NOT NULL,
  `OriginatorConversationID` varchar(191) NOT NULL,
  `ResponseCode` varchar(191) NOT NULL,
  `ResponseDescription` varchar(191) NOT NULL,
  `issued` tinyint(1) NOT NULL DEFAULT 0,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_off_mpesa_disbursement_requests_loan_id_foreign` (`loan_id`),
  KEY `check_off_mpesa_disbursement_requests_requested_by_foreign` (`requested_by`),
  CONSTRAINT `check_off_mpesa_disbursement_requests_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `check_off_loans` (`id`),
  CONSTRAINT `check_off_mpesa_disbursement_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_mpesa_disbursement_responses`;
CREATE TABLE `check_off_mpesa_disbursement_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `OriginatorConversationID` varchar(191) DEFAULT NULL,
  `ConversationID` varchar(191) DEFAULT NULL,
  `TransactionID` varchar(191) DEFAULT NULL,
  `TransactionAmount` decimal(8,2) DEFAULT NULL,
  `TransactionReceipt` varchar(191) DEFAULT NULL,
  `B2CRecipientIsRegisteredCustomer` varchar(191) DEFAULT NULL,
  `issued` tinyint(1) NOT NULL DEFAULT 0,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `ResultCode` varchar(191) DEFAULT NULL,
  `ResultDesc` varchar(191) DEFAULT NULL,
  `B2CChargesPaidAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `ReceiverPartyPublicName` varchar(191) DEFAULT NULL,
  `TransactionCompletedDateTime` timestamp NULL DEFAULT NULL,
  `B2CUtilityAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `B2CWorkingAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_off_mpesa_disbursement_responses_loan_id_foreign` (`loan_id`),
  CONSTRAINT `check_off_mpesa_disbursement_responses_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `check_off_loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_mpesa_disbursement_transactions`;
CREATE TABLE `check_off_mpesa_disbursement_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `transaction_receipt` varchar(191) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `channel` varchar(191) NOT NULL DEFAULT 'MPESA DISBURSEMENT API',
  `disbursed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_off_mpesa_disbursement_transactions_loan_id_foreign` (`loan_id`),
  CONSTRAINT `check_off_mpesa_disbursement_transactions_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `check_off_loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_payments`;
CREATE TABLE `check_off_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `employer_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(191) NOT NULL DEFAULT 'MPESA-PAYBILL',
  `TransID` varchar(191) NOT NULL,
  `TransAmount` decimal(8,2) NOT NULL,
  `TransTime` varchar(191) NOT NULL,
  `BusinessShortCode` varchar(191) NOT NULL,
  `BillRefNumber` varchar(191) NOT NULL,
  `InvoiceNumber` varchar(191) DEFAULT NULL,
  `OrgAccountBalance` decimal(8,2) DEFAULT NULL,
  `MSISDN` varchar(191) NOT NULL,
  `FirstName` varchar(191) DEFAULT NULL,
  `MiddleName` varchar(191) DEFAULT NULL,
  `LastName` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_off_payments_transid_unique` (`TransID`),
  KEY `check_off_payments_loan_id_foreign` (`loan_id`),
  KEY `check_off_payments_employer_id_foreign` (`employer_id`),
  KEY `check_off_payments_employee_id_foreign` (`employee_id`),
  CONSTRAINT `check_off_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `check_off_employees` (`id`),
  CONSTRAINT `check_off_payments_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `check_off_employers` (`id`),
  CONSTRAINT `check_off_payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `check_off_loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `check_off_products`;
CREATE TABLE `check_off_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `interest` int(11) NOT NULL,
  `period` int(11) NOT NULL COMMENT 'period in months',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `check_off_products` (`id`, `name`, `interest`, `period`, `status`, `created_at`, `updated_at`) VALUES
(1,	'Willow Advance',	12,	31,	1,	'2022-11-15 13:30:18',	'2022-11-15 13:30:18');

DROP TABLE IF EXISTS `collaterals`;
CREATE TABLE `collaterals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `item` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `serial_no` varchar(191) DEFAULT NULL,
  `market_value` varchar(191) NOT NULL,
  `image_url` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collaterals_loan_id_foreign` (`loan_id`),
  CONSTRAINT `collaterals_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `counties`;
CREATE TABLE `counties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `counties` (`id`, `cname`, `created_at`, `updated_at`) VALUES
(1,	'Mombasa',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(2,	'Kwale',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(3,	'Kilifi',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(4,	'Tana-River',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(5,	'Lamu',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(6,	'Taita-Taveta',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(7,	'Garissa',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(8,	'Wajir',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(9,	'Mandera',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(10,	'Marsabit',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(11,	'Isiolo',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(12,	'Meru',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(13,	'Tharaka-Nithi',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(14,	'Embu',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(15,	'Kitui',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(16,	'Machakos',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(17,	'Makueni',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(18,	'Nyandarua',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(19,	'Nyeri',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(20,	'Kirinyaga',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(21,	'Muranga',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(22,	'Kiambu',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(23,	'Turkana',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(24,	'West Pokot',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(25,	'Samburu',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(26,	'Trans-Nzoia',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(27,	'Uasin Gishu',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(28,	'Elgeyo-Marakwet',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(29,	'Nandi',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(30,	'Baringo',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(31,	'Laikipia',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(32,	'Nakuru',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(33,	'Narok',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(34,	'Kajiado',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(35,	'Kericho',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(36,	'Bomet',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(37,	'Kakamega',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(38,	'Vihiga',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(39,	'Bungoma',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(40,	'Busia',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(41,	'Siaya',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(42,	'Kisumu',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(43,	'Homa Bay',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(44,	'Migori',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(45,	'Kisii',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(46,	'Nyamira',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19'),
(47,	'Nairobi',	'2019-04-15 13:02:19',	'2019-04-15 13:02:19');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(191) NOT NULL DEFAULT 'individual',
  `title` varchar(191) NOT NULL,
  `fname` varchar(191) NOT NULL,
  `mname` varchar(191) DEFAULT NULL,
  `lname` varchar(191) NOT NULL,
  `field_agent_id` bigint(20) unsigned NOT NULL,
  `guarantor_id` bigint(20) unsigned DEFAULT NULL,
  `tax_pin` varchar(191) DEFAULT NULL,
  `dob` varchar(191) DEFAULT NULL,
  `phone` varchar(191) NOT NULL,
  `alternate_phone` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `document_id` bigint(20) unsigned NOT NULL,
  `id_no` varchar(191) NOT NULL,
  `marital_status` varchar(191) DEFAULT NULL,
  `gender` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_employed` tinyint(3) unsigned DEFAULT NULL,
  `employment_status` varchar(191) DEFAULT NULL,
  `income_range_id` bigint(20) unsigned DEFAULT NULL,
  `employment_date` varchar(191) DEFAULT NULL,
  `employer` varchar(191) DEFAULT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `industry_id` bigint(20) unsigned DEFAULT NULL,
  `business_type_id` bigint(20) unsigned DEFAULT NULL,
  `prequalified_amount` int(11) NOT NULL,
  `previous_prequalified_amount` int(11) DEFAULT NULL,
  `times_loan_applied` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `customers_document_id_foreign` (`document_id`),
  KEY `customers_guarantor_id_foreign` (`guarantor_id`),
  KEY `customers_branch_id_foreign` (`branch_id`),
  KEY `customers_account_id_foreign` (`account_id`),
  KEY `customers_industry_id_foreign` (`industry_id`),
  KEY `customers_business_type_id_foreign` (`business_type_id`),
  KEY `customers_income_range_id_foreign` (`income_range_id`),
  CONSTRAINT `customers_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_business_type_id_foreign` FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_guarantor_id_foreign` FOREIGN KEY (`guarantor_id`) REFERENCES `guarantors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_income_range_id_foreign` FOREIGN KEY (`income_range_id`) REFERENCES `income_ranges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_industry_id_foreign` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `type`, `title`, `fname`, `mname`, `lname`, `field_agent_id`, `guarantor_id`, `tax_pin`, `dob`, `phone`, `alternate_phone`, `email`, `document_id`, `id_no`, `marital_status`, `gender`, `created_at`, `updated_at`, `status`, `is_employed`, `employment_status`, `income_range_id`, `employment_date`, `employer`, `branch_id`, `account_id`, `industry_id`, `business_type_id`, `prequalified_amount`, `previous_prequalified_amount`, `times_loan_applied`) VALUES
(11972,	'Individual',	'Mr',	'Jane',	'Atieno',	'Aloo',	10,	NULL,	NULL,	NULL,	'254740782174',	'254740782174',	'jane@gmail.com',	1,	'45454545',	NULL,	NULL,	'2024-08-23 11:14:33',	'2024-08-23 11:14:33',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1),
(11973,	'Individual',	'Mr',	'George',	'Kioko',	'Wahiki',	10,	NULL,	NULL,	NULL,	'254740025323',	'254740025323',	'george@gmail.com',	1,	'67467437643',	NULL,	NULL,	'2024-08-23 11:20:02',	'2024-08-23 11:20:02',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1),
(11974,	'Individual',	'Mr',	'Jacob',	'Akama',	'Omuok',	10,	NULL,	NULL,	NULL,	'254796095303',	'254',	NULL,	1,	'34577380',	NULL,	NULL,	'2024-09-05 15:56:18',	'2024-09-05 15:56:18',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1),
(11975,	'Individual',	'Mrs',	'Mercy',	'Anyango',	'Sunguti',	10,	NULL,	NULL,	NULL,	'254701369768',	'254',	NULL,	1,	'33865837',	NULL,	NULL,	'2024-09-05 15:56:46',	'2024-09-05 15:56:46',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1),
(11976,	'Individual',	'Mrs',	'Doreen',	'Nafula',	'Wekesa',	10,	NULL,	NULL,	NULL,	'254799653416',	'254',	NULL,	1,	'26222866',	NULL,	NULL,	'2024-09-05 15:58:24',	'2024-09-05 15:58:24',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1),
(11977,	'Individual',	'Mrs',	'Isaac',	'Isaac',	'Oloo',	10,	NULL,	NULL,	NULL,	'254769652516',	'254254',	NULL,	1,	'34577381',	NULL,	NULL,	'2024-09-05 16:03:29',	'2024-09-05 16:20:52',	1,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	NULL,	NULL,	7000,	NULL,	1);

DROP TABLE IF EXISTS `customer_documents`;
CREATE TABLE `customer_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `profile_photo_path` varchar(191) DEFAULT NULL,
  `id_front_path` varchar(191) DEFAULT NULL,
  `id_back_path` varchar(191) DEFAULT NULL,
  `mpesa_statement_path` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_documents_customer_id_foreign` (`customer_id`),
  KEY `customer_documents_user_id_foreign` (`user_id`),
  CONSTRAINT `customer_documents_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customer_group`;
CREATE TABLE `customer_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  `role` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_group_customer_id_foreign` (`customer_id`),
  KEY `customer_group_group_id_foreign` (`group_id`),
  CONSTRAINT `customer_group_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_group_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customer_history_threads`;
CREATE TABLE `customer_history_threads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `remark` longtext NOT NULL,
  `date_visited` date NOT NULL,
  `next_scheduled_visit` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_history_threads_customer_id_foreign` (`customer_id`),
  KEY `customer_history_threads_user_id_foreign` (`user_id`),
  CONSTRAINT `customer_history_threads_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_history_threads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customer_interactions`;
CREATE TABLE `customer_interactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `interaction_type_id` bigint(20) unsigned NOT NULL,
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `remark` longtext NOT NULL,
  `next_scheduled_interaction` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `interaction_category_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 1,
  `followed_up` int(11) NOT NULL DEFAULT 1,
  `target` int(11) NOT NULL DEFAULT 2,
  `closed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_interactions_customer_id_foreign` (`customer_id`),
  KEY `customer_interactions_user_id_foreign` (`user_id`),
  KEY `customer_interactions_interaction_type_id_foreign` (`interaction_type_id`),
  CONSTRAINT `customer_interactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_interactions_interaction_type_id_foreign` FOREIGN KEY (`interaction_type_id`) REFERENCES `customer_interaction_types` (`id`),
  CONSTRAINT `customer_interactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customer_interaction_categories`;
CREATE TABLE `customer_interaction_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_interaction_categories` (`id`, `name`, `priority`, `created_at`, `updated_at`) VALUES
(1,	'Customer Satisfaction survey',	2,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17'),
(2,	'Prepayment',	1,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17'),
(3,	'Due Collection',	1,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17'),
(4,	'Arrear Collection',	1,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17'),
(5,	'First Visit Lo',	2,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17'),
(6,	'First Visit Co',	2,	'2023-07-25 10:42:17',	'2023-07-25 10:42:17');

DROP TABLE IF EXISTS `customer_interaction_followups`;
CREATE TABLE `customer_interaction_followups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `follow_up_id` bigint(20) unsigned NOT NULL,
  `follow_by` bigint(20) unsigned NOT NULL,
  `remark` longtext NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `next_scheduled_interaction` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_interaction_followups_follow_up_id_foreign` (`follow_up_id`),
  CONSTRAINT `customer_interaction_followups_follow_up_id_foreign` FOREIGN KEY (`follow_up_id`) REFERENCES `customer_interactions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `customer_interaction_types`;
CREATE TABLE `customer_interaction_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_interaction_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1,	'Physical Visit',	'2023-02-20 12:07:10',	'2023-07-25 10:42:17'),
(2,	'Phone Call',	'2023-02-20 12:07:10',	'2023-07-25 10:42:17'),
(3,	'Text/Whatsapp Conversation',	'2023-02-20 12:07:10',	'2023-07-25 10:42:17'),
(4,	'Office Visit',	'2023-02-20 12:07:10',	'2023-07-25 10:42:17');

DROP TABLE IF EXISTS `customer_locations`;
CREATE TABLE `customer_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `postal_address` varchar(191) DEFAULT NULL,
  `postal_code` varchar(191) DEFAULT NULL,
  `country` varchar(191) NOT NULL DEFAULT 'Kenya',
  `county_id` bigint(20) unsigned NOT NULL,
  `constituency` varchar(191) NOT NULL,
  `ward` varchar(191) NOT NULL,
  `physical_address` varchar(191) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `business_address` varchar(191) DEFAULT NULL,
  `business_latitude` decimal(10,7) DEFAULT NULL,
  `business_longitude` decimal(10,7) DEFAULT NULL,
  `residence_type` varchar(191) DEFAULT NULL,
  `years_lived` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `home_coordinates` varchar(191) DEFAULT NULL,
  `business_coordinates` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_locations_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_locations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_locations` (`id`, `customer_id`, `postal_address`, `postal_code`, `country`, `county_id`, `constituency`, `ward`, `physical_address`, `latitude`, `longitude`, `business_address`, `business_latitude`, `business_longitude`, `residence_type`, `years_lived`, `created_at`, `updated_at`, `home_coordinates`, `business_coordinates`) VALUES
(1,	11972,	NULL,	NULL,	'Kenya',	1,	'Kisumu',	'Maseno',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-08-23 11:14:33',	'2024-08-23 11:14:33',	NULL,	NULL),
(2,	11973,	NULL,	NULL,	'Kenya',	1,	'Mombasa',	'Kiligwa',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-08-23 11:20:02',	'2024-08-23 11:20:02',	NULL,	NULL),
(3,	11974,	NULL,	NULL,	'Kenya',	41,	'Bondo',	'West Yimbo',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-09-05 15:56:18',	'2024-09-05 15:56:18',	NULL,	NULL),
(4,	11975,	NULL,	NULL,	'Kenya',	37,	'Lugari',	'BUMULA',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-09-05 15:56:46',	'2024-09-05 15:56:46',	NULL,	NULL),
(5,	11976,	NULL,	NULL,	'Kenya',	41,	'Madiany',	'West Uyoma',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-09-05 15:58:24',	'2024-09-05 15:58:24',	NULL,	NULL),
(6,	11977,	NULL,	NULL,	'Kenya',	40,	'Matayos',	'Matayos North',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2024-09-05 16:03:29',	'2024-09-05 16:20:52',	NULL,	NULL);

DROP TABLE IF EXISTS `customer_referee`;
CREATE TABLE `customer_referee` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `referee_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_referee_customer_id_foreign` (`customer_id`),
  KEY `customer_referee_referee_id_foreign` (`referee_id`),
  CONSTRAINT `customer_referee_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `customer_referee_referee_id_foreign` FOREIGN KEY (`referee_id`) REFERENCES `referees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_referee` (`id`, `customer_id`, `referee_id`, `created_at`, `updated_at`) VALUES
(1,	11972,	1,	'2024-08-23 11:14:33',	'2024-08-23 11:14:33'),
(2,	11973,	2,	'2024-08-23 11:20:02',	'2024-08-23 11:20:02'),
(3,	11974,	3,	'2024-09-05 15:56:18',	'2024-09-05 15:56:18'),
(4,	11975,	4,	'2024-09-05 15:56:46',	'2024-09-05 15:56:46'),
(5,	11976,	5,	'2024-09-05 15:58:24',	'2024-09-05 15:58:24'),
(6,	11977,	6,	'2024-09-05 16:03:29',	'2024-09-05 16:03:29');

DROP TABLE IF EXISTS `customer_sms`;
CREATE TABLE `customer_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `sms` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_sms_customer_id_foreign` (`customer_id`),
  KEY `customer_sms_branch_id_foreign` (`branch_id`),
  CONSTRAINT `customer_sms_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_sms_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `documents` (`id`, `dname`, `created_at`, `updated_at`) VALUES
(1,	'National ID',	NULL,	NULL),
(2,	'Passport',	NULL,	NULL),
(3,	'Alien ID',	NULL,	NULL),
(4,	'Driving License',	NULL,	NULL);

DROP TABLE IF EXISTS `employers`;
CREATE TABLE `employers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ename` varchar(191) NOT NULL,
  `location` varchar(191) NOT NULL,
  `latitude` varchar(191) NOT NULL,
  `longitude` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ephone` varchar(191) NOT NULL,
  `eemail` varchar(191) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_type_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `date_payed` datetime NOT NULL,
  `paid_by` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_type_id_foreign` (`expense_type_id`),
  KEY `expenses_branch_id_foreign` (`branch_id`),
  CONSTRAINT `expenses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_expense_type_id_foreign` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `expense_types`;
CREATE TABLE `expense_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `expense_types` (`id`, `expense_name`, `created_at`, `updated_at`) VALUES
(1,	'Stationery',	'2019-04-15 13:02:23',	'2019-04-15 13:02:23'),
(2,	'Field',	'2019-04-15 13:02:23',	'2019-04-15 13:02:23'),
(3,	'Airtime',	'2019-04-15 13:02:23',	'2019-04-15 13:02:23'),
(4,	'Fuel',	'2019-04-15 13:02:23',	'2019-04-15 13:02:23'),
(5,	'R/Maintenance',	'2019-04-15 13:02:23',	'2019-04-15 13:02:23'),
(6,	'Office Lunch',	'2024-01-15 05:58:18',	'2024-01-15 05:58:18'),
(7,	'H/R',	'2024-01-15 05:58:29',	'2024-01-15 05:58:29'),
(8,	'Director Expenses',	'2024-01-15 05:58:44',	'2024-01-15 05:58:44'),
(9,	'Rent',	'2024-01-15 05:58:56',	'2024-01-15 05:58:56');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `unique_id` varchar(191) NOT NULL,
  `leader_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `loan_officer_id` bigint(20) unsigned DEFAULT NULL,
  `customers_count` int(11) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approval_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groups_leader_id_foreign` (`leader_id`),
  KEY `groups_created_by_foreign` (`created_by`),
  KEY `groups_branch_id_foreign` (`branch_id`),
  KEY `groups_approved_by_foreign` (`approved_by`),
  KEY `groups_loan_officer_id_foreign` (`loan_officer_id`),
  CONSTRAINT `groups_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groups_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_leader_id_foreign` FOREIGN KEY (`leader_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_loan_officer_id_foreign` FOREIGN KEY (`loan_officer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `guarantors`;
CREATE TABLE `guarantors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gname` varchar(191) NOT NULL,
  `gphone` varchar(191) NOT NULL,
  `gdob` varchar(191) NOT NULL,
  `gid` varchar(191) NOT NULL,
  `marital_status` varchar(191) NOT NULL,
  `location` varchar(191) NOT NULL,
  `latitude` varchar(191) NOT NULL,
  `longitude` varchar(191) NOT NULL,
  `industry_id` bigint(20) unsigned NOT NULL,
  `business_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guarantors_industry_id_foreign` (`industry_id`),
  KEY `guarantors_business_id_foreign` (`business_id`),
  CONSTRAINT `guarantors_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guarantors_industry_id_foreign` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `income_ranges`;
CREATE TABLE `income_ranges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `income_ranges` (`id`, `name`) VALUES
(1,	'Below KSh 10,000'),
(2,	'KSh 10,000 - 20,000'),
(3,	'KSh 20,000 - 30,000'),
(4,	'KSh 30,000 - 40,000'),
(5,	'KSh 40,000 - 50,000'),
(6,	'KSh 50,000 - 60,000'),
(7,	'KSh 60,000 - 70,000'),
(8,	'KSh 70,000 - 80,000'),
(9,	'KSh 80,000 - 90,000'),
(10,	'Above KSh 90,000');

DROP TABLE IF EXISTS `industries`;
CREATE TABLE `industries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `iname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `industries` (`id`, `iname`, `created_at`, `updated_at`) VALUES
(1,	'Personal Services',	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(2,	'Real Estate & Housing',	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(3,	'Safety/Security & Legal',	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(4,	'Transportation',	'2019-04-15 13:02:20',	'2019-04-15 13:02:20'),
(5,	'Natural Resource/Environment',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(6,	'Human Health & Animal Services',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(7,	'General Hardware & Electronics',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(8,	'Food & Hospitality',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(9,	'Business & Information',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(10,	'Fashion & Beauty Products',	'2019-04-15 13:02:21',	'2019-04-15 13:02:21'),
(11,	'Finance & Insurance',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(12,	'Automobile Services',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(13,	'Agriculture',	'2023-05-08 15:58:26',	'2023-05-08 15:58:26'),
(14,	'Construction',	'2023-05-08 15:58:39',	'2023-05-08 15:58:39'),
(15,	'Retail',	'2023-05-08 15:59:11',	'2023-05-08 15:59:11');

DROP TABLE IF EXISTS `installments`;
CREATE TABLE `installments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `principal_amount` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `position` int(11) NOT NULL,
  `for_rollover` tinyint(1) NOT NULL DEFAULT 0,
  `interest` int(11) NOT NULL,
  `lp_fee` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `amount_paid` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `last_payment_date` date DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `in_arrear` tinyint(1) NOT NULL DEFAULT 0,
  `being_paid` tinyint(1) NOT NULL DEFAULT 0,
  `interest_payment_date` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `installments_loan_id_foreign` (`loan_id`),
  CONSTRAINT `installments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `investments`;
CREATE TABLE `investments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `transaction_no` varchar(191) NOT NULL,
  `channel` varchar(191) DEFAULT NULL,
  `transaction_id` varchar(191) DEFAULT NULL,
  `date_payed` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investments_user_id_foreign` (`user_id`),
  CONSTRAINT `investments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `phone_number` varchar(191) NOT NULL,
  `type_of_business` varchar(191) NOT NULL,
  `estimated_amount` varchar(191) NOT NULL,
  `location` varchar(191) NOT NULL,
  `officer_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_officer_id_foreign` (`officer_id`),
  CONSTRAINT `leads_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `loans`;
CREATE TABLE `loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_account` varchar(191) NOT NULL,
  `loan_amount` int(11) NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `loan_type_id` varchar(191) DEFAULT NULL,
  `date_created` date NOT NULL,
  `end_date` date NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_date` varchar(191) DEFAULT NULL,
  `disbursed` tinyint(1) NOT NULL DEFAULT 0,
  `disbursement_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purpose` varchar(191) NOT NULL,
  `document_path` varchar(191) DEFAULT NULL,
  `settled` tinyint(1) NOT NULL DEFAULT 0,
  `rolled_over` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `last_edited_by` bigint(20) unsigned DEFAULT NULL,
  `restructured` tinyint(1) NOT NULL DEFAULT 0,
  `has_lp_fee` tinyint(1) NOT NULL DEFAULT 0,
  `self_application` tinyint(1) NOT NULL DEFAULT 0,
  `total_amount` double DEFAULT 0,
  `total_amount_paid` double NOT NULL DEFAULT 0,
  `create_loan_ip` varchar(191) DEFAULT NULL,
  `approve_loan_ip` varchar(191) DEFAULT NULL,
  `disburse_loan_ip` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loans_product_id_foreign` (`product_id`),
  KEY `loans_customer_id_foreign` (`customer_id`),
  KEY `loans_group_id_foreign` (`group_id`),
  KEY `loans_created_by_foreign` (`created_by`),
  KEY `loans_last_edited_by_foreign` (`last_edited_by`),
  CONSTRAINT `loans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loans_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loans_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loans_last_edited_by_foreign` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loans_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loans` (`id`, `loan_account`, `loan_amount`, `product_id`, `customer_id`, `group_id`, `loan_type_id`, `date_created`, `end_date`, `approved`, `approved_date`, `disbursed`, `disbursement_date`, `created_at`, `updated_at`, `purpose`, `document_path`, `settled`, `rolled_over`, `approved_by`, `disbursed_by`, `created_by`, `last_edited_by`, `restructured`, `has_lp_fee`, `self_application`, `total_amount`, `total_amount_paid`, `create_loan_ip`, `approve_loan_ip`, `disburse_loan_ip`) VALUES
(1,	'Nairobi-08/23-6392',	5600,	5,	11972,	NULL,	'1',	'2024-08-23',	'2024-09-20',	0,	NULL,	0,	NULL,	'2024-08-23 11:14:33',	'2024-08-23 11:14:33',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	10,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL),
(2,	'Nairobi-08/23-52',	5600,	5,	11973,	NULL,	'1',	'2024-08-23',	'2024-09-20',	0,	NULL,	0,	NULL,	'2024-08-23 11:20:02',	'2024-08-23 11:20:02',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	10,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL),
(3,	'Nairobi-09/05-5146',	5600,	5,	11974,	NULL,	'1',	'2024-09-05',	'2024-10-03',	0,	NULL,	0,	NULL,	'2024-09-05 15:56:18',	'2024-09-05 15:56:18',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	2,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL),
(4,	'Nairobi-09/05-3616',	5600,	5,	11975,	NULL,	'1',	'2024-09-05',	'2024-10-03',	0,	NULL,	0,	NULL,	'2024-09-05 15:56:46',	'2024-09-05 15:56:46',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	2,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL),
(5,	'Nairobi-09/05-6226',	5600,	5,	11976,	NULL,	'1',	'2024-09-05',	'2024-10-03',	0,	NULL,	0,	NULL,	'2024-09-05 15:58:24',	'2024-09-05 15:58:24',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	2,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL),
(6,	'Nairobi-09/05-5206',	5600,	5,	11977,	NULL,	'1',	'2024-09-05',	'2024-10-03',	0,	NULL,	0,	NULL,	'2024-09-05 16:03:29',	'2024-09-05 16:03:29',	'Business Expense',	NULL,	0,	0,	NULL,	NULL,	2,	NULL,	0,	0,	0,	7000,	0,	'41.139.191.127',	NULL,	NULL);

DROP TABLE IF EXISTS `loan_officers`;
CREATE TABLE `loan_officers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `ophone` varchar(191) NOT NULL,
  `oemail` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `branch_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `loan_types`;
CREATE TABLE `loan_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loan_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1,	'daily',	'2020-05-27 21:50:42',	'2020-05-27 21:50:42'),
(2,	'weekly',	'2020-05-27 21:50:42',	'2020-05-27 21:50:42');

DROP TABLE IF EXISTS `login_tokens`;
CREATE TABLE `login_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(191) NOT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `in_use` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `login_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(6,	'App\\User',	1),
(6,	'App\\User',	2),
(6,	'App\\User',	3),
(4,	'App\\User',	10);

DROP TABLE IF EXISTS `mpesa_settlements`;
CREATE TABLE `mpesa_settlements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ResultCode` varchar(191) NOT NULL,
  `ResultDesc` varchar(191) NOT NULL,
  `OriginatorConversationID` varchar(191) NOT NULL,
  `ConversationID` varchar(191) NOT NULL,
  `TransactionID` varchar(191) NOT NULL,
  `TransactionAmount` varchar(191) DEFAULT NULL,
  `TransactionReceipt` varchar(191) DEFAULT NULL,
  `B2CRecipientIsRegisteredCustomer` varchar(191) DEFAULT NULL,
  `B2CChargesPaidAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `ReceiverPartyPublicName` varchar(191) DEFAULT NULL,
  `TransactionCompletedDateTime` varchar(191) DEFAULT NULL,
  `B2CUtilityAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `B2CWorkingAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `mpesa_transactions`;
CREATE TABLE `mpesa_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `ResultCode` varchar(191) NOT NULL,
  `ResultDesc` varchar(191) NOT NULL,
  `OriginatorConversationID` varchar(191) NOT NULL,
  `ConversationID` varchar(191) NOT NULL,
  `TransactionID` varchar(191) NOT NULL,
  `TransactionAmount` varchar(191) DEFAULT NULL,
  `TransactionReceipt` varchar(191) DEFAULT NULL,
  `B2CRecipientIsRegisteredCustomer` varchar(191) DEFAULT NULL,
  `B2CChargesPaidAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `ReceiverPartyPublicName` varchar(191) DEFAULT NULL,
  `TransactionCompletedDateTime` varchar(191) DEFAULT NULL,
  `B2CUtilityAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `B2CWorkingAccountAvailableFunds` varchar(191) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `mrequests`;
CREATE TABLE `mrequests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ConversationID` varchar(191) NOT NULL,
  `OriginatorConversationID` varchar(191) NOT NULL,
  `ResponseCode` varchar(191) NOT NULL,
  `ResponseDescription` varchar(191) NOT NULL,
  `settled` tinyint(1) NOT NULL DEFAULT 0,
  `loan_id` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `disburse_loan_ip` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `msettings`;
CREATE TABLE `msettings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paybill` varchar(1000) NOT NULL,
  `SecurityCredential` varchar(2000) NOT NULL,
  `InitiatorName` varchar(1000) NOT NULL,
  `Consumer_Key` varchar(1000) NOT NULL,
  `Consumer_Secret` varchar(1000) NOT NULL,
  `Utility_balance` varchar(191) NOT NULL DEFAULT 'Utility Account|KES|0.00|0.00|0.00|0.00',
  `MMF_balance` varchar(191) NOT NULL DEFAULT 'Working Account|KES|0.00|0.00|0.00|0.00',
  `last_updated` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `msettings` (`id`, `paybill`, `SecurityCredential`, `InitiatorName`, `Consumer_Key`, `Consumer_Secret`, `Utility_balance`, `MMF_balance`, `last_updated`, `created_at`, `updated_at`) VALUES
(1,	'4123359',	'HDThC4UtlJUzWxoR4ygrcoDfWnR0fd5zbFczgHg3Hr5QLP2ALP7M79gVybhRdj4KtsvjHNDTc6R5AWgSUBlsP47tx5nUZPcncy9/1JtVkN5yslqld7ZI3wpjBQVaeyzKJa2vuLtRhmN8vVRYwlTNc0aABMmHtUVASejZxXEmZ83ReKAnkADfv1dO9Z6spsG8+oIm40iVXhQDTWwprkHbsCQqoP3OwRrAl2xd7c4spvRvWs6+F4lhj7v5W0wSXVnrZ9z9e2GicVnkKKuJO+PGGTMt6y/s8TyCgoYkwqTuoJs0nfjvhHzsmqzHnD3MewvrKk0Sl0jqo0W/BjfCH2LH3w==',	'LITSA WEB B2C Admin',	'98H46G7Ohz80hbGpG2hywsCehyq4CxTX',	'Ffi2iLNYh8cI6Z6L',	'Utility Account|KES|986598.00|986598.00|0.00|0.00',	'Working Account|KES|377489.00|377489.00|0.00|0.00',	'2024-01-15 13:17:33',	'2019-04-15 13:02:23',	'2024-01-15 13:17:33');

DROP TABLE IF EXISTS `next_of_kins`;
CREATE TABLE `next_of_kins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Kin_name` varchar(191) NOT NULL,
  `Kin_phone` varchar(191) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `relationship_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `next_of_kins_customer_id_foreign` (`customer_id`),
  CONSTRAINT `next_of_kins_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `date_payed` varchar(191) NOT NULL,
  `transaction_id` varchar(191) NOT NULL,
  `amount` varchar(191) NOT NULL,
  `channel` varchar(191) NOT NULL,
  `payment_type_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `for_rollover` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `payments_loan_id_foreign` (`loan_id`),
  CONSTRAINT `payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `payment_types`;
CREATE TABLE `payment_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1,	'Loan Settlement',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(2,	'Loan Disbursement',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(3,	'Processing Fee',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22');

DROP TABLE IF EXISTS `pending_rollovers`;
CREATE TABLE `pending_rollovers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `rollover_interest` int(11) NOT NULL,
  `rollover_due` int(11) NOT NULL,
  `rollover_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pending_rollovers_loan_id_foreign` (`loan_id`),
  CONSTRAINT `pending_rollovers_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `prequalified_amount_adjustments`;
CREATE TABLE `prequalified_amount_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `initial_amount` decimal(8,2) NOT NULL,
  `proposed_amount` decimal(8,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `initiated_by` bigint(20) unsigned NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prequalified_amount_adjustments_customer_id_foreign` (`customer_id`),
  KEY `prequalified_amount_adjustments_approved_by_foreign` (`approved_by`),
  KEY `prequalified_amount_adjustments_initiated_by_foreign` (`initiated_by`),
  CONSTRAINT `prequalified_amount_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prequalified_amount_adjustments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prequalified_amount_adjustments_initiated_by_foreign` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `prequalified_amount_adjustments` (`id`, `customer_id`, `initial_amount`, `proposed_amount`, `status`, `approved`, `approved_by`, `initiated_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1,	11977,	7000.00,	7000.00,	1,	0,	2,	2,	'2024-09-05 16:20:52',	'2024-09-05 16:20:52',	'2024-09-05 16:20:52');

DROP TABLE IF EXISTS `prequalified_loans`;
CREATE TABLE `prequalified_loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `prequalified_loans` (`id`, `amount`) VALUES
(42,	7000),
(43,	13500),
(44,	20250),
(45,	27000),
(46,	33760),
(47,	40500),
(48,	54000),
(49,	67500),
(50,	81000);

DROP TABLE IF EXISTS `pre_interactions`;
CREATE TABLE `pre_interactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  `due_date` datetime NOT NULL,
  `model_id` int(11) NOT NULL,
  `system_remark` longtext NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `interaction_category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pre_interactions_customer_id_foreign` (`customer_id`),
  KEY `pre_interactions_interaction_category_id_foreign` (`interaction_category_id`),
  CONSTRAINT `pre_interactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `pre_interactions_interaction_category_id_foreign` FOREIGN KEY (`interaction_category_id`) REFERENCES `customer_interaction_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_name` varchar(191) NOT NULL,
  `installments` int(11) NOT NULL,
  `interest` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `duration` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `product_name`, `installments`, `interest`, `status`, `created_at`, `updated_at`, `duration`) VALUES
(5,	'Biashara Loans 1',	28,	20,	1,	'2023-11-30 15:42:07',	'2024-01-08 13:16:43',	28),
(6,	'Biashara Loans 2',	45,	35,	1,	'2023-11-30 15:44:22',	'2024-01-08 13:17:11',	45);

DROP TABLE IF EXISTS `prospects`;
CREATE TABLE `prospects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `received` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `raw_payments`;
CREATE TABLE `raw_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amount` varchar(191) NOT NULL,
  `mpesaReceiptNumber` varchar(191) NOT NULL,
  `customer` varchar(191) NOT NULL,
  `phoneNumber` varchar(191) NOT NULL,
  `BusinessShortCode` varchar(191) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `raw_payments` (`id`, `amount`, `mpesaReceiptNumber`, `customer`, `phoneNumber`, `BusinessShortCode`, `account_number`, `created_at`, `updated_at`) VALUES
(1,	'200.00',	'SI44CQ6DXE',	'MARY',	'71c8f946c97383b7ead756941afb545be349077d82d131635f7376da9263df61',	'4123359',	'254723878532',	'2024-09-04 00:21:21',	'2024-09-04 00:21:21'),
(2,	'200.00',	'SI42CVR1BU',	'VERAH',	'e3979742f2182e7f58b4e3c2cb4b4f2f9bd625c15c3269c187ff2597cc25e89c',	'4123359',	'254707401179',	'2024-09-04 06:36:05',	'2024-09-04 06:36:05');

DROP TABLE IF EXISTS `reconsiliation_transactions`;
CREATE TABLE `reconsiliation_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `reconsiled_by` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `transaction_id` varchar(191) NOT NULL,
  `date_paid` datetime NOT NULL,
  `phone_number` varchar(191) NOT NULL,
  `channel` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reconsiliation_transactions_customer_id_foreign` (`customer_id`),
  KEY `reconsiliation_transactions_reconsiled_by_foreign` (`reconsiled_by`),
  CONSTRAINT `reconsiliation_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `reconsiliation_transactions_reconsiled_by_foreign` FOREIGN KEY (`reconsiled_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `referees`;
CREATE TABLE `referees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(191) NOT NULL,
  `id_number` varchar(191) NOT NULL,
  `phone_number` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `referees` (`id`, `full_name`, `id_number`, `phone_number`, `created_at`, `updated_at`) VALUES
(1,	'George Miko',	'645464546',	'254740782174',	'2024-08-23 11:14:33',	'2024-08-23 11:14:33'),
(2,	'Felix Kaunda',	'647363836',	'254740025323',	'2024-08-23 11:20:02',	'2024-08-23 11:20:02'),
(3,	'Mercy Okello',	'23456780',	'254796717015',	'2024-09-05 15:56:18',	'2024-09-05 15:56:18'),
(4,	'Doreen Wekesa',	'33765497',	'254710214576',	'2024-09-05 15:56:46',	'2024-09-05 15:56:46'),
(5,	'Mary Otieno',	'33456798',	'254743133454',	'2024-09-05 15:58:24',	'2024-09-05 15:58:24'),
(6,	'Mercy Atieno',	'23456780',	'254725678045',	'2024-09-05 16:03:29',	'2024-09-05 16:03:29');

DROP TABLE IF EXISTS `regpayments`;
CREATE TABLE `regpayments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `date_payed` varchar(191) NOT NULL,
  `transaction_id` varchar(191) NOT NULL,
  `amount` varchar(191) NOT NULL,
  `channel` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `regpayments_customer_id_foreign` (`customer_id`),
  CONSTRAINT `regpayments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `relationships`;
CREATE TABLE `relationships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rname` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `relationships` (`id`, `rname`, `created_at`, `updated_at`) VALUES
(1,	'Father',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(2,	'Mother',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(3,	'Son',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(4,	'Daughter',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(5,	'Brother',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(6,	'Sister',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(7,	'Spouse',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(8,	'Other',	'2019-04-15 13:02:22',	'2019-04-15 13:02:22');

DROP TABLE IF EXISTS `repayment_mpesa_transactions`;
CREATE TABLE `repayment_mpesa_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  `mpesaReceiptNumber` varchar(191) NOT NULL,
  `transactionDate` datetime NOT NULL,
  `phoneNumber` varchar(191) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `repayment_mpesa_transactions_customer_id_foreign` (`customer_id`),
  CONSTRAINT `repayment_mpesa_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rname` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `route` text NOT NULL,
  `for_group` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reports` (`id`, `rname`, `description`, `route`, `for_group`, `created_at`, `updated_at`) VALUES
(1,	'FIELD AGENT PERFORMANCE',	'Field Agent Performance',	'field_agent_performance',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(2,	'BRANCH MANAGER PERFORMANCE',	'Manager Performance Report',	'manager_officer_performance',	0,	NULL,	NULL),
(3,	'LOANS DUE TODAY',	'Loans Due Today',	'loan_due_today',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(4,	'LOAN ARREARS',	'Loan Arrears',	'loan_arrears',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(5,	'LOAN ARREARS WITH SKIPPED PAYMENTS',	'Loan Arrears with skipped payments ',	'loan_arreas_skipped_payments',	0,	NULL,	NULL),
(6,	'NON - PERFORMING LOANS',	'Non - Performing Loans',	'non_performing_loans',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(7,	'MPESA REPAYMENTS',	'MPesa Repayments',	'mpesa_repayments',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(8,	'ROLLED OVER LOANS',	'Rolled Over Loans',	'rolled_over_loans',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(9,	'DISBURSED LOANS',	'Disbursed Loans',	'disbursed_loans',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(10,	'INCOME STATEMENT',	'Income Statement',	'income_statement_v2',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(11,	'GROUP REPORTS',	'Group Reports',	'group_reports',	0,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(12,	'SMS SUMMARY REPORT',	'SMS SUMMARY REPORT	',	'sms_summary',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(13,	'CUSTOMER ACCOUNT STATEMENT',	'Customer Account Statement',	'customer_account_statement',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(14,	'LOAN COLLECTIONS PER MONTH',	'Loan Collections per month',	'loan_collections_per_month',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(15,	'LOANS PENDING DISBURSEMENT',	'Loans Pending Disbursement',	'loan_pending_disbursements',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(16,	'OUTSTANDING LOAN BALANCE',	'Outstanding Loan Balance\r\n',	'loans_balance',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(17,	'LOANS PENDING APPROVAL',	'Loans Pending Approval',	'loan_pending_approval',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(18,	'DISBURSED LOANS SUMMARY PER MONTH',	'disbursement loan summary per month',	'loan_disbursement_permonth',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(19,	'DISBURSED LOANS SUMMARY',	'Disbursed Loans Summary',	'disbursed_loans_summary',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(20,	'LOAN COLLECTION',	'Loan Collections',	'loan_collections',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(21,	'CUSTOMER LISTING',	'Customer Listing',	'customer_listing',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(22,	'CASHFLOW STATEMENT',	'Cashflow statement',	'cash_flow_statement',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(23,	'BRANCH EXPENSES',	'Expenses of a branch',	'branch_expenses',	0,	NULL,	NULL),
(24,	'INACTIVE CUSTOMERS',	'Inactive Customers',	'inactive_customers',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(25,	'BLOCKED CUSTOMERS',	'Blocked Customers',	'blocked_customers',	0,	NULL,	NULL),
(26,	'PAR ANALYSIS',	'PAR ANALYSIS',	'par_analysis',	0,	NULL,	NULL),
(27,	'COLLECTION RATE',	'COLLECTION RATE',	'collection_rate',	0,	NULL,	NULL),
(28,	'CUSTOMER SCORING REPORT',	'CUSTOMER SCORING REPORT',	'customer_scoring',	0,	NULL,	NULL),
(29,	'SYSTEM USERS',	'System Users',	'systems_users',	0,	'2019-04-15 13:02:22',	'2019-04-15 13:02:22'),
(30,	'GROUP SCORING REPORT',	'Group scoring overview',	'group_scoring',	1,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(31,	'DISBURSED GROUP LOANS',	'Disbursed Group Loans',	'group_disbursed_loans',	1,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(32,	'GROUP LOANS IN ARREARS',	'Group loans in arrears',	'group_loan_arrears',	1,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(33,	'GROUP LOANS WITH SKIPPED PAYMENTS',	'Group Loans with skipped payments',	'group_loan_skipped_payments',	1,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(34,	'GROUP LOAN BALANCES',	'List of group lending loans with balances',	'group_loans_balance',	1,	'2020-03-22 18:03:05',	'2020-03-22 18:03:05'),
(35,	'CUSTOMER INTERACTIONS',	'SIMPLE REPORT FOR VIEWING ALL CUSTOMER INTERACTIONS\r\n',	'customer-interactions-report',	0,	'2023-02-20 15:06:55',	'2023-02-20 15:06:55'),
(36,	'DEFAULT ANALYSIS REPORT',	'A report that shows the money that was not paid in a particular month',	'default_analysis_report',	0,	NULL,	NULL),
(37,	'LEADS REPORT',	'LEADS REPORT',	'leads',	0,	NULL,	NULL);

DROP TABLE IF EXISTS `restructured_installments`;
CREATE TABLE `restructured_installments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `principal_amount` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL,
  `for_rollover` tinyint(1) NOT NULL DEFAULT 0,
  `interest` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `amount_paid` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `last_payment_date` date DEFAULT NULL,
  `interest_payment_date` date DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `in_arrear` tinyint(1) NOT NULL DEFAULT 0,
  `being_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restructured_installments_loan_id_foreign` (`loan_id`),
  CONSTRAINT `restructured_installments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1,	'manager',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(2,	'customer_informant',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(3,	'accountant',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(4,	'field_agent',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(5,	'investor',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(6,	'admin',	'web',	'2019-04-15 13:02:18',	'2019-04-15 13:02:18'),
(7,	'Intern',	'web',	NULL,	NULL),
(8,	'collection_officer',	'web',	NULL,	NULL),
(9,	'agent_care',	'web',	NULL,	NULL),
(10,	'phone_handler',	'web',	NULL,	NULL),
(11,	'sector_manager',	'web',	'2024-01-13 13:06:30',	'2024-01-13 13:06:30');

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `rollovers`;
CREATE TABLE `rollovers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `rollover_interest` int(11) NOT NULL,
  `rollover_due` int(11) NOT NULL,
  `rollover_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rollovers_loan_id_foreign` (`loan_id`),
  CONSTRAINT `rollovers_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ro_targets`;
CREATE TABLE `ro_targets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `disbursement_target` int(11) DEFAULT NULL,
  `disbursement_target_amount` int(11) NOT NULL DEFAULT 1000000,
  `collection_target` int(11) DEFAULT NULL,
  `customer_target` int(11) NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `registration_fee` int(11) NOT NULL,
  `loan_processing_fee` int(11) NOT NULL,
  `rollover_interest` int(11) NOT NULL,
  `lp_fee` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `registration_fee`, `loan_processing_fee`, `rollover_interest`, `lp_fee`) VALUES
(1,	1,	1,	1,	0);

DROP TABLE IF EXISTS `settllement_requests`;
CREATE TABLE `settllement_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ConversationID` varchar(191) NOT NULL,
  `OriginatorConversationID` varchar(191) NOT NULL,
  `ResponseCode` varchar(191) NOT NULL,
  `ResponseDescription` varchar(191) NOT NULL,
  `settled` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `requested_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settllement_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `settllement_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `super_crunch_responses`;
CREATE TABLE `super_crunch_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `branch_id` bigint(20) unsigned NOT NULL,
  `salary` int(11) DEFAULT NULL,
  `field_agent_id` int(11) DEFAULT NULL,
  `is_recipient` tinyint(1) NOT NULL DEFAULT 0,
  `recipient_email` varchar(191) DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_recipient_email_unique` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `status`, `branch_id`, `salary`, `field_agent_id`, `is_recipient`, `recipient_email`, `last_seen`) VALUES
(2,	'Litsa Admin',	'litsa@admin.com',	'254740782174',	NULL,	'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',	NULL,	'2023-11-29 08:14:35',	'2024-09-11 09:47:20',	1,	8,	NULL,	NULL,	0,	NULL,	'2024-09-11 09:47:20'),
(3,	'Elizabeth',	'litsaelizabeth@gmail.com',	'254711591065',	NULL,	'$2y$10$iYRMFdm9murBB.iIL/XhXOmfEw30S3RW3U0qsyLUTlL3THsQr52Vm',	'epYc1SfkBSaMCgAbKiSeHYZ4JkYg9HQQf5AIj3efrBPA3cZa4yk54etBajVu',	'2023-11-30 14:14:50',	'2024-07-15 17:40:09',	1,	1,	20000,	NULL,	0,	NULL,	'2024-07-15 17:40:09'),
(10,	'Erick',	'omundierick3@gmail.com',	'254740782174',	NULL,	'$2y$10$J3AfTU3HS0PVOTUX.PE4D.t/AiE/Rm5fUtWSC7dE78/aRZ.iLRY0i',	NULL,	'2024-08-23 11:05:55',	'2024-08-23 11:56:47',	1,	1,	4500,	NULL,	0,	NULL,	'2024-08-23 11:56:47');

DROP TABLE IF EXISTS `user_payments`;
CREATE TABLE `user_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `expense_id` bigint(20) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `date_payed` datetime NOT NULL,
  `channel` varchar(191) NOT NULL,
  `transaction_id` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_payments_user_id_foreign` (`user_id`),
  KEY `user_payments_expense_id_foreign` (`expense_id`),
  CONSTRAINT `user_payments_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user_sms`;
CREATE TABLE `user_sms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `sms` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_sms_user_id_foreign` (`user_id`),
  KEY `user_sms_branch_id_foreign` (`branch_id`),
  CONSTRAINT `user_sms_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_sms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_sms` (`id`, `branch_id`, `user_id`, `sms`, `created_at`, `updated_at`, `phone`) VALUES
(10,	1,	10,	'Your LITSA CREDIT account has been created. Username: omundierick3@gmail.com Password: GktnFU0h',	'2024-08-23 11:05:57',	'2024-08-23 11:05:57',	'254740782174');

-- 2024-09-11 06:56:33
