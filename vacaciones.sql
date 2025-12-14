-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 08:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flujo_vaca`
--

-- --------------------------------------------------------

--
-- Table structure for table `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `rrhh_id` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias_solicitados` int(11) NOT NULL,
  `motivo` text DEFAULT NULL,
  `estado` enum('pendiente','aprobado_supervisor','rechazado_supervisor','aprobado_rrhh','rechazado_rrhh','finalizado') DEFAULT 'pendiente',
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `motivo_rechazo` text DEFAULT NULL,
  `dias_disponibles` int(11) DEFAULT 30,
  `dias_descontar` int(11) DEFAULT NULL,
  `fecha_aprobacion_supervisor` datetime DEFAULT NULL,
  `fecha_aprobacion_rrhh` datetime DEFAULT NULL,
  `comentarios_supervisor` text DEFAULT NULL,
  `comentarios_rrhh` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vacaciones`
--

INSERT INTO `vacaciones` (`id`, `empleado_id`, `supervisor_id`, `rrhh_id`, `fecha_inicio`, `fecha_fin`, `dias_solicitados`, `motivo`, `estado`, `fecha_solicitud`, `motivo_rechazo`, `dias_disponibles`, `dias_descontar`, `fecha_aprobacion_supervisor`, `fecha_aprobacion_rrhh`, `comentarios_supervisor`, `comentarios_rrhh`) VALUES
(1, 7, 2, 3, '2025-12-16', '2025-12-18', 3, 'fiebre', 'aprobado_rrhh', '2025-12-14 02:06:48', '', 30, 3, '2025-12-14 02:08:27', '2025-12-14 02:09:44', 'chi', 'bien'),
(2, 1, NULL, NULL, '2025-12-19', '2025-12-26', 8, 'ooo', 'pendiente', '2025-12-14 02:14:02', NULL, 30, NULL, NULL, NULL, NULL, NULL),
(3, 1, 2, NULL, '2025-12-16', '2025-12-19', 4, 'Vacaciones familiares', 'aprobado_supervisor', '2025-12-14 02:41:12', '', 30, NULL, '2025-12-14 03:21:50', NULL, '', NULL),
(4, 1, NULL, NULL, '2025-12-16', '2025-12-18', 3, 'owi', 'pendiente', '2025-12-14 03:02:00', NULL, 30, NULL, NULL, NULL, NULL, NULL),
(5, 1, 2, NULL, '2025-01-10', '2025-01-15', 6, 'Vacaciones familiares', 'aprobado_supervisor', '2025-12-14 03:12:26', NULL, 30, NULL, NULL, NULL, NULL, NULL),
(6, 4, 2, 3, '2025-02-01', '2025-02-05', 5, 'Descanso personal', 'aprobado_rrhh', '2025-12-14 03:12:26', NULL, 25, 5, NULL, NULL, NULL, NULL),
(7, 1, 2, NULL, '2025-01-10', '2025-01-15', 6, 'Vacaciones familiares', 'aprobado_supervisor', '2025-12-11 03:20:52', NULL, 30, NULL, NULL, NULL, NULL, NULL),
(8, 4, 2, 3, '2025-02-01', '2025-02-05', 5, 'Descanso personal', 'aprobado_rrhh', '2025-12-09 03:20:52', NULL, 25, 5, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `rrhh_id` (`rrhh_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `vacaciones_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `vacaciones_ibfk_3` FOREIGN KEY (`rrhh_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
