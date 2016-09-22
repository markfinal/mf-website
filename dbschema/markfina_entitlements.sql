-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 22, 2016 at 11:57 AM
-- Server version: 5.7.15-0ubuntu0.16.04.1
-- PHP Version: 7.0.8-0ubuntu0.16.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `markfina_entitlements`
--

-- --------------------------------------------------------

--
-- Table structure for table `AccessToken`
--

CREATE TABLE `AccessToken` (
  `id` int(11) NOT NULL,
  `token` char(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userhost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Host`
--

CREATE TABLE `Host` (
  `id` int(11) NOT NULL,
  `MAC` varchar(64) NOT NULL,
  `RevokeReason` varchar(4096) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `License`
--

CREATE TABLE `License` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `duration_days` int(11) NOT NULL,
  `product` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LicenseSession`
--

CREATE TABLE `LicenseSession` (
  `id` int(11) NOT NULL,
  `license` int(11) NOT NULL,
  `session_token` char(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LicenseType`
--

CREATE TABLE `LicenseType` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `duration_days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `LicenseType`
--

INSERT INTO `LicenseType` (`id`, `name`, `duration_days`) VALUES
(1, 'Trial', 30),
(2, 'Purchased', 366),
(3, 'Development', 8192);

-- --------------------------------------------------------

--
-- Table structure for table `Log`
--

CREATE TABLE `Log` (
  `id` int(11) NOT NULL,
  `token` varchar(32) NOT NULL,
  `message` varchar(4096) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` int(11) DEFAULT NULL,
  `host` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `certificate` blob NOT NULL,
  `maxmachines` int(11) NOT NULL DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `UserHostMachine`
--

CREATE TABLE `UserHostMachine` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `host` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `UserHostMachineRequest`
--

CREATE TABLE `UserHostMachineRequest` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `MAC` varchar(64) NOT NULL,
  `url` varchar(2048) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AccessToken`
--
ALTER TABLE `AccessToken`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Host`
--
ALTER TABLE `Host`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `MAC` (`MAC`);

--
-- Indexes for table `License`
--
ALTER TABLE `License`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LicenseSession`
--
ALTER TABLE `LicenseSession`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LicenseType`
--
ALTER TABLE `LicenseType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Log`
--
ALTER TABLE `Log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `UserHostMachine`
--
ALTER TABLE `UserHostMachine`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userhostpair` (`user`,`host`) USING BTREE;

--
-- Indexes for table `UserHostMachineRequest`
--
ALTER TABLE `UserHostMachineRequest`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AccessToken`
--
ALTER TABLE `AccessToken`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Host`
--
ALTER TABLE `Host`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `License`
--
ALTER TABLE `License`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `LicenseSession`
--
ALTER TABLE `LicenseSession`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `LicenseType`
--
ALTER TABLE `LicenseType`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Log`
--
ALTER TABLE `Log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `UserHostMachine`
--
ALTER TABLE `UserHostMachine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `UserHostMachineRequest`
--
ALTER TABLE `UserHostMachineRequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
