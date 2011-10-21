USE zipvilla;
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(50) NOT NULL,
  firstname varchar(50) NOT NULL,
  lastname varchar(50) NOT NULL,
  email varchar(50) NOT NULL,
  salt varchar(50) NOT NULL,
  role varchar(50) NOT NULL,
  date_modified datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (`username`)
);
-- create user admin with password zipvilla and salt '1234567890abcdef'
INSERT INTO users (username, password, firstname, lastname, email, salt, role, date_modified)
VALUES ('admin@zipvilla.com', SHA1('zipvilla12ab34cd56ef78gh90ij'), 'System', 'Administrator', 'admin@zipvilla.com', '12ab34cd56ef78gh90ij', 'administrator', NOW());
