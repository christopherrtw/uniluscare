<?php
require_once __DIR__.'/../../config/db.php';
requireRole('nurse');
$PAGE_TITLE='Nursing Notes'; $ACTIVE_NAV='notes';
$nid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = $_POST['patient_id']; $note = $_POST['note'];
    $stmt = $conn->prepare("INSERT INTO nursing_notes (patient_id,nurse_id,note) VALUES (?,?,?)");
    $stmt->bind_param('sss',$pid,$nid,$note);
    $stmt->execute();
    header("Location: ".BASE_URL."/pages/nurse/notes.php?flash=Note+saved"); exit;
}

$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name");
$recent = $conn->query("SELECT n.*,p.full_name AS pname FROM nursing_notes n LEFT JOIN patients p ON n.patient_id=p.patient_id WHERE n.nurse_id='$nid' ORDER BY n.created_at DESC LIMIT 20");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">📝 Add Nursing Note</div>
    <form method="POST">
        <div class="form-group"><label>Patient</label>
            <select name="patient_id" required>
                <option value="">-- Select --</option>
                <?php while($p=$patients->fetch_assoc()):?>
                    <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?></option>
                <?php endwhile;?>
            </select>
        </div>
        <div class="form-group"><label>Note</label><textarea name="note" rows="5" required placeholder="Observations, patient condition, care provided, consultation notes..."></textarea></div>
        <button class="btn btn-primary" type="submit">Save Note</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📋 Your Recent Notes</div>
    <?php while($n=$recent->fetch_assoc()):?>
        <div style="border-left:3px solid var(--primary);padding:0.6rem 1rem;margin-bottom:0.75rem;background:#f8f9fa;">
            <div class="text-sm text-muted"><?=e($n['pname'])?> — <?=date('d M Y H:i',strtotime($n['created_at']))?></div>
            <div><?=e($n['note'])?></div>
        </div>
    <?php endwhile;?>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
