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
</style>
<table>
  <thead>
    <tr><th>Ajatempel</th><th>Õpilane</th><th>Email</th><th>Harjutus</th><th>Tulemus (s)</th><?php if ($isAdmin) echo '<th></th>'; ?></tr>
  </thead>
  <tbody>
  <?php foreach ($results as $row): ?>
    <?php $formatted = date('d.m.Y H:i', strtotime($row['timestamp'])); ?>
    <tr>
      <td><?= $formatted ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= htmlspecialchars($row['exercise_id']) ?></td>
      <?php if ($row['exercise_id'] === '006'): ?>
        <?php $wpm = $row['elapsed']; $failed = $wpm < 0; ?>
        <td style="<?= $failed ? 'color: #f44336;' : 'color: #4CAF50;' ?>"><?= abs(round($wpm)) ?> WPM <?= $failed ? '✗' : '✓' ?></td>
      <?php else: ?>
        <td><?= round($row['elapsed']) ?> s</td>
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
  </script>
