-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ascom_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `target_entity` varchar(100) DEFAULT NULL,
  `target_entity_id` int(11) DEFAULT NULL,
  `activity_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (96,NULL,'super_admin','','Assigned librarian role to user: Liza Nillama',NULL,NULL,'2025-11-05 07:14:50'),(97,NULL,'Super Admin MIS','User Creation','Created new user:  Liza Nillama (EMP: 768974) with role: Teacher for Department: College of Teacher Education, Arts and Sciences','User',58,'2025-11-05 07:20:18'),(98,NULL,'Super Admin MIS','User Creation','Created new user:  Janus Agustero-Naparan (EMP: 246564) with role: Teacher for Department: College of Teacher Education, Arts and Sciences','User',59,'2025-11-05 07:21:07'),(100,NULL,'Super Admin MIS','Department Creation','Created new department: College of Computing Studies (CCS)','Department',16,'2025-11-05 08:41:04'),(101,NULL,'Super Admin MIS','User Creation','Created new user:  Philipchris Encarnacion (EMP: 128744) with role: Teacher for Department: College of Computing Studies','User',60,'2025-11-05 08:41:53'),(102,NULL,'super_admin','','Assigned Philipchris Encarnacion as dean of department CCS',NULL,NULL,'2025-11-05 08:42:25'),(103,NULL,'super_admin','','Assigned librarian role to user: Liza Nillama',NULL,NULL,'2025-11-05 08:50:12'),(104,NULL,'super_admin','','Assigned Quality Assurance role to user: Janus Agustero-Naparan',NULL,NULL,'2025-11-05 08:50:16'),(105,NULL,'Super Admin MIS','User Creation','Created new user:  Leomar Nuevo (EMP: 489530) with role: Teacher for Department: College of Computing Studies','User',61,'2025-11-07 00:52:41'),(106,NULL,'Super Admin MIS','User Creation','Created new user:  Rowelyn Lagnason (EMP: 664489) with role: Teacher for Department: College of Computing Studies','User',62,'2025-11-07 02:57:00'),(107,NULL,'super_admin','','Assigned Leomar Nuevo as dean of department CCS',NULL,NULL,'2025-11-07 03:32:20'),(108,NULL,'super_admin','','Assigned Philipchris Encarnacion as dean of department CCS',NULL,NULL,'2025-11-07 03:32:29'),(109,NULL,'super_admin','','Assigned librarian role to user: Leomar Nuevo (replaced Liza Nillama)',NULL,NULL,'2025-12-02 12:25:35'),(110,NULL,'super_admin','','Assigned librarian role to user: Liza Nillama (replaced Leomar Nuevo)',NULL,NULL,'2025-12-02 12:25:39');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book_references`
--

DROP TABLE IF EXISTS `book_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `no_of_copies` int(11) DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `processing_status` enum('processing','completed','drafted') DEFAULT 'processing',
  `status_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_availability` (`no_of_copies`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_processing_status` (`processing_status`),
  CONSTRAINT `book_references_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_ref_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_ref_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2386 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_references`
--

LOCK TABLES `book_references` WRITE;
/*!40000 ALTER TABLE `book_references` DISABLE KEYS */;
/*!40000 ALTER TABLE `book_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classifications`
--

DROP TABLE IF EXISTS `classifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'DDC' COMMENT 'Classification type (DDC, LCC, etc.)',
  `call_number_range` varchar(20) NOT NULL COMMENT 'Range like 000-099',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `location` varchar(100) DEFAULT NULL,
  `library_location_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL COMMENT 'User ID who created this classification',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_call_number_range` (`call_number_range`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_library_location_id` (`library_location_id`),
  CONSTRAINT `fk_classifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library classification systems (Dewey Decimal, etc.)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classifications`
--

LOCK TABLES `classifications` WRITE;
/*!40000 ALTER TABLE `classifications` DISABLE KEYS */;
INSERT INTO `classifications` VALUES (11,'GENERALITIES','DDC','000-099','General knowledge, information, and computer science.','active','Main Library',1,58,'2025-11-16 08:20:09','2025-11-16 08:54:47'),(12,'Philosophy & Psychology','DDC','100-199','Ideas, human behavior, and ways of thinking.','active','Main Library',1,58,'2025-11-16 08:28:38','2025-11-16 08:30:11'),(13,'RELIGION','DDC','200-299','Beliefs, faiths, and religious practices.','active','Main Library',1,58,'2025-11-16 08:47:20','2025-11-16 08:54:22'),(14,'Social Sciences','DDC','300-399','People, society, law, education, and economics.','active','Main Library',1,58,'2025-11-16 08:48:47','2025-11-16 08:48:47'),(15,'LANGUAGE','DDC','400-499','Grammar, linguistics, and world languages.','active','Main Library',1,58,'2025-11-16 08:53:56','2025-11-16 08:53:56'),(16,'Natural Sciences & Mathematics','DDC','500-599','Nature, science, and mathematical studies.','active','Main Library',1,58,'2025-11-16 08:56:07','2025-11-16 08:56:07'),(17,'Technology (Applied Sciences)','DDC','600-699','Practical use of science-medicine, engineering, agriculture.','active','Main Library',1,58,'2025-11-16 08:57:59','2025-11-16 08:57:59'),(18,'The Arts','DDC','700-799','Visual arts, music, design, and recreation.','active','Main Library',1,58,'2025-11-16 08:59:05','2025-11-16 08:59:05'),(19,'Literature & Rhetoric','DDC','800-899','Poetry, fiction, drama, and literary works.','active','Main Library',1,58,'2025-11-16 09:00:24','2025-11-16 09:00:24'),(20,'Geography & History Biography Filipiniana','DDC','900-999','Places, past events, and life stories (includes Filipiniana).','active','Main Library',1,58,'2025-11-16 09:08:53','2025-11-16 09:08:53');
/*!40000 ALTER TABLE `classifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_drafts`
--

DROP TABLE IF EXISTS `course_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_drafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `courses_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of course draft data' CHECK (json_valid(`courses_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `course_drafts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_drafts_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_drafts`
--

LOCK TABLES `course_drafts` WRITE;
/*!40000 ALTER TABLE `course_drafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_drafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_proposals`
--

DROP TABLE IF EXISTS `course_proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID of the user who submitted the proposal',
  `program_id` int(11) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `course_type` varchar(50) DEFAULT NULL COMMENT 'New Course Proposal, Course Revision, Cross-Department',
  `status` enum('Draft','Pending QA Review','Under Review','Approved','Rejected','Added to Program') DEFAULT 'Pending QA Review' COMMENT 'Proposal status',
  `courses_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of course proposal data' CHECK (json_valid(`courses_data`)),
  `submitted_at` timestamp NULL DEFAULT NULL COMMENT 'When the proposal was submitted to QA',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'When the proposal was reviewed',
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'ID of the user who reviewed the proposal',
  `review_notes` text DEFAULT NULL COMMENT 'Review comments or notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_status` (`status`),
  KEY `idx_submitted_at` (`submitted_at`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `course_proposals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_proposals_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `course_proposals_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_proposals`
--

LOCK TABLES `course_proposals` WRITE;
/*!40000 ALTER TABLE `course_proposals` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_proposals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL,
  `course_title` varchar(200) NOT NULL,
  `units` int(11) DEFAULT 3,
  `program_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `term` varchar(50) DEFAULT '1st Semester',
  `academic_year` varchar(20) DEFAULT 'A.Y. 2025-2026',
  `year_level` varchar(20) DEFAULT '1st Year',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `faculty_id` (`faculty_id`),
  KEY `idx_courses_created_by` (`created_by`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`),
  CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_courses_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=480 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `dean_user_id` int(11) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_code` (`department_code`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'CBE','College of Business Education','#FFC000',46,'Super Admin MIS','2025-07-15 08:27:26','2025-08-08 04:10:40'),(3,'COC','College of Criminology','#228B22',45,'Super Admin MIS','2025-07-15 08:32:36','2025-08-08 04:11:17'),(4,'CTEAS','College of Teacher Education, Arts and Sciences','#0047AB',47,'Super Admin MIS','2025-07-15 08:34:09','2025-08-08 04:11:32'),(16,'CCS','College of Computing Studies','#C11F1F',60,'Super Admin MIS','2025-11-05 08:41:04','2025-11-07 03:32:29');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_locations`
--

DROP TABLE IF EXISTS `library_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_library_location_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Physical library locations (Main, Buenavista, etc.)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_locations`
--

LOCK TABLES `library_locations` WRITE;
/*!40000 ALTER TABLE `library_locations` DISABLE KEYS */;
INSERT INTO `library_locations` VALUES (1,'Main Library',NULL,1,'2025-11-16 08:20:07','2025-11-16 08:20:07');
/*!40000 ALTER TABLE `library_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_role` varchar(100) DEFAULT NULL,
  `recipient_type` enum('all','super_admin','librarian','quality_assurance','dean','teacher') DEFAULT 'all',
  `recipient_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `idx_recipient_type` (`recipient_type`),
  KEY `idx_recipient_id` (`recipient_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (34,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-10-25 04:37:02',NULL,NULL),(35,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-10-25 04:37:02',NULL,NULL),(36,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-10-25 04:37:02',NULL,NULL),(38,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-10-25 04:37:02',NULL,NULL),(39,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Real Estate Management (BSREM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-10-25 04:37:02',NULL,NULL),(40,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts in Psychology (BAP)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-10-28 10:48:43',NULL,NULL),(41,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts in Psychology (BAP). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-10-28 10:48:43',NULL,NULL),(42,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts in Psychology (BAP). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-10-28 10:48:43',NULL,NULL),(43,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts in Psychology (BAP). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-10-28 10:48:43',NULL,NULL),(46,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts in Psychology (BAP). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-10-28 10:48:43',NULL,NULL),(47,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-10-29 13:24:19',NULL,NULL),(48,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-10-29 13:24:19',NULL,NULL),(49,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-10-29 13:24:19',NULL,NULL),(51,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-10-29 13:24:19',NULL,NULL),(52,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-10-29 13:24:19',NULL,NULL),(53,'New Program Created','College of Criminology Dean has created a new program: Bachelor of Science in Criminology (BSC)','info',45,'Romelinda Romeo Salvacion','dean','super_admin',NULL,0,'2025-11-02 15:51:04',NULL,NULL),(54,'New Program Created','College of Criminology Dean has created a new program: Bachelor of Science in Criminology (BSC). Please update library resources accordingly.','info',45,'Romelinda Romeo Salvacion','dean','librarian',NULL,0,'2025-11-02 15:51:04',NULL,NULL),(55,'New Program Created','College of Criminology Dean has created a new program: Bachelor of Science in Criminology (BSC). Please review for quality assurance.','info',45,'Romelinda Romeo Salvacion','dean','quality_assurance',NULL,0,'2025-11-02 15:51:04',NULL,NULL),(56,'New Program Created','College of Criminology Dean has created a new program: Bachelor of Science in Criminology (BSC). This program is now available for your department.','info',45,'Romelinda Romeo Salvacion','dean','teacher',45,0,'2025-11-02 15:51:04',NULL,NULL),(57,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Criminology (BSC). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',45,0,'2025-11-02 15:51:04',NULL,NULL),(58,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accountancy (BSA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-02 16:20:00',NULL,NULL),(59,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accountancy (BSA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-02 16:20:00',NULL,NULL),(60,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accountancy (BSA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-02 16:20:00',NULL,NULL),(62,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accountancy (BSA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-02 16:20:00',NULL,NULL),(63,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Accountancy (BSA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-02 16:20:00',NULL,NULL),(64,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Management Accounting (BSMA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-02 16:20:39',NULL,NULL),(65,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Management Accounting (BSMA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-02 16:20:39',NULL,NULL),(66,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Management Accounting (BSMA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-02 16:20:39',NULL,NULL),(68,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Management Accounting (BSMA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-02 16:20:40',NULL,NULL),(69,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Management Accounting (BSMA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-02 16:20:40',NULL,NULL),(70,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Internal Auditing (BSIA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-02 16:21:16',NULL,NULL),(71,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Internal Auditing (BSIA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-02 16:21:16',NULL,NULL),(72,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Internal Auditing (BSIA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-02 16:21:16',NULL,NULL),(74,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Internal Auditing (BSIA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-02 16:21:16',NULL,NULL),(75,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Internal Auditing (BSIA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-02 16:21:16',NULL,NULL),(76,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accounting Information System (BSAIS)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-02 16:21:57',NULL,NULL),(77,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accounting Information System (BSAIS). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-02 16:21:57',NULL,NULL),(78,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accounting Information System (BSAIS). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-02 16:21:57',NULL,NULL),(80,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Accounting Information System (BSAIS). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-02 16:21:57',NULL,NULL),(81,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Accounting Information System (BSAIS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-02 16:21:57',NULL,NULL),(82,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-02 16:24:27',NULL,NULL),(83,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-02 16:24:27',NULL,NULL),(84,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-02 16:24:27',NULL,NULL),(86,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-02 16:24:27',NULL,NULL),(87,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-02 16:24:27',NULL,NULL),(88,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-FM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:17:30',NULL,NULL),(89,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-FM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:17:30',NULL,NULL),(90,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-FM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:17:30',NULL,NULL),(92,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-FM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:17:30',NULL,NULL),(93,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA-FM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:17:30',NULL,NULL),(94,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-HRM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:31:58',NULL,NULL),(95,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-HRM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:31:58',NULL,NULL),(96,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-HRM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:31:58',NULL,NULL),(98,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-HRM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:31:58',NULL,NULL),(99,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA-HRM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:31:58',NULL,NULL),(100,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-OM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:32:56',NULL,NULL),(101,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-OM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:32:56',NULL,NULL),(102,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-OM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:32:56',NULL,NULL),(104,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-OM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:32:56',NULL,NULL),(105,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA-OM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:32:56',NULL,NULL),(106,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-MM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:33:43',NULL,NULL),(107,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-MM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:33:43',NULL,NULL),(108,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-MM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:33:43',NULL,NULL),(110,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Business Administration (BSBA-MM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:33:45',NULL,NULL),(111,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Business Administration (BSBA-MM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:33:45',NULL,NULL),(112,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Hospitality Management (BSHM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:34:34',NULL,NULL),(113,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Hospitality Management (BSHM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:34:34',NULL,NULL),(114,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Hospitality Management (BSHM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:34:35',NULL,NULL),(116,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Hospitality Management (BSHM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:34:35',NULL,NULL),(117,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Hospitality Management (BSHM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:34:35',NULL,NULL),(118,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Office Administration (BSOA)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:35:04',NULL,NULL),(119,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Office Administration (BSOA). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:35:04',NULL,NULL),(120,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Office Administration (BSOA). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:35:04',NULL,NULL),(122,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Office Administration (BSOA). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:35:04',NULL,NULL),(123,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Office Administration (BSOA). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:35:04',NULL,NULL),(124,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM)','info',46,'Susan Ramirez','dean','super_admin',NULL,0,'2025-11-03 00:35:33',NULL,NULL),(125,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please update library resources accordingly.','info',46,'Susan Ramirez','dean','librarian',NULL,0,'2025-11-03 00:35:33',NULL,NULL),(126,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please review for quality assurance.','info',46,'Susan Ramirez','dean','quality_assurance',NULL,0,'2025-11-03 00:35:33',NULL,NULL),(128,'New Program Created','College of Business Education Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). This program is now available for your department.','info',46,'Susan Ramirez','dean','teacher',46,0,'2025-11-03 00:35:33',NULL,NULL),(129,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Real Estate Management (BSREM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',46,0,'2025-11-03 00:35:33',NULL,NULL),(130,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Social Work (BSSW)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:38:38',NULL,NULL),(131,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Social Work (BSSW). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:38:38',NULL,NULL),(132,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Social Work (BSSW). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:38:38',NULL,NULL),(133,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Social Work (BSSW). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:38:38',NULL,NULL),(136,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Social Work (BSSW). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:38:39',NULL,NULL),(137,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:40:39',NULL,NULL),(138,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:40:39',NULL,NULL),(139,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:40:39',NULL,NULL),(140,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:40:39',NULL,NULL),(143,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:40:39',NULL,NULL),(144,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-EL)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:41:44',NULL,NULL),(145,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-EL). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:41:44',NULL,NULL),(146,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-EL). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:41:44',NULL,NULL),(147,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-EL). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:41:44',NULL,NULL),(150,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-EL). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:41:44',NULL,NULL),(151,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-H)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:42:22',NULL,NULL),(152,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-H). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:42:22',NULL,NULL),(153,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-H). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:42:22',NULL,NULL),(154,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-H). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:42:22',NULL,NULL),(157,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-H). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:42:22',NULL,NULL),(158,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-P)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:42:58',NULL,NULL),(159,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-P). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:42:58',NULL,NULL),(160,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-P). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:42:58',NULL,NULL),(161,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-P). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:42:58',NULL,NULL),(164,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-P). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:42:58',NULL,NULL),(165,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PS)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:43:25',NULL,NULL),(166,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PS). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:43:25',NULL,NULL),(167,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PS). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:43:26',NULL,NULL),(168,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PS). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:43:26',NULL,NULL),(171,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:43:26',NULL,NULL),(172,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PHILO)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:45:22',NULL,NULL),(173,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PHILO). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:45:22',NULL,NULL),(174,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PHILO). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:45:22',NULL,NULL),(175,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PHILO). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:45:22',NULL,NULL),(178,'Program Created Successfully','You have successfully created a new program: Bachelor of Arts (Artium Baccalaureus) (AB-PHILO). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:45:22',NULL,NULL),(179,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Elementary Education (BEED)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:45:53',NULL,NULL),(180,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Elementary Education (BEED). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:45:53',NULL,NULL),(181,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Elementary Education (BEED). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:45:53',NULL,NULL),(182,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Elementary Education (BEED). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:45:53',NULL,NULL),(185,'Program Created Successfully','You have successfully created a new program: Bachelor of Elementary Education (BEED). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:45:53',NULL,NULL),(186,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Physical Education (BPEd)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:46:32',NULL,NULL),(187,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Physical Education (BPEd). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:46:32',NULL,NULL),(188,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Physical Education (BPEd). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:46:32',NULL,NULL),(189,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Physical Education (BPEd). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:46:33',NULL,NULL),(192,'Program Created Successfully','You have successfully created a new program: Bachelor of Physical Education (BPEd). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:46:33',NULL,NULL),(193,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Technology & Livelihood Education (BTLEd)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:47:07',NULL,NULL),(194,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Technology & Livelihood Education (BTLEd). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:47:07',NULL,NULL),(195,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Technology & Livelihood Education (BTLEd). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:47:07',NULL,NULL),(196,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Technology & Livelihood Education (BTLEd). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:47:07',NULL,NULL),(199,'Program Created Successfully','You have successfully created a new program: Bachelor of Technology & Livelihood Education (BTLEd). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:47:08',NULL,NULL),(200,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:47:36',NULL,NULL),(201,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:47:36',NULL,NULL),(202,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:47:36',NULL,NULL),(203,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:47:36',NULL,NULL),(206,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:47:36',NULL,NULL),(207,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-ENG)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:51:38',NULL,NULL),(208,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-ENG). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:51:38',NULL,NULL),(209,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-ENG). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:51:38',NULL,NULL),(210,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-ENG). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:51:38',NULL,NULL),(213,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-ENG). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:51:38',NULL,NULL),(214,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SCI)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:52:10',NULL,NULL),(215,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SCI). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:52:10',NULL,NULL),(216,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SCI). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:52:10',NULL,NULL),(217,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SCI). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:52:10',NULL,NULL),(220,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-SCI). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:52:10',NULL,NULL),(221,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-MATH)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:52:40',NULL,NULL),(222,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-MATH). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:52:41',NULL,NULL),(223,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-MATH). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:52:41',NULL,NULL),(224,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-MATH). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:52:41',NULL,NULL),(227,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-MATH). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:52:42',NULL,NULL),(228,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SS)','info',47,'Genesis Naparan','dean','super_admin',NULL,1,'2025-11-03 00:53:20','2026-02-26 02:58:31',NULL),(229,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SS). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:53:20',NULL,NULL),(230,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SS). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:53:20',NULL,NULL),(231,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-SS). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:53:20',NULL,NULL),(234,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-SS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:53:20',NULL,NULL),(235,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-FIL)','info',47,'Genesis Naparan','dean','super_admin',NULL,0,'2025-11-03 00:54:01',NULL,NULL),(236,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-FIL). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:54:01',NULL,NULL),(237,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-FIL). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:54:01',NULL,NULL),(238,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-FIL). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:54:01',NULL,NULL),(241,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-FIL). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:54:01',NULL,NULL),(242,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-REL)','info',47,'Genesis Naparan','dean','super_admin',NULL,1,'2025-11-03 00:54:48','2026-02-26 03:29:42',NULL),(243,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-REL). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:54:48',NULL,NULL),(244,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-REL). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:54:48',NULL,NULL),(245,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Secondary Education (BSED-REL). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:54:49',NULL,NULL),(248,'Program Created Successfully','You have successfully created a new program: Bachelor of Secondary Education (BSED-REL). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:54:49',NULL,NULL),(249,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM)','info',47,'Genesis Naparan','dean','super_admin',NULL,1,'2025-11-03 00:55:11','2026-02-26 02:58:40',NULL),(250,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please update library resources accordingly.','info',47,'Genesis Naparan','dean','librarian',NULL,0,'2025-11-03 00:55:11',NULL,NULL),(251,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). Please review for quality assurance.','info',47,'Genesis Naparan','dean','quality_assurance',NULL,0,'2025-11-03 00:55:11',NULL,NULL),(252,'New Program Created','College of Teacher Education, Arts and Sciences Dean has created a new program: Bachelor of Science in Real Estate Management (BSREM). This program is now available for your department.','info',47,'Genesis Naparan','dean','teacher',47,0,'2025-11-03 00:55:11',NULL,NULL),(255,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Real Estate Management (BSREM). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',47,0,'2025-11-03 00:55:11',NULL,NULL),(256,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Technology (BSIT)','info',60,'Philipchris Encarnacion','dean','super_admin',NULL,1,'2025-11-05 08:47:14','2026-02-26 03:29:42',NULL),(257,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Technology (BSIT). Please update library resources accordingly.','info',60,'Philipchris Encarnacion','dean','librarian',NULL,0,'2025-11-05 08:47:14',NULL,NULL),(258,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Technology (BSIT). Please review for quality assurance.','info',60,'Philipchris Encarnacion','dean','quality_assurance',NULL,0,'2025-11-05 08:47:14',NULL,NULL),(259,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Information Technology (BSIT). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',60,0,'2025-11-05 08:47:14',NULL,NULL),(260,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor in Library and Information Science (BLIS)','info',60,'Philipchris Encarnacion','dean','super_admin',NULL,1,'2025-11-07 00:29:44','2026-02-26 02:07:26',NULL),(261,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor in Library and Information Science (BLIS). Please update library resources accordingly.','info',60,'Philipchris Encarnacion','dean','librarian',NULL,0,'2025-11-07 00:29:44',NULL,NULL),(262,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor in Library and Information Science (BLIS). Please review for quality assurance.','info',60,'Philipchris Encarnacion','dean','quality_assurance',NULL,0,'2025-11-07 00:29:44',NULL,NULL),(263,'Program Created Successfully','You have successfully created a new program: Bachelor in Library and Information Science (BLIS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',60,0,'2025-11-07 00:29:44',NULL,NULL),(264,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Computer Science (BSCS)','info',60,'Philipchris Encarnacion','dean','super_admin',NULL,1,'2025-11-07 00:30:39','2026-02-26 02:07:23',NULL),(265,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Computer Science (BSCS). Please update library resources accordingly.','info',60,'Philipchris Encarnacion','dean','librarian',NULL,0,'2025-11-07 00:30:39',NULL,NULL),(266,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Computer Science (BSCS). Please review for quality assurance.','info',60,'Philipchris Encarnacion','dean','quality_assurance',NULL,0,'2025-11-07 00:30:39',NULL,NULL),(267,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Computer Science (BSCS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',60,0,'2025-11-07 00:30:39',NULL,NULL),(268,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Systems (BSIS)','info',60,'Philipchris Encarnacion','dean','super_admin',NULL,1,'2025-11-07 00:31:29','2026-02-25 10:00:06',NULL),(269,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Systems (BSIS). Please update library resources accordingly.','info',60,'Philipchris Encarnacion','dean','librarian',NULL,0,'2025-11-07 00:31:29',NULL,NULL),(270,'New Program Created','College of Computing Studies Dean has created a new program: Bachelor of Science in Information Systems (BSIS). Please review for quality assurance.','info',60,'Philipchris Encarnacion','dean','quality_assurance',NULL,0,'2025-11-07 00:31:29',NULL,NULL),(271,'Program Created Successfully','You have successfully created a new program: Bachelor of Science in Information Systems (BSIS). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.','success',NULL,'System','system','dean',60,1,'2025-11-07 00:31:29','2026-02-25 09:17:05',NULL);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_code` varchar(20) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `major` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `color_code` varchar(7) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_program_department` (`program_code`,`department_id`),
  KEY `department_id` (`department_id`),
  KEY `idx_programs_created_by` (`created_by`),
  CONSTRAINT `fk_programs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES (13,'BSC','Bachelor of Science in Criminology','',45,'#228B22',3,'2025-11-02 15:51:04','2025-11-02 15:51:04'),(14,'BSA','Bachelor of Science in Accountancy','',46,'#FFC000',1,'2025-11-02 16:20:00','2025-11-02 16:20:00'),(15,'BSMA','Bachelor of Science in Management Accounting','',46,'#FFC000',1,'2025-11-02 16:20:39','2025-11-02 16:20:39'),(16,'BSIA','Bachelor of Science in Internal Auditing','',46,'#FFC000',1,'2025-11-02 16:21:16','2025-11-02 16:21:16'),(17,'BSAIS','Bachelor of Science in Accounting Information System','',46,'#FFC000',1,'2025-11-02 16:21:56','2025-11-02 16:21:56'),(18,'BSBA','Bachelor of Science in Business Administration','',46,'#FFC000',1,'2025-11-02 16:24:26','2025-11-02 16:24:26'),(19,'BSBA-FM','Bachelor of Science in Business Administration','Financial Management',46,'#FFC000',1,'2025-11-03 00:17:30','2025-11-03 00:17:30'),(20,'BSBA-HRM','Bachelor of Science in Business Administration','Human Resource Management',46,'#FFC000',1,'2025-11-03 00:31:56','2025-11-03 00:31:56'),(21,'BSBA-OM','Bachelor of Science in Business Administration','Operations Management',46,'#FFC000',1,'2025-11-03 00:32:56','2025-11-03 00:32:56'),(22,'BSBA-MM','Bachelor of Science in Business Administration','Marketing Management',46,'#FFC000',1,'2025-11-03 00:33:43','2025-11-03 00:33:43'),(23,'BSHM','Bachelor of Science in Hospitality Management','',46,'#FFC000',1,'2025-11-03 00:34:33','2025-11-03 00:34:33'),(24,'BSOA','Bachelor of Science in Office Administration','',46,'#FFC000',1,'2025-11-03 00:35:04','2025-11-03 00:35:04'),(25,'BSREM','Bachelor of Science in Real Estate Management','',46,'#FFC000',1,'2025-11-03 00:35:33','2025-11-03 00:35:33'),(26,'BSSW','Bachelor of Science in Social Work','',47,'#0047AB',4,'2025-11-03 00:38:38','2025-11-03 00:38:38'),(27,'AB','Bachelor of Arts (Artium Baccalaureus)','',47,'#0047AB',4,'2025-11-03 00:40:39','2025-11-03 00:40:39'),(28,'AB-EL','Bachelor of Arts (Artium Baccalaureus)','English Language',47,'#0047AB',4,'2025-11-03 00:41:44','2025-11-03 00:41:44'),(29,'AB-H','Bachelor of Arts (Artium Baccalaureus)','History',47,'#0047AB',4,'2025-11-03 00:42:22','2025-11-03 00:42:22'),(30,'AB-PSY','Bachelor of Arts (Artium Baccalaureus)','Psychology',47,'#0047AB',4,'2025-11-03 00:42:58','2025-11-03 00:44:04'),(31,'AB-POLSCI','Bachelor of Arts (Artium Baccalaureus)','Political Science',47,'#0047AB',4,'2025-11-03 00:43:24','2025-11-03 00:44:34'),(32,'AB-PHILO','Bachelor of Arts (Artium Baccalaureus)','Philosophy',47,'#0047AB',4,'2025-11-03 00:45:21','2025-11-03 00:45:21'),(33,'BEED','Bachelor of Elementary Education','',47,'#0047AB',4,'2025-11-03 00:45:53','2025-11-03 00:45:53'),(34,'BPEd','Bachelor of Physical Education','',47,'#0047AB',4,'2025-11-03 00:46:31','2025-11-03 00:46:31'),(35,'BTLEd','Bachelor of Technology & Livelihood Education','',47,'#0047AB',4,'2025-11-03 00:47:07','2025-11-03 00:47:07'),(36,'BSED','Bachelor of Secondary Education','',47,'#0047AB',4,'2025-11-03 00:47:36','2025-11-03 00:47:36'),(37,'BSED-ENG','Bachelor of Secondary Education','English',47,'#0047AB',4,'2025-11-03 00:51:38','2025-11-03 00:51:38'),(38,'BSED-SCI','Bachelor of Secondary Education','Science',47,'#0047AB',4,'2025-11-03 00:52:10','2025-11-03 00:52:10'),(39,'BSED-MATH','Bachelor of Secondary Education','Mathematics',47,'#0047AB',4,'2025-11-03 00:52:40','2025-11-03 00:52:40'),(40,'BSED-SS','Bachelor of Secondary Education','Social Studies',47,'#0047AB',4,'2025-11-03 00:53:20','2025-11-03 00:53:20'),(41,'BSED-FIL','Bachelor of Secondary Education','Filipino',47,'#0047AB',4,'2025-11-03 00:54:01','2025-11-03 00:54:01'),(42,'BSED-REL','Bachelor of Secondary Education','Val. Educ. w/ Religious Educ.',47,'#0047AB',4,'2025-11-03 00:54:48','2025-11-03 00:54:48'),(43,'BSREM','Bachelor of Science in Real Estate Management','',47,'#0047AB',4,'2025-11-03 00:55:11','2025-11-03 00:55:11'),(44,'BSIT','Bachelor of Science in Information Technology','',60,'#C11F1F',16,'2025-11-05 08:47:14','2025-11-05 08:47:14'),(45,'BLIS','Bachelor in Library and Information Science','',60,'#C11F1F',16,'2025-11-07 00:29:44','2025-11-07 00:29:44'),(46,'BSCS','Bachelor of Science in Computer Science','',60,'#C11F1F',16,'2025-11-07 00:30:39','2025-11-07 00:30:39'),(47,'BSIS','Bachelor of Science in Information Systems','',60,'#C11F1F',16,'2025-11-07 00:31:29','2025-11-07 00:31:29');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin - Quality Assurance'),(2,'Department Dean'),(3,'Librarian'),(4,'Teacher');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_calendar`
--

DROP TABLE IF EXISTS `school_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term_id` int(11) NOT NULL,
  `event_type` enum('class','holiday','exam','event') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `term_id` (`term_id`),
  CONSTRAINT `school_calendar_ibfk_1` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_calendar`
--

LOCK TABLES `school_calendar` WRITE;
/*!40000 ALTER TABLE `school_calendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_terms`
--

DROP TABLE IF EXISTS `school_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_year_id` (`school_year_id`),
  CONSTRAINT `school_terms_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_terms`
--

LOCK TABLES `school_terms` WRITE;
/*!40000 ALTER TABLE `school_terms` DISABLE KEYS */;
INSERT INTO `school_terms` VALUES (5,'1st Semester',35,'2025-07-07','2025-12-18','Active','2025-08-12 10:37:58','2025-08-12 10:37:58'),(6,'2nd Semester',35,'2026-01-05','2026-03-12','Inactive','2025-08-12 10:46:14','2025-08-12 10:46:14');
/*!40000 ALTER TABLE `school_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_years`
--

DROP TABLE IF EXISTS `school_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year_label` varchar(50) DEFAULT NULL,
  `year_start` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `year_end` int(11) NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_years`
--

LOCK TABLES `school_years` WRITE;
/*!40000 ALTER TABLE `school_years` DISABLE KEYS */;
INSERT INTO `school_years` VALUES (35,'A.Y. 2025 - 2026',2025,'2025-07-07',2026,'2026-06-08','Active','2025-08-12 07:34:43');
/*!40000 ALTER TABLE `school_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_admin`
--

DROP TABLE IF EXISTS `super_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `super_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admin`
--

LOCK TABLES `super_admin` WRITE;
/*!40000 ALTER TABLE `super_admin` DISABLE KEYS */;
INSERT INTO `super_admin` VALUES (1,'superadmin@sccpag.edu.ph','password123',1,'2025-07-07 07:25:27');
/*!40000 ALTER TABLE `super_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_year_id` (`school_year_id`),
  CONSTRAINT `terms_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (1,35,'1st Semester','2025-08-01','2025-12-15',1,'2025-10-21 21:59:24'),(2,35,'2nd Semester','2026-01-15','2026-05-31',0,'2025-10-21 21:59:25'),(3,35,'Summer Semester','2026-06-01','2026-07-31',0,'2025-10-21 21:59:25');
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `assigned_by` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_name`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (6,45,'teacher','system','2025-08-08 07:00:57',1),(7,46,'teacher','system','2025-08-08 07:00:57',1),(8,47,'teacher','system','2025-08-08 07:00:57',1),(24,58,'librarian','super_admin','2025-12-02 12:25:39',1),(25,59,'quality_assurance','super_admin','2025-11-05 08:50:16',1),(26,61,'librarian','super_admin','2025-12-02 12:25:35',0);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `name_prefix` varchar(20) DEFAULT NULL,
  `institutional_email` varchar(150) NOT NULL,
  `mobile_no` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity` timestamp NULL DEFAULT NULL,
  `online_status` enum('online','offline') DEFAULT 'offline',
  `last_login` timestamp NULL DEFAULT NULL,
  `last_logout` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_no` (`employee_no`),
  UNIQUE KEY `institutional_email` (`institutional_email`),
  KEY `role_id` (`role_id`),
  KEY `department_id` (`department_id`),
  KEY `idx_users_online_status` (`online_status`),
  KEY `idx_users_last_login` (`last_login`),
  KEY `idx_users_last_logout` (`last_logout`),
  KEY `idx_users_last_activity` (`last_activity`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (45,'125036','Romelinda Romeo','','Salvacion','Dr.','','romelindasalvacion@sccpag.edu.ph','','125036TCHCOC',4,3,'Super Admin MIS',1,'2025-08-07 17:35:59','2025-11-05 15:16:48','2025-11-05 07:16:48','offline','2025-11-05 07:16:06','2025-11-05 07:16:48'),(46,'102564','Susan','','Ramirez','Dr.','','susanramirez@sccpag.edu.ph','','102564TCHCBE',4,1,'Super Admin MIS',1,'2025-08-07 17:36:59','2025-11-07 08:50:12','2025-11-07 00:50:12','offline','2025-11-07 00:49:30','2025-11-07 00:50:12'),(47,'150640','Genesis','','Naparan','Dr.','','genesisnaparan@sccpag.edu.ph','','150640TCHCTEAS',4,4,'Super Admin MIS',1,'2025-08-07 17:39:07','2025-11-07 08:49:01','2025-11-07 00:49:01','offline','2025-11-07 00:48:23','2025-11-07 00:49:01'),(58,'768974','Liza','','Nillama',NULL,'','lizanillama@sccpag.edu.ph','','768974TCHCTEAS',4,4,'Super Admin MIS',1,'2025-11-05 15:20:18','2026-02-20 22:39:04','2026-02-20 14:39:03','offline','2025-12-02 17:11:38','2026-02-20 14:39:04'),(59,'246564','Janus','','Agustero-Naparan',NULL,'','janusagustero@sccpag.edu.ph','','246564TCHCTEAS',4,4,'Super Admin MIS',1,'2025-11-05 15:21:07','2026-02-26 04:41:18','2026-02-25 10:42:02','offline','2026-02-25 09:27:23','2026-02-25 20:41:18'),(60,'128744','Philipchris','C.','Encarnacion','Dr.','','philipcrisencarnacion@sccpag.edu.ph','','128744TCHCCS',4,16,'Super Admin MIS',1,'2025-11-05 16:41:53','2026-02-26 10:26:35','2026-02-26 02:26:35','online','2026-02-26 02:22:30','2026-02-25 09:20:15'),(61,'489530','Leomar','F.','Nuevo',NULL,'','leomarnuevo@sccpag.edu.ph','','489530TCHCCS',4,16,'Super Admin MIS',1,'2025-11-07 08:52:41','2026-02-25 17:24:32','2026-02-25 09:24:32','offline','2025-12-03 02:00:14','2026-02-25 09:24:32'),(62,'664489','Rowelyn','','Lagnason',NULL,'','rowelynlagnason@sccpag.edu.ph','','664489TCHCCS',4,16,'Super Admin MIS',1,'2025-11-07 10:56:59','2025-11-07 11:02:23','2025-11-07 02:58:58','offline','2025-11-07 02:57:40','2025-11-07 02:58:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-17 22:37:30
