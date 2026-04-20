
<style>
    #typing-area {
        width: 100%;
        max-width: 1250px;
        margin: 20px auto;
    }
    .original-text {
        white-space: pre-wrap;
        font-family: monospace;
        font-size: 18px;
        line-height: 2.2;
        text-align: left;
        user-select: none;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px 8px 0 0;
        background: #fafafa;
        min-height: 150px;
    }
    .char {
        position: relative;
        transition: background-color 0.1s;
    }
    .char.correct {
        color: #2e7d32;
        background: #c8e6c9;
    }
    .char.incorrect {
        color: #c62828;
        background: #ffcdd2;
    }
    .char.current {
        background: #bbdefb;
        outline: 2px solid #2196F3;
        border-radius: 2px;
    }
    #typing-input {
        width: 100%;
        font-family: monospace;
        font-size: 18px;
        line-height: 2.2;
        padding: 15px;
        border: 2px solid #ddd;
        border-top: none;
        border-radius: 0 0 8px 8px;
        box-sizing: border-box;
        resize: none;
        min-height: 100px;
        outline: none;
    }
    #typing-input:focus {
        border-color: #4CAF50;
    }
    #typing-input.blocked {
        border-color: #f44336;
        background: #fff5f5;
    }
    #progress-info {
        margin: 10px auto;
        max-width: 1250px;
        font-family: monospace;
    }
    #timer {
        font-weight: bold;
        color: #333;
    }
    .stat-row {
        display: flex;
        gap: 30px;
        margin-bottom: 10px;
    }
    .stat-item {
        display: flex;
        gap: 5px;
    }
    .stat-label {
        color: #666;
    }
    .stat-value {
        font-weight: bold;
    }
    .stat-value.success {
        color: #4CAF50;
    }
    .stat-value.warning {
        color: #ff9800;
    }
    .stat-value.danger {
        color: #f44336;
    }
    .requirements {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .options-row {
        margin: 10px auto;
        max-width: 1250px;
        font-family: monospace;
        font-size: 14px;
    }
    .options-row label {
        cursor: pointer;
        user-select: none;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
    }
    .input-shake {
        animation: shake 0.2s;
    }
    /* Result Modal */
    .result-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .result-modal.show {
        display: flex;
    }
    .result-modal-content {
        background: white;
        padding: 40px;
        border-radius: 10px;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    .result-modal-content h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    .result-modal-content h2.passed {
        color: #4CAF50;
    }
    .result-modal-content h2.failed {
        color: #f44336;
    }
    .result-level {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
    }
    .result-stats {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
    .result-stat-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }
    .result-stat-row:last-child {
        border-bottom: none;
    }
    .result-stat-label {
        color: #666;
    }
    .result-stat-value {
        font-weight: bold;
    }
    .result-stat-value.passed {
        color: #4CAF50;
    }
    .result-stat-value.failed {
        color: #f44336;
    }
    .result-message {
        margin: 20px 0;
        padding: 15px;
        border-radius: 8px;
        font-size: 14px;
    }
    .result-message.success {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .result-message.failure {
        background: #ffebee;
        color: #c62828;
    }
    .result-btn {
        padding: 12px 40px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
    .result-btn.primary {
        background: #4CAF50;
        color: white;
    }
    .result-btn.primary:hover {
        background: #45a049;
    }
    .result-btn.secondary {
        background: #2196F3;
        color: white;
    }
    .result-btn.secondary:hover {
        background: #1976D2;
    }
</style>

<p>Pimekirjutamise harjutus kirjavahemärkidega. Kirjuta allolevas tekstikastis ülemises kastis kuvatav tekst — kaasa arvatud märgid ' ; : " ! ?. Vale klahvi vajutamisel ei saa edasi – kustuta viga. Sul on aega 30 sekundit.</p>
<p class="requirements" id="requirements">Nõuded: WPM ≥ 17, Täpsus ≥ 97%</p>

<div class="options-row">
    <label><input type="checkbox" id="fixed-text-toggle"> Sama tekst igal korral</label>
</div>

<div id="typing-area">
    <div class="original-text" id="original-text"></div>
    <textarea id="typing-input" placeholder="Hakka siia kirjutama..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
</div>

<div id="progress-info">
    <div class="stat-row">
        <div class="stat-item">
            <span class="stat-label">Järelejäänud aeg:</span>
            <span class="stat-value" id="timer">30.0 s</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">WPM:</span>
            <span class="stat-value" id="wpm-display">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Täpsus:</span>
            <span class="stat-value" id="accuracy-display">100%</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Vead:</span>
            <span class="stat-value" id="errors-display">0</span>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="result-modal" id="result-modal">
    <div class="result-modal-content">
        <h2 id="result-title">Tulemus</h2>
        <div class="result-level" id="result-level">Ülesanne 015</div>
        <div class="result-stats">
            <div class="result-stat-row">
                <span class="result-stat-label">Kirjutamiskiirus (WPM):</span>
                <span class="result-stat-value" id="result-wpm">0</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Nõutav WPM:</span>
                <span class="result-stat-value" id="result-req-wpm">17</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Täpsus:</span>
                <span class="result-stat-value" id="result-accuracy">100%</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Nõutav täpsus:</span>
                <span class="result-stat-value" id="result-req-accuracy">97%</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Vead:</span>
                <span class="result-stat-value" id="result-errors">0</span>
            </div>
        </div>
        <div class="result-message" id="result-message"></div>
        <button class="result-btn primary" id="result-btn" onclick="closeModal()">Jätka</button>
    </div>
</div>

<script>
    const REQUIRED_WPM = 17;
    const REQUIRED_ACCURACY = 97;
    const TIME_LIMIT = 30;
    const STORAGE_KEY = 'exercise015_fixed_text';
    const STORAGE_CHECKBOX_KEY = 'exercise015_fixed_enabled';

    const estonianSentences = [
        'Kui ilus päev täna!',
        'Kas sa tulid juba koju?',
        'Õpetaja küsis: "Kas kõik on valmis?"',
        'Päev oli soe; õhtu külm.',
        'Ta kirjutas tahvlile sõna \'tere\'.',
        'Tule ruttu siia!',
        'Kuhu poisid läksid mängima?',
        'Ema ütles: "Tule õhtusöögile!"',
        'Poiss jooksis; tüdruk kõndis rahulikult.',
        'Proovi kirjutada sõna \'tänan\'.',
        'Oh, kui kiire meil on!',
        'Kes seal ukse taga hõikab?',
        'Ta sõnas: "Olgu nii, nagu otsustasid."',
        'Mäel puhus tuul; orus sadas vihma.',
        'Õpetaja seletas sõna \'puusepp\' tähendust.',
        'Hästi tehtud, väike sõber!',
        'Mis kell praegu on?',
        'Silt teatas lühidalt: "Vaikus majas."',
        'Kell lõi kümme; linnud vakatasid.',
        'Lapsed õppisid sõna \'kõrvits\'.',
        'Tänan sind väga!',
        'Miks see nii juhtus?',
        'Treener hõikas selja tagant: "Veel üks kord!"',
        'Lumi sulas ära; kevad saabus jälle.',
        'Sõna \'köök\' algab tähega k.',
        'Vaata, milline lõbus mäng!',
        'Kuidas sul täna läheb?',
        'Isa vastas rahulikult: "Tule parem homme."',
        'Töö tehti ära; puhkus algas.',
        'Õpilane küsis sõna \'kirjanik\' tähendust.',
        'Aitab juba tööga!',
        'Mida sa homseks soovid?',
        'Arst rääkis pehmelt: "Jää kohe magama."',
        'Päike loojus; tähed süttisid taevasse.',
        'Mõtle hoolega sõna \'õnnelik\' peale.',
        'Mis tore üllatus!',
        'Kus ma küll eksisin selle tee peal?',
        'Sõber sosistas: "Kuula hoolega!"',
        'Ta mõtles kaua; siis vastas lühidalt.',
        'Proovi öelda sõna \'pühapäev\' selgelt välja.',
        'Hõisa, see on õige vastus!',
        'Kas mäletad seda päeva?',
        'Juht käskis: "Alusta nüüd kohe!"',
        'Raamat oli pikk; lugemine läks kiirelt.',
        'Õpetaja kirjutas tahvlile sõna \'ajaleht\'.',
        'Head reisi sulle!',
        'Kellele sa selle kirja saatsid?',
        'Mees hüüdis õuest: "Vajan abi!"',
        'Pood oli kinni; küla oli vaikne.',
        'Kirjutame sõna \'kõrgus\' hoolega.',
        'Pane uks korralikult kinni!',
        'Kas söögiga on kõik hästi?',
        'Naine vastas viisakalt: "Tänan küsimast."',
        'Tuli süttis; toas läks soojaks.',
        'Proovime kirjutada sõna \'õpilane\' vigadeta.',
        'Küll on täna palav!',
        'Miks nüüd nii kurb meel?',
        'Poiss hüüdis õuest: "Tule kohe siia!"',
        'Orus voolas jõgi; kaldal kasvasid pajud.',
        'Mõtle sõna \'järve\' tähenduse peale.',
        'Oi, kui kerge see oli!',
        'Mis toimub seal majas?',
        'Valvur teatas rangelt: "Hoone sulgub!"',
        'Lauluklass laulis; publik kuulas vaikselt.',
        'Sõna \'ülekuulamine\' on pikk ja raske.',
        'Hei, kas oled kohal?',
        'Müüja naeratas: "Proovige seda kindlasti!"',
        'Ta tõusis püsti; ruum jäi vaikseks.',
        'Õpilased küsisid sõna \'mõõtmed\' tähendust.',
        'Head pühi sulle ja perele!',
    ];

    const fixedTextToggle = document.getElementById('fixed-text-toggle');

    if (localStorage.getItem(STORAGE_CHECKBOX_KEY) === 'true') {
        fixedTextToggle.checked = true;
    }

    fixedTextToggle.addEventListener('change', function() {
        localStorage.setItem(STORAGE_CHECKBOX_KEY, this.checked);
        if (!this.checked) {
            localStorage.removeItem(STORAGE_KEY);
        }
    });

    function getRandomSentences(count) {
        const shuffled = [...estonianSentences].sort(() => Math.random() - 0.5);
        return shuffled.slice(0, Math.min(count, shuffled.length));
    }

    function getOriginalText() {
        if (fixedTextToggle.checked) {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) return stored;
        }
        const text = getRandomSentences(15).join(' ');
        if (fixedTextToggle.checked) {
            localStorage.setItem(STORAGE_KEY, text);
        }
        return text;
    }

    const originalText = getOriginalText();
    const originalTextDiv = document.getElementById('original-text');
    const typingInput = document.getElementById('typing-input');
    const timerDisplay = document.getElementById('timer');
    const wpmDisplay = document.getElementById('wpm-display');
    const accuracyDisplay = document.getElementById('accuracy-display');
    const errorsDisplay = document.getElementById('errors-display');

    let charSpans = [];
    let correctChars = 0;
    let totalChars = 0;
    let errors = 0;
    let startTime = null;
    let isTestActive = false;
    let timerInterval = null;
    let sessionTracker = null;
    let isBlocked = false;

    // Build character spans for original text
    function initializeDisplay() {
        originalTextDiv.innerHTML = '';
        charSpans = [];
        for (let i = 0; i < originalText.length; i++) {
            const span = document.createElement('span');
            span.className = 'char';
            span.textContent = originalText[i];
            originalTextDiv.appendChild(span);
            charSpans.push(span);
        }
        // Highlight first character as current
        if (charSpans.length > 0) {
            charSpans[0].classList.add('current');
        }
    }

    function updateHighlighting(matchCount, errorPos) {
        for (let i = 0; i < charSpans.length; i++) {
            charSpans[i].className = 'char';
            if (i < matchCount) {
                charSpans[i].classList.add('correct');
            } else if (i === errorPos) {
                charSpans[i].classList.add('incorrect');
            } else if (i === matchCount && errorPos === -1) {
                charSpans[i].classList.add('current');
            }
        }
    }

    function startTest() {
        if (!isTestActive) {
            isTestActive = true;
            startTime = Date.now();
            timerInterval = setInterval(updateStats, 100);
            if (window.SessionTracker && window.RIIDAJA_USER) {
                sessionTracker = new SessionTracker(
                    window.RIIDAJA_USER.email,
                    window.RIIDAJA_USER.name,
                    '015'
                );
                sessionTracker.start();
            }
        }
    }

    function updateStats() {
        if (!startTime) return;

        const elapsedSeconds = (Date.now() - startTime) / 1000;
        const remainingSeconds = Math.max(0, TIME_LIMIT - elapsedSeconds);
        const minutes = elapsedSeconds / 60;
        const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
        const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;

        timerDisplay.textContent = remainingSeconds.toFixed(1) + ' s';
        if (remainingSeconds <= 5) {
            timerDisplay.className = 'stat-value danger';
        } else if (remainingSeconds <= 10) {
            timerDisplay.className = 'stat-value warning';
        } else {
            timerDisplay.className = 'stat-value';
        }

        wpmDisplay.textContent = wpm;
        if (wpm >= REQUIRED_WPM) {
            wpmDisplay.className = 'stat-value success';
        } else if (wpm >= REQUIRED_WPM * 0.7) {
            wpmDisplay.className = 'stat-value warning';
        } else {
            wpmDisplay.className = 'stat-value danger';
        }

        accuracyDisplay.textContent = accuracy + '%';
        if (accuracy >= REQUIRED_ACCURACY) {
            accuracyDisplay.className = 'stat-value success';
        } else if (accuracy >= REQUIRED_ACCURACY - 5) {
            accuracyDisplay.className = 'stat-value warning';
        } else {
            accuracyDisplay.className = 'stat-value danger';
        }

        errorsDisplay.textContent = errors;

        if (remainingSeconds <= 0) {
            endTest();
        }
    }

    function endTest() {
        isTestActive = false;
        clearInterval(timerInterval);
        typingInput.disabled = true;
        if (sessionTracker) sessionTracker.complete();

        const elapsedSeconds = Math.min((Date.now() - startTime) / 1000, TIME_LIMIT);
        const minutes = elapsedSeconds / 60;
        const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
        const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;

        const passed = wpm >= REQUIRED_WPM && accuracy >= REQUIRED_ACCURACY;

        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                elapsed: passed ? wpm : -wpm,
                accuracy: accuracy,
                duration: Math.round(elapsedSeconds),
                exercise_id: '015'
            })
        });

        const resultTitle = document.getElementById('result-title');
        const resultWpm = document.getElementById('result-wpm');
        const resultReqWpm = document.getElementById('result-req-wpm');
        const resultAccuracy = document.getElementById('result-accuracy');
        const resultReqAccuracy = document.getElementById('result-req-accuracy');
        const resultErrors = document.getElementById('result-errors');
        const resultMessage = document.getElementById('result-message');

        resultReqWpm.textContent = REQUIRED_WPM;
        resultReqAccuracy.textContent = REQUIRED_ACCURACY + '%';
        resultErrors.textContent = errors;

        resultWpm.textContent = wpm;
        resultWpm.className = 'result-stat-value ' + (wpm >= REQUIRED_WPM ? 'passed' : 'failed');

        resultAccuracy.textContent = accuracy + '%';
        resultAccuracy.className = 'result-stat-value ' + (accuracy >= REQUIRED_ACCURACY ? 'passed' : 'failed');

        if (passed) {
            resultTitle.textContent = 'LÄBITUD!';
            resultTitle.className = 'passed';
            resultMessage.textContent = 'Suurepärane! Sa läbisid ülesande.';
            resultMessage.className = 'result-message success';
        } else {
            resultTitle.textContent = 'LÄBIMATA';
            resultTitle.className = 'failed';
            let failReasons = [];
            if (wpm < REQUIRED_WPM) failReasons.push(`WPM on liiga madal (${wpm} < ${REQUIRED_WPM})`);
            if (accuracy < REQUIRED_ACCURACY) failReasons.push(`Täpsus on liiga madal (${accuracy}% < ${REQUIRED_ACCURACY}%)`);
            resultMessage.textContent = failReasons.join('. ') + '. Proovi uuesti!';
            resultMessage.className = 'result-message failure';
        }

        document.getElementById('result-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('result-modal').classList.remove('show');
        location.reload();
    }

    // Block non-allowed keys when there is an error
    typingInput.addEventListener('keydown', (e) => {
        if (!isBlocked) return;

        const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'Shift', 'Control', 'Alt', 'Meta', 'Tab'];
        if (!allowed.includes(e.key)) {
            e.preventDefault();
            // Shake the input
            typingInput.classList.remove('input-shake');
            void typingInput.offsetWidth;
            typingInput.classList.add('input-shake');
        }
    });

    // Prevent paste
    typingInput.addEventListener('paste', (e) => e.preventDefault());

    typingInput.addEventListener('input', (e) => {
        if (!isTestActive && typingInput.value.length > 0) {
            startTest();
        }

        const typed = typingInput.value;

        // Find sequential match count and first error position
        let matchCount = 0;
        let errorPos = -1;
        for (let i = 0; i < typed.length && i < originalText.length; i++) {
            if (typed[i] === originalText[i]) {
                matchCount++;
            } else {
                errorPos = i;
                break;
            }
        }
        // Also error if typed is longer than original
        if (typed.length > originalText.length && errorPos === -1) {
            errorPos = originalText.length;
        }

        const hasError = errorPos !== -1;

        // Track keystrokes
        if (e.inputType === 'insertText' || e.inputType === 'insertCompositionText') {
            totalChars++;
            if (hasError) {
                errors++;
            } else {
                correctChars++;
            }
        } else if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
            // Backspace/delete
            if (isBlocked) {
                // Was deleting the error character - don't change counts
            } else {
                // Was deleting a correct character
                if (correctChars > 0) {
                    correctChars--;
                    totalChars--;
                }
            }
        }

        isBlocked = hasError;
        typingInput.classList.toggle('blocked', hasError);
        updateHighlighting(matchCount, errorPos);
        updateStats();

        // Check completion
        if (matchCount === originalText.length && !hasError) {
            endTest();
        }
    });

    // Initialize
    initializeDisplay();
    typingInput.focus();
</script>
