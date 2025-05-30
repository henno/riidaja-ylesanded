<?php
// Get the active tab from the URL or default to 'harjutused'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'harjutused';

if ($exerciseFilter) {
  echo "<h2>Tulemused – Ülesanne " . htmlspecialchars($exerciseFilter) . "</h2>";
  echo "<p><a href=\"?page=results&tab=" . htmlspecialchars($activeTab) . "\">« Kõik tulemused</a></p>";
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

<!-- Tabs -->
<div class="tabs">
  <a href="?page=results&tab=harjutused" class="tab <?php echo $activeTab === 'harjutused' ? 'active' : ''; ?>">Harjutused</a>
  <a href="?page=results&tab=opilased" class="tab <?php echo $activeTab === 'opilased' ? 'active' : ''; ?>">Õpilased</a>
</div>

<style>
  .view-toggle {
    margin: 15px 0;
  }
  .tabs {
    display: flex;
    margin: 20px 0;
    border-bottom: 1px solid #ccc;
  }
  .tab {
    padding: 10px 20px;
    text-decoration: none;
    color: #333;
    border: 1px solid #ccc;
    border-bottom: none;
    border-radius: 4px 4px 0 0;
    margin-right: 5px;
    background-color: #f5f5f5;
  }
  .tab.active {
    background-color: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
    font-weight: bold;
  }
  .exercise-header, .student-header {
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
    background-color: #f0f0f0; /* Light gray background for undone exercises */
    color: #666; /* Darker gray text */
  }
</style>

<?php if ($activeTab === 'harjutused'): ?>
  <?php if (empty($summaryResults)): ?>
    <p>Tulemusi pole.</p>
  <?php else: ?>
    <!-- Exercise filter input -->
    <div style="margin-bottom: 10px;">
      <input type="text" id="exerciseFilter" placeholder="Filtreeri harjutusi..." style="padding: 5px; width: 200px;">
    </div>

    <?php foreach ($summaryResults as $exerciseId => $exerciseResults): ?>
      <div class="exercise-header">Harjutus <?php echo htmlspecialchars($exerciseId); ?></div>

      <?php
      // Check if this exercise has any attempts
      $hasAttempts = !empty($exerciseResults);

      if (!$hasAttempts) {
        // Display a message for exercises with no attempts
        echo '<table>';
        echo '<thead><tr><th>Õpilane</th><th>Tulemusi</th><th data-bs-toggle="tooltip" data-bs-placement="top" title="Õpilase parim tulemus (kõikide õpilaste keskmine tulemus)">Parim Tulemus</th>';
        if ($isAdmin) echo '<th></th>';
        echo '</tr></thead>';
        echo '<tbody><tr><td colspan="' . ($isAdmin ? '4' : '3') . '" class="no-attempt">Keegi pole seda harjutust veel teinud</td></tr></tbody>';
        echo '</table>';
      } else {
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
            <th data-bs-toggle="tooltip" data-bs-placement="top" title="Õpilase parim tulemus (kõikide õpilaste keskmine tulemus)">Parim Tulemus</th>
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

            // Check if this is an undone exercise
            $isUndone = empty($result['attempts']);
            $completionClass = $isUndone ? 'no-attempt' :
                              ($attemptCount >= 3 ? 'completion-three-or-more' : 'completion-under-three');
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
                echo "<td{$classAttr} data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" title=\"" . htmlspecialchars($result['name']) . " parim tulemus (kõikide õpilaste keskmine tulemus)\">" . round($bestAttempt) . " s (" . round($globalAverages[$exerciseId]) . " s)" . $crownSymbol . "</td>";
              } else {
                echo "<td class=\"{$completionClass}\">-</td>";
              }
              ?>
              <?php if ($isAdmin): ?>
                <td>
                  <a class="view-details-link" href="?page=results&exercise=<?= $result['exercise_id'] ?>&summary=0&email=<?= urlencode($result['email']) ?>&tab=<?= htmlspecialchars($activeTab) ?>">🔍</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php } ?>
    <?php endforeach; ?>
  <?php endif; ?>
<?php elseif ($activeTab === 'opilased'): ?>
  <?php if (empty($studentResults)): ?>
    <p>Tulemusi pole.</p>
  <?php else: ?>
    <!-- Student filter input -->
    <div style="margin-bottom: 10px;">
      <input type="text" id="studentFilter" placeholder="Filtreeri õpilasi..." style="padding: 5px; width: 200px;">
    </div>

    <?php
    // Get student grades and group students by grade
    require_once __DIR__ . '/../models/StudentsModel.php';
    $studentsModel = new StudentsModel();
    $studentsWithGrades = $studentsModel->getAllStudents();

    // Create a lookup for student grades
    $studentGrades = [];
    foreach ($studentsWithGrades as $student) {
      $studentGrades[$student['email']] = $student['grade'];
    }

    // Group students by grade
    $studentsByGrade = [
      '5r' => [],
      '7r' => [],
      '8r' => [],
      'Määramata' => []
    ];

    foreach ($studentResults as $email => $student) {
      $grade = isset($studentGrades[$email]) ? $studentGrades[$email] : null;
      $gradeKey = $grade ?: 'Määramata';

      // Add grade info to student data
      $student['grade'] = $grade;
      $student['email'] = $email;

      $studentsByGrade[$gradeKey][] = $student;
    }

    // Sort students within each grade by name
    foreach ($studentsByGrade as $grade => $students) {
      usort($studentsByGrade[$grade], function($a, $b) {
        return strcmp($a['name'], $b['name']);
      });
    }
    ?>

    <!-- Add CSS for clickable exercise cells and matrix layout -->
    <style>
      .exercise-cell {
        cursor: pointer;
        text-decoration: none;
        color: inherit;
      }
      .exercise-cell:hover {
        text-decoration: underline;
      }

      /* Matrix table layout styling */
      .student-header {
        margin-bottom: 0;
      }
      .student-header + table {
        margin-top: 0;
        table-layout: fixed;
        width: 100%;
      }
      .student-header + table th:first-child,
      .student-header + table td:first-child {
        width: 60px; /* Position column */
      }
      .student-header + table th:nth-child(2),
      .student-header + table td:nth-child(2) {
        width: 150px; /* Student name column */
      }
      .student-header + table th:nth-child(n+3),
      .student-header + table td:nth-child(n+3) {
        width: calc((100% - 210px) / <?php echo count($allExercises); ?>); /* Exercise columns */
      }
    </style>

    <?php foreach ($studentsByGrade as $gradeLabel => $gradeStudents): ?>
      <?php if (!empty($gradeStudents)): ?>
        <?php
        // Calculate best results for each exercise within this grade section
        $gradeBestResults = [];

        // Initialize best results array for all exercises
        foreach ($allExercises as $exercise) {
          $gradeBestResults[$exercise['id']] = PHP_FLOAT_MAX;
        }

        // Find the best result for each exercise among students in this grade
        foreach ($gradeStudents as $student) {
          foreach ($student['exercises'] as $exercise) {
            $exerciseId = $exercise['exercise_id'];

            // Only consider exercises with attempts
            if (!empty($exercise['attempts'])) {
              $attempts = explode(',', $exercise['attempts']);

              // Find student's best result for this exercise
              $studentBest = PHP_FLOAT_MAX;
              foreach ($attempts as $attempt) {
                $attempt = (float)$attempt;
                if ($attempt < $studentBest) {
                  $studentBest = $attempt;
                }
              }

              // Update grade best if this student's result is better
              if ($studentBest < $gradeBestResults[$exerciseId]) {
                $gradeBestResults[$exerciseId] = $studentBest;
              }
            }
          }
        }

        // Convert PHP_FLOAT_MAX to null for exercises with no attempts in this grade
        foreach ($gradeBestResults as $exerciseId => $bestTime) {
          if ($bestTime === PHP_FLOAT_MAX) {
            $gradeBestResults[$exerciseId] = null;
          }
        }
        ?>

        <div class="student-header"><?php echo htmlspecialchars($gradeLabel); ?></div>

        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Õpilane</th>
              <?php foreach ($allExercises as $exercise): ?>
                <th><?php echo htmlspecialchars($exercise['id']); ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($gradeStudents as $index => $student): ?>
              <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>

                <?php
                // Create lookup for student's exercise data
                $studentExercises = [];
                foreach ($student['exercises'] as $exercise) {
                  $studentExercises[$exercise['exercise_id']] = $exercise;
                }
                ?>

                <?php foreach ($allExercises as $exercise): ?>
                  <?php
                  $exerciseId = $exercise['id'];
                  $exerciseData = isset($studentExercises[$exerciseId]) ? $studentExercises[$exerciseId] : null;

                  if ($exerciseData && !empty($exerciseData['attempts'])) {
                    // Student has attempts for this exercise
                    $attempts = explode(',', $exerciseData['attempts']);
                    $attemptCount = count($attempts);

                    // Find best result
                    $bestResult = PHP_FLOAT_MAX;
                    foreach ($attempts as $attempt) {
                      $attempt = (float)$attempt;
                      if ($attempt < $bestResult) {
                        $bestResult = $attempt;
                      }
                    }

                    // Determine completion class
                    $completionClass = $attemptCount >= 3 ? 'completion-three-or-more' : 'completion-under-three';

                    // Get global average
                    $globalAverage = isset($globalAverages[$exerciseId]) ? $globalAverages[$exerciseId] : null;

                    // Format cell content
                    $cellContent = round($bestResult) . ' s';
                    if ($globalAverage !== null) {
                      $cellContent .= ' (' . round($globalAverage) . ' s)';
                    }

                    // Add crown icon if this is the best result for this exercise in this grade
                    if ($gradeBestResults[$exerciseId] !== null && abs($bestResult - $gradeBestResults[$exerciseId]) < 0.001) {
                      $cellContent .= ' 👑';
                    }

                    $tooltipTitle = htmlspecialchars($student['name']) . ' parim tulemus (kõikide õpilaste keskmine tulemus)';
                    $detailUrl = '?page=results&exercise=' . urlencode($exerciseId) . '&summary=0&email=' . urlencode($student['email']) . '&tab=' . htmlspecialchars($activeTab);

                    echo '<td class="' . $completionClass . '">';
                    echo '<a href="' . $detailUrl . '" class="exercise-cell" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tooltipTitle . '">';
                    echo $cellContent;
                    echo '</a>';
                    echo '</td>';
                  } else {
                    // Student has no attempts for this exercise
                    echo '<td class="no-attempt">-</td>';
                  }
                  ?>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>

<script>
  // Toggle between summary and detailed view
  document.getElementById('summary-toggle').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('summary', this.checked ? '1' : '0');
    // Preserve the tab parameter
    const activeTab = url.searchParams.get('tab') || 'harjutused';
    url.searchParams.set('tab', activeTab);
    window.location.href = url.toString();
  });

  // Exercise filter functionality in Harjutused tab
  const exerciseFilter = document.getElementById('exerciseFilter');
  if (exerciseFilter) {
    exerciseFilter.addEventListener('input', function() {
      const filterValue = this.value.toLowerCase();
      const exerciseHeaders = document.querySelectorAll('.exercise-header');
      let matchFound = false;

      exerciseHeaders.forEach(header => {
        // Get the exercise name/number from the header
        const exerciseName = header.textContent.toLowerCase();
        const table = header.nextElementSibling;

        // Skip if table is not found or not a table
        if (!table || table.tagName !== 'TABLE') return;

        // Show/hide based on filter match
        if (exerciseName.includes(filterValue)) {
          header.style.display = '';
          table.style.display = '';
          matchFound = true;
        } else {
          header.style.display = 'none';
          table.style.display = 'none';
        }
      });

      // Show a message when no matches are found
      let noMatchMessage = document.getElementById('no-exercise-matches');
      if (!matchFound && filterValue) {
        if (!noMatchMessage) {
          noMatchMessage = document.createElement('div');
          noMatchMessage.id = 'no-exercise-matches';
          noMatchMessage.style.padding = '10px';
          noMatchMessage.style.backgroundColor = '#f8f8f8';
          noMatchMessage.style.marginTop = '10px';
          noMatchMessage.style.borderRadius = '4px';
          exerciseFilter.parentNode.after(noMatchMessage);
        }
        noMatchMessage.textContent = `Ühtegi harjutust ei leitud otsinguga "${filterValue}"`;
        noMatchMessage.style.display = '';
      } else if (noMatchMessage) {
        noMatchMessage.style.display = 'none';
      }
    });
  }

  // Student filter functionality in Õpilased tab
  const studentFilter = document.getElementById('studentFilter');
  if (studentFilter) {
    studentFilter.addEventListener('input', function() {
      const filterValue = this.value.toLowerCase();
      let matchFound = false;

      // Get all grade sections (headers and their tables)
      const gradeHeaders = document.querySelectorAll('.student-header');

      gradeHeaders.forEach(header => {
        const table = header.nextElementSibling;
        if (!table || table.tagName !== 'TABLE') return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');
        let gradeHasMatches = false;

        // Check each student row in this grade
        rows.forEach(row => {
          const studentNameCell = row.querySelector('td:nth-child(2)'); // Student name is now in 2nd column

          if (studentNameCell) {
            const studentName = studentNameCell.textContent.toLowerCase();

            // Show/hide row based on filter match (name only)
            if (studentName.includes(filterValue)) {
              row.style.display = '';
              gradeHasMatches = true;
              matchFound = true;
            } else {
              row.style.display = 'none';
            }
          }
        });

        // Show/hide entire grade section based on whether it has matches
        if (gradeHasMatches || !filterValue) {
          header.style.display = '';
          table.style.display = '';
        } else {
          header.style.display = 'none';
          table.style.display = 'none';
        }
      });

      // Show a message when no matches are found
      let noMatchMessage = document.getElementById('no-student-matches');
      if (!matchFound && filterValue) {
        if (!noMatchMessage) {
          noMatchMessage = document.createElement('div');
          noMatchMessage.id = 'no-student-matches';
          noMatchMessage.style.padding = '10px';
          noMatchMessage.style.backgroundColor = '#f8f8f8';
          noMatchMessage.style.marginTop = '10px';
          noMatchMessage.style.borderRadius = '4px';
          studentFilter.parentNode.after(noMatchMessage);
        }
        noMatchMessage.textContent = `Ühtegi õpilast ei leitud otsinguga "${filterValue}"`;
        noMatchMessage.style.display = '';
      } else if (noMatchMessage) {
        noMatchMessage.style.display = 'none';
      }
    });
  }

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

  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
</script>
