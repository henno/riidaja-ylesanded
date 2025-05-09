<h2>Ülesannete nimekiri</h2>
<style>
  .completion-box {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin: 0 2px;
    border: 1px solid #333;
  }
  .completion-box.not-completed {
    background-color: #000;
  }
  .completion-box.completed-once {
    background-color: #f00;
  }
  .completion-box.completed-twice {
    background-color: #ffa500;
  }
  .completion-box.completed-thrice {
    background-color: #0a0;
  }
  .completion-container {
    display: flex;
    justify-content: center;
  }
</style>
<table>
  <thead>
    <tr>
      <th>Ülesanne</th>
      <th>Läbimised</th>
      <th>Minu parim tulemus</th>
      <th>Parim tulemus</th>
      <th>Keskmine tulemus</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php
    $userEmail = $_SESSION['user']['email'];
    $files = glob(__DIR__ . '/../exercises/[0-9][0-9].php');
    natcasesort($files);

    foreach ($files as $filePath) {
      $id = basename($filePath, '.php');

      $myBest = $resultsModel->getUserBest($userEmail, $id);
      $myBestFormatted = $myBest ? number_format($myBest, 2) . ' s' : '-';

      $best = $resultsModel->getGlobalBest($id);
      $bestFormatted = $best
        ? number_format($best['elapsed'], 2) . ' s (' . htmlspecialchars($best['name']) . ')'
        : '-';

      $avg = $resultsModel->getAverage($id);
      $avgFormatted = $avg ? number_format($avg, 2) . ' s' : '-';

      // Get completion count for this exercise
      $completionCount = $resultsModel->getUserCompletionCount($userEmail, $id);

      // Generate completion boxes HTML
      $completionBoxes = '<div class="completion-container">';
      for ($i = 0; $i < 3; $i++) {
        if ($completionCount >= 3) {
          $boxClass = 'completed-thrice'; // All green if completed 3 or more times
        } elseif ($i < $completionCount) {
          $boxClass = $i == 0 ? 'completed-once' : 'completed-twice'; // First red, second orange
        } else {
          $boxClass = 'not-completed'; // Black for not completed
        }
        $completionBoxes .= '<div class="completion-box ' . $boxClass . '"></div>';
      }
      $completionBoxes .= '</div>';

      echo '<tr>';
      echo '<td><a href="?page=tasks&task=' . $id . '">Ülesanne ' . $id . '</a></td>';
      echo '<td>' . $completionBoxes . '</td>';
      echo '<td>' . $myBestFormatted . '</td>';
      echo '<td>' . $bestFormatted . '</td>';
      echo '<td>' . $avgFormatted . '</td>';
      echo '<td><a href="?page=results&exercise=' . $id . '">Tulemused</a></td>';
      echo '</tr>';
    }
  ?>
  </tbody>
</table>
