USE zipvilla;
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(50) NOT NULL,
  salt varchar(50) NOT NULL,
  role varchar(50) NOT NULL,
  date_created datetime NOT NULL,
  PRIMARY KEY (id)
);
-- create user admin with password zipvilla and salt '1234567890abcdef'
INSERT INTO users (username, password, salt, role, date_created)
VALUES ('admin', SHA1('zipvilla12ab34cd56ef78gh90ij'), '12ab34cd56ef78gh90ij', 'administrator', NOW());
