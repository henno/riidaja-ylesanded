<style>
  #word-table {
    width: 100%;
    max-width: 400px;
    margin: 20px auto;
  }
  .input-cell {
    display: flex;
    align-items: center;
    gap: 4px;
  }
  input[type="text"] {
    width: 100px;
    padding: 2px;
  }
  .mismatch {
    background-color: #ffcccc;
  }
</style>

<p>Kopeeri igasse tekstikasti täpselt see sama sõna, mis on vasakul. Kui kõik sõnad on õigesti sisestatud, mõõdetakse aeg. Sul on aega 3 minutit (180 sekundit).</p>
<form id="task-form">
  <table id="word-table">
    <thead>
      <tr><th>#</th><th>Sõna</th><th>Tekstikast</th></tr>
    </thead>
    <tbody></tbody>
  </table>
  <div id="timer">Kulunud aeg: 0.00 s</div>
</form>

<script>
const tableBody    = document.querySelector('#word-table tbody');
const timerDisplay = document.getElementById('timer');
let startTime      = null;
let timerInterval  = null;
const inputs       = [];
const rows         = 40;

const generateWord = () => {
  const letters = 'abcdefghijklmnopqrstuvwxyz';
  let word = '';
  for (let i = 0; i < 8; i++) word += letters.charAt(Math.floor(Math.random() * letters.length));
  return word;
};

for (let i = 0; i < rows; i++) {
  const word = generateWord();
  const tr   = document.createElement('tr');
  tr.innerHTML = `<td>${i + 1}</td><td>${word}</td><td><div class="input-cell"><input type="text" data-correct="${word}" /></div></td>`;
  const input = tr.querySelector('input');
  input.addEventListener('input', handleInput);
  inputs.push(input);
  tableBody.appendChild(tr);
}

function handleInput() {
  if (startTime === null) {
    startTime = Date.now();
    timerInterval = setInterval(updateTimer, 50);
  }
  let allCorrect = true;
  for (const input of inputs) {
    const target = input.dataset.correct;
    if (input.value === target) {
      input.classList.remove('mismatch');
    } else {
      input.classList.add('mismatch');
      allCorrect = false;
    }
  }
  if (allCorrect) {
    clearInterval(timerInterval);
    const elapsed = (Date.now() - startTime) / 1000;
    timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
    fetch('save_result.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        elapsed: elapsed.toFixed(2),
        exercise_id: '<?php echo htmlspecialchars($_GET["task"] ?? "01"); ?>'
      })
    });
  }
}

function updateTimer() {
  const elapsed = (Date.now() - startTime) / 1000;
  timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
  if (elapsed >= 180) {
    clearInterval(timerInterval);
    alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
    location.reload();
  }
}
</script>

<style>
.input-cell { display: flex; align-items: center; gap: 4px; }
input[type="text"] { width: 100px; padding: 2px; }
.mismatch { background-color: #ffcccc; }
</style>