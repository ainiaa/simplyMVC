/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.5.40 : Database - smvc_test_master
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`smvc_test_master` /*!40100 DEFAULT CHARACTER SET gbk */;

USE `smvc_test_master`;

/*Table structure for table `smvc_admin` */

DROP TABLE IF EXISTS `smvc_admin`;

CREATE TABLE `smvc_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(45) DEFAULT NULL,
  `password` char(35) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `smvc_admin` */

insert  into `smvc_admin`(`id`,`user_name`,`password`,`email`) values (1,'admin','96e79218965eb72c92a549dd5a330112','admin@admin.com');

/*Table structure for table `smvc_posts` */

DROP TABLE IF EXISTS `smvc_posts`;

CREATE TABLE `smvc_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `smvc_posts` */

/*Table structure for table `smvc_terms` */

DROP TABLE IF EXISTS `smvc_terms`;

CREATE TABLE `smvc_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `smvc_terms` */

/*Table structure for table `smvc_test` */

DROP TABLE IF EXISTS `smvc_test`;

CREATE TABLE `smvc_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `desc` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=latin1;

/*Data for the table `smvc_test` */

insert  into `smvc_test`(`id`,`name`,`desc`) values (1,'a','aaaaaaa'),(2,'b','bbbbbbb'),(3,'c','ccccccc'),(4,'d','ddddddd'),(5,'e','eeeeeee'),(6,'addName','addNameaddNameaddNameaddName'),(7,'addName','addNameaddNameaddNameaddName'),(8,'addName','addNameaddNameaddNameaddName'),(9,'addName','addNameaddNameaddNameaddName'),(10,'addName','addNameaddNameaddNameaddName'),(11,'addName','addNameaddNameaddNameaddName'),(12,'addName','addNameaddNameaddNameaddName'),(13,'addName','addNameaddNameaddNameaddName'),(14,'addName','addNameaddNameaddNameaddName'),(15,'addName','addNameaddNameaddNameaddName'),(16,'addName','addNameaddNameaddNameaddName'),(17,'addName','addNameaddNameaddNameaddName'),(18,'addName','addNameaddNameaddNameaddName'),(19,'addName','addNameaddNameaddNameaddName'),(20,'addName','addNameaddNameaddNameaddName'),(21,'addName','addNameaddNameaddNameaddName'),(22,'addName','addNameaddNameaddNameaddName'),(23,'addName','addNameaddNameaddNameaddName'),(24,'addName','addNameaddNameaddNameaddName'),(25,'addName','addNameaddNameaddNameaddName'),(26,'addName','addNameaddNameaddNameaddName'),(27,'addName','addNameaddNameaddNameaddName'),(28,'addName','addNameaddNameaddNameaddName'),(29,'addName','addNameaddNameaddNameaddName'),(30,'addName','addNameaddNameaddNameaddName'),(31,'addName','addNameaddNameaddNameaddName'),(32,'addName','addNameaddNameaddNameaddName'),(33,'addName','addNameaddNameaddNameaddName'),(34,'addName','addNameaddNameaddNameaddName'),(35,'addName','addNameaddNameaddNameaddName'),(36,'addName','addNameaddNameaddNameaddName'),(37,'addName','addNameaddNameaddNameaddName'),(38,'addName','addNameaddNameaddNameaddName'),(39,'addName','addNameaddNameaddNameaddName'),(40,'addName','addNameaddNameaddNameaddName'),(41,'addName','addNameaddNameaddNameaddName'),(42,'addName','addNameaddNameaddNameaddName'),(43,'addName','addNameaddNameaddNameaddName'),(44,'addName','addNameaddNameaddNameaddName'),(45,'addName','addNameaddNameaddNameaddName'),(46,'addName','addNameaddNameaddNameaddName'),(47,'addName','addNameaddNameaddNameaddName'),(48,'addName','addNameaddNameaddNameaddName'),(49,'addName','addNameaddNameaddNameaddName'),(50,'addName','addNameaddNameaddNameaddName'),(51,'addName','addNameaddNameaddNameaddName'),(52,'addName','addNameaddNameaddNameaddName'),(53,'addName','addNameaddNameaddNameaddName'),(54,'addName','addNameaddNameaddNameaddName'),(55,'addName','addNameaddNameaddNameaddName'),(56,'addName','addNameaddNameaddNameaddName'),(57,'addName','addNameaddNameaddNameaddName'),(58,'addName','addNameaddNameaddNameaddName'),(59,'addName','addNameaddNameaddNameaddName'),(60,'addName','addNameaddNameaddNameaddName'),(61,'addName','addNameaddNameaddNameaddName'),(62,'addName','addNameaddNameaddNameaddName'),(63,'addName','addNameaddNameaddNameaddName'),(64,'addName','addNameaddNameaddNameaddName'),(65,'addName','addNameaddNameaddNameaddName'),(66,'addName','addNameaddNameaddNameaddName'),(67,'addName','addNameaddNameaddNameaddName'),(68,'addName','addNameaddNameaddNameaddName'),(69,'addName','addNameaddNameaddNameaddName'),(70,'addName','addNameaddNameaddNameaddName'),(71,'addName','addNameaddNameaddNameaddName'),(72,'addName','addNameaddNameaddNameaddName'),(73,'addName','addNameaddNameaddNameaddName'),(74,'addName','addNameaddNameaddNameaddName'),(75,'addName','addNameaddNameaddNameaddName'),(76,'addName','addNameaddNameaddNameaddName'),(77,'addName','addNameaddNameaddNameaddName'),(78,'addName','addNameaddNameaddNameaddName'),(79,'addName','addNameaddNameaddNameaddName'),(80,'addName','addNameaddNameaddNameaddName'),(81,'addName','addNameaddNameaddNameaddName'),(82,'addName','addNameaddNameaddNameaddName'),(83,'addName','addNameaddNameaddNameaddName'),(84,'addName','addNameaddNameaddNameaddName'),(85,'addName','addNameaddNameaddNameaddName'),(86,'addName','addNameaddNameaddNameaddName'),(87,'addName','addNameaddNameaddNameaddName'),(88,'addName','addNameaddNameaddNameaddName'),(89,'addName','addNameaddNameaddNameaddName'),(90,'addName','addNameaddNameaddNameaddName'),(91,'addName','addNameaddNameaddNameaddName'),(92,'addName','addNameaddNameaddNameaddName'),(93,'addName','addNameaddNameaddNameaddName'),(94,'addName','addNameaddNameaddNameaddName'),(95,'addName','addNameaddNameaddNameaddName'),(96,'addName','addNameaddNameaddNameaddName'),(97,'addName','addNameaddNameaddNameaddName'),(98,'addName','addNameaddNameaddNameaddName'),(99,'addName','addNameaddNameaddNameaddName'),(100,'addName','addNameaddNameaddNameaddName'),(101,'addName','addNameaddNameaddNameaddName'),(102,'addName','addNameaddNameaddNameaddName'),(103,'addName','addNameaddNameaddNameaddName'),(104,'addName','addNameaddNameaddNameaddName'),(105,'addName','addNameaddNameaddNameaddName'),(106,'addName','addNameaddNameaddNameaddName'),(107,'addName','addNameaddNameaddNameaddName'),(108,'addName','addNameaddNameaddNameaddName'),(109,'addName','addNameaddNameaddNameaddName'),(110,'addName','addNameaddNameaddNameaddName'),(111,'addName','addNameaddNameaddNameaddName'),(112,'addName','addNameaddNameaddNameaddName'),(113,'addName','addNameaddNameaddNameaddName'),(114,'addName','addNameaddNameaddNameaddName'),(115,'addName','addNameaddNameaddNameaddName'),(116,'addName','addNameaddNameaddNameaddName'),(117,'addName','addNameaddNameaddNameaddName'),(118,'addName','addNameaddNameaddNameaddName'),(119,'addName','addNameaddNameaddNameaddName'),(120,'addName','addNameaddNameaddNameaddName'),(121,'addName','addNameaddNameaddNameaddName'),(122,'addName','addNameaddNameaddNameaddName'),(123,'addName','addNameaddNameaddNameaddName'),(124,'addName','addNameaddNameaddNameaddName'),(125,'addName','addNameaddNameaddNameaddName'),(126,'addName','addNameaddNameaddNameaddName'),(127,'addName','addNameaddNameaddNameaddName'),(128,'addName','addNameaddNameaddNameaddName'),(129,'addName','addNameaddNameaddNameaddName'),(130,'addName','addNameaddNameaddNameaddName'),(131,'addName','addNameaddNameaddNameaddName'),(132,'addName','addNameaddNameaddNameaddName'),(133,'addName','addNameaddNameaddNameaddName'),(134,'addName','addNameaddNameaddNameaddName'),(135,'addName','addNameaddNameaddNameaddName'),(136,'addName','addNameaddNameaddNameaddName'),(137,'addName','addNameaddNameaddNameaddName'),(138,'addName','addNameaddNameaddNameaddName'),(139,'addName','addNameaddNameaddNameaddName'),(140,'addName','addNameaddNameaddNameaddName'),(141,'addName','addNameaddNameaddNameaddName');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
