-- Active: 1687679119490@@containers-us-west-58.railway.app@8006@railway
CREATE TABLE `user` (
  `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
   `mail` VARCHAR(255) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `security_question` VARCHAR(255) NOT NULL,
   `security_answer` VARCHAR(255) NOT NULL,
   `lastname` VARCHAR(255) NOT NULL,
   `firstname` VARCHAR(255) NOT NULL,
   `birth_date` VARCHAR(10) NOT NULL,
   `phone_number` VARCHAR(10) NOT NULL,
   `pp_image` VARCHAR(255),
   `client_role` ENUM('0', '1') DEFAULT '1',
   `management_role` ENUM('0', '1') DEFAULT '0',
   `maintenance_role` ENUM('0', '1') DEFAULT '0',
   `admin_role` ENUM('0', '1') DEFAULT '0',
   `status` ENUM('0', '1') DEFAULT '1',
   `registration_date_time` DATETIME
);

CREATE TABLE `token` (
  `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `token` VARCHAR(255)
);

CREATE TABLE `housing` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `place` VARCHAR(255) NOT NULL,
    `district` VARCHAR(255) NOT NULL,
    `number_of_pieces` SMALLINT NOT NULL,
    `area` SMALLINT NOT NULL,
    `price` VARCHAR(10) NOT NULL,
    `description` TEXT NOT NULL,
    `capacity` SMALLINT NOT NULL,
    `type` VARCHAR(32) NOT NULL
);

CREATE TABLE `housing_image` (
  `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `housing_id` INT(11) UNSIGNED NOT NULL,
  `image` VARCHAR(255) NOT NULL
);

CREATE TABLE `housing_service` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `housing_id` INT(11) UNSIGNED NOT NULL,
    `concierge` ENUM('0', '1') DEFAULT '1',
    `driver` ENUM('0', '1') DEFAULT '1',
    `chef` ENUM('0', '1') DEFAULT '0',
    `babysitter` ENUM('0', '1') DEFAULT '0',
    `guide` ENUM('0', '1') DEFAULT '0'
);

CREATE TABLE `booking` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `housing_id` INT(11) UNSIGNED NOT NULL,
    `price` INT(10) NOT NULL,
    `start_date_time` VARCHAR(10) NOT NULL,
    `end_date_time` VARCHAR(10) NOT NULL,
    `booking_date_time` DATETIME
);

CREATE TABLE `booking_messaging` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `booking_id` INT(11) UNSIGNED NOT NULL
);

CREATE TABLE `housing_review` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `housing_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `review` TEXT,
    `review_date_time` DATETIME
);

CREATE TABLE `maintenance` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `housing_id` INT(11) UNSIGNED NOT NULL,
    `schedule_date` VARCHAR(10),
    `status` VARCHAR(10),
    `title` VARCHAR(32)
);

CREATE TABLE `maintenance_note` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `maintenance_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `content` TEXT
);

CREATE TABLE `favorite` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `housing_id` INT(11) UNSIGNED NOT NULL
);

CREATE TABLE `booking_service` (
    `id` INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `booking_id` INT(11) UNSIGNED NOT NULL,
    `concierge` ENUM('0', '1') DEFAULT '0',
    `driver` ENUM('0', '1') DEFAULT '0',
    `chef` ENUM('0', '1') DEFAULT '0',
    `babysitter` ENUM('0', '1') DEFAULT '0',
    `guide` ENUM('0', '1') DEFAULT '0'
);