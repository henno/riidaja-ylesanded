<h2>Ülesannete nimekiri</h2>
<table>
  <thead>
    <tr>
      <th>Ülesanne</th>
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

      echo '<tr>';
      echo '<td><a href="?page=tasks&task=' . $id . '">Ülesanne ' . $id . '</a></td>';
      echo '<td>' . $myBestFormatted . '</td>';
      echo '<td>' . $bestFormatted . '</td>';
      echo '<td>' . $avgFormatted . '</td>';
      echo '<td><a href="?page=results&exercise=' . $id . '">Tulemused</a></td>';
      echo '</tr>';
    }
  ?>
  </tbody>
</table>
