-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 15, 2012 at 10:04 AM
-- Server version: 5.5.28
-- PHP Version: 5.4.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `tumblr_tag_cloud`
--

-- --------------------------------------------------------

--
-- Table structure for table `cloud_blog`
--

CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `cloud_blog_cache`
--

CREATE TABLE IF NOT EXISTS `blog_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'tag_cloud',
  `data` text,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `mod_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`,`type`,`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Table structure for table `cloud_blog_setting`
--

CREATE TABLE IF NOT EXISTS `blog_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=107 ;
