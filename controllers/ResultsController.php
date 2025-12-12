<?php

class ResultsController {
  private $resultsModel;
  private $isAdmin;

  public function __construct($resultsModel, $isAdmin, $blindTypingResultsModel = null) {
    $this->resultsModel = $resultsModel;
    $this->isAdmin = $isAdmin;
  }

  public function show($exerciseFilter = null) {
    $isAdmin = $this->isAdmin;
    $showSummary = isset($_GET['summary']) ? (bool)$_GET['summary'] : true; // Default to summary view
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'harjutused'; // Default to 'harjutused' tab

    if ($showSummary) {
      if ($activeTab === 'harjutused') {
        if ($exerciseFilter) {
          // Show a summary for a specific exercise
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

          // Get a global average for this exercise
          $globalAverages = [];
          $globalAverages[$exerciseFilter] = $this->resultsModel->getAverage($exerciseFilter);
        } else {
          // Show a summary for all exercises
          $summaryResults = $this->resultsModel->getSummaryResults();

          // Get a global average for each exercise
          $globalAverages = [];
          $allExercises = $this->resultsModel->getAllExercisesInfo();
          foreach ($allExercises as $exercise) {
            $exerciseId = $exercise['id'];
            $globalAverages[$exerciseId] = $this->resultsModel->getAverage($exerciseId);
          }
        }
      } else if ($activeTab === 'opilased') {
        // Show summary grouped by students
        $studentResults = $this->resultsModel->getStudentSummaryResults();

        // Get a global average for each exercise
        $globalAverages = [];
        $allExercises = $this->resultsModel->getAllExercisesInfo();
        foreach ($allExercises as $exercise) {
          $exerciseId = $exercise['id'];
          $globalAverages[$exerciseId] = $this->resultsModel->getAverage($exerciseId);
        }
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
      // Make sure $emailFilter and $activeTab are available in the view
      include __DIR__ . '/../views/results.php';
    }
  }
}
