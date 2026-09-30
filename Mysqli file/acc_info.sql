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
-- Table structure for table `acc_info`
--

DROP TABLE IF EXISTS `acc_info`;
CREATE TABLE IF NOT EXISTS `acc_info` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FNAME` varchar(100) NOT NULL,
  `MNAME` varchar(100) NOT NULL,
  `LNAME` varchar(100) NOT NULL,
  `GENDER` varchar(100) NOT NULL,
  `BDAY` date NOT NULL,
  `USERNAME` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `PASSWORD` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `acc_info`
--

INSERT INTO `acc_info` (`ID`, `FNAME`, `MNAME`, `LNAME`, `GENDER`, `BDAY`, `USERNAME`, `PASSWORD`) VALUES
(36, 'kyle', 'zedrick', 'versosa', 'male', '2006-03-10', 'kyle', '3445223357'),
(23, 'Jackieline', 'Eclarinal', 'Jimenez', 'Female', '2005-11-25', 'jack', '437831520'),
(22, 'Lindsay', 'Doinog', 'Molino', 'Female', '2006-07-01', 'lindsay', '240115535'),
(21, 'Homer', 'Bugarin', 'Lemon', 'Male', '2006-07-19', 'homer', '2658666788'),
(25, 'Justine', 'Monsalud', 'Nicolas', 'Male', '2005-11-19', 'justine', '92154090'),
(35, 'christian', 'kubatbat', 'monsalud', 'Male', '2005-12-31', 'ichan', '461932074');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
