-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 07:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blogsite`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_info`
--

CREATE TABLE `acc_info` (
  `ID` int(11) NOT NULL,
  `FNAME` varchar(255) NOT NULL,
  `MNAME` varchar(255) NOT NULL,
  `LNAME` varchar(255) NOT NULL,
  `GENDER` text NOT NULL,
  `BDAY` date NOT NULL,
  `USERNAME` varchar(100) NOT NULL,
  `PASSWORD` varchar(100) NOT NULL,
  `SECURITY_QUESTION` varchar(50) NOT NULL,
  `SECURITY_ANSWER` varchar(255) NOT NULL,
  `SECURITY_QUESTION_2` varchar(50) NOT NULL,
  `SECURITY_ANSWER_2` varchar(255) NOT NULL,
  `SECURITY_QUESTION_3` varchar(50) NOT NULL,
  `SECURITY_ANSWER_3` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_info`
--

INSERT INTO `acc_info` (`ID`, `FNAME`, `MNAME`, `LNAME`, `GENDER`, `BDAY`, `USERNAME`, `PASSWORD`, `SECURITY_QUESTION`, `SECURITY_ANSWER`, `SECURITY_QUESTION_2`, `SECURITY_ANSWER_2`, `SECURITY_QUESTION_3`, `SECURITY_ANSWER_3`) VALUES
(1, 'Homer', 'Bugarin', 'Lemon', 'Male', '2006-07-19', 'homer', '381854583', 'favorite_color', '$2y$10$ig9tchi7YAd2Hm3g4gCfXO4ULu.tKn.pzbzLY78fmJ9sCchhEiGNG', 'favorite_hobby', '$2y$10$FS6ka/Gqb14axS0XcFpMrOnDLkltVoSOAS.CYVR7SlMH9IteTPW6K', 'favorite_song', '$2y$10$AGKEqIDAWGwdvhlzgZIN2eFr8ok0wmIlOfL7J3cjYaEQx9ZQ4FwMG');

-- --------------------------------------------------------

--
-- Table structure for table `vlog_posts`
--

CREATE TABLE `vlog_posts` (
  `PID` int(11) NOT NULL,
  `TITLE` varchar(255) NOT NULL,
  `IMAGE` varchar(255) DEFAULT NULL,
  `AUTHOR_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vlog_posts`
--

INSERT INTO `vlog_posts` (`PID`, `TITLE`, `IMAGE`, `AUTHOR_ID`) VALUES
(1, 'work hee hee', '7e9a6a3b9948d2ad1b119151be264c4e.png', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_info`
--
ALTER TABLE `acc_info`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vlog_posts`
--
ALTER TABLE `vlog_posts`
  ADD PRIMARY KEY (`PID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_info`
--
ALTER TABLE `acc_info`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vlog_posts`
--
ALTER TABLE `vlog_posts`
  MODIFY `PID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
