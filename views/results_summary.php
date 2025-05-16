<?php
if ($exerciseFilter) {
  echo "<h2>Tulemused – Ülesanne " . htmlspecialchars($exerciseFilter) . "</h2>";
  echo "<p><a href=\"?page=results\">« Kõik tulemused</a></p>";
} else {
  echo "<h2>Kõik tulemused</h2>";
}
?>

<div class="view-toggle">
  <label>
    <input type="checkbox" id="summary-toggle" <?php echo $showSummary ? 'checked' : ''; ?>>
    Kokkuvõte
  </label>
</div>

<style>
  .view-toggle {
    margin: 15px 0;
  }
  .exercise-header {
    background-color: #f0f0f0;
    padding: 8px;
    margin-top: 20px;
    font-weight: bold;
    border-radius: 4px;
  }
  .completion-count {
    text-align: center;
    font-weight: bold;
  }
  .completion-under-three {
    background-color: #ffff99; /* Yellow */
  }
  .completion-three-or-more {
    background-color: #ccffcc; /* Green */
  }
  .view-details-link {
    text-decoration: none;
    font-size: 1.2em;
  }
  .best-result {
    background-color: #ccffcc; /* Green background */
  }
  .global-best-result {
    font-weight: bold;
  }
  .no-attempt {
    background-color: #ccc; /* Consistent gray background */
    color: #000; /* Black text */
  }
</style>

<?php if (empty($summaryResults)): ?>
  <p>Tulemusi pole.</p>
<?php else: ?>
  <?php foreach ($summaryResults as $exerciseId => $exerciseResults): ?>
    <div class="exercise-header">Harjutus <?php echo htmlspecialchars($exerciseId); ?></div>

    <?php
    // Sort results by student name
    usort($exerciseResults, function($a, $b) {
      return strcmp($a['name'], $b['name']);
    });

    // Find the maximum number of attempts for this exercise
    $maxAttemptsForExercise = 0;

    // Find the global best result for this exercise
    $globalBestResult = PHP_FLOAT_MAX;

    // Process each student's results
    foreach ($exerciseResults as &$result) {
      // Convert attempts string to array and sort numerically
      $attempts = explode(',', $result['attempts']);
      $attemptsCount = count($attempts);

      // Update max attempts count
      if ($attemptsCount > $maxAttemptsForExercise) {
        $maxAttemptsForExercise = $attemptsCount;
      }

      // Find student's best result
      $studentBestResult = PHP_FLOAT_MAX;
      $studentBestIndex = -1;

      foreach ($attempts as $index => $attempt) {
        $attempt = (float)$attempt;
        if ($attempt < $studentBestResult) {
          $studentBestResult = $attempt;
          $studentBestIndex = $index;
        }

        // Update global best
        if ($attempt < $globalBestResult) {
          $globalBestResult = $attempt;
        }
      }

      // Store the best result index for this student
      $result['best_index'] = $studentBestIndex;
    }
    unset($result); // Unset reference

    // Store the global best result
    $globalBestResult = $globalBestResult === PHP_FLOAT_MAX ? null : $globalBestResult;
    ?>

    <table>
      <thead>
        <tr>
          <th>Õpilane</th>
          <th>Tulemusi</th>
          <th>Parim Tulemus</th>
          <?php if ($isAdmin): ?>
            <th></th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($exerciseResults as $result): ?>
          <?php
          $attempts = explode(',', $result['attempts']);
          $attemptCount = count($attempts);
          $completionClass = $attemptCount >= 3 ? 'completion-three-or-more' : 'completion-under-three';
          ?>
          <tr>
            <td class="<?= $completionClass ?>"><?= htmlspecialchars($result['name']) ?></td>
            <td class="completion-count <?= $completionClass ?>"><?= (int)$attemptCount ?></td>

            <?php
            // Display best result with the same background color as the student name cell
            if ($result['best_index'] !== -1) {
              $bestAttempt = (float)$attempts[$result['best_index']];
              $isGlobalBest = abs($bestAttempt - $globalBestResult) < 0.001;
              $crownSymbol = $isGlobalBest ? ' 👑' : '';
              $classes = [$completionClass];

              if ($isGlobalBest) {
                $classes[] = 'global-best-result';
              }

              $classAttr = ' class="' . implode(' ', $classes) . '"';
              echo "<td{$classAttr}>" . round($bestAttempt) . " s" . $crownSymbol . "</td>";
            } else {
              echo "<td class=\"{$completionClass}\">-</td>";
            }
            ?>
            <?php if ($isAdmin): ?>
              <td>
                <a class="view-details-link" href="?page=results&exercise=<?= $result['exercise_id'] ?>&summary=0&email=<?= urlencode($result['email']) ?>">🔍</a>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  // Toggle between summary and detailed view
  document.getElementById('summary-toggle').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('summary', this.checked ? '1' : '0');
    window.location.href = url.toString();
  });

  // Make table headers sortable
  document.querySelectorAll('th').forEach((header, index) => {
    header.style.cursor = 'pointer';
    header.addEventListener('click', () => {
      const table = header.closest('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const isNumeric = !isNaN(rows[0].children[index].innerText.trim());
      const isDate = index === 0; // First column is timestamp
      const ascending = header.dataset.sortOrder !== 'asc';
      rows.sort((a, b) => {
        let aText = a.children[index].innerText.trim();
        let bText = b.children[index].innerText.trim();

        if (isDate) {
          // Parse date in format dd.mm.yyyy HH:MM
          const aParts = aText.split(' ');
          const bParts = bText.split(' ');

          const aDateParts = aParts[0].split('.');
          const bDateParts = bParts[0].split('.');

          // Create date objects (format: yyyy-mm-dd HH:MM)
          const aDate = new Date(`${aDateParts[2]}-${aDateParts[1]}-${aDateParts[0]} ${aParts[1]}`);
          const bDate = new Date(`${bDateParts[2]}-${bDateParts[1]}-${bDateParts[0]} ${bParts[1]}`);

          return ascending ? aDate - bDate : bDate - aDate;
        } else if (isNumeric) {
          return ascending ? (aText - bText) : (bText - aText);
        } else {
          return ascending ? aText.localeCompare(bText) : bText.localeCompare(aText);
        }
      });
      rows.forEach(row => tbody.appendChild(row));
      document.querySelectorAll('th').forEach(th => th.removeAttribute('data-sort-order'));
      header.dataset.sortOrder = ascending ? 'asc' : 'desc';
    });
  });
</script>
