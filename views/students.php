<h2>Õpilaste klassid</h2>

<?php if (empty($students)): ?>
    <p>Ühtegi õpilast pole veel tulemusi esitanud.</p>
<?php else: ?>
    <!-- Grade statistics -->
    <?php if (!empty($gradeStats)): ?>
        <div class="grade-stats">
            <h3>Statistika</h3>
            <div class="stats-grid">
                <?php foreach ($gradeStats as $stat): ?>
                    <div class="stat-item">
                        <strong><?= htmlspecialchars($stat['grade_label']) ?>:</strong> 
                        <?= $stat['count'] ?> õpilast
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Students table -->
    <table class="students-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nimi</th>
                <th>Email</th>
                <th>Klass</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td>
                        <select 
                            class="grade-select" 
                            data-email="<?= htmlspecialchars($student['email']) ?>"
                            id="grade-<?= htmlspecialchars($student['email']) ?>"
                        >
                            <option value="">-- Vali klass --</option>
                            <option value="5r" <?= $student['grade'] === '5r' ? 'selected' : '' ?>>5r</option>
                            <option value="7r" <?= $student['grade'] === '7r' ? 'selected' : '' ?>>7r</option>
                            <option value="8r" <?= $student['grade'] === '8r' ? 'selected' : '' ?>>8r</option>
                        </select>
                        <span class="save-status" id="status-<?= htmlspecialchars($student['email']) ?>"></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
    .grade-stats {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    
    .stat-item {
        background-color: white;
        padding: 10px;
        border-radius: 3px;
        border: 1px solid #dee2e6;
    }
    
    .students-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .students-table th,
    .students-table td {
        border: 1px solid #999;
        padding: 8px 12px;
        text-align: left;
    }
    
    .students-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }
    
    .students-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .grade-select {
        padding: 5px 8px;
        border: 1px solid #ccc;
        border-radius: 3px;
        background-color: white;
        min-width: 120px;
    }
    
    .save-status {
        margin-left: 10px;
        font-size: 0.9em;
        font-weight: bold;
    }
    
    .save-status.success {
        color: #28a745;
    }
    
    .save-status.error {
        color: #dc3545;
    }
    
    .save-status.saving {
        color: #007bff;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all grade select dropdowns
    const gradeSelects = document.querySelectorAll('.grade-select');
    
    gradeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const email = this.dataset.email;
            const grade = this.value;
            const statusElement = document.getElementById('status-' + email);
            
            // Show saving status
            statusElement.textContent = 'Salvestamine...';
            statusElement.className = 'save-status saving';
            
            // Send AJAX request to save the grade
            fetch('save_grade.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    grade: grade
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusElement.textContent = '✓ Salvestatud';
                    statusElement.className = 'save-status success';
                    
                    // Clear success message after 3 seconds
                    setTimeout(() => {
                        statusElement.textContent = '';
                        statusElement.className = 'save-status';
                    }, 3000);
                } else {
                    statusElement.textContent = '✗ Viga: ' + (data.message || 'Salvestamine ebaõnnestus');
                    statusElement.className = 'save-status error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusElement.textContent = '✗ Võrgu viga';
                statusElement.className = 'save-status error';
            });
        });
    });
});
</script>
