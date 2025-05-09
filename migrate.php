<?php
/**
 * Database Migration Script
 * 
 * This script applies database migrations in sequential order.
 * It keeps track of applied migrations in a migrations table.
 */

// Ensure this script is run from the command line
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.";
    exit(1);
}

// Database connection
$dbPath = __DIR__ . '/database.db';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create migrations table if it doesn't exist
$db->exec('
    CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY,
        migration TEXT,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
');

// Get list of applied migrations
$appliedMigrations = $db->query('SELECT migration FROM migrations ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);

// Get list of migration files
$migrationFiles = glob(__DIR__ . '/migrations/*.sql');
natcasesort($migrationFiles);

// Check if there are any migrations to apply
if (empty($migrationFiles)) {
    echo "No migration files found in the migrations directory.\n";
    exit(0);
}

$migrationsApplied = 0;

// Apply each migration if not already applied
foreach ($migrationFiles as $migrationFile) {
    $migrationName = basename($migrationFile);
    
    if (in_array($migrationName, $appliedMigrations)) {
        echo "Migration {$migrationName} already applied. Skipping.\n";
        continue;
    }
    
    echo "Applying migration: {$migrationName}...\n";
    
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Read and execute migration SQL
        $sql = file_get_contents($migrationFile);
        $db->exec($sql);
        
        // Record the migration
        $stmt = $db->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$migrationName]);
        
        // Commit transaction
        $db->commit();
        
        echo "Migration {$migrationName} applied successfully.\n";
        $migrationsApplied++;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        echo "Error applying migration {$migrationName}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($migrationsApplied > 0) {
    echo "\n{$migrationsApplied} migration(s) applied successfully.\n";
} else {
    echo "\nNo new migrations to apply. Database is up to date.\n";
}

// Update the database schema file
echo "\nUpdating database_schema.sql...\n";
$schemaOutput = shell_exec('sqlite3 ' . escapeshellarg($dbPath) . ' .schema');
file_put_contents(__DIR__ . '/database_schema.sql', $schemaOutput);
echo "Schema file updated.\n";
