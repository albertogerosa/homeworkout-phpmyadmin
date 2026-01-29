-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 29, 2026 alle 11:24
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
-- Database: `allenamenti`
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

-- --------------------------------------------------------

--
-- Struttura della tabella `amicizie`
--

CREATE TABLE `amicizie` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `amico_id` int(11) DEFAULT NULL,
  `stato` enum('pending','accepted') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struttura della tabella `feedback_allenamento`
--

CREATE TABLE `feedback_allenamento` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `allenamento_giornaliero_id` int(11) DEFAULT NULL,
  `voto` int(11) DEFAULT NULL CHECK (`voto` between 1 and 5),
  `commento` text DEFAULT NULL
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
-- Struttura della tabella `permessi`
--

CREATE TABLE `permessi` (
  `id` int(11) NOT NULL,
  `nome_permesso` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `stato` enum('attivo','completato') DEFAULT 'attivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struttura della tabella `ruoli`
--

CREATE TABLE `ruoli` (
  `id` int(11) NOT NULL,
  `nome_ruolo` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ruolo_permesso`
--

CREATE TABLE `ruolo_permesso` (
  `ruolo_id` int(11) NOT NULL,
  `permesso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struttura della tabella `utente_ruolo`
--

CREATE TABLE `utente_ruolo` (
  `utente_id` int(11) NOT NULL,
  `ruolo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `creato_il` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `progressi`
--
ALTER TABLE `progressi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `allenamento_giornaliero_id` (`allenamento_giornaliero_id`);

--
-- Indici per le tabelle `questionario_utente`
--
ALTER TABLE `questionario_utente`
  ADD PRIMARY KEY (`id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `amicizie`
--
ALTER TABLE `amicizie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `classifiche`
--
ALTER TABLE `classifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `esercizi`
--
ALTER TABLE `esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `esercizi_allenamento`
--
ALTER TABLE `esercizi_allenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT per la tabella `permessi`
--
ALTER TABLE `permessi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `piani_allenamento`
--
ALTER TABLE `piani_allenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `progressi`
--
ALTER TABLE `progressi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `questionario_utente`
--
ALTER TABLE `questionario_utente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ruoli`
--
ALTER TABLE `ruoli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `statistiche_esercizi`
--
ALTER TABLE `statistiche_esercizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Limiti per la tabella `questionario_utente`
--
ALTER TABLE `questionario_utente`
  ADD CONSTRAINT `questionario_utente_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

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
-- Limiti per la tabella `utente_ruolo`
--
ALTER TABLE `utente_ruolo`
  ADD CONSTRAINT `utente_ruolo_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utente_ruolo_ibfk_2` FOREIGN KEY (`ruolo_id`) REFERENCES `ruoli` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
