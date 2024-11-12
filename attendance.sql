	CREATE TABLE `attendance_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `passcode` int(200) DEFAULT NULL,
 `name` varchar(100) DEFAULT NULL,
 `time_in` datetime DEFAULT NULL,
 `time_in_picture` longblob DEFAULT NULL,
 `time_out` datetime DEFAULT NULL,
 `time_out_picture` longblob DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `employees` (
 `id` int(200) NOT NULL AUTO_INCREMENT,
 `passcode` int(200) NOT NULL,
 `name` varchar(100) NOT NULL,
 `picture` longblob DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci