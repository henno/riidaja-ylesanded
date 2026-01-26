
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

<p>Pimekirjutamise harjutus. Kirjuta ekraanil kuvatavad sõnad võimalikult kiiresti ja täpselt. Sul on aega 30 sekundit.</p>
<p class="requirements" id="requirements">Nõuded: WPM ≥ 30, Täpsus ≥ 90%</p>

<form id="task-form">
    <table id="typing-table">
        <thead>
        <tr><th>Kirjuta sõnad (klõpsa siia alustamiseks)</th></tr>
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
        <div class="result-level" id="result-level">Tase 1</div>
        <div class="result-stats">
            <div class="result-stat-row">
                <span class="result-stat-label">Kirjutamiskiirus (WPM):</span>
                <span class="result-stat-value" id="result-wpm">0</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Nõutav WPM:</span>
                <span class="result-stat-value" id="result-req-wpm">20</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Täpsus:</span>
                <span class="result-stat-value" id="result-accuracy">100%</span>
            </div>
            <div class="result-stat-row">
                <span class="result-stat-label">Nõutav täpsus:</span>
                <span class="result-stat-value" id="result-req-accuracy">90%</span>
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
    // Requirements: 30 WPM, 90% accuracy
    const REQUIRED_WPM = 30;
    const REQUIRED_ACCURACY = 90;
    const TIME_LIMIT = 30;

    // Estonian common words
    const estonianWords = [
        'ja', 'on', 'ei', 'see', 'kui', 'aga', 'kas', 'või', 'siis', 'et',
        'ma', 'sa', 'ta', 'me', 'te', 'nad', 'kes', 'mis', 'kus', 'kuidas',
        'miks', 'millal', 'oma', 'üks', 'kaks', 'kolm', 'neli', 'viis', 'kuus',
        'seitse', 'kaheksa', 'üheksa', 'kümme', 'aeg', 'inimene', 'maja', 'tee',
        'käsi', 'silm', 'jalg', 'päev', 'öö', 'hommik', 'õhtu', 'aasta', 'kuu',
        'nädal', 'tund', 'minut', 'sekund', 'vesi', 'tuli', 'maa', 'taevas',
        'päike', 'kuu', 'täht', 'puu', 'lill', 'lind', 'kala', 'koer', 'kass',
        'laud', 'tool', 'raamat', 'paber', 'pliiats', 'õun', 'leib', 'piim',
        'vein', 'kohv', 'tee', 'sool', 'suhkur', 'mesi', 'või', 'juust', 'liha',
        'kana', 'muna', 'riis', 'kartul', 'tomat', 'kurk', 'sibul', 'küüslauk',
        'punane', 'sinine', 'roheline', 'kollane', 'valge', 'must', 'hall', 'pruun',
        'suur', 'väike', 'hea', 'halb', 'ilus', 'kena', 'kiire', 'aeglane',
        'tugev', 'nõrk', 'pikk', 'lühike', 'lai', 'kitsas', 'uus', 'vana',
        'minema', 'tulema', 'olema', 'tegema', 'võtma', 'andma', 'nägema', 'kuulma',
        'teadma', 'mõtlema', 'rääkima', 'ütlema', 'küsima', 'vastama', 'lugema', 'kirjutama',
        'sööma', 'jooma', 'magama', 'ärkama', 'istuma', 'seisma', 'kõndima', 'jooksma',
        'töö', 'kool', 'kodu', 'perekond', 'ema', 'isa', 'laps', 'õde', 'vend',
        'vanaema', 'vanaisa', 'sõber', 'naaber', 'õpetaja', 'arst', 'poliitik', 'kirjanik',
        'riik', 'linn', 'küla', 'tänav', 'väljak', 'park', 'mets', 'jõgi', 'järv',
        'meri', 'rand', 'mägi', 'org', 'väli', 'aed', 'õu', 'ruum', 'korter',
        'auto', 'buss', 'tramm', 'rong', 'laev', 'lennuk', 'jalgratas', 'mootorratas',
        'telefon', 'arvuti', 'internet', 'sõnum', 'kiri', 'pakett',
        'number', 'arv', 'summa', 'hind', 'raha', 'euro', 'sent', 'palk',
        'kell', 'minut', 'tund', 'varajane', 'hiline', 'nüüd', 'täna', 'homme', 'eile',
        'alati', 'mitte', 'kunagi', 'vahel', 'sageli', 'harva', 'veel', 'juba', 'peaaegu',
        'väga', 'liiga', 'palju', 'vähe', 'rohkem', 'vähem', 'kõik', 'mõni', 'ükski'
    ];

    function getRandomWords(count) {
        const shuffled = [...estonianWords].sort(() => Math.random() - 0.5);
        return shuffled.slice(0, Math.min(count, shuffled.length));
    }

    let words = getRandomWords(100);
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
    function showWrongKeyBubble(letterElement, wrongChar) {
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
                    '007'
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
        // For failed attempts, save negative WPM to distinguish from passed
        fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                elapsed: passed ? wpm : -wpm,
                accuracy: accuracy,
                duration: Math.round(elapsedSeconds),
                exercise_id: '007'
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

        resultLevel.textContent = 'Ülesanne 007';
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
                    // If the letter was incorrect, reduce error count
                    if (currentLetter.classList.contains('incorrect')) {
                        errors--;
                    }
                    // If the letter was correct, reduce correct count
                    if (currentLetter.classList.contains('correct')) {
                        correctChars--;
                    }
                    // Reduce total chars if letter was typed
                    if (currentLetter.classList.contains('correct') || currentLetter.classList.contains('incorrect')) {
                        totalChars--;
                    }
                    // Remove styling
                    currentLetter.classList.remove('correct', 'incorrect');
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

        totalChars++;

        if (typedChar === expectedChar) {
            currentLetter.classList.add('correct');
            correctChars++;
        } else {
            currentLetter.classList.add('incorrect');
            errors++;
            // Show speech bubble with the wrong key pressed
            showWrongKeyBubble(currentLetter, typedChar);
        }

        currentLetterIndex++;

        // Check if we're at the end of the word
        const nextLetter = currentWord.querySelector(`[data-letter-index="${currentLetterIndex}"]`);
        if (!nextLetter) {
            // Move to next word
            currentWordIndex++;
            currentLetterIndex = 0;

            // Check if all words completed
            if (currentWordIndex >= words.length) {
                endTest();
                typingInput.value = '';
                return;
            }
        }

        updateCurrentLetter();
        updateStats();
        typingInput.value = '';
    });

    // Initialize
    initializeTest();

    // Auto-focus on load
    typingInput.focus();
</script>
