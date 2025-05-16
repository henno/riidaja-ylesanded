<?php
/**
 * Database Migration Script
 * 
 * This script applies database migrations in sequential order.
 * It keeps track of applied migrations in a migrations table.
 * It also automatically updates the exercises table with information
 * from exercise files in the exercises directory.
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

// Update exercises table with information from exercise files
echo "\nUpdating exercises table with information from exercise files...\n";

// Make sure the exercises table exists (it should have been created by the migrations)
try {
    $db->query("SELECT 1 FROM exercises LIMIT 1");
} catch (Exception $e) {
    echo "The exercises table doesn't exist yet. Please make sure migration 003_add_exercises_table.sql has been applied.\n";
    exit(1);
}

// Get existing exercises from database
$existingExercises = $db->query("SELECT id FROM exercises")->fetchAll(PDO::FETCH_COLUMN);

// Get all exercise files
$exerciseFiles = glob(__DIR__ . '/exercises/[0-9][0-9][0-9].php');
natcasesort($exerciseFiles);

$exercisesAdded = 0;
$exercisesUpdated = 0;

// Function to extract target time from an exercise file
function extractTargetTime($filePath) {
    $content = file_get_contents($filePath);
    // Default target time if not specified
    $targetTime = 60;
    
    // Look for target time in the file
    // Pattern 1: Specific value assignment (most exercises)
    if (preg_match('/elapsed >= (\d+)/', $content, $matches)) {
        $targetTime = $matches[1];
    }
    // Pattern 2: Direct reference to time in seconds
    elseif (preg_match('/aega (\d+) sekundit/', $content, $matches)) {
        $targetTime = $matches[1];
    }
    
    return $targetTime;
}

// Function to extract a simple description from an exercise file
function extractDescription($filePath) {
    $content = file_get_contents($filePath);
    
    // Try to find the task description in a <p> tag
    if (preg_match('/<p>(.*?)<\/p>/s', $content, $matches)) {
        // Extract the first 100 characters of the description and clean it up
        $description = strip_tags($matches[1]);
        $description = trim(preg_replace('/\s+/', ' ', $description));
        
        // Limit to 100 characters and add ellipsis if needed
        if (strlen($description) > 100) {
            $description = substr($description, 0, 97) . '...';
        }
        
        return $description;
    }
    
    // Default description if not found
    return "Exercise " . basename($filePath, '.php');
}

// Process each exercise file
foreach ($exerciseFiles as $exerciseFile) {
    $exerciseId = basename($exerciseFile, '.php');
    $targetTime = extractTargetTime($exerciseFile);
    $description = extractDescription($exerciseFile);
    
    // Generate a title based on the exercise ID
    $title = "Ülesanne " . $exerciseId;
    
    // Check if this exercise already exists in the database
    if (in_array($exerciseId, $existingExercises)) {
        // Update existing exercise
        $stmt = $db->prepare("UPDATE exercises SET title = ?, target_time = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $targetTime, $description, $exerciseId]);
        echo "Updated exercise: {$exerciseId}\n";
        $exercisesUpdated++;
    } else {
        // Insert new exercise
        $stmt = $db->prepare("INSERT INTO exercises (id, title, target_time, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$exerciseId, $title, $targetTime, $description]);
        echo "Added new exercise: {$exerciseId}\n";
        $exercisesAdded++;
    }
}

echo "Exercises processed: {$exercisesAdded} added, {$exercisesUpdated} updated\n";

// Update the database schema file
echo "\nUpdating database_schema.sql...\n";
$schemaOutput = shell_exec('sqlite3 ' . escapeshellarg($dbPath) . ' .schema');
file_put_contents(__DIR__ . '/database_schema.sql', $schemaOutput);
echo "Schema file updated.\n";
