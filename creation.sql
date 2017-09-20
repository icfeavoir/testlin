CREATE DATABASE IF NOT EXISTS linkedinBot;
use linkedinBot;

CREATE TABLE IF NOT EXISTS `bot_on_off` (
	`isOn` bit NOT NULL,
	`last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `connect_asked` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`profile_id` varchar(255) NOT NULL,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `connect_list` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`profile_id` varchar(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `msg_sent` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`profile_id` varchar(255) NOT NULL,
	`conv_id` varchar(255) NOT NULL,
	`msg_id` varchar(255) NOT NULL,
	`template_msg` int NOT NULL DEFAULT 0,
	`msg` varchar(255) NOT NULL,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `msg_received` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`profile_id` varchar(255) NOT NULL,
	`conv_id` varchar(255) NOT NULL,
	`msg_id` varchar(255) NOT NULL,
	`template_msg` int NOT NULL DEFAULT 0,
	`msg` varchar(255) NOT NULL,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `key_word_list` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`key_word` varchar(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `msg_template` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`msg` varchar(255) NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- DEFAULT VALUES
INSERT INTO bot_on_off (isOn) SELECT 0 WHERE NOT EXISTS (SELECT * FROM msg_template);
INSERT INTO msg_template (msg) SELECT 'default' WHERE NOT EXISTS (SELECT * FROM msg_template);
