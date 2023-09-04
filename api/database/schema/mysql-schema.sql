/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `TD_Accreditation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Accreditation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fencer_id` int NOT NULL,
  `event_id` int NOT NULL,
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `hash` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `file_hash` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `template_id` int NOT NULL,
  `file_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `generated` datetime DEFAULT NULL,
  `is_dirty` datetime DEFAULT NULL,
  `fe_id` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Accreditation_Template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Accreditation_Template` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `event_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Category` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(45) DEFAULT NULL,
  `category_type` enum('T','I') NOT NULL COMMENT 'Team or Individual',
  `category_abbr` varchar(20) NOT NULL,
  `category_value` int NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Competition Type';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Competition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Competition` (
  `competition_id` int NOT NULL AUTO_INCREMENT,
  `competition_event` int DEFAULT NULL,
  `competition_category` int DEFAULT NULL,
  `competition_weapon` int DEFAULT NULL,
  `competition_opens` date DEFAULT NULL,
  `competition_weapon_check` date DEFAULT NULL,
  PRIMARY KEY (`competition_id`),
  KEY `competition_event_idx` (`competition_event`),
  KEY `competition_type_idx` (`competition_category`),
  KEY `competition_weapon_idx` (`competition_weapon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Competition';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Country` (
  `country_id` int NOT NULL AUTO_INCREMENT,
  `country_abbr` varchar(5) DEFAULT NULL,
  `country_name` varchar(45) DEFAULT NULL,
  `country_rep_firstname` varchar(45) DEFAULT NULL,
  `country_rep_surname` varchar(45) DEFAULT NULL,
  `country_rep_email` varchar(60) DEFAULT NULL,
  `country_rep_telnum` varchar(45) DEFAULT NULL,
  `country_rep_mob` varchar(45) DEFAULT NULL,
  `country_flag_path` varchar(1024) DEFAULT NULL,
  `country_web` varchar(100) DEFAULT NULL,
  `country_registered` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Country';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Document` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hash` varchar(260) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Event` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(100) DEFAULT NULL,
  `event_open` date DEFAULT NULL,
  `event_year` int NOT NULL,
  `event_duration` int DEFAULT NULL,
  `event_email` text,
  `event_web` text,
  `event_location` varchar(45) DEFAULT NULL,
  `event_country` int DEFAULT NULL,
  `event_type` int NOT NULL,
  `event_currency_symbol` varchar(10) NOT NULL,
  `event_currency_name` varchar(30) NOT NULL,
  `event_bank` varchar(100) NOT NULL,
  `event_account_name` varchar(100) NOT NULL,
  `event_organisers_address` text NOT NULL,
  `event_iban` varchar(40) NOT NULL,
  `event_swift` varchar(20) NOT NULL,
  `event_reference` varchar(255) DEFAULT NULL,
  `event_in_ranking` enum('Y','N') NOT NULL DEFAULT 'N',
  `event_frontend` int DEFAULT NULL,
  `event_factor` float DEFAULT '1',
  `event_registration_open` date DEFAULT NULL,
  `event_registration_close` date DEFAULT NULL,
  `event_base_fee` float DEFAULT NULL,
  `event_competition_fee` float DEFAULT NULL,
  `event_payments` varchar(20) DEFAULT NULL,
  `event_feed` text,
  `event_config` text,
  PRIMARY KEY (`event_id`),
  KEY `event_country_idx` (`event_country`),
  KEY `event_country` (`event_country`),
  CONSTRAINT `event_country` FOREIGN KEY (`event_country`) REFERENCES `TD_Country` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Event';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Event_Role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Event_Role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_type` enum('organiser','registrar','accreditation','cashier') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Event_Side`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Event_Side` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `costs` float NOT NULL,
  `competition_id` int DEFAULT NULL,
  `starts` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Event_Type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Event_Type` (
  `event_type_id` int NOT NULL AUTO_INCREMENT,
  `event_type_name` varchar(30) NOT NULL,
  `event_type_abbr` varchar(2) NOT NULL,
  `event_type_group` varchar(20) NOT NULL DEFAULT 'Team',
  PRIMARY KEY (`event_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Fencer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Fencer` (
  `fencer_id` int NOT NULL AUTO_INCREMENT,
  `fencer_firstname` varchar(45) DEFAULT NULL,
  `fencer_surname` varchar(45) DEFAULT NULL,
  `fencer_country` int DEFAULT NULL,
  `fencer_dob` date DEFAULT NULL,
  `fencer_gender` enum('M','F') DEFAULT NULL,
  `fencer_picture` enum('Y','N','A','R') DEFAULT NULL,
  PRIMARY KEY (`fencer_id`),
  KEY `fencer_country_idx` (`fencer_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Fencer';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Migration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Program` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(30) NOT NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` varchar(20) NOT NULL,
  `payload` text NOT NULL,
  `attempts` int NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime DEFAULT NULL,
  `queue` varchar(20) NOT NULL,
  `event_id` int DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Registrar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Registrar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `country_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Registration` (
  `registration_id` int NOT NULL AUTO_INCREMENT,
  `registration_role` int NOT NULL,
  `registration_fencer` int NOT NULL,
  `registration_event` int DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registration_paid` enum('Y','N') DEFAULT NULL,
  `registration_paid_hod` enum('Y','N') DEFAULT NULL,
  `registration_mainevent` int DEFAULT NULL,
  `registration_payment` char(1) DEFAULT NULL,
  `registration_state` char(1) DEFAULT NULL,
  `registration_team` varchar(100) DEFAULT NULL,
  `registration_country` int DEFAULT NULL,
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Result` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `result_competition` int DEFAULT NULL,
  `result_fencer` int DEFAULT NULL,
  `result_place` int DEFAULT NULL,
  `result_points` float NOT NULL DEFAULT '0',
  `result_entry` int NOT NULL DEFAULT '0',
  `result_de_points` float NOT NULL DEFAULT '0',
  `result_podium_points` float NOT NULL DEFAULT '0',
  `result_total_points` float NOT NULL DEFAULT '0',
  `result_in_ranking` enum('Y','N','E') DEFAULT 'N',
  PRIMARY KEY (`result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Entry/Result';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(30) NOT NULL,
  `role_type` int NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Role_Type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Role_Type` (
  `role_type_id` int NOT NULL AUTO_INCREMENT,
  `role_type_name` varchar(30) NOT NULL,
  `org_declaration` enum('Country','EVF','Org','FIE') DEFAULT NULL,
  PRIMARY KEY (`role_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `TD_Weapon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TD_Weapon` (
  `weapon_id` int NOT NULL AUTO_INCREMENT,
  `weapon_abbr` varchar(2) DEFAULT NULL,
  `weapon_name` varchar(45) DEFAULT NULL,
  `weapon_gender` enum('M','F') NOT NULL,
  PRIMARY KEY (`weapon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Weapon';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wp_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = @saved_cs_client */;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) UNSIGNED NOT NULL,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wp_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_users` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int NOT NULL DEFAULT '0',
  `display_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

