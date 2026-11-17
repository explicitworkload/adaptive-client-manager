-- Adaptive Client Manager 2.0: one-time change for an EXISTING database.
-- Run this in phpMyAdmin (select your database, then the SQL tab).
-- Back up the database first (phpMyAdmin: Export).
--
-- The old app stored 40-character sha1 password hashes. Version 2.0 upgrades
-- each user to a bcrypt hash (60 characters) when they next log in, so the
-- column must be wider. Until this is run, logins still work but passwords
-- stay on the old sha1 hashes.

ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;
