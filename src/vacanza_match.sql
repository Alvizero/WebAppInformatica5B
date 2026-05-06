-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 06, 2026 alle 10:25
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vacanza_match`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `conversations`
--

CREATE TABLE `conversations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user1_id` int(10) UNSIGNED NOT NULL,
  `user2_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `conversations`
--

INSERT INTO `conversations` (`id`, `user1_id`, `user2_id`, `created_at`) VALUES
(1, 6, 8, '2026-05-06 06:55:08');

-- --------------------------------------------------------

--
-- Struttura della tabella `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 8, 'ciao amore', '2026-05-06 06:55:14'),
(2, 1, 6, 'ehii', '2026-05-06 06:55:23'),
(3, 1, 8, 'ciao 🥰', '2026-05-06 06:56:22'),
(4, 1, 8, 'pidfdfn', '2026-05-06 07:00:02'),
(5, 1, 8, '🔥', '2026-05-06 07:34:44'),
(6, 1, 6, ',,', '2026-05-06 07:35:00'),
(7, 1, 6, 'vv', '2026-05-06 07:35:05'),
(8, 1, 6, 'mm', '2026-05-06 07:35:08'),
(9, 1, 6, 'mm', '2026-05-06 07:35:10'),
(10, 1, 6, '😎', '2026-05-06 07:35:17');

-- --------------------------------------------------------

--
-- Struttura della tabella `pacchetti`
--

CREATE TABLE `pacchetti` (
  `id` int(10) UNSIGNED NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `localita` varchar(255) NOT NULL,
  `latitudine` decimal(10,7) DEFAULT NULL,
  `longitudine` decimal(10,7) DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `nome_agenzia` varchar(255) NOT NULL,
  `email_agenzia` varchar(150) NOT NULL,
  `link_esterno` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `pacchetti`
--

INSERT INTO `pacchetti` (`id`, `titolo`, `descrizione`, `localita`, `latitudine`, `longitudine`, `prezzo`, `nome_agenzia`, `email_agenzia`, `link_esterno`, `created_at`) VALUES
(1, 'test', 'vvv', 'Jesolo, Venezia, Veneto, 30016, Italia', 45.5347443, 12.6538449, 10.00, 'sacconato', 'sacconato@sac.it', '', '2026-05-06 08:20:58');

-- --------------------------------------------------------

--
-- Struttura della tabella `pacchetti_immagini`
--

CREATE TABLE `pacchetti_immagini` (
  `id` int(10) UNSIGNED NOT NULL,
  `pacchetto_id` int(10) UNSIGNED NOT NULL,
  `percorso` varchar(255) NOT NULL,
  `is_copertina` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `messaggio` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `support_messages`
--

INSERT INTO `support_messages` (`id`, `ticket_id`, `sender_id`, `messaggio`, `created_at`) VALUES
(18, 7, 6, 'cc', '2026-04-16 09:22:18'),
(19, 7, 6, 'cc', '2026-04-16 09:29:30'),
(21, 7, 6, 'hhh', '2026-04-21 10:20:01'),
(22, 7, 6, 'mk', '2026-04-30 08:22:42');

-- --------------------------------------------------------

--
-- Struttura della tabella `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `oggetto` varchar(255) NOT NULL,
  `stato` enum('aperto','risposto','chiuso') NOT NULL DEFAULT 'aperto',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `oggetto`, `stato`, `created_at`) VALUES
(7, 6, 'cvfc', 'risposto', '2026-04-16 09:22:18');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nazionalita` varchar(100) NOT NULL,
  `lingua` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `livello_utente` tinyint(3) UNSIGNED NOT NULL DEFAULT 255
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `nome`, `cognome`, `email`, `password`, `nazionalita`, `lingua`, `created_at`, `livello_utente`) VALUES
(6, 'Alvise', 'Sacconato', 'alviseparide@i.it', '$2y$10$PqjLtOdtAw9V28LEwLkUFu.Yqlud9ohIBRe88NE4LkJrO1Iv4EeSm', 'italiana', 'italiano', '2026-04-16 08:28:08', 1),
(8, 'Diego', 'Bincoletto', 'binco@i.it', '$2y$10$7rOJWvF/LbtX9UTiiw8xqesU1HXKj12ZaAmzvF0Hj9O40XgucSv5W', 'italiana', 'italiano', '2026-05-06 06:53:08', 255);

-- --------------------------------------------------------

--
-- Struttura della tabella `viaggi`
--

CREATE TABLE `viaggi` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `destinazione` varchar(255) NOT NULL,
  `latitudine` decimal(10,7) NOT NULL,
  `longitudine` decimal(10,7) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `viaggi`
--

INSERT INTO `viaggi` (`id`, `user_id`, `destinazione`, `latitudine`, `longitudine`, `data_inizio`, `data_fine`, `created_at`) VALUES
(15, 6, 'Excelsior, 3, Via Zara, Lido di Jesolo, Jesolo, Venezia, Veneto, 30016, Italia', 45.4954257, 12.6184648, '2026-05-13', '2026-05-15', '2026-04-30 09:12:17');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_users_pair` (`user1_id`,`user2_id`),
  ADD KEY `idx_user1` (`user1_id`),
  ADD KEY `idx_user2` (`user2_id`);

--
-- Indici per le tabelle `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_sender` (`sender_id`);

--
-- Indici per le tabelle `pacchetti`
--
ALTER TABLE `pacchetti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_localita` (`localita`);

--
-- Indici per le tabelle `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pacchetto` (`pacchetto_id`);

--
-- Indici per le tabelle `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msg_ticket` (`ticket_id`),
  ADD KEY `idx_msg_sender` (`sender_id`);

--
-- Indici per le tabelle `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket_user` (`user_id`),
  ADD KEY `idx_ticket_stato` (`stato`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_lingua` (`lingua`),
  ADD KEY `idx_nazionalita` (`nazionalita`);

--
-- Indici per le tabelle `viaggi`
--
ALTER TABLE `viaggi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`data_inizio`,`data_fine`),
  ADD KEY `idx_user_viaggi` (`user_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `pacchetti`
--
ALTER TABLE `pacchetti`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT per la tabella `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `viaggi`
--
ALTER TABLE `viaggi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conv_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conv_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  ADD CONSTRAINT `fk_pkg_img` FOREIGN KEY (`pacchetto_id`) REFERENCES `pacchetti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `viaggi`
--
ALTER TABLE `viaggi`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
