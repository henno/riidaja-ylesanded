<?php
/**
 * Domain detection and configuration bootstrap.
 *
 * This file must be included at the very top of all entry points
 * BEFORE any authentication or database code runs.
 *
 * Sets:
 * - AUTH_PROVIDER: 'google' or 'azure'
 * - DB_FILE_PATH: path to the appropriate SQLite database
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
$isVkok = ($host === 'vkok.ee' || str_ends_with($host, '.vkok.ee'));

define('AUTH_PROVIDER', $isVkok ? 'google' : 'azure');
define('DB_FILE_PATH', __DIR__ . ($isVkok ? '/database_vkok.db' : '/database.db'));
