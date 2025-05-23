<?php

class Database
{
    private static ?PDO $pdo = null;

    private const DB_FILE_PATH = __DIR__ . '/../database.db';
    private const MIGRATIONS_GLOB_PATH = __DIR__ . '/../migrations/*.sql';
    private const EXERCISES_GLOB_PATH = __DIR__ . '/../exercises/[0-9][0-9][0-9].php';

    private const TABLE_MIGRATIONS = 'migrations';
    private const TABLE_EXERCISES = 'exercises';
    // private const TABLE_RESULTS = 'results'; // If 'results' is created by a migration

    private const DEFAULT_TARGET_TIME = 60;
    private const DESCRIPTION_MAX_LENGTH = 100;
    private const DESCRIPTION_TRUNCATE_SUFFIX = '...';

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO('sqlite:' . self::DB_FILE_PATH);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::initializeSchemaAndData();
        }
        return self::$pdo;
    }

    /**
     * Ensures the database schema is up to date by creating the necessary tables
     * and applying migrations and updating exercise data.
     */
    private static function initializeSchemaAndData(): void
    {
        // 1. Create a migration table if it doesn't exist
        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS ' . self::TABLE_MIGRATIONS . ' (
                id INTEGER PRIMARY KEY,
                migration TEXT UNIQUE,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        // 2. Apply pending migrations
        self::applyMigrations();

        // 3. Sync exercises table from files (if the table exists - typically created by a migration)
        self::syncExercisesTable();
    }

    private static function tableExists(string $tableName): bool
    {
        try {
            // Querying information_schema or specific system tables is more robust,
            // but for SQLite, a simple query and catching the exception is common.
            self::$pdo->query("SELECT 1 FROM {$tableName} LIMIT 1");
            return true;
        } catch (PDOException $e) {
            // This will catch the "no such table" error.
            return false;
        }
    }

    private static function applyMigrations(): void
    {
        $migrationFiles = glob(self::MIGRATIONS_GLOB_PATH) ?: [];
        natcasesort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $migrationName = basename($migrationFile);

            $stmt = self::$pdo->prepare('SELECT 1 FROM ' . self::TABLE_MIGRATIONS . ' WHERE migration = ?');
            $stmt->execute([$migrationName]);
            if ($stmt->fetchColumn()) {
                continue; // Already applied
            }

            try {
                self::$pdo->beginTransaction();
                self::$pdo->exec(file_get_contents($migrationFile));
                self::recordMigration($migrationName);
                self::$pdo->commit();
            } catch (Exception $e) {
                self::$pdo->rollBack();
                error_log("Failed to apply migration {$migrationName}: " . $e->getMessage());
                throw $e; // Re-throw to halt further operations or inform the caller
            }
        }
    }

    private static function recordMigration(string $migrationName): void
    {
        $stmt = self::$pdo->prepare('INSERT INTO ' . self::TABLE_MIGRATIONS . ' (migration) VALUES (?)');
        $stmt->execute([$migrationName]);
    }

    private static function syncExercisesTable(): void
    {
        if (!self::tableExists(self::TABLE_EXERCISES)) {
            // If the exercise table doesn't exist (e.g., migration hasn't run), skip syncing.
            return;
        }

        $exerciseFiles = self::getExerciseFiles();
        foreach ($exerciseFiles as $exerciseFile) {
            $exerciseData = self::processExerciseFile($exerciseFile);
            self::saveExercise($exerciseData);
        }
    }

    private static function getExerciseFiles(): array
    {
        $files = glob(self::EXERCISES_GLOB_PATH) ?: [];
        natcasesort($files);
        return $files;
    }

    private static function processExerciseFile(string $filePath): array
    {
        $exerciseId = basename($filePath, '.php');
        return [
            'id' => $exerciseId,
            'title' => "Ülesanne " . $exerciseId, // Consider if this should be configurable
            'target_time' => self::extractTargetTimeFromFile($filePath),
            'description' => self::extractDescriptionFromFile($filePath, $exerciseId)
        ];
    }

    private static function saveExercise(array $exercise): void
    {
        $sql = '
            INSERT INTO ' . self::TABLE_EXERCISES . ' (id, title, target_time, description)
            VALUES (:id, :title, :target_time, :description)
            ON CONFLICT(id) DO UPDATE SET
                title = excluded.title,
                target_time = excluded.target_time,
                description = excluded.description
        ';
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':id' => $exercise['id'],
            ':title' => $exercise['title'],
            ':target_time' => $exercise['target_time'],
            ':description' => $exercise['description'],
        ]);
    }

    private static function extractTargetTimeFromFile(string $filePath): int
    {
        $content = file_get_contents($filePath);
        $targetTime = self::DEFAULT_TARGET_TIME;

        if (preg_match('/elapsed >= (\d+)/', $content, $matches) ||
            preg_match('/aega (\d+) sekundit/', $content, $matches)) {
            $targetTime = (int)$matches[1];
        }
        return $targetTime;
    }

    private static function extractDescriptionFromFile(string $filePath, string $exerciseId): string
    {
        $content = file_get_contents($filePath);

        if (preg_match('/<p>(.*?)<\/p>/s', $content, $matches)) {
            $description = strip_tags($matches[1]);
            $description = trim(preg_replace('/\s+/', ' ', $description));

            if (mb_strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
                $description = mb_substr($description, 0, self::DESCRIPTION_MAX_LENGTH - mb_strlen(self::DESCRIPTION_TRUNCATE_SUFFIX)) . self::DESCRIPTION_TRUNCATE_SUFFIX;
            }
            return $description;
        }
        return "Exercise " . $exerciseId; // Default description
    }
}