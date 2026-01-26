
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
        .original-text {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
            height: 100%;
            overflow: auto;
            padding: 8px;
            box-sizing: border-box;
            color: #333;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            cursor: default;
        }
        .correction {
            padding: 0px !important;
        }
        .editable-text {
            width: 100%;
            height: 100%;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
            padding: 8px;
            box-sizing: border-box;
            overflow: auto;
            color: #333;
            outline: none;
            cursor: text;
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

<p>Kustuta parempoolses tekstikastis liigsed sõnad. Vasakul näed õiget teksti (kuidas peab olema). Paremal on parandatav tekst - roheline on õige, punane sõna tuleb kustutada. Sul on aega 60 sekundit.</p>

<form id="task-form">
    <table id="text-correction-table">
        <thead>
        <tr><th>#</th><th>Õige tekst</th><th>Parandatav tekst (punane = kustuta)</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td><div id="original-text" class="original-text"></div></td>
            <td class="correction">
                <div id="editable-text" class="editable-text" contenteditable="true"></div>
            </td>
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
    const editableText = document.getElementById('editable-text');
    const timerDisplay = document.getElementById('timer');
    const progressDisplay = document.getElementById('progress');

    let startTime = null;
    let timerInterval = null;
    let originalText = '';
    let corruptedText = ''; // Algne rikutud tekst (ei muutu kunagi)
    let lastFirstErrorPos = -1;
    let debounceTimeout = null;
    let currentProgressPercent = 0;
    let sessionTracker = null;

    // Text pairs: original and corrupted version
    // NB: Extra words must NEVER be at the beginning of a sentence (capitalization issue)
    const textPairs = [
        {
            original: "Klaviatuuri ja hiire koostöö on tööl oluline. Mõlemad tööriistad täiendavad teineteist. Hiir sobib täpseteks valikuteks ja navigeerimiseks. Topeltklõps valib terve sõna kiirelt. Klaviatuur on kiire teksti sisestamiseks. Koos kasutades saavutad parima tulemuse. Hiire klõps valib menüüst käsu. Klaviatuur sisestab andmed kiiresti. Mõlema valdamine tõstab efektiivsust. Tasakaalustatud kasutamine on võti. Päevatöös mõlemad on hädavajalikud. Kogenud kasutaja lülitub nende vahel sujuvalt. Õige töövahendi valik säästab aega. Hiir võimaldab graafilist suhtlust arvutiga. Klaviatuur pakub kiiret tekstisisestust. Mõlemad seadmed on ergonoomiliselt disainitud. Hiire nupud täidavad erinevaid funktsioone. Klaviatuuri paigutus järgib standardeid. Juhtmevabad versioonid annavad vabadust. Õige asend hoiab tervise korras.",
            corrupted: "Klaviatuuri ja hiire hea koostöö on tööl väga oluline. Mõlemad head tööriistad täiendavad teineteist täielikult. Hiir sobib väga täpseteks valikuteks ja mugavaks navigeerimiseks. Topeltklõps valib hiire abil terve sõna väga kiirelt. Klaviatuur on eriti kiire teksti sisestamiseks. Koos kasutades saavutad kõige parima tulemuse. Hiire klõps valib menüüst sobiva käsu. Klaviatuur sisestab kõik andmed väga kiiresti. Mõlema hea valdamine tõstab efektiivsust märgatavalt. Tasakaalustatud kasutamine on alati võti. Päevatöös mõlemad on igapäevaselt väga hädavajalikud. Kogenud kasutaja lülitub nende vahel väga sujuvalt. Õige töövahendi valik säästab palju aega. Hiir võimaldab graafilist suhtlust arvutiga mugavalt. Klaviatuur pakub kiiret tekstisisestust alati. Mõlemad seadmed on ergonoomiliselt hästi disainitud. Hiire nupud täidavad erinevaid olulisi funktsioone. Klaviatuuri paigutus järgib rahvusvahelisi standardeid. Juhtmevabad versioonid annavad suurt vabadust. Õige asend hoiab tervise alati korras."
        },
        {
            original: "Teksti redigeerimisel töötavad klaviatuur ja hiir koos. Hiir liigutab kursorit täpselt õigesse kohta. Klaviatuur sisestab või muudab teksti. Hiire valik märgib teksti osa. Topeltklõpsuga aktiveerid sõna hetkega. Klaviatuuri klahvid vormindavad seda. Hiire rullimisega liigud dokumendis. Klaviatuuri nooled täpsustavad positsiooni. Mõlemad on vajalikud tõhusaks töötamiseks. Hiire lohistamine liigutab teksti lõike. Klaviatuuriga saab teksti struktuuri muuta. Koostoimes sünnib kiire ja täpne töö. Teksti joondamine nõuab mõlemat tööriista. Hiir valib lõigud ühe liigutusega. Klaviatuur muudab taandeid ja vahesid. Tabelite loomine on lihtsam koos. Hiir joonistab tabeli piirjooned. Klaviatuur täidab lahtrid sisuga. Päised ja jalused vajavad täpset paigutust. Korrektne dokument nõuab hoolikust.",
            corrupted: "Teksti redigeerimisel töötavad klaviatuur ja hiir hästi koos. Hiir liigutab kursorit väga täpselt õigesse kohta. Klaviatuur sisestab või muudab kogu teksti. Hiire kiire valik märgib teksti osa. Topeltklõpsuga aktiveerid sõna väga kiiresti hetkega. Klaviatuuri erinevad klahvid vormindavad seda. Hiire rullimisega liigud kiiresti dokumendis. Klaviatuuri nooled täpsustavad positsiooni täpselt. Mõlemad on väga vajalikud tõhusaks töötamiseks. Hiire lohistamine liigutab kiiresti teksti lõike. Klaviatuuriga saab teksti struktuuri täielikult muuta. Koostoimes sünnib väga kiire ja täpne töö. Teksti joondamine nõuab kindlasti mõlemat tööriista. Hiir valib lõigud ühe kiire liigutusega. Klaviatuur muudab taandeid ja vahesid täpselt. Tabelite loomine on lihtsam koos tehes. Hiir joonistab tabeli piirjooned täpselt. Klaviatuur täidab lahtrid kogu sisuga. Päised ja jalused vajavad väga täpset paigutust. Korrektne dokument nõuab suurt hoolikust."
        },
        {
            original: "Programmeerijad kombineerivad klaviatuuri ja hiirt. Hiir navigeerib koodi struktuuris. Klaviatuur kirjutab koodi read. Hiire klõps avab faile ja funktsioone. Topeltklõps valib muutuja või funktsiooni nime. Klaviatuuri kiirklahvid täidavad käske. Hiir seab murrangupunkte silumiseks. Klaviatuur juhib programmi täitmist. Mõlema oskuslik kasutus on oluline. Koostöö suurendab arenduskiirust. Hiir valib ridu refaktoreerimiseks. Klaviatuur teeb koodimuudatusi automaatselt. Mõlema kombinatsioon teeb arenduse produktiivseks. Versioonihaldus nõuab täpseid käske. Hiir võrdleb faile visuaalselt. Klaviatuur sisestab muudatuste kirjeldusi. Testimine vajab mõlemat tööriista. Hiir käivitab teste nupuvajutusega. Klaviatuur kirjutab testide koodi. Dokumentatsioon sünnib koostöös.",
            corrupted: "Programmeerijad kombineerivad klaviatuuri ja hiirt hästi. Hiir navigeerib koodi keerulises struktuuris. Klaviatuur kirjutab kõik koodi read. Hiire klõps avab kiiresti faile ja erinevaid funktsioone. Topeltklõps valib hiire abil muutuja või funktsiooni täpse nime. Klaviatuuri kiirklahvid täidavad kõik käske. Hiir seab murrangupunkte täpselt silumiseks. Klaviatuur juhib programmi täitmist täpselt. Mõlema väga oskuslik kasutus on oluline. Koostöö suurendab arenduskiirust märgatavalt. Hiir valib täpselt ridu refaktoreerimiseks. Klaviatuur teeb kõik koodimuudatusi automaatselt. Mõlema hea kombinatsioon teeb arenduse väga produktiivseks. Versioonihaldus nõuab väga täpseid käske. Hiir võrdleb faile visuaalselt kiiresti. Klaviatuur sisestab muudatuste kirjeldusi täpselt. Testimine vajab kindlasti mõlemat tööriista. Hiir käivitab teste lihtsa nupuvajutusega. Klaviatuur kirjutab testide koodi kiiresti. Dokumentatsioon sünnib heas koostöös."
        },
        {
            original: "Andmesisestuses on mõlemad tööriistad kasulikud. Hiir valib väljad ja menüüd. Klaviatuur sisestab numbrid ja teksti. Hiire lohistamine kopeerib andmeid. Topeltklõps aktiveerib lahtri sisu kiiresti. Klaviatuuri Tab liigub väljade vahel. Hiire topeltklõps avab kirjeid. Klaviatuuri Enter kinnitab sisestuse. Mõlema kombinatsioon kiirendab tööd. Efektiivsus tuleneb koostööst. Hiir valib andmeväljasid kontekstist. Klaviatuur täidab vormid numbritega. Koostoimes toimub andmesisestus sujuvalt. Andmebaasid vajavad täpset sisestust. Hiir navigeerib kirjete vahel. Klaviatuur filtreerib tulemusi. Otsingud kombineerivad mõlemat. Hiir valib otsinguvälja. Klaviatuur sisestab märksõnad. Tulemuste sorteerimine on kiire.",
            corrupted: "Andmesisestuses on mõlemad head tööriistad väga kasulikud. Hiir valib täpselt väljad ja erinevad menüüd. Klaviatuur sisestab kõik numbrid ja teksti. Hiire lohistamine kopeerib kiiresti andmeid. Topeltklõps aktiveerib hiire abil lahtri sisu väga kiiresti. Klaviatuuri Tab liigub väljade vahel kiiresti. Hiire topeltklõps avab kiiresti kirjeid. Klaviatuuri Enter kinnitab sisestuse täielikult. Mõlema hea kombinatsioon kiirendab tööd märgatavalt. Efektiivsus tuleneb heast koostööst. Hiir valib täpselt andmeväljasid kontekstist. Klaviatuur täidab vormid kõikide numbritega. Koostoimes toimub andmesisestus väga sujuvalt. Andmebaasid vajavad väga täpset sisestust. Hiir navigeerib kirjete vahel kiiresti. Klaviatuur filtreerib tulemusi täpselt. Otsingud kombineerivad kindlasti mõlemat. Hiir valib otsinguvälja kiiresti. Klaviatuur sisestab märksõnad täpselt. Tulemuste sorteerimine on väga kiire."
        },
        {
            original: "Tabelarvutuses on hiir asendamatu. Hiir valib lahtrid ja vahemikud. Klaviatuur sisestab valemid ja väärtused. Hiire lohistamine kopeerib lahtrite sisu. Topeltklõps avab lahtri redigeerimiseks. Klaviatuuri nooled liiguvad lahtrite vahel. Hiir muudab veergude laiust. Klaviatuur kinnitab sisestused kiiresti. Mõlemad tööriistad on vajalikud. Efektiivne töö nõuab mõlemat. Hiir valib diagrammi elemendid. Klaviatuur muudab andmeid otse. Koostöö tagab kiire tulemuse. Diagrammid visualiseerivad andmeid. Hiir valib diagrammi tüübi. Klaviatuur sisestab pealkirjad. Värvid ja stiilid vajavad hiirt. Andmeseeriad lisatakse klõpsuga. Klaviatuur muudab väärtusi täpselt. Esitluskõlbulik tabel sünnib koostöös.",
            corrupted: "Tabelarvutuses on hiir täiesti asendamatu. Hiir valib täpselt lahtrid ja erinevad vahemikud. Klaviatuur sisestab kõik valemid ja väärtused. Hiire lohistamine kopeerib kiiresti lahtrite sisu. Topeltklõps avab kohe lahtri redigeerimiseks. Klaviatuuri nooled liiguvad lahtrite vahel kiiresti. Hiir muudab veergude laiust täpselt. Klaviatuur kinnitab sisestused väga kiiresti. Mõlemad head tööriistad on vajalikud. Efektiivne töö nõuab kindlasti mõlemat. Hiir valib täpselt diagrammi elemendid. Klaviatuur muudab andmeid otse kiiresti. Koostöö tagab väga kiire tulemuse. Diagrammid visualiseerivad andmeid selgelt. Hiir valib diagrammi sobiva tüübi. Klaviatuur sisestab pealkirjad täpselt. Värvid ja stiilid vajavad kindlasti hiirt. Andmeseeriad lisatakse lihtsa klõpsuga. Klaviatuur muudab väärtusi väga täpselt. Esitluskõlbulik tabel sünnib heas koostöös."
        },
        {
            original: "Veebilehitseja kasutamisel on mõlemad abiks. Hiir klõpsab linkidel ja nuppudel. Klaviatuur sisestab veebiaadresse ja otsinguid. Hiire rullimine kerib lehte alla. Topeltklõps valib sõnu veebilehel. Klaviatuuri klahvid avavad uusi vahelehti. Hiir lohistab faile allalaadimiseks. Klaviatuur täidab vorme andmetega. Mõlemad on veebis surfamisel vajalikud. Kiire navigeerimine nõuab mõlemat. Hiir avab kontekstimenüüd. Klaviatuur kasutab otseteid brauseris. Oskuslik kasutamine säästab aega. Järjehoidjad vajavad mõlemat tööriista. Hiir lohistab linke kaustadesse. Klaviatuur nimetab järjehoidjaid. Ajalugu sirvitakse mõlemaga. Hiir valib kuupäevi. Klaviatuur otsib märksõnu ajaloost. Privaatsus nõuab teadlikku kasutamist.",
            corrupted: "Veebilehitseja kasutamisel on mõlemad väga abiks. Hiir klõpsab linkidel ja erinevatel nuppudel. Klaviatuur sisestab kõik veebiaadresse ja otsinguid. Hiire rullimine kerib lehte kiiresti alla. Topeltklõps valib sõnu veebilehel täpselt. Klaviatuuri klahvid avavad uusi vahelehti kiiresti. Hiir lohistab faile mugavalt allalaadimiseks. Klaviatuur täidab vorme kõigi andmetega. Mõlemad on veebis surfamisel väga vajalikud. Kiire navigeerimine nõuab kindlasti mõlemat. Hiir avab erinevaid kontekstimenüüd. Klaviatuur kasutab otseteid brauseris kiiresti. Oskuslik kasutamine säästab palju aega. Järjehoidjad vajavad kindlasti mõlemat tööriista. Hiir lohistab linke kaustadesse mugavalt. Klaviatuur nimetab järjehoidjaid täpselt. Ajalugu sirvitakse mõlemaga kiiresti. Hiir valib kuupäevi täpselt. Klaviatuur otsib märksõnu ajaloost kiiresti. Privaatsus nõuab väga teadlikku kasutamist."
        }
    ];

    // Initialize
    function initializeExercise() {
        const randomIndex = Math.floor(Math.random() * textPairs.length);
        const selectedText = textPairs[randomIndex];

        originalText = selectedText.original;
        corruptedText = selectedText.corrupted;

        // Set initial text content (without triggering input event)
        editableText.textContent = selectedText.corrupted;

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

        // Always update the highlight layer (shows current text with colors)
        updateOriginalTextDisplay(firstErrorPos);
        lastFirstErrorPos = firstErrorPos;

        // Update progress
        const totalChars = originalText.length;
        currentProgressPercent = Math.floor((correctChars / totalChars) * 100);
        progressDisplay.textContent = `Õigeid märke: ${correctChars}/${totalChars} (${currentProgressPercent}%)`;

        return firstErrorPos === -1 && currentText.length === originalText.length;
    }

    // Save and restore cursor position for contenteditable
    function saveCursorPosition() {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(editableText);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            return preCaretRange.toString().length;
        }
        return 0;
    }

    function restoreCursorPosition(position) {
        const selection = window.getSelection();
        const range = document.createRange();

        let charCount = 0;
        let foundNode = null;
        let foundOffset = 0;

        function traverseNodes(node) {
            if (foundNode) return;
            if (node.nodeType === Node.TEXT_NODE) {
                if (charCount + node.length >= position) {
                    foundNode = node;
                    foundOffset = position - charCount;
                    return;
                }
                charCount += node.length;
            } else {
                for (let child of node.childNodes) {
                    traverseNodes(child);
                }
            }
        }

        traverseNodes(editableText);

        if (foundNode) {
            range.setStart(foundNode, foundOffset);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }

    // Update displays:
    // - Left column (originalTextDiv): shows target text statically
    // - Right column (editableText): shows editable text with highlighting
    function updateOriginalTextDisplay(firstErrorPos) {
        // Left column: always show the target/correct text (static)
        originalTextDiv.innerHTML = escapeHtml(originalText);

        // Right column: get current text and show with colors
        const currentText = editableText.textContent;

        // Save cursor position before updating HTML
        const cursorPos = saveCursorPosition();

        if (firstErrorPos === -1) {
            // All text is correct - show all green
            editableText.innerHTML = `<span class="correct-text">${escapeHtml(currentText)}</span>`;
        } else {
            // Show: green (correct part) + red (word to delete) + normal (rest)
            const correctPart = currentText.substring(0, firstErrorPos);

            // Find the end of the error word
            let errorEndPos = firstErrorPos;

            // If error starts with a space, include the space and find the following word
            if (currentText[firstErrorPos] === ' ') {
                errorEndPos++; // Include the leading space
                // Now find the end of the word after the space
                while (errorEndPos < currentText.length) {
                    const char = currentText[errorEndPos];
                    if (char === ' ' || char === '.' || char === ',' || char === '!' || char === '?') {
                        break;
                    }
                    errorEndPos++;
                }
            } else {
                // Error starts with a letter - find end of this word
                while (errorEndPos < currentText.length) {
                    const char = currentText[errorEndPos];
                    if (char === ' ' || char === '.' || char === ',' || char === '!' || char === '?') {
                        errorEndPos++; // Include the trailing space/punctuation
                        break;
                    }
                    errorEndPos++;
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

            editableText.innerHTML = html;
        }

        // Restore cursor position after updating HTML
        restoreCursorPosition(cursorPos);
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
            const currentText = editableText.textContent;
            const isComplete = updateDisplay(currentText);

            if (isComplete) {
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

    // Event listeners
    editableText.addEventListener('input', handleInput);

    // Prevent copying from the original text column
    originalTextDiv.addEventListener('copy', function(e) {
        e.preventDefault();
    });
    originalTextDiv.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Initialize the exercise
    initializeExercise();
</script>
