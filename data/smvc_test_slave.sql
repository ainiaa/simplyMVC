/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.5.40 : Database - smvc_test_slave
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`smvc_test_slave` /*!40100 DEFAULT CHARACTER SET gbk */;

USE `smvc_test_slave`;

/*Table structure for table `smvc_test` */

DROP TABLE IF EXISTS `smvc_test`;

CREATE TABLE `smvc_test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `desc` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `smvc_test` */

insert  into `smvc_test`(`id`,`name`,`desc`) values (1,'slavea','slaveaslaveaslaveaslaveaslavea'),(2,'slaveb','slavebslavebslavebslavebslaveb'),(3,'slavec','slavecslavecslavecslavecslavec'),(4,'slaved','slavedslavedslavedslavedslaved'),(5,'slavee','slaveeslaveeslaveeslaveeslavee');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
