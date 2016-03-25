-- MySQL dump 10.13  Distrib 5.5.47, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: github
-- ------------------------------------------------------
-- Server version	5.5.47-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_page_id` int(10) unsigned DEFAULT NULL,
  `template_id` int(10) unsigned NOT NULL,
  `page_key` varchar(100) NOT NULL,
  `route` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `sort_key` int(11) NOT NULL,
  PRIMARY KEY (`page_id`),
  KEY `fk_page__parent_page_id_idx` (`parent_page_id`),
  KEY `fk_page__template_id_idx` (`template_id`),
  KEY `page__sort_key_idx` (`sort_key`),
  KEY `page__parent_page_id_enabled_show_in_menu_idx` (`parent_page_id`,`enabled`,`show_in_menu`),
  KEY `page__page_key` (`page_key`),
  CONSTRAINT `fk_page__parent_page_id` FOREIGN KEY (`parent_page_id`) REFERENCES `page` (`page_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_page__template_id` FOREIGN KEY (`template_id`) REFERENCES `template` (`template_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page`
--

LOCK TABLES `page` WRITE;
/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` VALUES (1,NULL,1,'home','/home/index','Home',NULL,1,1,1);
/*!40000 ALTER TABLE `page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repo`
--

DROP TABLE IF EXISTS `repo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repo` (
  `repo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sync_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `repo_created_on` timestamp NULL DEFAULT NULL,
  `repo_updated_on` timestamp NULL DEFAULT NULL,
  `last_push_date` timestamp NULL DEFAULT NULL,
  `stars` int(10) unsigned DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`repo_id`),
  KEY `repo__sync_id_idx` (`sync_id`),
  KEY `repo__stars_desc_idx` (`stars`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repo`
--

LOCK TABLES `repo` WRITE;
/*!40000 ALTER TABLE `repo` DISABLE KEYS */;
/*!40000 ALTER TABLE `repo` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`github`@`localhost`*/ /*!50003 TRIGGER `github`.`repo_BEFORE_UPDATE` BEFORE UPDATE ON `repo` FOR EACH ROW
BEGIN
	IF NEW.updated_on IS NULL OR NEW.updated_on = '0000-00-00 00:00:00'
    THEN
		SET NEW.updated_on = CURRENT_TIMESTAMP;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `repo_import`
--

DROP TABLE IF EXISTS `repo_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repo_import` (
  `repo_import_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`repo_import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repo_import`
--

LOCK TABLES `repo_import` WRITE;
/*!40000 ALTER TABLE `repo_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `repo_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repo_import_object`
--

DROP TABLE IF EXISTS `repo_import_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repo_import_object` (
  `repo_import_id` int(10) unsigned NOT NULL,
  `repo_id` int(10) unsigned NOT NULL,
  `created_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`repo_import_id`,`repo_id`),
  KEY `fk_repo_import_object__repo_id_idx` (`repo_id`),
  CONSTRAINT `fk_repo_import_object__repo_id` FOREIGN KEY (`repo_id`) REFERENCES `repo` (`repo_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_repo_import_object__repo_import_id` FOREIGN KEY (`repo_import_id`) REFERENCES `repo_import` (`repo_import_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repo_import_object`
--

LOCK TABLES `repo_import_object` WRITE;
/*!40000 ALTER TABLE `repo_import_object` DISABLE KEYS */;
/*!40000 ALTER TABLE `repo_import_object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template`
--

LOCK TABLES `template` WRITE;
/*!40000 ALTER TABLE `template` DISABLE KEYS */;
INSERT INTO `template` VALUES (1,'main','Main',NULL);
/*!40000 ALTER TABLE `template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'github'
--
/*!50003 DROP PROCEDURE IF EXISTS `repo_import` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`github`@`localhost` PROCEDURE `repo_import`(
	IN p_repo_import_id INTEGER(10) UNSIGNED,
	IN p_sync_id INTEGER(10) UNSIGNED,
	IN p_name CHARACTER VARYING(255),
	IN p_description TEXT, 
	IN p_url CHARACTER VARYING(255),
	IN p_repo_created_on TIMESTAMP,
	IN p_repo_updated_on TIMESTAMP,
	IN p_last_push_date TIMESTAMP,
	IN p_stars INTEGER(10) UNSIGNED
)
BEGIN
	--
	-- Saves a repo and stores its id in repo_import_object
	--

	CALL repo_upsert
	(
		p_sync_id,
		p_name,
		p_description,
		p_url,
		p_repo_created_on,
		p_repo_updated_on,
		p_last_push_date,
		p_stars,
		@repo_id
	);

	INSERT INTO repo_import_object (repo_import_id, repo_id)
	VALUES (p_repo_import_id, @repo_id);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `repo_upsert` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`github`@`localhost` PROCEDURE `repo_upsert`(
	IN p_sync_id INTEGER(10) UNSIGNED,
	IN p_name CHARACTER VARYING(255),
	IN p_description TEXT, 
	IN p_url CHARACTER VARYING(255),
	IN p_repo_created_on TIMESTAMP,
	IN p_repo_updated_on TIMESTAMP,
	IN p_last_push_date TIMESTAMP,
	IN p_stars INTEGER(10) UNSIGNED,
	OUT p_repo_id INTEGER(10) UNSIGNED
)
BEGIN
	--
	-- Upsert logic for adding / updating repos
	--

	-- Check if sync_id already exists in repo, if so store the repo_id
	SELECT repo_id INTO p_repo_id FROM repo WHERE sync_id = p_sync_id;


	-- If the sync_id did exist do an update
	IF p_repo_id IS NOT NULL
	THEN

		UPDATE repo
		SET
			name = p_name,
			description = p_description,
			url = p_url,
			repo_created_on = p_repo_created_on,
			repo_updated_on = p_repo_updated_on,
			last_push_date = p_last_push_date,
			stars = p_stars
		WHERE sync_id = p_sync_id;
		
	-- Otherwise do an insert
	ELSE

		INSERT INTO repo (
			sync_id,
			name,
			description,
			url,
			repo_created_on,
			repo_updated_on,
			last_push_date,
			stars
		)
		VALUES (
			p_sync_id,
			p_name,
			p_description,
			p_url,
			p_repo_created_on,
			p_repo_updated_on,
			p_last_push_date,
			p_stars
		);
		
		-- Get repo_id from insert above
		SELECT last_insert_id() INTO p_repo_id;
		
	END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `test_proc` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`github`@`localhost` PROCEDURE `test_proc`(
	IN foo integer,
	OUT bar integer
)
BEGIN
	SELECT foo INTO bar;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-03-25  2:50:50
