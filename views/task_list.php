<h2>Ülesannete nimekiri</h2>
<style>
  .completion-box {
    display: inline-block;
    position: relative;
    width: 30px;
    height: 30px;
    margin: 0 2px;
    border: 1px solid #333;
    text-align: center;
    line-height: 30px;
    font-weight: bold;
    color: white;
  }
  .completion-box[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    min-width: 220px;
    max-width: 320px;
    padding: 8px 10px;
    border-radius: 4px;
    background: #000;
    color: #fff;
    font-size: 13px;
    font-weight: normal;
    line-height: 1.35;
    text-align: left;
    white-space: normal;
    z-index: 1000;
    pointer-events: none;
  }
  .completion-box[data-tooltip]:hover::before {
    content: '';
    position: absolute;
    left: 50%;
    bottom: calc(100% + 2px);
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #000;
    z-index: 1000;
    pointer-events: none;
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
  .completion-box.failed {
    background-color: #f44336; /* Red for failed attempts (exercise 006) */
  }
  .completion-box.passed {
    background-color: #4CAF50; /* Green for passed attempts (exercise 006) */
  }
  .completion-container {
    display: flex;
    justify-content: center;
  }
  .required-result-cell {
    text-align: left;
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
    if (!function_exists('completionBox')) {
      function completionBox($class, $content, $tooltip) {
        return '<div class="completion-box ' . htmlspecialchars($class) . '" data-tooltip="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string)$content) . '</div>';
      }
    }

    if (!function_exists('roundConditionText')) {
      function roundConditionText($exerciseId, $round, $exercise = null) {
        $targetTime = $exercise && isset($exercise['target_time']) ? (int)$exercise['target_time'] : 60;

        $conditions = [
          '001' => [
            1 => '15 sõna, aeg ' . ($targetTime + 30) . ' s',
            2 => '22 sõna, aeg ' . ($targetTime + 15) . ' s',
            3 => '30 sõna, aeg ' . $targetTime . ' s',
          ],
          '002' => [
            1 => '8 lugu, aeg 55 s',
            2 => '10 lugu, aeg 55 s',
            3 => '12 lugu, aeg 55 s',
          ],
          '003' => [
            1 => '7 URL-i, aeg 90 s',
            2 => '10 URL-i, aeg 75 s',
            3 => '13 URL-i, aeg 60 s',
          ],
          '004' => [
            1 => 'Paranda tekst, aeg 120 s',
            2 => 'Paranda tekst, aeg 90 s',
            3 => 'Paranda tekst, aeg 60 s',
          ],
          '005' => [
            1 => 'Kustuta liigsed sõnad, aeg 120 s',
            2 => 'Kustuta liigsed sõnad, aeg 90 s',
            3 => 'Kustuta liigsed sõnad, aeg 40 s',
          ],
          '006' => [
            1 => 'WPM >= 8, täpsus >= 90%, aeg 30 s',
            2 => 'WPM >= 10, täpsus >= 90%, aeg 30 s',
            3 => 'WPM >= 12, täpsus >= 90%, aeg 30 s',
          ],
          '007' => [
            1 => 'WPM >= 20, täpsus >= 90%, aeg 30 s',
            2 => 'WPM >= 25, täpsus >= 90%, aeg 30 s',
            3 => 'WPM >= 30, täpsus >= 90%, aeg 30 s',
          ],
          '008' => [
            1 => '3 lauset, aeg 160 s',
            2 => '4 lauset, aeg 140 s',
            3 => '6 lauset, aeg 120 s',
          ],
          '009' => [
            1 => 'WPM >= 24, täpsus >= 90%, aeg 30 s',
            2 => 'WPM >= 28, täpsus >= 90%, aeg 30 s',
            3 => 'WPM >= 32, täpsus >= 90%, aeg 30 s',
          ],
          '010' => [
            1 => 'WPM >= 12, täpsus >= 97%, aeg 30 s',
            2 => 'WPM >= 15, täpsus >= 97%, aeg 30 s',
            3 => 'WPM >= 17, täpsus >= 97%, aeg 30 s',
          ],
          '011' => [
            1 => 'WPM >= 12, täpsus >= 97%, 10 lauset, aeg 30 s',
            2 => 'WPM >= 15, täpsus >= 97%, 15 lauset, aeg 30 s',
            3 => 'WPM >= 17, täpsus >= 97%, 20 lauset, aeg 30 s',
          ],
          '012' => [
            1 => 'WPM >= 12, täpsus >= 97%, 8 lauset, aeg 30 s',
            2 => 'WPM >= 15, täpsus >= 97%, 12 lauset, aeg 30 s',
            3 => 'WPM >= 17, täpsus >= 97%, 15 lauset, aeg 30 s',
          ],
          '013' => [
            1 => 'WPM >= 12, täpsus >= 90%, 5 elu, viivitus 5 s',
            2 => 'WPM >= 16, täpsus >= 90%, 4 elu, viivitus 4 s',
            3 => 'WPM >= 20, täpsus >= 90%, 3 elu, viivitus 3 s',
          ],
          '014' => [
            1 => 'WPM >= 20, täpsus >= 90%, aeg 30 s, lihtsad sõnad',
            2 => 'WPM >= 28, täpsus >= 90%, aeg 30 s, keskmised sõnad',
            3 => 'WPM >= 35, täpsus >= 90%, aeg 30 s, rasked sõnad',
          ],
          '015' => [
            1 => 'WPM >= 10, täpsus >= 97%, kirjavahemärkide vihjed, aeg 30 s',
            2 => 'WPM >= 17, täpsus >= 97%, aeg 30 s',
            3 => 'WPM >= 25, täpsus >= 97%, aeg 30 s',
          ],
        ];

        return $conditions[$exerciseId][$round] ?? 'Raundi tingimused pole seadistatud';
      }
    }

    if (!function_exists('roundTooltip')) {
      function roundTooltip($exerciseId, $round, $exercise = null) {
        return 'Raund ' . $round . ' / 3: ' . roundConditionText($exerciseId, $round, $exercise);
      }
    }

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
      $resultType = $exercise && isset($exercise['result_type']) ? $exercise['result_type'] : 'time';

      // Get user's attempts for this exercise
      $attempts = $resultsModel->getUserAttempts($userEmail, $id);
      $completionCount = count($attempts);
      $passedAttempts = array_values(array_filter($attempts, function($v) { return floatval($v) > 0; }));
      $passedCount = count($passedAttempts);
      $currentRound = min($passedCount + 1, 3);
      $targetFormatted = roundConditionText($id, $currentRound, $exercise);

      // Format based on result type
      if ($resultType === 'wpm') {
        // WPM exercises: format results as WPM
        $myBestFormatted = ($myBest !== null && $myBest !== false) ? round($myBest) . ' WPM' : '-';
        $bestFormatted = ($best && isset($best['elapsed']) && $best['elapsed'] !== null)
          ? round($best['elapsed']) . ' WPM (' . htmlspecialchars($best['name']) . ')'
          : '-';
        $avgFormatted = ($avg !== null && $avg !== false) ? round($avg) . ' WPM' : '-';
      }

      // Generate completion boxes HTML
      $completionBoxes = '<div class="completion-container">';

      // Special handling for WPM exercises (only count passed attempts)
      if ($resultType === 'wpm') {
        if ($passedCount == 0) {
          // No passed attempts - show gray boxes
          for ($i = 0; $i < 3; $i++) {
            $completionBoxes .= completionBox('not-completed', '?', roundTooltip($id, $i + 1, $exercise));
          }
        } elseif ($passedCount < 3) {
          // Show passed attempts (green boxes with WPM)
          $passedValues = $passedAttempts;
          for ($i = 0; $i < $passedCount; $i++) {
            $wpm = round($passedValues[$i]);
            $completionBoxes .= completionBox('passed', $wpm, roundTooltip($id, $i + 1, $exercise));
          }
          // Fill remaining with gray boxes
          for ($i = $passedCount; $i < 3; $i++) {
            $completionBoxes .= completionBox('not-completed', '?', roundTooltip($id, $i + 1, $exercise));
          }
        } else {
          // 3+ passed attempts - show last 3 in green
          $passedValues = $passedAttempts;
          $lastThree = array_slice($passedValues, -3);
          foreach ($lastThree as $i => $wpm) {
            $completionBoxes .= completionBox('passed', round($wpm), roundTooltip($id, $i + 1, $exercise));
          }
        }
      }
      // Standard exercises (time-based)
      else {
        // When the student has not passed the exercise a single time: 3 gray boxes with question marks
        if ($passedCount == 0) {
          for ($i = 0; $i < 3; $i++) {
            $completionBoxes .= completionBox('not-completed', '?', roundTooltip($id, $i + 1, $exercise));
          }
        }
        // When the student has passed the exercise 1-2 times: orange boxes with scores + black boxes
        elseif ($passedCount < 3) {
          // Show completed attempts (orange boxes with scores)
          for ($i = 0; $i < $passedCount; $i++) {
            $score = round($passedAttempts[$i]);
            $completionBoxes .= completionBox('completed', $score, roundTooltip($id, $i + 1, $exercise));
          }

          // Fill remaining slots with gray boxes with question marks
          for ($i = $passedCount; $i < 3; $i++) {
            $completionBoxes .= completionBox('not-completed', '?', roundTooltip($id, $i + 1, $exercise));
          }
        }
        // When the student has passed the exercise 3+ times: three green boxes with scores
        else {
          // For 3+ completions, show the three most relevant scores in green boxes
          $relevantAttempts = array_slice($passedAttempts, -3);

          foreach ($relevantAttempts as $i => $attempt) {
            $score = round($attempt);
            $completionBoxes .= completionBox('completed-thrice', $score, roundTooltip($id, $i + 1, $exercise));
          }
        }
      }

      $completionBoxes .= '</div>';

      echo '<tr>';
      echo '<td><a href="?page=tasks&task=' . $id . '">Ülesanne ' . $id . '</a></td>';
      echo '<td>' . $completionBoxes . '</td>';
      echo '<td class="required-result-cell">' . htmlspecialchars($targetFormatted) . '</td>';
      echo '<td>' . $myBestFormatted . '</td>';
      echo '<td>' . $bestFormatted . '</td>';
      echo '<td>' . $avgFormatted . '</td>';
      echo '<td><a href="?page=results&exercise=' . $id . '&summary=0">Tulemused</a></td>';
      echo '</tr>';
    }
  ?>
  </tbody>
</table>
