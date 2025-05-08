-- Database: note_management

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

/* --- Tables --- */

CREATE TABLE `users` (
    `id`             INT(11)      NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(255) NOT NULL,
    `display_name`   VARCHAR(255) NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `is_active`      TINYINT(4)   DEFAULT 0,
    `activation_token` VARCHAR(255) DEFAULT NULL,
    `preferences`    TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    `image`          VARCHAR(255) DEFAULT '',
    `theme`          ENUM('light', 'dark') DEFAULT 'light',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notes` (
    `id`           INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`      INT(11)      NOT NULL,
    `title`        VARCHAR(255) NOT NULL,
    `content`      TEXT         DEFAULT NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `modified_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    `is_pinned`    TINYINT(4)   DEFAULT 0,
    `category`     VARCHAR(255) DEFAULT NULL,
    `tags`         VARCHAR(255) DEFAULT NULL,
    `password`     VARCHAR(50)  DEFAULT NULL,
    `image`        VARCHAR(255) DEFAULT NULL,
    `font_size`    VARCHAR(20)  DEFAULT '16px',
    `note_color`   VARCHAR(7)   DEFAULT '#ffffff',
    `status_pass`  TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_title` (`title`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_user_id_notes` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `note_history` (
    `id`        INT(11)      NOT NULL AUTO_INCREMENT,
    `note_id`   INT(11)      NOT NULL,
    `user_id`   INT(11)      NOT NULL,
    `action`    VARCHAR(255) NOT NULL,
    `timestamp` DATETIME     DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (`id`),
    KEY `idx_note_id` (`note_id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_note_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tags` (
    `id`      INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id` INT(11)      NOT NULL,
    `name`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_tags_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `note_tags` (
    `note_id` INT(11) NOT NULL,
    `tag_id`  INT(11) NOT NULL,
    PRIMARY KEY (`note_id`, `tag_id`),
    KEY `idx_tag_id` (`tag_id`),
    CONSTRAINT `fk_note_tags_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_note_tags_tag`  FOREIGN KEY (`tag_id`)  REFERENCES `tags`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `shared_notes` (
    `id`              INT(11)      NOT NULL AUTO_INCREMENT,
    `note_id`         INT(11)      NOT NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `permission`      ENUM('read', 'edit') NOT NULL,
    `access_password` VARCHAR(255) NOT NULL,
    `created_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP(),
    `shared_by`       INT(11)      DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_shared_by` (`shared_by`),
    CONSTRAINT `fk_shared_notes_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `expires`    DATETIME     NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;