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
    $stmt = $this->db->prepare('SELECT MIN(elapsed) FROM results WHERE email = ? AND exercise_id = ?');
    $stmt->execute([$email, $exerciseId]);
    return $stmt->fetchColumn();
  }

  public function getGlobalBest($exerciseId) {
    $stmt = $this->db->prepare('SELECT MIN(elapsed) FROM results WHERE exercise_id = ?');
    $stmt->execute([$exerciseId]);
    $elapsed = $stmt->fetchColumn();

    if ($elapsed !== false) {
      $stmt = $this->db->prepare('SELECT name FROM results WHERE exercise_id = ? AND elapsed = ? LIMIT 1');
      $stmt->execute([$exerciseId, $elapsed]);
      $name = $stmt->fetchColumn();
      return ['elapsed' => $elapsed, 'name' => $name];
    }

    return null;
  }

  public function getAverage($exerciseId) {
    $stmt = $this->db->prepare('SELECT AVG(elapsed) FROM results WHERE exercise_id = ?');
    $stmt->execute([$exerciseId]);
    return $stmt->fetchColumn();
  }

  public function getAll($exerciseId = null) {
    if ($exerciseId) {
      $stmt = $this->db->prepare('SELECT * FROM results WHERE exercise_id = ? ORDER BY timestamp DESC');
      $stmt->execute([$exerciseId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $this->db->query('SELECT * FROM results ORDER BY timestamp DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  public function getAllByEmailAndExercise($email, $exerciseId = null) {
    if ($exerciseId) {
      $stmt = $this->db->prepare('SELECT * FROM results WHERE email = ? AND exercise_id = ? ORDER BY timestamp DESC');
      $stmt->execute([$email, $exerciseId]);
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
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM results WHERE email = ? AND exercise_id = ?');
    $stmt->execute([$email, $exerciseId]);
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
          r.exercise_id = ?
        GROUP BY
          r.email, r.name, r.exercise_id
        ORDER BY
          first_timestamp DESC
      ');
      $stmt->execute([$exerciseId]);
      $exerciseResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (!empty($exerciseResults)) {
        $results[$exerciseId] = $exerciseResults;
      }
    }

    return $results;
  }
}
