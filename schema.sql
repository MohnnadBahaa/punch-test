CREATE TABLE `merchants`
(
 `id`          int NOT NULL AUTO_INCREMENT ,
 `name`        varchar
(100) NOT NULL ,
 `index_order` int NOT NULL ,
 `updated_at`  datetime NOT NULL ,
 `created_at`  datetime NOT NULL ,

PRIMARY KEY
(`id`)
);


-- ************************************** `users`

CREATE TABLE `users`
(
 `id`         int NOT NULL AUTO_INCREMENT ,
 `name`       varchar
(100) NOT NULL ,
 `username`   varchar
(100) NOT NULL ,
 `email`      varchar
(100) NOT NULL ,
 `dob`        date NULL ,
 `gender`     enum
('m','f') NULL ,
 `password`   varchar
(100) NOT NULL ,
 `updated_at` datetime NOT NULL ,
 `created_at` datetime NOT NULL ,

PRIMARY KEY
(`id`)
);

-- ****************** SqlDBM: MySQL ******************;
-- ***************************************************;


-- ************************************** `merc_usr`

CREATE TABLE `merc_usr`
(
 `user_id` int NOT NULL ,
 `id`      int NOT NULL AUTO_INCREMENT ,
 `merc_id` int NOT NULL ,

PRIMARY KEY
(`id`),
KEY `fkIdx_35`
(`user_id`),
CONSTRAINT `FK_35` FOREIGN KEY `fkIdx_35`
(`user_id`) REFERENCES `users`
(`id`),
KEY `fkIdx_38`
(`merc_id`),
CONSTRAINT `FK_38` FOREIGN KEY `fkIdx_38`
(`merc_id`) REFERENCES `merchants`
(`id`)
);
