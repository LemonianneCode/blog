-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 08, 2026 at 11:33 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

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
-- Table structure for table `vlog_posts`
--

DROP TABLE IF EXISTS `vlog_posts`;
CREATE TABLE IF NOT EXISTS `vlog_posts` (
  `PID` int NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL,
  `IMAGE` varchar(255) DEFAULT NULL,
  `AUTHOR_ID` int DEFAULT NULL,
  PRIMARY KEY (`PID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vlog_posts`
--

INSERT INTO `vlog_posts` (`PID`, `TITLE`, `IMAGE`, `AUTHOR_ID`) VALUES
(4, 'gago ka say', '1b7b60339d0c4fafe042d571df8502a8.png', 36),
(2, 'sandrone sa inazuma', 'fa8b1cc65487e6054c469053a391e699.png', 21),
(8, 'say the grounds', NULL, 23),
(6, 'di ako makapag post ng image na mag malaki aup', NULL, 21),
(7, 'taena mo punta ka dito ng magkaalaman', NULL, 22),
(9, 'tara jack', NULL, 22);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
