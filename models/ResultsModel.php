<?php
require_once __DIR__ . '/Database.php';

class ResultsModel {
  private $db;

  public function __construct() {
    $this->db = Database::connect();
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

  public function delete($id) {
    $stmt = $this->db->prepare('DELETE FROM results WHERE id = ?');
    return $stmt->execute([$id]);
  }
}
