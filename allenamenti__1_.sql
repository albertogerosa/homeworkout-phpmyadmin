-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 22, 2026 alle 06:26
-- Versione del server: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versione PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `allenamenti (1)`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `allenamenti_giornalieri`
--

CREATE TABLE `allenamenti_giornalieri` (
  `id` int(11) NOT NULL,
  `piano_id` int(11) DEFAULT NULL,
  `data_all` date DEFAULT NULL,
  `completato` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `allenamenti_giornalieri`
--

INSERT INTO `allenamenti_giornalieri` (`id`, `piano_id`, `data_all`, `completato`) VALUES
(1, 1, '2026-02-09', 1),
(2, 1, '2026-02-10', 0),
(3, 2, '2026-05-11', 1),
(4, 2, '2026-05-12', 0),
(5, 3, '2026-05-14', 1),
(6, 3, '2026-05-15', 0),
(7, 4, '2026-04-15', 1),
(8, 5, '2026-04-16', 0),
(9, 5, '2026-04-17', 1),
(10, 2, '2026-05-13', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `amicizie`
--

CREATE TABLE `amicizie` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `amico_id` int(11) DEFAULT NULL,
  `stato` enum('pending','accepted') DEFAULT 'pending',
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `amicizie`
--

INSERT INTO `amicizie` (`id`, `utente_id`, `amico_id`, `stato`, `tenant_id`) VALUES
(1, 12, 5, 'pending', 1),
(2, 14, 11, 'pending', 1),
(3, 14, 4, 'pending', 1),
(4, 14, 8, 'pending', 1),
(5, 15, 5, 'pending', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `classifiche`
--

CREATE TABLE `classifiche` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `punti` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `esercizi`
--

CREATE TABLE `esercizi` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `gruppo_muscolare` varchar(50) DEFAULT NULL,
  `livello` enum('facile','medio','difficile') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `esercizi`
--

INSERT INTO `esercizi` (`id`, `nome`, `descrizione`, `gruppo_muscolare`, `livello`) VALUES
(1, 'Push-up a muro', 'Spinta contro il muro, ideale per principianti', 'petto', 'facile'),
(2, 'Squat', 'Squat a corpo libero', 'gambe', 'facile'),
(3, 'Plancia', 'Plancia frontale, mantenere posizione', 'core', 'facile'),
(4, 'Flessioni', 'Flessioni standard da terra', 'petto', 'medio'),
(5, 'Affondi', 'Affondi alternati', 'gambe', 'facile'),
(6, 'Mountain climber', 'Esercizio cardio a corpo libero', 'cardio', 'medio'),
(7, 'Salti', 'Salti sul posto (jumping jacks alternativi)', 'cardio', 'facile'),
(8, 'Flessioni archer', 'Varianti avanzate di flessioni', 'petto', 'difficile'),
(9, 'Pistol squat', 'Squat su una gamba', 'gambe', 'difficile'),
(10, 'Handstand hold', 'Sostegno in verticale (handstand)', 'spalle', 'difficile'),
(11, 'Flessioni planche', 'Esercizio avanzato per il core e spalle', 'spalle', 'difficile'),
(12, 'Human flag', 'Tenuta bandiera umana', 'core', 'difficile'),
(13, 'Muscle up', 'Trazioni + dip combinati', 'schiena', 'difficile'),
(14, 'L-sit', 'Tenuta L-sit', 'core', 'medio');

-- --------------------------------------------------------

--
-- Struttura della tabella `esercizi_allenamento`
--

CREATE TABLE `esercizi_allenamento` (
  `id` int(11) NOT NULL,
  `allenamento_giornaliero_id` int(11) DEFAULT NULL,
  `esercizio_id` int(11) DEFAULT NULL,
  `ripetizioni` int(11) DEFAULT NULL,
  `serie` int(11) DEFAULT NULL,
  `durata` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `esercizi_piano`
--

CREATE TABLE `esercizi_piano` (
  `id` int(11) NOT NULL,
  `piano_id` int(11) NOT NULL,
  `nome_esercizio` varchar(255) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `ripetizioni` int(11) DEFAULT NULL,
  `serie` int(11) DEFAULT NULL,
  `giorno` int(11) DEFAULT NULL,
  `difficolta_moltiplicatore` float DEFAULT 1,
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `esercizi_piano`
--

INSERT INTO `esercizi_piano` (`id`, `piano_id`, `nome_esercizio`, `descrizione`, `ripetizioni`, `serie`, `giorno`, `difficolta_moltiplicatore`, `tenant_id`) VALUES
(1, 1, 'Push-up a muro', 'A muro per principianti', 10, 3, 1, 1, NULL),
(2, 1, 'Squat', 'Squat a corpo libero', 15, 3, 2, 1, NULL),
(3, 1, 'Plancia', 'Plancia frontale', 20, 2, 3, 1, NULL),
(4, 1, 'Flessioni', 'Flessioni da terra', 5, 3, 4, 1, NULL),
(5, 1, 'Affondi', 'Affondi alternati', 10, 3, 5, 1, NULL),
(6, 1, 'Mountain climber', 'Mountain climber', 15, 2, 6, 1, NULL),
(7, 1, 'Salti', 'Salti sul posto', 20, 2, 7, 1, NULL),
(8, 1, 'Push-up a muro', 'A muro per principianti', 10, 3, 8, 1.15, NULL),
(9, 1, 'Squat', 'Squat a corpo libero', 15, 3, 9, 1.15, NULL),
(10, 1, 'Plancia', 'Plancia frontale', 20, 2, 10, 1.15, NULL),
(11, 1, 'Flessioni', 'Flessioni da terra', 5, 3, 11, 1.15, NULL),
(12, 1, 'Affondi', 'Affondi alternati', 10, 3, 12, 1.15, NULL),
(13, 1, 'Mountain climber', 'Mountain climber', 15, 2, 13, 1.15, NULL),
(14, 1, 'Salti', 'Salti sul posto', 20, 2, 14, 1.15, NULL),
(15, 1, 'Push-up a muro', 'A muro per principianti', 10, 3, 15, 1.3, NULL),
(16, 1, 'Squat', 'Squat a corpo libero', 15, 3, 16, 1.3, NULL),
(17, 1, 'Plancia', 'Plancia frontale', 20, 2, 17, 1.3, NULL),
(18, 1, 'Flessioni', 'Flessioni da terra', 5, 3, 18, 1.3, NULL),
(19, 1, 'Affondi', 'Affondi alternati', 10, 3, 19, 1.3, NULL),
(20, 1, 'Mountain climber', 'Mountain climber', 15, 2, 20, 1.3, NULL),
(21, 1, 'Salti', 'Salti sul posto', 20, 2, 21, 1.3, NULL),
(22, 1, 'Push-up a muro', 'A muro per principianti', 10, 3, 22, 1.45, NULL),
(23, 1, 'Squat', 'Squat a corpo libero', 15, 3, 23, 1.45, NULL),
(24, 1, 'Plancia', 'Plancia frontale', 20, 2, 24, 1.45, NULL),
(25, 1, 'Flessioni', 'Flessioni da terra', 5, 3, 25, 1.45, NULL),
(26, 1, 'Affondi', 'Affondi alternati', 10, 3, 26, 1.45, NULL),
(27, 1, 'Mountain climber', 'Mountain climber', 15, 2, 27, 1.45, NULL),
(28, 1, 'Salti', 'Salti sul posto', 20, 2, 28, 1.45, NULL),
(29, 2, 'Push-up a muro', 'A muro per principianti', 10, 3, 1, 1, 1),
(30, 2, 'Squat', 'Squat a corpo libero', 15, 3, 2, 1, 1),
(31, 2, 'Plancia', 'Plancia frontale', 20, 2, 3, 1, 1),
(32, 2, 'Flessioni', 'Flessioni da terra', 5, 3, 4, 1, 1),
(33, 2, 'Affondi', 'Affondi alternati', 10, 3, 5, 1, 1),
(34, 2, 'Mountain climber', 'Mountain climber', 15, 2, 6, 1, 1),
(35, 2, 'Salti', 'Salti sul posto', 20, 2, 7, 1, 1),
(36, 2, 'Push-up a muro', 'A muro per principianti', 10, 3, 8, 1.15, 1),
(37, 2, 'Squat', 'Squat a corpo libero', 15, 3, 9, 1.15, 1),
(38, 2, 'Plancia', 'Plancia frontale', 20, 2, 10, 1.15, 1),
(39, 2, 'Flessioni', 'Flessioni da terra', 5, 3, 11, 1.15, 1),
(40, 2, 'Affondi', 'Affondi alternati', 10, 3, 12, 1.15, 1),
(41, 2, 'Mountain climber', 'Mountain climber', 15, 2, 13, 1.15, 1),
(42, 2, 'Salti', 'Salti sul posto', 20, 2, 14, 1.15, 1),
(43, 2, 'Push-up a muro', 'A muro per principianti', 10, 3, 15, 1.3, 1),
(44, 2, 'Squat', 'Squat a corpo libero', 15, 3, 16, 1.3, 1),
(45, 2, 'Plancia', 'Plancia frontale', 20, 2, 17, 1.3, 1),
(46, 2, 'Flessioni', 'Flessioni da terra', 5, 3, 18, 1.3, 1),
(47, 2, 'Affondi', 'Affondi alternati', 10, 3, 19, 1.3, 1),
(48, 2, 'Mountain climber', 'Mountain climber', 15, 2, 20, 1.3, 1),
(49, 2, 'Salti', 'Salti sul posto', 20, 2, 21, 1.3, 1),
(50, 2, 'Push-up a muro', 'A muro per principianti', 10, 3, 22, 1.45, 1),
(51, 2, 'Squat', 'Squat a corpo libero', 15, 3, 23, 1.45, 1),
(52, 2, 'Plancia', 'Plancia frontale', 20, 2, 24, 1.45, 1),
(53, 2, 'Flessioni', 'Flessioni da terra', 5, 3, 25, 1.45, 1),
(54, 2, 'Affondi', 'Affondi alternati', 10, 3, 26, 1.45, 1),
(55, 2, 'Mountain climber', 'Mountain climber', 15, 2, 27, 1.45, 1),
(56, 2, 'Salti', 'Salti sul posto', 20, 2, 28, 1.45, 1),
(57, 3, 'Flessioni archer', 'Flessioni archer', 10, 4, 1, 1, 1),
(58, 3, 'Pistol squat', 'Squat pistol completo', 10, 4, 2, 1, 1),
(59, 3, 'Handstand hold', 'Handstand hold', 30, 3, 3, 1, 1),
(60, 3, 'Flessioni planche', 'Flessioni planche', 8, 4, 4, 1, 1),
(61, 3, 'Human flag', 'Human flag hold', 15, 3, 5, 1, 1),
(62, 3, 'Muscle up', 'Muscle up', 5, 3, 6, 1, 1),
(63, 3, 'L-sit', 'L-sit hold', 30, 3, 7, 1, 1),
(64, 3, 'Flessioni archer', 'Flessioni archer', 10, 4, 8, 1.15, 1),
(65, 3, 'Pistol squat', 'Squat pistol completo', 10, 4, 9, 1.15, 1),
(66, 3, 'Handstand hold', 'Handstand hold', 30, 3, 10, 1.15, 1),
(67, 3, 'Flessioni planche', 'Flessioni planche', 8, 4, 11, 1.15, 1),
(68, 3, 'Human flag', 'Human flag hold', 15, 3, 12, 1.15, 1),
(69, 3, 'Muscle up', 'Muscle up', 5, 3, 13, 1.15, 1),
(70, 3, 'L-sit', 'L-sit hold', 30, 3, 14, 1.15, 1),
(71, 3, 'Flessioni archer', 'Flessioni archer', 10, 4, 15, 1.3, 1),
(72, 3, 'Pistol squat', 'Squat pistol completo', 10, 4, 16, 1.3, 1),
(73, 3, 'Handstand hold', 'Handstand hold', 30, 3, 17, 1.3, 1),
(74, 3, 'Flessioni planche', 'Flessioni planche', 8, 4, 18, 1.3, 1),
(75, 3, 'Human flag', 'Human flag hold', 15, 3, 19, 1.3, 1),
(76, 3, 'Muscle up', 'Muscle up', 5, 3, 20, 1.3, 1),
(77, 3, 'L-sit', 'L-sit hold', 30, 3, 21, 1.3, 1),
(78, 3, 'Flessioni archer', 'Flessioni archer', 10, 4, 22, 1.45, 1),
(79, 3, 'Pistol squat', 'Squat pistol completo', 10, 4, 23, 1.45, 1),
(80, 3, 'Handstand hold', 'Handstand hold', 30, 3, 24, 1.45, 1),
(81, 3, 'Flessioni planche', 'Flessioni planche', 8, 4, 25, 1.45, 1),
(82, 3, 'Human flag', 'Human flag hold', 15, 3, 26, 1.45, 1),
(83, 3, 'Muscle up', 'Muscle up', 5, 3, 27, 1.45, 1),
(84, 3, 'L-sit', 'L-sit hold', 30, 3, 28, 1.45, 1),
(85, 4, 'Flessioni archer', 'Flessioni archer', 10, 4, 1, 1, 1),
(86, 4, 'Pistol squat', 'Squat pistol completo', 10, 4, 2, 1, 1),
(87, 4, 'Handstand hold', 'Handstand hold', 30, 3, 3, 1, 1),
(88, 4, 'Flessioni planche', 'Flessioni planche', 8, 4, 4, 1, 1),
(89, 4, 'Human flag', 'Human flag hold', 15, 3, 5, 1, 1),
(90, 4, 'Muscle up', 'Muscle up', 5, 3, 6, 1, 1),
(91, 4, 'L-sit', 'L-sit hold', 30, 3, 7, 1, 1),
(92, 4, 'Flessioni archer', 'Flessioni archer', 10, 4, 8, 1.15, 1),
(93, 4, 'Pistol squat', 'Squat pistol completo', 10, 4, 9, 1.15, 1),
(94, 4, 'Handstand hold', 'Handstand hold', 30, 3, 10, 1.15, 1),
(95, 4, 'Flessioni planche', 'Flessioni planche', 8, 4, 11, 1.15, 1),
(96, 4, 'Human flag', 'Human flag hold', 15, 3, 12, 1.15, 1),
(97, 4, 'Muscle up', 'Muscle up', 5, 3, 13, 1.15, 1),
(98, 4, 'L-sit', 'L-sit hold', 30, 3, 14, 1.15, 1),
(99, 4, 'Flessioni archer', 'Flessioni archer', 10, 4, 15, 1.3, 1),
(100, 4, 'Pistol squat', 'Squat pistol completo', 10, 4, 16, 1.3, 1),
(101, 4, 'Handstand hold', 'Handstand hold', 30, 3, 17, 1.3, 1),
(102, 4, 'Flessioni planche', 'Flessioni planche', 8, 4, 18, 1.3, 1),
(103, 4, 'Human flag', 'Human flag hold', 15, 3, 19, 1.3, 1),
(104, 4, 'Muscle up', 'Muscle up', 5, 3, 20, 1.3, 1),
(105, 4, 'L-sit', 'L-sit hold', 30, 3, 21, 1.3, 1),
(106, 4, 'Flessioni archer', 'Flessioni archer', 10, 4, 22, 1.45, 1),
(107, 4, 'Pistol squat', 'Squat pistol completo', 10, 4, 23, 1.45, 1),
(108, 4, 'Handstand hold', 'Handstand hold', 30, 3, 24, 1.45, 1),
(109, 4, 'Flessioni planche', 'Flessioni planche', 8, 4, 25, 1.45, 1),
(110, 4, 'Human flag', 'Human flag hold', 15, 3, 26, 1.45, 1),
(111, 4, 'Muscle up', 'Muscle up', 5, 3, 27, 1.45, 1),
(112, 4, 'L-sit', 'L-sit hold', 30, 3, 28, 1.45, 1),
(113, 5, 'Flessioni archer', 'Flessioni archer', 10, 4, 1, 1, 1),
(114, 5, 'Pistol squat', 'Squat pistol completo', 10, 4, 2, 1, 1),
(115, 5, 'Handstand hold', 'Handstand hold', 30, 3, 3, 1, 1),
(116, 5, 'Flessioni planche', 'Flessioni planche', 8, 4, 4, 1, 1),
(117, 5, 'Human flag', 'Human flag hold', 15, 3, 5, 1, 1),
(118, 5, 'Muscle up', 'Muscle up', 5, 3, 6, 1, 1),
(119, 5, 'L-sit', 'L-sit hold', 30, 3, 7, 1, 1),
(120, 5, 'Flessioni archer', 'Flessioni archer', 10, 4, 8, 1.15, 1),
(121, 5, 'Pistol squat', 'Squat pistol completo', 10, 4, 9, 1.15, 1),
(122, 5, 'Handstand hold', 'Handstand hold', 30, 3, 10, 1.15, 1),
(123, 5, 'Flessioni planche', 'Flessioni planche', 8, 4, 11, 1.15, 1),
(124, 5, 'Human flag', 'Human flag hold', 15, 3, 12, 1.15, 1),
(125, 5, 'Muscle up', 'Muscle up', 5, 3, 13, 1.15, 1),
(126, 5, 'L-sit', 'L-sit hold', 30, 3, 14, 1.15, 1),
(127, 5, 'Flessioni archer', 'Flessioni archer', 10, 4, 15, 1.3, 1),
(128, 5, 'Pistol squat', 'Squat pistol completo', 10, 4, 16, 1.3, 1),
(129, 5, 'Handstand hold', 'Handstand hold', 30, 3, 17, 1.3, 1),
(130, 5, 'Flessioni planche', 'Flessioni planche', 8, 4, 18, 1.3, 1),
(131, 5, 'Human flag', 'Human flag hold', 15, 3, 19, 1.3, 1),
(132, 5, 'Muscle up', 'Muscle up', 5, 3, 20, 1.3, 1),
(133, 5, 'L-sit', 'L-sit hold', 30, 3, 21, 1.3, 1),
(134, 5, 'Flessioni archer', 'Flessioni archer', 10, 4, 22, 1.45, 1),
(135, 5, 'Pistol squat', 'Squat pistol completo', 10, 4, 23, 1.45, 1),
(136, 5, 'Handstand hold', 'Handstand hold', 30, 3, 24, 1.45, 1),
(137, 5, 'Flessioni planche', 'Flessioni planche', 8, 4, 25, 1.45, 1),
(138, 5, 'Human flag', 'Human flag hold', 15, 3, 26, 1.45, 1),
(139, 5, 'Muscle up', 'Muscle up', 5, 3, 27, 1.45, 1),
(140, 5, 'L-sit', 'L-sit hold', 30, 3, 28, 1.45, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `feedback_allenamento`
--

CREATE TABLE `feedback_allenamento` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `allenamento_giornaliero_id` int(11) DEFAULT NULL,
  `voto` int(11) DEFAULT NULL CHECK (`voto` between 1 and 5),
  `commento` text DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `feedback_finale`
--

CREATE TABLE `feedback_finale` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `piano_id` int(11) DEFAULT NULL,
  `voto` int(11) DEFAULT NULL,
  `commento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `giorni_riposo`
--

CREATE TABLE `giorni_riposo` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `data_riposo` date DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `orario` time DEFAULT NULL,
  `attiva` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `periodi_riposo`
--

CREATE TABLE `periodi_riposo` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `giorni_consecutivi` int(11) DEFAULT 0,
  `giorni_riposo_consigliati` int(11) DEFAULT 1,
  `ultimo_allenamento` date DEFAULT NULL,
  `data_creazione` timestamp NULL DEFAULT current_timestamp(),
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `periodi_riposo`
--

INSERT INTO `periodi_riposo` (`id`, `utente_id`, `giorni_consecutivi`, `giorni_riposo_consigliati`, `ultimo_allenamento`, `data_creazione`, `tenant_id`) VALUES
(1, 15, 0, 7, '2026-05-15', '2026-05-15 07:43:29', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi`
--

CREATE TABLE `permessi` (
  `id` int(11) NOT NULL,
  `nome_permesso` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `permessi`
--

INSERT INTO `permessi` (`id`, `nome_permesso`, `descrizione`) VALUES
(1, 'registrazione', 'Registrazione utente'),
(2, 'gestione_profilo', 'Gestione profilo utente'),
(3, 'visualizza_allenamenti', 'Visualizzare allenamenti giornalieri'),
(4, 'completa_allenamento', 'Segnare allenamento come completato'),
(5, 'feedback_allenamento', 'Lasciare feedback allenamento'),
(6, 'visualizza_progressi', 'Visualizzare progressi'),
(7, 'gestione_amicizie', 'Aggiungere e gestire amici'),
(8, 'visualizza_classifica', 'Visualizzare classifica'),
(9, 'creazione_piani', 'Creare piani allenamento'),
(10, 'modifica_piani', 'Modificare piani allenamento'),
(11, 'gestione_esercizi', 'Creare e modificare esercizi'),
(12, 'visualizza_statistiche', 'Visualizzare statistiche utenti'),
(13, 'gestione_utenti', 'Gestione utenti'),
(14, 'gestione_ruoli', 'Gestione ruoli'),
(15, 'gestione_sistema', 'Gestione completa sistema');

-- --------------------------------------------------------

--
-- Struttura della tabella `piani_allenamento`
--

CREATE TABLE `piani_allenamento` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `difficolta` int(11) DEFAULT 1,
  `stato` enum('attivo','completato') DEFAULT 'attivo',
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `piani_allenamento`
--

INSERT INTO `piani_allenamento` (`id`, `utente_id`, `data_inizio`, `data_fine`, `difficolta`, `stato`, `tenant_id`) VALUES
(1, 5, '2026-02-09', '2026-03-09', 2, 'attivo', NULL),
(2, 12, '2026-05-11', '2026-06-08', 2, 'attivo', 1),
(3, 14, '2026-05-14', '2026-06-11', 4, 'attivo', 1),
(4, 15, '2026-04-15', '2026-05-12', 4, 'completato', 1),
(5, 15, '2026-04-15', '2026-05-12', 4, 'attivo', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `progressi`
--

CREATE TABLE `progressi` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `allenamento_giornaliero_id` int(11) DEFAULT NULL,
  `calorie_bruciate` int(11) DEFAULT NULL,
  `fatica` int(11) DEFAULT NULL,
  `data_all` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `progressi_dettaglio`
--

CREATE TABLE `progressi_dettaglio` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `esercizio_id` int(11) NOT NULL,
  `data_allenamento` date DEFAULT NULL,
  `ripetizioni_fatte` int(11) DEFAULT NULL,
  `serie_fatte` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `difficolta_eseguita` float DEFAULT 1,
  `completato` int(11) DEFAULT 0,
  `data_creazione` timestamp NULL DEFAULT current_timestamp(),
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `progressi_dettaglio`
--

INSERT INTO `progressi_dettaglio` (`id`, `utente_id`, `esercizio_id`, `data_allenamento`, `ripetizioni_fatte`, `serie_fatte`, `feedback`, `difficolta_eseguita`, `completato`, `data_creazione`, `tenant_id`) VALUES
(1, 5, 1, '2026-02-09', 10, 3, 'ben dai', 1, 1, '2026-02-09 07:50:09', NULL),
(2, 12, 29, '2026-05-11', 10, 3, 'bene dai', 1, 1, '2026-05-11 06:14:06', 1),
(3, 14, 57, '2026-05-14', 10, 4, 'facilotto', 1, 1, '2026-05-14 09:35:41', 1),
(4, 12, 33, '2026-05-15', 10, 5, 'benissimo', 1, 1, '2026-05-15 07:36:56', 1),
(5, 15, 85, '2026-05-15', 10, 4, '', 1, 1, '2026-05-15 07:43:55', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `questionario_utente`
--

CREATE TABLE `questionario_utente` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `obiettivo` enum('dimagrimento','tonificazione','forza') DEFAULT NULL,
  `tempo_disponibile` int(11) DEFAULT NULL,
  `frequenza_settimanale` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `quiz_risposte`
--

CREATE TABLE `quiz_risposte` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `eta` int(11) DEFAULT NULL,
  `livello_fitness` enum('principiante','intermedio','avanzato') DEFAULT 'principiante',
  `obiettivo` varchar(255) DEFAULT NULL,
  `orario_notifica` time DEFAULT NULL,
  `completato` int(11) DEFAULT 0,
  `data_quiz` timestamp NULL DEFAULT current_timestamp(),
  `tenant_id` int(11) DEFAULT NULL,
  `notifiche_attive` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `quiz_risposte`
--

INSERT INTO `quiz_risposte` (`id`, `utente_id`, `eta`, `livello_fitness`, `obiettivo`, `orario_notifica`, `completato`, `data_quiz`, `tenant_id`, `notifiche_attive`) VALUES
(1, 5, 18, 'principiante', 'diventare enorme', '06:00:00', 1, '2026-02-09 07:49:28', NULL, 1),
(2, 12, 19, 'principiante', 'diventare enorme', '14:00:00', 1, '2026-05-11 06:13:46', 1, 1),
(3, 14, 18, 'avanzato', 'aumentare massa', '17:00:00', 1, '2026-05-14 09:34:54', 1, 1),
(5, 15, 81, 'avanzato', '82', '12:00:00', 1, '2026-05-15 07:42:07', 1, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `ruoli`
--

CREATE TABLE `ruoli` (
  `id` int(11) NOT NULL,
  `nome_ruolo` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ruoli`
--

INSERT INTO `ruoli` (`id`, `nome_ruolo`, `descrizione`) VALUES
(1, 'utente', 'Utente standard'),
(2, 'allenatore', 'Coach/allenatore'),
(3, 'amministratore', 'Amministratore piattaforma'),
(4, 'super_admin', 'Super amministratore multi-tenant');

-- --------------------------------------------------------

--
-- Struttura della tabella `ruolo_permesso`
--

CREATE TABLE `ruolo_permesso` (
  `ruolo_id` int(11) NOT NULL,
  `permesso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ruolo_permesso`
--

INSERT INTO `ruolo_permesso` (`ruolo_id`, `permesso_id`) VALUES
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(2, 6),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8);

-- --------------------------------------------------------

--
-- Struttura della tabella `statistiche_esercizi`
--

CREATE TABLE `statistiche_esercizi` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `esercizio_id` int(11) DEFAULT NULL,
  `totale_ripetizioni` int(11) DEFAULT NULL,
  `tempo_totale` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `statistiche_esercizio`
--

CREATE TABLE `statistiche_esercizio` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `nome_esercizio` varchar(255) DEFAULT NULL,
  `volte_completato` int(11) DEFAULT 0,
  `ripetizioni_totali` int(11) DEFAULT 0,
  `difficolta_media` float DEFAULT 1,
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `statistiche_esercizio`
--

INSERT INTO `statistiche_esercizio` (`id`, `utente_id`, `nome_esercizio`, `volte_completato`, `ripetizioni_totali`, `difficolta_media`, `tenant_id`) VALUES
(1, 5, 'Push-up a muro', 1, 10, 1, NULL),
(2, 12, 'Push-up a muro', 1, 10, 1, 1),
(3, 14, 'Flessioni archer', 1, 10, 1, 1),
(4, 12, 'Affondi', 1, 10, 1, 1),
(5, 15, 'Flessioni archer', 1, 10, 1, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `stato` enum('active','suspended') DEFAULT 'active',
  `logo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `tenants`
--

INSERT INTO `tenants` (`id`, `nome`, `slug`, `stato`, `logo_url`, `created_at`) VALUES
(1, 'HomeWorkout Demo', 'demo-homeworkout', 'active', NULL, '2026-04-10 09:38:10'),
(2, 'Greentheory', 'madone', 'active', NULL, '2026-04-10 09:41:06'),
(3, 'max fitness', 'mapello', 'active', NULL, '2026-04-13 06:30:35');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_ruolo`
--

CREATE TABLE `utente_ruolo` (
  `utente_id` int(11) NOT NULL,
  `ruolo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente_ruolo`
--

INSERT INTO `utente_ruolo` (`utente_id`, `ruolo_id`) VALUES
(1, 1),
(2, 2),
(5, 1),
(9, 2),
(10, 4),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `cognome` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `livello` enum('principiante','intermedio','avanzato') DEFAULT 'principiante',
  `creato_il` timestamp NULL DEFAULT current_timestamp(),
  `refresh_token` varchar(255) DEFAULT NULL,
  `refresh_token_scadenza` datetime DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `nome`, `cognome`, `email`, `password`, `livello`, `creato_il`, `refresh_token`, `refresh_token_scadenza`, `tenant_id`) VALUES
(1, 'marco', 'q', 'gerosa.alberto.studente@itispaleocapa.it', '$2y$10$mVZnpE1Vznf.XntumfmN.OrV77NdwxdqOYl1P6kM4Ksi62vfNXsFe', 'principiante', '2026-01-29 11:27:50', '5823d4f3347f522103c4bd70084801618b8aff10c42c73d4fd77efdb572d92d9', '2026-01-29 11:55:38', 1),
(2, 'alberto', 'grana', 'alberto@l.cprna', '$2y$10$5vMNtrz1DgNLgI1TjGHWgOXd9rcvWju/KdV2.dFASjHMZ84jvFMJe', 'principiante', '2026-02-02 07:19:40', '5e9a4146b78725214ab989f1b4422a98c891738fec6f89783225366f496c9a4e', '2026-02-02 07:50:49', 1),
(3, 'a', 'a', 'a@e.com', '$2y$10$Bb08gWqxFiV/PIskbLEjZeRHrNIeW8K.0xy4bv1Os/BrFLzvqDw9e', 'principiante', '2026-02-02 07:49:16', NULL, NULL, 1),
(4, 'alberto', 'a', 'gerosa.studente@itispaleocapa.it', '$2y$10$uMo.Zjyh9DejMtMbhD9yjedKzOQ78nKm4sURDAgTwM1vs4UBQ.giK', 'principiante', '2026-02-02 07:50:40', 'fc2ea12879da3e1f2f1113a5327027476208ca7c55e9ba75f73a4272166937ea', '2026-02-02 08:00:54', 1),
(5, 'mattia', 'corna', 'corna@ciao.it', '$2y$10$fsO0fKiXUJErvOaw2YzZvevcOpuqT43A.aWaGdIng4E6r/GLSPW7.', 'principiante', '2026-02-09 07:22:27', 'db7335311206aa09554008028860aec75705a19b09beec82d786be5eee4129e7', '2026-02-09 07:58:53', 1),
(6, 'a', 'a', 'alberto@it.com', '$2y$10$/MfD0Pie4ueW8YArvNeo5OSTAwffGSFSyvSe/i5qBchUl0hpeaFUe', 'principiante', '2026-02-20 11:43:45', NULL, NULL, 1),
(7, 'a', 'a', 'alberto@ciao.it', '$2y$10$WG1k8HNcr0LFBfo4EASkNuPux4yySu8076s0cN70TC2mz/RrEuSru', 'principiante', '2026-02-27 12:40:19', '14623086950795c949adfdc85a9d85c003c3008a104b9e426ee369bdb70e948d', '2026-02-27 12:50:42', 1),
(8, 'alberto', 'gerosa', 'leonardo@ardo.it', '$2y$10$herNRrQW8rjwgQkzttTFieEIJRF7vKohNNlLi8zyaDH9ZEtYtGSYK', 'principiante', '2026-03-13 11:46:34', '2c2ac6fe15b2012352bc19fd62a9f03f791aff993f2ebdda630666cea271c81a', '2026-03-13 12:30:39', 1),
(9, 'stiven', 'kurtu', 'stiven@iven.it', '$2y$10$AkiyvoTRwokoUHlDIOqRVe35uvnMxAKpRGlBqJwE5one2fmC8yWdi', 'principiante', '2026-03-13 12:22:35', 'd47d82731a0f5be7b5cd41d241311119cb3ae2be1f6728ba8261b5c76dc10450', '2026-03-13 12:32:50', 1),
(10, 'Super', 'Admin', 'superadmin@homeworkout.local', '$2y$10$//qw5W3cP1eq23EudwkKwOMHZlWJb8BdpcrD3.LBFhv4OD4Uv9J8W', 'avanzato', '2026-04-10 09:38:11', '94ddc4b87e79df95b913385d5263f53afdbc8281b839049185aa8479a48e5647', '2026-05-09 08:32:09', 1),
(11, 'alberto', 'gerosa', 'alberto@gmail.com', '$2y$10$.wrwK5za/M7gDlbj1myeIOnS4xhPTH2LIBh1VnUJa0yQ/bBIbbese', 'principiante', '2026-04-24 08:11:46', 'dbb5c421347ac90ae508a4212a421f4ee9d54858d195f50e60efc0202d725b18', '2026-04-24 08:38:10', 1),
(12, 'ALBERTO', 'GEROSA', 'gerosa@gmail.com', '$2y$10$iswUMxmnmV8EGTdHUGjIV.JWLtHhUMWHofbtPvfRBCWnLjBefA55C', 'principiante', '2026-05-04 07:45:30', 'e7e614a17b29eb801c6912745fb5158c0fffd467a6cce078becc1eb9f5843cd0', '2026-05-15 07:46:20', 1),
(13, 'a', 'a', 'a@gmail.com', '$2y$10$mDJaKxcyFZtmgknDGo0ELuim34i6TTYCRc.hY8IRKp0WojJds0GlK', 'principiante', '2026-05-04 08:53:30', '0b8658ada1e9578a04d7860468d89f01f2b2ccb6696cfcf7354d3d2c439cc94a', '2026-05-04 09:03:34', 1),
(14, 'stiven', 'kurtu', 'stiven@kurtu.com', '$2y$10$5xtmYR64kwjl3FpmXIvmOu5aYXpJtnxZz9EiUQ0SNc7A018m5O8p6', 'principiante', '2026-05-14 09:34:06', '2728ad434386bdbe321543fb39599e1dce281bd51c0adc1d985510b0aa783ca8', '2026-05-14 09:46:33', 1),
(15, 'io', 'io', 'io@gmail.com', '$2y$10$zVQOhT9NPp89wP.5DFrjnuBvzeYWEb5mMK97YtBEfsM4NKcu1WDu6', 'principiante', '2026-05-15 07:41:15', '04f4176aed20dc7474386f3a592c8c1e172ae656266e338bef3db35daeba36bd', '2026-05-15 11:36:38', 1);

-- --------------------------------------------------------
-- Dati demo aggiuntivi: popolo le tabelle vuote per HomeWorkout
-- --------------------------------------------------------

-- Classifiche
INSERT INTO `classifiche` (`id`, `utente_id`, `punti`) VALUES
(1, 5, 100),
(2, 12, 80),
(3, 14, 120),
(4, 15, 95);

-- Esercizi assegnati agli allenamenti (esercizi_allenamento)
INSERT INTO `esercizi_allenamento` (`id`, `allenamento_giornaliero_id`, `esercizio_id`, `ripetizioni`, `serie`, `durata`) VALUES
(1, 1, 1, 10, 3, NULL),
(2, 1, 2, 15, 3, NULL),
(3, 2, 1, 8, 3, NULL),
(4, 3, 29, 10, 3, NULL),
(5, 3, 30, 15, 3, NULL),
(6, 4, 36, 10, 3, NULL),
(7, 5, 57, 10, 4, NULL),
(8, 6, 64, 10, 4, NULL),
(9, 7, 85, 10, 4, NULL),
(10, 8, 1, 12, 3, NULL),
(11, 9, 3, NULL, 3, 90),
(12, 10, 5, 10, 3, NULL);

-- Feedback sugli allenamenti
INSERT INTO `feedback_allenamento` (`id`, `utente_id`, `allenamento_giornaliero_id`, `voto`, `commento`, `tenant_id`) VALUES
(1, 5, 1, 4, 'Buon allenamento per iniziare', NULL),
(2, 12, 3, 5, 'Molto intenso ma fattibile', 1),
(3, 14, 5, 3, 'Troppo difficile in alcune parti', 1);

-- Feedback finale per piani
INSERT INTO `feedback_finale` (`id`, `utente_id`, `piano_id`, `voto`, `commento`) VALUES
(1, 15, 4, 5, 'Ottimo piano, risultati visibili');

-- Giorni di riposo
INSERT INTO `giorni_riposo` (`id`, `utente_id`, `data_riposo`, `motivo`) VALUES
(1, 15, '2026-05-10', 'Recupero muscolare'),
(2, 12, '2026-05-08', 'Influenza');

-- Notifiche
INSERT INTO `notifiche` (`id`, `utente_id`, `orario`, `attiva`) VALUES
(1, 5, '06:00:00', 1),
(2, 12, '14:00:00', 1),
(3, 14, '17:00:00', 1),
(4, 15, '12:00:00', 0);

-- Progressi a livello di allenamento
INSERT INTO `progressi` (`id`, `utente_id`, `allenamento_giornaliero_id`, `calorie_bruciate`, `fatica`, `data_all`) VALUES
(1, 5, 1, 120, 3, '2026-02-09'),
(2, 12, 3, 150, 4, '2026-05-11'),
(3, 14, 5, 300, 5, '2026-05-14'),
(4, 15, 9, 220, 4, '2026-04-17');

-- Questionari utente
INSERT INTO `questionario_utente` (`id`, `utente_id`, `obiettivo`, `tempo_disponibile`, `frequenza_settimanale`) VALUES
(1, 5, 'dimagrimento', 30, 3),
(2, 12, 'forza', 45, 4),
(3, 14, 'tonificazione', 20, 2);

-- Statistiche aggregate esercizi
INSERT INTO `statistiche_esercizi` (`id`, `utente_id`, `esercizio_id`, `totale_ripetizioni`, `tempo_totale`) VALUES
(1, 5, 1, 10, NULL),
(2, 12, 1, 20, NULL),
(3, 14, 8, 10, NULL);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_allenamenti_giornalieri`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_allenamenti_giornalieri` (
`allenamento_id` int(11)
,`utente_id` int(11)
,`data_all` date
,`completato` tinyint(1)
,`difficolta` int(11)
,`stato` enum('attivo','completato')
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_andamento_allenamenti`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_andamento_allenamenti` (
`utente_id` int(11)
,`nome` varchar(50)
,`allenamenti_totali` bigint(21)
,`allenamenti_completati` decimal(25,0)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_classifica_mondiale`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_classifica_mondiale` (
`id` int(11)
,`nome` varchar(50)
,`cognome` varchar(50)
,`punti` int(11)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_esercizi_giornalieri`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_esercizi_giornalieri` (
`allenamento_id` int(11)
,`nome_esercizio` varchar(255)
,`ripetizioni` int(11)
,`serie` int(11)
,`difficolta_moltiplicatore` float
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_progressi_utente`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_progressi_utente` (
`utente_id` int(11)
,`nome` varchar(50)
,`cognome` varchar(50)
,`data_all` date
,`calorie_bruciate` int(11)
,`fatica` int(11)
);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `allenamenti_giornalieri`
--
ALTER TABLE `allenamenti_giornalieri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `piano_id` (`piano_id`);

--
-- Indici per le tabelle `amicizie`
--
ALTER TABLE `amicizie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `amico_id` (`amico_id`);

--
-- Indici per le tabelle `classifiche`
--
ALTER TABLE `classifiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `esercizi`
--
ALTER TABLE `esercizi`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `esercizi_allenamento`
--
ALTER TABLE `esercizi_allenamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allenamento_giornaliero_id` (`allenamento_giornaliero_id`),
  ADD KEY `esercizio_id` (`esercizio_id`);

--
-- Indici per le tabelle `esercizi_piano`
--
ALTER TABLE `esercizi_piano`
  ADD PRIMARY KEY (`id`),
  ADD KEY `piano_id` (`piano_id`);

--
-- Indici per le tabelle `feedback_allenamento`
--
ALTER TABLE `feedback_allenamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `allenamento_giornaliero_id` (`allenamento_giornaliero_id`);

--
-- Indici per le tabelle `feedback_finale`
--
ALTER TABLE `feedback_finale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `piano_id` (`piano_id`);

--
-- Indici per le tabelle `giorni_riposo`
--
ALTER TABLE `giorni_riposo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `periodi_riposo`
--
ALTER TABLE `periodi_riposo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `permessi`
--
ALTER TABLE `permessi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_permesso` (`nome_permesso`);

--
-- Indici per le tabelle `piani_allenamento`
--
ALTER TABLE `piani_allenamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `idx_piani_tenant_utente` (`tenant_id`,`utente_id`);

--
-- Indici per le tabelle `progressi`
--
ALTER TABLE `progressi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `allenamento_giornaliero_id` (`allenamento_giornaliero_id`);

--
-- Indici per le tabelle `progressi_dettaglio`
--
ALTER TABLE `progressi_dettaglio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `esercizio_id` (`esercizio_id`);

--
-- Indici per le tabelle `questionario_utente`
--
ALTER TABLE `questionario_utente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `quiz_risposte`
--
ALTER TABLE `quiz_risposte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_quiz_tenant_utente` (`tenant_id`,`utente_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `ruoli`
--
ALTER TABLE `ruoli`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_ruolo` (`nome_ruolo`);

--
-- Indici per le tabelle `ruolo_permesso`
--
ALTER TABLE `ruolo_permesso`
  ADD PRIMARY KEY (`ruolo_id`,`permesso_id`),
  ADD KEY `permesso_id` (`permesso_id`);

--
-- Indici per le tabelle `statistiche_esercizi`
--
ALTER TABLE `statistiche_esercizi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `esercizio_id` (`esercizio_id`);

--
-- Indici per le tabelle `statistiche_esercizio`
--
ALTER TABLE `statistiche_esercizio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_stat_tenant_utente_nome` (`tenant_id`,`utente_id`,`nome_esercizio`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indici per le tabelle `utente_ruolo`
--
ALTER TABLE `utente_ruolo`
  ADD PRIMARY KEY (`utente_id`,`ruolo_id`),
  ADD KEY `ruolo_id` (`ruolo_id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `allenamenti_giornalieri`
--
ALTER TABLE `allenamenti_giornalieri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `amicizie`
--
ALTER TABLE `amicizie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `classifiche`
--
ALTER TABLE `classifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `esercizi`
--
ALTER TABLE `esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `esercizi_allenamento`
--
ALTER TABLE `esercizi_allenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `esercizi_piano`
--
ALTER TABLE `esercizi_piano`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT per la tabella `feedback_allenamento`
--
ALTER TABLE `feedback_allenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `feedback_finale`
--
ALTER TABLE `feedback_finale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `giorni_riposo`
--
ALTER TABLE `giorni_riposo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `periodi_riposo`
--
ALTER TABLE `periodi_riposo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `permessi`
--
ALTER TABLE `permessi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `piani_allenamento`
--
ALTER TABLE `piani_allenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `progressi`
--
ALTER TABLE `progressi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `progressi_dettaglio`
--
ALTER TABLE `progressi_dettaglio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `questionario_utente`
--
ALTER TABLE `questionario_utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `quiz_risposte`
--
ALTER TABLE `quiz_risposte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `ruoli`
--
ALTER TABLE `ruoli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `statistiche_esercizi`
--
ALTER TABLE `statistiche_esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `statistiche_esercizio`
--
ALTER TABLE `statistiche_esercizio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_allenamenti_giornalieri`
--
DROP TABLE IF EXISTS `vista_allenamenti_giornalieri`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vista_allenamenti_giornalieri`  AS SELECT `ag`.`id` AS `allenamento_id`, `pa`.`utente_id` AS `utente_id`, `ag`.`data_all` AS `data_all`, `ag`.`completato` AS `completato`, `pa`.`difficolta` AS `difficolta`, `pa`.`stato` AS `stato` FROM (`allenamenti_giornalieri` `ag` join `piani_allenamento` `pa` on(`ag`.`piano_id` = `pa`.`id`)) ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_andamento_allenamenti`
--
DROP TABLE IF EXISTS `vista_andamento_allenamenti`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vista_andamento_allenamenti`  AS SELECT `u`.`id` AS `utente_id`, `u`.`nome` AS `nome`, count(`ag`.`id`) AS `allenamenti_totali`, sum(`ag`.`completato`) AS `allenamenti_completati` FROM ((`utenti` `u` left join `piani_allenamento` `pa` on(`u`.`id` = `pa`.`utente_id`)) left join `allenamenti_giornalieri` `ag` on(`pa`.`id` = `ag`.`piano_id`)) GROUP BY `u`.`id` ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_classifica_mondiale`
--
DROP TABLE IF EXISTS `vista_classifica_mondiale`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vista_classifica_mondiale`  AS SELECT `u`.`id` AS `id`, `u`.`nome` AS `nome`, `u`.`cognome` AS `cognome`, `c`.`punti` AS `punti` FROM (`classifiche` `c` join `utenti` `u` on(`c`.`utente_id` = `u`.`id`)) ORDER BY `c`.`punti` DESC ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_esercizi_giornalieri`
--
DROP TABLE IF EXISTS `vista_esercizi_giornalieri`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vista_esercizi_giornalieri`  AS SELECT `ag`.`id` AS `allenamento_id`, `ep`.`nome_esercizio` AS `nome_esercizio`, `ep`.`ripetizioni` AS `ripetizioni`, `ep`.`serie` AS `serie`, `ep`.`difficolta_moltiplicatore` AS `difficolta_moltiplicatore` FROM (`allenamenti_giornalieri` `ag` join `esercizi_piano` `ep` on(`ep`.`piano_id` = `ag`.`piano_id`)) ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_progressi_utente`
--
DROP TABLE IF EXISTS `vista_progressi_utente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`utente_phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vista_progressi_utente`  AS SELECT `u`.`id` AS `utente_id`, `u`.`nome` AS `nome`, `u`.`cognome` AS `cognome`, `p`.`data_all` AS `data_all`, `p`.`calorie_bruciate` AS `calorie_bruciate`, `p`.`fatica` AS `fatica` FROM (`utenti` `u` join `progressi` `p` on(`u`.`id` = `p`.`utente_id`)) ;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `allenamenti_giornalieri`
--
ALTER TABLE `allenamenti_giornalieri`
  ADD CONSTRAINT `allenamenti_giornalieri_ibfk_1` FOREIGN KEY (`piano_id`) REFERENCES `piani_allenamento` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `amicizie`
--
ALTER TABLE `amicizie`
  ADD CONSTRAINT `amicizie_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `amicizie_ibfk_2` FOREIGN KEY (`amico_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `classifiche`
--
ALTER TABLE `classifiche`
  ADD CONSTRAINT `classifiche_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `esercizi_allenamento`
--
ALTER TABLE `esercizi_allenamento`
  ADD CONSTRAINT `esercizi_allenamento_ibfk_1` FOREIGN KEY (`allenamento_giornaliero_id`) REFERENCES `allenamenti_giornalieri` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `esercizi_allenamento_ibfk_2` FOREIGN KEY (`esercizio_id`) REFERENCES `esercizi` (`id`);

--
-- Limiti per la tabella `esercizi_piano`
--
ALTER TABLE `esercizi_piano`
  ADD CONSTRAINT `esercizi_piano_ibfk_1` FOREIGN KEY (`piano_id`) REFERENCES `piani_allenamento` (`id`);

--
-- Limiti per la tabella `feedback_allenamento`
--
ALTER TABLE `feedback_allenamento`
  ADD CONSTRAINT `feedback_allenamento_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `feedback_allenamento_ibfk_2` FOREIGN KEY (`allenamento_giornaliero_id`) REFERENCES `allenamenti_giornalieri` (`id`);

--
-- Limiti per la tabella `feedback_finale`
--
ALTER TABLE `feedback_finale`
  ADD CONSTRAINT `feedback_finale_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `feedback_finale_ibfk_2` FOREIGN KEY (`piano_id`) REFERENCES `piani_allenamento` (`id`);

--
-- Limiti per la tabella `giorni_riposo`
--
ALTER TABLE `giorni_riposo`
  ADD CONSTRAINT `giorni_riposo_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  ADD CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `periodi_riposo`
--
ALTER TABLE `periodi_riposo`
  ADD CONSTRAINT `periodi_riposo_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `piani_allenamento`
--
ALTER TABLE `piani_allenamento`
  ADD CONSTRAINT `piani_allenamento_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `progressi`
--
ALTER TABLE `progressi`
  ADD CONSTRAINT `progressi_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `progressi_ibfk_2` FOREIGN KEY (`allenamento_giornaliero_id`) REFERENCES `allenamenti_giornalieri` (`id`);

--
-- Limiti per la tabella `progressi_dettaglio`
--
ALTER TABLE `progressi_dettaglio`
  ADD CONSTRAINT `progressi_dettaglio_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `progressi_dettaglio_ibfk_2` FOREIGN KEY (`esercizio_id`) REFERENCES `esercizi_piano` (`id`);

--
-- Limiti per la tabella `questionario_utente`
--
ALTER TABLE `questionario_utente`
  ADD CONSTRAINT `questionario_utente_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `quiz_risposte`
--
ALTER TABLE `quiz_risposte`
  ADD CONSTRAINT `quiz_risposte_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `ruolo_permesso`
--
ALTER TABLE `ruolo_permesso`
  ADD CONSTRAINT `ruolo_permesso_ibfk_1` FOREIGN KEY (`ruolo_id`) REFERENCES `ruoli` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ruolo_permesso_ibfk_2` FOREIGN KEY (`permesso_id`) REFERENCES `permessi` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `statistiche_esercizi`
--
ALTER TABLE `statistiche_esercizi`
  ADD CONSTRAINT `statistiche_esercizi_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `statistiche_esercizi_ibfk_2` FOREIGN KEY (`esercizio_id`) REFERENCES `esercizi` (`id`);

--
-- Limiti per la tabella `statistiche_esercizio`
--
ALTER TABLE `statistiche_esercizio`
  ADD CONSTRAINT `statistiche_esercizio_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `utente_ruolo`
--
ALTER TABLE `utente_ruolo`
  ADD CONSTRAINT `utente_ruolo_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utente_ruolo_ibfk_2` FOREIGN KEY (`ruolo_id`) REFERENCES `ruoli` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
