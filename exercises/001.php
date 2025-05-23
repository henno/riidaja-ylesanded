<?php
require_once __DIR__ . '/../models/ResultsModel.php';

$exerciseId = htmlspecialchars($_GET['task'] ?? '001');
$model       = new ResultsModel();
$exercise    = $model->getExercise($exerciseId) ?? [];
$targetTime  = (int) ($exercise['target_time'] ?? 60);
?>
<style>
    #word-table {
        width: 100%;
        max-width: 400px;
        margin: 20px auto;
    }

    input[type="text"] {
        width: 100px;
        padding: 2px;
    }

    .mismatch {
        background-color: #ffcccc;
    }

    #stats-banner {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 250px;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        font-size: 12px;
        line-height: 1.5;
        display: none;
        z-index: 100;
    }

    #stats-banner.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    #stats-banner.warning {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .stats-header {
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .stats-row {
        display: flex;
        justify-content: space-between;
        margin: 2px 0;
    }

    .stats-label {
        font-weight: 500;
    }

    .stats-value {
        font-weight: bold;
    }

    #timer {
        text-align: center;
        font-size: 18px;
        margin: 20px 0;
        font-weight: bold;
    }

    .highlight-red {
        color: red;
        font-weight: bold;
    }
</style>

<p>
    Kopeeri igasse tekstikasti täpselt see sama sõna, mis on vasakul.
    Kui kõik sõnad on õigesti sisestatud, mõõdetakse aeg.
    Sul on aega <?= $targetTime ?> sekundit.
</p>

<table id="word-table">
    <thead>
    <tr><th>#</th><th>Sõna</th><th>Tekstikast</th></tr>
    </thead>
    <tbody></tbody>
</table>

<div id="stats-banner" class="success">
    <div class="stats-header">SAAD HAKKAMA! 👍</div>
    <div class="stats-content"></div>
</div>

<div id="timer">Kulunud aeg: 0.00 s</div>

<script>
    (() => {
        const rows = 30;
        const targetTime = <?= $targetTime ?>;

        const tableBody    = document.querySelector('#word-table tbody');
        const timerDisplay = document.getElementById('timer');
        const statsBanner  = document.getElementById('stats-banner');
        const statsHeader  = statsBanner.querySelector('.stats-header');
        const statsContent = statsBanner.querySelector('.stats-content');

        /* --- build table ---------------------------------------------------- */
        const letters = 'abcdefghijklmnopqrstuvwxyz';
        const randomWord = () =>
            Array.from({ length: 8 }, () => letters[Math.floor(Math.random() * 26)]).join('');

        let html = '';
        for (let i = 0; i < rows; i++) {
            const w = randomWord();
            html += `<tr><td>${i + 1}</td><td>${w}</td><td><input type="text" data-correct="${w}"></td></tr>`;
        }
        tableBody.innerHTML = html;

        const inputs = [...tableBody.querySelectorAll('input')];
        inputs.forEach(input => input.addEventListener('input', handleInput));

        /* --- timers & state -------------------------------------------------- */
        let startTime     = null;
        let timerInterval = null;
        let bestRate      = 0;

        function handleInput() {
            if (startTime === null) {
                startTime = Date.now();
                timerInterval = setInterval(updateTimer, 50);
            }

            let completed  = 0;
            let allCorrect = true;

            inputs.forEach(inp => {
                const ok = inp.value === inp.dataset.correct;
                inp.classList.toggle('mismatch', !ok && inp.value !== '');
                if (ok) completed++;
                else if (inp.value !== '') allCorrect = false;
            });

            if (completed >= 4) updateStats(completed);
            if (allCorrect && completed === rows) finish();
        }

        function updateStats(completed) {
            const elapsed       = (Date.now() - startTime) / 1000;
            const currentRate   = completed / elapsed;
            bestRate            = Math.max(bestRate, currentRate);

            const remaining     = rows - completed;
            const remainingTime = targetTime - elapsed;
            const timeNeeded    = remaining / bestRate;
            const predictedBest = elapsed + timeNeeded;
            const predictedAvg  = elapsed + remaining / currentRate;

            const canFinish = remainingTime >= timeNeeded;

            statsBanner.className     = canFinish ? 'success' : 'warning';
            statsHeader.textContent   = canFinish
                ? 'SAAD HAKKAMA! 👍'
                : 'EI JÕUA LÕPETADA! ⚠️';
            statsBanner.style.display = 'block';

            statsContent.innerHTML = `
            <div class="stats-row"><span class="stats-label">Tehtud:</span><span class="stats-value">${completed}/${rows}</span></div>
            <div class="stats-row"><span class="stats-label">Kiirus:</span><span class="stats-value">${currentRate.toFixed(2)} sõna/s</span></div>
            <div class="stats-row"><span class="stats-label">Parim kiirus:</span><span class="stats-value">${bestRate.toFixed(2)} sõna/s</span></div>
            <div class="stats-row"><span class="stats-label">Aega jäänud:</span><span class="stats-value">${Math.max(0, remainingTime).toFixed(1)}s</span></div>
            <div class="stats-row"><span class="stats-label">Vaja aega:</span><span class="stats-value">${timeNeeded.toFixed(1)}s</span></div>
            <div class="stats-row"><span class="stats-label">Prognoositav lõpp (parim):</span>
                <span class="stats-value ${predictedBest > targetTime ? 'highlight-red' : ''}">${predictedBest.toFixed(1)}s</span>
            </div>
            <div class="stats-row"><span class="stats-label">Prognoositav lõpp (keskmine):</span>
                <span class="stats-value ${predictedAvg > targetTime ? 'highlight-red' : ''}">${predictedAvg.toFixed(1)}s</span>
            </div>`;
        }

        function updateTimer() {
            const elapsed = (Date.now() - startTime) / 1000;
            timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;

            if (elapsed >= targetTime) {
                clearInterval(timerInterval);
                alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
                location.reload();
            }
        }

        function finish() {
            clearInterval(timerInterval);
            const elapsed = (Date.now() - startTime) / 1000;
            timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;

            fetch('save_result.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    elapsed:     elapsed.toFixed(2),
                    exercise_id: '<?= $exerciseId ?>'
                })
            });
        }
    })();
</script>
