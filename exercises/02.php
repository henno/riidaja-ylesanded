<style>
  #sentence-table {
    width: 100%;
    max-width: 800px;
    margin: 20px auto;
    border-collapse: collapse;
  }
  #sentence-table th, #sentence-table td {
    border: 1px solid #ddd;
    padding: 8px;
    vertical-align: top;
  }
  #sentence-table th:first-child {
    width: 40px;
  }
  #sentence-table th:nth-child(2),
  #sentence-table th:nth-child(3) {
    width: 50%;
  }
  textarea {
    width: 100%;
    height: 100px;
    padding: 5px;
    resize: vertical;
    box-sizing: border-box;
    margin: 0;
    display: block;
  }
  .correct {
    background-color: #ccffcc;
  }
  .incorrect {
    background-color: #ffcccc;
  }
  .story-cell {
    white-space: pre-line;
    user-select: none; /* Takistab teksti valimist */
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
  }
</style>

<p>Taasta iga tekstikastis lausete õige järjekord. Kui laused on õiges järjekorras, muutub tekstikasti taust roheliseks. Sul on aega 2 minut (120 sekundit).</p>
<form id="task-form">
  <table id="sentence-table">
    <thead>
      <tr><th>#</th><th>Originaaltekst</th><th>Tekstikast</th></tr>
    </thead>
    <tbody></tbody>
  </table>
  <div id="timer">Kulunud aeg: 0.00 s</div>
</form>

<script>
const tableBody    = document.querySelector('#sentence-table tbody');
const timerDisplay = document.getElementById('timer');
let startTime      = null;
let timerInterval  = null;
const textareas    = [];
const rows         = 12;

// Lühijuttude näidised
const stories = [
  "Täna on ilus päev.\nPäike paistab eredalt.\nTaevas on sinine ja pilvedeta.",
  "Koer jooksis pargis ringi.\nTa nägi oravat puu otsas.\nOrav hüppas kiiresti teisele puule.",
  "Mart läks poodi piima ostma.\nTa unustas oma rahakoti koju.\nTa pidi tühjade kätega koju minema.",
  "Lapsed mängisid rannas liivaga.\nNad ehitasid suure liivalossi.\nLained uhtusid lossi minema.",
  "Õpetaja seletas uut teemat.\nÕpilased kuulasid tähelepanelikult.\nKõik said teemast hästi aru.",
  "Vanaema küpsetas kooki.\nKöögis levis magus lõhn.\nLapsed ootasid põnevusega maiustamist.",
  "Linnud laulsid puu otsas.\nKevad oli saabunud.\nLilled hakkasid õitsema.",
  "Poiss sõitis jalgrattaga.\nTa kukkus ja sai põlve katki.\nEma pani põlvele plaastri.",
  "Tuul puhus tugevalt.\nPuude oksad kõikusid.\nLehed langesid maapinnale.",
  "Tüdruk luges raamatut.\nLugu oli väga põnev.\nTa ei suutnud lugemist lõpetada.",
  "Kass magas diivanil.\nKoer tuli tuppa.\nKass ärkas ja jooksis minema.",
  "Mees istus kohvikus.\nTa jõi tassi kohvi.\nTa vaatas aknast välja.",
  "Õunad kasvasid puu otsas.\nNad olid punased ja mahlased.\nLapsed korjasid neid korvi.",
  "Vihm sadas terve päeva.\nTänavad olid märjad.\nInimesed kandsid vihmavarje.",
  "Rebane hiilis metsas.\nTa otsis süüa.\nTa leidis mõned marjad põõsast.",
  "Õpilased tegid kontrolltööd.\nÜlesanded olid keerulised.\nKõik püüdsid keskenduda.",
  "Päike loojus mäe taha.\nTaevas värvus punaseks.\nÕhtu hakkas saabuma."
];

// Segab lause järjekorra kindlasse mustrisse (2-3-1)
function shuffleSentences(text) {
  const sentences = text.split('\n');
  
  // Kui on vähem kui 3 lauset, kasutame tavalist segamist
  if (sentences.length < 3) {
    for (let i = sentences.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [sentences[i], sentences[j]] = [sentences[j], sentences[i]];
    }
    return sentences.join('\n');
  }
  
  // Muudame järjekorra 1-2-3 -> 2-3-1
  return [sentences[1], sentences[2], sentences[0]].join('\n');
}

// Valime juhuslikud lood
const selectedStories = [];
while (selectedStories.length < rows) {
  const randomIndex = Math.floor(Math.random() * stories.length);
  const story = stories[randomIndex];
  if (!selectedStories.includes(story)) {
    selectedStories.push(story);
  }
}

// Loome tabeli read
for (let i = 0; i < rows; i++) {
  const story = selectedStories[i];
  const shuffledStory = shuffleSentences(story);
  
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${i + 1}</td>
    <td class="story-cell">${story}</td>
    <td><textarea data-correct="${story}" class="incorrect">${shuffledStory}</textarea></td>
  `;
  
  const textarea = tr.querySelector('textarea');
  textarea.addEventListener('input', handleInput);
  textareas.push(textarea);
  tableBody.appendChild(tr);
}

function handleInput() {
  if (startTime === null) {
    startTime = Date.now();
    timerInterval = setInterval(updateTimer, 50);
  }
  
  let allCorrect = true;
  for (const textarea of textareas) {
    const target = textarea.dataset.correct;
    
    // Eemaldame tühikud ja reavahetused teksti algusest ja lõpust
    // ning normaliseerime reavahetused teksti sees
    const current = textarea.value.trim().split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)  // Eemaldame tühjad read
      .join('\n');
      
    const expected = target.trim().split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)  // Eemaldame tühjad read
      .join('\n');
    
    if (current === expected) {
      textarea.classList.remove('incorrect');
      textarea.classList.add('correct');
    } else {
      textarea.classList.remove('correct');
      textarea.classList.add('incorrect');
      allCorrect = false;
    }
  }
  
  if (allCorrect) {
    clearInterval(timerInterval);
    const elapsed = (Date.now() - startTime) / 1000;
    timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
    fetch('save_result.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        elapsed: elapsed.toFixed(2),
        exercise_id: '<?php echo htmlspecialchars($_GET["task"] ?? "02"); ?>'
      })
    });
  }
}

function updateTimer() {
  const elapsed = (Date.now() - startTime) / 1000;
  timerDisplay.textContent = `Kulunud aeg: ${elapsed.toFixed(2)} s`;
  if (elapsed >= 120) {
    clearInterval(timerInterval);
    alert('Lubatud aeg ületatud. Vajuta OK, et uuesti proovida.');
    location.reload();
  }
}
</script>
