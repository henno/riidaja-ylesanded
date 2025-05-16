<?php
require_once __DIR__ . '/Database.php';

class ResultsModel {
  private $db;

  public function __construct() {
    $this->db = Database::connect();
  }

  public function getDb() {
    return $this->db;
  }

  public function getUserBest($email, $exerciseId) {
    // Handle both string ('01') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;

    $stmt = $this->db->prepare('SELECT MIN(elapsed) FROM results WHERE email = ? AND (exercise_id = ? OR exercise_id = ?)');
    $stmt->execute([$email, $exerciseId, $numericExerciseId]);
    return $stmt->fetchColumn();
  }

  public function getGlobalBest($exerciseId) {
    // Handle both string ('01') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;

    $stmt = $this->db->prepare('SELECT MIN(elapsed) FROM results WHERE exercise_id = ? OR exercise_id = ?');
    $stmt->execute([$exerciseId, $numericExerciseId]);
    $elapsed = $stmt->fetchColumn();

    if ($elapsed !== false) {
      $stmt = $this->db->prepare('SELECT name FROM results WHERE (exercise_id = ? OR exercise_id = ?) AND elapsed = ? LIMIT 1');
      $stmt->execute([$exerciseId, $numericExerciseId, $elapsed]);
      $name = $stmt->fetchColumn();
      return ['elapsed' => $elapsed, 'name' => $name];
    }

    return null;
  }

  public function getAverage($exerciseId) {
    // Handle both string ('01') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;

    $stmt = $this->db->prepare('SELECT AVG(elapsed) FROM results WHERE exercise_id = ? OR exercise_id = ?');
    $stmt->execute([$exerciseId, $numericExerciseId]);
    return $stmt->fetchColumn();
  }

  public function getAll($exerciseId = null) {
    if ($exerciseId) {
      // Handle both string ('01') and numeric (1) exercise IDs
      $numericExerciseId = (int)$exerciseId;

      $stmt = $this->db->prepare('SELECT * FROM results WHERE exercise_id = ? OR exercise_id = ? ORDER BY timestamp DESC');
      $stmt->execute([$exerciseId, $numericExerciseId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $this->db->query('SELECT * FROM results ORDER BY timestamp DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  public function getAllByEmailAndExercise($email, $exerciseId = null) {
    if ($exerciseId) {
      // Handle both string ('01') and numeric (1) exercise IDs
      $numericExerciseId = (int)$exerciseId;

      $stmt = $this->db->prepare('SELECT * FROM results WHERE email = ? AND (exercise_id = ? OR exercise_id = ?) ORDER BY timestamp DESC');
      $stmt->execute([$email, $exerciseId, $numericExerciseId]);
    } else {
      $stmt = $this->db->prepare('SELECT * FROM results WHERE email = ? ORDER BY timestamp DESC');
      $stmt->execute([$email]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function delete($id) {
    $stmt = $this->db->prepare('DELETE FROM results WHERE id = ?');
    return $stmt->execute([$id]);
  }

  public function getUserCompletionCount($email, $exerciseId) {
    // Handle both string ('01') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;

    $stmt = $this->db->prepare('SELECT COUNT(*) FROM results WHERE email = ? AND (exercise_id = ? OR exercise_id = ?)');
    $stmt->execute([$email, $exerciseId, $numericExerciseId]);
    return $stmt->fetchColumn();
  }

  public function getMaxAttemptCount() {
    return $this->db->query('SELECT email, exercise_id, COUNT(*) as count
                            FROM results
                            GROUP BY email, exercise_id
                            ORDER BY count DESC
                            LIMIT 1')->fetchColumn(2);
  }

  public function getAllExercises() {
    return $this->db->query('SELECT DISTINCT exercise_id FROM results ORDER BY exercise_id')->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getSummaryResults() {
    $exercises = $this->getAllExercises();
    $results = [];

    foreach ($exercises as $exerciseId) {
      // Format the exercise ID with leading zero if needed
      $formattedExerciseId = sprintf('%02d', $exerciseId);

      $stmt = $this->db->prepare('
        SELECT
          r.email,
          r.name,
          r.exercise_id,
          MIN(r.timestamp) as first_timestamp,
          GROUP_CONCAT(r.elapsed, ",") as attempts
        FROM
          results r
        WHERE
          r.exercise_id = ? OR r.exercise_id = ?
        GROUP BY
          r.email, r.name, r.exercise_id
        ORDER BY
          first_timestamp DESC
      ');
      $stmt->execute([$exerciseId, $formattedExerciseId]);
      $exerciseResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (!empty($exerciseResults)) {
        $results[$exerciseId] = $exerciseResults;
      }
    }

    return $results;
  }

  /**
   * Get user's attempts for an exercise in chronological order
   *
   * @param string $email User's email
   * @param string $exerciseId Exercise ID
   * @return array Array of attempt results (elapsed times)
   */
  public function getUserAttempts($email, $exerciseId) {
    // Handle both string ('01') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;

    $stmt = $this->db->prepare('
      SELECT elapsed
      FROM results
      WHERE email = ? AND (exercise_id = ? OR exercise_id = ?)
      ORDER BY timestamp ASC
    ');
    $stmt->execute([$email, $exerciseId, $numericExerciseId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  /**
   * Get exercise information
   *
   * @param string $exerciseId Exercise ID
   * @return array|null Exercise information or null if not found
   */
  public function getExercise($exerciseId) {
    // Handle both string ('001') and numeric (1) exercise IDs
    $numericExerciseId = (int)$exerciseId;
    
    $stmt = $this->db->prepare('
      SELECT id, title, target_time, description 
      FROM exercises 
      WHERE id = ? OR id = ?
      LIMIT 1
    ');
    $stmt->execute([$exerciseId, $numericExerciseId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get all exercises
   *
   * @return array Array of exercise information
   */
  public function getAllExercisesInfo() {
    $stmt = $this->db->query('SELECT id, title, target_time, description FROM exercises ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
