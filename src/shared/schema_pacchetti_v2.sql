-- Aggiornamento tabella pacchetti
DROP TABLE IF EXISTS `pacchetti_immagini`;
DROP TABLE IF EXISTS `pacchetti`;

CREATE TABLE `pacchetti` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titolo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `localita` varchar(255) NOT NULL,
  `latitudine` decimal(10,7) DEFAULT NULL,
  `longitudine` decimal(10,7) DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `nome_agenzia` varchar(255) NOT NULL,
  `email_agenzia` varchar(150) NOT NULL,
  `link_esterno` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_localita` (`localita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pacchetti_immagini` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pacchetto_id` int(10) UNSIGNED NOT NULL,
  `percorso` varchar(255) NOT NULL,
  `is_copertina` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_pacchetto` (`pacchetto_id`),
  CONSTRAINT `fk_pkg_img` FOREIGN KEY (`pacchetto_id`) REFERENCES `pacchetti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
