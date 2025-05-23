
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
            background-color: #ccffcc;
        }
        .incorrect {
            background-color: #ffcccc;
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

<p>Paranda parempoolses tekstikastis olev tekst, et see vastaks vasakpoolsele originaaltekstile. Esimest viga näidatakse punase värviga. Sul on aega 60 sekundit.</p>

<form id="task-form">
    <table id="text-correction-table">
        <thead>
        <tr><th>#</th><th>Originaaltekst</th><th>Parandatav tekst</th></tr>
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

    // Text sets
    const textSets = [
        {
            original: `8.2 Iteratsiooni Lõpetamine (Nädala lõpus)

Iteratsiooni viimasel päeval keskenduvad Mart, Liina ja Peeter sellele, et tagada nädala jooksul arendatud kasutajalugude täielik valmimine ja vastavus kokkulepitule.

1.  Viimased Lihvid ja Kontroll:
    Viimane arenduspaar (oletame, et Mart ja Peeter) lõpetavad viimase poolelioleva tehnilise ülesande mõne iteratsiooniks valitud loo jaoks.
    Nad kontrollivad veelkord, et kõik testid (ühiktestid bun test abil ja Playwrightiga kirjutatud aktsepteerimistestid) läbivad nii nende kohalikes masinates kui ka viimases GitHub Actionsi jooksus main harus.
    Nad vaatavad üle koodi, et veenduda selle vastavuses kokkulepitud kodeerimisstandarditele (Prettier/ESLint on ideaalis automaatselt rakendatud).

2.  Kasutajalugude Ülevaatus Ora.pm-is:
    Mart, Liina ja Peeter avavad projektitahvli Ora.pm-is ja vaatavad üle need lood, mis olid selleks iteratsiooniks valitud (need, mille punktide summa oli 8).
    Nad märgivad kõik loodud tehnilised ülesanded nende lugude all lõpetatuks, kui need on tõesti valmis ja vastav kood on main harusse liidetud ja testitud.

3.  Aktsepteerimiskoosolek Annaga (Demo ja Tagasiside):
    Peeter planeerib lühikese (nt 30 min) Zoomi koosoleku kogu meeskonnaga, sealhulgas Annaga.
    Liina jagab oma ekraani ja demonstreerib valminud funktsionaalsust. Ta näitab, kuidas saab nüüd rakenduse kaudu (isegi kui see on veel väga lihtsa kasutajaliidesega või ainult API endpointidena) lisada uue ülesande ja seejärel näha lisatud ülesannete nimekirja.
    Anna jälgib demo ja võrdleb nähtut Ora.pm-is olevate kasutajalugude ja nende aktsepteerimistingimustega. Ta võib paluda Liinal proovida mõnda konkreetset stsenaariumi (nt lisada ülesanne, mille tekstis on erimärke).
    Kui funktsionaalsus vastab ootustele ja aktsepteerimistingimused on täidetud, annab Anna oma heakskiidu. Ta märgib vastavad lood Ora.pm-is "Aktsepteeritud" staatusesse.
    Kui Anna märkab midagi, mis ei vasta ootustele või on viga, arutab meeskond kiirelt:
    Selles stsenaariumis oletame, et mõlemad lood ("Lisa ülesanne" ja "Näita ülesandeid") vastasid tingimustele ja Anna aktsepteeris need.

8.3 Iteratsiooni Retrospektiiv

Vahetult pärast edukat demo ja aktsepteerimist viib meeskond (Mart, Liina, Peeter ja soovi korral ka Anna protsessi osas) läbi lühikese retrospektiivi Zoomis.

Eesmärk: Õppida lõppenud nädalast ja leppida kokku parendustes järgmiseks iteratsiooniks.
Eesmärk: Õppida lõppenud nädalast ja leppida kokku parendustes järgmiseks iteratsiooniks.

8.4 Väljalase ("Laivi Laskmine")

Kuna Anna aktsepteeris valminud funktsionaalsuse ja see moodustab osa kokkulepitud MVP-st, soovib meeskond selle võimalikult kiiresti reaalsesse kasutuskeskkonda viia.

1.  Automatiseeritud Väljalase GitHub Actionsiga:
    Peeter ja Mart olid Iteratsioon 0 ajal juba ette valmistanud GitHub Actionsi töövoo nii, et see mitte ainult ei testi koodi, vaid ka ehitab rakenduse (nt bun build ./src/index.ts --outfile ./dist/bundle.js) ja loob tootmiskõlbuliku artefakti (nt Docker image'i või lihtsalt pakendatud JS faili koos node_modules vajalike osadega).
    Nad lisavad/konfigureerivad töövoos sammu, mis käivitub ainult siis, kui kood liidetakse main harusse (või luuakse spetsiifiline Git tag). See samm kasutab GitHub Secrets'isse salvestatud autentimisinfot (nt API võtit valitud pilveplatvormi jaoks) ja käivitab käsu uue versiooni paigaldamiseks (nt fly deploy või render deploy).
    Mart teeb viimase koodi liitmise (või loob tag'i), mis käivitab väljalaske töövoo GitHub Actionsis.

2.  Kontroll Pärast Väljalaset:
    Mart ja Peeter jälgivad GitHub Actionsi logisid, et veenduda väljalaske õnnestumises.
    Kui Actions näitab edu, võtab Liina rakenduse avaliku URL-i ja teostab kiired suitsutestid ("smoke tests"): kas leht avaneb? Kas ta saab lisada uue ülesande? Kas ta näeb lisatud ülesannet nimekirjas?
    Liina saadab toimiva URL-i Annale Google Chatis. Anna proovib samuti rakendust oma arvutis ja kinnitab, et see töötab tema jaoks.

3.  Kiiruse Arvutamine ja Järgmise Iteratsiooni Ettevalmistus:
    Peeter märgib üles, et meeskond sai selle iteratsiooni jooksul valmis ja Anna poolt aktsepteeritud 8 punkti väärtuses kasutajalugusid. See 8 punkti on nüüd nende teadaolev kiirus (Velocity).
    Seda kiirust kasutatakse järgmise esmaspäeva Iteration Planning koosolekul sisendina, et valida järgmise nädala jaoks realistlik kogus tööd (tõenäoliselt jälle umbes 8 punkti väärtuses lugusid Anna prioriteetide järjekorras).`,
            corrupted: `8.2 Iteratsiooni Lõpetamine (Nädala lõpus)

Iteratsiooni viimasel päeval keskenduvad Mart, Liina ja Peeter sellele, et tagada nädala jooksul arendatud kasutajalugude täielik valmimine ja vastavus kokkulepitule.

1.  Viimased Lihvid ja Kontroll:
    *   Viimane arenduspaar (oletame, et Mart ja Peeter) lõpetavad viimase poolelioleva tehnilise ülesande mõne iteratsiooniks valitud loo jaoks.
    *   Nad kontrollivad veelkord, et kõik testid (ühiktestid bun test abil ja Playwrightiga kirjutatud aktsepteerimistestid) läbivad nii nende kohalikes masinates kui ka viimases GitHub Actionsi jooksus main harus.
    *   Nad vaatavad üle koodi, et veenduda selle vastavuses kokkulepitud kodeerimisstandarditele (Prettier/ESLint on ideaalis automaatselt rakendatud).

2.  Kasutajalugude Ülevaatus Ora.pm-is:
    *   Mart, Liina ja Peeter avavad projektitahvli Ora.pm-is ja vaatavad üle need lood, mis olid selleks iteratsiooniks valitud (need, mille punktide summa oli 8).
    *   Nad märgivad kõik loodud tehnilised ülesanded nende lugude all lõpetatuks, kui need on tõesti valmis ja vastav kood on main harusse liidetud ja testitud.

3.  Aktsepteerimiskoosolek Annaga (Demo ja Tagasiside):
    *   Peeter planeerib lühikese (nt 30 min) Zoomi koosoleku kogu meeskonnaga, sealhulgas Annaga.
    *   Liina jagab oma ekraani ja demonstreerib valminud funktsionaalsust. Ta näitab, kuidas saab nüüd rakenduse kaudu (isegi kui see on veel väga lihtsa kasutajaliidesega või ainult API endpointidena) lisada uue ülesande ja seejärel näha lisatud ülesannete nimekirja.
    *   Anna jälgib demo ja võrdleb nähtut Ora.pm-is olevate kasutajalugude ja nende aktsepteerimistingimustega. Ta võib paluda Liinal proovida mõnda konkreetset stsenaariumi (nt lisada ülesanne, mille tekstis on erimärke).
    *   Kui funktsionaalsus vastab ootustele ja aktsepteerimistingimused on täidetud, annab Anna oma heakskiidu. Ta märgib vastavad lood Ora.pm-is "Aktsepteeritud" staatusesse.
    *   Kui Anna märkab midagi, mis ei vasta ootustele või on viga, arutab meeskond kiirelt:
    *   Selles stsenaariumis oletame, et mõlemad lood ("Lisa ülesanne" ja "Näita ülesandeid") vastasid tingimustele ja Anna aktsepteeris need.

8.3 Iteratsiooni Retrospektiiv

Vahetult pärast edukat demo ja aktsepteerimist viib meeskond (Mart, Liina, Peeter ja soovi korral ka Anna protsessi osas) läbi lühikese retrospektiivi Zoomis.

*   Eesmärk: Õppida lõppenud nädalast ja leppida kokku parendustes järgmiseks iteratsiooniks.
*   Eesmärk: Õppida lõppenud nädalast ja leppida kokku parendustes järgmiseks iteratsiooniks.

8.4 Väljalase ("Laivi Laskmine")

Kuna Anna aktsepteeris valminud funktsionaalsuse ja see moodustab osa kokkulepitud MVP-st, soovib meeskond selle võimalikult kiiresti reaalsesse kasutuskeskkonda viia.

1.  Automatiseeritud Väljalase GitHub Actionsiga:
    *   Peeter ja Mart olid Iteratsioon 0 ajal juba ette valmistanud GitHub Actionsi töövoo nii, et see mitte ainult ei testi koodi, vaid ka ehitab rakenduse (nt bun build ./src/index.ts --outfile ./dist/bundle.js) ja loob tootmiskõlbuliku artefakti (nt Docker image'i või lihtsalt pakendatud JS faili koos node_modules vajalike osadega).
    *   Nad lisavad/konfigureerivad töövoos sammu, mis käivitub ainult siis, kui kood liidetakse main harusse (või luuakse spetsiifiline Git tag). See samm kasutab GitHub Secrets'isse salvestatud autentimisinfot (nt API võtit valitud pilveplatvormi jaoks) ja käivitab käsu uue versiooni paigaldamiseks (nt fly deploy või render deploy).
    *   Mart teeb viimase koodi liitmise (või loob tag'i), mis käivitab väljalaske töövoo GitHub Actionsis.

2.  Kontroll Pärast Väljalaset:
    *   Mart ja Peeter jälgivad GitHub Actionsi logisid, et veenduda väljalaske õnnestumises.
    *   Kui Actions näitab edu, võtab Liina rakenduse avaliku URL-i ja teostab kiired suitsutestid ("smoke tests"): kas leht avaneb? Kas ta saab lisada uue ülesande? Kas ta näeb lisatud ülesannet nimekirjas?
    *   Liina saadab toimiva URL-i Annale Google Chatis. Anna proovib samuti rakendust oma arvutis ja kinnitab, et see töötab tema jaoks.

3.  Kiiruse Arvutamine ja Järgmise Iteratsiooni Ettevalmistus:
    *   Peeter märgib üles, et meeskond sai selle iteratsiooni jooksul valmis ja Anna poolt aktsepteeritud 8 punkti väärtuses kasutajalugusid. See 8 punkti on nüüd nende teadaolev kiirus (Velocity).
    *   Seda kiirust kasutatakse järgmise esmaspäeva Iteration Planning koosolekul sisendina, et valida järgmise nädala jaoks realistlik kogus tööd (tõenäoliselt jälle umbes 8 punkti väärtuses lugusid Anna prioriteetide järjekorras).`
        }
    ];

    // Initialize
    function initializeExercise() {
        const randomIndex = Math.floor(Math.random() * textSets.length);
        const selectedText = textSets[randomIndex];

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
        const progressPercent = Math.floor((correctChars / totalChars) * 100);
        progressDisplay.textContent = `Õigeid märke: ${correctChars}/${totalChars} (${progressPercent}%)`;

        return firstErrorPos === -1 && currentText.length === originalText.length;
    }

    // Efficiently update the original text display
    function updateOriginalTextDisplay(firstErrorPos) {
        if (firstErrorPos === -1) {
            // All text is correct
            originalTextDiv.innerHTML = `<span class="correct-text">${escapeHtml(originalText)}</span>`;
        } else {
            const correctPart = originalText.substring(0, firstErrorPos);
            const errorChar = originalText[firstErrorPos] || '';
            const remainingPart = originalText.substring(firstErrorPos + 1);

            let html = '';
            if (correctPart) {
                html += `<span class="correct-text">${escapeHtml(correctPart)}</span>`;
            }
            if (errorChar) {
                html += `<span class="first-error">${escapeHtml(errorChar)}</span>`;
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
                const elapsed = (Date.now() - startTime) / 1000;
                timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;

                fetch('save_result.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        elapsed: elapsed.toFixed(2),
                        exercise_id: '004'
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
            alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
            location.reload();
        }
    }

    // Mouse selection prevention (kept from original)
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
