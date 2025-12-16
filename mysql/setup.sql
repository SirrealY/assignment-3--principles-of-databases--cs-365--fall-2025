/*
NOTE:Run this in the MySQL space to stand up the database. MUST be in the same session when running commands.sql.
*/


-- Create Database
DROP DATABASE IF EXISTS student_passwords;
CREATE DATABASE student_passwords
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_bin;
USE student_passwords;

-- Create database user and grant permissions

DROP USER IF EXISTS 'passwords_user'@'localhost';
CREATE USER 'passwords_user'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON student_passwords.* TO 'passwords_user'@'localhost';
FLUSH PRIVILEGES;

-- Setup Password Encryption
SET block_encryption_mode = 'aes-256-cbc';
SET @key_str = UNHEX(SHA2('SEUZ', 512));
SET @init_vector = RANDOM_BYTES(16);

-- Create Tables

CREATE TABLE IF NOT EXISTS users (
  user_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(100)   NOT NULL,
  first_name VARCHAR(256)   NOT NULL,
  last_name  VARCHAR(256)   NOT NULL,
  email      VARCHAR(256)   NOT NULL,
  created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_email (email)
);

CREATE TABLE IF NOT EXISTS websites (
  website_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  url        VARCHAR(256)   NOT NULL,
  name       VARCHAR(256)   NOT NULL,
  created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_website_url (url)
);

CREATE TABLE IF NOT EXISTS credentials (
  credential_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED       NOT NULL,
  website_id    INT UNSIGNED       NOT NULL,
  site_username VARCHAR(100)       NOT NULL,
  url           VARCHAR(256)       NOT NULL, -- Redundant but useful for quick lookups
  passwords_enc VARBINARY(512)     NOT NULL,
  comment VARCHAR(255)             DEFAULT NULL,
  created_at    TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)            REFERENCES users(user_id),
  FOREIGN KEY (website_id)         REFERENCES websites(website_id)
);

INSERT INTO users (username, first_name, last_name, email) VALUES
  ('lebron123',     'LeBron',   'James',   'bron@gmail.com'),
  ('steph456',      'Stephen',  'Curry',   'curry@yahoo.com'),
  ('spiderman2030', 'Peter',    'Parker',  'spiderguy22@gmail.com'),
  ('youngman789',   'Sirreal',  'Young',   'upsidedown2500@hartford.edu');

INSERT INTO websites (url, name) VALUES
  ('https://www.mysql.com',                'MySQL'),
  ('https://www.wizard101.com',            'Wizard101'),
  ('https://www.pirate101.com',            'Pirate101'),
  ('https://www.hulu.com',                 'Hulu'),
  ('https://www.minecraft.net',            'Minecraft'),
  ('https://www.leagueoflegends.com',      'League of Legends'),
  ('https://www.fortnite.com',             'Fortnite'),
  ('https://www.callofduty.com',           'Call of Duty'),
  ('https://www.elderscrollsonline.com',   'Elder Scrolls Online'),
  ('https://www.cyberpunk.net',            'Cyberpunk 2077');

INSERT INTO credentials (user_id, website_id, site_username, url, passwords_enc) VALUES
  (1,  1, 'lebron123',     'https://www.mysql.com',                AES_ENCRYPT('mysqlR0cks!',      @key_str, @init_vector)),
  (1,  2, 'lebron123',     'https://www.wizard101.com',            AES_ENCRYPT('w1zardP@ss',      @key_str, @init_vector)),
  (2,  3, 'steph456',      'https://www.pirate101.com',            AES_ENCRYPT('pirateP@ss',      @key_str, @init_vector)),
  (2,  4, 'steph456',      'https://www.hulu.com',                 AES_ENCRYPT('huluL0ve$',       @key_str, @init_vector)),
  (3,  5, 'spiderman2030', 'https://www.minecraft.net',            AES_ENCRYPT('mineCr@ft!',      @key_str, @init_vector)),
  (3,  6, 'spiderman2030', 'https://www.leagueoflegends.com',      AES_ENCRYPT('l3gendsRul3!',    @key_str, @init_vector)),
  (4,  7, 'youngman789',   'https://www.fortnite.com',             AES_ENCRYPT('f0rtnite!',       @key_str, @init_vector)),
  (4,  8, 'youngman789',   'https://www.callofduty.com',           AES_ENCRYPT('c@ll0fDutY!',     @key_str, @init_vector)),
  (4,  9, 'youngman789',   'https://www.elderscrollsonline.com',   AES_ENCRYPT('3lderscr0lls!',   @key_str, @init_vector)),
  (4, 10, 'youngman789',   'https://www.cyberpunk.net',            AES_ENCRYPT('cyb3rpunk!',      @key_str, @init_vector));
