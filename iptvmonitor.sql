-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 12, 2025 at 05:22 AM
-- Server version: 8.0.42-0ubuntu0.24.04.1
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iptvmonitor`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_address` text COLLATE utf8mb4_unicode_ci,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `connection_type` enum('PPPoE','Static IP','DHCP') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mac_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dealer_id` bigint UNSIGNED DEFAULT NULL,
  `sub_dealer_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `customer_limit` int DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dealer_package`
--

CREATE TABLE `dealer_package` (
  `dealer_id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','sent','paid','partial','overdue','cancelled','advance','unpaid','due','balance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2022_12_14_083707_create_settings_table', 1),
(5, '2023_01_06_000010_create_templates_table', 1),
(6, '2023_01_06_000011_create_settings', 1),
(7, '2023_06_01_000000_create_companies_table', 1),
(8, '2023_06_01_000002_create_dealers_table', 1),
(9, '2023_06_01_000003_create_sub_dealers_table', 1),
(10, '2023_06_01_000004_create_packages_table', 1),
(11, '2023_06_01_000005_create_customers_table', 1),
(12, '2023_06_01_000006_create_subscriptions_table', 1),
(13, '2023_06_01_000007_create_invoices_table', 1),
(14, '2023_06_01_000008_create_payments_table', 1),
(15, '2024_04_07_add_company_id_to_users_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `speed_down` int NOT NULL,
  `speed_up` int NOT NULL,
  `data_limit` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `billing_cycle` enum('monthly','quarterly','biannually','annually') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','online_gateway','cheque') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('aou2rHYjrgnoDJuOWn0mYUcuYgrjIvaQwjT0Z1p8', NULL, '175.107.244.49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ1pmMmRtVGpKckVyWnRuZnNza1JnSzZFdVhVYUs4Tk1XNWJ4Y2dvdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1744135039),
('my2UqyxUAa6O5vak0FrhK1txDQR7PgaQALSasV0O', NULL, '154.81.156.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36 Edg/90.0.818.46', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoicEt6UHFkU3g4UmQ2Nk8zaUJ6aGlSaXJzb0FLMjFkMHpqNjRtQUZOUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1744134992);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `group`, `name`, `locked`, `payload`, `created_at`, `updated_at`) VALUES
(1, 'general', 'company_name', 0, '\"ISP Billing System\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(2, 'general', 'company_address', 0, '\"123 Main Street, City, Country\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(3, 'general', 'company_email', 0, '\"contact@example.com\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(4, 'general', 'company_phone', 0, '\"+1234567890\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(5, 'general', 'company_website', 0, '\"https://example.com\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(6, 'general', 'company_logo', 0, '\"/images/logo.png\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(7, 'general', 'currency', 0, '\"USD\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(8, 'general', 'timezone', 0, '\"UTC\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(9, 'general', 'enable_dealer_module', 0, 'true', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(10, 'general', 'enable_payment_module', 0, 'true', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(11, 'general', 'enable_customer_portal', 0, 'true', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(12, 'invoice', 'default_due_days', 0, '7', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(13, 'invoice', 'send_invoice_notification', 0, 'true', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(14, 'invoice', 'default_invoice_pdf_template_id', 0, '1', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(15, 'invoice', 'default_print_80mm_template_id', 0, '2', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(16, 'invoice', 'footer_notes', 0, '\"Thank you for your business. If you have any questions, please contact our support team.\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(17, 'invoice', 'payment_instructions', 0, '\"Please make payment via bank transfer to Account Number: 1234567890, Bank Name: Example Bank.\"', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(18, 'invoice', 'default_tax_rate', 0, '0', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(19, 'invoice', 'auto_send_overdue_reminders', 0, 'false', '2025-04-08 12:48:16', '2025-04-08 12:48:16'),
(20, 'invoice', 'overdue_reminder_days', 0, '3', '2025-04-08 12:48:16', '2025-04-08 12:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `activation_date` date NOT NULL,
  `next_invoice_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `price_override` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_dealers`
--

CREATE TABLE `sub_dealers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `dealer_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'invoice_pdf, print_80mm, email, sms, etc.',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `css` longtext COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `name`, `type`, `content`, `css`, `is_default`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Default Invoice PDF', 'invoice_pdf', '<div class=\"invoice-container\">\n    <div class=\"header\">\n        <div class=\"logo\">\n            @if(isset($data[\'company\'][\'logo\']) && $data[\'company\'][\'logo\'])\n                <img src=\"{{ $data[\'company\'][\'logo\'] }}\" alt=\"Company Logo\">\n            @else\n                <h2>{{ $data[\'company\'][\'name\'] }}</h2>\n            @endif\n        </div>\n        <div class=\"company-info\">\n            <h3>{{ $data[\'company\'][\'name\'] }}</h3>\n            <p>{{ $data[\'company\'][\'address\'] }}</p>\n            <p>Phone: {{ $data[\'company\'][\'phone\'] }}</p>\n            <p>Email: {{ $data[\'company\'][\'email\'] }}</p>\n            <p>Website: {{ $data[\'company\'][\'website\'] }}</p>\n        </div>\n    </div>\n\n    <div class=\"invoice-header\">\n        <h1>INVOICE</h1>\n        <div class=\"invoice-details\">\n            <div class=\"row\">\n                <div class=\"col\">\n                    <strong>Invoice Number:</strong> {{ $data[\'invoice\']->invoice_number }}\n                </div>\n                <div class=\"col\">\n                    <strong>Invoice Date:</strong> {{ $data[\'invoice\']->invoice_date->format(\'d M, Y\') }}\n                </div>\n            </div>\n            <div class=\"row\">\n                <div class=\"col\">\n                    <strong>Due Date:</strong> {{ $data[\'invoice\']->due_date->format(\'d M, Y\') }}\n                </div>\n                <div class=\"col\">\n                    <strong>Status:</strong> <span class=\"status-{{ strtolower($data[\'invoice\']->status) }}\">{{ strtoupper($data[\'invoice\']->status) }}</span>\n                </div>\n            </div>\n        </div>\n    </div>\n\n    <div class=\"bill-to\">\n        <h3>Bill To:</h3>\n        <p><strong>{{ $data[\'invoice\']->customer->name }}</strong></p>\n        <p>{{ $data[\'invoice\']->customer->address }}</p>\n        <p>Phone: {{ $data[\'invoice\']->customer->phone }}</p>\n        <p>Email: {{ $data[\'invoice\']->customer->email }}</p>\n    </div>\n\n    <div class=\"invoice-items\">\n        <table>\n            <thead>\n                <tr>\n                    <th>#</th>\n                    <th>Description</th>\n                    <th>Package</th>\n                    <th>Period</th>\n                    <th>Price</th>\n                </tr>\n            </thead>\n            <tbody>\n                @foreach($data[\'invoice\']->items as $index => $item)\n                <tr>\n                    <td>{{ $index + 1 }}</td>\n                    <td>{{ $item->description }}</td>\n                    <td>{{ $item->package->name ?? \'N/A\' }}</td>\n                    <td>\n                        @if($item->period_start && $item->period_end)\n                            {{ $item->period_start->format(\'d M, Y\') }} - {{ $item->period_end->format(\'d M, Y\') }}\n                        @else\n                            N/A\n                        @endif\n                    </td>\n                    <td>{{ $data[\'currency\'] }} {{ number_format($item->amount, 2) }}</td>\n                </tr>\n                @endforeach\n            </tbody>\n        </table>\n    </div>\n\n    <div class=\"invoice-summary\">\n        <div class=\"summary-item\">\n            <span>Subtotal:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount - $data[\'invoice\']->tax_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-item\">\n            <span>Tax ({{ $data[\'invoice\']->tax_rate }}%):</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->tax_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-item total\">\n            <span>Total:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount, 2) }}</span>\n        </div>\n        @if($data[\'invoice\']->status === \'paid\')\n        <div class=\"summary-item paid\">\n            <span>Paid:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->paid_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-item balance\">\n            <span>Balance:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount - $data[\'invoice\']->paid_amount, 2) }}</span>\n        </div>\n        @endif\n    </div>\n\n    <div class=\"payment-methods\">\n        <h3>Payment Methods</h3>\n        <p>Please make payment via bank transfer to:</p>\n        <p>Bank Name: [BANK NAME]</p>\n        <p>Account Name: [ACCOUNT NAME]</p>\n        <p>Account Number: [ACCOUNT NUMBER]</p>\n    </div>\n\n    <div class=\"footer\">\n        <p>{{ $data[\'footer_notes\'] }}</p>\n        <p>Thank you for your business!</p>\n    </div>\n</div>', 'body {\n    font-family: Arial, sans-serif;\n    margin: 0;\n    padding: 0;\n    color: #333;\n}\n\n.invoice-container {\n    max-width: 800px;\n    margin: 0 auto;\n    padding: 20px;\n}\n\n.header {\n    display: flex;\n    justify-content: space-between;\n    margin-bottom: 30px;\n}\n\n.logo img {\n    max-height: 80px;\n}\n\n.company-info h3 {\n    margin: 0 0 10px 0;\n    color: #2c3e50;\n}\n\n.company-info p {\n    margin: 2px 0;\n    font-size: 14px;\n}\n\n.invoice-header {\n    text-align: center;\n    margin-bottom: 30px;\n}\n\n.invoice-header h1 {\n    color: #2c3e50;\n    margin: 0 0 15px 0;\n}\n\n.invoice-details {\n    background: #f8f9fa;\n    padding: 15px;\n    border-radius: 5px;\n}\n\n.row {\n    display: flex;\n    margin-bottom: 8px;\n}\n\n.col {\n    flex: 1;\n}\n\n.bill-to {\n    margin-bottom: 30px;\n}\n\n.bill-to h3 {\n    color: #2c3e50;\n    margin-bottom: 10px;\n}\n\n.bill-to p {\n    margin: 2px 0;\n    font-size: 14px;\n}\n\n.invoice-items {\n    margin-bottom: 30px;\n}\n\ntable {\n    width: 100%;\n    border-collapse: collapse;\n}\n\nth, td {\n    text-align: left;\n    padding: 12px;\n    border-bottom: 1px solid #ddd;\n}\n\nth {\n    background-color: #f8f9fa;\n    font-weight: bold;\n}\n\n.invoice-summary {\n    margin-bottom: 30px;\n}\n\n.summary-item {\n    display: flex;\n    justify-content: space-between;\n    padding: 8px 0;\n}\n\n.total {\n    font-weight: bold;\n    font-size: 18px;\n    border-top: 2px solid #ddd;\n    padding-top: 12px;\n}\n\n.paid, .balance {\n    font-weight: bold;\n}\n\n.payment-methods {\n    margin-bottom: 30px;\n    padding: 15px;\n    background: #f8f9fa;\n    border-radius: 5px;\n}\n\n.payment-methods h3 {\n    margin-top: 0;\n    color: #2c3e50;\n}\n\n.footer {\n    margin-top: 30px;\n    text-align: center;\n    font-size: 14px;\n    color: #6c757d;\n}\n\n.status-paid {\n    color: #28a745;\n}\n\n.status-pending {\n    color: #ffc107;\n}\n\n.status-overdue {\n    color: #dc3545;\n}\n\n.status-cancelled {\n    color: #6c757d;\n}', 1, NULL, NULL, '2025-04-08 12:48:16', '2025-04-08 12:48:16', NULL),
(2, 'Default 80mm Print Receipt', 'print_80mm', '<div class=\"receipt\">\n    <div class=\"header\">\n        <h1>{{ $data[\'company\'][\'name\'] }}</h1>\n        <p>{{ $data[\'company\'][\'address\'] }}</p>\n        <p>Tel: {{ $data[\'company\'][\'phone\'] }}</p>\n        @if(isset($data[\'company\'][\'website\']))\n        <p>{{ $data[\'company\'][\'website\'] }}</p>\n        @endif\n    </div>\n\n    <div class=\"invoice-info\">\n        <p>Receipt #: {{ $data[\'invoice\']->invoice_number }}</p>\n        <p>Date: {{ $data[\'invoice\']->invoice_date->format(\'d/m/Y H:i\') }}</p>\n        <p>Customer: {{ $data[\'invoice\']->customer->name }}</p>\n    </div>\n\n    <div class=\"separator\">--------------------------------</div>\n\n    <div class=\"items\">\n        @foreach($data[\'invoice\']->items as $item)\n        <div class=\"item\">\n            <p>{{ $item->description }}</p>\n            <p>{{ $item->package->name ?? \'N/A\' }}</p>\n            <p class=\"amount\">{{ $data[\'currency\'] }} {{ number_format($item->amount, 2) }}</p>\n        </div>\n        @endforeach\n    </div>\n\n    <div class=\"separator\">--------------------------------</div>\n\n    <div class=\"summary\">\n        <div class=\"summary-line\">\n            <span>Subtotal:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount - $data[\'invoice\']->tax_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-line\">\n            <span>Tax ({{ $data[\'invoice\']->tax_rate }}%):</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->tax_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-line total\">\n            <span>Total:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount, 2) }}</span>\n        </div>\n        @if($data[\'invoice\']->status === \'paid\')\n        <div class=\"summary-line\">\n            <span>Paid:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->paid_amount, 2) }}</span>\n        </div>\n        <div class=\"summary-line\">\n            <span>Balance:</span>\n            <span>{{ $data[\'currency\'] }} {{ number_format($data[\'invoice\']->total_amount - $data[\'invoice\']->paid_amount, 2) }}</span>\n        </div>\n        @endif\n    </div>\n\n    <div class=\"footer\">\n        <p>Thank you for your business!</p>\n        <p>Status: {{ strtoupper($data[\'invoice\']->status) }}</p>\n    </div>\n</div>', 'body {\n    font-family: \'Courier New\', monospace;\n    font-size: 12px;\n    margin: 0;\n    padding: 0;\n    width: 80mm;\n}\n\n.receipt {\n    width: 100%;\n    margin: 0 auto;\n    padding: 5px;\n}\n\n.header {\n    text-align: center;\n    margin-bottom: 10px;\n}\n\n.header h1 {\n    font-size: 16px;\n    margin: 0 0 5px 0;\n}\n\n.header p {\n    margin: 2px 0;\n    font-size: 12px;\n}\n\n.invoice-info {\n    margin-bottom: 10px;\n}\n\n.invoice-info p {\n    margin: 2px 0;\n}\n\n.separator {\n    text-align: center;\n    margin: 10px 0;\n}\n\n.items {\n    margin-bottom: 10px;\n}\n\n.item {\n    margin-bottom: 8px;\n}\n\n.item p {\n    margin: 1px 0;\n}\n\n.amount {\n    text-align: right;\n}\n\n.summary {\n    margin-bottom: 10px;\n}\n\n.summary-line {\n    display: flex;\n    justify-content: space-between;\n    margin: 2px 0;\n}\n\n.total {\n    font-weight: bold;\n    margin-top: 5px;\n}\n\n.footer {\n    text-align: center;\n    margin-top: 10px;\n    font-size: 12px;\n}\n\n.footer p {\n    margin: 2px 0;\n}\n\n@media print {\n    body {\n        width: 80mm;\n    }\n    \n    @page {\n        margin: 0;\n        size: 80mm auto;\n    }\n}', 1, NULL, NULL, '2025-04-08 12:48:16', '2025-04-08 12:48:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin', 'admin@lyarinet.com', NULL, '$2y$10$3pfJzTaUCQTKw7QU1amg..D6NVdcXNQg1jqXIVArbvflyvP4.0f6u', NULL, '2025-04-08 12:51:29', '2025-04-08 12:51:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_dealer_id_foreign` (`dealer_id`),
  ADD KEY `customers_sub_dealer_id_foreign` (`sub_dealer_id`),
  ADD KEY `customers_user_id_foreign` (`user_id`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dealers_company_id_foreign` (`company_id`),
  ADD KEY `dealers_user_id_foreign` (`user_id`);

--
-- Indexes for table `dealer_package`
--
ALTER TABLE `dealer_package`
  ADD PRIMARY KEY (`dealer_id`,`package_id`),
  ADD KEY `dealer_package_package_id_foreign` (`package_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_items_package_id_foreign` (`package_id`),
  ADD KEY `invoice_items_subscription_id_foreign` (`subscription_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_customer_id_foreign` (`customer_id`),
  ADD KEY `payments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `payments_received_by_user_id_foreign` (`received_by_user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_group_name_unique` (`group`,`name`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_customer_id_foreign` (`customer_id`),
  ADD KEY `subscriptions_package_id_foreign` (`package_id`);

--
-- Indexes for table `sub_dealers`
--
ALTER TABLE `sub_dealers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_dealers_dealer_id_foreign` (`dealer_id`),
  ADD KEY `sub_dealers_user_id_foreign` (`user_id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templates_created_by_foreign` (`created_by`),
  ADD KEY `templates_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_company_id_foreign` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dealers`
--
ALTER TABLE `dealers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_dealers`
--
ALTER TABLE `sub_dealers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_sub_dealer_id_foreign` FOREIGN KEY (`sub_dealer_id`) REFERENCES `sub_dealers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dealers`
--
ALTER TABLE `dealers`
  ADD CONSTRAINT `dealers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dealers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dealer_package`
--
ALTER TABLE `dealer_package`
  ADD CONSTRAINT `dealer_package_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dealer_package_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_items_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_received_by_user_id_foreign` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_dealers`
--
ALTER TABLE `sub_dealers`
  ADD CONSTRAINT `sub_dealers_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_dealers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
