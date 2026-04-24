<?php
require_once __DIR__.'/../../config/db.php';
requireRole('nurse');
$PAGE_TITLE='Medications'; $ACTIVE_NAV='medication';
$nid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = $_POST['patient_id']; $med = $_POST['medicine_name']; $dose = $_POST['dosage'];
    $stmt = $conn->prepare("INSERT INTO medication_administration (patient_id,nurse_id,medicine_name,dosage) VALUES (?,?,?,?)");
    $stmt->bind_param('ssss',$pid,$nid,$med,$dose);
    $stmt->execute();
    header("Location: ".BASE_URL."/pages/nurse/medication.php?flash=Medication+recorded"); exit;
}

$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name");
$recent = $conn->query("SELECT m.*,p.full_name AS pname FROM medication_administration m LEFT JOIN patients p ON m.patient_id=p.patient_id WHERE m.nurse_id='$nid' ORDER BY m.administered_at DESC LIMIT 20");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">💊 Administer Medication</div>
    <form method="POST">
        <div class="form-group"><label>Patient</label>
            <select name="patient_id" required>
                <option value="">-- Select --</option>
                <?php while($p=$patients->fetch_assoc()):?>
                    <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?></option>
                <?php endwhile;?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Medicine</label><input type="text" name="medicine_name" required></div>
            <div class="form-group"><label>Dosage</label><input type="text" name="dosage" required placeholder="e.g. 500mg PO"></div>
        </div>
        <button class="btn btn-primary" type="submit">Record Administration</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📋 Today's Administration Log</div>
    <table class="data-table">
        <tr><th>Patient</th><th>Medicine</th><th>Dosage</th><th>When</th></tr>
        <?php while($m=$recent->fetch_assoc()):?>
            <tr><td><?=e($m['pname'])?></td><td><?=e($m['medicine_name'])?></td>
            <td><?=e($m['dosage'])?></td>
            <td><?=date('d M H:i',strtotime($m['administered_at']))?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
