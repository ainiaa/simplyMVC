/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.5.40 : Database - smvc_test_slave
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`smvc_test_slave` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `smvc_test_slave`;

/*Table structure for table `smvc_admin` */

DROP TABLE IF EXISTS `smvc_admin`;

CREATE TABLE `smvc_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(45) NOT NULL,
  `password` char(35) NOT NULL,
  `email` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `smvc_admin` */

LOCK TABLES `smvc_admin` WRITE;

insert  into `smvc_admin`(`id`,`user_name`,`password`,`email`) values (1,'admin','96e79218965eb72c92a549dd5a330112','admin@admin.com');

UNLOCK TABLES;

/*Table structure for table `smvc_category` */

DROP TABLE IF EXISTS `smvc_category`;

CREATE TABLE `smvc_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `desc` varchar(200) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父类ID',
  `path` mediumtext NOT NULL,
  `type` enum('category','tag') DEFAULT 'category' COMMENT '类型',
  `depth` tinyint(4) NOT NULL DEFAULT '1' COMMENT '深度',
  `order_by` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_by` int(11) NOT NULL DEFAULT '0' COMMENT '更新人ID',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

/*Data for the table `smvc_category` */

LOCK TABLES `smvc_category` WRITE;

insert  into `smvc_category`(`id`,`name`,`desc`,`parent_id`,`path`,`depth`,`order_by`,`created_by`,`created_at`,`updated_by`,`updated_at`) values (2,'植物','	植物分类',0,',0,',1,0,0,1470886530,0,0),(3,'节肢动物','节肢动物',0,',0,',1,0,0,1470887856,0,0),(4,'软体动物','软体动物',0,',0,',1,0,0,1470889508,0,0),(5,'哺乳动物','哺乳动物',0,',0,',1,0,0,1470889549,0,0),(9,'	其他动物','	其他动物',4,',0,4,',2,0,0,1497948159,0,0);

UNLOCK TABLES;

/*Table structure for table `smvc_posts` */

DROP TABLE IF EXISTS `smvc_posts`;

CREATE TABLE `smvc_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '作者',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加日期',
  `post_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `post_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章标题',
  `post_excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章摘要',
  `post_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish' COMMENT '文章状态',
  `comment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT '文章屏蔽状态',
  `ping_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT '文章ping状态',
  `post_password` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章访问密码',
  `post_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文章name',
  `to_ping` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pinged` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `post_modified` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '文章修改时间',
  `post_modified_gmt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '文章修改时间',
  `post_content_filtered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `post_parent` bigint(20) unsigned DEFAULT '0',
  `guid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `menu_order` int(11) DEFAULT '0',
  `post_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'post' COMMENT '文章类型',
  `post_mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文章类型',
  `comment_count` bigint(20) DEFAULT '0' COMMENT '文章评论数量',
  PRIMARY KEY (`id`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`id`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `smvc_posts` */

LOCK TABLES `smvc_posts` WRITE;

insert  into `smvc_posts`(`id`,`post_author`,`post_date`,`post_content`,`post_title`,`post_excerpt`,`post_status`,`comment_status`,`ping_status`,`post_password`,`post_name`,`to_ping`,`pinged`,`post_modified`,`post_modified_gmt`,`post_content_filtered`,`post_parent`,`guid`,`menu_order`,`post_type`,`post_mime_type`,`comment_count`) values (1,0,'2016-03-23 14:38:12','hello, world!!','first post','hello, world!!','publish','open','open','','','','','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,'',0,'post','',0),(2,0,'2016-05-05 14:27:19','hello, world!!ssssssddddddd','first post','hello, world!!ssssss','publish','open','open','','','','','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,'',0,'post','',0),(3,0,'2016-09-29 14:43:28','','','0','publish','open','open','','','','','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,'',0,'post','',0);

UNLOCK TABLES;

/*Table structure for table `smvc_terms` */

DROP TABLE IF EXISTS `smvc_terms`;

CREATE TABLE `smvc_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `term_type` tinyint(1) DEFAULT '0' COMMENT '类型',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`desc`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `smvc_terms` */

LOCK TABLES `smvc_terms` WRITE;

UNLOCK TABLES;

/*Table structure for table `smvc_test` */

DROP TABLE IF EXISTS `smvc_test`;

CREATE TABLE `smvc_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET latin1 NOT NULL,
  `desc` varchar(45) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8;

/*Data for the table `smvc_test` */

LOCK TABLES `smvc_test` WRITE;

insert  into `smvc_test`(`id`,`name`,`desc`) values (1,'a','aaaaaaa'),(2,'b','bbbbbbb'),(3,'c','ccccccc'),(4,'d','ddddddd'),(5,'e','eeeeeee'),(6,'addName','addNameaddNameaddNameaddName'),(7,'addName','addNameaddNameaddNameaddName'),(8,'addName','addNameaddNameaddNameaddName'),(9,'addName','addNameaddNameaddNameaddName'),(10,'addName','addNameaddNameaddNameaddName'),(11,'addName','addNameaddNameaddNameaddName'),(12,'addName','addNameaddNameaddNameaddName'),(13,'addName','addNameaddNameaddNameaddName'),(14,'addName','addNameaddNameaddNameaddName'),(15,'addName','addNameaddNameaddNameaddName'),(16,'addName','addNameaddNameaddNameaddName'),(17,'addName','addNameaddNameaddNameaddName'),(18,'addName','addNameaddNameaddNameaddName'),(19,'addName','addNameaddNameaddNameaddName'),(20,'addName','addNameaddNameaddNameaddName'),(21,'addName','addNameaddNameaddNameaddName'),(22,'addName','addNameaddNameaddNameaddName'),(23,'addName','addNameaddNameaddNameaddName'),(24,'addName','addNameaddNameaddNameaddName'),(25,'addName','addNameaddNameaddNameaddName'),(26,'addName','addNameaddNameaddNameaddName'),(27,'addName','addNameaddNameaddNameaddName'),(28,'addName','addNameaddNameaddNameaddName'),(29,'addName','addNameaddNameaddNameaddName'),(30,'addName','addNameaddNameaddNameaddName'),(31,'addName','addNameaddNameaddNameaddName'),(32,'addName','addNameaddNameaddNameaddName'),(33,'addName','addNameaddNameaddNameaddName'),(34,'addName','addNameaddNameaddNameaddName'),(35,'addName','addNameaddNameaddNameaddName'),(36,'addName','addNameaddNameaddNameaddName'),(37,'addName','addNameaddNameaddNameaddName'),(38,'addName','addNameaddNameaddNameaddName'),(39,'addName','addNameaddNameaddNameaddName'),(40,'addName','addNameaddNameaddNameaddName'),(41,'addName','addNameaddNameaddNameaddName'),(42,'addName','addNameaddNameaddNameaddName'),(43,'addName','addNameaddNameaddNameaddName'),(44,'addName','addNameaddNameaddNameaddName'),(45,'addName','addNameaddNameaddNameaddName'),(46,'addName','addNameaddNameaddNameaddName'),(47,'addName','addNameaddNameaddNameaddName'),(48,'addName','addNameaddNameaddNameaddName'),(49,'addName','addNameaddNameaddNameaddName'),(50,'addName','addNameaddNameaddNameaddName'),(51,'addName','addNameaddNameaddNameaddName'),(52,'addName','addNameaddNameaddNameaddName'),(53,'addName','addNameaddNameaddNameaddName'),(54,'addName','addNameaddNameaddNameaddName'),(55,'addName','addNameaddNameaddNameaddName'),(56,'addName','addNameaddNameaddNameaddName'),(57,'addName','addNameaddNameaddNameaddName'),(58,'addName','addNameaddNameaddNameaddName'),(59,'addName','addNameaddNameaddNameaddName'),(60,'addName','addNameaddNameaddNameaddName'),(61,'addName','addNameaddNameaddNameaddName'),(62,'addName','addNameaddNameaddNameaddName'),(63,'addName','addNameaddNameaddNameaddName'),(64,'addName','addNameaddNameaddNameaddName'),(65,'addName','addNameaddNameaddNameaddName'),(66,'addName','addNameaddNameaddNameaddName'),(67,'addName','addNameaddNameaddNameaddName'),(68,'addName','addNameaddNameaddNameaddName'),(69,'addName','addNameaddNameaddNameaddName'),(70,'addName','addNameaddNameaddNameaddName'),(71,'addName','addNameaddNameaddNameaddName'),(72,'addName','addNameaddNameaddNameaddName'),(73,'addName','addNameaddNameaddNameaddName'),(74,'addName','addNameaddNameaddNameaddName'),(75,'addName','addNameaddNameaddNameaddName'),(76,'addName','addNameaddNameaddNameaddName'),(77,'addName','addNameaddNameaddNameaddName'),(78,'addName','addNameaddNameaddNameaddName'),(79,'addName','addNameaddNameaddNameaddName'),(80,'addName','addNameaddNameaddNameaddName'),(81,'addName','addNameaddNameaddNameaddName'),(82,'addName','addNameaddNameaddNameaddName'),(83,'addName','addNameaddNameaddNameaddName'),(84,'addName','addNameaddNameaddNameaddName'),(85,'addName','addNameaddNameaddNameaddName'),(86,'addName','addNameaddNameaddNameaddName'),(87,'addName','addNameaddNameaddNameaddName'),(88,'addName','addNameaddNameaddNameaddName'),(89,'addName','addNameaddNameaddNameaddName'),(90,'addName','addNameaddNameaddNameaddName'),(91,'addName','addNameaddNameaddNameaddName'),(92,'addName','addNameaddNameaddNameaddName'),(93,'addName','addNameaddNameaddNameaddName'),(94,'addName','addNameaddNameaddNameaddName'),(95,'addName','addNameaddNameaddNameaddName'),(96,'addName','addNameaddNameaddNameaddName'),(97,'addName','addNameaddNameaddNameaddName'),(98,'addName','addNameaddNameaddNameaddName'),(99,'addName','addNameaddNameaddNameaddName'),(100,'addName','addNameaddNameaddNameaddName'),(101,'addName','addNameaddNameaddNameaddName'),(102,'addName','addNameaddNameaddNameaddName'),(103,'addName','addNameaddNameaddNameaddName'),(104,'addName','addNameaddNameaddNameaddName'),(105,'addName','addNameaddNameaddNameaddName'),(106,'addName','addNameaddNameaddNameaddName'),(107,'addName','addNameaddNameaddNameaddName'),(108,'addName','addNameaddNameaddNameaddName'),(109,'addName','addNameaddNameaddNameaddName'),(110,'addName','addNameaddNameaddNameaddName'),(111,'addName','addNameaddNameaddNameaddName'),(112,'addName','addNameaddNameaddNameaddName'),(113,'addName','addNameaddNameaddNameaddName'),(114,'addName','addNameaddNameaddNameaddName'),(115,'addName','addNameaddNameaddNameaddName'),(116,'addName','addNameaddNameaddNameaddName'),(117,'addName','addNameaddNameaddNameaddName'),(118,'addName','addNameaddNameaddNameaddName'),(119,'addName','addNameaddNameaddNameaddName'),(120,'addName','addNameaddNameaddNameaddName'),(121,'addName','addNameaddNameaddNameaddName'),(122,'addName','addNameaddNameaddNameaddName'),(123,'addName','addNameaddNameaddNameaddName'),(124,'addName','addNameaddNameaddNameaddName'),(125,'addName','addNameaddNameaddNameaddName'),(126,'addName','addNameaddNameaddNameaddName'),(127,'addName','addNameaddNameaddNameaddName'),(128,'addName','addNameaddNameaddNameaddName'),(129,'addName','addNameaddNameaddNameaddName'),(130,'addName','addNameaddNameaddNameaddName'),(131,'addName','addNameaddNameaddNameaddName'),(132,'addName','addNameaddNameaddNameaddName'),(133,'addName','addNameaddNameaddNameaddName'),(134,'addName','addNameaddNameaddNameaddName'),(135,'addName','addNameaddNameaddNameaddName'),(136,'addName','addNameaddNameaddNameaddName'),(137,'addName','addNameaddNameaddNameaddName'),(138,'addName','addNameaddNameaddNameaddName'),(139,'addName','addNameaddNameaddNameaddName'),(140,'addName','addNameaddNameaddNameaddName'),(141,'addName','addNameaddNameaddNameaddName'),(142,'addName','addNameaddNameaddNameaddName'),(143,'addName','addNameaddNameaddNameaddName'),(144,'addName','addNameaddNameaddNameaddName'),(145,'addName','addNameaddNameaddNameaddName'),(146,'addName','addNameaddNameaddNameaddName'),(147,'addName','addNameaddNameaddNameaddName'),(148,'addName','addNameaddNameaddNameaddName'),(149,'addName','addNameaddNameaddNameaddName'),(150,'addName','addNameaddNameaddNameaddName'),(151,'addName','addNameaddNameaddNameaddName'),(152,'addName','addNameaddNameaddNameaddName'),(153,'addName','addNameaddNameaddNameaddName'),(154,'addName','addNameaddNameaddNameaddName'),(155,'addName','addNameaddNameaddNameaddName'),(156,'addName','addNameaddNameaddNameaddName'),(157,'addName','addNameaddNameaddNameaddName'),(158,'addName','addNameaddNameaddNameaddName'),(159,'addName','addNameaddNameaddNameaddName'),(160,'addName','addNameaddNameaddNameaddName'),(161,'addName','addNameaddNameaddNameaddName'),(162,'addName','addNameaddNameaddNameaddName'),(163,'addName','addNameaddNameaddNameaddName'),(164,'addName','addNameaddNameaddNameaddName'),(165,'addName','addNameaddNameaddNameaddName'),(166,'addName','addNameaddNameaddNameaddName'),(167,'addName','addNameaddNameaddNameaddName'),(168,'addName','addNameaddNameaddNameaddName'),(169,'addName','addNameaddNameaddNameaddName'),(170,'addName','addNameaddNameaddNameaddName'),(171,'addName','addNameaddNameaddNameaddName'),(172,'addName','addNameaddNameaddNameaddName'),(173,'addName','addNameaddNameaddNameaddName'),(174,'addName','addNameaddNameaddNameaddName'),(175,'addName','addNameaddNameaddNameaddName'),(176,'addName','addNameaddNameaddNameaddName'),(177,'addName','addNameaddNameaddNameaddName'),(178,'addName','addNameaddNameaddNameaddName'),(179,'addName','addNameaddNameaddNameaddName'),(180,'addName','addNameaddNameaddNameaddName'),(181,'addName','addNameaddNameaddNameaddName'),(182,'addName','addNameaddNameaddNameaddName'),(183,'addName','addNameaddNameaddNameaddName'),(184,'addName','addNameaddNameaddNameaddName'),(185,'addName','addNameaddNameaddNameaddName'),(186,'addName','addNameaddNameaddNameaddName'),(187,'addName','addNameaddNameaddNameaddName'),(188,'addName','addNameaddNameaddNameaddName'),(189,'addName','addNameaddNameaddNameaddName'),(190,'addName','addNameaddNameaddNameaddName'),(191,'addName','addNameaddNameaddNameaddName'),(192,'addName','addNameaddNameaddNameaddName'),(193,'addName','addNameaddNameaddNameaddName'),(194,'addName','addNameaddNameaddNameaddName'),(195,'addName','addNameaddNameaddNameaddName'),(196,'addName','addNameaddNameaddNameaddName'),(197,'addName','addNameaddNameaddNameaddName'),(198,'addName','addNameaddNameaddNameaddName'),(199,'addName','addNameaddNameaddNameaddName');

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
