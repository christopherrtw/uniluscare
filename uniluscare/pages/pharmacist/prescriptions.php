<?php
require_once __DIR__.'/../../config/db.php';
requireRole('pharmacist');
$PAGE_TITLE='Prescriptions'; $ACTIVE_NAV='prescriptions';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='dispense') {
    $id = (int)$_POST['prescription_id'];
    $conn->query("UPDATE prescriptions SET status='dispensed' WHERE prescription_id=$id");
    $conn->query("UPDATE prescription_items SET dispensed=1 WHERE prescription_id=$id");
    // Decrement stock for each medicine (match by name)
    $items = $conn->query("SELECT medicine_name FROM prescription_items WHERE prescription_id=$id");
    while ($it = $items->fetch_assoc()) {
        $name = $conn->real_escape_string($it['medicine_name']);
        $conn->query("UPDATE medicines SET stock_quantity = GREATEST(stock_quantity - 1, 0) WHERE name = '$name'");
    }
    // Notify patient
    $res = $conn->query("SELECT patient_id FROM prescriptions WHERE prescription_id=$id");
    if ($row=$res->fetch_assoc()) {
        $pid = $row['patient_id'];
        $stmt = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'patient','Medication Ready','Your medication is ready for collection at the pharmacy.','medication')");
        $stmt->bind_param('s',$pid); $stmt->execute();
    }
    header("Location: ".BASE_URL."/pages/pharmacist/prescriptions.php?flash=Dispensed+and+inventory+updated"); exit;
}

$active = $conn->query("SELECT r.*,p.full_name AS pname,d.full_name AS dname FROM prescriptions r LEFT JOIN patients p ON r.patient_id=p.patient_id LEFT JOIN doctors d ON r.doctor_id=d.doctor_id WHERE r.status='active' ORDER BY r.created_at DESC");
$done = $conn->query("SELECT r.*,p.full_name AS pname FROM prescriptions r LEFT JOIN patients p ON r.patient_id=p.patient_id WHERE r.status='dispensed' ORDER BY r.created_at DESC LIMIT 20");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">💊 Active Prescriptions — Awaiting Dispensing</div>
    <?php if ($active->num_rows > 0): while($r=$active->fetch_assoc()):
        $items = $conn->query("SELECT * FROM prescription_items WHERE prescription_id=".(int)$r['prescription_id']);?>
        <div style="border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-bottom:0.75rem;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <div><strong><?=e($r['pname'])?></strong> <small class="text-muted">— prescribed by <?=e($r['dname'])?></small><br>
                <small><?=e($r['diagnosis'])?></small></div>
                <form method="POST">
                    <input type="hidden" name="action" value="dispense">
                    <input type="hidden" name="prescription_id" value="<?=(int)$r['prescription_id']?>">
                    <button class="btn btn-sm btn-primary">✓ Dispense</button>
                    <a href="<?=BASE_URL?>/api/prescription_pdf.php?id=<?=(int)$r['prescription_id']?>" target="_blank" class="btn btn-sm btn-outline">📄</a>
                </form>
            </div>
            <table class="data-table mt-2">
                <tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th></tr>
                <?php while($i=$items->fetch_assoc()):?>
                    <tr><td><?=e($i['medicine_name'])?></td><td><?=e($i['dosage'])?></td>
                    <td><?=e($i['frequency'])?></td><td><?=e($i['duration'])?></td></tr>
                <?php endwhile;?>
            </table>
        </div>
    <?php endwhile; else:?><p class="text-muted">No active prescriptions awaiting dispensing.</p><?php endif;?>
</div>
<div class="card">
    <div class="card-title">✓ Recently Dispensed</div>
    <table class="data-table">
        <tr><th>Patient</th><th>Diagnosis</th><th>Date</th></tr>
        <?php while($r=$done->fetch_assoc()):?>
            <tr><td><?=e($r['pname'])?></td><td><?=e($r['diagnosis'])?></td>
            <td><?=date('d M Y',strtotime($r['created_at']))?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
