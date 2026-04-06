-- ParentConnecta full setup export
-- Generated on 2026-04-03
-- Import this single file into the already-selected phpMyAdmin database.
-- Default password for all sample users: Password123!

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS=0;

-- Core utility tables
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Base app tables without outward foreign keys
DROP TABLE IF EXISTS `schools`;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `timezone` varchar(100) NOT NULL DEFAULT 'Africa/Lagos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_slug_unique` (`slug`),
  KEY `schools_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `guard_name` varchar(125) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_school_id_status_index` (`school_id`, `status`),
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `school_classes`;
CREATE TABLE `school_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_classes_school_id_name_section_unique` (`school_id`, `name`, `section`),
  UNIQUE KEY `school_classes_school_id_code_unique` (`school_id`, `code`),
  KEY `school_classes_school_id_status_index` (`school_id`, `status`),
  CONSTRAINT `school_classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `school_class_id` bigint(20) unsigned DEFAULT NULL,
  `admission_number` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male', 'female', 'other') DEFAULT NULL,
  `status` enum('active', 'graduated', 'withdrawn', 'inactive') NOT NULL DEFAULT 'active',
  `admitted_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_school_id_admission_number_unique` (`school_id`, `admission_number`),
  KEY `students_school_class_id_foreign` (`school_class_id`),
  KEY `students_school_id_school_class_id_status_index` (`school_id`, `school_class_id`, `status`),
  KEY `students_school_id_last_name_first_name_index` (`school_id`, `last_name`, `first_name`),
  CONSTRAINT `students_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Permission pivot tables
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(125) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Domain tables with deeper dependencies
DROP TABLE IF EXISTS `parent_student`;
CREATE TABLE `parent_student` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `parent_user_id` bigint(20) unsigned NOT NULL,
  `relationship_type` enum('father', 'mother', 'guardian', 'other') NOT NULL DEFAULT 'other',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_student_school_id_student_id_parent_user_id_unique` (`school_id`, `student_id`, `parent_user_id`),
  KEY `parent_student_student_id_foreign` (`student_id`),
  KEY `parent_student_parent_user_id_foreign` (`parent_user_id`),
  KEY `parent_student_school_id_parent_user_id_index` (`school_id`, `parent_user_id`),
  CONSTRAINT `parent_student_parent_user_id_foreign` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_student_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `teacher_class`;
CREATE TABLE `teacher_class` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `school_class_id` bigint(20) unsigned NOT NULL,
  `teacher_user_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_class_school_id_school_class_id_teacher_user_id_unique` (`school_id`, `school_class_id`, `teacher_user_id`),
  KEY `teacher_class_school_class_id_foreign` (`school_class_id`),
  KEY `teacher_class_teacher_user_id_foreign` (`teacher_user_id`),
  KEY `teacher_class_school_id_teacher_user_id_index` (`school_id`, `teacher_user_id`),
  CONSTRAINT `teacher_class_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_class_teacher_user_id_foreign` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `results`;
CREATE TABLE `results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `school_class_id` bigint(20) unsigned NOT NULL,
  `teacher_user_id` bigint(20) unsigned DEFAULT NULL,
  `subject_name` varchar(100) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `term` enum('first', 'second', 'third') NOT NULL,
  `ca_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `exam_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grade` varchar(5) DEFAULT NULL,
  `remark` varchar(191) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `results_student_subject_term_unique` (`school_id`, `student_id`, `subject_name`, `academic_year`, `term`),
  KEY `results_student_id_foreign` (`student_id`),
  KEY `results_school_class_id_foreign` (`school_class_id`),
  KEY `results_teacher_user_id_foreign` (`teacher_user_id`),
  KEY `results_class_year_term_index` (`school_id`, `school_class_id`, `academic_year`, `term`),
  KEY `results_school_teacher_index` (`school_id`, `teacher_user_id`),
  CONSTRAINT `results_school_class_id_foreign` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`),
  CONSTRAINT `results_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `results_teacher_user_id_foreign` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `parent_user_id` bigint(20) unsigned DEFAULT NULL,
  `reference` varchar(191) NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `academic_year` varchar(9) DEFAULT NULL,
  `term` enum('first', 'second', 'third') DEFAULT NULL,
  `amount_due` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'NGN',
  `status` enum('paid', 'partial', 'unpaid') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(100) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_unique` (`reference`),
  KEY `payments_student_id_foreign` (`student_id`),
  KEY `payments_parent_user_id_foreign` (`parent_user_id`),
  KEY `payments_school_id_student_id_index` (`school_id`, `student_id`),
  KEY `payments_school_id_status_index` (`school_id`, `status`),
  KEY `payments_school_id_paid_at_index` (`school_id`, `paid_at`),
  CONSTRAINT `payments_parent_user_id_foreign` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Migration metadata matching this repository
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_31_114256_create_permission_tables', 1),
(5, '2026_03_31_115620_create_schools_table', 1),
(6, '2026_03_31_115621_add_school_id_and_status_to_users_table', 1),
(7, '2026_03_31_115622_create_school_classes_table', 1),
(8, '2026_03_31_115623_create_students_table', 1),
(9, '2026_03_31_115624_create_parent_student_table', 1),
(10, '2026_03_31_115625_create_teacher_class_table', 1),
(11, '2026_03_31_115626_create_results_table', 1),
(12, '2026_03_31_115627_create_payments_table', 1),
(13, '2026_03_31_130500_upgrade_payments_for_tracking_module', 1),
(14, '2026_04_03_000000_create_sessions_table', 1);

-- Starter seed data
INSERT IGNORE INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('super_admin', 'web', '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
('school_admin', 'web', '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
('teacher', 'web', '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
('parent', 'web', '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `schools` (`name`, `slug`, `email`, `phone`, `address`, `status`, `timezone`, `created_at`, `updated_at`) VALUES
('Demo School', 'demo-school', 'info@demo-school.test', '+2348000000000', '12 Demo Avenue, Lagos', 'active', 'Africa/Lagos', '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `users` (`school_id`, `name`, `email`, `email_verified_at`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(NULL, 'Super Admin', 'superadmin@parentconnecta.test', '2026-03-31 20:42:35', '$2y$10$qNPnAuuDP3.RWG/NyrzF1eQobOX2pY0BfyaJX0Ue74ok6Y5OkqgMq', 'active', NULL, '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), 'Demo School Admin', 'admin@demo-school.test', '2026-03-31 20:42:35', '$2y$10$qNPnAuuDP3.RWG/NyrzF1eQobOX2pY0BfyaJX0Ue74ok6Y5OkqgMq', 'active', NULL, '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), 'Demo Teacher', 'teacher@demo-school.test', '2026-03-31 20:42:35', '$2y$10$qNPnAuuDP3.RWG/NyrzF1eQobOX2pY0BfyaJX0Ue74ok6Y5OkqgMq', 'active', NULL, '2026-03-31 20:42:35', '2026-03-31 20:42:35'),
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), 'Demo Parent', 'parent@demo-school.test', '2026-03-31 20:42:35', '$2y$10$qNPnAuuDP3.RWG/NyrzF1eQobOX2pY0BfyaJX0Ue74ok6Y5OkqgMq', 'active', NULL, '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
((SELECT `id` FROM `roles` WHERE `name` = 'super_admin' AND `guard_name` = 'web' LIMIT 1), 'App\\Models\\User', (SELECT `id` FROM `users` WHERE `email` = 'superadmin@parentconnecta.test' LIMIT 1)),
((SELECT `id` FROM `roles` WHERE `name` = 'school_admin' AND `guard_name` = 'web' LIMIT 1), 'App\\Models\\User', (SELECT `id` FROM `users` WHERE `email` = 'admin@demo-school.test' LIMIT 1)),
((SELECT `id` FROM `roles` WHERE `name` = 'teacher' AND `guard_name` = 'web' LIMIT 1), 'App\\Models\\User', (SELECT `id` FROM `users` WHERE `email` = 'teacher@demo-school.test' LIMIT 1)),
((SELECT `id` FROM `roles` WHERE `name` = 'parent' AND `guard_name` = 'web' LIMIT 1), 'App\\Models\\User', (SELECT `id` FROM `users` WHERE `email` = 'parent@demo-school.test' LIMIT 1));

INSERT INTO `school_classes` (`school_id`, `name`, `section`, `code`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), 'Primary 1', 'A', 'P1A', 30, 'active', '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `students` (`school_id`, `school_class_id`, `admission_number`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `gender`, `status`, `admitted_at`, `created_at`, `updated_at`) VALUES
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), (SELECT `id` FROM `school_classes` WHERE `code` = 'P1A' LIMIT 1), 'DEMO-STU-001', 'Amina', 'Bello', NULL, '2018-05-14', 'female', 'active', '2026-01-15', '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `teacher_class` (`school_id`, `school_class_id`, `teacher_user_id`, `is_primary`, `assigned_at`, `created_at`, `updated_at`) VALUES
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), (SELECT `id` FROM `school_classes` WHERE `code` = 'P1A' LIMIT 1), (SELECT `id` FROM `users` WHERE `email` = 'teacher@demo-school.test' LIMIT 1), 1, '2026-03-31 20:42:35', '2026-03-31 20:42:35', '2026-03-31 20:42:35');

INSERT INTO `parent_student` (`school_id`, `student_id`, `parent_user_id`, `relationship_type`, `is_primary`, `created_at`, `updated_at`) VALUES
((SELECT `id` FROM `schools` WHERE `slug` = 'demo-school' LIMIT 1), (SELECT `id` FROM `students` WHERE `admission_number` = 'DEMO-STU-001' LIMIT 1), (SELECT `id` FROM `users` WHERE `email` = 'parent@demo-school.test' LIMIT 1), 'mother', 1, '2026-03-31 20:42:35', '2026-03-31 20:42:35');

SET FOREIGN_KEY_CHECKS=1;

-- Sample login accounts
-- Super Admin: superadmin@parentconnecta.test / Password123!
-- School Admin: admin@demo-school.test / Password123!
-- Teacher: teacher@demo-school.test / Password123!
-- Parent: parent@demo-school.test / Password123!
-- School portal: /school/demo-school
