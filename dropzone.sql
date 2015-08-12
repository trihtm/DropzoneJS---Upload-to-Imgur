-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 12, 2015 at 07:59 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `dropzone`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `sanpham`
-- 

CREATE TABLE `sanpham` (
  `idsp` int(11) NOT NULL auto_increment,
  `tensp` varchar(100) collate utf8_unicode_ci NOT NULL,
  `giaban` varchar(100) collate utf8_unicode_ci NOT NULL,
  `imgs` varchar(250) collate utf8_unicode_ci default NULL,
  `img_main` varchar(250) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`idsp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `sanpham`
-- 

INSERT INTO `sanpham` VALUES (1, 'áo thun', '150000', 'http://i.imgur.com/jIB2hgN.png,http://i.imgur.com/8hhlE0e.jpg,', 'http://i.imgur.com/jIB2hgN.png');
