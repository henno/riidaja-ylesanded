<?php
// Get the active tab from the URL or default to 'harjutused'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'harjutused';

if ($emailFilter && $exerciseFilter) {
  echo "<h2>Tulemused – " . htmlspecialchars($emailFilter) . " – Ülesanne " . htmlspecialchars($exerciseFilter) . "</h2>";
  echo "<p><a href=\"?page=results&tab=" . htmlspecialchars($activeTab) . "\">« Kõik tulemused</a></p>";
} elseif ($emailFilter) {
  echo "<h2>Tulemused – " . htmlspecialchars($emailFilter) . "</h2>";
  echo "<p><a href=\"?page=results&tab=" . htmlspecialchars($activeTab) . "\">« Kõik tulemused</a></p>";
} elseif ($exerciseFilter) {
  echo "<h2>Tulemused – Ülesanne " . htmlspecialchars($exerciseFilter) . "</h2>";
  echo "<p><a href=\"?page=results&tab=" . htmlspecialchars($activeTab) . "\">« Kõik tulemused</a></p>";
} else {
  echo "<h2>Kõik tulemused <span id=\"filter-count\" style=\"font-size: 14px; font-weight: normal; color: #666;\"></span></h2>";
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
  .filter-row td {
    padding: 4px 6px;
    background: #f9f9f9;
  }
  .filter-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }
  .filter-wrapper input,
  .filter-wrapper select {
    width: 100%;
    padding: 5px 24px 5px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    box-sizing: border-box;
  }
  .filter-wrapper input:focus,
  .filter-wrapper select:focus {
    outline: none;
    border-color: #4CAF50;
  }
  .filter-clear-btn {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #999;
    font-size: 14px;
    padding: 2px 4px;
    line-height: 1;
    display: none;
  }
  .filter-clear-btn:hover {
    color: #333;
  }
  .filter-wrapper.has-value .filter-clear-btn {
    display: block;
  }
  /* Select dropdown - move X to the left of the arrow */
  .filter-wrapper.has-select .filter-clear-btn {
    right: 22px;
  }
  .filter-wrapper.has-select select {
    padding-right: 40px;
  }
  .filter-count {
    font-size: 13px;
    color: #666;
    margin-left: 10px;
  }
  /* Alternating day colors */
  tr.day-even td {
    background-color: #f5f5f5;
  }
  tr.day-odd td {
    background-color: #e8e8e8;
  }
</style>

<table>
  <thead>
    <tr><th>Ajatempel</th><th>Õpilane</th><th>Email</th><th>Harjutus</th><th>Tulemus (s)</th><?php if ($isAdmin) echo '<th></th>'; ?></tr>
    <tr class="filter-row">
      <td>
        <div class="filter-wrapper">
          <input type="date" id="filter-date" title="Filtreeri kuupäeva järgi">
          <button class="filter-clear-btn" data-for="filter-date">&times;</button>
        </div>
      </td>
      <td>
        <div class="filter-wrapper">
          <input type="text" id="filter-name" placeholder="Otsi...">
          <button class="filter-clear-btn" data-for="filter-name">&times;</button>
        </div>
      </td>
      <td>
        <div class="filter-wrapper">
          <input type="text" id="filter-email" placeholder="Otsi...">
          <button class="filter-clear-btn" data-for="filter-email">&times;</button>
        </div>
      </td>
      <td>
        <div class="filter-wrapper has-select">
          <select id="filter-exercise">
            <option value="">Kõik</option>
            <?php
            $exercises = [];
            foreach ($results as $row) {
              if (!in_array($row['exercise_id'], $exercises)) {
                $exercises[] = $row['exercise_id'];
              }
            }
            sort($exercises);
            foreach ($exercises as $ex): ?>
              <option value="<?= htmlspecialchars($ex) ?>"><?= htmlspecialchars($ex) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="filter-clear-btn" data-for="filter-exercise">&times;</button>
        </div>
      </td>
      <td>
        <div class="filter-wrapper">
          <input type="text" id="filter-result" placeholder="nt: >50 või 10-30">
          <button class="filter-clear-btn" data-for="filter-result">&times;</button>
        </div>
      </td>
      <?php if ($isAdmin): ?><td></td><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($results as $row): ?>
    <?php $formatted = date('d.m.Y H:i', strtotime($row['timestamp'])); ?>
    <tr>
      <td><?= $formatted ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= htmlspecialchars($row['exercise_id']) ?></td>
      <?php
      // Get exercise info to determine result type
      global $resultsModel;
      if (!isset($resultsModel)) {
        require_once __DIR__ . '/../models/ResultsModel.php';
        $resultsModel = new ResultsModel();
      }
      $exercise = $resultsModel->getExercise($row['exercise_id']);
      $resultType = $exercise ? $exercise['result_type'] : 'time';
      ?>
      <?php if ($resultType === 'wpm'): ?>
        <?php
        $wpm = $row['elapsed'];
        $failed = $wpm < 0;
        $accuracy = isset($row['accuracy']) ? $row['accuracy'] : null;
        $duration = isset($row['duration']) ? $row['duration'] : null;
        $accuracyStr = $accuracy !== null ? ', ' . round($accuracy) . '%' : '';
        $durationStr = $duration !== null ? ' (' . round($duration) . ' s)' : '';
        ?>
        <td style="<?= $failed ? 'color: #f44336;' : 'color: #4CAF50;' ?>"><?= abs(round($wpm)) ?> WPM<?= $accuracyStr ?><?= $durationStr ?> <?= $failed ? '✗' : '✓' ?></td>
      <?php else: ?>
        <?php
        $elapsed = $row['elapsed'];
        $failed = $elapsed < 0;
        $accuracy = isset($row['accuracy']) ? $row['accuracy'] : null;
        ?>
        <?php if ($failed && $accuracy !== null): ?>
          <td style="color: #f44336;"><?= round($accuracy) ?>% ✗</td>
        <?php else: ?>
          <td style="<?= $failed ? 'color: #f44336;' : '' ?>"><?= abs(round($elapsed)) ?> s <?= $failed ? '✗' : '' ?></td>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($isAdmin): ?>
        <td><a class="delete-link" href="?page=results&delete=<?= $row['id'] ?>" onclick="return confirm('Kustuta see kirje?')">🗑</a></td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
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

  // Filtering functionality
  const filterDate = document.getElementById('filter-date');
  const filterName = document.getElementById('filter-name');
  const filterEmail = document.getElementById('filter-email');
  const filterExercise = document.getElementById('filter-exercise');
  const filterResult = document.getElementById('filter-result');
  const tbody = document.querySelector('table tbody');
  const allRows = Array.from(tbody.querySelectorAll('tr'));

  function parseDate(dateStr) {
    // Parse dd.mm.yyyy HH:MM format
    const parts = dateStr.split(' ');
    const dateParts = parts[0].split('.');
    return new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
  }

  function parseResult(resultStr) {
    // Extract numeric value from result string (e.g., "123 s" -> 123, "45 WPM" -> 45)
    const match = resultStr.match(/(\d+)/);
    return match ? parseInt(match[1]) : null;
  }

  function parseResultFilter(filterStr) {
    // Parse result filter: ">50", "<30", "10-50", or just "50"
    filterStr = filterStr.trim();
    if (!filterStr) return null;

    // Range: "10-50"
    const rangeMatch = filterStr.match(/^(\d+)\s*-\s*(\d+)$/);
    if (rangeMatch) {
      return { min: parseInt(rangeMatch[1]), max: parseInt(rangeMatch[2]) };
    }

    // Greater than: ">50"
    const gtMatch = filterStr.match(/^>\s*(\d+)$/);
    if (gtMatch) {
      return { min: parseInt(gtMatch[1]) + 1, max: null };
    }

    // Less than: "<50"
    const ltMatch = filterStr.match(/^<\s*(\d+)$/);
    if (ltMatch) {
      return { min: null, max: parseInt(ltMatch[1]) - 1 };
    }

    // Exact or contains number
    const numMatch = filterStr.match(/^(\d+)$/);
    if (numMatch) {
      return { exact: parseInt(numMatch[1]) };
    }

    return null;
  }

  function updateClearButtons() {
    document.querySelectorAll('.filter-wrapper').forEach(wrapper => {
      const input = wrapper.querySelector('input, select');
      if (input.value && input.value !== '') {
        wrapper.classList.add('has-value');
      } else {
        wrapper.classList.remove('has-value');
      }
    });
  }

  function applyFilters() {
    const dateFilter = filterDate.value ? new Date(filterDate.value) : null;
    const name = filterName.value.toLowerCase().trim();
    const email = filterEmail.value.toLowerCase().trim();
    const exercise = filterExercise.value;
    const resultFilter = parseResultFilter(filterResult.value);

    let visibleCount = 0;
    const totalCount = allRows.length;

    allRows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const rowDate = parseDate(cells[0].innerText.trim());
      const rowName = cells[1].innerText.toLowerCase().trim();
      const rowEmail = cells[2].innerText.toLowerCase().trim();
      const rowExercise = cells[3].innerText.trim();
      const rowResult = parseResult(cells[4].innerText);

      let visible = true;

      // Date filter (matches the selected date)
      if (dateFilter) {
        const rowDateOnly = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
        const filterDateOnly = new Date(dateFilter.getFullYear(), dateFilter.getMonth(), dateFilter.getDate());
        if (rowDateOnly.getTime() !== filterDateOnly.getTime()) {
          visible = false;
        }
      }

      // Name filter
      if (name && !rowName.includes(name)) {
        visible = false;
      }

      // Email filter
      if (email && !rowEmail.includes(email)) {
        visible = false;
      }

      // Exercise filter
      if (exercise && rowExercise !== exercise) {
        visible = false;
      }

      // Result filter
      if (resultFilter && rowResult !== null) {
        if (resultFilter.exact !== undefined && rowResult !== resultFilter.exact) {
          visible = false;
        }
        if (resultFilter.min !== null && rowResult < resultFilter.min) {
          visible = false;
        }
        if (resultFilter.max !== null && rowResult > resultFilter.max) {
          visible = false;
        }
      }

      row.style.display = visible ? '' : 'none';
      if (visible) visibleCount++;
    });

    // Update filter count display
    const filterCountEl = document.getElementById('filter-count');
    if (filterCountEl) {
      if (visibleCount < totalCount) {
        filterCountEl.textContent = `(${visibleCount}/${totalCount})`;
      } else {
        filterCountEl.textContent = '';
      }
    }

    updateClearButtons();
  }

  // Add event listeners for filter inputs
  [filterDate, filterName, filterEmail, filterExercise, filterResult].forEach(el => {
    el.addEventListener('input', applyFilters);
    el.addEventListener('change', applyFilters);
  });

  // Clear button functionality
  document.querySelectorAll('.filter-clear-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const inputId = btn.dataset.for;
      const input = document.getElementById(inputId);
      if (input.tagName === 'SELECT') {
        input.value = '';
      } else {
        input.value = '';
      }
      applyFilters();
    });
  });

  // Apply alternating day colors
  function applyDayColors() {
    let lastDate = null;
    let dayIndex = 0;

    allRows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const dateText = cells[0].innerText.trim().split(' ')[0]; // Get just the date part (dd.mm.yyyy)

      if (dateText !== lastDate) {
        if (lastDate !== null) {
          dayIndex++;
        }
        lastDate = dateText;
      }

      row.classList.remove('day-even', 'day-odd');
      row.classList.add(dayIndex % 2 === 0 ? 'day-even' : 'day-odd');
    });
  }

  applyDayColors();
  </script>
