<?php
require_once __DIR__ . '/../models/ResultsModel.php';
require_once __DIR__ . '/../models/StudentsModel.php';

$resultsModel = new ResultsModel();
$studentsModel = new StudentsModel();

$userEmail = $_SESSION['user']['email'] ?? '';

// Count user's positive WPM attempts (passed attempts have positive elapsed)
$passedAttempts = $resultsModel->getAllByEmailAndExercise($userEmail, '014');
$passCount = 0;
foreach ($passedAttempts as $attempt) {
    if ($attempt['elapsed'] > 0) {
        $passCount++;
    }
}
$passCount = min($passCount, 3);
?>
<style>
    #space-invaders-game {
        max-width: 900px;
        margin: 20px auto;
        font-family: 'Courier New', monospace;
    }
    .game-container {
        position: relative;
        width: 100%;
        height: 500px;
        background: #000;
        border: 3px solid #0f0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 30px rgba(0, 255, 0, 0.3);
    }
    .game-container::before {
        content: '';
        position: absolute;
        inset: 0;
        background: 
            radial-gradient(circle at 20% 30%, rgba(0, 255, 0, 0.03) 0 2px, transparent 3px),
            radial-gradient(circle at 60% 70%, rgba(0, 255, 0, 0.03) 0 2px, transparent 3px),
            radial-gradient(circle at 80% 20%, rgba(0, 255, 0, 0.03) 0 2px, transparent 3px);
        background-size: 50px 50px;
        pointer-events: none;
    }
    .star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: #0f0;
        border-radius: 50%;
        opacity: 0.5;
    }
    .invader {
        position: absolute;
        font-size: 18px;
        font-weight: bold;
        color: #0f0;
        text-shadow: 0 0 10px #0f0;
        padding: 8px 12px;
        border: 2px solid transparent;
        border-radius: 4px;
        transition: border-color 0.15s, box-shadow 0.15s;
        white-space: nowrap;
        cursor: default;
        transform: translateX(-50%);
        will-change: top, left;
    }
    .invader.locked {
        border-color: #ff0;
        box-shadow: 0 0 15px #ff0, 0 0 30px rgba(255, 255, 0, 0.3);
    }
    .inv-letter {
        color: #0f0;
        text-shadow: 0 0 10px #0f0;
        transition: color 0.1s, text-shadow 0.1s;
    }
    .invader.locked .inv-letter.typed {
        color: #555;
        text-shadow: none;
    }
    .invader.locked .inv-letter {
        color: #ff0;
        text-shadow: 0 0 10px #ff0;
    }
    .invader.exploding {
        animation: explode 0.3s ease-out forwards;
    }
    .invader.grounded {
        opacity: 0.6;
        transform: translateX(-50%) scale(0.85);
        pointer-events: none;
        z-index: 2;
    }
    @keyframes explode {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.8; color: #fff; text-shadow: 0 0 30px #fff; }
        100% { transform: scale(0); opacity: 0; }
    }
    .invader-label {
        position: absolute;
        bottom: -18px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        color: #0f0;
        opacity: 0.7;
    }
    .game-over-line {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #f00;
        box-shadow: 0 0 10px #f00;
    }
    .stats-bar {
        display: flex;
        justify-content: space-between;
        padding: 15px 20px;
        background: #111;
        border: 2px solid #0f0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        color: #0f0;
        font-size: 14px;
    }
    .stat-item {
        display: flex;
        gap: 8px;
    }
    .stat-label {
        opacity: 0.7;
    }
    .stat-value {
        font-weight: bold;
    }
    .stat-value.success {
        color: #0f0;
    }
    .stat-value.warning {
        color: #ff0;
    }
    .stat-value.danger {
        color: #f00;
    }
    .level-indicator {
        color: #ff0;
        border: 1px solid #ff0;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .hint-text {
        text-align: center;
        margin-top: 10px;
        padding: 10px;
        background: #111;
        border: 1px solid #0f0;
        border-radius: 4px;
        color: #0f0;
        font-size: 12px;
    }
    .hidden-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
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
    .requirements {
        font-size: 12px;
        color: #333;
        margin-top: 5px;
    }
    .progress-bar {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 150px;
        height: 8px;
        background: #222;
        border: 1px solid #0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: #0f0;
        transition: width 0.1s linear;
    }
    .completions-display {
        position: absolute;
        top: 10px;
        left: 10px;
        display: flex;
        gap: 5px;
    }
    .completion-box {
        width: 20px;
        height: 20px;
        border: 2px solid #0f0;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #0f0;
    }
    .completion-box.completed {
        background: #0f0;
        color: #000;
    }
</style>

<p>Klaviatuurimäng kosmose-stiilis. Vajuta sõna esimest tähte, et lukustuda ja kirjuta see lõpuni. Sõnad langevad alla - kui jõuavad alla, mäng lõppeb!</p>
<p class="requirements" id="requirements-text">Nõuded: WPM ≥ 20, Täpsus ≥ 90% | Kestus: 30 sekundit</p>

<div id="space-invaders-game">
    <div class="game-container" id="game-container">
        <div class="completions-display" id="completions-display">
            <div class="completion-box" id="comp-1">1</div>
            <div class="completion-box" id="comp-2">2</div>
            <div class="completion-box" id="comp-3">3</div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
        <input type="text" id="typing-input" class="hidden-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
        <div class="game-over-line"></div>
    </div>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-label">AEG:</span>
            <span class="stat-value" id="timer">30.0s</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">LEVEL:</span>
            <span class="level-indicator" id="level-display">1</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">WPM:</span>
            <span class="stat-value" id="wpm-display">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">TÄPSUS:</span>
            <span class="stat-value" id="accuracy-display">100%</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">VEAD:</span>
            <span class="stat-value" id="errors-display">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">SÕNU:</span>
            <span class="stat-value" id="words-display">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">KIIRUS:</span>
            <span class="stat-value" id="speed-display">1.0x</span>
        </div>
    </div>

    <p class="hint-text">Vajuta sõna esimest tähte selleks, et lukustada ja kirjutada. Kui sõna jõuab alla, kiireneb mäng!</p>
</div>

<div class="result-modal" id="result-modal">
    <div class="result-modal-content">
        <h2 id="result-title">Tulemus</h2>
        <div class="result-level" id="result-level">Ülesanne 014</div>
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
            <div class="result-stat-row">
                <span class="result-stat-label">Läbimised:</span>
                <span class="result-stat-value" id="result-completions">0</span>
            </div>
        </div>
        <div class="result-message" id="result-message"></div>
        <button class="result-btn" id="result-btn">Jätka</button>
    </div>
</div>

<script>
const REQUIRED_WPM = 20;
const REQUIRED_WPM_LEVEL_2 = 28;
const REQUIRED_WPM_LEVEL_3 = 35;
const REQUIRED_ACCURACY = 90;
const TIME_LIMIT = 30;
const FALL_SPEED_MIN = 15;
const FALL_SPEED_MAX = 80;
const MAX_WORDS_ON_SCREEN = 10;
const WORD_SPAWN_INTERVAL = 1500;
const SPEED_ZONE_TOP = 100;
const SPEED_ZONE_BOTTOM = 400;
const NUM_COLUMNS = 7;
const COLUMN_MARGIN = 40;

const WORDS_LEVEL_1 = [
    'aaha', 'alla', 'haak', 'hala', 'hall', 'häda', 'hääl', 'äll', 'jada', 'jaha',
    'jakk', 'jala', 'jalg', 'kaja', 'kaka', 'kakk', 'kala', 'kask', 'kass', 'laad',
    'laas', 'laga', 'lahk', 'laks', 'lall', 'lass', 'lask', 'sada', 'saag', 'saak',
    'saal', 'saga', 'saha', 'sakk', 'sala', 'sall', 'halli', 'halja', 'älli', 'jadad',
    'jakki', 'jalad', 'jalga', 'jalal', 'kajad', 'kajal', 'kalad', 'kalla', 'kassa',
    'kassi', 'laada', 'laadi', 'lahja', 'saaga', 'saaki', 'saali', 'sahad', 'salad',
    'sagad', 'sajad', 'salli'
];

const WORDS_LEVEL_2 = [
    'ajatus', 'algaja', 'alliga', 'allikas', 'alused', 'arukas', 'arutas', 'asutus',
    'eelist', 'elajas', 'elutöö', 'eriala', 'hallid', 'harida', 'harjus', 'hoolas',
    'huulik', 'jalutu', 'jootja', 'juurde', 'jutuke', 'kaelas', 'kaikus', 'kaotas',
    'karikas', 'kasuta', 'katuse', 'keelas', 'keeras', 'kehaka', 'kohale', 'korras',
    'kuulas', 'kuulja', 'laekus', 'lahtis', 'laulis', 'laused', 'leekis', 'leidis',
    'liikus', 'loetav', 'loojad', 'lootus', 'luulet', 'olijate', 'ootaja', 'osutus',
    'paljas', 'paraku', 'parasi', 'peatus', 'peegel', 'piisav', 'pojuke', 'puhkus',
    'raekoda', 'rahulik', 'raiusa', 'rikkus', 'roojane', 'saatus', 'saealus',
    'sajatas', 'sallida', 'sarikas', 'seadus', 'seelik', 'selgus', 'sisukas',
    'soojus', 'sulges', 'suurus', 'taheti', 'taotlus', 'teekate', 'teelõik',
    'tehakse', 'tellija', 'toetaja', 'tootja', 'tugeja', 'tuleks', 'tulija',
    'tuuris', 'uueaeg', 'uurija', 'vajalik', 'valges', 'valija', 'varjuk',
    'õpetus', 'ajalugu', 'algatus', 'allikas', 'arutelu', 'asutaja', 'edukalt',
    'eelarve', 'eelkõige', 'ehitaja', 'ehituse', 'elukool', 'eraldus', 'esialgu',
    'haiglas', 'hallata', 'harilik', 'harjutus', 'hooldus', 'jalakas', 'jälitus',
    'järelda', 'jätkuja', 'joonela', 'juhtida', 'juuksed', 'kaaluda', 'kaotaja',
    'karikas', 'kasulik', 'katkest', 'keerata', 'kehtida', 'kohalik', 'kohtuda',
    'koolias', 'kuulaja', 'laialtki', 'laotaja', 'lauljad', 'leidlik', 'liialdus',
    'loodaja', 'loogika', 'lugejad', 'luuletus', 'olukord', 'ootused', 'osaleda',
    'osataja', 'pahataht', 'parajalt', 'pealadu', 'peatuses', 'piisavalt', 'poliitik',
    'rahulik', 'raskelt', 'rohkesti', 'seaduse', 'selgitas', 'sõjaline', 'tõeliku',
    'teadlik', 'tegelik', 'toetaja', 'tootlus', 'tugevus', 'tulemus', 'tuletus',
    'uurijad', 'vajadus', 'valikute', 'õpetaja'
];

const WORDS_LEVEL_3 = [
    'abinõude', 'aednikud', 'ajalised', 'ajalugu', 'aknalaud', 'algklassid',
    'allikates', 'ametlikud', 'andekamad', 'andmebaas', 'arendajad', 'arvutites',
    'asjalikud', 'asutustes', 'autoritel', 'avalikult', 'ehitajate', 'ehitused',
    'elanikest', 'ettekande', 'fotograaf', 'hakkasid', 'haljastus', 'harilikud',
    'harjutame', 'huvitavad', 'igapäevane', 'ilmateade', 'inimlikkus', 'iseloomus',
    'istutatud', 'jalgpallur', 'järeldada', 'jätkuvalt', 'joonistada', 'jutustavad',
    'kaasaegne', 'kahtlused', 'kalendrid', 'kannatlik', 'kasutajad', 'kategooria',
    'katsetused', 'keerulisem', 'kehtestada', 'kirjanikud', 'kirjutasid', 'kogemused',
    'kogukonnas', 'kolmapäev', 'korraldada', 'kriitiline', 'kujutamine', 'kultuurid',
    'kvaliteedi', 'külastajad', 'lahendused', 'lahutamine', 'laialdased', 'laotamine',
    'lasteaeda', 'legendaarne', 'liikumised', 'looduslik', 'lugemisel', 'maailmade',
    'majanduses', 'mälumängur', 'meeskonnad', 'mälumängud', 'mõistlikud', 'naeratades',
    'nädalavahetus', 'näitlejad', 'nimetamine', 'noorematele', 'nõudlikkus',
    'objektidel', 'olukordades', 'omapärased', 'otsingutes', 'paindlikud', 'parandused',
    'parlamendis', 'pealkirjad', 'peatükid', 'perekonnad', 'piirkonnad', 'planeeritud',
    'praktiline', 'proovimine', 'rahulikult', 'rakendused', 'raamatutes', 'raskustega',
    'reageerima', 'riigikogu', 'sagedamini', 'salvestada', 'seadistada', 'selgitused',
    'sotsiaalne', 'soovitused', 'spetsialist', 'suhtlemine', 'sündmustel', 'tähelepanu',
    'taastamine', 'tagajärjed', 'teadmistes', 'tegevused', 'tehniline', 'toetamine',
    'tootmises', 'traditsioon', 'treeningud', 'tulevikus', 'turvaliselt', 'tutvumine',
    'tõenäoline', 'uudishimud', 'uuendamine', 'valdkonnad', 'vajalikud', 'vastutavad',
    'veebilehed', 'veenvamalt', 'võimalused', 'võrdlemine', 'õpetajatel', 'õpilastest',
    'ühendamine', 'ülesanded', 'üritustel'
];

const gameContainer = document.getElementById('game-container');
const typingInput = document.getElementById('typing-input');
const timerDisplay = document.getElementById('timer');
const wpmDisplay = document.getElementById('wpm-display');
const accuracyDisplay = document.getElementById('accuracy-display');
const errorsDisplay = document.getElementById('errors-display');
const wordsDisplay = document.getElementById('words-display');
const speedDisplay = document.getElementById('speed-display');
const levelDisplay = document.getElementById('level-display');
const progressFill = document.getElementById('progress-fill');
const resultModal = document.getElementById('result-modal');
const requirementsText = document.getElementById('requirements-text');

let invaders = [];
let correctChars = 0;
let totalChars = 0;
let errors = 0;
let wordsCompleted = 0;
let completionCount = <?= $passCount ?>;
let currentLevel = <?= $passCount ?> >= 2 ? 3 : (<?= $passCount ?> >= 1 ? 2 : 1);
let lockedWords = [];
let groundedWords = [];
let speedMultiplier = 1.0;
let startTime = null;
let isGameActive = false;
let isGameOver = false;
let gameLoopId = null;
let spawnIntervalId = null;
let lastFrameTime = 0;
let sessionTracker = null;
let wordsTyped = [];

function updateLevelDisplay() {
    levelDisplay.textContent = currentLevel;
    requirementsText.textContent = `Nõuded: WPM >= ${getRequiredWpm()}, Täpsus >= ${REQUIRED_ACCURACY}% | Kestus: 30 sekundit`;
    if (currentLevel === 1) {
        levelDisplay.style.borderColor = '#0f0';
        levelDisplay.style.color = '#0f0';
    } else if (currentLevel === 2) {
        levelDisplay.style.borderColor = '#ff0';
        levelDisplay.style.color = '#ff0';
    } else {
        levelDisplay.style.borderColor = '#f0f';
        levelDisplay.style.color = '#f0f';
    }
}

function getRequiredWpm() {
    if (currentLevel === 3) return REQUIRED_WPM_LEVEL_3;
    if (currentLevel === 2) return REQUIRED_WPM_LEVEL_2;
    return REQUIRED_WPM;
}

function getInitialSpeedMultiplier() {
    return currentLevel === 3 ? 1.35 : 1.0;
}

function getInitialSpawnCount() {
    return currentLevel === 3 ? 5 : 7;
}

function getWordsForLevel(level) {
    switch(level) {
        case 1: return WORDS_LEVEL_1;
        case 2: return WORDS_LEVEL_2;
        case 3: return WORDS_LEVEL_3;
        default: return WORDS_LEVEL_1;
    }
}

function getRandomWord(level) {
    const words = getWordsForLevel(level);
    return words[Math.floor(Math.random() * words.length)];
}

function createStars() {
    for (let i = 0; i < 50; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDelay = Math.random() * 2 + 's';
        gameContainer.appendChild(star);
    }
}

function getColumnFromX(x) {
    const containerRect = gameContainer.getBoundingClientRect();
    const columnWidth = (containerRect.width - COLUMN_MARGIN * 2) / NUM_COLUMNS;
    return Math.floor((x - COLUMN_MARGIN) / columnWidth);
}

function getUsedStartingLetters() {
    const used = new Set();
    for (const inv of invaders) {
        used.add(inv.dataset.word[0].toLowerCase());
    }
    for (const gw of groundedWords) {
        used.add(gw.dataset.word[0].toLowerCase());
    }
    return used;
}

function getActiveColumns() {
    const columns = [];
    for (const inv of invaders) {
        const invColumn = inv.dataset.column !== undefined
            ? parseInt(inv.dataset.column)
            : getColumnFromX(parseFloat(inv.dataset.x));
        if (invColumn >= 0) {
            columns.push(invColumn);
        }
    }
    return columns;
}

function spawnWord() {
    if (isGameOver) return;

    const containerRect = gameContainer.getBoundingClientRect();
    const columnWidth = (containerRect.width - COLUMN_MARGIN * 2) / NUM_COLUMNS;
    const activeColumns = getActiveColumns();

    let availableColumns = [];
    for (let col = 0; col < NUM_COLUMNS; col++) {
        let isBlocked = false;
        for (const activeColumn of activeColumns) {
            if (Math.abs(activeColumn - col) <= 1) {
                isBlocked = true;
                break;
            }
        }
        if (!isBlocked) {
            availableColumns.push(col);
        }
    }

    if (availableColumns.length === 0) return;

    const usedLetters = getUsedStartingLetters();
    const words = getWordsForLevel(currentLevel);
    const availableWords = words.filter(w => !usedLetters.has(w[0].toLowerCase()));
    
    if (availableWords.length === 0) return;

    const word = availableWords[Math.floor(Math.random() * availableWords.length)];
    const column = availableColumns[Math.floor(Math.random() * availableColumns.length)];

    const invader = document.createElement('div');
    invader.className = 'invader';
    invader.dataset.word = word;
    invader.dataset.y = 0;
    invader.dataset.column = column;

    let html = '';
    for (let i = 0; i < word.length; i++) {
        html += `<span class="inv-letter">${word[i]}</span>`;
    }
    invader.innerHTML = html;

    const x = COLUMN_MARGIN + column * columnWidth + columnWidth / 2;
    invader.style.left = x + 'px';
    invader.style.top = '60px';
    invader.dataset.x = x;

    gameContainer.appendChild(invader);
    invaders.push(invader);
}

function updateCompletionsDisplay() {
    for (let i = 1; i <= 3; i++) {
        const box = document.getElementById('comp-' + i);
        if (i <= completionCount) {
            box.classList.add('completed');
        } else {
            box.classList.remove('completed');
        }
    }
}

function checkLevelUp() {
    if (completionCount >= 1 && currentLevel < 2) {
        currentLevel = 2;
        updateLevelDisplay();
    }
    if (completionCount >= 2 && currentLevel < 3) {
        currentLevel = 3;
        updateLevelDisplay();
    }
}

function updateLockedWord() {
    invaders.forEach(inv => inv.classList.remove('locked'));
    lockedWords = [];
}

function updateLockedWordsProgress() {
    lockedWords.forEach(lw => {
        const letters = lw.element.querySelectorAll('.inv-letter');
        letters.forEach((letter, i) => {
            if (i < lw.index) {
                letter.classList.add('typed');
            } else {
                letter.classList.remove('typed');
            }
        });
    });
}

function handleKeyPress(key) {
    if (isGameOver) return;

    lockedWords = lockedWords.filter(lw => invaders.includes(lw.element) && !lw.element.classList.contains('grounded'));
    if (lockedWords.length === 0) {
        invaders.forEach(inv => inv.classList.remove('locked'));
    }

    const normalizedKey = key.toLowerCase();

    if (!isGameActive) {
        startGame();
    }

    if (lockedWords.length > 0) {
        let matchedWord = null;
        let matchedIndex = -1;

        for (let i = 0; i < lockedWords.length; i++) {
            const lw = lockedWords[i];
            const expectedChar = lw.element.dataset.word[lw.index];
            if (normalizedKey === expectedChar) {
                matchedWord = lw.element;
                matchedIndex = i;
                break;
            }
        }

        if (matchedWord) {
            lockedWords[matchedIndex].index++;
            correctChars++;
            totalChars++;
            
            for (let i = 0; i < lockedWords.length; i++) {
                if (i !== matchedIndex) {
                    const lw = lockedWords[i];
                    const nextChar = lw.element.dataset.word[lw.index];
                    if (normalizedKey === nextChar) {
                        lw.index++;
                    }
                }
            }
            
            updateLockedWordsProgress();

            let completedIndices = [];
            for (let i = 0; i < lockedWords.length; i++) {
                const lw = lockedWords[i];
                if (lw.index >= lw.element.dataset.word.length) {
                    completedIndices.push(i);
                }
            }

            for (let i = completedIndices.length - 1; i >= 0; i--) {
                const completedWord = lockedWords[completedIndices[i]].element;
                wordsCompleted++;
                wordsTyped.push(completedWord.dataset.word);
                completedWord.classList.add('exploding');
                lockedWords.splice(completedIndices[i], 1);
                checkLevelUp();
            }

            if (lockedWords.length === 0) {
                invaders.forEach(inv => inv.classList.remove('locked'));
            } else {
                lockedWords.forEach(lw => {
                    if (!invaders.includes(lw.element)) {
                        lw.element.classList.remove('locked');
                    }
                });
            }

            setTimeout(() => {
                document.querySelectorAll('.invader.exploding').forEach(el => {
                    const idx = invaders.indexOf(el);
                    if (idx > -1) invaders.splice(idx, 1);
                    el.remove();
                });
                if (invaders.length <= 1) {
                    spawnWord();
                }
            }, 300);

            updateStats();
        } else {
            errors++;
            totalChars++;
            updateStats();
        }
    } else {
        let matchingWords = [];
        invaders.forEach(inv => {
            if (inv.dataset.word[0] === normalizedKey && !inv.classList.contains('exploding')) {
                matchingWords.push({ element: inv, index: 1 });
            }
        });

        if (matchingWords.length > 0) {
            matchingWords.forEach(mw => mw.element.classList.add('locked'));
            lockedWords = matchingWords;
            correctChars++;
            totalChars++;
            updateLockedWordsProgress();
        } else {
            errors++;
            totalChars++;
        }
        updateStats();
    }

    typingInput.value = '';
}

function startGame() {
    if (isGameActive) return;
    isGameActive = true;
    startTime = Date.now();
    speedMultiplier = getInitialSpeedMultiplier();
    groundedWords.forEach(gw => gw.remove());
    groundedWords = [];

    spawnWord();
    spawnIntervalId = setInterval(spawnWord, WORD_SPAWN_INTERVAL);

    if (window.SessionTracker && window.RIIDAJA_USER) {
        sessionTracker = new SessionTracker(
            window.RIIDAJA_USER.email,
            window.RIIDAJA_USER.name,
            '014'
        );
        sessionTracker.start();
    }

    requestAnimationFrame(gameLoop);
}

function gameLoop(timestamp) {
    if (isGameOver) return;

    if (!lastFrameTime) lastFrameTime = timestamp;
    const deltaTime = (timestamp - lastFrameTime) / 1000;
    lastFrameTime = timestamp;

    const containerHeight = gameContainer.clientHeight - 30;
    if (containerHeight <= 30) {
        gameLoopId = requestAnimationFrame(gameLoop);
        return;
    }

    for (let i = invaders.length - 1; i >= 0; i--) {
        const inv = invaders[i];
        if (inv.classList.contains('exploding')) continue;

        let y = parseFloat(inv.dataset.y);
        
        const speedFactor = Math.min(1, Math.max(0, (y - SPEED_ZONE_TOP) / (SPEED_ZONE_BOTTOM - SPEED_ZONE_TOP)));
        const currentSpeed = (FALL_SPEED_MIN + (FALL_SPEED_MAX - FALL_SPEED_MIN) * speedFactor) * speedMultiplier;
        
        y += currentSpeed * deltaTime;
        inv.dataset.y = y;
        inv.style.top = y + 'px';

        if (y >= containerHeight) {
            // Word reaches bottom - add to grounded words
            const grounded = invaders.splice(i, 1)[0];
            grounded.classList.remove('locked');
            grounded.querySelectorAll('.inv-letter').forEach(letter => letter.classList.remove('typed'));
            grounded.classList.add('grounded');
            grounded.dataset.y = containerHeight;
            groundedWords.push(grounded);

            lockedWords = lockedWords.filter(lw => lw.element !== grounded);
            if (lockedWords.length === 0) {
                invaders.forEach(inv => inv.classList.remove('locked'));
            }
            
            // Increase speed by 15%
            speedMultiplier += 0.15;
            
            // Flash effect on grounded word
            grounded.style.backgroundColor = '#f00';
            grounded.style.color = '#fff';
            setTimeout(() => {
                grounded.style.backgroundColor = '';
                grounded.style.color = '';
            }, 200);
            
            // Reposition grounded words
            repositionGroundedWords();
            
            // Spawn new word if needed
            if (invaders.length < 3) {
                spawnWord();
            }
        }
    }

    updateStats();
    gameLoopId = requestAnimationFrame(gameLoop);
}

function repositionGroundedWords() {
    const containerWidth = gameContainer.clientWidth;
    const containerHeight = gameContainer.clientHeight;
    const wordsPerRow = 8;
    const wordHeight = 34;
    const wordSpacing = (containerWidth - 80) / wordsPerRow;
    
    groundedWords.forEach((gw, index) => {
        const row = Math.floor(index / wordsPerRow);
        const col = index % wordsPerRow;
        const x = 40 + col * wordSpacing;
        const y = containerHeight - 55 - row * wordHeight;
        
        gw.style.left = x + 'px';
        gw.style.top = y + 'px';
        gw.dataset.column = -1; // Mark as grounded
    });
}

function updateStats() {
    if (!startTime) return;

    const requiredWpm = getRequiredWpm();

    const elapsedSeconds = (Date.now() - startTime) / 1000;
    const remainingSeconds = Math.max(0, TIME_LIMIT - elapsedSeconds);
    const minutes = elapsedSeconds / 60;
    const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
    const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;

    timerDisplay.textContent = remainingSeconds.toFixed(1) + 's';
    timerDisplay.className = remainingSeconds <= 5
        ? 'stat-value danger'
        : remainingSeconds <= 10
            ? 'stat-value warning'
            : 'stat-value';

    wpmDisplay.textContent = wpm;
    wpmDisplay.className = wpm >= requiredWpm
        ? 'stat-value success'
        : wpm >= requiredWpm * 0.7
            ? 'stat-value warning'
            : 'stat-value danger';

    accuracyDisplay.textContent = accuracy + '%';
    accuracyDisplay.className = accuracy >= REQUIRED_ACCURACY
        ? 'stat-value success'
        : accuracy >= REQUIRED_ACCURACY - 5
            ? 'stat-value warning'
            : 'stat-value danger';

    errorsDisplay.textContent = errors;
    wordsDisplay.textContent = wordsCompleted;
    speedDisplay.textContent = speedMultiplier.toFixed(1) + 'x';
    speedDisplay.className = speedMultiplier >= 2 ? 'stat-value danger' : speedMultiplier >= 1.5 ? 'stat-value warning' : 'stat-value';

    progressFill.style.width = ((TIME_LIMIT - remainingSeconds) / TIME_LIMIT * 100) + '%';

    if (remainingSeconds <= 0 && isGameActive) {
        endGame('time');
    }
}

function endGame(reason = 'time') {
    if (isGameOver) return;

    isGameOver = true;
    isGameActive = false;

    if (gameLoopId) cancelAnimationFrame(gameLoopId);
    if (spawnIntervalId) clearInterval(spawnIntervalId);
    if (sessionTracker) sessionTracker.complete();

    const elapsedSeconds = Math.min((Date.now() - startTime) / 1000, TIME_LIMIT);
    const minutes = elapsedSeconds / 60;
    const wpm = minutes > 0 ? Math.round((correctChars / 5) / minutes) : 0;
    const accuracy = totalChars > 0 ? Math.round((correctChars / totalChars) * 100) : 100;
    const requiredWpm = getRequiredWpm();
    const passed = wpm >= requiredWpm && accuracy >= REQUIRED_ACCURACY;

    fetch('save_result.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            elapsed: passed ? wpm : -wpm,
            accuracy: accuracy,
            duration: Math.round(elapsedSeconds),
            exercise_id: '014'
        })
    });

    document.getElementById('result-wpm').textContent = wpm;
    document.getElementById('result-wpm').className = 'result-stat-value ' + (wpm >= requiredWpm ? 'passed' : 'failed');
    document.getElementById('result-req-wpm').textContent = requiredWpm;
    document.getElementById('result-accuracy').textContent = accuracy + '%';
    document.getElementById('result-accuracy').className = 'result-stat-value ' + (accuracy >= REQUIRED_ACCURACY ? 'passed' : 'failed');
    document.getElementById('result-req-accuracy').textContent = REQUIRED_ACCURACY + '%';
    document.getElementById('result-errors').textContent = errors;
    document.getElementById('result-completions').textContent = completionCount + ' / 3';

    const resultTitle = document.getElementById('result-title');
    const resultMessage = document.getElementById('result-message');

    if (passed) {
        resultTitle.textContent = 'LÄBITUD!';
        resultTitle.className = 'passed';
        resultMessage.textContent = 'Piisav kiirus ja täpsus. Ülesanne sai tehtud.';
        resultMessage.className = 'result-message success';

        // Pass count is stored in database via save_result.php
        // completionCount is set from PHP on page load
        checkLevelUp();
        updateCompletionsDisplay();
    } else {
        resultTitle.textContent = 'LÄBIMATA';
        resultTitle.className = 'failed';

        if (reason === 'invader_reached_bottom') {
            resultMessage.textContent = 'Sõna jõudis alla. Mäng läks läbi. Proovi uuesti.';
        } else {
            const failReasons = [];
            if (wpm < requiredWpm) {
                failReasons.push(`WPM on liiga madal (${wpm} < ${requiredWpm})`);
            }
            if (accuracy < REQUIRED_ACCURACY) {
                failReasons.push(`Täpsus on liiga madal (${accuracy}% < ${REQUIRED_ACCURACY}%)`);
            }
            resultMessage.textContent = failReasons.join('. ') + '. Proovi uuesti.';
        }
        resultMessage.className = 'result-message failure';
    }

    resultModal.classList.add('show');
}

function closeModal() {
    resultModal.classList.remove('show');
    location.reload();
}

updateLevelDisplay();
updateCompletionsDisplay();
createStars();
for (let i = 0; i < getInitialSpawnCount(); i++) spawnWord();
typingInput.focus();

gameContainer.addEventListener('click', () => typingInput.focus());

typingInput.addEventListener('keydown', (event) => {
    if (event.key === 'Tab' || event.key === 'Backspace' || event.key === 'Delete') {
        event.preventDefault();
        return;
    }
});

typingInput.addEventListener('input', (event) => {
    const char = event.data || typingInput.value.slice(-1);
    if (char && char.length === 1) {
        handleKeyPress(char);
    }
    typingInput.value = '';
});

typingInput.addEventListener('paste', (event) => event.preventDefault());

document.getElementById('result-btn').addEventListener('click', closeModal);

updateStats();
</script>
