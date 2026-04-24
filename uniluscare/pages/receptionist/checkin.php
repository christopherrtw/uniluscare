<?php
require_once __DIR__.'/../../config/db.php';
requireRole('receptionist');
$PAGE_TITLE='Check-in/out'; $ACTIVE_NAV='checkin';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)$_POST['appointment_id'];
    $act = $_POST['act'];
    $status = $act==='checkin' ? 'confirmed' : 'completed';
    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");
    $stmt->bind_param('si',$status,$id); $stmt->execute();
    header("Location: ".BASE_URL."/pages/receptionist/checkin.php?flash=Status+updated"); exit;
}

$today = $conn->query("SELECT a.*,p.full_name AS pname,d.full_name AS dname FROM appointments a LEFT JOIN patients p ON a.patient_id=p.patient_id LEFT JOIN doctors d ON a.doctor_id=d.doctor_id WHERE a.appointment_date=CURDATE() ORDER BY a.appointment_time");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">🚪 Today's Check-in / Check-out</div>
    <table class="data-table">
        <tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th><th>Actions</th></tr>
        <?php while($a=$today->fetch_assoc()):?>
            <tr><td><?=date('H:i',strtotime($a['appointment_time']))?></td>
            <td><?=e($a['pname'])?></td><td><?=e($a['dname']?:'—')?></td>
            <td><span class="badge badge-<?=e($a['status'])?>"><?=e($a['status'])?></span></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="appointment_id" value="<?=(int)$a['appointment_id']?>">
                    <?php if ($a['status']==='pending' || $a['status']==='confirmed'):?>
                        <button name="act" value="checkin" class="btn btn-sm btn-primary">✓ Check in</button>
                    <?php endif;?>
                    <?php if ($a['status']!=='completed' && $a['status']!=='cancelled'):?>
                        <button name="act" value="checkout" class="btn btn-sm btn-outline">✓ Check out</button>
                    <?php endif;?>
                </form>
            </td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
