-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1:3306
-- 生成日期： 2021-12-21 10:44:36
-- 服务器版本： 10.4.13-MariaDB
-- PHP 版本： 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `filecloud`
--

DELIMITER $$
--
-- 函数
--
DROP FUNCTION IF EXISTS `get_child_list`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `get_child_list` (`in_id` VARCHAR(10)) RETURNS VARCHAR(1000) CHARSET utf8 begin 
 declare ids varchar(1000) default ''; 
 declare tempids varchar(1000); 
 
 set tempids = in_id; 
 while tempids is not null do 
  set ids = CONCAT_WS(',',ids,tempids); 
  select GROUP_CONCAT(FID) into tempids from file where FIND_IN_SET(Parent,tempids)>0;  
 end while; 
 return ids; 
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

DROP TABLE IF EXISTS `file`;
CREATE TABLE IF NOT EXISTS `file` (
  `FID` int(10) NOT NULL AUTO_INCREMENT,
  `FileMD5` varchar(32) DEFAULT NULL COMMENT '文件MD5',
  `FileName` varchar(60) NOT NULL COMMENT '文件名',
  `FileSize` bigint(20) UNSIGNED NOT NULL COMMENT '文件大小',
  `Parent` int(10) UNSIGNED DEFAULT NULL COMMENT '父文件夹ID',
  `UID` int(10) NOT NULL,
  PRIMARY KEY (`FID`),
  KEY `Parent` (`Parent`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `file`
--

INSERT INTO `file` (`FID`, `FileMD5`, `FileName`, `FileSize`, `Parent`, `UID`) VALUES
(1, '', 'ROOT', 0, NULL, 0);

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `UID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `UName` varchar(15) NOT NULL COMMENT '用户名',
  `Password` varchar(32) NOT NULL COMMENT '密码',
  `RootPath` int(10) UNSIGNED DEFAULT NULL COMMENT '根目录文件ID',
  PRIMARY KEY (`UID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='存储用户信息';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
