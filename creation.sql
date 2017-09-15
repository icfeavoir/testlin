CREATE DATABASE IF NOT EXISTS linkedinBot;
use linkedinBot;

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
	`msg` varchar(255) NOT NULL,
	`conv_id` varchar(255) NOT NULL,
	`msg_id` varchar(255) NOT NULL,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `key_word_list` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`key_word` varchar(255) NOT NULL
);