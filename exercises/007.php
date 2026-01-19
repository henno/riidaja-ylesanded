<style>
    #sentence-table {
        width: 100%;
        max-width: 900px;
        margin: 20px auto;
        border-collapse: collapse;
    }
    #sentence-table th, #sentence-table td {
        border: 1px solid #ddd;
        padding: 8px;
        vertical-align: top;
    }
    #sentence-table th:first-child {
        width: 30px;
    }
    #sentence-table th:nth-child(2) {
        width: 35%;
    }
    #sentence-table th:nth-child(3) {
        width: 30%;
    }
    #sentence-table th:nth-child(4) {
        width: 35%;
    }
    .source-cell {
        font-family: monospace;
        font-size: 14px;
    }
    .target-cell {
        font-family: monospace;
        font-size: 14px;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    textarea {
        width: 100%;
        height: 50px;
        padding: 5px;
        resize: vertical;
        box-sizing: border-box;
        margin: 0;
        display: block;
        font-family: monospace;
        font-size: 14px;
    }
    .correct {
        background-color: #ccffcc;
    }
    .incorrect {
        background-color: #ffcccc;
    }
    #timer {
        text-align: left;
        font-size: 18px;
        margin: 20px 0;
        font-weight: bold;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }
    .mouse-warning {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #ff9800;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 1000;
        font-weight: bold;
        display: none;
    }
</style>

<p>Kopeeri alglause vastuse lahtrisse ja muuda sõnade järjekord õigeks. Kasuta ainult klaviatuuri! Otseteed: Tab (liigu väljade vahel), Ctrl+A (vali kõik), Ctrl+C (kopeeri), Ctrl+V (kleebi), Ctrl+Shift+nooled (vali sõna), Ctrl+X (lõika). Sul on aega 120 sekundit.</p>

<div class="mouse-warning" id="mouse-warning">Hiir on keelatud! Kasuta klaviatuuri.</div>

<form id="task-form">
    <table id="sentence-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Alglause (kopeeri)</th>
                <th>Õige lause</th>
                <th>Sinu vastus</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div id="timer">Kulunud aeg: 0.00 s</div>
</form>

<script>
const tableBody = document.querySelector('#sentence-table tbody');
const timerDisplay = document.getElementById('timer');
const mouseWarning = document.getElementById('mouse-warning');
let startTime = null;
let timerInterval = null;
let sessionTracker = null;
const textareas = [];

// Laused: [segatud, õige] - ainult 4 lauset, lihtsad vahetused
const sentences = [
    {
        scrambled: "Väike koer jooksis poole õue",
        correct: "Väike koer jooksis õue poole"
    },
    {
        scrambled: "Täna on taevas ilus väga",
        correct: "Täna on taevas väga ilus"
    },
    {
        scrambled: "Ma lähen hommikul kooli iga päev",
        correct: "Ma lähen iga päev hommikul kooli"
    },
    {
        scrambled: "Ema tegi hommikusöögi maitsva väga",
        correct: "Ema tegi väga maitsva hommikusöögi"
    },
    {
        scrambled: "Kass mustvalge magas diivanil pehmel",
        correct: "Mustvalge kass magas pehmel diivanil"
    },
    {
        scrambled: "Vanaisa rääkis lastele õhtul põnevaid vanu lugusid pikki",
        correct: "Vanaisa rääkis õhtul lastele pikki põnevaid vanu lugusid"
    }
];

// Loome tabeli read
sentences.forEach((sentence, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${i + 1}</td>
        <td class="source-cell">
            <textarea class="source-textarea" readonly>${sentence.scrambled}</textarea>
        </td>
        <td class="target-cell">${sentence.correct}</td>
        <td><textarea class="answer-textarea" data-correct="${sentence.correct}" placeholder="Kleebi siia ja paranda..."></textarea></td>
    `;

    const sourceTextarea = tr.querySelector('.source-textarea');
    const answerTextarea = tr.querySelector('.answer-textarea');

    // Keela hiir mõlemas textarea-s
    [sourceTextarea, answerTextarea].forEach(ta => {
        ta.addEventListener('mousedown', blockMouse);
        ta.addEventListener('click', blockMouse);
        ta.addEventListener('contextmenu', blockMouse);
    });

    answerTextarea.addEventListener('input', handleInput);

    textareas.push(answerTextarea);
    tableBody.appendChild(tr);
});

let mouseWarningTimeout = null;
function blockMouse(e) {
    e.preventDefault();
    mouseWarning.style.display = 'block';
    clearTimeout(mouseWarningTimeout);
    mouseWarningTimeout = setTimeout(() => {
        mouseWarning.style.display = 'none';
    }, 2000);
}

// Taimer käivitub ainult Tab klahvi vajutamisel
document.addEventListener('keydown', function(e) {
    if (e.key === 'Tab' && startTime === null) {
        startTime = Date.now();
        timerInterval = setInterval(updateTimer, 50);
        // Start session tracking
        if (window.SessionTracker && window.RIIDAJA_USER) {
            sessionTracker = new SessionTracker(
                window.RIIDAJA_USER.email,
                window.RIIDAJA_USER.name,
                '007'
            );
            sessionTracker.start();
        }
    }
});

function normalizeText(text) {
    return text.trim().toLowerCase().replace(/\s+/g, ' ');
}

function handleInput() {
    let allCorrect = true;
    let correctCount = 0;

    for (const textarea of textareas) {
        const target = normalizeText(textarea.dataset.correct);
        const current = normalizeText(textarea.value);

        // Kui lahter on tühi, eemalda värvid
        if (textarea.value.trim() === '') {
            textarea.classList.remove('correct', 'incorrect');
            allCorrect = false;
        } else if (current === target) {
            textarea.classList.remove('incorrect');
            textarea.classList.add('correct');
            correctCount++;
        } else {
            textarea.classList.remove('correct');
            textarea.classList.add('incorrect');
            allCorrect = false;
        }
    }

    if (allCorrect && correctCount === sentences.length) {
        clearInterval(timerInterval);
        // Mark session as complete (success)
        if (sessionTracker) sessionTracker.complete();
        const elapsed = (Date.now() - startTime) / 1000;
        timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s - VALMIS!`;
        timerDisplay.style.color = '#4CAF50';
        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                elapsed: elapsed.toFixed(2),
                exercise_id: '007'
            })
        });
    }
}

function updateTimer() {
    const elapsed = (Date.now() - startTime) / 1000;
    timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;

    // Ajapiir 120 sekundit
    if (elapsed >= 120) {
        clearInterval(timerInterval);
        // Mark session as complete (failed)
        if (sessionTracker) sessionTracker.complete();
        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                elapsed: -elapsed.toFixed(2),
                exercise_id: '007'
            })
        }).then(() => {
            alert('Aeg on läbi! Vajuta OK, et uuesti proovida.');
            location.reload();
        });
    }
}

// Fookus esimesele alglausele
document.querySelector('.source-textarea').focus();
</script>
