<?php
require_once __DIR__.'/../../config/db.php';
requireRole('lab');
$PAGE_TITLE='Test Requests'; $ACTIVE_NAV='tests';
$lid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $id = (int)$_POST['test_id'];
    $status = $_POST['action']==='collect' ? 'sample_collected' : 'in_progress';
    $stmt = $conn->prepare("UPDATE lab_tests SET status=?,lab_tech_id=? WHERE test_id=?");
    $stmt->bind_param('ssi',$status,$lid,$id);
    $stmt->execute();
    header("Location: ".BASE_URL."/pages/lab/tests.php?flash=Status+updated"); exit;
}

$tests = $conn->query("SELECT l.*,p.full_name AS pname,d.full_name AS dname FROM lab_tests l LEFT JOIN patients p ON l.patient_id=p.patient_id LEFT JOIN doctors d ON l.doctor_id=d.doctor_id WHERE l.status!='completed' ORDER BY l.requested_at ASC");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card">
    <div class="card-title">🧪 Pending & In-Progress Lab Tests</div>
    <?php if ($tests->num_rows > 0):?>
        <table class="data-table">
            <tr><th>Patient</th><th>Test</th><th>Type</th><th>Requested By</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            <?php while($t=$tests->fetch_assoc()):?>
                <tr><td><?=e($t['pname'])?><br><small class="text-muted"><?=e($t['patient_id'])?></small></td>
                <td><strong><?=e($t['test_name'])?></strong></td><td><?=e($t['test_type'])?></td>
                <td><?=e($t['dname'])?></td>
                <td><?=date('d M H:i',strtotime($t['requested_at']))?></td>
                <td><span class="badge badge-<?=$t['status']==='requested'?'warning':'info'?>"><?=e($t['status'])?></span></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="test_id" value="<?=(int)$t['test_id']?>">
                        <?php if($t['status']==='requested'):?>
                            <button name="action" value="collect" class="btn btn-sm btn-outline">Mark Collected</button>
                        <?php endif;?>
                        <?php if($t['status']==='sample_collected'):?>
                            <button name="action" value="progress" class="btn btn-sm btn-outline">In Progress</button>
                        <?php endif;?>
                    </form>
                    <a href="<?=BASE_URL?>/pages/lab/results.php?test_id=<?=(int)$t['test_id']?>" class="btn btn-sm btn-primary">Enter Result</a>
                </td></tr>
            <?php endwhile;?>
        </table>
    <?php else:?><p class="text-muted">No pending tests.</p><?php endif;?>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
