
<style>
    #typing-table {
        width: 100%;
        max-width: 1250px;
        margin: 20px auto;
        border-collapse: collapse;
    }
    #typing-table th, #typing-table td {
        border: 1px solid #ddd;
        padding: 8px;
        vertical-align: top;
    }
    #typing-table th {
        background-color: #f2f2f2;
    }
    #typing-table td {
        position: relative;
        height: 400px;
    }
    .text-display {
        white-space: pre-wrap;
        font-family: monospace;
        font-size: 18px;
        line-height: 2.5;
        text-align: left;
        height: 100%;
        overflow: visible;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        padding: 40px 8px 8px 8px;
        box-sizing: border-box;
    }
    .typing-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .word {
        display: inline;
    }
    .letter {
        position: relative;
        overflow: visible !important;
    }
    .letter.correct {
        color: #4CAF50;
    }
    .letter.incorrect {
        color: #f44336;
        background: #ffebee;
    }
    .letter.blocked {
        animation: shake 0.2s;
        background: #ffcdd2;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
    }
    .floating-error-bubble {
        position: absolute;
        background-color: #ffb6c1;
        color: #000000;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 18px;
        font-weight: bold;
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        line-height: 1;
        min-width: 20px;
        min-height: 20px;
        text-align: center;
        display: none;
        pointer-events: none;
    }
    .floating-error-bubble.visible {
        display: block;
    }
    .floating-error-bubble::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #ffb6c1 transparent transparent transparent;
    }
    .letter.current {
        background: #2196F3;
        color: white;
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

<p>Pimekirjutamise harjutus lausetega. Kirjuta ekraanil kuvatavad laused võimalikult kiiresti ja täpselt. Vale klahvi vajutamisel ei saa edasi – vajuta õiget tähte. Sul on aega 30 sekundit.</p>
<p class="requirements" id="requirements">Nõuded: WPM ≥ 17, Täpsus ≥ 97%</p>

<div class="options-row">
    <label><input type="checkbox" id="fixed-text-toggle"> Sama tekst igal korral</label>
</div>

<form id="task-form">
    <table id="typing-table">
        <thead>
        <tr><th>Kirjuta laused (klõpsa siia alustamiseks)</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <div id="text-display" class="text-display" tabindex="0"></div>
                <div id="error-bubble" class="floating-error-bubble"></div>
            </td>
        </tr>
        </tbody>
    </table>
    <input type="text" class="typing-input" id="typing-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
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
</form>

<!-- Result Modal -->
<div class="result-modal" id="result-modal">
    <div class="result-modal-content">
        <h2 id="result-title">Tulemus</h2>
        <div class="result-level" id="result-level">Ülesanne 011</div>
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
    // Requirements: 17 WPM, 97% accuracy
    const REQUIRED_WPM = 17;
    const REQUIRED_ACCURACY = 97;
    const TIME_LIMIT = 30;
    const STORAGE_KEY = 'exercise011_fixed_text';
    const STORAGE_CHECKBOX_KEY = 'exercise011_fixed_enabled';

    // Estonian short sentences with lots of special characters (ä, ö, ü, õ)
    const estonianSentences = [
        'Täna on ilus päev.',
        'Õpilased läksid kooli.',
        'Väike öökull istus puul.',
        'Külla tuli vanaema.',
        'Mägi on väga kõrge.',
        'Sügisel värvuvad lehed.',
        'Jõgi voolab läbi küla.',
        'Päike tõuseb hommikul üles.',
        'Õhtu jõudis kätte.',
        'Küünal põleb laual.',
        'Lühike öö möödus kiiresti.',
        'Väike tüdruk jooksis õue.',
        'Köögis lõhnab hästi.',
        'Rääkisin õpetajaga täna.',
        'Müts kukkus pähe.',
        'Õunad küpsesid aias.',
        'Tänavu sügis tuli vara.',
        'Käsitöö on põnev.',
        'Rüütel ratsustas mööda teed.',
        'Hüppas üle oja.',
        'Sööma mindi kööki.',
        'Tühi tänav oli hämar.',
        'Küll see päev veel tuleb.',
        'Õues sajab lühikest aega.',
        'Öö oli väga külm.',
        'Nüüd on õige aeg.',
        'Lõuna ajal söödi suppi.',
        'Mööda tänavat kõndis mees.',
        'Välja läks üks õpilane.',
        'Kõik jõudsid õigeks ajaks.',
        'Päev oli lühike ja külm.',
        'Tööd tehti hoolega.',
        'Süüa tehti köögis.',
        'Räägi mulle üks lugu.',
        'Kõrge mäe otsas seisis loss.',
        'Hääl kostis kaugelt.',
        'Jäätis sulas käes ära.',
        'Põõsas kasvas roosi taga.',
        'Küsimus jäi õhku.',
        'Tüli sai alguse väikesest asjast.',
        'Löök tabas täpselt.',
        'Üle jõe viis vana sild.',
        'Mööbel oli väga väärtuslik.',
        'Hästi öeldud sõna aitab.',
        'Võõras tuli külla.',
        'Öö hakkas kätte jõudma.',
        'Süda lõi kiiremini.',
        'Käärid olid laual.',
        'Päästja tõttas appi.',
        'Nõu oli väga hea.',
        'Rõõm täitis südame.',
        'Töö lõppes õhtul.',
        'Väga külm õhk puhus.',
        'Lühike käik pööras paremale.',
        'Õppimine nõuab süvenemist.',
        'Küünlad süüdati õhtul.',
        'Nägu läks rõõmsaks.',
        'Pühapäev oli väga ilus.',
        'Vööt oli roheline ja valge.',
        'Äärmiselt põnev päev.',
        'Nööp kukkus mantlilt maha.',
        'Üürile anti väike korter.',
        'Mõõk rippus seinal.',
        'Tõde tuli välja.',
        'Hõõgvel põles hämaralt.',
        'Nüüd läks asi põnevaks.',
        'Käär lõikas paberit.',
        'Müüja pakkus head hinda.',
        'Rõõmsad lapsed jooksid õues.',
        'Lõõtspill mängis hästi.',
    ];

    const fixedTextToggle = document.getElementById('fixed-text-toggle');

    // Restore checkbox state from localStorage
    if (localStorage.getItem(STORAGE_CHECKBOX_KEY) === 'true') {
        fixedTextToggle.checked = true;
    }

    // Save checkbox state on change
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

    function getTextWords() {
        // If fixed text is enabled and we have stored text, use it
        if (fixedTextToggle.checked) {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                return JSON.parse(stored);
            }
        }

        // Generate new text from random sentences
        const sentences = getRandomSentences(20);
        const fullText = sentences.join(' ');
        const words = fullText.split(' ');

        // Store if checkbox is checked
        if (fixedTextToggle.checked) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(words));
        }

        return words;
    }

    let words = getTextWords();
    let currentWordIndex = 0;
    let currentLetterIndex = 0;
    let errors = 0;
    let correctChars = 0;
    let totalChars = 0;
    let startTime = null;
    let isTestActive = false;
    let timerInterval = null;
    let sessionTracker = null;

    const textDisplay = document.getElementById('text-display');
    const typingInput = document.getElementById('typing-input');
    const timerDisplay = document.getElementById('timer');
    const wpmDisplay = document.getElementById('wpm-display');
    const accuracyDisplay = document.getElementById('accuracy-display');
    const errorsDisplay = document.getElementById('errors-display');
    const requirementsDisplay = document.getElementById('requirements');
    const errorBubble = document.getElementById('error-bubble');
    let accumulatedErrors = '';
    let errorBubbleWordIndex = -1;

    function initializeTest() {
        textDisplay.innerHTML = '';
        words.forEach((word, wordIdx) => {
            const wordSpan = document.createElement('span');
            wordSpan.className = 'word';
            wordSpan.dataset.wordIndex = wordIdx;

            word.split('').forEach((letter, letterIdx) => {
                const letterSpan = document.createElement('span');
                letterSpan.className = 'letter';
                letterSpan.textContent = letter;
                letterSpan.dataset.letterIndex = letterIdx;
                wordSpan.appendChild(letterSpan);
            });

            // Add space after each word except the last
            if (wordIdx < words.length - 1) {
                const spaceSpan = document.createElement('span');
                spaceSpan.className = 'letter space';
                spaceSpan.textContent = ' ';
                spaceSpan.dataset.letterIndex = word.length;
                wordSpan.appendChild(spaceSpan);
            }

            textDisplay.appendChild(wordSpan);
        });

        updateCurrentLetter();
    }

    function updateCurrentLetter() {
        document.querySelectorAll('.letter.current').forEach(el => el.classList.remove('current'));

        const currentWord = textDisplay.querySelector(`[data-word-index="${currentWordIndex}"]`);
        if (currentWord) {
            const currentLetter = currentWord.querySelector(`[data-letter-index="${currentLetterIndex}"]`);
            if (currentLetter) {
                currentLetter.classList.add('current');
            }
        }
    }

    // Show floating error bubble above current letter
    function showWrongKeyBubble(letterElement, wrongChar, wordIndex) {
        if (wordIndex !== errorBubbleWordIndex) {
            accumulatedErrors = '';
            errorBubbleWordIndex = wordIndex;
        }
        const displayChar = wrongChar === ' ' ? '␣' : wrongChar;
        accumulatedErrors += displayChar;
        updateErrorBubble(letterElement);
    }

    function updateErrorBubble(letterElement) {
        if (accumulatedErrors.length > 0 && letterElement) {
            const rect = letterElement.getBoundingClientRect();
            const containerRect = textDisplay.getBoundingClientRect();
            errorBubble.textContent = accumulatedErrors;
            errorBubble.style.left = (rect.left - containerRect.left + rect.width / 2) + 'px';
            errorBubble.style.top = (rect.top - containerRect.top - 35) + 'px';
            errorBubble.style.transform = 'translateX(-50%)';
            errorBubble.classList.add('visible');
        } else {
            errorBubble.classList.remove('visible');
        }
    }

    function clearErrorBubble() {
        accumulatedErrors = '';
        errorBubbleWordIndex = -1;
        errorBubble.classList.remove('visible');
    }

    function startTest() {
        if (!isTestActive) {
            isTestActive = true;
            startTime = Date.now();
            timerInterval = setInterval(updateStats, 100);
            // Start session tracking
            if (window.SessionTracker && window.RIIDAJA_USER) {
                sessionTracker = new SessionTracker(
                    window.RIIDAJA_USER.email,
                    window.RIIDAJA_USER.name,
                    '011'
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

        // Update timer
        timerDisplay.textContent = remainingSeconds.toFixed(1) + ' s';
        if (remainingSeconds <= 5) {
            timerDisplay.className = 'stat-value danger';
        } else if (remainingSeconds <= 10) {
            timerDisplay.className = 'stat-value warning';
        } else {
            timerDisplay.className = 'stat-value';
        }

        // Update WPM with color coding
        wpmDisplay.textContent = wpm;
        if (wpm >= REQUIRED_WPM) {
            wpmDisplay.className = 'stat-value success';
        } else if (wpm >= REQUIRED_WPM * 0.7) {
            wpmDisplay.className = 'stat-value warning';
        } else {
            wpmDisplay.className = 'stat-value danger';
        }

        // Update accuracy with color coding
        accuracyDisplay.textContent = accuracy + '%';
        if (accuracy >= REQUIRED_ACCURACY) {
            accuracyDisplay.className = 'stat-value success';
        } else if (accuracy >= REQUIRED_ACCURACY - 5) {
            accuracyDisplay.className = 'stat-value warning';
        } else {
            accuracyDisplay.className = 'stat-value danger';
        }

        errorsDisplay.textContent = errors;

        // Check if time is up
        if (remainingSeconds <= 0) {
            endTest();
        }
    }

    function endTest() {
        isTestActive = false;
        clearInterval(timerInterval);
        // Mark session as complete
        if (sessionTracker) sessionTracker.complete();

        const elapsedSeconds = Math.min((Date.now() - startTime) / 1000, TIME_LIMIT);
        const minutes = elapsedSeconds / 60;
        const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
        const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;

        // Check if passed
        const passed = wpm >= REQUIRED_WPM && accuracy >= REQUIRED_ACCURACY;

        // Save WPM result to database
        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                elapsed: passed ? wpm : -wpm,
                accuracy: accuracy,
                duration: Math.round(elapsedSeconds),
                exercise_id: '011'
            })
        });

        // Update modal content
        const resultTitle = document.getElementById('result-title');
        const resultLevel = document.getElementById('result-level');
        const resultWpm = document.getElementById('result-wpm');
        const resultReqWpm = document.getElementById('result-req-wpm');
        const resultAccuracy = document.getElementById('result-accuracy');
        const resultReqAccuracy = document.getElementById('result-req-accuracy');
        const resultErrors = document.getElementById('result-errors');
        const resultMessage = document.getElementById('result-message');
        const resultBtn = document.getElementById('result-btn');

        resultLevel.textContent = 'Ülesanne 011';
        resultReqWpm.textContent = REQUIRED_WPM;
        resultReqAccuracy.textContent = REQUIRED_ACCURACY + '%';
        resultErrors.textContent = errors;

        // WPM with pass/fail color
        resultWpm.textContent = wpm;
        resultWpm.className = 'result-stat-value ' + (wpm >= REQUIRED_WPM ? 'passed' : 'failed');

        // Accuracy with pass/fail color
        resultAccuracy.textContent = accuracy + '%';
        resultAccuracy.className = 'result-stat-value ' + (accuracy >= REQUIRED_ACCURACY ? 'passed' : 'failed');

        if (passed) {
            resultTitle.textContent = 'LÄBITUD!';
            resultTitle.className = 'passed';
            resultMessage.textContent = 'Suurepärane! Sa läbisid ülesande.';
            resultMessage.className = 'result-message success';
            resultBtn.textContent = 'Proovi uuesti';
            resultBtn.className = 'result-btn primary';
        } else {
            resultTitle.textContent = 'LÄBIMATA';
            resultTitle.className = 'failed';

            let failReasons = [];
            if (wpm < REQUIRED_WPM) {
                failReasons.push(`WPM on liiga madal (${wpm} < ${REQUIRED_WPM})`);
            }
            if (accuracy < REQUIRED_ACCURACY) {
                failReasons.push(`Täpsus on liiga madal (${accuracy}% < ${REQUIRED_ACCURACY}%)`);
            }
            resultMessage.textContent = failReasons.join('. ') + '. Proovi uuesti!';
            resultMessage.className = 'result-message failure';
            resultBtn.textContent = 'Proovi uuesti';
            resultBtn.className = 'result-btn secondary';
        }

        // Show modal
        document.getElementById('result-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('result-modal').classList.remove('show');
        location.reload();
    }

    // Event listeners
    textDisplay.addEventListener('click', () => {
        typingInput.focus();
    });

    // Handle backspace key
    typingInput.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
            e.preventDefault();

            if (!isTestActive) return;

            // Can't go back if at the very beginning
            if (currentWordIndex === 0 && currentLetterIndex === 0) return;

            // Go back one position
            if (currentLetterIndex > 0) {
                currentLetterIndex--;
            } else {
                // Go to previous word
                currentWordIndex--;
                const prevWord = textDisplay.querySelector(`[data-word-index="${currentWordIndex}"]`);
                if (prevWord) {
                    const letters = prevWord.querySelectorAll('.letter');
                    currentLetterIndex = letters.length - 1;
                }
            }

            // Get the letter we're going back to
            const currentWord = textDisplay.querySelector(`[data-word-index="${currentWordIndex}"]`);
            if (currentWord) {
                const currentLetter = currentWord.querySelector(`[data-letter-index="${currentLetterIndex}"]`);
                if (currentLetter) {
                    // If the letter was correct, reduce correct count
                    if (currentLetter.classList.contains('correct')) {
                        correctChars--;
                        totalChars--;
                    }
                    // Remove styling
                    currentLetter.classList.remove('correct');
                }
            }

            // Clear the floating error bubble
            clearErrorBubble();

            updateCurrentLetter();
            updateStats();
        }
    });

    typingInput.addEventListener('input', (e) => {
        if (!isTestActive) {
            startTest();
        }

        const typedChar = e.data;
        if (!typedChar) return;

        const currentWord = textDisplay.querySelector(`[data-word-index="${currentWordIndex}"]`);
        if (!currentWord) return;

        const currentLetter = currentWord.querySelector(`[data-letter-index="${currentLetterIndex}"]`);
        if (!currentLetter) return;

        const expectedChar = currentLetter.textContent;

        if (typedChar === expectedChar) {
            // Correct key - advance
            currentLetter.classList.remove('blocked');
            currentLetter.classList.add('correct');
            correctChars++;
            totalChars++;

            // Clear error bubble when moving to a new word
            currentLetterIndex++;
            const nextLetter = currentWord.querySelector(`[data-letter-index="${currentLetterIndex}"]`);
            if (!nextLetter) {
                // Move to next word
                currentWordIndex++;
                currentLetterIndex = 0;
                clearErrorBubble();

                // Check if all words completed
                if (currentWordIndex >= words.length) {
                    endTest();
                    typingInput.value = '';
                    return;
                }
            }

            updateCurrentLetter();
            updateStats();
        } else {
            // Wrong key - BLOCK, don't advance
            errors++;
            totalChars++;

            // Shake animation
            currentLetter.classList.remove('blocked');
            void currentLetter.offsetWidth; // Force reflow to restart animation
            currentLetter.classList.add('blocked');

            // Show error bubble
            showWrongKeyBubble(currentLetter, typedChar, currentWordIndex);

            updateStats();
        }

        typingInput.value = '';
    });

    // Initialize
    initializeTest();

    // Auto-focus on load
    typingInput.focus();
</script>
