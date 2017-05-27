/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.7.11-log : Database - wangcai
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`wangcai` /*!40100 DEFAULT CHARACTER SET gbk */;

USE `wangcai`;

/*Table structure for table `tp_menu` */

DROP TABLE IF EXISTS `tp_menu`;

CREATE TABLE `tp_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `menu_name` varchar(50) NOT NULL COMMENT '菜单名称',
  `menu_url` varchar(80) NOT NULL COMMENT '菜单url',
  `pid` int(11) DEFAULT NULL COMMENT '父菜单id',
  `menu_url_ex` varchar(80) DEFAULT NULL COMMENT '菜单url扩展',
  `depth` tinyint(4) DEFAULT '1' COMMENT '深度',
  `path` mediumtext COMMENT 'path',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=gbk;

/*Data for the table `tp_menu` */

LOCK TABLES `tp_menu` WRITE;

insert  into `tp_menu`(`id`,`menu_name`,`menu_url`,`pid`,`menu_url_ex`,`depth`,`path`) values (1,'多乐互动','MarketActive/Activity/index',0,'g=MarketActive&m=Activity&a=index',1,',0,'),(2,'常用模板','MarketActive/Activity/createMarket',1,'MarketActive/Activity/createMarket',2,',0,2,'),(3,'免费工具','*',1,'*',2,',0,3,'),(4,'数据中心','MarketActive/DataCenter/index',1,'MarketActive/DataCenter/index',2,',0,4,'),(5,'多赢积分','Integral/Integral/integralMarketing',0,'Integral/Integral/integralMarketing',2,',0,');

UNLOCK TABLES;

/*Table structure for table `tp_menu_privilege_relation` */

DROP TABLE IF EXISTS `tp_menu_privilege_relation`;

CREATE TABLE `tp_menu_privilege_relation` (
  `mid` int(11) unsigned NOT NULL COMMENT 'tp_menu.id',
  `pid` int(11) unsigned NOT NULL COMMENT 'tp_privilege.id',
  `mpid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  PRIMARY KEY (`mpid`),
  UNIQUE KEY `mid_pid_idx` (`mid`,`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=gbk;

/*Data for the table `tp_menu_privilege_relation` */

LOCK TABLES `tp_menu_privilege_relation` WRITE;

insert  into `tp_menu_privilege_relation`(`mid`,`pid`,`mpid`) values (1,10,1),(2,10,2),(3,10,4),(4,10,5),(5,10,6);

UNLOCK TABLES;

/*Table structure for table `tp_operation` */

DROP TABLE IF EXISTS `tp_operation`;

CREATE TABLE `tp_operation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `operation_name` varchar(50) NOT NULL COMMENT '操作名称',
  `operation_code` varchar(20) NOT NULL COMMENT '操作码',
  `operation_url` varchar(80) NOT NULL COMMENT '操作url',
  `pid` int(11) DEFAULT NULL COMMENT '父操作id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_operation` */

LOCK TABLES `tp_operation` WRITE;

UNLOCK TABLES;

/*Table structure for table `tp_operation_privilege_relation` */

DROP TABLE IF EXISTS `tp_operation_privilege_relation`;

CREATE TABLE `tp_operation_privilege_relation` (
  `oid` int(11) NOT NULL COMMENT 'tp_operation.id',
  `pid` int(11) NOT NULL COMMENT 'tp_privilege.id',
  PRIMARY KEY (`oid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_operation_privilege_relation` */

LOCK TABLES `tp_operation_privilege_relation` WRITE;

UNLOCK TABLES;

/*Table structure for table `tp_privilege` */

DROP TABLE IF EXISTS `tp_privilege`;

CREATE TABLE `tp_privilege` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `priv_name` varchar(30) DEFAULT NULL COMMENT '权限名称',
  `priv_desc` varchar(80) DEFAULT NULL COMMENT '权限描述',
  `priv_type` enum('MENU','OPERATION','FILE','RESOURCE','ELEMENT') NOT NULL COMMENT '权限类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=gbk;

/*Data for the table `tp_privilege` */

LOCK TABLES `tp_privilege` WRITE;

insert  into `tp_privilege`(`id`,`priv_name`,`priv_desc`,`priv_type`) values (1,'add_group','添加分组','OPERATION'),(2,'edit_group','修改分组','OPERATION'),(3,'delete_group','删除分组','OPERATION'),(4,'add_user','添加用户','OPERATION'),(5,'edit_user','修改用户','OPERATION'),(6,'delete_user','删除用户','OPERATION'),(7,'add_menu','添加菜单','MENU'),(8,'edit_menu','修改菜单','MENU'),(9,'delete_menu','删除菜单','MENU'),(10,'access_menu','访问菜单','MENU'),(11,'show_menu','展示菜单','MENU'),(12,'add_resource','添加资源','RESOURCE'),(13,'edit_resource','编辑资源','RESOURCE'),(14,'delete_resource','删除资源','RESOURCE'),(15,'access_resource','访问资源','RESOURCE'),(16,'download_resource','下载资源','RESOURCE');

UNLOCK TABLES;

/*Table structure for table `tp_public_resource` */

DROP TABLE IF EXISTS `tp_public_resource`;

CREATE TABLE `tp_public_resource` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `resource_name` varchar(50) NOT NULL COMMENT '资源名称',
  `resource_url` varchar(80) NOT NULL COMMENT '资源url',
  `resource_type` varchar(20) DEFAULT 'menu' COMMENT '资源类型 1:menu 2:file',
  `pid` int(11) DEFAULT '0' COMMENT '父资源id',
  `resource_url_ex` varchar(80) DEFAULT NULL COMMENT '资源url扩展',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=gbk;

/*Data for the table `tp_public_resource` */

LOCK TABLES `tp_public_resource` WRITE;

insert  into `tp_public_resource`(`id`,`resource_name`,`resource_url`,`resource_type`,`pid`,`resource_url_ex`) values (1,'首页','Home/Index/index','menu',0,'Home/Index/index');

UNLOCK TABLES;

/*Table structure for table `tp_role` */

DROP TABLE IF EXISTS `tp_role`;

CREATE TABLE `tp_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `role_name` varchar(30) NOT NULL COMMENT '角色名称',
  `role_desc` varchar(255) NOT NULL COMMENT '角色描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=gbk;

/*Data for the table `tp_role` */

LOCK TABLES `tp_role` WRITE;

insert  into `tp_role`(`id`,`role_name`,`role_desc`) values (1,'超级管理员','全权限用户（卡券使用审核权限除外）'),(2,'卡券管理员','可以创建电子券并发布到电子券交易大厅，可以在电子券交易大厅采购电子券'),(3,'门店管理员','可以使用门店管理、门店导航、可以申请终端'),(4,'渠道管理员','可以新增或停用渠道，可以使用微信助手、微博助手、直达号助手、炫码'),(5,'营销活动管理员','可以创建各类营销活动并发布到渠道，使用微官网、炫码、会员管理、数据中心'),(6,' O2O电商管理员','可以使用O2O电商'),(7,'条码支付管理员','可以使用条码支付'),(8,'条码支付查询员','可查询条码支付权益,但可查询的门店的需在“条码支付”模块设置'),(9,'旺分销管理员','可以使用旺分销中的所有功能。'),(10,' 旺分销-提成管理员','可以设置商品提成比例、查看提成明细、下载提成报表并发放提成。'),(11,'旺分销-审核管理员',' 可以在旺分销中新增、审核、停用经销商及销售员。'),(12,'旺分销-录入管理员','可以在旺分销中新增经销商及销售员'),(13,'卡券审核员','仅可审核卡券，无其他权限'),(14,'天猫卡券客服','天猫卡券客服');

UNLOCK TABLES;

/*Table structure for table `tp_role_menu_privilege_relation` */

DROP TABLE IF EXISTS `tp_role_menu_privilege_relation`;

CREATE TABLE `tp_role_menu_privilege_relation` (
  `rid` int(11) DEFAULT NULL COMMENT 'tp_role.id',
  `mid` int(11) DEFAULT NULL COMMENT 'tp_menu.id',
  `pid` int(11) DEFAULT NULL COMMENT 'tp_privilege.id',
  `mpid` int(11) DEFAULT NULL COMMENT 'tp_menu_privilege_relation.id',
  KEY `mid_pid_idx` (`mid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_role_menu_privilege_relation` */

LOCK TABLES `tp_role_menu_privilege_relation` WRITE;

insert  into `tp_role_menu_privilege_relation`(`rid`,`mid`,`pid`,`mpid`) values (1,1,1,NULL),(1,1,10,NULL);

UNLOCK TABLES;

/*Table structure for table `tp_role_privilege_relation` */

DROP TABLE IF EXISTS `tp_role_privilege_relation`;

CREATE TABLE `tp_role_privilege_relation` (
  `rid` int(11) NOT NULL COMMENT 'tp_role.id',
  `pid` int(11) NOT NULL COMMENT 'tp_privilege.id',
  PRIMARY KEY (`rid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_role_privilege_relation` */

LOCK TABLES `tp_role_privilege_relation` WRITE;

UNLOCK TABLES;

/*Table structure for table `tp_user` */

DROP TABLE IF EXISTS `tp_user`;

CREATE TABLE `tp_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) unsigned NOT NULL COMMENT 'tuser_info.user_id',
  `user_name` varchar(50) DEFAULT NULL COMMENT 'tuser_info.user_name',
  `is_super_admin` tinyint(1) DEFAULT '0' COMMENT '是否为超级管理员',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=gbk;

/*Data for the table `tp_user` */

LOCK TABLES `tp_user` WRITE;

insert  into `tp_user`(`id`,`user_id`,`user_name`,`is_super_admin`) values (1,10820,'chenglq',0),(2,2347,'91',1);

UNLOCK TABLES;

/*Table structure for table `tp_user_group_relation` */

DROP TABLE IF EXISTS `tp_user_group_relation`;

CREATE TABLE `tp_user_group_relation` (
  `uid` int(11) NOT NULL COMMENT 'tp_user.id',
  `gid` int(11) NOT NULL COMMENT 'tp_usergroup.id',
  PRIMARY KEY (`uid`,`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_user_group_relation` */

LOCK TABLES `tp_user_group_relation` WRITE;

insert  into `tp_user_group_relation`(`uid`,`gid`) values (95,2);

UNLOCK TABLES;

/*Table structure for table `tp_user_role_relation` */

DROP TABLE IF EXISTS `tp_user_role_relation`;

CREATE TABLE `tp_user_role_relation` (
  `uid` int(11) NOT NULL COMMENT 'tp_user.id',
  `rid` int(11) NOT NULL COMMENT 'tp_role.id',
  PRIMARY KEY (`uid`,`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_user_role_relation` */

LOCK TABLES `tp_user_role_relation` WRITE;

insert  into `tp_user_role_relation`(`uid`,`rid`) values (95,3),(10820,1);

UNLOCK TABLES;

/*Table structure for table `tp_usergroup` */

DROP TABLE IF EXISTS `tp_usergroup`;

CREATE TABLE `tp_usergroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_name` varchar(45) NOT NULL COMMENT '用户组名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父用户组id',
  `desc` varchar(200) DEFAULT NULL COMMENT '描述',
  `path` mediumtext COMMENT '父类path',
  `depth` tinyint(4) DEFAULT '1' COMMENT '深度',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=gbk;

/*Data for the table `tp_usergroup` */

LOCK TABLES `tp_usergroup` WRITE;

insert  into `tp_usergroup`(`id`,`group_name`,`pid`,`desc`,`path`,`depth`) values (1,'super_admin',0,NULL,NULL,1),(2,'admin',1,NULL,NULL,1),(3,'paied',0,NULL,NULL,1),(4,'register',0,NULL,NULL,1),(5,'guest',0,NULL,NULL,1);

UNLOCK TABLES;

/*Table structure for table `tp_usergroup_role_relation` */

DROP TABLE IF EXISTS `tp_usergroup_role_relation`;

CREATE TABLE `tp_usergroup_role_relation` (
  `gid` int(11) NOT NULL COMMENT 'tp_usergroup.id',
  `rid` int(11) NOT NULL COMMENT 'tp_role.id',
  PRIMARY KEY (`gid`,`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=gbk;

/*Data for the table `tp_usergroup_role_relation` */

LOCK TABLES `tp_usergroup_role_relation` WRITE;

insert  into `tp_usergroup_role_relation`(`gid`,`rid`) values (2,1),(2,2),(2,3);

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
