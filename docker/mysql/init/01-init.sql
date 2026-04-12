# ============================================================
# ASCOM Database Initialization Script
# Phase 1: Environment Setup & Docker Foundation
# This script runs on first container startup
# ============================================================

-- Enable UTF-8 support
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Set SQL mode for compatibility
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- Create database if not exists (handled by docker-compose)
-- Database: ascom_db

-- ============================================================
# Note: Legacy SQL schema is in:
# /home/pwn/Projects/ASCOM---Academic-Standards-Compliance-Monitoring-System/ascom_db_full_export.sql
#
# For Laravel migrations, use:
# - php artisan migrate (for fresh installations)
# - Review schema and create migrations in database/migrations/
# ============================================================