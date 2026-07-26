-- Migration: Add remember_token column to users table for remember-me authentication
ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) DEFAULT NULL AFTER `status`;
