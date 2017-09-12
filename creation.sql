CREATE DATABASE IF NOT EXISTS linkedinBot;
use linkedinBot;

CREATE TABLE `connect_asked` (
	`ID` int NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`profile_id` varchar(255) NOT NULL,
	`request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);