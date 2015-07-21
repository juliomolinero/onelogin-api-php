-- Create DATABASE
CREATE DATABASE `one_login_api` DEFAULT CHARACTER SET utf8;

-- Set Database
USE `one_login_api`;

/*Table structure for table `applications_users_to_import` */
DROP TABLE IF EXISTS `applications_users_to_import`;
CREATE TABLE `applications_users_to_import` (
  `user_name` varchar(80) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `email` varchar(240) NOT NULL,
  PRIMARY KEY (`user_name`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `ol_admins` */
DROP TABLE IF EXISTS `ol_admins`;
CREATE TABLE `ol_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(120) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(255) NOT NULL,
  `last_login` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Data for the table `ol_admins` PASSWORD is Admin_123!"#$ */
insert  into `ol_admins`(`id`,`user`,`password`,`email`,`last_login`,`active`) values (1,'admin','a69758638642aab8260a0b651bef043e','admin@mail.com',0,1);

/*Table structure for table `ol_admins_logs` */
DROP TABLE IF EXISTS `ol_admins_logs`;
CREATE TABLE `ol_admins_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(120) NOT NULL,
  `page` varchar(255) NOT NULL,
  `params` mediumtext NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date` int(11) NOT NULL,
  `remote_host` varchar(120) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `FK_ol_admins` (`user`),
  CONSTRAINT `FK_ol_admins` FOREIGN KEY (`user`) REFERENCES `ol_admins` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
