<style>
    #balloon-exercise {
        max-width: 1250px;
        margin: 20px auto;
        font-family: "Trebuchet MS", Verdana, sans-serif;
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
    .scene {
        position: relative;
        min-height: 700px;
        overflow: hidden;
        border-radius: 24px;
        background:
            radial-gradient(circle at 15% 14%, rgba(255, 255, 255, 0.35) 0 28px, transparent 29px),
            radial-gradient(circle at 20% 14%, rgba(255, 255, 255, 0.35) 0 34px, transparent 35px),
            radial-gradient(circle at 25% 14%, rgba(255, 255, 255, 0.35) 0 24px, transparent 25px),
            radial-gradient(circle at 60% 10%, rgba(255, 255, 255, 0.3) 0 26px, transparent 27px),
            radial-gradient(circle at 64% 10%, rgba(255, 255, 255, 0.3) 0 32px, transparent 33px),
            radial-gradient(circle at 68% 10%, rgba(255, 255, 255, 0.3) 0 22px, transparent 23px),
            linear-gradient(180deg, #2f88c4 0%, #64b0d7 56%, #86cfcf 100%);
        box-shadow: 0 22px 40px rgba(37, 88, 125, 0.18);
        user-select: none;
    }
    .scene::before {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 210px;
        background:
            radial-gradient(circle at 10% 100%, rgba(82, 154, 180, 0.6) 0 85px, transparent 86px),
            radial-gradient(circle at 28% 100%, rgba(82, 154, 180, 0.55) 0 120px, transparent 121px),
            radial-gradient(circle at 48% 100%, rgba(82, 154, 180, 0.45) 0 80px, transparent 81px),
            radial-gradient(circle at 74% 100%, rgba(82, 154, 180, 0.55) 0 110px, transparent 111px),
            radial-gradient(circle at 92% 100%, rgba(82, 154, 180, 0.5) 0 90px, transparent 91px);
        opacity: 0.8;
        pointer-events: none;
    }
    .scene::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 130px;
        background:
            radial-gradient(circle at 15% 0, rgba(30, 111, 147, 0.6) 0 52px, transparent 53px),
            radial-gradient(circle at 40% 0, rgba(30, 111, 147, 0.55) 0 72px, transparent 73px),
            radial-gradient(circle at 65% 0, rgba(30, 111, 147, 0.58) 0 64px, transparent 65px),
            radial-gradient(circle at 88% 0, rgba(30, 111, 147, 0.5) 0 58px, transparent 59px);
        pointer-events: none;
    }
    .balloon-row {
        position: absolute;
        inset: 60px 40px auto 40px;
        height: 280px;
    }
    .balloon-slot {
        position: absolute;
        width: 120px;
        height: 185px;
        transform: translateX(-50%);
        transition: transform 0.18s ease, filter 0.18s ease, opacity 0.18s ease;
    }
    .balloon-slot.active {
        transform: translateX(-50%) scale(1.05);
        filter: drop-shadow(0 0 18px rgba(255, 244, 174, 0.78));
    }
    .balloon-slot.wrong {
        animation: balloon-shake 0.24s ease;
    }
    .balloon {
        position: absolute;
        top: 0;
        left: 18px;
        width: 84px;
        height: 112px;
        border-radius: 54% 54% 48% 48%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 56px;
        line-height: 1;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
        box-shadow:
            inset -10px -16px 18px rgba(0, 0, 0, 0.16),
            inset 10px 14px 18px rgba(255, 255, 255, 0.3),
            0 16px 24px rgba(0, 0, 0, 0.14);
    }
    .balloon::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 18px;
        width: 22px;
        height: 28px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.28);
        transform: rotate(-18deg);
    }
    .balloon::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -12px;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 9px solid transparent;
        border-right: 9px solid transparent;
        border-top: 14px solid rgba(170, 111, 44, 0.85);
    }
    .balloon-letter {
        position: relative;
        top: -6px;
    }
    .rope {
        position: absolute;
        left: 59px;
        top: 112px;
        width: 2px;
        height: 44px;
        background: rgba(120, 90, 57, 0.75);
    }
    .basket {
        position: absolute;
        left: 44px;
        top: 156px;
        width: 30px;
        height: 15px;
        border-radius: 3px;
        background: linear-gradient(180deg, #a9733f 0%, #795330 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }
    .basket::before,
    .basket::after {
        content: '';
        position: absolute;
        top: -10px;
        width: 2px;
        height: 10px;
        background: rgba(120, 90, 57, 0.75);
    }
    .basket::before {
        left: 4px;
        transform: rotate(16deg);
    }
    .basket::after {
        right: 4px;
        transform: rotate(-16deg);
    }
    .arrow {
        position: absolute;
        top: 38px;
        width: 32px;
        height: 24px;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .balloon-slot.active .arrow {
        opacity: 1;
    }
    .arrow::before,
    .arrow::after {
        content: '';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
    }
    .arrow::before {
        width: 18px;
        height: 8px;
        background: #f8e44f;
        border-radius: 999px;
    }
    .arrow::after {
        width: 0;
        height: 0;
        border-top: 9px solid transparent;
        border-bottom: 9px solid transparent;
    }
    .arrow-left {
        left: -18px;
    }
    .arrow-left::before {
        right: 0;
    }
    .arrow-left::after {
        left: 0;
        border-right: 14px solid #f8e44f;
    }
    .arrow-right {
        right: -18px;
    }
    .arrow-right::before {
        left: 0;
    }
    .arrow-right::after {
        right: 0;
        border-left: 14px solid #f8e44f;
    }
    .scene-instruction {
        position: absolute;
        inset: 330px 40px auto;
        text-align: center;
        color: rgba(255, 255, 255, 0.95);
        font-size: 30px;
        font-weight: bold;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
    }
    .scene-subtitle {
        margin-top: 12px;
        font-size: 18px;
        font-weight: normal;
        opacity: 0.95;
    }
    .current-target {
        display: inline-flex;
        min-width: 58px;
        height: 58px;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.18);
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.18);
        font-size: 34px;
    }
    .hint-banner {
        position: absolute;
        left: 50%;
        bottom: 86px;
        transform: translateX(-50%);
        min-width: 260px;
        padding: 14px 20px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        text-align: center;
        font-size: 18px;
        backdrop-filter: blur(6px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
    }
    #progress-info {
        margin: 14px auto 0;
        max-width: 1250px;
        font-family: monospace;
    }
    .stat-row {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
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
    .hidden-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    @keyframes balloon-shake {
        0%, 100% { transform: translateX(-50%); }
        25% { transform: translateX(calc(-50% - 6px)); }
        75% { transform: translateX(calc(-50% + 6px)); }
    }
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
        background: #2196F3;
        color: white;
    }
    .result-btn:hover {
        background: #1976D2;
    }
    @media (max-width: 900px) {
        .scene {
            min-height: 760px;
        }
        .balloon-row {
            inset: 40px 15px auto 15px;
            height: 320px;
        }
        .balloon-slot {
            width: 92px;
            height: 160px;
        }
        .balloon {
            left: 10px;
            width: 72px;
            height: 98px;
            font-size: 46px;
        }
        .rope {
            left: 46px;
        }
        .basket {
            left: 31px;
            top: 142px;
        }
        .scene-instruction {
            inset: 360px 20px auto;
            font-size: 24px;
        }
        .scene-subtitle {
            font-size: 16px;
        }
        .hint-banner {
            min-width: 220px;
            width: calc(100% - 36px);
            bottom: 70px;
            font-size: 16px;
        }
    }
</style>

<p>Klaviatuuriharjutus õhupallidega. Vajuta seda tähte, mille poole kaks kollast noolt näitavad. Harjutus mõõdab kiirust ja täpsust 30 sekundi jooksul.</p>
<p class="requirements" id="requirements">Nõuded: WPM ≥ 20, Täpsus ≥ 90%</p>

<div class="options-row">
    <label><input type="checkbox" id="fixed-sequence-toggle"> Sama tähtede jada igal korral</label>
</div>

<div id="balloon-exercise">
    <div class="scene" id="scene">
        <div class="balloon-row" id="balloon-row"></div>
        <div class="scene-instruction">
            Vajuta tähte
            <span class="current-target" id="current-target">a</span>
            <div class="scene-subtitle">Kollased nooled näitavad aktiivset õhupalli</div>
        </div>
        <div class="hint-banner" id="hint-banner">Klõpsa mängualal või vajuta kohe klaviatuuril</div>
        <input type="text" id="typing-input" class="hidden-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
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
            <div class="stat-item">
                <span class="stat-label">Õigeid tähti:</span>
                <span class="stat-value" id="hits-display">0</span>
            </div>
        </div>
    </div>
</div>

<div class="result-modal" id="result-modal">
    <div class="result-modal-content">
        <h2 id="result-title">Tulemus</h2>
        <div class="result-level" id="result-level">Ülesanne 013</div>
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
        <button class="result-btn" id="result-btn" onclick="closeModal()">Jätka</button>
    </div>
</div>

<script>
    const REQUIRED_WPM = 20;
    const REQUIRED_ACCURACY = 90;
    const TIME_LIMIT = 30;
    const STORAGE_KEY = 'exercise013_fixed_sequence';
    const STORAGE_CHECKBOX_KEY = 'exercise013_fixed_enabled';
    const LETTER_POOL = ['a', 's', 'd', 'f', 'j', 'k', 'l', 'ä', 'ö', 'õ', 'ü', 'u'];
    const BALLOON_COLORS = ['#b98be4', '#f56b4f', '#ffd55d', '#7595e8', '#ff845b', '#9f7be8', '#7cb2ff', '#f2ad46'];
    const BALLOON_SLOTS = [
        { left: 8, top: 54 },
        { left: 20, top: 8 },
        { left: 34, top: 18 },
        { left: 47, top: 6 },
        { left: 61, top: 58 },
        { left: 73, top: 40 },
        { left: 86, top: 14 },
        { left: 97, top: 24 }
    ];

    const fixedSequenceToggle = document.getElementById('fixed-sequence-toggle');
    const scene = document.getElementById('scene');
    const balloonRow = document.getElementById('balloon-row');
    const currentTargetDisplay = document.getElementById('current-target');
    const hintBanner = document.getElementById('hint-banner');
    const typingInput = document.getElementById('typing-input');
    const timerDisplay = document.getElementById('timer');
    const wpmDisplay = document.getElementById('wpm-display');
    const accuracyDisplay = document.getElementById('accuracy-display');
    const errorsDisplay = document.getElementById('errors-display');
    const hitsDisplay = document.getElementById('hits-display');

    if (localStorage.getItem(STORAGE_CHECKBOX_KEY) === 'true') {
        fixedSequenceToggle.checked = true;
    }

    fixedSequenceToggle.addEventListener('change', function() {
        localStorage.setItem(STORAGE_CHECKBOX_KEY, this.checked);
        if (!this.checked) {
            localStorage.removeItem(STORAGE_KEY);
        }
    });

    function shuffle(array) {
        const copy = [...array];
        for (let i = copy.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copy[i], copy[j]] = [copy[j], copy[i]];
        }
        return copy;
    }

    function buildSequence(length) {
        const sequence = [];
        for (let i = 0; i < length; i++) {
            sequence.push(LETTER_POOL[Math.floor(Math.random() * LETTER_POOL.length)]);
        }
        return sequence;
    }

    function getSequence() {
        if (fixedSequenceToggle.checked) {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                return JSON.parse(stored);
            }
        }

        const generated = buildSequence(250);
        if (fixedSequenceToggle.checked) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(generated));
        }
        return generated;
    }

    let sequence = getSequence();
    let currentIndex = 0;
    let correctChars = 0;
    let totalChars = 0;
    let errors = 0;
    let startTime = null;
    let isTestActive = false;
    let isFinished = false;
    let timerInterval = null;
    let sessionTracker = null;
    let activeBalloonSlot = null;

    function ensureSequenceAhead() {
        if (currentIndex >= sequence.length - 12) {
            sequence = sequence.concat(buildSequence(120));
        }
    }

    function createBalloonMarkup(letter, color, isActive, slot) {
        const slotDiv = document.createElement('div');
        slotDiv.className = 'balloon-slot' + (isActive ? ' active' : '');
        slotDiv.style.left = slot.left + '%';
        slotDiv.style.top = slot.top + 'px';
        slotDiv.dataset.letter = letter;

        slotDiv.innerHTML = `
            <div class="arrow arrow-left"></div>
            <div class="arrow arrow-right"></div>
            <div class="balloon" style="background:${color}">
                <span class="balloon-letter">${letter}</span>
            </div>
            <div class="rope"></div>
            <div class="basket"></div>
        `;

        return slotDiv;
    }

    function renderBalloons() {
        ensureSequenceAhead();
        const visibleLetters = sequence.slice(currentIndex, currentIndex + BALLOON_SLOTS.length);
        while (visibleLetters.length < BALLOON_SLOTS.length) {
            visibleLetters.push(LETTER_POOL[Math.floor(Math.random() * LETTER_POOL.length)]);
        }

        const targetLetter = visibleLetters[0];
        currentTargetDisplay.textContent = targetLetter;
        balloonRow.innerHTML = '';

        BALLOON_SLOTS.forEach((slot, index) => {
            const isActive = index === 0;
            const slotDiv = createBalloonMarkup(
                visibleLetters[index],
                BALLOON_COLORS[index % BALLOON_COLORS.length],
                isActive,
                slot
            );
            balloonRow.appendChild(slotDiv);
            if (isActive) {
                activeBalloonSlot = slotDiv;
            }
        });
    }

    function startTest() {
        if (isTestActive || isFinished) {
            return;
        }
        isTestActive = true;
        startTime = Date.now();
        hintBanner.textContent = 'Mäng käib. Vajuta alati vasakpoolset esile toodud tähte.';
        timerInterval = setInterval(updateStats, 100);

        if (window.SessionTracker && window.RIIDAJA_USER) {
            sessionTracker = new SessionTracker(
                window.RIIDAJA_USER.email,
                window.RIIDAJA_USER.name,
                '013'
            );
            sessionTracker.start();
        }
    }

    function updateStats() {
        if (!startTime) {
            return;
        }

        const elapsedSeconds = (Date.now() - startTime) / 1000;
        const remainingSeconds = Math.max(0, TIME_LIMIT - elapsedSeconds);
        const minutes = elapsedSeconds / 60;
        const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
        const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;

        timerDisplay.textContent = remainingSeconds.toFixed(1) + ' s';
        timerDisplay.className = remainingSeconds <= 5
            ? 'stat-value danger'
            : remainingSeconds <= 10
                ? 'stat-value warning'
                : 'stat-value';

        wpmDisplay.textContent = wpm;
        wpmDisplay.className = wpm >= REQUIRED_WPM
            ? 'stat-value success'
            : wpm >= REQUIRED_WPM * 0.7
                ? 'stat-value warning'
                : 'stat-value danger';

        accuracyDisplay.textContent = accuracy + '%';
        accuracyDisplay.className = accuracy >= REQUIRED_ACCURACY
            ? 'stat-value success'
            : accuracy >= REQUIRED_ACCURACY - 5
                ? 'stat-value warning'
                : 'stat-value danger';

        errorsDisplay.textContent = errors;
        hitsDisplay.textContent = correctChars;

        if (remainingSeconds <= 0) {
            endTest();
        }
    }

    function handleWrongInput() {
        errors++;
        totalChars++;
        hintBanner.textContent = 'Vale täht. Vaata vasakpoolset noolega õhupalli ja proovi uuesti.';

        if (activeBalloonSlot) {
            activeBalloonSlot.classList.remove('wrong');
            void activeBalloonSlot.offsetWidth;
            activeBalloonSlot.classList.add('wrong');
        }

        updateStats();
    }

    function handleCorrectInput() {
        correctChars++;
        totalChars++;
        currentIndex++;
        hintBanner.textContent = 'Õige. Järgmine täht ilmus samasse vasakpoolsesse kohta.';
        renderBalloons();
        updateStats();
    }

    function normalizeKey(key) {
        if (!key || key.length !== 1) {
            return null;
        }
        return key.toLowerCase();
    }

    function onTypedCharacter(rawKey) {
        if (isFinished) {
            return;
        }

        const normalized = normalizeKey(rawKey);
        if (!normalized) {
            return;
        }

        if (!isTestActive) {
            startTest();
        }

        const targetLetter = sequence[currentIndex];
        if (normalized === targetLetter) {
            handleCorrectInput();
        } else {
            handleWrongInput();
        }

        typingInput.value = '';
    }

    function endTest() {
        if (isFinished || !startTime) {
            return;
        }

        isFinished = true;
        isTestActive = false;
        clearInterval(timerInterval);

        if (sessionTracker) {
            sessionTracker.complete();
        }

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
                exercise_id: '013'
            })
        });

        document.getElementById('result-wpm').textContent = wpm;
        document.getElementById('result-wpm').className = 'result-stat-value ' + (wpm >= REQUIRED_WPM ? 'passed' : 'failed');
        document.getElementById('result-req-wpm').textContent = REQUIRED_WPM;
        document.getElementById('result-accuracy').textContent = accuracy + '%';
        document.getElementById('result-accuracy').className = 'result-stat-value ' + (accuracy >= REQUIRED_ACCURACY ? 'passed' : 'failed');
        document.getElementById('result-req-accuracy').textContent = REQUIRED_ACCURACY + '%';
        document.getElementById('result-errors').textContent = errors;

        const resultTitle = document.getElementById('result-title');
        const resultMessage = document.getElementById('result-message');

        if (passed) {
            resultTitle.textContent = 'LÄBITUD!';
            resultTitle.className = 'passed';
            resultMessage.textContent = 'Õige tempo ja piisav täpsus. Ülesanne sai tehtud.';
            resultMessage.className = 'result-message success';
        } else {
            const failReasons = [];
            if (wpm < REQUIRED_WPM) {
                failReasons.push(`WPM on liiga madal (${wpm} < ${REQUIRED_WPM})`);
            }
            if (accuracy < REQUIRED_ACCURACY) {
                failReasons.push(`Täpsus on liiga madal (${accuracy}% < ${REQUIRED_ACCURACY}%)`);
            }
            resultTitle.textContent = 'LÄBIMATA';
            resultTitle.className = 'failed';
            resultMessage.textContent = failReasons.join('. ') + '. Proovi uuesti.';
            resultMessage.className = 'result-message failure';
        }

        document.getElementById('result-modal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('result-modal').classList.remove('show');
        location.reload();
    }

    scene.addEventListener('click', () => typingInput.focus());
    typingInput.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            return;
        }

        if (event.key === 'Backspace' || event.key === 'Delete') {
            typingInput.value = '';
        }
    });

    typingInput.addEventListener('input', (event) => {
        const typedChar = event.data || typingInput.value.slice(-1);
        onTypedCharacter(typedChar);
        typingInput.value = '';
    });
    typingInput.addEventListener('paste', (event) => event.preventDefault());

    renderBalloons();
    updateStats();
    typingInput.focus();
</script>
