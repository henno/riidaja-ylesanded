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
    .help-bubble {
        position: absolute;
        background: #4A90D9;
        color: white;
        padding: 6px 14px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: bold;
        white-space: nowrap;
        z-index: 100;
        pointer-events: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transform: translateX(-50%);
        transition: opacity 0.3s, top 0.3s, left 0.3s;
    }
    .help-bubble::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid #4A90D9;
    }
    .help-bubble.hidden {
        opacity: 0;
        pointer-events: none;
    }
    .mouse-warning, .typing-warning {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 1000;
        font-weight: bold;
        display: none;
    }
    .mouse-warning {
        top: 20px;
        background: #ff9800;
    }
    .typing-warning {
        top: 70px;
        background: #e91e63;
    }
</style>

<p id="instructions">Kopeeri alglause vastuse lahtrisse ja muuda sõnade järjekord õigeks. Kasuta ainult klaviatuuri! Otseteed: Tab (liigu väljade vahel), Ctrl+A (vali kõik), Ctrl+C (kopeeri), Ctrl+V (kleebi), Ctrl+Shift+nooled (vali sõna), Ctrl+X (lõika). Sul on aega 120 sekundit.</p>
<script>
// macOS: näita ⌘/⌥ sümboleid juhendi tekstis
if (/Mac/i.test(navigator.platform)) {
    const p = document.getElementById('instructions');
    p.innerHTML = p.innerHTML
        .replace('Ctrl+Shift+nooled', '⌥+Shift+nooled')
        .replace(/Ctrl\+/g, '⌘+');
}
</script>

<div class="mouse-warning" id="mouse-warning">Hiir on keelatud! Kasuta klaviatuuri.</div>
<div class="help-bubble hidden" id="help-bubble"></div>
<div class="typing-warning" id="typing-warning">Kasuta teksti ümberkirjutamise asemel teksti lõikamist ja kleepimist</div>

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
const typingWarning = document.getElementById('typing-warning');
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

    answerTextarea.addEventListener('beforeinput', blockTyping);
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

let typingWarningTimeout = null;
function blockTyping(e) {
    // Blokeeri käsitsi tippimine, luba tühik (sõnade eraldamiseks), kleepimine, lõikamine, kustutamine, nooled jne
    if ((e.inputType === 'insertText' || e.inputType === 'insertCompositionText') && e.data !== ' ') {
        e.preventDefault();
        typingWarning.style.display = 'block';
        clearTimeout(typingWarningTimeout);
        typingWarningTimeout = setTimeout(() => {
            typingWarning.style.display = 'none';
        }, 3000);
    }
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
                '008'
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
                exercise_id: '008'
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
                exercise_id: '008'
            })
        }).then(() => {
            alert('Aeg on läbi! Vajuta OK, et uuesti proovida.');
            location.reload();
        });
    }
}

// --- Interaktiivne help bubble juhend (esimesed 3 rida) ---
const helpBubble = document.getElementById('help-bubble');
let guideRow = 0;
let guideStep = 0;
let bubbleTimeout = null;

// macOS tuvastamine — nooleklahvide sõna-haaval liigutamine kasutab Option (Alt) klahvi
const isMac = /Mac/i.test(navigator.platform);

// Platvormi-spetsiifiline sildi kuvamine
function getStepLabel(step) {
    if (!isMac) return step.label;
    const isArrow = step.key.startsWith('Arrow');
    // macOS: nooleklahvidega sõna-haaval → ⌥, muu (kopeeri/kleebi/lõika) → ⌘
    return step.label
        .replace('Ctrl+Shift+', isArrow ? '⌥+Shift+' : '⌘+Shift+')
        .replace('Ctrl+', isArrow ? '⌥+' : '⌘+');
}

// Iga rea täielik töövoog koos sõnade ümbertõstmisega
const guideSequences = [
    // Rida 1: "Väike koer jooksis poole õue" → "Väike koer jooksis õue poole"
    // Viimased 2 sõna vahetada kohad
    [
        { label: 'Ctrl+A',         key: 'a',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Ctrl+C',         key: 'c',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Tab',            key: 'Tab',        ctrl: false, shift: false, target: 'source' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+Shift+←',   key: 'ArrowLeft',  ctrl: true,  shift: true,  target: 'answer' },
        { label: 'Ctrl+X',         key: 'x',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+←',         key: 'ArrowLeft',  ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Tühik',          key: ' ',          ctrl: false, shift: false, target: 'answer' },
        { label: 'Tab',            key: 'Tab',        ctrl: false, shift: false, target: 'answer' }
    ],
    // Rida 2: "Täna on taevas ilus väga" → "Täna on taevas väga ilus"
    // Sama muster (viimased 2 sõna vahetada)
    [
        { label: 'Ctrl+A',         key: 'a',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Ctrl+C',         key: 'c',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Tab',            key: 'Tab',        ctrl: false, shift: false, target: 'source' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+Shift+←',   key: 'ArrowLeft',  ctrl: true,  shift: true,  target: 'answer' },
        { label: 'Ctrl+X',         key: 'x',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+←',         key: 'ArrowLeft',  ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Tühik',          key: ' ',          ctrl: false, shift: false, target: 'answer' },
        { label: 'Tab',            key: 'Tab',        ctrl: false, shift: false, target: 'answer' }
    ],
    // Rida 3: "Ma lähen hommikul kooli iga päev" → "Ma lähen iga päev hommikul kooli"
    // "iga päev" tuleb tõsta "hommikul" ette (2 sõna valida, 2 sõna üle hüpata)
    [
        { label: 'Ctrl+A',         key: 'a',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Ctrl+C',         key: 'c',          ctrl: true,  shift: false, target: 'source' },
        { label: 'Tab',            key: 'Tab',        ctrl: false, shift: false, target: 'source' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+Shift+←',   key: 'ArrowLeft',  ctrl: true,  shift: true,  target: 'answer' },
        { label: 'Ctrl+Shift+←',   key: 'ArrowLeft',  ctrl: true,  shift: true,  target: 'answer' },
        { label: 'Ctrl+X',         key: 'x',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+←',         key: 'ArrowLeft',  ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+←',         key: 'ArrowLeft',  ctrl: true,  shift: false, target: 'answer' },
        { label: 'Ctrl+V',         key: 'v',          ctrl: true,  shift: false, target: 'answer' },
        { label: 'Tühik',          key: ' ',          ctrl: false, shift: false, target: 'answer' }
    ]
];

function getGuideTarget() {
    if (guideRow >= guideSequences.length) return null;
    const row = tableBody.rows[guideRow];
    if (!row) return null;
    const step = guideSequences[guideRow][guideStep];
    return step.target === 'answer'
        ? row.querySelector('.answer-textarea')
        : row.querySelector('.source-textarea');
}

function positionBubble() {
    if (guideRow >= guideSequences.length) {
        helpBubble.classList.add('hidden');
        return;
    }
    const target = getGuideTarget();
    if (!target) return;

    const step = guideSequences[guideRow][guideStep];
    const rect = target.getBoundingClientRect();
    helpBubble.textContent = getStepLabel(step);
    helpBubble.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
    helpBubble.style.top = (rect.top + window.scrollY - 40) + 'px';
    helpBubble.classList.remove('hidden');
}

// Repositsioneeri mull (scrollimisel/resize'il) ilma fade-in viiteta
function repositionBubble() {
    if (guideRow >= guideSequences.length) return;
    if (helpBubble.classList.contains('hidden')) return;
    const target = getGuideTarget();
    if (!target) return;
    const rect = target.getBoundingClientRect();
    helpBubble.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
    helpBubble.style.top = (rect.top + window.scrollY - 40) + 'px';
}

// Näita mulli 500ms viitega (fade-in), et kasutajal oleks aega ise mõelda
function showBubbleDelayed() {
    helpBubble.classList.add('hidden');
    clearTimeout(bubbleTimeout);
    bubbleTimeout = setTimeout(positionBubble, 1000);
}

document.addEventListener('keydown', function(e) {
    if (guideRow >= guideSequences.length) return;
    const steps = guideSequences[guideRow];
    const step = steps[guideStep];

    const keyMatch = e.key === step.key || e.key.toLowerCase() === step.key;
    // macOS: nooleklahvide sõna-haaval liigutamine kasutab Option (altKey)
    const isArrow = step.key.startsWith('Arrow');
    const ctrlOk = step.ctrl
        ? (e.ctrlKey || e.metaKey || (isArrow && e.altKey))
        : !(e.ctrlKey || e.metaKey);
    const shiftOk = step.shift ? e.shiftKey : !e.shiftKey;

    if (keyMatch && ctrlOk && shiftOk) {
        guideStep++;
        if (guideStep >= steps.length) {
            guideStep = 0;
            guideRow++;
        }
        showBubbleDelayed();
    }
});

window.addEventListener('scroll', repositionBubble);
window.addEventListener('resize', repositionBubble);

// Fookus esimesele alglausele ja näita esimest vihjet
document.querySelector('.source-textarea').focus();
showBubbleDelayed();
</script>
