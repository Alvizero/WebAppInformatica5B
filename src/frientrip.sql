-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 25, 2026 alle 08:38
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
-- Database: `frientrip`
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
(2, 6, 9, '2026-05-14 08:52:48');

-- --------------------------------------------------------

--
-- Struttura della tabella `lingue`
--

CREATE TABLE `lingue` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `lingue`
--

INSERT INTO `lingue` (`id`, `nome`) VALUES
(28, 'Bahasa Indonesia'),
(29, 'Bahasa Melayu'),
(36, 'Català'),
(18, 'Čeština'),
(26, 'Dansk'),
(5, 'Deutsch'),
(39, 'Eesti'),
(2, 'English'),
(3, 'Español'),
(4, 'Français'),
(20, 'Hrvatski'),
(1, 'Italiano'),
(32, 'Kiswahili'),
(37, 'Latviešu'),
(38, 'Lietuvių'),
(17, 'Magyar'),
(16, 'Nederlands'),
(25, 'Norsk'),
(14, 'Polski'),
(6, 'Português'),
(15, 'Română'),
(19, 'Slovenčina'),
(35, 'Slovenščina'),
(21, 'Srpski'),
(27, 'Suomi'),
(24, 'Svenska'),
(30, 'Tiếng Việt'),
(13, 'Türkçe'),
(23, 'Ελληνικά'),
(22, 'Български'),
(7, 'Русский'),
(34, 'Українська'),
(33, 'עברית'),
(11, 'العربية'),
(40, 'فارسی'),
(12, 'हिन्दी'),
(31, 'ภาษาไทย'),
(8, '中文'),
(9, '日本語'),
(10, '한국어');

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
(11, 2, 9, 'hduUI', '2026-05-21 08:50:40');

-- --------------------------------------------------------

--
-- Struttura della tabella `nazionalita`
--

CREATE TABLE `nazionalita` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `nazionalita`
--

INSERT INTO `nazionalita` (`id`, `nome`) VALUES
(1, 'Afghana'),
(2, 'Albanese'),
(3, 'Algerina'),
(4, 'Andorrana'),
(5, 'Angolana'),
(6, 'Antiguana'),
(7, 'Argentina'),
(8, 'Armena'),
(9, 'Australiana'),
(10, 'Austriaca'),
(11, 'Azerbaigiana'),
(12, 'Bahamense'),
(13, 'Bahreinita'),
(14, 'Bangladese'),
(15, 'Barbadiana'),
(16, 'Belga'),
(17, 'Beliziana'),
(18, 'Beninese'),
(20, 'Bielorussa'),
(21, 'Birmana'),
(22, 'Boliviana'),
(23, 'Bosniaca'),
(24, 'Botswana'),
(25, 'Brasiliana'),
(26, 'Britannica'),
(27, 'Bruneiana'),
(28, 'Bulgara'),
(29, 'Burkinabé'),
(30, 'Burundese'),
(19, 'Butanese'),
(31, 'Cambogiana'),
(32, 'Camerunese'),
(33, 'Canadese'),
(34, 'Capoverdiana'),
(35, 'Centrafricana'),
(36, 'Ciadiana'),
(37, 'Cilena'),
(38, 'Cinese'),
(39, 'Cipriota'),
(40, 'Colombiana'),
(41, 'Comoriana'),
(42, 'Congolese'),
(43, 'Coreana'),
(44, 'Costaricana'),
(45, 'Croata'),
(46, 'Cubana'),
(47, 'Danese'),
(48, 'Dominicana'),
(49, 'Ecuadoregna'),
(50, 'Egiziana'),
(52, 'Emiratense'),
(53, 'Eritrea'),
(54, 'Estone'),
(55, 'Etiope'),
(56, 'Fidiana'),
(57, 'Filippina'),
(58, 'Finlandese'),
(59, 'Francese'),
(60, 'Gabonese'),
(61, 'Gambiana'),
(62, 'Georgiana'),
(64, 'Ghanese'),
(65, 'Giamaicana'),
(66, 'Giapponese'),
(67, 'Gibutiana'),
(68, 'Giordana'),
(69, 'Greca'),
(70, 'Grenadina'),
(71, 'Guatemalteca'),
(72, 'Guineana'),
(73, 'Guineense'),
(74, 'Guyanese'),
(75, 'Haitiana'),
(76, 'Honduregna'),
(77, 'Indiana'),
(78, 'Indonesiana'),
(80, 'Iraena'),
(79, 'Iraniana'),
(81, 'Irlandese'),
(82, 'Islandese'),
(83, 'Israeliana'),
(84, 'Italiana'),
(85, 'Ivoriana'),
(86, 'Kazaka'),
(87, 'Keniana'),
(88, 'Kirghisa'),
(89, 'Kiribatiana'),
(90, 'Kuwaitiana'),
(91, 'Laotiana'),
(92, 'Lettone'),
(93, 'Libanese'),
(94, 'Liberiana'),
(95, 'Libica'),
(96, 'Liechtensteinese'),
(97, 'Lituana'),
(98, 'Lussemburghese'),
(99, 'Macedone'),
(101, 'Malawiana'),
(103, 'Maldiviana'),
(102, 'Malese'),
(100, 'Malgascia'),
(104, 'Maliana'),
(105, 'Maltese'),
(106, 'Marocchina'),
(107, 'Marshallese'),
(109, 'Mauritana'),
(108, 'Mauriziana'),
(110, 'Messicana'),
(111, 'Micronesiana'),
(112, 'Moldava'),
(113, 'Monegasca'),
(114, 'Mongola'),
(115, 'Montenegrina'),
(116, 'Mozambicana'),
(117, 'Namibiana'),
(118, 'Nauruana'),
(124, 'Neozelandese'),
(119, 'Nepalese'),
(120, 'Nicaraguense'),
(122, 'Nigeriana'),
(121, 'Nigerina'),
(123, 'Norvegese'),
(126, 'Olandese'),
(125, 'Omanita'),
(127, 'Pakistana'),
(128, 'Palauana'),
(129, 'Palestinese'),
(130, 'Panamense'),
(131, 'Papuana'),
(132, 'Paraguaiana'),
(133, 'Peruviana'),
(134, 'Polacca'),
(135, 'Portoghese'),
(136, 'Portoricana'),
(137, 'Qatariota'),
(138, 'Romena'),
(139, 'Ruandese'),
(140, 'Russa'),
(51, 'Salvadoregna'),
(141, 'Samoana'),
(143, 'Santalucciana'),
(144, 'Saotomense'),
(145, 'Saudita'),
(146, 'Senegalese'),
(147, 'Serba'),
(148, 'Seychellese'),
(149, 'Sierraleonese'),
(150, 'Singaporiana'),
(151, 'Siriana'),
(152, 'Slovacca'),
(153, 'Slovena'),
(142, 'Smarinese'),
(154, 'Somala'),
(155, 'Spagnola'),
(156, 'Sri lankese'),
(157, 'Statunitense'),
(160, 'Sud-sudanese'),
(158, 'Sudafricana'),
(159, 'Sudanese'),
(161, 'Surinamense'),
(162, 'Svedese'),
(163, 'Svizzera'),
(164, 'Swati'),
(165, 'Tagika'),
(166, 'Tanzaniana'),
(63, 'Tedesea'),
(167, 'Thailandese'),
(168, 'Togolese'),
(169, 'Tongana'),
(170, 'Trinidadiana'),
(171, 'Tunisina'),
(172, 'Turca'),
(173, 'Turkmena'),
(174, 'Tuvaluana'),
(175, 'Ucraina'),
(176, 'Ugandese'),
(177, 'Ungherese'),
(178, 'Uruguaiana'),
(179, 'Uzbeka'),
(180, 'Vanuatuana'),
(181, 'Venezuelana'),
(182, 'Vietnamita'),
(183, 'Yemenita'),
(184, 'Zambiana'),
(185, 'Zimbabwese');

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
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `link_esterno` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `pacchetti`
--

INSERT INTO `pacchetti` (`id`, `titolo`, `descrizione`, `localita`, `latitudine`, `longitudine`, `prezzo`, `nome_agenzia`, `email_agenzia`, `user_id`, `link_esterno`, `created_at`) VALUES
(2, 'pacchetto jesolo', 'jj', 'Jesolo, Venezia, Veneto, 30016, Italia', 45.5347443, 12.6538449, 3000.00, 'Diego Bincoletto', 'binco@i.it', 8, '', '2026-05-06 08:48:20');

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
-- Struttura della tabella `package_conversations`
--

CREATE TABLE `package_conversations` (
  `id` int(10) UNSIGNED NOT NULL,
  `pacchetto_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `agenzia_user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `package_messages`
--

CREATE TABLE `package_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `package_conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `nazionalita_id` int(10) UNSIGNED NOT NULL,
  `lingua_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `livello_utente` tinyint(3) UNSIGNED NOT NULL DEFAULT 255,
  `foto_profilo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `nome`, `cognome`, `email`, `password`, `nazionalita_id`, `lingua_id`, `created_at`, `livello_utente`, `foto_profilo`) VALUES
(6, 'Alvise', 'Sacconato', 'alviseparide@i.it', '$2y$10$PqjLtOdtAw9V28LEwLkUFu.Yqlud9ohIBRe88NE4LkJrO1Iv4EeSm', 62, 1, '2026-04-16 08:28:08', 0, NULL),
(8, 'Diego', 'Bincoletto', 'binco@i.it', '$2y$10$7rOJWvF/LbtX9UTiiw8xqesU1HXKj12ZaAmzvF0Hj9O40XgucSv5W', 84, 1, '2026-05-06 06:53:08', 3, NULL),
(9, 'Mario', 'Rossi', 'mario@mario.it', '$2y$10$QQOX37DcFH/oFnu1Env/FudGsWSHDG5Ry56h7JDt99KAZ4.Se06QC', 2, 1, '2026-05-14 08:37:16', 255, NULL),
(10, 'Marioòò', 'kkk', 'Marioo@Mariooo.it', '$2y$10$FhJeN/MIWb9UDR4WUJ0nd.QkRsBp8TzipaOewDL4oVy4HIc5G3o5a', 2, 20, '2026-05-18 06:50:40', 255, NULL);

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
(15, 6, 'Excelsior, 3, Via Zara, Lido di Jesolo, Jesolo, Venezia, Veneto, 30016, Italia', 45.4954257, 12.6184648, '2026-05-13', '2026-05-15', '2026-04-30 09:12:17'),
(16, 6, 'Milano, Rodano, Milano, Lombardia, Italia', 45.4641943, 9.1896346, '2026-05-21', '2026-06-23', '2026-05-14 08:34:03');

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
-- Indici per le tabelle `lingue`
--
ALTER TABLE `lingue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nome` (`nome`);

--
-- Indici per le tabelle `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_sender` (`sender_id`);

--
-- Indici per le tabelle `nazionalita`
--
ALTER TABLE `nazionalita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nome` (`nome`);

--
-- Indici per le tabelle `pacchetti`
--
ALTER TABLE `pacchetti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_localita` (`localita`),
  ADD KEY `fk_pkg_user` (`user_id`);

--
-- Indici per le tabelle `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pacchetto` (`pacchetto_id`);

--
-- Indici per le tabelle `package_conversations`
--
ALTER TABLE `package_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_pkg_user_pair` (`pacchetto_id`,`user_id`,`agenzia_user_id`),
  ADD KEY `idx_pkg` (`pacchetto_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_agenzia` (`agenzia_user_id`);

--
-- Indici per le tabelle `package_messages`
--
ALTER TABLE `package_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pkg_conv` (`package_conversation_id`),
  ADD KEY `idx_sender` (`sender_id`);

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
  ADD KEY `idx_lingua` (`lingua_id`),
  ADD KEY `fk_user_nazionalita` (`nazionalita_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `lingue`
--
ALTER TABLE `lingue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT per la tabella `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `nazionalita`
--
ALTER TABLE `nazionalita`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT per la tabella `pacchetti`
--
ALTER TABLE `pacchetti`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `package_conversations`
--
ALTER TABLE `package_conversations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `package_messages`
--
ALTER TABLE `package_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT per la tabella `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `viaggi`
--
ALTER TABLE `viaggi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
-- Limiti per la tabella `pacchetti`
--
ALTER TABLE `pacchetti`
  ADD CONSTRAINT `fk_pkg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `pacchetti_immagini`
--
ALTER TABLE `pacchetti_immagini`
  ADD CONSTRAINT `fk_pkg_img` FOREIGN KEY (`pacchetto_id`) REFERENCES `pacchetti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `package_conversations`
--
ALTER TABLE `package_conversations`
  ADD CONSTRAINT `fk_pkg_conv_agenzia` FOREIGN KEY (`agenzia_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pkg_conv_pkg` FOREIGN KEY (`pacchetto_id`) REFERENCES `pacchetti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pkg_conv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `package_messages`
--
ALTER TABLE `package_messages`
  ADD CONSTRAINT `fk_pkg_msg_conv` FOREIGN KEY (`package_conversation_id`) REFERENCES `package_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pkg_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Limiti per la tabella `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_lingua` FOREIGN KEY (`lingua_id`) REFERENCES `lingue` (`id`),
  ADD CONSTRAINT `fk_user_nazionalita` FOREIGN KEY (`nazionalita_id`) REFERENCES `nazionalita` (`id`);

--
-- Limiti per la tabella `viaggi`
--
ALTER TABLE `viaggi`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
