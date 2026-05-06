-- Migrazione: Aggiunta supporto chat dedicate per pacchetti e ruolo agenzia

-- 1. Aggiungi colonna user_id a pacchetti per collegare a utente agenzia
ALTER TABLE `pacchetti` ADD COLUMN `user_id` int(10) UNSIGNED DEFAULT NULL AFTER `email_agenzia`;
ALTER TABLE `pacchetti` ADD CONSTRAINT `fk_pkg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 2. Crea tabella per conversazioni dedicate ai pacchetti
CREATE TABLE IF NOT EXISTS `package_conversations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pacchetto_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `agenzia_user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `idx_pkg_user_pair` (`pacchetto_id`, `user_id`, `agenzia_user_id`),
  KEY `idx_pkg` (`pacchetto_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_agenzia` (`agenzia_user_id`),
  CONSTRAINT `fk_pkg_conv_pkg` FOREIGN KEY (`pacchetto_id`) REFERENCES `pacchetti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkg_conv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkg_conv_agenzia` FOREIGN KEY (`agenzia_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crea tabella per messaggi dedicati ai pacchetti
CREATE TABLE IF NOT EXISTS `package_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `package_conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `idx_pkg_conv` (`package_conversation_id`),
  KEY `idx_sender` (`sender_id`),
  CONSTRAINT `fk_pkg_msg_conv` FOREIGN KEY (`package_conversation_id`) REFERENCES `package_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkg_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
