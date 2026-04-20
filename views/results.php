<?php
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'harjutused';
$showBackLink = $emailFilter || $exerciseFilter;
?>
<h2 id="results-title">Tulemused</h2>
<?php if ($showBackLink): ?>
<p><a href="?page=results&tab=<?= htmlspecialchars($activeTab) ?>">« Kõik tulemused</a></p>
<?php endif; ?>

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
  .bulk-actions {
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .bulk-delete-btn {
    background: #f44336;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
  }
  .bulk-delete-btn:hover {
    background: #d32f2f;
  }
  .bulk-delete-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
  }
  #select-all {
    cursor: pointer;
    width: 16px;
    height: 16px;
    display: block;
    margin: 0 auto;
  }
  .row-checkbox {
    cursor: pointer;
    width: 16px;
    height: 16px;
  }
  td:has(#select-all) {
    text-align: center;
  }
  #include-failures {
    margin-right: 6px;
  }
</style>

<div class="filter-row" style="padding: 8px 6px; background: #f9f9f9; display: flex; align-items: center; gap: 15px;">
  <label style="cursor: pointer; user-select: none;">
    <input type="checkbox" id="include-failures">
    Näita ka ebaõnnestumisi
  </label>
</div>

<table>
  <thead>
    <?php
    // Determine column header based on exercise filter
    $resultColumnHeader = 'Tulemus';
    if ($exerciseFilter) {
        global $resultsModel;
        if (!isset($resultsModel)) {
            require_once __DIR__ . '/../models/ResultsModel.php';
            $resultsModel = new ResultsModel();
        }
        $filteredExercise = $resultsModel->getExercise($exerciseFilter);
        $resultColumnHeader = ($filteredExercise && $filteredExercise['result_type'] === 'wpm') ? 'Tulemus (WPM)' : 'Tulemus (s)';
    } else {
        $resultColumnHeader = 'Tulemus';
    }
    ?>
    <tr><?php if ($isAdmin) echo '<th></th>'; ?><th>Ajatempel</th><th>Õpilane</th><th>Email</th><th>Harjutus</th><th><?= $resultColumnHeader ?></th><?php if ($isAdmin) echo '<th></th>'; ?></tr>
    <tr class="filter-row">
      <?php if ($isAdmin): ?><td><input type="checkbox" id="select-all" title="Vali kõik nähtavad"></td><?php endif; ?>
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
            <?php foreach ($allExercises as $ex): ?>
              <option value="<?= htmlspecialchars($ex['id']) ?>"<?= ($exerciseFilter == $ex['id']) ? ' selected' : '' ?>><?= htmlspecialchars($ex['id']) ?></option>
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
    <tr data-id="<?= $row['id'] ?>">
      <?php if ($isAdmin): ?><td><input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>"></td><?php endif; ?>
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
        <td><button class="delete-link btn btn-link p-0 border-0" data-id="<?= $row['id'] ?>">🗑</button></td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php if ($isAdmin): ?>
<div class="bulk-actions" id="bulk-actions" style="display: none;">
  <button class="bulk-delete-btn" id="bulk-delete-btn" disabled>Kustuta valitud</button>
  <span id="selected-count"></span>
</div>
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
  const includeFailures = document.getElementById('include-failures');
  const tbody = document.querySelector('table tbody');
  const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
  let currentResults = <?= json_encode($results) ?>;
  let allExercises = <?= json_encode($allExercises ?? []) ?>;

  function parseDate(dateStr) {
    const parts = dateStr.split(' ');
    const dateParts = parts[0].split('.');
    return new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
  }

  function parseResult(resultStr) {
    const match = resultStr.match(/(\d+)/);
    return match ? parseInt(match[1]) : null;
  }

  function parseResultFilter(filterStr) {
    filterStr = filterStr.trim();
    if (!filterStr) return null;

    const rangeMatch = filterStr.match(/^(\d+)\s*-\s*(\d+)$/);
    if (rangeMatch) {
      return { min: parseInt(rangeMatch[1]), max: parseInt(rangeMatch[2]) };
    }

    const gtMatch = filterStr.match(/^>\s*(\d+)$/);
    if (gtMatch) {
      return { min: parseInt(gtMatch[1]) + 1, max: null };
    }

    const ltMatch = filterStr.match(/^<\s*(\d+)$/);
    if (ltMatch) {
      return { min: null, max: parseInt(ltMatch[1]) - 1 };
    }

    const numMatch = filterStr.match(/^(\d+)$/);
    if (numMatch) {
      return { exact: parseInt(numMatch[1]) };
    }

    return null;
  }

  function formatDate(timestamp) {
    const d = new Date(timestamp);
    return d.toLocaleDateString('et-EE') + ' ' + d.toLocaleTimeString('et-EE', { hour: '2-digit', minute: '2-digit' });
  }

  function renderResult(row, isAdmin) {
    const failed = row.elapsed < 0;
    const accuracy = row.accuracy !== null ? row.accuracy : null;
    const duration = row.duration !== null ? row.duration : null;
    
    if (row.result_type === 'wpm') {
      const accuracyStr = accuracy !== null ? ', ' + Math.round(accuracy) + '%' : '';
      const durationStr = duration !== null ? ' (' + Math.round(duration) + ' s)' : '';
      return `<td style="${failed ? 'color: #f44336;' : 'color: #4CAF50;'}">${Math.abs(Math.round(row.elapsed))} WPM${accuracyStr}${durationStr} ${failed ? '✗' : '✓'}</td>`;
    } else {
      if (failed && accuracy !== null) {
        return `<td style="color: #f44336;">${Math.round(accuracy)}% ✗</td>`;
      }
      return `<td style="${failed ? 'color: #f44336;' : ''}">${Math.abs(Math.round(row.elapsed))} s ${failed ? '✗' : ''}</td>`;
    }
  }

  function getResultType(exerciseId) {
    const ex = allExercises.find(e => e.id === exerciseId);
    return ex ? ex.result_type : 'time';
  }

  function renderRows(results) {
    tbody.innerHTML = '';
    
    results.forEach(row => {
      const tr = document.createElement('tr');
      tr.dataset.id = row.id;
      const resultType = getResultType(row.exercise_id);
      const rowData = { ...row, result_type: resultType };
      
      let html = '';
      if (isAdmin) {
        html += `<td><input type="checkbox" class="row-checkbox" value="${row.id}"></td>`;
      }
      html += `<td>${formatDate(row.timestamp)}</td>`;
      html += `<td>${escapeHtml(row.name)}</td>`;
      html += `<td>${escapeHtml(row.email)}</td>`;
      html += `<td>${escapeHtml(row.exercise_id)}</td>`;
      html += renderResult(rowData, isAdmin);
      if (isAdmin) {
        html += `<td><button class="delete-link btn btn-link p-0 border-0" data-id="${row.id}">🗑</button></td>`;
      }
      tr.innerHTML = html;
      tbody.appendChild(tr);
    });

    updateBulkUI();
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  async function fetchResults() {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('page', 'api');
      url.searchParams.set('action', 'get_results');
      if (filterExercise.value) url.searchParams.set('exercise', filterExercise.value);
      if (filterEmail.value) url.searchParams.set('email', filterEmail.value);
      if (filterDate.value) url.searchParams.set('date', filterDate.value);
      if (filterName.value) url.searchParams.set('name', filterName.value);
      if (filterResult.value) url.searchParams.set('result', filterResult.value);
      if (includeFailures.checked) url.searchParams.set('include_failures', '1');
      else url.searchParams.delete('include_failures');
      
      const res = await fetch(url.toString());
      const data = await res.json();
      
      currentResults = data.results;
      allExercises = data.allExercises;
      renderRows(currentResults);
      applyFilters();
      updateExerciseDropdown();
      updateTitle();
    } catch (e) {
      console.error('Error fetching results:', e);
    }
  }

  function updateExerciseDropdown() {
    const select = filterExercise;
    const currentValue = select.value;
    select.innerHTML = '<option value="">Kõik</option>';
    allExercises.forEach(ex => {
      const opt = document.createElement('option');
      opt.value = ex.id;
      opt.textContent = ex.id;
      if (ex.id === currentValue) opt.selected = true;
      select.appendChild(opt);
    });
  }

  function updateTitle() {
    const titleEl = document.getElementById('results-title');
    const parts = [];
    
    if (filterExercise.value) {
      parts.push('Ülesanne ' + filterExercise.value);
    }
    if (filterEmail.value) {
      parts.push(filterEmail.value);
    }
    if (filterDate.value) {
      const d = new Date(filterDate.value);
      parts.push(d.toLocaleDateString('et-EE'));
    }
    if (filterName.value) {
      parts.push(filterName.value);
    }
    
    if (parts.length === 0) {
      titleEl.textContent = 'Kõik tulemused';
    } else {
      titleEl.textContent = 'Tulemused – ' + parts.join(' – ');
    }
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
    const resultFilter = parseResultFilter(filterResult.value);

    const rows = Array.from(tbody.querySelectorAll('tr'));
    let visibleCount = 0;
    const totalCount = rows.length;

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const rowDate = parseDate(cells[isAdmin ? 1 : 0].innerText.trim());
      const rowName = cells[isAdmin ? 2 : 1].innerText.toLowerCase().trim();
      const rowEmail = cells[isAdmin ? 3 : 2].innerText.toLowerCase().trim();
      const rowResult = parseResult(cells[isAdmin ? 5 : 4].innerText);

      let visible = true;

      if (dateFilter) {
        const rowDateOnly = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
        const filterDateOnly = new Date(dateFilter.getFullYear(), dateFilter.getMonth(), dateFilter.getDate());
        if (rowDateOnly.getTime() !== filterDateOnly.getTime()) {
          visible = false;
        }
      }

      if (name && !rowName.includes(name)) {
        visible = false;
      }

      if (email && !rowEmail.includes(email)) {
        visible = false;
      }

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

  // Sync all filters with URL on load
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('date')) filterDate.value = urlParams.get('date');
  if (urlParams.has('name')) filterName.value = urlParams.get('name');
  if (urlParams.has('email')) filterEmail.value = urlParams.get('email');
  if (urlParams.has('exercise')) filterExercise.value = urlParams.get('exercise');
  if (urlParams.has('result')) filterResult.value = urlParams.get('result');
  if (urlParams.has('include_failures')) includeFailures.checked = true;
  
  // If URL has server-side filters, fetch fresh data
  if (urlParams.has('exercise') || urlParams.has('email') || urlParams.has('date') || urlParams.has('name') || urlParams.has('include_failures')) {
    fetchResults();
  } else {
    applyFilters();
    updateTitle();
  }

  // Helper to update URL with current filter values
  function updateUrl() {
    const url = new URL(window.location.href);
    if (filterDate.value) url.searchParams.set('date', filterDate.value);
    else url.searchParams.delete('date');
    if (filterName.value) url.searchParams.set('name', filterName.value);
    else url.searchParams.delete('name');
    if (filterEmail.value) url.searchParams.set('email', filterEmail.value);
    else url.searchParams.delete('email');
    if (filterExercise.value) url.searchParams.set('exercise', filterExercise.value);
    else url.searchParams.delete('exercise');
    if (filterResult.value) url.searchParams.set('result', filterResult.value);
    else url.searchParams.delete('result');
    if (includeFailures.checked) url.searchParams.set('include_failures', '1');
    else url.searchParams.delete('include_failures');
    window.history.replaceState({}, document.title, url.toString());
  }

  // Add event listeners for filter inputs - all trigger AJAX fetch
  filterDate.addEventListener('change', () => { updateUrl(); fetchResults(); });
  filterName.addEventListener('input', () => { updateUrl(); fetchResults(); });
  filterEmail.addEventListener('input', () => { updateUrl(); fetchResults(); });
  filterExercise.addEventListener('change', () => { updateUrl(); fetchResults(); });
  filterResult.addEventListener('input', () => { updateUrl(); fetchResults(); });
  includeFailures.addEventListener('change', () => { updateUrl(); fetchResults(); });

  // Clear button functionality
  document.querySelectorAll('.filter-clear-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const inputId = btn.dataset.for;
      const input = document.getElementById(inputId);
      input.value = '';
      updateUrl();
      fetchResults();
    });
  });

  // Apply alternating day colors
  function applyDayColors() {
    let lastDate = null;
    let dayIndex = 0;
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const dateCellIndex = isAdmin ? 1 : 0;
      const dateText = cells[dateCellIndex].innerText.trim().split(' ')[0];

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

  // Select all / bulk delete functionality
  const selectAll = document.getElementById('select-all');
  const bulkActions = document.getElementById('bulk-actions');
  const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
  const selectedCount = document.getElementById('selected-count');

  function updateBulkUI() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const visibleCheckboxes = Array.from(document.querySelectorAll('.row-checkbox')).filter(cb => cb.closest('tr').style.display !== 'none');
    const checkedCount = checkboxes.length;
    
    if (visibleCheckboxes.length > 0) {
      bulkActions.style.display = 'flex';
    } else {
      bulkActions.style.display = 'none';
    }
    
    if (bulkDeleteBtn) bulkDeleteBtn.disabled = checkedCount === 0;
    if (selectedCount) selectedCount.textContent = checkedCount > 0 ? `(${checkedCount} valitud)` : '';
    
    if (selectAll) {
      if (visibleCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      } else {
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        const someChecked = visibleCheckboxes.some(cb => cb.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
      }
    }
  }

  function initBulkDelete() {
    const selAll = document.getElementById('select-all');
    if (selAll) {
      selAll.addEventListener('change', function() {
        const visibleCheckboxes = Array.from(document.querySelectorAll('.row-checkbox')).filter(cb => cb.closest('tr').style.display !== 'none');
        visibleCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkUI();
      });
    }

    // Use event delegation for checkbox changes
    tbody.addEventListener('change', function(e) {
      if (e.target.classList.contains('row-checkbox')) {
        updateBulkUI();
      }
    });

    if (bulkDeleteBtn) {
      bulkDeleteBtn.addEventListener('click', async function() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        if (ids.length === 0 || !confirm(`Kustuta ${ids.length} kirjet?`)) return;
        
        try {
          for (const id of ids) {
            const res = await fetch('?page=api&action=delete_result', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
              const row = document.querySelector(`tr[data-id="${id}"]`);
              if (row) row.remove();
            }
          }
          updateBulkUI();
        } catch (e) {
          alert('Viga kustutamisel');
        }
      });
    }
  }

  function initDeleteLinks() {
    // Use event delegation for delete links
    tbody.addEventListener('click', function(e) {
      const btn = e.target.closest('.delete-link');
      if (!btn) return;
      
      if (!confirm('Kustuta see kirje?')) return;
      const id = btn.dataset.id;
      
      fetch('?page=api&action=delete_result', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const row = btn.closest('tr');
          if (row) row.remove();
        }
      })
      .catch(() => alert('Viga kustutamisel'));
    });
  }

  // Apply day colors after filter changes
  const originalApplyFilters = applyFilters;
  applyFilters = function() {
    originalApplyFilters();
    applyDayColors();
    updateBulkUI();
  };

  if (isAdmin) {
    initBulkDelete();
    initDeleteLinks();
  }

  updateBulkUI();
  applyDayColors();
  </script>
