<?php
$activeTab   = $_GET['tab'] ?? 'harjutused';
$isExerciseTab = $activeTab === 'harjutused';

function h($s): string
{return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}

/* --------------------------------------------------------------------------
 *  Helper functions
 * ----------------------------------------------------------------------- */

function best($attempts, $resultType = 'time'){
    if ($attempts === '') return null;
    $arr = array_map('floatval', explode(',', $attempts));

    // WPM exercises: higher is better, use only positive values (passed attempts)
    if ($resultType === 'wpm') {
        // Filter only positive values (passed attempts)
        $positive = array_filter($arr, function($v) { return $v > 0; });
        return $positive ? max($positive) : null;
    }
    // Time exercises: lower is better
    return min($arr);
}

function completionClass($cnt): string
{
    return $cnt === 0 ? 'no-attempt' : ($cnt >= 3 ? 'completion-three-or-more' : 'completion-under-three');
}
?>
<h2>
    <?php if (isset($exerciseFilter)): ?>
        Tulemused – Ülesanne <?= h($exerciseFilter) ?>
        <small><a href="?page=results&tab=<?= h($activeTab) ?>">« Kõik tulemused</a></small>
    <?php else: ?>
        Kõik tulemused
    <?php endif; ?>
</h2>

<div class="view-toggle">
    <label><input type="checkbox" id="summary-toggle" <?= $showSummary ? 'checked' : '' ?>> Kokkuvõte</label>
</div>

<nav class="tabs">
    <?php foreach (array('harjutused' => 'Harjutused', 'opilased' => 'Õpilased') as $k => $lbl): ?>
        <a href="?page=results&tab=<?= $k ?>" class="tab <?= $activeTab === $k ? 'active' : '' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
</nav>

<!-- styles (unchanged) -->
<style>
    .tabs{display:flex;margin:20px 0;border-bottom:1px solid #ccc}
    .tab{padding:10px 20px;text-decoration:none;color:#333;border:1px solid #ccc;border-bottom:none;border-radius:4px 4px 0 0;margin-right:5px;background:#f5f5f5}
    .tab.active{background:#fff;border-bottom:1px solid #fff;margin-bottom:-1px;font-weight:bold}
    .view-toggle{margin:15px 0}
    .exercise-header{background:#f0f0f0;padding:8px;margin-top:20px;font-weight:bold;border-radius:4px}
    .student-header{background:#f0f0f0;padding:8px;margin-top:20px;margin-bottom:0;font-weight:bold;border-radius:4px 4px 0 0;border:1px solid #000}
    .completion-count{text-align:center;font-weight:bold}
    .completion-under-three{background:#ffff99}
    .completion-three-or-more{background:#ccffcc}
    .no-attempt{background: rgba(255, 142, 155, 0.68);color:#721c24}
    .filter-input{padding:5px;width:200px;margin-bottom:10px}
    .exercise-cell{cursor:pointer;text-decoration:none;color:inherit!important;display:flex;align-items:center;justify-content:center;position:relative;min-height:20px;padding:2px;background:transparent}
    .exercise-cell:hover{text-decoration:underline}
    .exercise-cell:visited{color:inherit!important}
    .time-content{position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center}
    .main-time{position:absolute;left:50%;transform:translateX(-50%);white-space:nowrap}
    .comparison-time{position:absolute;left:calc(50% + 2.8em);transform:translateX(-50%);font-size:.9em;color:#999;white-space:nowrap}
    .crown-icon{position:absolute;right:2px;top:50%;transform:translateY(-50%);font-size:1.2em;line-height:1}
    .warning-icon{color:#ffc107;margin-right:5px;font-size:1em}
    th{background:#f0f0f0}
    .student-header + table{margin-top:0;border-top:none}
</style>

<?php if ($isExerciseTab): ?>
    <?php if (empty($summaryResults)): ?>
        <p>Tulemusi pole.</p>
    <?php else: ?>
        <?php
        // Create exercise lookup table
        $exerciseLookup = array();
        foreach ($allExercises as $ex) {
            $exerciseLookup[$ex['id']] = $ex;
        }
        ?>
        <input type="text" id="exerciseFilter" class="filter-input" placeholder="Filtreeri harjutusi...">
        <?php foreach ($summaryResults as $exId => $rows): ?>
            <div class="exercise-header">Harjutus <?= h($exId) ?></div>
            <?php if (empty($rows)): ?>
                <table><tbody><tr><td colspan="<?= $isAdmin ? 4 : 3 ?>" class="no-attempt">Keegi pole seda harjutust veel teinud</td></tr></tbody></table>
                <?php continue; endif; ?>
            <?php
            usort($rows, function($a, $b){ return strcmp($a['name'], $b['name']); });
            $exercise = isset($exerciseLookup[$exId]) ? $exerciseLookup[$exId] : null;
            $resultType = $exercise ? $exercise['result_type'] : 'time';
            $unit = ($resultType === 'wpm') ? ' WPM' : ' s';
            $globalBest = ($resultType === 'wpm') ? 0 : INF;
            foreach ($rows as $tmp){
                $b = best($tmp['attempts'], $resultType);
                if ($b !== null) {
                    if ($resultType === 'wpm' && $b > $globalBest) $globalBest = $b;
                    elseif ($resultType === 'time' && $b < $globalBest) $globalBest = $b;
                }
            }
            if ($globalBest === INF || $globalBest === 0) $globalBest = null;
            ?>
            <table>
                <thead><tr>
                    <th>Õpilane</th><th>Tulemusi</th><th data-bs-toggle="tooltip" title="Õpilase parim tulemus (kõikide õpilaste keskmine tulemus)">Parim Tulemus</th><?php if($isAdmin):?><th></th><?php endif; ?>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r):
                    $attempts = $r['attempts'] === '' ? array() : explode(',', $r['attempts']);
                    $cnt      = count($attempts);
                    $bestTry  = best($r['attempts'], $resultType);
                    $cls      = completionClass($cnt);
                    $isGlob   = ($bestTry !== null && $globalBest !== null && abs($bestTry - $globalBest) < 0.001);
                    ?>
                    <tr>
                        <td class="<?= $cls ?><?= $isGlob ? ' global-best-result' : '' ?>"><?= h($r['name']) ?></td>
                        <td class="completion-count <?= $cls ?>"><?= $cnt ?></td>
                        <td class="<?= $cls ?>" data-bs-toggle="tooltip" title="<?= h($r['name']) ?> parim tulemus (kõikide õpilaste keskmine tulemus)">
                            <?= $bestTry !== null ? round($bestTry) . $unit . ' (' . round($globalAverages[$exId]) . $unit . ')' : '-' ?><?= $isGlob ? ' 👑' : '' ?>
                        </td>
                        <?php if ($isAdmin): ?>
                            <td><a class="exercise-cell" href="?page=results&exercise=<?= h($r['exercise_id']) ?>&summary=0&email=<?= urlencode($r['email']) ?>&tab=<?= $activeTab ?>">🔍</a></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: /* Õpilased */ ?>
    <?php if (empty($studentResults)): ?>
        <p>Tulemusi pole.</p>
    <?php else: ?>
        <input type="text" id="studentFilter" class="filter-input" placeholder="Filtreeri õpilasi...">
        <?php
        require_once __DIR__ . '/../models/StudentsModel.php';
        $gradeMap = array();
        foreach ((new StudentsModel())->getAllStudents() as $s){
            $gradeMap[$s['email']] = $s['grade'];
        }
        $byGrade = array('5r'=>array(),'7r'=>array(),'8r'=>array(),'Määramata'=>array());
        foreach ($studentResults as $email => $stu){
            $grade = isset($gradeMap[$email]) ? $gradeMap[$email] : null;
            $key   = $grade ? $grade : 'Määramata';
            $stu['grade'] = $grade;
            $stu['email'] = $email;
            $byGrade[$key][] = $stu;
        }
        foreach ($byGrade as &$arr){
            usort($arr, function($a,$b){ return strcmp($a['name'], $b['name']); });
        }
        ?>
        <?php
        // Create exercise lookup table for students tab
        $exerciseLookup = array();
        foreach ($allExercises as $ex) {
            $exerciseLookup[$ex['id']] = $ex;
        }
        ?>
        <?php foreach ($byGrade as $grade => $students): if (empty($students)) continue; ?>
            <?php
            $best = $avg = $raw = array();
            foreach ($allExercises as $ex){
                $id = $ex['id'];
                $resultType = $ex['result_type'];
                $best[$id] = ($resultType === 'wpm') ? 0 : INF;
                $raw[$id]  = array();
            }
            foreach ($students as $st){
                foreach ($st['exercises'] as $ex){
                    $id = $ex['exercise_id'];
                    $exData = isset($exerciseLookup[$id]) ? $exerciseLookup[$id] : null;
                    $resultType = $exData ? $exData['result_type'] : 'time';
                    $b = best($ex['attempts'], $resultType);
                    if ($b !== null){
                        if ($resultType === 'wpm') {
                            if ($b > $best[$id]) $best[$id] = $b;
                        } else {
                            if ($b < $best[$id]) $best[$id] = $b;
                        }
                        $raw[$id][] = $b;
                    }
                }
            }
            foreach ($raw as $id=>$vals){
                $avg[$id] = $vals ? array_sum($vals)/count($vals) : null;
                if ($best[$id] === INF || $best[$id] === 0) $best[$id] = null;
            }
            ?>
            <table>
                <thead><tr><th>#</th><th><?= h($grade) ?></th><?php foreach ($allExercises as $ex){
                        $id = $ex['id'];
                        $unit = ($ex['result_type'] === 'wpm') ? ' WPM' : ' s';
                        echo '<th>'.h($id).($avg[$id]!==null?' ('.round($avg[$id]).$unit.')':'').'</th>';
                    } ?></tr></thead>
                <tbody>
                <?php foreach ($students as $idx => $st): ?>
                    <?php
                    $lookup = array();
                    foreach ($st['exercises'] as $ex){ $lookup[$ex['exercise_id']] = $ex; }

                    // Check if student has all exercises completed with 3+ attempts (all green)
                    $hasAllGreen = true;
                    foreach ($allExercises as $ex) {
                        $id = $ex['id'];
                        $d = isset($lookup[$id]) ? $lookup[$id] : null;
                        if ($d && ($b = best($d['attempts'], $ex['result_type'])) !== null) {
                            $cnt = $d['attempts'] === '' ? 0 : count(explode(',', $d['attempts']));
                            if ($cnt < 3) { // Not green (less than 3 attempts)
                                $hasAllGreen = false;
                                break;
                            }
                        } else {
                            // No attempt or no result
                            $hasAllGreen = false;
                            break;
                        }
                    }
                    ?>
                    <tr><td><?= $idx+1 ?></td><td><?= !$hasAllGreen ? '<span class="warning-icon">⚠️</span>' : '' ?><?= h($st['name']) ?></td>
                        <?php foreach ($allExercises as $ex): $id=$ex['id']; $d = isset($lookup[$id]) ? $lookup[$id] : null; if ($d && ($b=best($d['attempts'], $ex['result_type']))!==null):
                            $cnt = $d['attempts'] === '' ? 0 : count(explode(',', $d['attempts']));
                            $cls = completionClass($cnt);
                            $unit = ($ex['result_type'] === 'wpm') ? ' WPM' : ' s';
                            $diff = $avg[$id]!==null ? round($b - $avg[$id]) : 0;
                            $comp = $diff ? '(' . ($diff>0?'+':'') . $diff . $unit . ')' : '';
                            $top = ($best[$id]!==null && abs($b-$best[$id])<0.001);
                            ?>
                            <td class="<?= $cls ?>"><a href="?page=results&exercise=<?= urlencode($id) ?>&summary=0&email=<?= urlencode($st['email']) ?>&tab=<?= $activeTab ?>" class="exercise-cell" data-bs-toggle="tooltip" title="<?= h($st['name']) ?> parim tulemus (kõikide õpilaste keskmine tulemus)"><span class="time-content"><span class="main-time"><?= round($b) ?><?= $unit ?></span><?= $comp?'<span class="comparison-time">'.$comp.'</span>':'' ?></span><?= $top ? '<span class="crown-icon">👑</span>' : '' ?></a></td>
                        <?php else: ?><td class="no-attempt">-</td><?php endif; endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>


<script>
    (function(){
        /* summary toggle */
        var t=document.getElementById('summary-toggle'); if(t){ t.addEventListener('change',function(e){var u=new URL(window.location);u.searchParams.set('summary',e.target.checked?'1':'0');window.location=u;}); }
        /* filters */
        ['exercise','student'].forEach(function(type){
            var inp=document.getElementById(type+'Filter'); if(!inp) return;
            var selector=type==='exercise'?'.exercise-header':'.student-header';
            inp.addEventListener('input',function(e){
                var val=e.target.value.toLowerCase(); var any=false;
                Array.prototype.slice.call(document.querySelectorAll(selector)).forEach(function(h){
                    var tbl=h.nextElementSibling; if(!tbl||tbl.tagName!=='TABLE') return;
                    if(type==='exercise'){
                        var show=h.textContent.toLowerCase().indexOf(val)!==-1;
                        h.style.display=tbl.style.display=(show||!val)?'':'none'; any=any||show;
                    }else{
                        var vis=false; Array.prototype.slice.call(tbl.tBodies[0].rows).forEach(function(r){
                            var show=r.cells[1].textContent.toLowerCase().indexOf(val)!==-1;
                            r.style.display=show?'':'none'; vis=vis||show;
                        });
                        h.style.display=tbl.style.display=(vis||!val)?'':'none'; any=any||vis;
                    }
                });
                var msgId='no-'+type+'-matches'; var old=document.getElementById(msgId); if(old) old.parentNode.removeChild(old);
                if(val && !any){
                    var msg=document.createElement('div'); msg.id=msgId; msg.className='no-attempt'; msg.textContent='Ühtegi '+(type==='exercise'?'harjutust':'õpilast')+' ei leitud otsinguga "'+val+'"';
                    inp.parentNode.parentNode.insertBefore(msg, inp.parentNode.nextSibling);
                }
            });
        });
        /* sortable headers */
        Array.prototype.slice.call(document.querySelectorAll('table thead th')).forEach(function(th,idx){
            th.style.cursor='pointer'; th.addEventListener('click',function(){
                var tb=th.closest('table').tBodies[0]; var rows=Array.prototype.slice.call(tb.rows); var asc=th.getAttribute('data-sort')!=='asc';
                rows.sort(function(a,b){
                    var av=a.cells[idx].innerText.trim(); var bv=b.cells[idx].innerText.trim();
                    var num=!isNaN(av)&&!isNaN(bv);
                    return asc?(num?av-bv:av.localeCompare(bv)):(num?bv-av:bv.localeCompare(av));
                });
                rows.forEach(function(r){tb.appendChild(r);});
                th.setAttribute('data-sort', asc?'asc':'desc');
            });
        });
        /* Bootstrap tooltips (assumes bootstrap JS loaded) */
        if(window.bootstrap && bootstrap.Tooltip){ new bootstrap.Tooltip(document.body,{selector:'[data-bs-toggle="tooltip"]'}); }
    })();
</script>
