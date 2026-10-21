CREATE DATABASE IF NOT EXISTS classcloud
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classcloud;

CREATE TABLE IF NOT EXISTS schools (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  quota_vms INT UNSIGNED NOT NULL DEFAULT 10,
  quota_containers INT UNSIGNED NOT NULL DEFAULT 10,
  quota_vcpu INT UNSIGNED NOT NULL DEFAULT 16,
  quota_ram_mb INT UNSIGNED NOT NULL DEFAULT 32768,
  quota_disk_gb INT UNSIGNED NOT NULL DEFAULT 200,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','student') NOT NULL DEFAULT 'student',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_school FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resources (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  school_id BIGINT UNSIGNED NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  name VARCHAR(63) NOT NULL,
  resource_type ENUM('vm','container') NOT NULL,
  model VARCHAR(80) NOT NULL,
  status ENUM('provisioning','running','stopped','error') NOT NULL DEFAULT 'provisioning',
  vmid INT UNSIGNED NULL,
  proxmox_node VARCHAR(100) NULL,
  vcpu TINYINT UNSIGNED NOT NULL,
  ram_mb INT UNSIGNED NOT NULL,
  disk_gb INT UNSIGNED NOT NULL,
  error_message VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_resources_school FOREIGN KEY (school_id) REFERENCES schools(id),
  CONSTRAINT fk_resources_user FOREIGN KEY (created_by) REFERENCES users(id),
  UNIQUE KEY uq_proxmox_resource (proxmox_node, vmid),
  KEY idx_resources_school (school_id, created_at)
) ENGINE=InnoDB;

-- Exemple : créer une école de test après import du schéma
-- INSERT INTO schools (name) VALUES ('École 1');
