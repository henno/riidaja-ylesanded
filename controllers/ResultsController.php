<?php

class ResultsController {
  private $resultsModel;
  private $isAdmin;

  public function __construct($resultsModel, $isAdmin) {
    $this->resultsModel = $resultsModel;
    $this->isAdmin = $isAdmin;
  }

  public function show($exerciseFilter = null) {
    $results = $this->resultsModel->getAll($exerciseFilter);
    $isAdmin = $this->isAdmin;
    include __DIR__ . '/../views/results.php';
  }
}
