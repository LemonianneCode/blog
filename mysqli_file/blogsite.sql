-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 10:38 AM
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
(1, 'Homer', 'Bugarin', 'Lemon', 'Male', '2006-07-19', 'homer', '381854583', 'favorite_color', '$2y$10$ig9tchi7YAd2Hm3g4gCfXO4ULu.tKn.pzbzLY78fmJ9sCchhEiGNG', 'favorite_hobby', '$2y$10$FS6ka/Gqb14axS0XcFpMrOnDLkltVoSOAS.CYVR7SlMH9IteTPW6K', 'favorite_song', '$2y$10$AGKEqIDAWGwdvhlzgZIN2eFr8ok0wmIlOfL7J3cjYaEQx9ZQ4FwMG'),
(2, 'lindsay', 'doinog', 'molino', 'Female', '2006-07-01', 'sayness', '2932651766', 'favorite_color', '$2y$10$VgFEbO9WZW5a429pd1CL1.Y.Ed..JPBxv3rPVyO67XTQTqyrSKQVa', 'favorite_book', '$2y$10$OOqwbCHoQSKefVQGxkcfRO4Y1gEOvjwM4crqKRsZoDvOb3EDk3byS', 'favorite_hobby', '$2y$10$VSMDhQ2R0zWnX53d/kInc.YPKwx4pXpaXpD2TKApQ3TqYz6qpSZd.');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_posts`
--

CREATE TABLE `announcement_posts` (
  `ID` int(11) NOT NULL,
  `CONTENT` text NOT NULL,
  `IMAGE` varchar(255) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_posts`
--

INSERT INTO `announcement_posts` (`ID`, `CONTENT`, `IMAGE`, `CREATED_AT`) VALUES
(1, '𝐏𝐑𝐌𝐒𝐔 𝐀𝐩𝐩𝐫𝐨𝐯𝐞𝐝 𝐀𝐜𝐚𝐝𝐞𝐦𝐢𝐜 𝐂𝐚𝐥𝐞𝐧𝐝𝐚𝐫 𝐀𝐘 𝟐𝟎𝟐𝟔-𝟐𝟎𝟐𝟕\r\nThe University hereby formally approves the Academic Calendar for Academic Year 2026–2027, which outlines the official schedule of academic activities and key institutional milestones. This calendar serves as a comprehensive guide to ensure the orderly and efficient implementation of academic programs and services.\r\nSource: Office of the University Admission and Registration Services \r\n#AcademicCalendar #AY20262027', 'c1edecfe29d62cb96a35c39a62658ec2.jpg', '2026-10-24 08:11:11');

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
(1, 'work hee hee', '7e9a6a3b9948d2ad1b119151be264c4e.png', 1),
(2, 'tite malaki', 'ca744b7d7c6bf928b5573ba9dbcc1c18.jpg', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_info`
--
ALTER TABLE `acc_info`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `announcement_posts`
--
ALTER TABLE `announcement_posts`
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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcement_posts`
--
ALTER TABLE `announcement_posts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vlog_posts`
--
ALTER TABLE `vlog_posts`
  MODIFY `PID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
