<h2>Ülesannete nimekiri</h2>
<style>
  .completion-box {
    display: inline-block;
    width: 30px;
    height: 30px;
    margin: 0 2px;
    border: 1px solid #333;
    text-align: center;
    line-height: 30px;
    font-weight: bold;
    color: white;
  }
  .completion-box.not-completed {
    background-color: #ccc; /* Gray for no attempts */
    color: #000; /* Black text */
  }
  .completion-box.completed {
    background-color: #ffa500; /* Orange for 1-2 completions */
  }
  .completion-box.completed-thrice {
    background-color: #0a0; /* Green for 3+ completions */
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
      <th>Vajalik tulemus</th>
      <th>Minu parim tulemus</th>
      <th>Parim tulemus</th>
      <th>Keskmine tulemus</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php
    $userEmail = $_SESSION['user']['email'];
    $files = glob(__DIR__ . '/../exercises/[0-9][0-9][0-9].php');
    natcasesort($files);

    foreach ($files as $filePath) {
      $id = basename($filePath, '.php');

      $myBest = $resultsModel->getUserBest($userEmail, $id);
      $myBestFormatted = ($myBest !== null && $myBest !== false) ? number_format($myBest, 2) . ' s' : '-';

      $best = $resultsModel->getGlobalBest($id);
      $bestFormatted = ($best && isset($best['elapsed']) && $best['elapsed'] !== null)
        ? number_format($best['elapsed'], 2) . ' s (' . htmlspecialchars($best['name']) . ')'
        : '-';

      $avg = $resultsModel->getAverage($id);
      $avgFormatted = ($avg !== null && $avg !== false) ? number_format($avg, 2) . ' s' : '-';

      // Get exercise info
      $exercise = $resultsModel->getExercise($id);
      $targetFormatted = ($exercise && isset($exercise['target_time']) && $exercise['target_time'] !== null)
        ? number_format($exercise['target_time'], 2) . ' s'
        : '-';

      // For exercise 006, show WPM requirements and format results as WPM
      if ($id === '006') {
        $targetFormatted = '1/3: 20 WPM 90%<br>2/3: 30 WPM 90%<br>3/3: 40 WPM 90%';
        $myBestFormatted = ($myBest !== null && $myBest !== false) ? round($myBest) . ' WPM' : '-';
        $bestFormatted = ($best && isset($best['elapsed']) && $best['elapsed'] !== null)
          ? round($best['elapsed']) . ' WPM (' . htmlspecialchars($best['name']) . ')'
          : '-';
        $avgFormatted = ($avg !== null && $avg !== false) ? round($avg) . ' WPM' : '-';
      }

      // Get user's attempts for this exercise
      $attempts = $resultsModel->getUserAttempts($userEmail, $id);
      $completionCount = count($attempts);

      // Generate completion boxes HTML
      $completionBoxes = '<div class="completion-container">';

      // When the student has not passed the exercise a single time: 3 gray boxes with question marks
      if ($completionCount == 0) {
        for ($i = 0; $i < 3; $i++) {
          $completionBoxes .= '<div class="completion-box not-completed">?</div>';
        }
      }
      // When the student has passed the exercise 1-2 times: orange boxes with scores + black boxes
      elseif ($completionCount < 3) {
        // Show completed attempts (orange boxes with scores)
        for ($i = 0; $i < $completionCount; $i++) {
          $score = round($attempts[$i]);
          $completionBoxes .= '<div class="completion-box completed">' . $score . '</div>';
        }

        // Fill remaining slots with gray boxes with question marks
        for ($i = $completionCount; $i < 3; $i++) {
          $completionBoxes .= '<div class="completion-box not-completed">?</div>';
        }
      }
      // When the student has passed the exercise 3+ times: three green boxes with scores
      else {
        // For 3+ completions, show the three most relevant scores in green boxes
        if ($completionCount > 3) {
          // When more than 3 attempts:
          // - Rightmost box: last result (newest)
          // - Middle box: result before the last
          // - Leftmost box: result before the middle

          // Get the last 3 results in reverse order (oldest to newest)
          $relevantAttempts = array_slice($attempts, -3);

          foreach ($relevantAttempts as $attempt) {
            $score = round($attempt);
            $completionBoxes .= '<div class="completion-box completed-thrice">' . $score . '</div>';
          }
        } else { // Exactly 3 completions
          foreach ($attempts as $attempt) {
            $score = round($attempt);
            $completionBoxes .= '<div class="completion-box completed-thrice">' . $score . '</div>';
          }
        }
      }

      $completionBoxes .= '</div>';

      echo '<tr>';
      echo '<td><a href="?page=tasks&task=' . $id . '">Ülesanne ' . $id . '</a></td>';
      echo '<td>' . $completionBoxes . '</td>';
      echo '<td>' . $targetFormatted . '</td>';
      echo '<td>' . $myBestFormatted . '</td>';
      echo '<td>' . $bestFormatted . '</td>';
      echo '<td>' . $avgFormatted . '</td>';
      echo '<td><a href="?page=results&exercise=' . $id . '">Tulemused</a></td>';
      echo '</tr>';
    }
  ?>
  </tbody>
</table>

