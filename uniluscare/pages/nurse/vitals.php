<?php
require_once __DIR__.'/../../config/db.php';
requireRole('nurse');
$PAGE_TITLE='Record Vitals'; $ACTIVE_NAV='vitals';
$nid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid  = $_POST['patient_id'];
    $sys  = (int)$_POST['bp_systolic'];
    $dia  = (int)$_POST['bp_diastolic'];
    $hr   = (int)$_POST['heart_rate'];
    $temp = (float)$_POST['temperature'];
    $rr   = (int)$_POST['respiratory_rate'];
    $spo2 = (int)$_POST['oxygen_saturation'];
    $w    = (float)($_POST['weight'] ?: 0);
    $h    = (float)($_POST['height'] ?: 0);
    $notes= $_POST['notes'] ?? '';

    $stmt = $conn->prepare("INSERT INTO vitals (patient_id,recorded_by,bp_systolic,bp_diastolic,heart_rate,temperature,respiratory_rate,oxygen_saturation,weight,height,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    // 11 placeholders: s,s,i,i,i,d,i,i,d,d,s
    $stmt->bind_param('ssiiididds'.'s', $pid,$nid,$sys,$dia,$hr,$temp,$rr,$spo2,$w,$h,$notes);
    $stmt->execute();

    if ($sys > 140 || $dia > 90 || $hr > 100 || $spo2 < 95 || $temp > 38) {
        $msg = "Patient $pid has abnormal vitals — please review.";
        $n = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('D0001','doctor','Abnormal Vitals',?,'system')");
        $n->bind_param('s',$msg); $n->execute();
    }
    header("Location: ".BASE_URL."/pages/nurse/vitals.php?flash=Vitals+recorded"); exit;
}

$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name");
$recent = $conn->query("SELECT v.*,p.full_name AS pname FROM vitals v LEFT JOIN patients p ON v.patient_id=p.patient_id WHERE v.recorded_by='$nid' ORDER BY v.recorded_at DESC LIMIT 10");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">❤️ Record Patient Vitals</div>
    <form method="POST">
        <div class="form-group"><label>Patient</label>
            <select name="patient_id" required>
                <option value="">-- Select --</option>
                <?php while($p=$patients->fetch_assoc()):?>
                    <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?> (<?=e($p['patient_id'])?>)</option>
                <?php endwhile;?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group"><label>BP Systolic</label><input type="number" name="bp_systolic" required></div>
            <div class="form-group"><label>BP Diastolic</label><input type="number" name="bp_diastolic" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Heart Rate (bpm)</label><input type="number" name="heart_rate" required></div>
            <div class="form-group"><label>Temperature (°C)</label><input type="number" step="0.1" name="temperature" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Respiratory Rate</label><input type="number" name="respiratory_rate" required></div>
            <div class="form-group"><label>SpO₂ (%)</label><input type="number" name="oxygen_saturation" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Weight (kg)</label><input type="number" step="0.1" name="weight"></div>
            <div class="form-group"><label>Height (cm)</label><input type="number" step="0.1" name="height"></div>
        </div>
        <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>
        <button class="btn btn-primary" type="submit">Save Vitals</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📊 Recent Vitals You've Recorded</div>
    <table class="data-table">
        <tr><th>Patient</th><th>BP</th><th>HR</th><th>Temp</th><th>SpO₂</th><th>When</th></tr>
        <?php while($v=$recent->fetch_assoc()):?>
            <tr><td><?=e($v['pname'])?></td>
            <td><?=$v['bp_systolic']?>/<?=$v['bp_diastolic']?></td>
            <td><?=$v['heart_rate']?></td>
            <td><?=$v['temperature']?>°C</td>
            <td><?=$v['oxygen_saturation']?>%</td>
            <td><?=date('d M H:i',strtotime($v['recorded_at']))?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
