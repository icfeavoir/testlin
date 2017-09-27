CREATE DATABASE IF NOT EXISTS linkedinBot;
use linkedinBot;

CREATE TABLE IF NOT EXISTS `bot_on_off` (
	`isOn` boolean NOT NULL,
	`last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `bot_disconnect` (
	`is_disconnect` boolean NOT NULL,
	`last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `bot_action` (
	`action` text NOT NULL,
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

CREATE TABLE IF NOT EXISTS `msg_conversation` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`by_bot` boolean NOT NULL,
	`profile_id` varchar(255) NOT NULL,
	`conv_id` varchar(255) NOT NULL,
	`msg_id` varchar(255) NOT NULL,
	`template_msg` int NOT NULL DEFAULT 0,
	`msg` text NOT NULL,
	`watson_msg` boolean NOT NULL DEFAULT false,
	`watson_try` boolean NOT NULL DEFAULT false,
	`watson_context` text DEFAULT NULL,
	`is_read` boolean NOT NULL DEFAULT false,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `key_word_list` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`key_word` varchar(255) NOT NULL,
	`done` boolean NOT NULL DEFAULT false
);

CREATE TABLE IF NOT EXISTS `msg_template` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`msg` text NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`active` boolean NOT NULL DEFAULT true
);


-- DEFAULT VALUES IF NOT ALREADY INSERTED
INSERT INTO bot_on_off (isOn) SELECT 0 WHERE NOT EXISTS (SELECT * FROM bot_on_off);
INSERT INTO bot_disconnect (is_disconnect) SELECT 0 WHERE NOT EXISTS (SELECT * FROM bot_disconnect);
INSERT INTO msg_template (msg) SELECT 'YouPic' WHERE NOT EXISTS (SELECT * FROM msg_template);
INSERT INTO bot_action (action) SELECT 'Nothing' WHERE NOT EXISTS (SELECT * FROM bot_action);
