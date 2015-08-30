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

/*Table structure for table `smvc_test` */

DROP TABLE IF EXISTS `smvc_test`;

CREATE TABLE `smvc_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `desc` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;

/*Data for the table `smvc_test` */

insert  into `smvc_test`(`id`,`name`,`desc`) values (1,'a','aaaaaaa'),(2,'b','bbbbbbb'),(3,'c','ccccccc'),(4,'d','ddddddd'),(5,'e','eeeeeee'),(6,'addName','addNameaddNameaddNameaddName'),(7,'addName','addNameaddNameaddNameaddName'),(8,'addName','addNameaddNameaddNameaddName'),(9,'addName','addNameaddNameaddNameaddName'),(10,'addName','addNameaddNameaddNameaddName'),(11,'addName','addNameaddNameaddNameaddName'),(12,'addName','addNameaddNameaddNameaddName'),(13,'addName','addNameaddNameaddNameaddName'),(14,'addName','addNameaddNameaddNameaddName'),(15,'addName','addNameaddNameaddNameaddName'),(16,'addName','addNameaddNameaddNameaddName'),(17,'addName','addNameaddNameaddNameaddName'),(18,'addName','addNameaddNameaddNameaddName'),(19,'addName','addNameaddNameaddNameaddName'),(20,'addName','addNameaddNameaddNameaddName'),(21,'addName','addNameaddNameaddNameaddName'),(22,'addName','addNameaddNameaddNameaddName'),(23,'addName','addNameaddNameaddNameaddName'),(24,'addName','addNameaddNameaddNameaddName'),(25,'addName','addNameaddNameaddNameaddName'),(26,'addName','addNameaddNameaddNameaddName'),(27,'addName','addNameaddNameaddNameaddName'),(28,'addName','addNameaddNameaddNameaddName'),(29,'addName','addNameaddNameaddNameaddName'),(30,'addName','addNameaddNameaddNameaddName'),(31,'addName','addNameaddNameaddNameaddName'),(32,'addName','addNameaddNameaddNameaddName'),(33,'addName','addNameaddNameaddNameaddName'),(34,'addName','addNameaddNameaddNameaddName'),(35,'addName','addNameaddNameaddNameaddName'),(36,'addName','addNameaddNameaddNameaddName'),(37,'addName','addNameaddNameaddNameaddName'),(38,'addName','addNameaddNameaddNameaddName'),(39,'addName','addNameaddNameaddNameaddName'),(40,'addName','addNameaddNameaddNameaddName'),(41,'addName','addNameaddNameaddNameaddName'),(42,'addName','addNameaddNameaddNameaddName'),(43,'addName','addNameaddNameaddNameaddName'),(44,'addName','addNameaddNameaddNameaddName'),(45,'addName','addNameaddNameaddNameaddName'),(46,'addName','addNameaddNameaddNameaddName'),(47,'addName','addNameaddNameaddNameaddName'),(48,'addName','addNameaddNameaddNameaddName'),(49,'addName','addNameaddNameaddNameaddName'),(50,'addName','addNameaddNameaddNameaddName'),(51,'addName','addNameaddNameaddNameaddName'),(52,'addName','addNameaddNameaddNameaddName'),(53,'addName','addNameaddNameaddNameaddName'),(54,'addName','addNameaddNameaddNameaddName'),(55,'addName','addNameaddNameaddNameaddName'),(56,'addName','addNameaddNameaddNameaddName'),(57,'addName','addNameaddNameaddNameaddName'),(58,'addName','addNameaddNameaddNameaddName'),(59,'addName','addNameaddNameaddNameaddName'),(60,'addName','addNameaddNameaddNameaddName'),(61,'addName','addNameaddNameaddNameaddName'),(62,'addName','addNameaddNameaddNameaddName'),(63,'addName','addNameaddNameaddNameaddName'),(64,'addName','addNameaddNameaddNameaddName'),(65,'addName','addNameaddNameaddNameaddName'),(66,'addName','addNameaddNameaddNameaddName'),(67,'addName','addNameaddNameaddNameaddName'),(68,'addName','addNameaddNameaddNameaddName'),(69,'addName','addNameaddNameaddNameaddName'),(70,'addName','addNameaddNameaddNameaddName'),(71,'addName','addNameaddNameaddNameaddName'),(72,'addName','addNameaddNameaddNameaddName'),(73,'addName','addNameaddNameaddNameaddName'),(74,'addName','addNameaddNameaddNameaddName');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
