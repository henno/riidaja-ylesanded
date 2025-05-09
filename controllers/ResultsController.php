<?php

class ResultsController {
  private $resultsModel;
  private $isAdmin;

  public function __construct($resultsModel, $isAdmin) {
    $this->resultsModel = $resultsModel;
    $this->isAdmin = $isAdmin;
  }

  public function show($exerciseFilter = null) {
    $isAdmin = $this->isAdmin;
    $showSummary = isset($_GET['summary']) ? (bool)$_GET['summary'] : true; // Default to summary view

    if ($showSummary) {
      if ($exerciseFilter) {
        // Show summary for a specific exercise
        // Handle both string ('01') and numeric (1) exercise IDs
        $numericExerciseId = (int)$exerciseFilter;
        $stmt = $this->resultsModel->getDb()->prepare('
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
        $stmt->execute([$exerciseFilter, $numericExerciseId]);
        $summaryResults = [$exerciseFilter => $stmt->fetchAll(PDO::FETCH_ASSOC)];
      } else {
        // Show summary for all exercises
        $summaryResults = $this->resultsModel->getSummaryResults();
      }

      $maxAttempts = $this->resultsModel->getMaxAttemptCount();
      include __DIR__ . '/../views/results_summary.php';
    } else {
      // Show detailed view (original behavior)
      $emailFilter = isset($_GET['email']) ? $_GET['email'] : null;

      if ($emailFilter) {
        // Filter by exercise and email
        $results = $this->resultsModel->getAllByEmailAndExercise($emailFilter, $exerciseFilter);
      } else {
        // Just filter by exercise (or show all)
        $results = $this->resultsModel->getAll($exerciseFilter);
      }

      $showSummary = false; // Set this for the toggle in the view
      // Make sure $emailFilter is available in the view
      include __DIR__ . '/../views/results.php';
    }
  }
}
