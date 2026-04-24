<?php
require_once __DIR__.'/../../config/db.php';
requireRole('triage');
$PAGE_TITLE='Active Cases'; $ACTIVE_NAV='cases';
$flash = $_GET['flash'] ?? '';

if (isset($_GET['close'])) {
    $id = (int)$_GET['close'];
    $conn->query("UPDATE triage_cases SET status='closed' WHERE case_id=$id");
    header("Location: ".BASE_URL."/pages/triage/cases.php?flash=Case+closed"); exit;
}

$cases = $conn->query("SELECT t.*, p.full_name AS pname, d.full_name AS dname FROM triage_cases t LEFT JOIN patients p ON t.patient_id=p.patient_id LEFT JOIN doctors d ON t.assigned_doctor_id=d.doctor_id WHERE t.status='active' ORDER BY FIELD(priority,'red','yellow','green'), t.created_at");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">📋 Active Triage Cases</div>
    <?php if ($cases->num_rows > 0):?>
        <table class="data-table">
            <tr><th>Priority</th><th>Patient</th><th>Complaint</th><th>Assigned To</th><th>Since</th><th>Action</th></tr>
            <?php while($c=$cases->fetch_assoc()):?>
                <tr><td><span class="badge badge-<?=e($c['priority'])?>"><?=strtoupper(e($c['priority']))?></span></td>
                <td><?=e($c['pname'] ?: $c['patient_name'])?><br><small class="text-muted"><?=e($c['patient_id'])?></small></td>
                <td><?=e($c['chief_complaint'])?></td>
                <td><?=e($c['dname'] ?: 'Unassigned')?></td>
                <td><?=date('d M H:i',strtotime($c['created_at']))?></td>
                <td><a href="?close=<?=(int)$c['case_id']?>" data-confirm="Close this case?" class="btn btn-sm btn-outline">Close</a></td></tr>
            <?php endwhile;?>
        </table>
    <?php else:?><p class="text-muted">No active cases.</p><?php endif;?>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
