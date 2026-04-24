<?php
require_once __DIR__.'/../../config/db.php';
requireRole('lab');
$PAGE_TITLE='Submit Results'; $ACTIVE_NAV='results';
$lid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)$_POST['test_id']; $res = $_POST['results']; $notes = $_POST['notes'] ?? '';
    $fname = null;
    if (isset($_FILES['result_file']) && $_FILES['result_file']['error']===0) {
        $fname = $id.'_'.time().'_'.preg_replace('/[^A-Za-z0-9._-]/','',basename($_FILES['result_file']['name']));
        move_uploaded_file($_FILES['result_file']['tmp_name'], __DIR__.'/../../assets/uploads/lab/'.$fname);
    }
    $stmt = $conn->prepare("UPDATE lab_tests SET results=?,notes=?,result_file=?,status='completed',completed_at=NOW(),lab_tech_id=? WHERE test_id=?");
    $stmt->bind_param('ssssi',$res,$notes,$fname,$lid,$id);
    $stmt->execute();
    // Notify patient & doctor
    $r = $conn->query("SELECT patient_id,doctor_id,test_name FROM lab_tests WHERE test_id=$id")->fetch_assoc();
    $pid = $r['patient_id']; $did = $r['doctor_id']; $msg = "Lab result for {$r['test_name']} is ready.";
    $conn->query("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('$pid','patient','Lab Result Ready','$msg','lab')");
    if ($did) $conn->query("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES ('$did','doctor','Lab Result Ready','$msg','lab')");
    header("Location: ".BASE_URL."/pages/lab/results.php?flash=Result+submitted+and+notifications+sent"); exit;
}

$tid = (int)($_GET['test_id'] ?? 0);
$selected = $tid ? $conn->query("SELECT l.*,p.full_name AS pname FROM lab_tests l LEFT JOIN patients p ON l.patient_id=p.patient_id WHERE test_id=$tid")->fetch_assoc() : null;
$recent = $conn->query("SELECT l.*,p.full_name AS pname FROM lab_tests l LEFT JOIN patients p ON l.patient_id=p.patient_id WHERE l.status='completed' AND l.lab_tech_id='$lid' ORDER BY l.completed_at DESC LIMIT 20");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<?php if ($selected):?>
<div class="card mb-3">
    <div class="card-title">📝 Submit Result for <?=e($selected['test_name'])?></div>
    <p><strong>Patient:</strong> <?=e($selected['pname'])?></p>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="test_id" value="<?=$tid?>">
        <div class="form-group"><label>Results</label><textarea name="results" rows="5" required placeholder="Enter test results, reference ranges, interpretations..."></textarea></div>
        <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>
        <div class="form-group"><label>Attach Report File (optional)</label><input type="file" name="result_file"></div>
        <button class="btn btn-primary" type="submit">Submit Result & Notify</button>
    </form>
</div>
<?php else:?>
<div class="alert alert-info">Select a test from the <a href="<?=BASE_URL?>/pages/lab/tests.php">Test Requests</a> page to enter results.</div>
<?php endif;?>
<div class="card">
    <div class="card-title">✓ Recently Completed</div>
    <table class="data-table">
        <tr><th>Patient</th><th>Test</th><th>Completed</th><th>Report</th></tr>
        <?php while($t=$recent->fetch_assoc()):?>
            <tr><td><?=e($t['pname'])?></td><td><?=e($t['test_name'])?></td>
            <td><?=date('d M Y H:i',strtotime($t['completed_at']))?></td>
            <td><?php if($t['result_file']):?><a href="<?=BASE_URL?>/assets/uploads/lab/<?=e($t['result_file'])?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
