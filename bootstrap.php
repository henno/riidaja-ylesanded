<?php
/**
 * Bootstrap configuration.
 *
 * This file sets up the database path based on domain.
 * All requests use the same database.db - no separate databases per domain.
 */

// Always use the same database
define('DB_FILE_PATH', __DIR__ . '/database.db');
