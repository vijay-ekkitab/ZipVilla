-- Initializes a New Zipvilla Database;

drop database if exists zipvilla;
create database zipvilla;

-- Create an admin user;
GRANT ALL PRIVILEGES  ON zipvilla.* TO 'admin'@'%' IDENTIFIED BY 'zipvilla' WITH GRANT OPTION;

source users.sql;
