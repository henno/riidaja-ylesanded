<?php
if ($exerciseFilter) {
  echo "<h2>Tulemused – Ülesanne " . htmlspecialchars($exerciseFilter) . "</h2>";
  echo "<p><a href=\"?page=results\">« Kõik tulemused</a></p>";
} else {
  echo "<h2>Kõik tulemused</h2>";
}
?>
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
      <td><?= $row['elapsed'] ?></td>
      <?php if ($isAdmin): ?>
        <td><a class="delete-link" href="?page=results&delete=<?= $row['id'] ?>" onclick="return confirm('Kustuta see kirje?')">🗑</a></td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<script>
  document.querySelectorAll('th').forEach((header, index) => {
    header.style.cursor = 'pointer';
    header.addEventListener('click', () => {
      const table = header.closest('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const isNumeric = !isNaN(rows[0].children[index].innerText.trim());
      const ascending = header.dataset.sortOrder !== 'asc';
      rows.sort((a, b) => {
        let aText = a.children[index].innerText.trim();
        let bText = b.children[index].innerText.trim();
        return isNumeric
          ? ascending ? (aText - bText) : (bText - aText)
          : ascending ? aText.localeCompare(bText) : bText.localeCompare(aText);
      });
      rows.forEach(row => tbody.appendChild(row));
      document.querySelectorAll('th').forEach(th => th.removeAttribute('data-sort-order'));
      header.dataset.sortOrder = ascending ? 'asc' : 'desc';
    });
  });
  </script>