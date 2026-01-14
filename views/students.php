<?php
$activeTab = $activeTab ?? 'active';
$period = $period ?? '30';
$groupBy = $groupBy ?? 'day';
$isAdmin = $isAdmin ?? false;
$weekOffset = $weekOffset ?? 0;
$weekInfo = $weekInfo ?? null;
$dateFrom = $dateFrom ?? date('Y-m-d', strtotime('monday this week'));
$dateTo = $dateTo ?? date('Y-m-d');
$periodPreset = $periodPreset ?? 'week';

// Non-admins can only see 'active' tab
if (!$isAdmin && $activeTab === 'classes') {
    $activeTab = 'active';
}
?>

<style>
    .tabs {
        display: flex;
        border-bottom: 2px solid #ddd;
        margin-bottom: 20px;
    }
    .tab {
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        background: none;
        font-size: 16px;
        color: #666;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        text-decoration: none;
    }
    .tab:hover {
        color: #333;
        background: #f5f5f5;
    }
    .tab.active {
        color: #4CAF50;
        border-bottom-color: #4CAF50;
        font-weight: bold;
    }
    /* Period filter styles */
    .period-filter-container {
        margin-bottom: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e0e0e0;
    }
    .period-display {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .period-label {
        font-weight: 600;
        color: #333;
        font-size: 15px;
    }
    .period-dates {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .date-input-wrapper {
        position: relative;
    }
    .date-input {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        background: white;
        min-width: 120px;
    }
    .date-input:hover {
        border-color: #4CAF50;
    }
    .date-input:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }
    .date-separator {
        color: #666;
        font-weight: 500;
    }
    .period-presets {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .preset-btn {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        color: #555;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .preset-btn:hover {
        background: #f0f0f0;
        border-color: #bbb;
    }
    .preset-btn.active {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }
    .student-count {
        margin-left: auto;
        color: #666;
        font-size: 14px;
        padding: 6px 12px;
        background: white;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .student-total {
        margin-left: 15px;
        color: #666;
        font-size: 14px;
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
    /* Alternating day colors */
    tr.day-even td {
        background-color: #f5f5f5;
    }
    tr.day-odd td {
        background-color: #e8e8e8;
    }
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
    .save-status.success { color: #28a745; }
    .save-status.error { color: #dc3545; }
    .save-status.saving { color: #007bff; }
</style>

<h2>Õpilased</h2>

<div class="tabs">
    <a href="?page=students&tab=active" class="tab <?= $activeTab === 'active' ? 'active' : '' ?>">Aktiivsed õpilased</a>
    <?php if ($isAdmin): ?>
        <a href="?page=students&tab=classes" class="tab <?= $activeTab === 'classes' ? 'active' : '' ?>">Klassid</a>
    <?php endif; ?>
</div>

<?php if ($activeTab === 'active'): ?>
    <!-- Active Students Tab -->
    <div class="period-filter-container">
        <div class="period-display">
            <span class="period-label">Periood</span>
            <div class="period-dates">
                <div class="date-input-wrapper">
                    <input type="date" id="dateFrom" class="date-input" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <span class="date-separator">–</span>
                <div class="date-input-wrapper">
                    <input type="date" id="dateTo" class="date-input" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
            </div>
            <span class="student-count"><?= count($activeStudents) ?> aktiivset õpilast</span>
        </div>
        <div class="period-presets">
            <button type="button" class="preset-btn <?= $periodPreset === 'today' ? 'active' : '' ?>" data-preset="today">Täna</button>
            <button type="button" class="preset-btn <?= $periodPreset === 'week' ? 'active' : '' ?>" data-preset="week">Jooksev nädal</button>
            <button type="button" class="preset-btn <?= $periodPreset === 'month' ? 'active' : '' ?>" data-preset="month">Jooksev kuu</button>
            <button type="button" class="preset-btn <?= $periodPreset === 'prevmonth' ? 'active' : '' ?>" data-preset="prevmonth">Eelmine kuu</button>
            <button type="button" class="preset-btn <?= $periodPreset === 'prevmonth_to_today' ? 'active' : '' ?>" data-preset="prevmonth_to_today">Eelmise kuu algusest tänaseni</button>
            <button type="button" class="preset-btn <?= $periodPreset === 'prevyear' ? 'active' : '' ?>" data-preset="prevyear">Eelmine aasta</button>
        </div>
    </div>

    <?php
    // Flatten studentsByDay into a single array for table display
    $allStudentRows = [];
    foreach ($studentsByDay as $date => $dayStudents) {
        foreach ($dayStudents as $student) {
            $allStudentRows[] = [
                'date' => $date,
                'name' => $student['name'],
                'email' => $student['email'],
                'result_count' => $student['result_count'],
                'total_seconds' => $student['total_seconds'] ?? 0
            ];
        }
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>Kuupäev</th>
                <th>Õpilane</th>
                <th>Email</th>
                <th>Ülesandeid</th>
                <th>Aeg</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($allStudentRows as $row): ?>
            <?php
            $dateObj = new DateTime($row['date']);
            $formattedDate = $dateObj->format('d.m.Y');
            $dayName = ['P', 'E', 'T', 'K', 'N', 'R', 'L'][$dateObj->format('w')];
            $totalMinutes = round($row['total_seconds'] / 60);
            ?>
            <tr>
                <td><?= $dayName ?> <?= $formattedDate ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['result_count'] ?></td>
                <td><?= $totalMinutes ?> min</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($allStudentRows)): ?>
        <p style="margin-top: 20px;">Valitud perioodil pole ühtegi aktiivset õpilast.</p>
    <?php endif; ?>

    <script>
        // Apply alternating day colors
        function applyDayColors() {
            const tbody = document.querySelector('table tbody');
            if (!tbody) return;
            const allRows = Array.from(tbody.querySelectorAll('tr'));
            let lastDate = null;
            let dayIndex = 0;
            allRows.forEach(row => {
                const dateText = row.querySelectorAll('td')[0].innerText.trim();
                if (dateText !== lastDate) {
                    if (lastDate !== null) dayIndex++;
                    lastDate = dateText;
                }
                row.classList.remove('day-even', 'day-odd');
                row.classList.add(dayIndex % 2 === 0 ? 'day-even' : 'day-odd');
            });
        }

        applyDayColors();

        // Period filter functionality
        (function() {
            const dateFromInput = document.getElementById('dateFrom');
            const dateToInput = document.getElementById('dateTo');
            const presetButtons = document.querySelectorAll('.preset-btn');

            // Calculate preset dates
            function getPresetDates(preset) {
                const today = new Date();
                const year = today.getFullYear();
                const month = today.getMonth();
                const day = today.getDate();
                const dayOfWeek = today.getDay(); // 0 = Sunday

                let fromDate, toDate;

                switch(preset) {
                    case 'today':
                        fromDate = toDate = today;
                        break;
                    case 'week':
                        // Monday of current week
                        const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
                        fromDate = new Date(year, month, day + mondayOffset);
                        toDate = today;
                        break;
                    case 'month':
                        fromDate = new Date(year, month, 1);
                        toDate = today;
                        break;
                    case 'prevmonth':
                        fromDate = new Date(year, month - 1, 1);
                        toDate = new Date(year, month, 0); // Last day of previous month
                        break;
                    case 'prevmonth_to_today':
                        fromDate = new Date(year, month - 1, 1);
                        toDate = today;
                        break;
                    case 'prevyear':
                        fromDate = new Date(year - 1, 0, 1);
                        toDate = new Date(year - 1, 11, 31);
                        break;
                    default:
                        return null;
                }

                return {
                    from: formatDate(fromDate),
                    to: formatDate(toDate)
                };
            }

            function formatDate(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function navigateWithDates(from, to, preset) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', 'students');
                params.set('tab', 'active');
                params.set('from', from);
                params.set('to', to);
                if (preset) {
                    params.set('preset', preset);
                } else {
                    params.delete('preset');
                }
                // Remove old week parameter
                params.delete('week');
                window.location.href = '?' + params.toString();
            }

            // Preset button clicks
            presetButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const preset = this.dataset.preset;
                    const dates = getPresetDates(preset);
                    if (dates) {
                        navigateWithDates(dates.from, dates.to, preset);
                    }
                });
            });

            // Date input changes
            dateFromInput.addEventListener('change', function() {
                navigateWithDates(this.value, dateToInput.value, null);
            });

            dateToInput.addEventListener('change', function() {
                navigateWithDates(dateFromInput.value, this.value, null);
            });
        })();
    </script>

<?php else: ?>
    <!-- Classes Tab -->
    <h3>Õpilaste klassid</h3>

    <?php if (empty($students)): ?>
        <p>Ühtegi õpilast pole veel tulemusi esitanud.</p>
    <?php else: ?>
        <!-- Grade statistics -->
        <?php if (!empty($gradeStats)): ?>
            <div class="grade-stats">
                <h4>Statistika</h4>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const gradeSelects = document.querySelectorAll('.grade-select');

        gradeSelects.forEach(select => {
            select.addEventListener('change', function() {
                const email = this.dataset.email;
                const grade = this.value;
                const statusElement = document.getElementById('status-' + email);

                statusElement.textContent = 'Salvestamine...';
                statusElement.className = 'save-status saving';

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
<?php endif; ?>
