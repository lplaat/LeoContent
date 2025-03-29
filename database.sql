CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `is_admin` bit(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

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
  `is_prepared` bit(1) DEFAULT NULL,
  `episode` int DEFAULT NULL,
  `season` int DEFAULT NULL,
  'total_episodes' int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `type` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `content_image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `path` text NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `media_directory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `type` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `duration` int NOT NULL,
  `quality` text NOT NULL,
  `media_directory_id` int DEFAULT NULL,
  `content_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `job` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` tinyint NOT NULL,
  `type` tinyint DEFAULT NULL,
  `parent_id` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `stream` (
  `id` int NOT NULL,
  `code` text NOT NULL,
  `user_id` int NOT NULL,
  `media_id` int NOT NULL,
  `alive` bit(1) NOT NULL DEFAULT b'1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `stream_config` (
  `id` INT NOT NULL, 
  `name` TEXT NOT NULL , 
  `value` TEXT NOT NULL , 
  PRIMARY KEY (`id`)
) ENGINE = InnoDB; 

ALTER TABLE `stream` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `stream_config` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `content` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `content_image` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `job` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `media` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `media_directory` MODIFY `id` int NOT NULL AUTO_INCREMENT;
ALTER TABLE `user` MODIFY `id` int NOT NULL AUTO_INCREMENT;

COMMIT;