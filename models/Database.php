<?php
class Database {
  private static $pdo = null;

  public static function connect() {
    if (self::$pdo === null) {
      $dbPath = __DIR__ . '/../database.db';

      // Create PDO connection
      self::$pdo = new PDO('sqlite:' . $dbPath);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Check if required tables exist
      self::ensureTablesExist();
    }
    return self::$pdo;
  }

  /**
   * Ensure that all required tables exist
   */
  private static function ensureTablesExist() {
    try {
      // Try to query the results table
      self::$pdo->query("SELECT 1 FROM results LIMIT 1");
    } catch (Exception $e) {
      // If the table doesn't exist, create and migrate the database
      self::createAndMigrateDatabase();
    }
  }

    /**
     * Create and migrate the database if it doesn't exist
     * @throws Exception
     */
  private static function createAndMigrateDatabase() {
    // Create a migration table
    self::$pdo->exec('
      CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY,
        migration TEXT,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
      )
    ');

    // Apply migrations from migrations directory
    $migrationFiles = glob(__DIR__ . '/../migrations/*.sql');
    natcasesort($migrationFiles);

    // Apply each migration
    foreach ($migrationFiles as $migrationFile) {
      $migrationName = basename($migrationFile);

      // Check if migration already applied
      $stmt = self::$pdo->prepare('SELECT 1 FROM migrations WHERE migration = ?');
      $stmt->execute([$migrationName]);
      if ($stmt->fetchColumn()) {
        continue;
      }

      try {
        // Begin transaction
        self::$pdo->beginTransaction();

        // Read and execute migration SQL
        $sql = file_get_contents($migrationFile);
        self::$pdo->exec($sql);

        // Record the migration
        $stmt = self::$pdo->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$migrationName]);

        // Commit transaction
        self::$pdo->commit();
      } catch (Exception $e) {
        // Rollback transaction on error
        self::$pdo->rollBack();
        throw $e;
      }
    }

    // Update exercises table with information from exercise files
    self::updateExercisesTable();
  }

  /**
   * Update exercises table with information from exercise files
   */
  private static function updateExercisesTable() {
    // Make sure the exercise table exists
    try {
      self::$pdo->query("SELECT 1 FROM exercises LIMIT 1");
    } catch (Exception $e) {
      // Table doesn't exist yet, which is fine if the migration hasn't created it
      return;
    }

    // Get existing exercises from database
    $existingExercises = self::$pdo->query("SELECT id FROM exercises")->fetchAll(PDO::FETCH_COLUMN);

    // Get all exercise files
    $exerciseFiles = glob(__DIR__ . '/../exercises/[0-9][0-9][0-9].php');
    natcasesort($exerciseFiles);

    // Process each exercise file
    foreach ($exerciseFiles as $exerciseFile) {
      $exerciseId = basename($exerciseFile, '.php');
      $targetTime = self::extractTargetTime($exerciseFile);
      $description = self::extractDescription($exerciseFile);

      // Generate a title based on the exercise ID
      $title = "Ülesanne " . $exerciseId;

      // Check if this exercise already exists in the database
      if (in_array($exerciseId, $existingExercises)) {
        // Update existing exercise
        $stmt = self::$pdo->prepare("UPDATE exercises SET title = ?, target_time = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $targetTime, $description, $exerciseId]);
      } else {
        // Insert new exercise
        $stmt = self::$pdo->prepare("INSERT INTO exercises (id, title, target_time, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$exerciseId, $title, $targetTime, $description]);
      }
    }
  }

  /**
   * Extract target time from an exercise file
   */
  private static function extractTargetTime($filePath) {
    $content = file_get_contents($filePath);
    // Default target time if isn't specified
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

  /**
   * Extract a simple description from an exercise file
   */
  private static function extractDescription($filePath) {
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
}
