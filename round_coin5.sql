/*
SQLyog Community v13.1.5  (64 bit)
MySQL - 10.4.14-MariaDB : Database - lioncomp
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `round_coin5` */

DROP TABLE IF EXISTS `round_coin5`;

CREATE TABLE `round_coin5` (
  `round_fid` int(11) NOT NULL AUTO_INCREMENT,
  `round_date` varchar(12) NOT NULL,
  `round_num` int(11) NOT NULL,
  `round_time` datetime NOT NULL,
  `round_state` int(11) NOT NULL,
  `round_result_1` varchar(2) NOT NULL,
  `round_result_2` varchar(2) NOT NULL,
  `round_result_3` varchar(2) NOT NULL,
  `round_result_4` varchar(2) NOT NULL,
  `round_result_5` varchar(2) NOT NULL,
  `round_power` varchar(4) NOT NULL,
  `round_normal` varchar(20) NOT NULL,
  `round_hash` varchar(30) NOT NULL,
  PRIMARY KEY (`round_fid`)
) ENGINE=InnoDB AUTO_INCREMENT=167066 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `round_eos5` */

DROP TABLE IF EXISTS `round_eos5`;

CREATE TABLE `round_eos5` (
  `round_fid` int(11) NOT NULL AUTO_INCREMENT,
  `round_date` varchar(12) NOT NULL,
  `round_num` int(11) NOT NULL,
  `round_time` datetime NOT NULL,
  `round_state` int(11) NOT NULL,
  `round_result_1` varchar(2) NOT NULL,
  `round_result_2` varchar(2) NOT NULL,
  `round_result_3` varchar(2) NOT NULL,
  `round_result_4` varchar(2) NOT NULL,
  `round_result_5` varchar(2) NOT NULL,
  `round_power` varchar(4) NOT NULL,
  `round_normal` varchar(20) NOT NULL,
  `round_hash` varchar(30) NOT NULL,
  PRIMARY KEY (`round_fid`)
) ENGINE=InnoDB AUTO_INCREMENT=108338 DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
