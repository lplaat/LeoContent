CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `is_admin` bit(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`id`, `username`, `password`, `is_admin`) VALUES
  (1, 'admin', '$2y$10$OUyDStQk5zZZyeLlvKJ18OaoMnqwG1HkhQQ0lGozm7LlE43IPzST.', b'1');

CREATE TABLE `content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `origin_id` int NOT NULL,
  `title` text,
  `description` text,
  `release_date` datetime DEFAULT NULL,
  `adult_only` tinyint(1) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `type` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `content_image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `path` text NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `media_directory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `type` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `media_directory_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `job` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` tinyint NOT NULL,
  `type` tinyint DEFAULT NULL,
  `parent_id` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `content` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `content_image` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `job` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `media` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
ALTER TABLE `media_directory` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
ALTER TABLE `user` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;