DROP DATABASE IF EXISTS pwboxdb;
CREATE DATABASE IF NOT EXISTS pwboxdb;
USE pwboxdb;

# Table which'll store the information of the users
CREATE TABLE users (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    hash_id VARCHAR(40) NOT NULL UNIQUE,
    username VARCHAR(25) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    birth_date VARCHAR(20) NOT NULL DEFAULT '',
    password VARCHAR(150) NOT NULL DEFAULT '',
    folder_path VARCHAR(255) NOT NULL DEFAULT '',
    remaining_storage INT NOT NULL DEFAULT 1000000000,		# In bytes (1 GB = 1000000 B)
    PRIMARY KEY(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

# Table which'll store the information of the folders
CREATE TABLE folders (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    hash_id VARCHAR(40) NOT NULL UNIQUE,
    user_id INT(11) UNSIGNED NOT NULL,
    folder_name VARCHAR(255) NOT NULL DEFAULT '',
    root_folder BOOLEAN DEFAULT FALSE,
    shared BOOLEAN DEFAULT FALSE,
    PRIMARY KEY(id),
    FOREIGN KEY(user_id)
		REFERENCES users(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

# Table which'll store the information of the subfolders
CREATE TABLE subfolders (
    parent_folder INT(11) UNSIGNED NOT NULL,
    child_folder INT(11) UNSIGNED NOT NULL,
    FOREIGN KEY(parent_folder)
		REFERENCES folders(id),
	FOREIGN KEY(child_folder)
		REFERENCES folders(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

# Table which'll store the information of the files
CREATE TABLE files (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    hash_id VARCHAR(40) NOT NULL UNIQUE,
    folder_id INT(11) UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL DEFAULT '',
    file_path VARCHAR(255) NOT NULL DEFAULT '',
    shared BOOLEAN DEFAULT FALSE,
    PRIMARY KEY(id),
    FOREIGN KEY(folder_id)
		REFERENCES folders(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

# Table which'll store the information of the shared folders
CREATE TABLE shared (
    folder_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    admin BOOLEAN DEFAULT FALSE,
    FOREIGN KEY(folder_id)
		REFERENCES folders(id),
	FOREIGN KEY(user_id)
		REFERENCES users(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

SELECT * FROM users;
SELECT * FROM folders;
SELECT * FROM subfolders;
SELECT * FROM files;
SELECT * FROM shared;