<?php

class TaskController {
  private $resultsModel;

  public function __construct($resultsModel) {
    $this->resultsModel = $resultsModel;
  }

public function list($userEmail) {
  $files = glob(__DIR__ . '/../exercises/[0-9][0-9].php');
  natcasesort($files);

  // anna vajalikud muutujad kaasa vaatele
  $resultsModel = $this->resultsModel;
  include __DIR__ . '/../views/task_list.php';
}

  public function show($id) {
    $filename = __DIR__ . '/../exercises/' . basename($id) . '.php';
    if (preg_match('/^\\d{2}$/', $id) && file_exists($filename)) {
      echo "<h2>Ülesanne $id</h2>";
      include $filename;
    } else {
      echo "<p>Ülesannet ei leitud.</p>";
    }
  }
}
