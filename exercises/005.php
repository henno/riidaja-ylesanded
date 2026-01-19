
    <style>
        #text-correction-table {
            width: 100%;
            max-width: 1250px;
            margin: 20px auto;
            border-collapse: collapse;
        }
        #text-correction-table th, #text-correction-table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }
        #text-correction-table th:first-child {
            width: 40px;
        }
        #text-correction-table th:nth-child(2),
        #text-correction-table th:nth-child(3) {
            width: 50%;
        }
        #text-correction-table th {
            background-color: #f2f2f2;
        }
        #text-correction-table td {
            position: relative;
            height: 500px;
        }
        textarea {
            width: 100%;
            height: 100%;
            border: none;
            resize: none;
            padding: 8px;
            box-sizing: border-box;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        .correct {
            background-color: #ffffff;
        }
        .incorrect {
            background-color: #ffffff;
        }
        .original-text {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
            height: 100%;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            pointer-events: none;
            padding: 8px;
            box-sizing: border-box;
        }
        .correction {
            padding: 0px !important;
        }
        .correct-text {
            background-color: #ccffcc;
        }
        .first-error {
            background-color: #ffcccc;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { background-color: #ffcccc; }
            50% { background-color: #ff9999; }
            100% { background-color: #ffcccc; }
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
    </style>

<p>Kustuta parempoolses tekstikastis liigsed sõnad. Vasakul näed oma praegust teksti - punane sõna näitab, mida tuleb kustutada. Sul on aega 60 sekundit.</p>

<form id="task-form">
    <table id="text-correction-table">
        <thead>
        <tr><th>#</th><th>Sinu tekst (punane = kustuta)</th><th>Parandatav tekst</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td><div id="original-text" class="original-text"></div></td>
            <td class="correction"><textarea id="correction-textarea" class="incorrect"></textarea></td>
        </tr>
        </tbody>
    </table>
    <div id="progress-info">
        <div id="timer">Kulunud aeg: 0.00 s</div>
        <div id="progress">Õigeid märke: 0/0 (0%)</div>
    </div>
</form>

<script>
    const originalTextDiv = document.getElementById('original-text');
    const correctionTextarea = document.getElementById('correction-textarea');
    const timerDisplay = document.getElementById('timer');
    const progressDisplay = document.getElementById('progress');

    let startTime = null;
    let timerInterval = null;
    let originalText = '';
    let lastFirstErrorPos = -1;
    let debounceTimeout = null;
    let currentProgressPercent = 0;
    let sessionTracker = null;

    // Text pairs: original and corrupted version
    // NB: Extra words must NEVER be at the beginning of a sentence (capitalization issue)
    const textPairs = [
        {
            original: "Klaviatuuri ja hiire koostöö on tööl oluline. Mõlemad tööriistad täiendavad teineteist. Hiir sobib täpseteks valikuteks ja navigeerimiseks. Topeltklõps valib terve sõna kiirelt. Klaviatuur on kiire teksti sisestamiseks. Koos kasutades saavutad parima tulemuse. Hiire klõps valib menüüst käsu. Klaviatuur sisestab andmed kiiresti. Mõlema valdamine tõstab efektiivsust. Tasakaalustatud kasutamine on võti. Päevatöös mõlemad on hädavajalikud. Kogenud kasutaja lülitub nende vahel sujuvalt. Õige töövahendi valik säästab aega.",
            corrupted: "Klaviatuuri ja hiire hea koostöö on tööl väga oluline. Mõlemad head tööriistad täiendavad teineteist täielikult. Hiir sobib väga täpseteks valikuteks ja mugavaks navigeerimiseks. Topeltklõps valib hiire abil terve sõna väga kiirelt. Klaviatuur on eriti kiire teksti sisestamiseks. Koos kasutades saavutad kõige parima tulemuse. Hiire klõps valib menüüst sobiva käsu. Klaviatuur sisestab kõik andmed väga kiiresti. Mõlema hea valdamine tõstab efektiivsust märgatavalt. Tasakaalustatud kasutamine on alati võti. Päevatöös mõlemad on igapäevaselt väga hädavajalikud. Kogenud kasutaja lülitub nende vahel väga sujuvalt. Õige töövahendi valik säästab palju aega."
        },
        {
            original: "Teksti redigeerimisel töötavad klaviatuur ja hiir koos. Hiir liigutab kursorit täpselt õigesse kohta. Klaviatuur sisestab või muudab teksti. Hiire valik märgib teksti osa. Topeltklõpsuga aktiveerid sõna hetkega. Klaviatuuri klahvid vormindavad seda. Ctrl+klahvid kombineeritakse hiire tegevustega. Hiire rullimisega liigud dokumendis. Klaviatuuri nooled täpsustavad positsiooni. Mõlemad on vajalikud tõhusaks töötamiseks. Hiire lohistamine liigutab teksti lõike. Klaviatuuriga saab teksti struktuuri muuta. Koostoimes sünnib kiire ja täpne töö.",
            corrupted: "Teksti redigeerimisel töötavad klaviatuur ja hiir hästi koos. Hiir liigutab kursorit väga täpselt õigesse kohta. Klaviatuur sisestab või muudab kogu teksti. Hiire kiire valik märgib teksti osa. Topeltklõpsuga aktiveerid sõna väga kiiresti hetkega. Klaviatuuri erinevad klahvid vormindavad seda. Ctrl+klahvid kombineeritakse hiire kõikide tegevustega. Hiire rullimisega liigud kiiresti dokumendis. Klaviatuuri nooled täpsustavad positsiooni täpselt. Mõlemad on väga vajalikud tõhusaks töötamiseks. Hiire lohistamine liigutab kiiresti teksti lõike. Klaviatuuriga saab teksti struktuuri täielikult muuta. Koostoimes sünnib väga kiire ja täpne töö."
        },
        {
            original: "Programmeerijad kombineerivad klaviatuuri ja hiirt. Hiir navigeerib koodi struktuuris. Klaviatuur kirjutab koodi read. Hiire klõps avab faile ja funktsioone. Topeltklõps valib muutuja või funktsiooni nime. Klaviatuuri kiirklahvid täidavad käske. Debug režiimis hiir seab murrangupunkte. Klaviatuur juhib programmi täitmist. Mõlema oskuslik kasutus on oluline. Koostöö suurendab arenduskiirust. Hiir valib ridu refaktoreerimiseks. Klaviatuur teeb koodimuudatusi automaatselt. Mõlema kombinatsioon teeb arenduse produktiivseks.",
            corrupted: "Programmeerijad kombineerivad klaviatuuri ja hiirt hästi. Hiir navigeerib koodi keerulises struktuuris. Klaviatuur kirjutab kõik koodi read. Hiire klõps avab kiiresti faile ja erinevaid funktsioone. Topeltklõps valib hiire abil muutuja või funktsiooni täpse nime. Klaviatuuri kiirklahvid täidavad kõik käske. Debug režiimis hiir seab täpsed murrangupunkte. Klaviatuur juhib programmi täitmist täpselt. Mõlema väga oskuslik kasutus on oluline. Koostöö suurendab arenduskiirust märgatavalt. Hiir valib täpselt ridu refaktoreerimiseks. Klaviatuur teeb kõik koodimuudatusi automaatselt. Mõlema hea kombinatsioon teeb arenduse väga produktiivseks."
        },
        {
            original: "Andmesisestuses on mõlemad tööriistad kasulikud. Hiir valib väljad ja menüüd. Klaviatuur sisestab numbrid ja teksti. Hiire lohistamine kopeerib andmeid. Topeltklõps aktiveerib lahtri sisu kiiresti. Klaviatuuri Tab klahv liigub väljade vahel. Hiire topeltklõps avab kirjeid. Klaviatuuri Enter kinnitab sisestuse. Mõlema kombinatsioon kiirendab tööd. Efektiivsus tuleneb koostööst. Hiir valib andmeväljasid kontekstist. Klaviatuur täidab vormid numbritega. Koostoimes toimub andmesisestus sujuvalt.",
            corrupted: "Andmesisestuses on mõlemad head tööriistad väga kasulikud. Hiir valib täpselt väljad ja erinevad menüüd. Klaviatuur sisestab kõik numbrid ja teksti. Hiire lohistamine kopeerib kiiresti andmeid. Topeltklõps aktiveerib hiire abil lahtri sisu väga kiiresti. Klaviatuuri Tab klahv liigub väljade vahel kiiresti. Hiire topeltklõps avab kiiresti kirjeid. Klaviatuuri Enter kinnitab sisestuse täielikult. Mõlema hea kombinatsioon kiirendab tööd märgatavalt. Efektiivsus tuleneb heast koostööst. Hiir valib täpselt andmeväljasid kontekstist. Klaviatuur täidab vormid kõikide numbritega. Koostoimes toimub andmesisestus väga sujuvalt."
        },
        {
            original: "Dokumendi vormindamisel mõlemad aitavad. Hiir valib teksti ja objekte. Klaviatuur rakendab vormindust kiirelt. Hiire menüüst valitakse stiilid. Topeltklõps teeb sõna aktiivseks koheselt. Klaviatuuri klahvid muudavad fonti ja suurust. Hiir paigutab pilte ja tabeleid. Klaviatuur sisestab pealkirjad ja tekstid. Mõlema kasutamine annab parima tulemuse. Koostöö muudab töö sujuvaks. Hiire klõps lisab lehekülgedele elemente. Klaviatuur vormistab struktuuri kiiresti. Tasakaalustatud töö annab professionaalse tulemuse.",
            corrupted: "Dokumendi vormindamisel mõlemad head aitavad. Hiir valib täpselt teksti ja erinevad objekte. Klaviatuur rakendab vormindust väga kiirelt. Hiire menüüst valitakse sobivad stiilid. Topeltklõps teeb hiire abil sõna aktiivseks väga koheselt. Klaviatuuri klahvid muudavad fonti ja suurust täpselt. Hiir paigutab täpselt pilte ja tabeleid. Klaviatuur sisestab kõik pealkirjad ja tekstid. Mõlema hea kasutamine annab kõige parima tulemuse. Koostöö muudab töö väga sujuvaks. Hiire klõps lisab lehekülgedele erinevaid elemente. Klaviatuur vormistab struktuuri väga kiiresti. Tasakaalustatud töö annab väga professionaalse tulemuse."
        }
    ];

    // Initialize
    function initializeExercise() {
        const randomIndex = Math.floor(Math.random() * textPairs.length);
        const selectedText = textPairs[randomIndex];

        originalText = selectedText.original;
        correctionTextarea.value = selectedText.corrupted;
        correctionTextarea.dataset.original = originalText;

        // Initial display setup
        updateDisplay(selectedText.corrupted);
    }

    // Optimized function to find first difference and update display
    function updateDisplay(currentText) {
        let firstErrorPos = -1;
        let correctChars = 0;

        // Find first difference position
        const minLength = Math.min(originalText.length, currentText.length);
        for (let i = 0; i < minLength; i++) {
            if (originalText[i] === currentText[i]) {
                correctChars++;
            } else {
                firstErrorPos = i;
                break;
            }
        }

        // If all compared characters match but lengths differ
        if (firstErrorPos === -1 && originalText.length !== currentText.length) {
            firstErrorPos = minLength;
            correctChars = minLength;
        }

        // Only update DOM if the first error position changed
        if (firstErrorPos !== lastFirstErrorPos) {
            updateOriginalTextDisplay(firstErrorPos);
            lastFirstErrorPos = firstErrorPos;
        }

        // Update progress
        const totalChars = originalText.length;
        currentProgressPercent = Math.floor((correctChars / totalChars) * 100);
        progressDisplay.textContent = `Õigeid märke: ${correctChars}/${totalChars} (${currentProgressPercent}%)`;

        return firstErrorPos === -1 && currentText.length === originalText.length;
    }

    // Efficiently update the original text display - shows user's current text with extra words in red
    function updateOriginalTextDisplay(firstErrorPos) {
        const currentText = correctionTextarea.value;

        if (firstErrorPos === -1) {
            // All text is correct - show user's text in green
            originalTextDiv.innerHTML = `<span class="correct-text">${escapeHtml(currentText)}</span>`;
        } else {
            // Show user's text with the extra part highlighted in red
            const correctPart = currentText.substring(0, firstErrorPos);

            // Find the end of the error chunk in user's text (the extra word/part)
            let errorEndPos = firstErrorPos;

            // Look ahead to find where the extra part ends (next space or where texts match again)
            while (errorEndPos < currentText.length) {
                errorEndPos++;
                // Check if we've found a space or punctuation that marks word boundary
                const char = currentText[errorEndPos];
                if (char === ' ' || char === '.' || char === ',' || char === '!' || char === '?') {
                    errorEndPos++; // Include the space/punctuation
                    break;
                }
            }

            const errorPart = currentText.substring(firstErrorPos, errorEndPos);
            const remainingPart = currentText.substring(errorEndPos);

            let html = '';
            if (correctPart) {
                html += `<span class="correct-text">${escapeHtml(correctPart)}</span>`;
            }
            if (errorPart) {
                html += `<span class="first-error">${escapeHtml(errorPart)}</span>`;
            }
            if (remainingPart) {
                html += escapeHtml(remainingPart);
            }

            originalTextDiv.innerHTML = html;
        }
    }

    // Optimized HTML escaping
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/\n/g, '<br>');
    }

    // Debounced input handler
    function handleInput() {
        // Start timer on first input
        if (startTime === null) {
            startTime = Date.now();
            timerInterval = setInterval(updateTimer, 50);
            // Start session tracking
            if (window.SessionTracker && window.RIIDAJA_USER) {
                sessionTracker = new SessionTracker(
                    window.RIIDAJA_USER.email,
                    window.RIIDAJA_USER.name,
                    '005'
                );
                sessionTracker.start();
            }
        }

        // Clear previous debounce
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        // Debounce the expensive operations
        debounceTimeout = setTimeout(() => {
            const currentText = correctionTextarea.value;
            const isComplete = updateDisplay(currentText);

            if (isComplete) {
                correctionTextarea.classList.remove('incorrect');
                correctionTextarea.classList.add('correct');

                clearInterval(timerInterval);
                // Mark session as complete (success)
                if (sessionTracker) sessionTracker.complete();
                const elapsed = (Date.now() - startTime) / 1000;
                timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;

                fetch('save_result.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        elapsed: elapsed.toFixed(2),
                        exercise_id: '005'
                    })
                });

            } else {
                correctionTextarea.classList.remove('correct');
                correctionTextarea.classList.add('incorrect');
            }
        }, 16); // ~60fps debouncing
    }

    // Timer function
    function updateTimer() {
        const elapsed = (Date.now() - startTime) / 1000;
        timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
        if (elapsed >= 60) {
            clearInterval(timerInterval);
            // Mark session as complete (failed)
            if (sessionTracker) sessionTracker.complete();
            // Save failed attempt with negative elapsed time and progress percentage
            fetch('save_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    elapsed: -elapsed.toFixed(2),
                    accuracy: currentProgressPercent,
                    exercise_id: '005'
                })
            }).then(() => {
                alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
                location.reload();
            });
        }
    }

    // Mouse selection prevention
    let mouseIsDown = false;
    let lastClickPos = 0;

    correctionTextarea.addEventListener('mousedown', function(event) {
        if (event.button === 0) {
            mouseIsDown = true;
            lastClickPos = correctionTextarea.selectionStart;
        }
    });

    correctionTextarea.addEventListener('mousemove', function(event) {
        if (mouseIsDown) {
            event.preventDefault();
            const currentPos = correctionTextarea.selectionStart;
            const diff = currentPos - lastClickPos;
            correctionTextarea.scrollLeft -= diff * 8;
            lastClickPos = currentPos;
        }
    });

    document.addEventListener('mouseup', function() {
        mouseIsDown = false;
    });

    // Event listeners
    correctionTextarea.addEventListener('input', handleInput);

    // Initialize the exercise
    initializeExercise();
</script>
